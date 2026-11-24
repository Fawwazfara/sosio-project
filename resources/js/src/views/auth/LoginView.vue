<template>
  <div class="min-h-screen bg-[#F5F3EE] text-[#1C1917] flex flex-col lg:flex-row">
    <!-- Left Architectural Brand & Role Overview Panel -->
    <aside
      class="lg:w-[44%] bg-gradient-to-br from-[#292524] via-[#221E1C] to-[#171412] text-white p-7 sm:p-10 lg:p-12 flex flex-col justify-between relative overflow-hidden"
    >
      <div
        class="pointer-events-none absolute -top-24 -left-24 w-80 h-80 rounded-full bg-[#A8551E]/25 blur-3xl"
        aria-hidden="true"
      ></div>
      <div
        class="pointer-events-none absolute -bottom-28 -right-20 w-80 h-80 rounded-full bg-[#D97706]/15 blur-3xl"
        aria-hidden="true"
      ></div>

      <div class="relative flex items-center gap-3.5 ui-stagger ui-stagger-1">
        <div class="w-11 h-11 rounded-[12px] bg-[#A8551E] shadow-[inset_0_1px_0_0_oklch(1_0_0_/_0.22),0_0_0_1px_oklch(1_0_0_/_0.16)] flex items-center justify-center text-white font-bold text-base tracking-tight shrink-0">
          AS
        </div>
        <div>
          <p class="text-base font-bold text-white leading-tight tracking-tight">Madrasah As-Sajjaad</p>
          <p class="text-xs text-[#D6CFC2] mt-0.5">Sistem Administrasi &amp; Transparansi Infak</p>
        </div>
      </div>

      <div class="relative my-9 lg:my-0 max-w-lg">
        <span class="ui-stagger ui-stagger-1 inline-flex items-center gap-2 text-xs font-semibold px-3 py-1 rounded-full bg-white/10 text-[#FDE8D7] border border-white/15 mb-5">
          Portal Orang Tua, Wali Kelas &amp; Admin
        </span>
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
            <p class="text-xs sm:text-sm text-[#F5F3EE] leading-snug">Pembayaran online QRIS dengan verifikasi status otomatis</p>
          </div>
          <div class="px-4 py-3 rounded-[12px] bg-white/[0.04] flex items-center gap-3.5">
            <div class="w-8 h-8 rounded-[8px] bg-[#9A6B16]/40 ring-1 ring-inset ring-white/15 flex items-center justify-center shrink-0 text-[#F3D79B]">
              <UiIcon name="handshake" :size="18" :weight="500" />
            </div>
            <p class="text-xs sm:text-sm text-[#F5F3EE] leading-snug">Setoran tunai lewat wali kelas terverifikasi langsung ke checklist siswa</p>
          </div>
          <div class="px-4 py-3 rounded-[12px] bg-white/[0.04] flex items-center gap-3.5">
            <div class="w-8 h-8 rounded-[8px] bg-white/10 ring-1 ring-inset ring-white/15 flex items-center justify-center shrink-0 text-[#E7DFD3]">
              <UiIcon name="account_balance" :size="18" :weight="500" />
            </div>
            <p class="text-xs sm:text-sm text-[#F5F3EE] leading-snug">Laporan kas bulanan, riwayat transaksi, &amp; ekspor PDF real-time</p>
          </div>
        </div>
      </div>

      <div class="relative pt-6 border-t border-white/[0.12] flex flex-wrap items-center justify-between gap-3 text-xs text-[#C8C0B2]">
        <span>&copy; {{ new Date().getFullYear() }} Madrasah Diniyah As-Sajjaad</span>
        <span class="font-mono">Portal Digital Multi-Peran</span>
      </div>
    </aside>

    <!-- Right Login Form Panel -->
    <main class="flex-1 flex items-center justify-center p-5 sm:p-10 lg:p-14">
      <div class="w-full max-w-[448px]">
        <transition
          enter-active-class="transition-[opacity,transform] duration-300 ease-out"
          enter-from-class="opacity-0 motion-safe:-translate-y-2"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition-[opacity,transform] duration-150 ease-out"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 motion-safe:-translate-y-2"
        >
          <div
            v-if="errorMessage"
            id="login-error"
            role="alert"
            class="mb-5 bg-[#FDF2F4] text-[#881337] rounded-[14px] px-4 py-3.5 flex items-start gap-3 text-sm border border-transparent shadow-[0_0_0_1px_oklch(0.55_0.18_12_/_0.22),0_4px_10px_-2px_oklch(0.45_0.16_12_/_0.1)]"
          >
            <UiIcon name="error" :size="18" :weight="600" class="mt-0.5 text-[#9F1239] shrink-0" />
            <span>{{ errorMessage }}</span>
          </div>
        </transition>

        <section class="ui-panel-elevated p-6 sm:p-9 ui-stagger ui-stagger-2" aria-labelledby="login-title">
          <div class="mb-7">
            <h2 id="login-title" class="text-2xl font-bold text-[#1C1917] tracking-tight">Masuk ke Portal</h2>
            <p class="text-sm text-[#57534E] mt-1.5 leading-relaxed">
              Gunakan email, username, atau nomor NIS siswa (untuk akun Orang Tua / Wali).
            </p>
          </div>

          <form class="flex flex-col gap-5" :aria-busy="isLoading" @submit.prevent="handleLogin">
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#44403C]" for="email">Username, Email, atau NIS Siswa</label>
              <input
                v-model="form.email"
                id="email"
                name="email"
                type="text"
                required
                autocomplete="username"
                autocapitalize="none"
                :spellcheck="false"
                placeholder="Contoh: nama@assajjad.com atau NIS siswa"
                class="ui-input"
                :aria-describedby="errorMessage ? 'login-error' : undefined"
              />
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
                  :aria-describedby="errorMessage ? 'login-error' : undefined"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                  :aria-pressed="showPassword"
                  aria-controls="password"
                  class="absolute right-1.5 top-1/2 -translate-y-1/2 min-h-[36px] min-w-[36px] flex items-center justify-center text-[#78716C] hover:text-[#1C1917] hover:bg-[#ECE8DF] rounded-[8px] transition-[color,background-color,transform] duration-150 ease-out enabled:active:scale-[0.96]"
                >
                  <span class="ui-icon-swap">
                    <span :class="showPassword ? 'ui-icon-swap-shown' : 'ui-icon-swap-hidden'">
                      <UiIcon name="visibility_off" :size="18" :weight="500" />
                    </span>
                    <span :class="showPassword ? 'ui-icon-swap-hidden' : 'ui-icon-swap-shown'">
                      <UiIcon name="visibility" :size="18" :weight="500" />
                    </span>
                  </span>
                </button>
              </div>
            </div>

            <label class="flex items-center gap-2.5 text-sm text-[#57534E] cursor-pointer select-none py-1">
              <input v-model="form.remember" type="checkbox" name="remember" class="w-4 h-4 rounded border-[#D6D0C4] text-[#A8551E] focus:ring-[#A8551E]" />
              <span>Ingat sesi login saya</span>
            </label>

            <button
              type="submit"
              :disabled="isLoading"
              class="ui-btn-primary ui-btn-icon-r w-full"
            >
              <UiIcon v-if="isLoading" name="progress_activity" :size="18" :weight="600" class="animate-spin" />
              <span>{{ isLoading ? 'Memverifikasi...' : 'Masuk ke Portal' }}</span>
              <UiIcon v-if="!isLoading" name="arrow_forward" :size="18" :weight="600" directional />
            </button>
          </form>

          <p class="mt-7 pt-5 border-t border-black/[0.06] text-xs text-center text-[#78716C] leading-relaxed">
            Butuh akun portal wali atau lupa kata sandi? Hubungi Tata Usaha Madrasah As-Sajjaad.
          </p>
        </section>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { getRoleHome } from '@/utils/auth';

const router = useRouter();
const showPassword = ref(false);
const errorMessage = ref('');
const isLoading = ref(false);
const form = reactive({ email: '', password: '', remember: false });

const handleLogin = async () => {
  if (isLoading.value) return;
  isLoading.value = true;
  errorMessage.value = '';
  try {
    const apiOrigin = new URL(axios.defaults.baseURL || '/api', window.location.origin).origin;
    await axios.get('/sanctum/csrf-cookie', { baseURL: apiOrigin });
    const { data } = await axios.post('/login', { ...form, email: form.email.trim() });
    const home = getRoleHome(data.user?.role);
    if (!home) throw new Error('Unsupported account role');
    localStorage.setItem('user', JSON.stringify(data.user));
    await router.replace(home);
  } catch (error) {
    const status = error.response?.status;
    errorMessage.value = status === 422 || status === 401
      ? 'Username, email, NIS, atau kata sandi salah.'
      : status === 419
        ? 'Sesi berakhir. Silakan masuk lagi.'
        : 'Tidak dapat masuk. Silakan coba lagi.';
  } finally {
    isLoading.value = false;
  }
};
</script>
