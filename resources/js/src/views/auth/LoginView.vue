<template>
  <div class="min-h-screen bg-[#F5F3EE] text-[#1C1917] flex flex-col lg:flex-row">
    <!-- Left Architectural Brand & Role Overview Panel -->
    <aside
      class="lg:w-[43%] bg-gradient-to-br from-[#292524] via-[#221E1C] to-[#171412] text-white p-7 sm:p-10 lg:p-12 flex flex-col justify-between relative overflow-hidden"
    >
      <div
        class="pointer-events-none absolute -top-24 -left-24 w-80 h-80 rounded-full bg-[#A8551E]/25 blur-3xl"
        aria-hidden="true"
      ></div>

      <div class="relative flex items-center gap-3.5 ui-stagger ui-stagger-1">
        <div class="w-11 h-11 rounded-[12px] bg-[#A8551E] shadow-[inset_0_1px_0_0_oklch(1_0_0_/_0.22),0_0_0_1px_oklch(1_0_0_/_0.16)] flex items-center justify-center text-white font-bold text-base tracking-tight shrink-0">
          AS
        </div>
        <div>
          <p class="text-base font-bold text-white leading-tight tracking-tight">Madrasah As-Sajjaad</p>
          <p class="text-xs text-[#D6CFC2] mt-0.5">Sistem Informasi Infak &amp; Keuangan</p>
        </div>
      </div>

      <div class="relative my-9 lg:my-0 max-w-lg">
        <h1 class="ui-stagger ui-stagger-1 text-2xl sm:text-3xl lg:text-[34px] font-extrabold leading-[1.18] tracking-tight text-white">
          Satu Buku Besar Infak untuk Orang Tua, Wali Kelas, &amp; Tata Usaha.
        </h1>
        <p class="ui-stagger ui-stagger-2 text-sm sm:text-base text-[#E2DDD2] leading-relaxed mt-4">
          Pantau kewajiban infak bulanan secara terbuka. Wali murid bebas memilih <strong class="text-white font-semibold">bayar online via QRIS</strong> atau <strong class="text-white font-semibold">titip tunai langsung ke wali kelas</strong> tanpa mengubah alur buku kas madrasah.
        </p>

        <!-- Concentric feature surface: 6px padding + 12px inner radius = 18px outer radius -->
        <div class="ui-stagger ui-stagger-3 mt-8 p-1.5 rounded-[18px] bg-white/[0.05] ring-1 ring-inset ring-white/[0.11] flex flex-col gap-1">
          <div class="px-4 py-3 rounded-[12px] bg-white/[0.04] flex items-center gap-3.5">
            <div class="w-8 h-8 rounded-[8px] bg-[#A8551E]/40 ring-1 ring-inset ring-white/15 flex items-center justify-center shrink-0 text-[#F5C59F]">
              <UiIcon name="qr_code_2" :size="18" :weight="500" />
            </div>
            <p class="text-xs sm:text-sm text-[#F5F3EE] leading-snug">Pembayaran online QRIS dengan verifikasi otomatis (Mode Sandbox)</p>
          </div>
          <div class="px-4 py-3 rounded-[12px] bg-white/[0.04] flex items-center gap-3.5">
            <div class="w-8 h-8 rounded-[8px] bg-[#9A6B16]/40 ring-1 ring-inset ring-white/15 flex items-center justify-center shrink-0 text-[#F3D79B]">
              <UiIcon name="handshake" :size="18" :weight="500" />
            </div>
            <p class="text-xs sm:text-sm text-[#F5F3EE] leading-snug">Slip setoran tunai resmi saat menyerahkan uang langsung ke wali kelas</p>
          </div>
          <div class="px-4 py-3 rounded-[12px] bg-white/[0.04] flex items-center gap-3.5">
            <div class="w-8 h-8 rounded-[8px] bg-white/10 ring-1 ring-inset ring-white/15 flex items-center justify-center shrink-0 text-[#E7DFD3]">
              <UiIcon name="account_balance" :size="18" :weight="500" />
            </div>
            <p class="text-xs sm:text-sm text-[#F5F3EE] leading-snug">Laporan kas bulanan &amp; riwayat transaksi tercatat real-time</p>
          </div>
        </div>
      </div>

      <div class="relative pt-6 border-t border-white/[0.12] flex flex-wrap items-center justify-between gap-3 text-xs text-[#C8C0B2]">
        <span>Tahun Ajaran Aktif · Buku Kas Digital</span>
        <span class="font-mono">Sanctum Auth · Multi-Peran</span>
      </div>
    </aside>

    <!-- Right Login Form Panel -->
    <main class="flex-1 flex items-center justify-center p-5 sm:p-10 lg:p-14">
      <div class="w-full max-w-[464px]">
        <transition
          enter-active-class="transition-[opacity,transform] duration-300 ease-out"
          enter-from-class="opacity-0 motion-safe:-translate-y-2"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition-[opacity,transform] duration-150 ease-out"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 motion-safe:-translate-y-2"
        >
          <div
            v-if="showError"
            role="alert"
            class="mb-5 bg-[#FDF2F4] text-[#881337] rounded-[14px] px-4 py-3.5 flex items-start gap-3 text-sm border border-transparent shadow-[0_0_0_1px_oklch(0.55_0.18_12_/_0.22),0_4px_10px_-2px_oklch(0.45_0.16_12_/_0.1)]"
          >
            <UiIcon name="error" :size="18" :weight="600" class="mt-0.5 text-[#9F1239]" />
            <span>{{ errorMessage }}</span>
          </div>
        </transition>

        <div class="ui-panel-elevated p-6 sm:p-9 ui-stagger ui-stagger-2">
          <div class="mb-7">
            <h2 class="text-2xl font-bold text-[#1C1917] tracking-tight">Masuk ke Portal Infak</h2>
            <p class="text-sm text-[#57534E] mt-1.5 leading-relaxed">
              Masukkan email akun Anda atau nomor NIS siswa (khusus akun Orang Tua / Wali).
            </p>
          </div>

          <form class="flex flex-col gap-5" @submit.prevent="handleLogin">
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#44403C]" for="email">Email atau NIS Siswa</label>
              <input
                v-model="form.email"
                id="email"
                name="email"
                type="text"
                required
                autocomplete="username"
                placeholder="Contoh: wali1@assajjad.com atau 2026001"
                class="ui-input"
              >
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#44403C]" for="password">Kata Sandi</label>
              <div class="relative">
                <input
                  v-model="form.password"
                  id="password"
                  name="password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  autocomplete="current-password"
                  placeholder="Masukkan kata sandi"
                  class="ui-input pr-12"
                >
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  aria-label="Tampilkan atau sembunyikan kata sandi"
                  class="absolute right-1.5 top-1/2 -translate-y-1/2 min-h-[36px] min-w-[36px] flex items-center justify-center text-[#78716C] hover:text-[#1C1917] hover:bg-[#ECE8DF] rounded-[8px] transition-[color,background-color,transform] duration-150 ease-out enabled:active:scale-[0.96]"
                >
                  <span class="ui-icon-swap">
                    <span :class="showPassword ? 'ui-icon-swap-shown' : 'ui-icon-swap-hidden'">
                      <UiIcon name="visibility" :size="18" :weight="500" />
                    </span>
                    <span :class="showPassword ? 'ui-icon-swap-hidden' : 'ui-icon-swap-shown'">
                      <UiIcon name="visibility_off" :size="18" :weight="500" />
                    </span>
                  </span>
                </button>
              </div>
            </div>

            <label class="flex items-center gap-2.5 text-sm text-[#57534E] cursor-pointer select-none py-1">
              <input v-model="form.remember" type="checkbox" class="w-4 h-4 rounded border-[#D6D0C4] text-[#A8551E] focus:ring-[#A8551E]">
              Ingat sesi login saya di perangkat ini
            </label>

            <button
              type="submit"
              :disabled="isLoading"
              class="ui-btn-primary w-full"
              :class="isLoading ? 'ui-btn-icon-l' : ''"
            >
              <UiIcon v-if="isLoading" name="progress_activity" :size="18" :weight="600" class="animate-spin" />
              <span>{{ isLoading ? 'Memverifikasi...' : 'Masuk ke Portal' }}</span>
            </button>
          </form>

          <!-- Collapsible Quick Demo Credentials (Untuk Uji Coba / Presentasi) -->
          <div class="mt-7 pt-6 border-t border-black/[0.07]">
            <button
              type="button"
              @click="showDemo = !showDemo"
              class="w-full min-h-[40px] flex items-center justify-between text-xs font-semibold text-[#57534E] hover:text-[#1C1917] transition-[color,transform] duration-150 ease-out enabled:active:scale-[0.98]"
            >
              <span class="flex items-center gap-2">
                <UiIcon name="science" :size="16" :weight="600" class="text-[#A8551E]" />
                Akun Demo &amp; Uji Coba (Klik untuk isi otomatis)
              </span>
              <UiIcon
                name="expand_more"
                :size="18"
                :weight="600"
                class="transition-transform duration-150 ease-out"
                :class="{ 'rotate-180': showDemo }"
              />
            </button>

            <!-- Concentric group: 6px padding + 10px item radius = 16px outer -->
            <div v-if="showDemo" class="mt-3 p-1.5 rounded-[16px] bg-[#ECE8DF] flex flex-col gap-1.5">
              <button
                type="button"
                @click="fillDemo('wali1@assajjad.com', 'wali123')"
                class="w-full text-left px-3.5 py-2.5 rounded-[10px] bg-[#FDFCF9] border border-transparent shadow-[var(--shadow-border)] hover:shadow-[var(--shadow-border-hover)] hover:bg-white transition-[box-shadow,background-color,transform] duration-150 ease-out enabled:active:scale-[0.96] flex items-center justify-between gap-3"
              >
                <div class="min-w-0">
                  <p class="text-xs font-bold text-[#1C1917]">Orang Tua / Wali Siswa</p>
                  <p class="text-xs font-mono text-[#78716C] truncate mt-0.5 tabular-nums">wali1@assajjad.com · NIS 2026001 · wali123</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-[7px] bg-[#F9EFE7] text-[#A8551E] shrink-0">QRIS / Tunai</span>
              </button>

              <button
                type="button"
                @click="fillDemo('erawati@assajjad.com', 'era123')"
                class="w-full text-left px-3.5 py-2.5 rounded-[10px] bg-[#FDFCF9] border border-transparent shadow-[var(--shadow-border)] hover:shadow-[var(--shadow-border-hover)] hover:bg-white transition-[box-shadow,background-color,transform] duration-150 ease-out enabled:active:scale-[0.96] flex items-center justify-between gap-3"
              >
                <div class="min-w-0">
                  <p class="text-xs font-bold text-[#1C1917]">Guru / Wali Kelas</p>
                  <p class="text-xs font-mono text-[#78716C] truncate mt-0.5 tabular-nums">erawati@assajjad.com · era123</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-[7px] bg-[#FAF4E4] text-[#9A6B16] shrink-0">Terima Tunai</span>
              </button>

              <button
                type="button"
                @click="fillDemo('fawwazfara11@assajjad.com', 'fwaz000165')"
                class="w-full text-left px-3.5 py-2.5 rounded-[10px] bg-[#FDFCF9] border border-transparent shadow-[var(--shadow-border)] hover:shadow-[var(--shadow-border-hover)] hover:bg-white transition-[box-shadow,background-color,transform] duration-150 ease-out enabled:active:scale-[0.96] flex items-center justify-between gap-3"
              >
                <div class="min-w-0">
                  <p class="text-xs font-bold text-[#1C1917]">Admin Tata Usaha</p>
                  <p class="text-xs font-mono text-[#78716C] truncate mt-0.5 tabular-nums">fawwazfara11@assajjad.com · fwaz000165</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-[7px] bg-[#ECE8DF] text-[#292524] shrink-0">Kelola Penuh</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const showPassword = ref(false);
const showError = ref(false);
const showDemo = ref(true);
const errorMessage = ref('');
const isLoading = ref(false);

const form = reactive({
  email: '',
  password: '',
  remember: false,
});

const fillDemo = (email, password) => {
  form.email = email;
  form.password = password;
};

const handleLogin = async () => {
  isLoading.value = true;
  showError.value = false;

  try {
    await axios.get('/sanctum/csrf-cookie');
    const response = await axios.post('/login', form);

    const user = response.data.user;
    localStorage.setItem('user', JSON.stringify(user));

    if (user.role === 'admin') {
      router.push('/admin/dashboard');
    } else if (user.role === 'wali') {
      router.push('/wali/dashboard');
    } else {
      router.push('/guru/dashboard');
    }
  } catch (error) {
    showError.value = true;
    errorMessage.value = error.response?.data?.message || 'Email/NIS atau kata sandi salah. Silakan coba lagi.';
    setTimeout(() => {
      showError.value = false;
    }, 5000);
  } finally {
    isLoading.value = false;
  }
};
</script>
