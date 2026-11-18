<?php

namespace Database\Seeders;

use App\AcademicYear;
use App\Models\Infak;
use App\Models\Kelas;
use App\Models\LogAktivitas;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $academicYear = AcademicYear::current();

        // 1. Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'fawwazfara11@assajjad.com'],
            [
                'name' => 'Fawwaz Fara',
                'password' => Hash::make('fwaz000165'),
                'role' => 'admin',
            ]
        );

        // 2. Create Guru (Ibu Erawati)
        $erawati = User::firstOrCreate(
            ['email' => 'erawati@assajjad.com'],
            [
                'name' => 'Ibu Erawati',
                'password' => Hash::make('era123'),
                'role' => 'guru',
            ]
        );

        // 3. Create List of Classes
        $kelasNames = [
            'Bambim', 'TK', 'Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4A', 'Kelas 4B',
            'Kelas 5', 'Kelas 6', 'Kelas 7', 'Kelas 8', 'Kelas 9', 'Kelas Ulya',
        ];

        $kelasList = [];
        foreach ($kelasNames as $nama) {
            $guruId = in_array($nama, ['Kelas 1', 'Kelas 2'], true) ? $erawati->id : null;
            $kelasList[$nama] = Kelas::firstOrCreate(
                ['nama_kelas' => $nama],
                ['guru_id' => $guruId]
            );
        }

        // 4. Create Demo Wali Accounts (Orang Tua / Wali Siswa)
        $waliDemo1 = User::firstOrCreate(
            ['email' => 'wali1@assajjad.com'],
            [
                'name' => 'Bapak Ahmad Hidayat',
                'password' => Hash::make('wali123'),
                'role' => 'wali',
            ]
        );

        $waliDemo2 = User::firstOrCreate(
            ['email' => 'wali2@assajjad.com'],
            [
                'name' => 'Ibu Siti Aminah',
                'password' => Hash::make('wali123'),
                'role' => 'wali',
            ]
        );

        // Siswa utama untuk demo akun wali (punya 2 anak agar fitur multi-anak bisa didemokan)
        $anak1 = Siswa::firstOrCreate(
            ['nis' => '2026001'],
            [
                'nama_lengkap' => 'Muhammad Zaki Hidayat',
                'kelas_id' => $kelasList['Kelas 1']->id,
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Pesantren No. 14, Sukamaju',
                'blok' => 'Blok A-03',
                'nama_wali_1' => 'Bapak Ahmad Hidayat',
                'wa_wali_1' => '081234567801',
                'wali_user_id' => $waliDemo1->id,
            ]
        );

        $anak2 = Siswa::firstOrCreate(
            ['nis' => '2026002'],
            [
                'nama_lengkap' => 'Aisyah Rahma Hidayat',
                'kelas_id' => $kelasList['Kelas 2']->id,
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Pesantren No. 14, Sukamaju',
                'blok' => 'Blok B-02',
                'nama_wali_1' => 'Bapak Ahmad Hidayat',
                'wa_wali_1' => '081234567801',
                'wali_user_id' => $waliDemo1->id,
            ]
        );

        $anak3 = Siswa::firstOrCreate(
            ['nis' => '2026003'],
            [
                'nama_lengkap' => 'Naufal Hakim',
                'kelas_id' => $kelasList['Kelas 1']->id,
                'jenis_kelamin' => 'L',
                'alamat' => 'Kp. Cikole RT 02/04',
                'blok' => 'Non-Asrama',
                'nama_wali_1' => 'Ibu Siti Aminah',
                'wa_wali_1' => '081234567802',
                'wali_user_id' => $waliDemo2->id,
            ]
        );

        // Seed infak Juli - September untuk anak1 (biar Oktober-Juni belum lunas dan siap didemo bayar!)
        foreach (['July', 'August', 'September'] as $idx => $bulan) {
            Infak::firstOrCreate(
                [
                    'siswa_id' => $anak1->id,
                    'tahun' => $academicYear,
                    'bulan' => $bulan,
                ],
                [
                    'kelas_id' => $anak1->kelas_id,
                    'jumlah' => 25000,
                    'tanggal_bayar' => now()->subMonths(3 - $idx)->startOfMonth()->addDays(5)->toDateString(),
                    'keterangan' => 'Tunai via Wali Kelas',
                    'metode_bayar' => 'offline_wali',
                ]
            );
        }

        // Seed 1 transaksi offline menunggu konfirmasi wali kelas (anak3)
        Pembayaran::firstOrCreate(
            ['kode_referensi' => 'TUNAI-DEMO-01'],
            [
                'siswa_id' => $anak3->id,
                'kelas_id' => $anak3->kelas_id,
                'wali_user_id' => $waliDemo2->id,
                'metode' => 'offline',
                'channel' => 'tunai_wali_kelas',
                'status' => 'waiting_offline',
                'bulan_list' => ['October', 'November'],
                'tahun' => $academicYear,
                'nominal_per_bulan' => 25000,
                'total_nominal' => 50000,
                'catatan' => 'Titip uang tunai saat mengantar sekolah besok pagi.',
            ]
        );

        // Seed siswa lain di berbagai kelas
        $sampleNames = [
            'Rayyan Al-Farizi', 'Khansa Azzahra', 'Fathan Mubarak',
            'Zahra Kamila', 'Ibrahim Malik', 'Salma Nuraini',
            'Rafi Pratama', 'Hana Humaira', 'Dzaki Ramadhan',
            'Bilqis Shofia', 'Ammar Yasir', 'Syifa Maulida',
        ];

        $regularClasses = array_filter($kelasList, fn ($k, $nama) => $nama !== 'Bambim', ARRAY_FILTER_USE_BOTH);
        $nameIdx = 0;

        foreach ($regularClasses as $nama => $kelas) {
            for ($i = 1; $i <= 3; $i++) {
                $nis = '2026' . str_pad((string) ($kelas->id * 10 + $i), 3, '0', STR_PAD_LEFT);
                $studentName = $sampleNames[$nameIdx % count($sampleNames)] . ($nameIdx >= count($sampleNames) ? " ({$nama})" : '');
                $nameIdx++;

                $siswa = Siswa::firstOrCreate(
                    ['nis' => $nis],
                    [
                        'nama_lengkap' => $studentName,
                        'kelas_id' => $kelas->id,
                        'jenis_kelamin' => ($i % 2 === 0) ? 'P' : 'L',
                        'blok' => 'Blok ' . chr(65 + ($i % 3)),
                        'nama_wali_1' => 'Wali ' . $studentName,
                        'wa_wali_1' => '0813' . rand(10000000, 99999999),
                    ]
                );

                // Buat akun wali otomatis untuk sebagian siswa
                if (!$siswa->wali_user_id) {
                    $wUser = User::firstOrCreate(
                        ['email' => $nis . '@wali.assajjad.com'],
                        [
                            'name' => $siswa->nama_wali_1,
                            'password' => Hash::make('wali123'),
                            'role' => 'wali',
                        ]
                    );
                    $siswa->update(['wali_user_id' => $wUser->id]);
                }

                // Simulasi pembayaran Juli - Oktober
                foreach (['July', 'August', 'September', 'October'] as $mIdx => $b) {
                    if (($siswa->id + $mIdx) % 3 !== 0) {
                        Infak::firstOrCreate(
                            [
                                'siswa_id' => $siswa->id,
                                'tahun' => $academicYear,
                                'bulan' => $b,
                            ],
                            [
                                'kelas_id' => $kelas->id,
                                'jumlah' => 25000,
                                'tanggal_bayar' => now()->subDays(rand(2, 45))->toDateString(),
                                'keterangan' => 'Lunas via Seeder',
                                'metode_bayar' => $mIdx % 2 === 0 ? 'online_qris' : 'tunai_tu',
                            ]
                        );
                    }
                }
            }
        }

        // Pengeluaran sample
        if (Pengeluaran::count() === 0) {
            Pengeluaran::create([
                'keterangan' => 'Pengadaan Alat Kebersihan Kelas',
                'jumlah' => 150000,
                'tanggal' => now()->subDays(4)->toDateString(),
                'kategori' => 'Operasional',
            ]);

            Pengeluaran::create([
                'keterangan' => 'Cetak Modul Mengaji & Iqra',
                'jumlah' => 300000,
                'tanggal' => now()->subDays(9)->toDateString(),
                'kategori' => 'Kegiatan',
            ]);
        }

        if (LogAktivitas::count() === 0) {
            LogAktivitas::create([
                'type' => 'system',
                'title' => 'Sistem Portal Wali Aktif',
                'description' => 'Pembayaran QRIS (Sandbox) dan Konfirmasi Setoran Tunai Wali Kelas diaktifkan.',
                'amount' => null,
            ]);
        }
    }
}
