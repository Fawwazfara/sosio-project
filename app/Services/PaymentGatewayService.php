<?php

namespace App\Services;

use App\Models\Infak;
use App\Models\LogAktivitas;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PaymentGatewayService
{
    public function resolveDriver(): string
    {
        $driver = trim((string) config('services.payment.driver', ''));
        if ($driver === '') {
            return trim((string) config('services.midtrans.server_key', '')) !== '' ? 'midtrans' : 'sandbox';
        }

        return $driver;
    }

    public function midtransBaseUrl(): string
    {
        return config('services.midtrans.is_production', false)
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    /**
     * Hasilkan payload QRIS:
     * - Jika driver = midtrans & Server Key terisi -> panggil Midtrans Core API /v2/charge (payment_type=qris).
     * - Jika driver = sandbox -> hasilkan payload EMVCo lokal untuk demo offline/tanpa API key.
     */
    public function createQrisCharge(Pembayaran $pembayaran): array
    {
        $driver = $this->resolveDriver();
        $serverKey = trim((string) config('services.midtrans.server_key', ''));

        if ($driver === 'midtrans') {
            if ($serverKey === '') {
                throw new \RuntimeException('MIDTRANS_SERVER_KEY belum diatur pada file .env.');
            }

            return $this->chargeMidtransQris($pembayaran, $serverKey);
        }

        return $this->createLocalSandboxQris($pembayaran);
    }

    private function chargeMidtransQris(Pembayaran $pembayaran, string $serverKey): array
    {
        $pembayaran->loadMissing(['siswa', 'waliUser']);

        $email = $pembayaran->waliUser?->email;
        $validEmail = ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) ? $email : 'wali@assajjad.sch.id';
        $phone = preg_replace('/[^0-9+]/', '', (string) ($pembayaran->siswa?->wa_wali_1 ?: '081200000000'));
        $monthsCount = max(1, count($pembayaran->bulan_list ?? []));
        $isProduction = (bool) config('services.midtrans.is_production', false);

        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $pembayaran->kode_referensi,
                'gross_amount' => (int) $pembayaran->total_nominal,
            ],
            'qris' => [
                'acquirer' => (string) config('services.midtrans.qris_acquirer', 'gopay'),
            ],
            'customer_details' => [
                'first_name' => substr((string) ($pembayaran->siswa?->nama_lengkap ?? 'Wali Siswa'), 0, 50),
                'email' => $validEmail,
                'phone' => $phone ?: '081200000000',
            ],
            'item_details' => [[
                'id' => 'INFAK-' . $pembayaran->siswa_id,
                'price' => (int) $pembayaran->nominal_per_bulan,
                'quantity' => $monthsCount,
                'name' => substr('Infak ' . ($pembayaran->siswa?->nama_lengkap ?? 'Siswa'), 0, 50),
            ]],
            'custom_expiry' => [
                'expiry_duration' => 15,
                'unit' => 'minute',
            ],
        ];

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->timeout(15)
            ->post($this->midtransBaseUrl() . '/v2/charge', $payload);

        $data = $response->json() ?? [];
        $statusCode = (string) ($data['status_code'] ?? $response->status());

        if ($response->failed() || !in_array($statusCode, ['200', '201'], true)) {
            $msg = $data['status_message'] ?? 'Gagal menghubungi Midtrans QRIS API.';
            throw new \RuntimeException($msg);
        }

        $qrImageUrl = null;
        foreach ($data['actions'] ?? [] as $action) {
            if (in_array($action['name'] ?? '', ['generate-qr-code-v2', 'generate-qr-code'], true)) {
                $qrImageUrl = $action['url'] ?? null;
                if (($action['name'] ?? '') === 'generate-qr-code-v2') {
                    break;
                }
            }
        }

        $expiredAt = !empty($data['expiry_time'])
            ? \Carbon\Carbon::parse($data['expiry_time'])
            : now()->addMinutes(15);

        return [
            'driver' => 'midtrans',
            'channel' => $isProduction ? 'midtrans_qris' : 'midtrans_qris_sandbox',
            'sandbox' => !$isProduction,
            'qr_payload' => $data['qr_string'] ?? null,
            'qr_image_url' => $qrImageUrl,
            'gateway_transaction_id' => $data['transaction_id'] ?? null,
            'gateway_status' => $data['transaction_status'] ?? 'pending',
            'gateway_response' => $data,
            'expired_at' => $expiredAt,
        ];
    }

    private function createLocalSandboxQris(Pembayaran $pembayaran): array
    {
        $merchantName = substr(strtoupper((string) config('services.payment.merchant_name', 'MADRASAH AS-SAJJAAD')), 0, 25);
        $merchantCity = substr(strtoupper((string) config('services.payment.merchant_city', 'INDONESIA')), 0, 15);
        $nmid = (string) config('services.payment.nmid', 'ID1026000881920');
        $amountStr = (string) ((int) $pembayaran->total_nominal);

        $tlv = fn (string $id, string $val): string => $id . str_pad((string) strlen($val), 2, '0', STR_PAD_LEFT) . $val;

        $merchantAccountInfo = $tlv('00', 'ID.CO.QRIS.WWW')
            . $tlv('01', '936009143' . substr(preg_replace('/\D/', '', $nmid), 0, 10))
            . $tlv('02', $nmid)
            . $tlv('03', 'UMI');

        $additionalData = $tlv('01', $pembayaran->kode_referensi)
            . $tlv('07', 'INFAK-' . $pembayaran->siswa_id);

        $body = $tlv('00', '01')
            . $tlv('01', '12')
            . $tlv('26', $merchantAccountInfo)
            . $tlv('52', '8299')
            . $tlv('53', '360')
            . $tlv('54', $amountStr)
            . $tlv('58', 'ID')
            . $tlv('59', $merchantName)
            . $tlv('60', $merchantCity)
            . $tlv('61', '40000')
            . $tlv('62', $additionalData)
            . '6304';

        $crc = $this->crc16Ccitt($body);

        return [
            'driver' => 'sandbox',
            'channel' => 'qris_sandbox',
            'sandbox' => true,
            'qr_payload' => $body . $crc,
            'qr_image_url' => null,
            'gateway_transaction_id' => null,
            'gateway_status' => 'pending',
            'gateway_response' => null,
            'expired_at' => now()->addMinutes(15),
        ];
    }

    /**
     * Sinkronisasi status langsung ke Midtrans GET /v2/{order_id}/status
     * (berguna saat demo di localhost tanpa ngrok atau sebagai fallback polling).
     */
    public function syncMidtransStatus(Pembayaran $pembayaran): Pembayaran
    {
        $serverKey = trim((string) config('services.midtrans.server_key', ''));
        if ($serverKey === '' || $pembayaran->metode !== 'online') {
            return $pembayaran;
        }

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->timeout(12)
            ->get($this->midtransBaseUrl() . '/v2/' . urlencode($pembayaran->kode_referensi) . '/status');

        $data = $response->json() ?? [];
        if ($response->failed() || empty($data['transaction_status'])) {
            return $pembayaran;
        }

        return $this->applyMidtransStatus($pembayaran, $data);
    }

    public function cancelMidtransOrder(Pembayaran $pembayaran): void
    {
        $serverKey = trim((string) config('services.midtrans.server_key', ''));
        if ($serverKey === '' || !str_starts_with((string) $pembayaran->channel, 'midtrans')) {
            return;
        }

        try {
            Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->timeout(8)
                ->post($this->midtransBaseUrl() . '/v2/' . urlencode($pembayaran->kode_referensi) . '/cancel');
        } catch (\Throwable) {
            // Abaikan bila transaksi sudah expired/dibatalkan di Midtrans
        }
    }

    public function verifyMidtransSignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $serverKey = trim((string) config('services.midtrans.server_key', ''));
        if ($serverKey === '' || $signatureKey === '') {
            return false;
        }

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        return hash_equals($expected, $signatureKey);
    }

    public function applyMidtransStatus(Pembayaran $pembayaran, array $payload): Pembayaran
    {
        $txStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');

        $pembayaran->update([
            'gateway_transaction_id' => $payload['transaction_id'] ?? $pembayaran->gateway_transaction_id,
            'gateway_status' => $txStatus ?: $pembayaran->gateway_status,
            'gateway_response' => $payload,
        ]);

        if ($txStatus === 'settlement' || ($txStatus === 'capture' && ($fraudStatus === '' || $fraudStatus === 'accept'))) {
            $grossAmount = isset($payload['gross_amount']) ? (float) $payload['gross_amount'] : (float) $pembayaran->total_nominal;
            if ((int) round($grossAmount) !== (int) $pembayaran->total_nominal) {
                throw new \RuntimeException('Nominal pembayaran Midtrans tidak cocok dengan tagihan.');
            }

            return $this->settlePayment($pembayaran, null, 'online_qris');
        }

        if ($pembayaran->status !== 'paid') {
            if ($txStatus === 'expire') {
                $pembayaran->update(['status' => 'expired']);
            } elseif (in_array($txStatus, ['cancel', 'deny', 'failure'], true)) {
                $pembayaran->update(['status' => 'cancelled']);
            }
        }

        return $pembayaran->fresh(['siswa', 'kelas.wali_kelas', 'confirmedBy']);
    }

    /**
     * Selesaikan transaksi (dari callback/simulasi QRIS online atau konfirmasi tunai wali kelas).
     * Idempoten dan dibungkus transaksi database.
     */
    public function settlePayment(Pembayaran $pembayaran, ?User $actor = null, ?string $metodeBayar = null): Pembayaran
    {
        return DB::transaction(function () use ($pembayaran, $actor, $metodeBayar) {
            /** @var Pembayaran $locked */
            $locked = Pembayaran::with(['siswa', 'kelas'])->lockForUpdate()->findOrFail($pembayaran->id);

            if ($locked->status === 'paid') {
                return $locked->fresh(['siswa', 'kelas.wali_kelas', 'confirmedBy']);
            }

            $channelLabel = $locked->metode === 'online'
                ? 'Bayar Online (QRIS)'
                : 'Tunai via Wali Kelas';

            $resolvedMetode = $metodeBayar ?? ($locked->metode === 'online' ? 'online_qris' : 'offline_wali');

            $existingMonths = Infak::where('siswa_id', $locked->siswa_id)
                ->where('tahun', $locked->tahun)
                ->pluck('bulan')
                ->all();

            $monthsToInsert = array_values(array_diff($locked->bulan_list ?? [], $existingMonths));
            $now = now();
            $rows = [];

            foreach ($monthsToInsert as $bulan) {
                $rows[] = [
                    'siswa_id' => $locked->siswa_id,
                    'kelas_id' => $locked->kelas_id,
                    'jumlah' => $locked->nominal_per_bulan,
                    'bulan' => $bulan,
                    'tahun' => $locked->tahun,
                    'tanggal_bayar' => $now->toDateString(),
                    'keterangan' => $channelLabel . ' • Ref ' . $locked->kode_referensi,
                    'pembayaran_id' => $locked->id,
                    'metode_bayar' => $resolvedMetode,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($rows)) {
                Infak::insert($rows);
            }

            $locked->update([
                'status' => 'paid',
                'gateway_status' => $locked->metode === 'online' ? 'settlement' : $locked->gateway_status,
                'paid_at' => $now,
                'confirmed_by' => $actor?->id,
            ]);

            $siswaName = $locked->siswa?->nama_lengkap ?? 'Siswa';
            $kelasName = $locked->kelas?->nama_kelas ?? 'Kelas';
            $bulanStr = implode(', ', $locked->bulan_list ?? []);

            LogAktivitas::create([
                'type' => 'income',
                'title' => $locked->metode === 'online'
                    ? 'Pembayaran Online (QRIS) Diterima'
                    : 'Setoran Tunai Dikonfirmasi Wali Kelas',
                'description' => "{$siswaName} ({$kelasName}) melunasi bulan {$bulanStr} via {$channelLabel} [{$locked->kode_referensi}]",
                'amount' => $locked->total_nominal,
            ]);

            $waWali = $locked->siswa?->wa_wali_1;
            if ($waWali) {
                $pesan = "Assalamu'alaikum Wr. Wb.\n\n"
                    . "Alhamdulillah, pembayaran infak ananda *{$siswaName}* ({$kelasName}) untuk bulan *{$bulanStr}* sebesar *Rp "
                    . number_format((int) $locked->total_nominal, 0, ',', '.')
                    . "* melalui *{$channelLabel}* (Ref: {$locked->kode_referensi}) telah tercatat LUNAS.\n\n"
                    . "Terima kasih atas amanah Bapak/Ibu. Semoga Allah memberkahi keluarga.\n\n"
                    . "Wassalamu'alaikum Wr. Wb.\n"
                    . "Madrasah As-Sajjaad";
                WhatsAppService::queue($waWali, $pesan, 'terima-kasih');
            }

            return $locked->fresh(['siswa', 'kelas.wali_kelas', 'confirmedBy']);
        });
    }

    private function crc16Ccitt(string $data): string
    {
        $crc = 0xFFFF;
        $len = strlen($data);
        for ($i = 0; $i < $len; $i++) {
            $crc ^= ord($data[$i]) << 8;
            for ($j = 0; $j < 8; $j++) {
                if (($crc & 0x8000) !== 0) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
