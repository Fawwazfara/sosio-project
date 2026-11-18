<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Infak;
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

        $query->orderBy('nama_lengkap', 'asc');

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

            return $siswas->map(function($siswa) use ($months, $paidSets) {
                $payments = [];
                foreach ($months as $m) {
                    $hasPaid = isset($paidSets[$siswa->id][$this->mapBulanIndonesia($m)]);
                    $payments[] = $hasPaid;
                }
                return [
                    'id' => $siswa->id,
                    'name' => $siswa->nama_lengkap,
                    'fullName' => $siswa->nama_lengkap,
                    'nis' => $siswa->nis,
                    'blok' => $siswa->blok ?: 'N/A',
                    'waliName' => $siswa->nama_wali_1 ?: 'Wali Siswa',
                    'phone' => $siswa->wa_wali_1 ? '+62 ' . ltrim($siswa->wa_wali_1, '0') : '+62 812-0000-0000',
                    'kelas' => $siswa->kelas ? $siswa->kelas->nama_kelas : '',
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
        $siswa = Siswa::with('waliUser')->findOrFail($id);
        return response()->json($siswa);
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

    public function destroy($id)
    {
        $siswa = Siswa::findOrFail($id);
        $siswa->delete();
        
        return response()->json(['message' => 'Siswa deleted successfully']);
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

        $siswas = $query->get();
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

        $fileName = 'backup_siswa_' . date('Y_m_d_His') . '.csv';
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('No', 'Nama Lengkap', 'NIS', 'Kelas', 'Wali', 'Telepon');
        foreach ($months as $m) {
            $columns[] = $m;
        }

        $callback = function() use($siswas, $columns, $months, $paidSets, $englishMonths) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $i = 1;
            foreach ($siswas as $siswa) {
                $row = [
                    $i++,
                    $siswa->nama_lengkap,
                    $siswa->nis,
                    $siswa->kelas ? $siswa->kelas->nama_kelas : '',
                    $siswa->nama_wali_1 ?: '-',
                    $siswa->wa_wali_1 ?: '-'
                ];
                
                foreach ($months as $idx => $m) {
                    $hasPaid = isset($paidSets[$siswa->id][$englishMonths[$idx]]);
                    $row[] = $hasPaid ? 'Lunas' : 'Belum';
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

        $siswas = Siswa::where('kelas_id', $kelas_id)->orderBy('nama_lengkap', 'asc')->get();

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
}
