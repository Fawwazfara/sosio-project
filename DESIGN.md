---
name: Madrasah As-Sajjaad · Sistem Infak & Kas
description: Sistem desain arsitektural Slate & Oxford Cobalt (non-hijau, non-krem) untuk manajemen infak madrasah, portal wali QRIS/tunai, dan buku besar keuangan.
colors:
  canvas-slate: "#F2F4F8"
  surface-white: "#FFFFFF"
  surface-inset: "#F8FAFC"
  surface-rail: "#EAEFF6"
  surface-hover: "#F1F5F9"
  ink-primary: "#0F172A"
  ink-strong: "#334155"
  ink-secondary: "#475569"
  ink-muted: "#64748B"
  ink-subtle: "#94A3B8"
  hairline: "#DDE3EC"
  hairline-soft: "#E6ECF5"
  hairline-grid: "#E2E8F0"
  border-control: "#CBD5E1"
  oxford-navy: "#163A70"
  oxford-navy-deep: "#0E2952"
  oxford-navy-inset: "#0F2A54"
  oxford-navy-hover: "#102B54"
  oxford-navy-active: "#0B1F3E"
  cobalt-primary: "#1D4ED8"
  cobalt-hover: "#1E40AF"
  cobalt-dark: "#1E3A8A"
  cobalt-soft: "#EFF6FF"
  cobalt-tint: "#DBEAFE"
  cobalt-border: "#BFDBFE"
  cobalt-light: "#93C5FD"
  copper-offline: "#C2410C"
  copper-hover: "#9A3412"
  copper-soft: "#FFF7ED"
  copper-border: "#FED7AA"
  copper-tint: "#FFEDD5"
  ochre-pending: "#B45309"
  ochre-dark: "#92400E"
  ochre-deep: "#78350F"
  ochre-soft: "#FFFBEB"
  ochre-border: "#FDE68A"
  crimson-error: "#9F1239"
  crimson-dark: "#881337"
  crimson-soft: "#FFF1F2"
  crimson-hover: "#FFE4E6"
  crimson-border: "#FECDD3"
typography:
  display:
    fontFamily: "'Onest', system-ui, -apple-system, sans-serif"
    fontSize: "2rem"
    fontWeight: 800
    lineHeight: 1.2
    letterSpacing: "-0.025em"
  headline:
    fontFamily: "'Onest', system-ui, -apple-system, sans-serif"
    fontSize: "1.625rem"
    fontWeight: 700
    lineHeight: 1.3
    letterSpacing: "-0.02em"
  title:
    fontFamily: "'Onest', system-ui, -apple-system, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 700
    lineHeight: 1.4
    letterSpacing: "-0.015em"
  body:
    fontFamily: "'Onest', system-ui, -apple-system, sans-serif"
    fontSize: "0.9375rem"
    fontWeight: 400
    lineHeight: 1.55
    letterSpacing: "0em"
  label:
    fontFamily: "'Onest', system-ui, -apple-system, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 600
    lineHeight: 1.5
    letterSpacing: "0em"
  mono:
    fontFamily: "'JetBrains Mono', monospace"
    fontSize: "0.875rem"
    fontWeight: 600
    lineHeight: 1.4
  icon:
    fontFamily: "'Material Symbols Outlined'"
    fontSize: "1.25rem"
    fontWeight: 400
    lineHeight: 1
rounded:
  sm: "6px"
  md: "8px"
  lg: "10px"
  xl: "12px"
  2xl: "14px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "32px"
  2xl: "48px"
components:
  button-primary:
    backgroundColor: "{colors.cobalt-primary}"
    textColor: "{colors.surface-white}"
    rounded: "{rounded.md}"
    padding: "10px 16px"
    height: "44px"
  button-primary-hover:
    backgroundColor: "{colors.cobalt-hover}"
  button-navy:
    backgroundColor: "{colors.oxford-navy}"
    textColor: "{colors.surface-white}"
    rounded: "{rounded.md}"
    padding: "10px 16px"
    height: "44px"
  button-secondary:
    backgroundColor: "{colors.surface-white}"
    textColor: "{colors.ink-primary}"
    rounded: "{rounded.md}"
    padding: "10px 16px"
    height: "44px"
  input-field:
    backgroundColor: "{colors.surface-white}"
    textColor: "{colors.ink-primary}"
    rounded: "{rounded.md}"
    padding: "10px 14px"
    height: "44px"
  panel-sheet:
    backgroundColor: "{colors.surface-white}"
    rounded: "{rounded.xl}"
    padding: "24px"
---

## Overview

Sistem Desain **Arsitektural Slate & Oxford Cobalt** untuk aplikasi web Madrasah As-Sajjaad (`infak-madrasah2`). Dirancang dalam mode *Operate* yang tenang, jujur, ramah sentuh di perangkat mobile (`min-height: 44px`), dan bebas dari warna hijau maupun estetika template AI standar.

## Colors

- **Latar & Kertas Kerja:** `#F2F4F8` sebagai kanvas utama, `#FFFFFF` untuk lembar konten, dan `#F8FAFC` untuk area inset atau hover baris.
- **Identitas & Aksi Utama:** `#163A70` (*Oxford Navy*) pada navigasi dan angka saldo utama; `#1D4ED8` (*Cobalt Primary*) untuk tombol aksi utama, status lunas, dan QRIS online.
- **Semantik Keuangan Non-Hijau:**
  - *Menunggu Setoran Tunai / Pending:* `#FFFBEB` / `#FDE68A` / `#B45309`.
  - *Aksi Tunai Wali Kelas:* `#C2410C` / `#FFF7ED`.
  - *Pengeluaran Kas / Tunggakan / Hapus:* `#9F1239` / `#FFF1F2` / `#FECDD3`.

## Typography

- **UI Utama:** `Onest` (`15px` dasar, minimum fungsional `12px` pada label dan badge agar terbaca jelas di layar ponsel ber-DPI tinggi).
- **Angka & Kode Referensi:** `JetBrains Mono` dengan `font-variant-numeric: tabular-nums` khusus untuk nominal rupiah (`Rp ...`), NIS siswa, NMID, dan kode slip.
- **Hierarki Judul:** Setiap `h1`/`h2`/`h3` berdiri langsung tanpa label *eyebrow/kicker* di atasnya, dengan `text-wrap: balance` pada judul dan `text-wrap: pretty` pada paragraf.

## Layout

- Kontainer utama memakai kelas `.ui-page` (`max-w-6xl`, padding horizontal responsif `16px–32px`, jarak vertikal antar-seksi `24px–32px`).
- Pada layar ponsel, navigasi bawah Portal Wali dan Panel Guru memiliki tinggi lega (`72px`) dengan area sentuh minimal `44×44px`.

## Elevation & Depth

- Kedalaman mengandalkan pemisahan tonal (`#F2F4F8` vs `#FFFFFF`) dan garis tepi presisi `1px solid #DDE3EC`, tanpa bayangan tebal difus di setiap baris kartu maupun border kiri tebal (`border-l-4`).

## Shapes

- Radius seragam terukur (`8px` untuk tombol/input, `12px` untuk panel utama, `14px` untuk modal), tanpa kartu bersarang (*nested bordered cards*).

## Components

- Komponen global diseragamkan di `resources/css/app.css` (`.ui-panel`, `.ui-panel-padded`, `.ui-input`, `.ui-select`, `.ui-btn-primary`, `.ui-btn-navy`, `.ui-btn-secondary`, `.ui-btn-danger`) dan notifikasi non-blokir lewat `ToastRegion.vue`.
- Konfirmasi berisiko di daftar transaksi menggunakan konfirmasi dua langkah inline untuk menghindari modal berlebihan maupun dialog `window.confirm()`.

## Do's and Don'ts

- **Do:** Pertahankan minimal ukuran teks `12px`, tinggi tombol/input minimal `44px`, dan kesetaraan visual antara opsi bayar QRIS online dan setoran tunai wali kelas.
- **Don't:** Jangan gunakan tema warna hijau, latar belakang krem hangat (`#F7F5F0`), *eyebrow* di atas judul, `border-l-4`, `alert()`/`confirm()`, atau font `Plus Jakarta Sans`/`Inter`.
