<?php

namespace App\Http\Controllers\Api;

use App\AcademicYear;
use App\Http\Controllers\Controller;
use App\Models\Infak;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WaliPortalController extends Controller
{
    private const MONTH_MAP = [
        'Jul' => 'July',
        'Agu' => 'August',
        'Sep' => 'September',
        'Okt' => 'October',
        'Nov' => 'November',
        'Des' => 'December',
        'Jan' => 'January',
        'Feb' => 'February',
        'Mar' => 'March',
        'Apr' => 'April',
        'Mei' => 'May',
        'Jun' => 'June',
    ];

    private const MONTH_LABELS_ID = [
        'July' => 'Juli',
        'August' => 'Agustus',
        'September' => 'September',
        'October' => 'Oktober',
        'November' => 'November',
        'December' => 'Desember',
        'January' => 'Januari',
        'February' => 'Februari',
        'March' => 'Maret',
        'April' => 'April',
        'May' => 'Mei',
        'June' => 'Juni',
    ];

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $tahun = AcademicYear::current();

        $siswas = Siswa::with([
            'kelas.wali_kelas',
            'infaks' => fn ($q) => $q->where('tahun', $tahun),
            'pembayarans' => fn ($q) => $q->where('tahun', $tahun)->orderByDesc('created_at'),
        ])->where('wali_user_id', $user->id)->get();

        // Expire stale online QRIS transactions
        Pembayaran::where('wali_user_id', $user->id)
            ->where('metode', 'online')
            ->where('status', 'pending')
            ->whereNotNull('expired_at')
            ->where('expired_at', '<', now())
            ->update(['status' => 'expired']);

        $studentsData = $siswas->map(function (Siswa $siswa) {
            $paidByMonth = $siswa->infaks->keyBy('bulan');

            $activePayments = $siswa->pembayarans
                ->whereIn('status', ['pending', 'waiting_offline'])
                ->values();

            $pendingMonthMap = [];
            foreach ($activePayments as $p) {
                foreach ($p->bulan_list ?? [] as $m) {
                    $pendingMonthMap[$m] = [
                        'pembayaran_id' => $p->id,
                        'kode_referensi' => $p->kode_referensi,
                        'metode' => $p->metode,
                        'status' => $p->status,
                    ];
                }
            }

            $months = [];
            $paidCount = 0;
            foreach (self::MONTH_MAP as $short => $eng) {
                $infak = $paidByMonth->get($eng);
                $pending = $pendingMonthMap[$eng] ?? null;
                if ($infak) {
                    $paidCount++;
                }

                $months[] = [
                    'short' => $short,
                    'english' => $eng,
                    'label' => self::MONTH_LABELS_ID[$eng] ?? $eng,
                    'paid' => (bool) $infak,
                    'jumlah' => $infak ? (int) $infak->jumlah : null,
                    'tanggal_bayar' => $infak ? substr((string) $infak->tanggal_bayar, 0, 10) : null,
                    'metode_bayar' => $infak?->metode_bayar ?: ($infak ? 'tunai_tu' : null),
                    'keterangan' => $infak?->keterangan,
                    'pending' => $infak ? null : $pending,
                ];
            }

            return [
                'id' => $siswa->id,
                'nama_lengkap' => $siswa->nama_lengkap,
                'nis' => $siswa->nis,
                'blok' => $siswa->blok ?: '-',
                'nama_wali_1' => $siswa->nama_wali_1 ?: $siswa->waliUser?->name,
                'wa_wali_1' => $siswa->wa_wali_1,
                'kelas_id' => $siswa->kelas_id,
                'kelas' => $siswa->kelas?->nama_kelas ?? '-',
                'status' => $siswa->status ?: 'aktif',
                'tanggal_keluar' => $siswa->tanggal_keluar?->format('Y-m-d'),
                'wali_kelas' => $siswa->kelas?->wali_kelas?->name ?? 'Tata Usaha / Admin',
                'paid_count' => $paidCount,
                'unpaid_count' => 12 - $paidCount,
                'months' => $months,
                'active_payments' => $activePayments->map(fn (Pembayaran $p) => $this->formatPayment($p))->values(),
            ];
        });

        return response()->json([
            'wali' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'academic_year' => $tahun . '/' . ($tahun + 1),
            'payment_driver' => app(PaymentGatewayService::class)->resolveDriver(),
            'sandbox_enabled' => app(PaymentGatewayService::class)->resolveDriver() === 'midtrans'
                ? !config('services.midtrans.is_production', false)
                : (bool) config('services.payment.sandbox', true),
            'students' => $studentsData,
        ]);
    }

    public function createPayment(Request $request, PaymentGatewayService $gateway)
    {
        $request->validate([
            'siswa_id' => 'required|integer|exists:siswas,id',
            'months' => 'required|array|min:1',
            'months.*' => 'required|string',
            'nominal_per_bulan' => 'required|integer|min:1000|max:10000000',
            'metode' => 'required|in:online,offline',
            'catatan' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $siswa = Siswa::with(['kelas.wali_kelas'])
            ->where('id', $request->siswa_id)
            ->when($user->role !== 'admin', fn ($q) => $q->where('wali_user_id', $user->id))
            ->firstOrFail();

        if ($siswa->isKeluar()) {
            return response()->json([
                'message' => 'Siswa berstatus keluar, tidak dapat membuat pengajuan pembayaran baru.',
            ], 422);
        }

        $tahun = AcademicYear::current();
        $requestedEnglish = collect($request->months)
            ->map(fn ($m) => self::MONTH_MAP[$m] ?? $m)
            ->filter(fn ($m) => array_key_exists($m, self::MONTH_LABELS_ID))
            ->unique()
            ->values()
            ->all();

        if (empty($requestedEnglish)) {
            return response()->json(['message' => 'Pilih minimal satu bulan yang valid.'], 422);
        }

        $alreadyPaid = Infak::where('siswa_id', $siswa->id)
            ->where('tahun', $tahun)
            ->whereIn('bulan', $requestedEnglish)
            ->pluck('bulan')
            ->all();

        if (!empty($alreadyPaid)) {
            $labels = array_map(fn ($m) => self::MONTH_LABELS_ID[$m] ?? $m, $alreadyPaid);
            return response()->json([
                'message' => 'Bulan berikut sudah tercatat lunas: ' . implode(', ', $labels),
            ], 422);
        }

        // Batalkan tagihan pending online sebelumnya untuk siswa ini agar QRIS tetap rapi dan tidak menumpuk
        if ($request->metode === 'online') {
            Pembayaran::where('siswa_id', $siswa->id)
                ->where('metode', 'online')
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);
        }

        $activeSameMonths = Pembayaran::where('siswa_id', $siswa->id)
            ->where('tahun', $tahun)
            ->whereIn('status', ['pending', 'waiting_offline'])
            ->get()
            ->filter(fn (Pembayaran $p) => count(array_intersect($p->bulan_list ?? [], $requestedEnglish)) > 0);

        if ($activeSameMonths->isNotEmpty()) {
            return response()->json([
                'message' => 'Sebagian bulan yang dipilih masih dalam proses pengajuan setoran aktif. Selesaikan atau batalkan pengajuan sebelumnya terlebih dahulu.',
            ], 422);
        }

        $countMonths = count($requestedEnglish);
        $nominalPerBulan = (int) $request->nominal_per_bulan;
        $totalNominal = $nominalPerBulan * $countMonths;
        $prefix = $request->metode === 'online' ? 'QRIS' : 'TUNAI';
        $kodeRef = $prefix . '-' . now()->format('ymd') . '-' . strtoupper(Str::random(5));

        $pembayaran = Pembayaran::create([
            'kode_referensi' => $kodeRef,
            'siswa_id' => $siswa->id,
            'kelas_id' => $siswa->kelas_id,
            'wali_user_id' => $user->id,
            'metode' => $request->metode,
            'channel' => $request->metode === 'online' ? 'qris_sandbox' : 'tunai_wali_kelas',
            'status' => $request->metode === 'online' ? 'pending' : 'waiting_offline',
            'bulan_list' => $requestedEnglish,
            'tahun' => $tahun,
            'nominal_per_bulan' => $nominalPerBulan,
            'total_nominal' => $totalNominal,
            'catatan' => $request->catatan,
        ]);

        if ($request->metode === 'online') {
            try {
                $charge = $gateway->createQrisCharge($pembayaran);
                $pembayaran->update([
                    'channel' => $charge['channel'] ?? 'qris_sandbox',
                    'qr_payload' => $charge['qr_payload'] ?? null,
                    'qr_image_url' => $charge['qr_image_url'] ?? null,
                    'gateway_transaction_id' => $charge['gateway_transaction_id'] ?? null,
                    'gateway_status' => $charge['gateway_status'] ?? 'pending',
                    'gateway_response' => $charge['gateway_response'] ?? null,
                    'expired_at' => $charge['expired_at'] ?? now()->addMinutes(15),
                ]);
            } catch (\Throwable $e) {
                $pembayaran->delete();
                return response()->json([
                    'message' => 'Gagal membuat tagihan QRIS: ' . $e->getMessage(),
                ], 422);
            }
        }

        $pembayaran->load(['siswa', 'kelas.wali_kelas']);

        return response()->json([
            'message' => $request->metode === 'online'
                ? 'Kode QRIS pembayaran berhasil dibuat. Silakan scan barcode untuk menyelesaikan pembayaran.'
                : 'Pengajuan bayar offline ke Wali Kelas berhasil dicatat. Silakan serahkan uang tunai saat bertemu Wali Kelas.',
            'pembayaran' => $this->formatPayment($pembayaran),
        ], 201);
    }

    public function checkStatus(Request $request, $id, PaymentGatewayService $gateway)
    {
        $user = $request->user();
        $pembayaran = Pembayaran::with(['siswa', 'kelas.wali_kelas', 'confirmedBy'])
            ->where('id', $id)
            ->when($user->role !== 'admin', fn ($q) => $q->where('wali_user_id', $user->id))
            ->firstOrFail();

        if ($pembayaran->status !== 'paid' && str_starts_with((string) $pembayaran->channel, 'midtrans')) {
            try {
                $pembayaran = $gateway->syncMidtransStatus($pembayaran);
            } catch (\Throwable) {
                // Kembalikan status terakhir dari DB jika koneksi gateway sedang lambat
            }
        }

        return response()->json([
            'pembayaran' => $this->formatPayment($pembayaran),
        ]);
    }

    public function simulateSandboxPay(Request $request, $id, PaymentGatewayService $gateway)
    {
        if (!config('services.payment.sandbox', true)) {
            return response()->json(['message' => 'Simulasi sandbox hanya tersedia pada mode sandbox.'], 403);
        }

        $user = $request->user();
        $pembayaran = Pembayaran::with(['siswa', 'kelas'])
            ->where('id', $id)
            ->when($user->role !== 'admin', fn ($q) => $q->where('wali_user_id', $user->id))
            ->firstOrFail();

        if ($pembayaran->metode !== 'online') {
            return response()->json(['message' => 'Hanya pembayaran online QRIS yang dapat disimulasikan lewat sandbox.'], 422);
        }

        if ($pembayaran->status === 'cancelled' || $pembayaran->status === 'expired') {
            return response()->json(['message' => 'Tagihan ini sudah dibatalkan atau kedaluwarsa. Silakan buat kode QR baru.'], 422);
        }

        $settled = $gateway->settlePayment($pembayaran, $user, 'online_qris');

        return response()->json([
            'message' => 'Pembayaran QRIS (Sandbox) berhasil diverifikasi dan infak tercatat LUNAS.',
            'pembayaran' => $this->formatPayment($settled),
        ]);
    }

    public function cancelPayment(Request $request, $id, PaymentGatewayService $gateway)
    {
        $user = $request->user();
        $pembayaran = Pembayaran::where('id', $id)
            ->when($user->role !== 'admin', fn ($q) => $q->where('wali_user_id', $user->id))
            ->firstOrFail();

        if ($pembayaran->status === 'paid') {
            return response()->json(['message' => 'Pembayaran yang sudah lunas tidak dapat dibatalkan.'], 422);
        }

        $gateway->cancelMidtransOrder($pembayaran);
        $pembayaran->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Pengajuan pembayaran dibatalkan.']);
    }

    public function riwayat(Request $request)
    {
        $user = $request->user();
        $siswaIds = Siswa::where('wali_user_id', $user->id)->pluck('id');

        $list = Pembayaran::with(['siswa', 'kelas.wali_kelas', 'confirmedBy'])
            ->where(function ($q) use ($user, $siswaIds) {
                $q->where('wali_user_id', $user->id)
                    ->orWhereIn('siswa_id', $siswaIds);
            })
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Pembayaran $p) => $this->formatPayment($p));

        return response()->json($list);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user = $request->user();
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'message' => 'Kata sandi saat ini tidak sesuai.',
            ], 422);
        }

        if (Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'Kata sandi baru harus berbeda dari kata sandi sebelumnya.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return response()->json([
            'message' => 'Kata sandi berhasil diperbarui.',
        ]);
    }

    private function formatPayment(Pembayaran $p): array
    {
        $bulanIndo = array_map(
            fn ($m) => self::MONTH_LABELS_ID[$m] ?? $m,
            $p->bulan_list ?? []
        );

        return [
            'id' => $p->id,
            'kode_referensi' => $p->kode_referensi,
            'siswa_id' => $p->siswa_id,
            'siswa_nama' => $p->siswa?->nama_lengkap ?? '-',
            'siswa_nis' => $p->siswa?->nis ?? '-',
            'kelas_id' => $p->kelas_id,
            'kelas_nama' => $p->kelas?->nama_kelas ?? '-',
            'wali_kelas_nama' => $p->kelas?->wali_kelas?->name ?? 'Tata Usaha / Admin',
            'metode' => $p->metode,
            'channel' => $p->channel,
            'status' => $p->status,
            'bulan_list' => $p->bulan_list ?? [],
            'bulan_labels' => $bulanIndo,
            'tahun' => $p->tahun,
            'nominal_per_bulan' => (int) $p->nominal_per_bulan,
            'total_nominal' => (int) $p->total_nominal,
            'qr_payload' => $p->qr_payload,
            'qr_image_url' => $p->qr_image_url,
            'gateway_status' => $p->gateway_status,
            'catatan' => $p->catatan,
            'expired_at' => $p->expired_at?->toIso8601String(),
            'paid_at' => $p->paid_at?->toIso8601String(),
            'confirmed_by_name' => $p->confirmedBy?->name,
            'created_at' => $p->created_at?->toIso8601String(),
        ];
    }
}

