# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- **Orang Tua / Wali Siswa (`wali`):** Memantau status tagihan infak bulanan anak, memilih cara pembayaran sesuai kebiasaan dan kesiapan digital (bayar online QRIS atau ajukan slip setor tunai ke wali kelas), serta mengunduh atau melihat riwayat setoran sah. Pengguna sering mengakses lewat ponsel Android dengan layar sentuh.
- **Bendahara / Admin Madrasah (`admin`):** Mencatat dan memantau pemasukan infak serta pengeluaran kas madrasah, menyetujui atau membatalkan setoran tunai/online, mengelola data siswa dan akun wali, mengelola daftar wali kelas, mengirim pengingat WhatsApp, dan mencetak laporan keuangan bulanan.
- **Guru / Wali Kelas (`guru`):** Memantau rekap infak siswa di kelas binaan, mencatat setoran tunai dari orang tua saat pertemuan offline, memverifikasi slip setoran offline kelasnya, dan mengirim pengingat WhatsApp kepada wali murid.

## Product Purpose

Sistem Administrasi & Pembayaran Infak Madrasah As-Sajjaad: memodernisasi pencatatan keuangan infak madrasah dari alur tunai tradisional menuju pembayaran digital bertahap tanpa memutus akses orang tua yang masih terbiasa membayar uang tunai secara langsung kepada wali kelas.

## Positioning

Transisi ganda yang jujur untuk realitas madrasah di masyarakat: menyatukan transaksi QRIS (sandbox untuk pengujian) dan pengajuan setoran tunai offline kepada wali kelas dalam satu buku besar yang konsisten, transparan, dan terhubung ke akun siswa yang sama.

## Operating Context

- Aplikasi web tunggal (Laravel 12 API + Vue 3 SPA) yang dipakai harian di kantor administrasi madrasah (desktop) dan lewat browser ponsel orang tua/wali siswa.
- Pembayaran online menggunakan simulasi EMVCo QRIS Sandbox sebelum diaktifkan ke penyedia payment gateway produksi.
- Pembayaran offline menghasilkan kode slip yang dibawa orang tua saat bertemu wali kelas untuk diverifikasi menjadi status lunas.
- Seluruh UI berbahasa Indonesia baku-ringkas dengan dukungan sentuh yang lega dan responsif di layar kecil.

## Capabilities and Constraints

- Tiga peran terotentikasi melalui Sanctum token: `admin`, `guru`, dan `wali` (login dengan email atau NIS siswa).
- Pencatatan infak bulanan per tahun ajaran aktif, pembayaran QRIS sandbox idempoten, verifikasi/pembatalan slip setoran offline, manajemen pengeluaran, laporan periode, dan antrean pesan WhatsApp.
- Kendala teknis wajib: Tailwind CSS v3 (`tailwind.config.js` + `postcss.config.js`), chart data via Chart.js, generator QR via `qrcode`, tanpa memodifikasi proyek asli `infak-madrasah`.

## Brand Commitments

- **Nama:** Madrasah As-Sajjaad — Sistem Administrasi Infak & Kas Madrasah.
- **Warna Non-Hijau:** Dilarang menggunakan tema warna hijau (yang lazim pada template AI bertema religi/pendidikan).
- **Arah Visual Dipilih Pengguna:** Arsitektural Slate & Oxford Cobalt (`#F2F4F8` latar sejuk, permukaan putih bersih bergaris presisi `1px`, teks tinta `#0F172A`, aksen utama Biru Kobalt `#1D4ED8` & aksen tembaga hangat untuk tunggakan/perlu tindak lanjut; tanpa latar krem hangat).
- **Kepadatan & Kenyamanan Sentuh Dipilih Pengguna:** Tata letak lebih lega dengan ruang napas ekstra, target sentuh ramah jari di ponsel, dan kartu ringkas yang jelas untuk seluruh 17 halaman.

## Evidence on Hand

- Basis kode berjalan lengkap dengan migrasi MySQL `infak_madrasah2`, data demo akun `admin`, `guru`, dan `wali`, serta endpoint REST `/api/*`.
- Tidak ada aset logo gambar resmi madrasah yang terpasang di repositori; identitas menggunakan logomark vektor presisi dan tipografi resmi.

## Product Principles

1. **Kejelasan Peran di Detik Pertama:** Wali murid melihat kewajiban bulan ini dan tombol bayar tanpa tersesat dalam menu admin; bendahara melihat saldo dan antrean verifikasi secara langsung.
2. **Kesetaraan Alur Online & Offline:** Bayar lewat QRIS maupun setor tunai lewat wali kelas tampil setara, jelas langkahnya, dan tidak saling mendiskriminasi.
3. **Ketenangan Finansial tanpa Ornamen AI:** Tanpa label eyebrow di atas judul, tanpa kotak border samping tebal (`border-l-4`), tanpa gradien dekoratif, dan angka rupiah/NIS selalu rata tabular.
4. **Ramah Sentuh di Layar Ponsel:** Tombol utama, input, pemilih metode bayar, dan daftar tagihan memiliki jarak lega minimal sesuai standar sentuh nyaman.

