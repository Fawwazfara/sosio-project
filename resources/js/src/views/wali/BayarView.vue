<template>
  <div class="flex flex-col gap-6 sm:gap-8">
    <!-- Header -->
    <div class="ui-stagger ui-stagger-1">
      <router-link
        to="/wali/dashboard"
        class="text-sm font-semibold text-[#57534E] hover:text-[#1C1917] inline-flex items-center gap-1.5 mb-2 transition-[color,transform] duration-150 ease-out active:scale-[0.97]"
      >
        <UiIcon name="arrow_back" :size="16" :weight="600" directional />
        <span>Status infak</span>
      </router-link>
      <h1 class="text-2xl sm:text-[26px] font-bold text-[#1C1917] tracking-tight">Bayar infak</h1>
    </div>

    <div v-if="isLoading" class="ui-panel-padded space-y-4">
      <div class="h-6 w-48 bg-[#ECE8DF] rounded-[8px] animate-pulse"></div>
      <div class="h-32 w-full bg-[#F7F4ED] rounded-[12px] animate-pulse"></div>
    </div>

    <!-- ACTIVE QRIS / OFFLINE SLIP / RECEIPT SCREEN -->
    <div v-else-if="activeTransaction" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start ui-stagger ui-stagger-2">
      <!-- Left: QRIS Barcode or Offline Slip or Paid Receipt -->
      <div class="lg:col-span-7 ui-panel-padded">

        <!-- PAID RECEIPT STATE -->
        <template v-if="activeTransaction.status === 'paid'">
          <div class="flex items-center gap-3.5 pb-5 border-b border-black/[0.07]">
            <div class="w-12 h-12 rounded-[12px] bg-[#F9EFE7] shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.2)] text-[#A8551E] flex items-center justify-center shrink-0">
              <UiIcon name="verified" :size="24" :weight="600" filled />
            </div>
            <div>
              <h2 class="text-lg font-bold text-[#1C1917]">Pembayaran Lunas Terverifikasi</h2>
              <p class="text-sm text-[#A8551E] font-medium mt-0.5">Kwitansi Digital Infak Madrasah As-Sajjaad</p>
            </div>
          </div>

          <div class="py-5 space-y-3.5 text-sm border-b border-black/[0.07]">
            <div class="flex justify-between gap-4">
              <span class="text-[#78716C]">Nomor Referensi</span>
              <span class="font-mono font-bold text-[#1C1917]">{{ activeTransaction.kode_referensi }}</span>
            </div>
            <div class="flex justify-between gap-4">
              <span class="text-[#78716C]">Nama Siswa</span>
              <span class="font-semibold text-[#1C1917] text-right">{{ activeTransaction.siswa_nama }} ({{ activeTransaction.kelas_nama }})</span>
            </div>
            <div class="flex justify-between gap-4">
              <span class="text-[#78716C]">Bulan Dibayar</span>
              <span class="font-semibold text-[#1C1917] text-right">{{ activeTransaction.bulan_labels.join(', ') }}</span>
            </div>
            <div class="flex justify-between gap-4">
              <span class="text-[#78716C]">Metode</span>
              <span class="font-semibold text-[#292524] text-right">
                {{ activeTransaction.metode === 'online' ? 'Online · QRIS (Sandbox)' : 'Offline · Tunai Wali Kelas' }}
              </span>
            </div>
            <div class="flex justify-between items-center pt-3 border-t border-dashed border-[#D6D0C4]">
              <span class="font-bold text-[#1C1917]">Total Diterima</span>
              <span class="text-xl font-extrabold font-mono tabular-nums text-[#A8551E]">Rp {{ formatRupiah(activeTransaction.total_nominal) }}</span>
            </div>
          </div>

          <div class="pt-6 flex flex-wrap gap-3">
            <router-link to="/wali/dashboard" class="ui-btn-navy ui-btn-icon-l">
              <UiIcon name="fact_check" :size="18" :weight="600" />
              <span>Lihat Kartu Kontrol</span>
            </router-link>
            <button @click="resetToForm" class="ui-btn-secondary">
              Bayar Bulan Lainnya
            </button>
          </div>
        </template>

        <!-- ONLINE QRIS BARCODE STATE -->
        <template v-else-if="activeTransaction.metode === 'online'">
          <div class="flex flex-wrap items-start justify-between gap-3 pb-5 border-b border-black/[0.07]">
            <div>
              <h2 class="text-lg font-bold text-[#1C1917]">Scan Barcode QRIS untuk Membayar</h2>
              <p class="text-sm text-[#78716C] mt-0.5">Pembayaran Online Instan</p>
            </div>
            <div class="px-3 py-1.5 rounded-[10px] bg-[#F9EFE7] shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.2)] text-right">
              <span class="block text-xs text-[#57534E]">Kode Tagihan</span>
              <span class="text-xs font-mono font-bold text-[#7C3A10]">{{ activeTransaction.kode_referensi }}</span>
            </div>
          </div>

          <div class="py-6 flex flex-col items-center text-center">
            <!-- Concentric QRIS Ticket: p-5 (20px) keeps independent radius token, inner p-3 (12px) + rounded-[8px] = rounded-[20px] -->
            <div class="bg-[#F7F4ED] rounded-[20px] p-5 flex flex-col items-center w-full max-w-sm shadow-[inset_0_1px_2px_0_oklch(0.22_0.015_65_/_0.05),0_0_0_1px_oklch(0.22_0.015_65_/_0.07)]">
              <div class="flex items-center gap-2 mb-4">
                <span class="text-sm font-extrabold tracking-widest text-[#292524]">QRIS</span>
                <span class="text-xs text-[#78716C]">· MADRASAH AS-SAJJAAD</span>
              </div>

              <div class="bg-[#FDFCF9] p-3 rounded-[14px] shadow-[var(--shadow-border)]">
                <QrCodeSvg :value="activeTransaction.qr_payload || activeTransaction.kode_referensi" :size="208" />
              </div>

              <p class="text-xs font-mono text-[#78716C] mt-4 tabular-nums">NMID: ID1026000881920</p>
              <p class="text-2xl font-extrabold font-mono tabular-nums text-[#1C1917] mt-1">
                Rp {{ formatRupiah(activeTransaction.total_nominal) }}
              </p>
              <p class="text-xs text-[#57534E] mt-2 leading-relaxed">
                Dukung GoPay, OVO, DANA, ShopeePay, BCA, BRI, Mandiri, BSI &amp; seluruh mobile banking.
              </p>
            </div>

            <!-- Sandbox Simulation Panel -->
            <div
              v-if="sandboxEnabled"
              class="mt-6 w-full bg-[#F9EFE7] rounded-[14px] p-5 text-left border border-transparent shadow-[0_0_0_1px_oklch(0.55_0.13_42_/_0.22)]"
            >
              <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-[9px] bg-[#F2DFCF] text-[#A8551E] flex items-center justify-center shrink-0">
                  <UiIcon name="science" :size="18" :weight="600" />
                </div>
                <div class="flex-1">
                  <p class="text-sm font-bold text-[#1C1917]">Mode Sandbox Payment Gateway</p>
                  <p class="text-xs text-[#57534E] mt-1 leading-relaxed">
                    Koneksi penyedia QRIS live belum diaktifkan. Tekan tombol di bawah untuk menguji verifikasi pembayaran otomatis.
                  </p>
                  <div class="flex flex-wrap items-center gap-3 mt-4">
                    <button
                      @click="simulateSandboxPaid"
                      :disabled="isActionLoading"
                      class="ui-btn-primary ui-btn-icon-l"
                    >
                      <UiIcon v-if="isActionLoading" name="progress_activity" :size="17" :weight="600" class="animate-spin" />
                      <UiIcon v-else name="check_circle" :size="17" :weight="600" />
                      <span>{{ isActionLoading ? 'Memverifikasi...' : 'Simulasi Pembayaran Berhasil (Sandbox)' }}</span>
                    </button>

                    <template v-if="!confirmingCancel">
                      <button
                        @click="confirmingCancel = true"
                        :disabled="isActionLoading"
                        class="ui-btn-secondary"
                      >
                        Batalkan Tagihan
                      </button>
                    </template>
                    <template v-else>
                      <button
                        @click="cancelTransaction"
                        :disabled="isActionLoading"
                        class="ui-btn-danger"
                      >
                        Ya, Batalkan Sekarang
                      </button>
                      <button
                        @click="confirmingCancel = false"
                        class="ui-btn-secondary"
                      >
                        Batal
                      </button>
                    </template>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>

        <!-- OFFLINE WALI KELAS SLIP STATE -->
        <template v-else>
          <div class="flex flex-wrap items-start justify-between gap-3 pb-5 border-b border-black/[0.07]">
            <div>
              <h2 class="text-lg font-bold text-[#1C1917]">Slip Rencana Setoran Tunai</h2>
              <p class="text-sm text-[#9A6B16] font-medium mt-0.5">Pembayaran Offline via Wali Kelas</p>
            </div>
            <span class="text-xs font-mono font-bold px-3 py-1.5 rounded-[9px] bg-[#FAF4E4] shadow-[inset_0_0_0_1px_oklch(0.62_0.12_75_/_0.28)] text-[#6E4A0B]">
              {{ activeTransaction.kode_referensi }}
            </span>
          </div>

          <div class="py-5 space-y-5">
            <div class="bg-[#FAF4E4] rounded-[12px] p-5 text-sm text-[#6E4A0B] shadow-[inset_0_0_0_1px_oklch(0.62_0.12_75_/_0.25)]">
              <p class="font-bold mb-2">Langkah Penyerahan Tunai:</p>
              <ol class="list-decimal list-inside space-y-1.5 text-sm leading-relaxed">
                <li>Siapkan uang tunai pas sejumlah <strong class="font-mono tabular-nums">Rp {{ formatRupiah(activeTransaction.total_nominal) }}</strong>.</li>
                <li>Serahkan langsung kepada Wali Kelas (<strong>{{ activeTransaction.wali_kelas_nama }}</strong>) saat di madrasah.</li>
                <li>Wali Kelas menekan tombol konfirmasi di sistem dan status anak otomatis berubah menjadi <strong>Lunas</strong>.</li>
              </ol>
            </div>

            <div class="bg-[#F7F4ED] rounded-[12px] p-5 space-y-3 text-sm shadow-[inset_0_1px_2px_0_oklch(0.22_0.015_65_/_0.05),0_0_0_1px_oklch(0.22_0.015_65_/_0.07)]">
              <div class="flex justify-between gap-4">
                <span class="text-[#78716C]">Siswa</span>
                <span class="font-semibold text-[#1C1917] text-right">{{ activeTransaction.siswa_nama }} ({{ activeTransaction.kelas_nama }})</span>
              </div>
              <div class="flex justify-between gap-4">
                <span class="text-[#78716C]">Wali Kelas Penerima</span>
                <span class="font-semibold text-[#1C1917] text-right">{{ activeTransaction.wali_kelas_nama }}</span>
              </div>
              <div class="flex justify-between gap-4">
                <span class="text-[#78716C]">Bulan</span>
                <span class="font-semibold text-[#1C1917] text-right">{{ activeTransaction.bulan_labels.join(', ') }}</span>
              </div>
              <div v-if="activeTransaction.catatan" class="flex justify-between gap-4">
                <span class="text-[#78716C]">Catatan</span>
                <span class="text-right text-[#1C1917]">{{ activeTransaction.catatan }}</span>
              </div>
              <div class="flex justify-between items-center pt-3 border-t border-black/[0.07]">
                <span class="font-bold text-[#1C1917]">Total Tunai Diserahkan</span>
                <span class="font-mono font-bold text-[#9A6B16] text-lg tabular-nums">Rp {{ formatRupiah(activeTransaction.total_nominal) }}</span>
              </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
              <router-link to="/wali/dashboard" class="ui-btn-navy">
                Kembali ke Status Infak
              </router-link>

              <template v-if="!confirmingCancel">
                <button
                  @click="confirmingCancel = true"
                  :disabled="isActionLoading"
                  class="ui-btn-secondary"
                >
                  Batalkan Slip Tunai
                </button>
              </template>
              <template v-else>
                <button
                  @click="cancelTransaction"
                  :disabled="isActionLoading"
                  class="ui-btn-danger"
                >
                  Ya, Batalkan Slip Ini
                </button>
                <button
                  @click="confirmingCancel = false"
                  class="ui-btn-secondary"
                >
                  Batal
                </button>
              </template>
            </div>
          </div>
        </template>
      </div>

      <!-- Right Summary Box -->
      <aside class="lg:col-span-5 ui-panel-padded space-y-4">
        <h3 class="text-base font-bold text-[#1C1917]">Rincian Tagihan</h3>
        <div class="space-y-3 text-sm text-[#57534E] border-b border-black/[0.07] pb-4">
          <div class="flex justify-between gap-3">
            <span>Siswa</span>
            <span class="font-semibold text-[#1C1917] text-right">{{ activeTransaction.siswa_nama }}</span>
          </div>
          <div class="flex justify-between gap-3">
            <span>Kelas</span>
            <span class="font-semibold text-[#1C1917] text-right">{{ activeTransaction.kelas_nama }}</span>
          </div>
          <div class="flex justify-between gap-3">
            <span>Jumlah Bulan</span>
            <span class="font-semibold text-[#1C1917] tabular-nums">{{ activeTransaction.bulan_labels.length }} bulan</span>
          </div>
          <div class="flex justify-between gap-3">
            <span>Nominal / Bulan</span>
            <span class="font-mono font-semibold text-[#1C1917] tabular-nums">Rp {{ formatRupiah(activeTransaction.nominal_per_bulan) }}</span>
          </div>
        </div>
        <div class="flex justify-between items-center pt-1">
          <span class="text-sm font-bold text-[#1C1917]">Total</span>
          <span class="text-xl font-extrabold font-mono tabular-nums text-[#292524]">Rp {{ formatRupiah(activeTransaction.total_nominal) }}</span>
        </div>
      </aside>
    </div>

    <!-- PAYMENT FORM (CHOOSE MONTHS + ONLINE / OFFLINE METHOD) -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <div class="lg:col-span-8 flex flex-col gap-6">

        <!-- Error Banner -->
        <div
          v-if="errorMessage"
          role="alert"
          class="bg-[#FDF2F4] text-[#881337] rounded-[14px] px-4 py-3.5 text-sm flex items-start gap-2.5 border border-transparent shadow-[0_0_0_1px_oklch(0.55_0.18_12_/_0.22)]"
        >
          <UiIcon name="error" :size="18" :weight="600" class="mt-0.5 text-[#9F1239]" />
          <span>{{ errorMessage }}</span>
        </div>

        <!-- Step 1: Pilih Siswa (jika >1) -->
        <section v-if="students.length > 1" class="ui-panel-padded ui-stagger ui-stagger-2">
          <h2 class="text-base font-bold text-[#1C1917] mb-3.5">Pilih siswa</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <button
              v-for="s in students"
              :key="s.id"
              type="button"
              @click="selectStudent(s.id)"
              :class="[
                'min-h-[64px] p-4 rounded-[12px] text-left transition-[background-color,box-shadow,transform] duration-150 ease-out active:scale-[0.96]',
                form.siswa_id === s.id
                  ? 'bg-[#F9EFE7] border border-[#A8551E] shadow-[0_0_0_2px_oklch(0.55_0.13_42_/_0.14)]'
                  : 'bg-[#F7F4ED] border border-transparent shadow-[var(--shadow-border)] hover:shadow-[var(--shadow-border-hover)] hover:bg-[#FDFCF9]'
              ]"
            >
              <p class="text-sm font-bold text-[#1C1917]">{{ s.nama_lengkap }}</p>
              <p class="text-xs text-[#57534E] mt-1 tabular-nums">{{ s.kelas }} · {{ s.unpaid_count }} bulan belum dibayar</p>
            </button>
          </div>
        </section>

        <!-- Step 2: Pilih Bulan Belum Lunas -->
        <section class="ui-panel-padded ui-stagger ui-stagger-2">
          <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
              <h2 class="text-base font-bold text-[#1C1917]">Pilih Bulan Infak</h2>
              <p class="text-xs text-[#78716C] mt-0.5" v-if="currentStudent">
                {{ currentStudent.nama_lengkap }} ({{ currentStudent.kelas }})
              </p>
            </div>
            <button
              v-if="selectableMonths.length > 0"
              type="button"
              @click="toggleAllSelectable"
              class="min-h-[38px] px-3.5 rounded-[10px] border border-transparent bg-[#F7F4ED] shadow-[var(--shadow-border)] hover:shadow-[var(--shadow-border-hover)] hover:bg-[#FDFCF9] text-xs font-semibold text-[#A8551E] transition-[background-color,box-shadow,transform] duration-150 ease-out active:scale-[0.96]"
            >
              {{ form.months.length === selectableMonths.length ? 'Kosongkan Pilihan' : 'Pilih Semua Tersedia' }}
            </button>
          </div>

          <div
            v-if="currentStudent && currentStudent.months.every(m => m.paid)"
            class="p-4 rounded-[12px] bg-[#F9EFE7] text-sm text-[#7C3A10] shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.2)]"
          >
            Alhamdulillah, seluruh 12 bulan infak tahun ajaran ini sudah lunas.
          </div>

          <div v-else-if="currentStudent" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <button
              v-for="m in currentStudent.months"
              :key="m.english"
              type="button"
              :disabled="m.paid || Boolean(m.pending)"
              @click="toggleMonth(m.english)"
              :aria-pressed="form.months.includes(m.english)"
              :class="[
                'p-3.5 rounded-[12px] text-left transition-[background-color,color,box-shadow,border-color,transform] duration-150 ease-out flex flex-col justify-between min-h-[84px]',
                m.paid
                  ? 'bg-[#F9EFE7] border border-transparent shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.2)] text-[#7C3A10] opacity-75 cursor-not-allowed'
                  : m.pending
                    ? 'bg-[#FAF4E4] border border-transparent shadow-[inset_0_0_0_1px_oklch(0.62_0.12_75_/_0.28)] text-[#7D5714] opacity-85 cursor-not-allowed'
                    : form.months.includes(m.english)
                      ? 'bg-[#292524] border border-transparent text-white shadow-[var(--shadow-btn-navy)] enabled:active:scale-[0.96]'
                      : 'bg-[#F7F4ED] border border-transparent shadow-[var(--shadow-border)] text-[#1C1917] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)] enabled:active:scale-[0.96]'
              ]"
            >
              <div class="flex items-center justify-between w-full gap-1">
                <span class="text-sm font-bold">{{ m.label }}</span>
                <UiIcon v-if="form.months.includes(m.english)" name="check_circle" :size="17" :weight="600" />
              </div>
              <span class="text-xs mt-1.5" :class="form.months.includes(m.english) ? 'text-[#E7C0A1]' : 'text-[#78716C]'">
                {{ m.paid ? 'Sudah Lunas' : m.pending ? 'Sedang Proses' : 'Belum Bayar' }}
              </span>
            </button>
          </div>

          <!-- Nominal per bulan -->
          <div class="mt-6 pt-6 border-t border-black/[0.07]">
            <label class="block text-sm font-bold text-[#1C1917] mb-3" for="nominalInput">Nominal Infak per Bulan</label>
            <div class="flex flex-wrap gap-2.5 mb-3.5">
              <button
                v-for="preset in [25000, 30000, 50000, 100000]"
                :key="preset"
                type="button"
                @click="form.nominal_per_bulan = preset"
                :class="[
                  'min-h-[42px] px-4 rounded-[10px] border border-transparent text-xs font-mono font-bold tabular-nums transition-[background-color,color,box-shadow,transform] duration-150 ease-out active:scale-[0.96]',
                  Number(form.nominal_per_bulan) === preset
                    ? 'bg-[#292524] text-white shadow-[var(--shadow-btn-navy)]'
                    : 'bg-[#F7F4ED] text-[#1C1917] shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)]'
                ]"
              >
                Rp {{ formatRupiah(preset) }}
              </button>
            </div>
            <div class="relative max-w-xs">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-mono font-bold text-[#78716C]">Rp</span>
              <input
                id="nominalInput"
                v-model.number="form.nominal_per_bulan"
                type="number"
                min="1000"
                step="1000"
                class="ui-input pl-10 font-mono font-bold tabular-nums"
              >
            </div>
          </div>
        </section>

        <!-- Step 3: Metode Pembayaran (Online QRIS vs Offline Wali Kelas) -->
        <section class="ui-panel-padded ui-stagger ui-stagger-3">
          <h2 class="text-base font-bold text-[#1C1917] mb-4">Pilih Metode Pembayaran</h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Opsi Online QRIS -->
            <button
              type="button"
              @click="form.metode = 'online'"
              :aria-pressed="form.metode === 'online'"
              :class="[
                'p-4 sm:p-5 rounded-[14px] text-left transition-[background-color,border-color,box-shadow,transform] duration-150 ease-out active:scale-[0.96] flex flex-col gap-3',
                form.metode === 'online'
                  ? 'border border-[#A8551E] bg-[#F9EFE7] shadow-[0_0_0_2px_oklch(0.55_0.13_42_/_0.14)]'
                  : 'border border-transparent bg-[#F7F4ED] shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)]'
              ]"
            >
              <div class="flex items-center justify-between gap-2 w-full">
                <div class="flex items-center gap-2.5">
                  <UiIcon name="qr_code_2" :size="22" :weight="600" class="text-[#A8551E]" />
                  <span class="text-sm font-bold text-[#1C1917]">QRIS online</span>
                </div>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-[7px] bg-[#F2DFCF] text-[#7C3A10]">
                  Otomatis
                </span>
              </div>
              <p class="text-xs text-[#57534E] leading-relaxed">
                Bayar melalui e-wallet atau mobile banking. Tersedia simulasi sandbox.
              </p>
            </button>

            <!-- Opsi Offline Wali Kelas -->
            <button
              type="button"
              @click="form.metode = 'offline'"
              :aria-pressed="form.metode === 'offline'"
              :class="[
                'p-4 sm:p-5 rounded-[14px] text-left transition-[background-color,border-color,box-shadow,transform] duration-150 ease-out active:scale-[0.96] flex flex-col gap-3',
                form.metode === 'offline'
                  ? 'border border-[#9A6B16] bg-[#FAF4E4] shadow-[0_0_0_2px_oklch(0.62_0.12_75_/_0.15)]'
                  : 'border border-transparent bg-[#F7F4ED] shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)]'
              ]"
            >
              <div class="flex items-center justify-between gap-2 w-full">
                <div class="flex items-center gap-2.5">
                  <UiIcon name="handshake" :size="22" :weight="600" class="text-[#9A6B16]" />
                  <span class="text-sm font-bold text-[#1C1917]">Tunai ke wali kelas</span>
                </div>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-[7px] bg-[#F0DFB0] text-[#6E4A0B]">
                  Tatap Muka
                </span>
              </div>
              <p class="text-xs text-[#57534E] leading-relaxed">
                Buat slip, lalu serahkan uang ke <strong>{{ currentStudent?.wali_kelas || 'wali kelas' }}</strong>.
              </p>
            </button>
          </div>

          <div class="mt-5">
            <label class="block text-xs font-semibold text-[#44403C] mb-1.5" for="catatan">
              Catatan (opsional)
            </label>
            <input
              v-model="form.catatan"
              id="catatan"
              type="text"
              placeholder="Contoh: Uang dititipkan hari Senin saat antar anak"
              class="ui-input"
            >
          </div>
        </section>
      </div>

      <!-- Right Sticky Summary -->
      <aside class="lg:col-span-4 ui-panel-padded lg:sticky lg:top-24 flex flex-col gap-5 ui-stagger ui-stagger-3">
        <h2 class="text-base font-bold text-[#1C1917] pb-3.5 border-b border-black/[0.07]">Ringkasan Pembayaran</h2>

        <div class="space-y-3 text-sm">
          <div class="flex justify-between gap-2">
            <span class="text-[#78716C]">Siswa</span>
            <span class="font-semibold text-[#1C1917] text-right">{{ currentStudent?.nama_lengkap || '-' }}</span>
          </div>
          <div class="flex justify-between gap-2">
            <span class="text-[#78716C]">Metode</span>
            <span class="font-semibold" :class="form.metode === 'online' ? 'text-[#A8551E]' : 'text-[#9A6B16]'">
              {{ form.metode === 'online' ? 'Online (QRIS)' : 'Offline (Wali Kelas)' }}
            </span>
          </div>
          <div class="flex justify-between gap-2">
            <span class="text-[#78716C]">Bulan Dipilih</span>
            <span class="font-semibold text-[#1C1917] text-right tabular-nums">{{ form.months.length }} bulan</span>
          </div>
          <div class="flex justify-between gap-2">
            <span class="text-[#78716C]">Nominal / Bulan</span>
            <span class="font-mono font-semibold text-[#1C1917] tabular-nums">Rp {{ formatRupiah(form.nominal_per_bulan) }}</span>
          </div>
        </div>

        <div class="pt-4 border-t border-black/[0.07] flex justify-between items-center">
          <span class="text-sm font-bold text-[#1C1917]">Total Bayar</span>
          <span class="text-xl font-extrabold font-mono tabular-nums text-[#292524]">
            Rp {{ formatRupiah(totalBayar) }}
          </span>
        </div>

        <button
          type="button"
          @click="submitPayment"
          :disabled="isSubmitting || form.months.length === 0 || !form.siswa_id"
          class="ui-btn-base ui-btn-icon-l w-full min-h-[48px] text-white"
          :class="form.metode === 'online'
            ? 'bg-[#A8551E] hover:bg-[#8D4415] shadow-[var(--shadow-btn-primary)]'
            : 'bg-[#292524] hover:bg-[#1C1917] shadow-[var(--shadow-btn-navy)]'"
        >
          <UiIcon v-if="isSubmitting" name="progress_activity" :size="18" :weight="600" class="animate-spin" />
          <UiIcon v-else :name="form.metode === 'online' ? 'qr_code_2' : 'receipt_long'" :size="18" :weight="600" />
          <span>
            {{ isSubmitting ? 'Memproses...' : (form.metode === 'online' ? 'Buat Barcode QRIS' : 'Buat Slip Setoran Tunai') }}
          </span>
        </button>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import QrCodeSvg from '@/components/QrCodeSvg.vue';
import { notify } from '@/utils/toast';

const route = useRoute();
const router = useRouter();

const isLoading = ref(true);
const isSubmitting = ref(false);
const isActionLoading = ref(false);
const confirmingCancel = ref(false);
const errorMessage = ref('');
const sandboxEnabled = ref(true);
const students = ref([]);
const activeTransaction = ref(null);

const form = reactive({
  siswa_id: null,
  months: [],
  nominal_per_bulan: 25000,
  metode: 'online',
  catatan: '',
});

const currentStudent = computed(() => students.value.find(s => s.id === form.siswa_id) || null);
const selectableMonths = computed(() => {
  if (!currentStudent.value) return [];
  return currentStudent.value.months.filter(m => !m.paid && !m.pending).map(m => m.english);
});
const totalBayar = computed(() => form.months.length * (Number(form.nominal_per_bulan) || 0));

const formatRupiah = (n) => Number(n || 0).toLocaleString('id-ID');

const selectStudent = (id) => {
  form.siswa_id = id;
  form.months = [];
  errorMessage.value = '';
};

const toggleMonth = (eng) => {
  const idx = form.months.indexOf(eng);
  if (idx === -1) {
    form.months.push(eng);
  } else {
    form.months.splice(idx, 1);
  }
};

const toggleAllSelectable = () => {
  if (form.months.length === selectableMonths.value.length) {
    form.months = [];
  } else {
    form.months = [...selectableMonths.value];
  }
};

const loadDashboard = async () => {
  isLoading.value = true;
  confirmingCancel.value = false;
  try {
    const res = await axios.get('/wali/dashboard');
    students.value = res.data.students || [];
    sandboxEnabled.value = Boolean(res.data.sandbox_enabled);

    const qSiswaId = Number(route.query.siswa_id);
    const matchedStudent = students.value.find(s => s.id === qSiswaId) || students.value[0];
    if (matchedStudent) {
      form.siswa_id = matchedStudent.id;

      if (route.query.ref) {
        const found = matchedStudent.active_payments.find(p => p.kode_referensi === route.query.ref);
        if (found) {
          activeTransaction.value = found;
          return;
        }
      }

      if (route.query.month) {
        const target = matchedStudent.months.find(m => m.english === route.query.month && !m.paid && !m.pending);
        if (target) {
          form.months = [target.english];
        }
      }
    }
  } catch (err) {
    console.error(err);
    errorMessage.value = 'Gagal memuat data siswa.';
  } finally {
    isLoading.value = false;
  }
};

const submitPayment = async () => {
  errorMessage.value = '';
  isSubmitting.value = true;
  try {
    const res = await axios.post('/wali/pembayaran', {
      siswa_id: form.siswa_id,
      months: form.months,
      nominal_per_bulan: Number(form.nominal_per_bulan),
      metode: form.metode,
      catatan: form.catatan || null,
    });
    activeTransaction.value = res.data.pembayaran;
    notify(form.metode === 'online' ? 'Barcode QRIS siap dipindai.' : 'Slip setoran tunai berhasil dibuat.', 'success');
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Gagal membuat pengajuan pembayaran.';
  } finally {
    isSubmitting.value = false;
  }
};

const simulateSandboxPaid = async () => {
  if (!activeTransaction.value) return;
  isActionLoading.value = true;
  try {
    const res = await axios.post(`/wali/pembayaran/${activeTransaction.value.id}/simulate-paid`);
    activeTransaction.value = res.data.pembayaran;
    notify('Pembayaran QRIS Sandbox terverifikasi lunas.', 'success');
  } catch (err) {
    notify(err.response?.data?.message || 'Gagal simulasi pembayaran sandbox.', 'error');
  } finally {
    isActionLoading.value = false;
  }
};

const cancelTransaction = async () => {
  if (!activeTransaction.value) return;
  isActionLoading.value = true;
  try {
    await axios.post(`/wali/pembayaran/${activeTransaction.value.id}/cancel`);
    activeTransaction.value = null;
    confirmingCancel.value = false;
    router.replace({ path: '/wali/bayar', query: { siswa_id: form.siswa_id } });
    await loadDashboard();
    notify('Pengajuan pembayaran telah dibatalkan.', 'info');
  } catch (err) {
    notify(err.response?.data?.message || 'Gagal membatalkan pengajuan.', 'error');
  } finally {
    isActionLoading.value = false;
  }
};

const resetToForm = async () => {
  activeTransaction.value = null;
  confirmingCancel.value = false;
  form.months = [];
  form.catatan = '';
  router.replace({ path: '/wali/bayar', query: { siswa_id: form.siswa_id } });
  await loadDashboard();
};

onMounted(loadDashboard);
</script>
