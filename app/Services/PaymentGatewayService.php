<?php

namespace App\Services;

use App\Models\Infak;
use App\Models\LogAktivitas;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentGatewayService
{
    /**
     * Hasilkan payload QRIS berformat EMVCo untuk transaksi online.
     * Pada mode sandbox, menghasilkan payload QRIS standar lokal yang siap
     * diganti dengan respons Snap/QRIS Midtrans atau Xendit saat integrasi gateway.
     */
    public function createQrisCharge(Pembayaran $pembayaran): array
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
            . $tlv('01', '12') // Dynamic QRIS
            . $tlv('26', $merchantAccountInfo)
            . $tlv('52', '8299') // Educational Services
            . $tlv('53', '360')  // IDR
            . $tlv('54', $amountStr)
            . $tlv('58', 'ID')
            . $tlv('59', $merchantName)
            . $tlv('60', $merchantCity)
            . $tlv('61', '40000')
            . $tlv('62', $additionalData)
            . '6304';

        $crc = $this->crc16Ccitt($body);

        return [
            'driver' => config('services.payment.driver', 'sandbox'),
            'sandbox' => (bool) config('services.payment.sandbox', true),
            'qr_payload' => $body . $crc,
            'expired_at' => now()->addMinutes(15),
        ];
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
                return $locked;
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

            return $locked->fresh(['siswa', 'kelas', 'confirmedBy']);
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

