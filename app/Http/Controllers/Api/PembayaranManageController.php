<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pembayaran;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;

class PembayaranManageController extends Controller
{
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

    public function index(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'guru'], true)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $query = Pembayaran::with(['siswa', 'kelas', 'waliUser', 'confirmedBy'])
            ->orderByDesc('created_at');

        if ($user->role === 'guru') {
            $kelasIds = Kelas::where('guru_id', $user->id)->pluck('id');
            $query->whereIn('kelas_id', $kelasIds);
        }

        if ($request->filled('status') && $request->query('status') !== 'all') {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('metode') && $request->query('metode') !== 'all') {
            $query->where('metode', $request->query('metode'));
        }

        $items = $query->get();

        // Summary counts scoped by role
        $baseScope = Pembayaran::query();
        if ($user->role === 'guru') {
            $kelasIds = Kelas::where('guru_id', $user->id)->pluck('id');
            $baseScope->whereIn('kelas_id', $kelasIds);
        }

        $summary = [
            'waiting_offline' => (clone $baseScope)->where('status', 'waiting_offline')->count(),
            'pending_online' => (clone $baseScope)->where('status', 'pending')->where('metode', 'online')->count(),
            'paid_online' => (clone $baseScope)->where('status', 'paid')->where('metode', 'online')->count(),
            'paid_offline' => (clone $baseScope)->where('status', 'paid')->where('metode', 'offline')->count(),
        ];

        $data = $items->map(function (Pembayaran $p) {
            return [
                'id' => $p->id,
                'kode_referensi' => $p->kode_referensi,
                'siswa_id' => $p->siswa_id,
                'siswa_nama' => $p->siswa?->nama_lengkap ?? '-',
                'siswa_nis' => $p->siswa?->nis ?? '-',
                'kelas_id' => $p->kelas_id,
                'kelas_nama' => $p->kelas?->nama_kelas ?? '-',
                'wali_nama' => $p->waliUser?->name ?? ($p->siswa?->nama_wali_1 ?: 'Wali Siswa'),
                'metode' => $p->metode,
                'channel' => $p->channel,
                'status' => $p->status,
                'bulan_list' => $p->bulan_list ?? [],
                'bulan_labels' => array_map(fn ($m) => self::MONTH_LABELS_ID[$m] ?? $m, $p->bulan_list ?? []),
                'tahun' => $p->tahun,
                'nominal_per_bulan' => (int) $p->nominal_per_bulan,
                'total_nominal' => (int) $p->total_nominal,
                'catatan' => $p->catatan,
                'paid_at' => $p->paid_at?->toIso8601String(),
                'confirmed_by_name' => $p->confirmedBy?->name,
                'created_at' => $p->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'summary' => $summary,
            'items' => $data,
        ]);
    }

    public function confirmOffline(Request $request, $id, PaymentGatewayService $gateway)
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'guru'], true)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $pembayaran = Pembayaran::with(['siswa', 'kelas'])->findOrFail($id);

        if ($user->role === 'guru') {
            $ownsClass = Kelas::where('id', $pembayaran->kelas_id)
                ->where('guru_id', $user->id)
                ->exists();
            if (!$ownsClass) {
                return response()->json(['message' => 'Anda tidak berwenang mengonfirmasi setoran kelas ini.'], 403);
            }
        }

        if ($pembayaran->status === 'paid') {
            return response()->json(['message' => 'Setoran ini sudah dikonfirmasi lunas sebelumnya.']);
        }

        if ($pembayaran->status === 'cancelled') {
            return response()->json(['message' => 'Setoran ini sudah dibatalkan.'], 422);
        }

        $settled = $gateway->settlePayment($pembayaran, $user, 'offline_wali');

        return response()->json([
            'message' => 'Setoran tunai berhasil dikonfirmasi LUNAS.',
            'pembayaran' => $settled,
        ]);
    }

    public function rejectOffline(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'guru'], true)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $pembayaran = Pembayaran::findOrFail($id);

        if ($user->role === 'guru') {
            $ownsClass = Kelas::where('id', $pembayaran->kelas_id)
                ->where('guru_id', $user->id)
                ->exists();
            if (!$ownsClass) {
                return response()->json(['message' => 'Anda tidak berwenang membatalkan setoran kelas ini.'], 403);
            }
        }

        if ($pembayaran->status === 'paid') {
            return response()->json(['message' => 'Pembayaran yang sudah lunas tidak dapat ditolak.'], 422);
        }

        $pembayaran->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Pengajuan setoran tunai dibatalkan.']);
    }
}

