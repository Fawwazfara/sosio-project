<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Infak;
use App\Models\LogAktivitas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tahunAjaran = \App\AcademicYear::current();
        $perPage = (int) $request->query('per_page', 0); // 0 = all (backward compatible), >0 = paginate

        $query = Siswa::with(['kelas', 'waliUser', 'infaks' => function ($q) use ($tahunAjaran) {
            $q->where('tahun', $tahunAjaran);
        }]);

        if ($user->role === 'guru') {
            $kelasIds = Kelas::where('guru_id', $user->id)->pluck('id');
            $query->whereIn('kelas_id', $kelasIds);
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->query('kelas_id'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where('nama_lengkap', 'like', "%{$search}%");
        }

        $query->orderByRaw("CASE WHEN status = 'keluar' THEN 1 ELSE 0 END ASC")
              ->orderBy('nama_lengkap', 'asc');

        $transformCollection = function ($siswas) {
            $months = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
            $paidSets = [];
            foreach ($siswas as $s) {
                $set = [];
                foreach ($s->infaks as $infak) {
                    $set[$infak->bulan] = true;
                }
                $paidSets[$s->id] = $set;
            }

            $lastInfaks = Infak::whereIn('siswa_id', $siswas->pluck('id'))
                ->orderBy('tanggal_bayar', 'desc')
                ->orderBy('id', 'desc')
                ->get(['siswa_id', 'bulan', 'tahun', 'tanggal_bayar', 'jumlah'])
                ->unique('siswa_id')
                ->keyBy('siswa_id');

            return $siswas->map(function($siswa) use ($months, $paidSets, $lastInfaks) {
                $payments = [];
                foreach ($months as $m) {
                    $hasPaid = isset($paidSets[$siswa->id][$this->mapBulanIndonesia($m)]);
                    $payments[] = $hasPaid;
                }
                $lastInfak = $lastInfaks->get($siswa->id);
                $tanggalKeluar = $siswa->tanggal_keluar
                    ? Carbon::parse($siswa->tanggal_keluar)->format('Y-m-d')
                    : null;

                return [
                    'id' => $siswa->id,
                    'name' => $siswa->nama_lengkap,
                    'fullName' => $siswa->nama_lengkap,
                    'nis' => $siswa->nis,
                    'blok' => $siswa->blok ?: 'N/A',
                    'waliName' => $siswa->nama_wali_1 ?: 'Wali Siswa',
                    'phone' => $siswa->wa_wali_1 ? '+62 ' . ltrim($siswa->wa_wali_1, '0') : '+62 812-0000-0000',
                    'kelas' => $siswa->kelas ? $siswa->kelas->nama_kelas : '',
                    'status' => $siswa->status ?: 'aktif',
                    'tanggal_keluar' => $tanggalKeluar,
                    'keluar_label' => $this->formatBulanTahunIndo($tanggalKeluar),
                    'has_infak' => $lastInfak !== null,
                    'last_infak' => $this->formatLastInfak($lastInfak),
                    'wali_account' => $siswa->waliUser ? [
                        'id' => $siswa->waliUser->id,
                        'name' => $siswa->waliUser->name,
                        'email' => $siswa->waliUser->email,
                    ] : null,
                    'payments' => $payments
                ];
            });
        };

        if ($perPage > 0) {
            $paginator = $query->paginate($perPage);
            $transformed = $transformCollection($paginator->getCollection());
            $paginator->setCollection($transformed);
            return response()->json($paginator);
        }

        return response()->json($transformCollection($query->get()));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $siswa = Siswa::create([
            'nama_lengkap' => $request->nama_lengkap,
            'kelas_id' => $request->kelas_id,
            'status' => 'aktif',
            'nis' => $request->nis ?: (string) rand(1000000, 9999999),
            'jenis_kelamin' => $request->jenis_kelamin ?: 'L',
            'alamat' => $request->alamat,
            'blok' => $request->blok,
            'nama_wali_1' => $request->nama_wali_1,
            'wa_wali_1' => $request->wa_wali_1,
            'nama_wali_2' => $request->nama_wali_2,
            'wa_wali_2' => $request->wa_wali_2,
        ]);

        if ($request->boolean('create_wali_account')) {
            $email = strtolower(trim($request->input('wali_email') ?: ($siswa->nis . '@wali.assajjad.com')));
            $password = $request->input('wali_password') ?: 'wali123';
            $waliUser = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $siswa->nama_wali_1 ?: ('Wali ' . $siswa->nama_lengkap),
                    'password' => Hash::make($password),
                    'role' => 'wali',
                ]
            );
            $siswa->update(['wali_user_id' => $waliUser->id]);
        }

        return response()->json(['message' => 'Siswa created successfully', 'data' => $siswa->load('waliUser')], 201);
    }

    public function show($id)
    {
        $siswa = Siswa::with(['kelas', 'waliUser'])->findOrFail($id);
        $lastInfak = Infak::where('siswa_id', $siswa->id)
            ->orderBy('tanggal_bayar', 'desc')
            ->orderBy('id', 'desc')
            ->first(['siswa_id', 'bulan', 'tahun', 'tanggal_bayar', 'jumlah']);

        $data = $siswa->toArray();
        $data['status'] = $siswa->status ?: 'aktif';
        $data['tanggal_keluar'] = $siswa->tanggal_keluar
            ? Carbon::parse($siswa->tanggal_keluar)->format('Y-m-d')
            : null;
        $data['keluar_label'] = $this->formatBulanTahunIndo($data['tanggal_keluar']);
        $data['has_infak'] = $lastInfak !== null;
        $data['last_infak'] = $this->formatLastInfak($lastInfak);

        return response()->json($data);
    }

    public function provisionWaliAccount(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Hanya admin yang dapat mengelola akun wali siswa.'], 403);
        }

        $siswa = Siswa::with('waliUser')->findOrFail($id);

        $request->validate([
            'email' => 'nullable|email',
            'password' => 'nullable|string|min:6',
        ]);

        $email = strtolower(trim($request->input('email') ?: ($siswa->waliUser?->email ?? ($siswa->nis . '@wali.assajjad.com'))));
        $plainPassword = $request->input('password') ?: 'wali123';

        if ($siswa->waliUser) {
            $existingOther = User::where('email', $email)->where('id', '!=', $siswa->waliUser->id)->exists();
            if ($existingOther) {
                return response()->json(['message' => 'Email sudah dipakai oleh akun lain.'], 422);
            }

            $siswa->waliUser->update([
                'name' => $siswa->nama_wali_1 ?: $siswa->waliUser->name,
                'email' => $email,
                'password' => Hash::make($plainPassword),
            ]);
            $waliUser = $siswa->waliUser->fresh();
        } else {
            $waliUser = User::where('email', $email)->first();
            if (!$waliUser) {
                $waliUser = User::create([
                    'name' => $siswa->nama_wali_1 ?: ('Wali ' . $siswa->nama_lengkap),
                    'email' => $email,
                    'password' => Hash::make($plainPassword),
                    'role' => 'wali',
                ]);
            }
            $siswa->update(['wali_user_id' => $waliUser->id]);
        }

        return response()->json([
            'message' => 'Akun wali siswa siap digunakan.',
            'account' => [
                'id' => $waliUser->id,
                'name' => $waliUser->name,
                'email' => $waliUser->email,
                'nis' => $siswa->nis,
                'password' => $plainPassword,
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);
        $request->validate([
            'nama_lengkap' => 'required|string',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $siswa->update([
            'nama_lengkap' => $request->nama_lengkap,
            'kelas_id' => $request->kelas_id,
            'jenis_kelamin' => $request->jenis_kelamin ?: $siswa->jenis_kelamin,
            'alamat' => $request->alamat,
            'blok' => $request->blok,
            'nama_wali_1' => $request->nama_wali_1,
            'wa_wali_1' => $request->wa_wali_1,
            'nama_wali_2' => $request->nama_wali_2,
            'wa_wali_2' => $request->wa_wali_2,
        ]);

        return response()->json(['message' => 'Siswa updated successfully', 'data' => $siswa], 200);
    }

    public function markKeluar(Request $request, $id)
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json(['message' => 'Hanya admin yang dapat menandai siswa keluar.'], 403);
        }

        $request->validate([
            'tanggal_keluar' => 'nullable|date',
        ]);

        $siswa = Siswa::with('kelas')->findOrFail($id);
        $tanggalKeluar = $request->input('tanggal_keluar')
            ? Carbon::parse($request->input('tanggal_keluar'))->toDateString()
            : now()->toDateString();

        $siswa->update([
            'status' => 'keluar',
            'tanggal_keluar' => $tanggalKeluar,
        ]);

        $kelasName = $siswa->kelas ? $siswa->kelas->nama_kelas : '-';
        $keluarLabel = $this->formatBulanTahunIndo($tanggalKeluar) ?? $tanggalKeluar;

        LogAktivitas::create([
            'type' => 'system',
            'title' => 'Siswa Ditandai Keluar',
            'description' => "{$siswa->nama_lengkap} ({$kelasName}) ditandai keluar pada {$keluarLabel} ({$tanggalKeluar}). Riwayat infak tetap dipertahankan.",
            'amount' => null,
        ]);

        return response()->json([
            'message' => 'Siswa ditandai keluar. Riwayat infak, laporan, dan grafik tetap terjaga.',
            'data' => [
                'id' => $siswa->id,
                'status' => 'keluar',
                'tanggal_keluar' => $tanggalKeluar,
                'keluar_label' => $keluarLabel,
            ],
        ]);
    }

    public function aktifkan(Request $request, $id)
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json(['message' => 'Hanya admin yang dapat mengaktifkan kembali siswa.'], 403);
        }

        $siswa = Siswa::with('kelas')->findOrFail($id);
        $siswa->update([
            'status' => 'aktif',
            'tanggal_keluar' => null,
        ]);

        $kelasName = $siswa->kelas ? $siswa->kelas->nama_kelas : '-';

        LogAktivitas::create([
            'type' => 'system',
            'title' => 'Siswa Diaktifkan Kembali',
            'description' => "{$siswa->nama_lengkap} ({$kelasName}) diaktifkan kembali.",
            'amount' => null,
        ]);

        return response()->json([
            'message' => 'Siswa berhasil diaktifkan kembali.',
            'data' => [
                'id' => $siswa->id,
                'status' => 'aktif',
                'tanggal_keluar' => null,
                'keluar_label' => null,
            ],
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json(['message' => 'Hanya admin yang dapat menghapus data siswa.'], 403);
        }

        $siswa = Siswa::with('kelas')->findOrFail($id);
        $hasInfak = $siswa->infaks()->exists() || $siswa->pembayarans()->where('status', 'paid')->exists();

        // Jangan hapus permanen siswa yang sudah punya riwayat infak,
        // agar laporan bulan-bulan sebelumnya dan grafik arus kas tidak berubah.
        if ($hasInfak) {
            if ($request->boolean('permanent')) {
                return response()->json([
                    'message' => 'Siswa yang sudah memiliki riwayat infak tidak dapat dihapus permanen agar laporan dan grafik tetap akurat. Tandai siswa sebagai keluar.',
                ], 422);
            }

            $tanggalKeluar = $siswa->tanggal_keluar
                ? Carbon::parse($siswa->tanggal_keluar)->toDateString()
                : now()->toDateString();

            $siswa->update([
                'status' => 'keluar',
                'tanggal_keluar' => $tanggalKeluar,
            ]);

            $kelasName = $siswa->kelas ? $siswa->kelas->nama_kelas : '-';
            LogAktivitas::create([
                'type' => 'system',
                'title' => 'Siswa Ditandai Keluar',
                'description' => "{$siswa->nama_lengkap} ({$kelasName}) ditandai keluar pada {$tanggalKeluar}. Riwayat infak tetap dipertahankan.",
                'amount' => null,
            ]);

            return response()->json([
                'message' => 'Siswa ditandai keluar agar riwayat infak, laporan, dan grafik tetap terjaga.',
                'action' => 'keluar',
                'data' => [
                    'id' => $siswa->id,
                    'status' => 'keluar',
                    'tanggal_keluar' => $tanggalKeluar,
                    'keluar_label' => $this->formatBulanTahunIndo($tanggalKeluar),
                ],
            ]);
        }

        $nama = $siswa->nama_lengkap;
        $kelasName = $siswa->kelas ? $siswa->kelas->nama_kelas : '-';
        $siswa->delete();

        LogAktivitas::create([
            'type' => 'system',
            'title' => 'Hapus Data Siswa',
            'description' => "Data siswa {$nama} ({$kelasName}) dihapus permanen (belum ada riwayat infak).",
            'amount' => null,
        ]);

        return response()->json(['message' => 'Siswa deleted successfully', 'action' => 'deleted']);
    }

    public function exportExcel(Request $request)
    {
        $user = $request->user();
        $tahunAjaran = \App\AcademicYear::current();
        $query = Siswa::with(['kelas', 'waliUser', 'infaks' => function ($q) use ($tahunAjaran) {
            $q->where('tahun', $tahunAjaran);
        }]);

        if ($user->role === 'guru') {
            $kelasIds = Kelas::where('guru_id', $user->id)->pluck('id');
            $query->whereIn('kelas_id', $kelasIds);
        }

        $siswas = $query->orderByRaw("CASE WHEN status = 'keluar' THEN 1 ELSE 0 END ASC")
            ->orderBy('nama_lengkap', 'asc')
            ->get();
        $months = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
        $englishMonths = array_map([$this, 'mapBulanIndonesia'], $months);

        $paidSets = [];
        foreach ($siswas as $s) {
            $set = [];
            foreach ($s->infaks as $infak) {
                $set[$infak->bulan] = true;
            }
            $paidSets[$s->id] = $set;
        }

        $lastInfaks = Infak::whereIn('siswa_id', $siswas->pluck('id'))
            ->orderBy('tanggal_bayar', 'desc')
            ->orderBy('id', 'desc')
            ->get(['siswa_id', 'bulan', 'tahun', 'tanggal_bayar', 'jumlah'])
            ->unique('siswa_id')
            ->keyBy('siswa_id');

        $fileName = 'backup_siswa_' . date('Y_m_d_His') . '.csv';
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('No', 'Nama Lengkap', 'NIS', 'Kelas', 'Status', 'Tanggal Keluar', 'Infak Terakhir', 'Wali', 'Telepon');
        foreach ($months as $m) {
            $columns[] = $m;
        }

        $callback = function() use($siswas, $columns, $months, $paidSets, $englishMonths, $lastInfaks) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $i = 1;
            foreach ($siswas as $siswa) {
                $last = $this->formatLastInfak($lastInfaks->get($siswa->id));
                $lastText = $last ? ($last['bulan_indo'] . ' ' . $last['kalender_tahun'] . ' (' . $last['tanggal_bayar'] . ')') : '-';

                $row = [
                    $i++,
                    $siswa->nama_lengkap,
                    $siswa->nis,
                    $siswa->kelas ? $siswa->kelas->nama_kelas : '',
                    $siswa->status === 'keluar' ? 'Keluar' : 'Aktif',
                    $siswa->tanggal_keluar ? Carbon::parse($siswa->tanggal_keluar)->format('Y-m-d') : '-',
                    $lastText,
                    $siswa->nama_wali_1 ?: '-',
                    $siswa->wa_wali_1 ?: '-'
                ];
                
                foreach ($months as $idx => $m) {
                    $hasPaid = isset($paidSets[$siswa->id][$englishMonths[$idx]]);
                    $row[] = $hasPaid ? 'Lunas' : ($siswa->status === 'keluar' ? '-' : 'Belum');
                }
                
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getUnpaidStudents(Request $request, $kelas_id)
    {
        $bulan = $request->query('bulan');
        $tahun = \App\AcademicYear::current();

        if (!$bulan) {
            return response()->json(['error' => 'Bulan is required'], 400);
        }

        $bulanFull = $this->mapBulanIndonesia($bulan);

        $siswas = Siswa::where('kelas_id', $kelas_id)->aktif()->orderBy('nama_lengkap', 'asc')->get();

        $paidStudentIds = Infak::where('kelas_id', $kelas_id)
            ->where('bulan', $bulanFull)
            ->where('tahun', $tahun)
            ->pluck('siswa_id')
            ->toArray();

        $unpaidSiswas = $siswas->reject(function ($siswa) use ($paidStudentIds) {
            return in_array($siswa->id, $paidStudentIds);
        })->values();

        $result = $unpaidSiswas->map(function($siswa) {
            return [
                'id' => $siswa->id,
                'fullName' => $siswa->nama_lengkap,
                'nis' => $siswa->nis,
            ];
        });

        return response()->json($result);
    }

    private function mapBulanIndonesia($shortMonth)
    {
        $map = [
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
            'Jun' => 'June'
        ];

        return $map[$shortMonth] ?? $shortMonth;
    }

    private function mapEnglishToIndo(?string $englishMonth): string
    {
        $map = [
            'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
            'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember',
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
            'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
        ];

        return $map[$englishMonth ?? ''] ?? (string) $englishMonth;
    }

    private function formatBulanTahunIndo(?string $dateStr): ?string
    {
        if (!$dateStr) {
            return null;
        }
        $date = Carbon::parse($dateStr);
        $shortMonths = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        return ($shortMonths[$date->month] ?? $date->format('M')) . ' ' . $date->year;
    }

    private function formatLastInfak(?Infak $infak): ?array
    {
        if (!$infak) {
            return null;
        }

        $janToJun = ['January', 'February', 'March', 'April', 'May', 'June'];
        $kalenderTahun = in_array($infak->bulan, $janToJun, true)
            ? ((int) $infak->tahun + 1)
            : (int) $infak->tahun;

        return [
            'bulan' => $infak->bulan,
            'bulan_indo' => $this->mapEnglishToIndo($infak->bulan),
            'tahun' => (int) $infak->tahun,
            'kalender_tahun' => $kalenderTahun,
            'tanggal_bayar' => substr((string) $infak->tanggal_bayar, 0, 10),
            'jumlah' => (int) $infak->jumlah,
        ];
    }
}
