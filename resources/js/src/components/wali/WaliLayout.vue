<template>
  <div class="min-h-screen bg-[#F5F3EE] text-[#1C1917] flex flex-col">
    <!-- Top Institutional Bar -->
    <header class="bg-[#FDFCF9]/90 backdrop-blur-md border-b border-black/[0.07] sticky top-0 z-30">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 h-[68px] flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-10 h-10 rounded-[10px] bg-[#292524] text-white shadow-[0_1px_3px_oklch(0.2_0.02_55_/_0.3)] flex items-center justify-center font-bold text-sm shrink-0">
            AS
          </div>
          <div class="min-w-0">
            <p class="text-sm sm:text-base font-bold text-[#1C1917] truncate leading-tight">Madrasah As-Sajjaad</p>
            <p class="text-xs text-[#78716C] truncate">Portal Wali</p>
          </div>
        </div>

        <!-- Desktop Segmented Navigation (Concentric: 4px padding + 10px item radius = 14px outer) -->
        <nav class="hidden md:flex ui-segmented" aria-label="Navigasi Wali Siswa">
          <router-link to="/wali/dashboard" custom v-slot="{ href, navigate, isActive }">
            <a
              :href="href"
              @click="navigate"
              :class="[
                'ui-segmented-item ui-btn-icon-l',
                isActive ? 'ui-segmented-item-active text-[#A8551E]' : 'ui-segmented-item-idle hover:text-[#1C1917]'
              ]"
            >
              <UiIcon name="fact_check" :size="17" :weight="isActive ? 600 : 500" :filled="isActive" />
              <span>Status Infak</span>
            </a>
          </router-link>

          <router-link to="/wali/bayar" custom v-slot="{ href, navigate, isActive }">
            <a
              :href="href"
              @click="navigate"
              :class="[
                'ui-segmented-item ui-btn-icon-l',
                isActive
                  ? 'bg-[#A8551E] text-white shadow-[var(--shadow-btn-primary)]'
                  : 'ui-segmented-item-idle hover:text-[#1C1917]'
              ]"
            >
              <UiIcon name="qr_code_2" :size="17" :weight="isActive ? 600 : 500" />
              <span>Bayar Infak</span>
            </a>
          </router-link>

          <router-link to="/wali/riwayat" custom v-slot="{ href, navigate, isActive }">
            <a
              :href="href"
              @click="navigate"
              :class="[
                'ui-segmented-item ui-btn-icon-l',
                isActive ? 'ui-segmented-item-active text-[#A8551E]' : 'ui-segmented-item-idle hover:text-[#1C1917]'
              ]"
            >
              <UiIcon name="receipt_long" :size="17" :weight="isActive ? 600 : 500" :filled="isActive" />
              <span>Riwayat</span>
            </a>
          </router-link>
        </nav>

        <div class="flex items-center gap-2 sm:gap-3">
          <div class="hidden sm:flex flex-col text-right">
            <span class="text-xs font-bold text-[#1C1917]">{{ user?.name || 'Wali Siswa' }}</span>
            <span class="text-xs text-[#78716C]">Akun Wali Murid</span>
          </div>
          <button
            type="button"
            @click="openPasswordModal"
            title="Ubah kata sandi akun"
            aria-label="Ubah kata sandi akun"
            class="min-h-[40px] min-w-[40px] px-2.5 sm:px-3 rounded-[10px] border border-transparent bg-[#FDFCF9] shadow-[var(--shadow-border)] hover:shadow-[var(--shadow-border-hover)] hover:bg-[#F7F4ED] text-xs font-semibold text-[#44403C] flex items-center justify-center gap-1.5 transition-[background-color,color,box-shadow,transform] duration-150 ease-out enabled:active:scale-[0.96]"
          >
            <UiIcon name="key" :size="16" :weight="600" class="text-[#A8551E]" />
            <span class="hidden sm:inline">Sandi</span>
          </button>
          <button
            @click="handleLogout"
            title="Keluar dari akun"
            class="min-h-[40px] ps-3 pe-3.5 rounded-[10px] border border-transparent bg-[#FDFCF9] shadow-[var(--shadow-border)] hover:shadow-[var(--shadow-border-hover)] hover:bg-[#FDF2F4] hover:text-[#9F1239] text-xs font-semibold text-[#44403C] flex items-center gap-1.5 transition-[background-color,color,box-shadow,transform] duration-150 ease-out enabled:active:scale-[0.96]"
          >
            <UiIcon name="logout" :size="16" :weight="600" />
            <span class="hidden sm:inline">Keluar</span>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Page Body -->
    <main class="flex-1 w-full max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8 pb-28 md:pb-12">
      <router-view></router-view>
    </main>

    <!-- Mobile Bottom Navigation -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-[72px] bg-[#FDFCF9]/95 backdrop-blur-md border-t border-black/[0.07] px-3 flex justify-around items-center z-40">
      <router-link to="/wali/dashboard" custom v-slot="{ href, navigate, isActive }">
        <a
          :href="href"
          @click="navigate"
          :class="[
            'min-h-[48px] min-w-[78px] flex flex-col items-center justify-center gap-1 px-3 py-1.5 rounded-[12px] transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]',
            isActive ? 'text-[#A8551E] bg-[#F9EFE7] font-semibold' : 'text-[#78716C]'
          ]"
        >
          <UiIcon name="fact_check" :size="20" :weight="isActive ? 600 : 500" :filled="isActive" />
          <span class="text-xs">Status</span>
        </a>
      </router-link>

      <router-link to="/wali/bayar" custom v-slot="{ href, navigate }">
        <a
          :href="href"
          @click="navigate"
          class="min-h-[48px] px-5 py-1.5 flex flex-col items-center justify-center gap-1 rounded-[12px] bg-[#A8551E] text-white font-semibold shadow-[var(--shadow-btn-primary)] transition-transform duration-150 ease-out active:scale-[0.96]"
        >
          <UiIcon name="qr_code_2" :size="20" :weight="600" />
          <span class="text-xs">Bayar</span>
        </a>
      </router-link>

      <router-link to="/wali/riwayat" custom v-slot="{ href, navigate, isActive }">
        <a
          :href="href"
          @click="navigate"
          :class="[
            'min-h-[48px] min-w-[78px] flex flex-col items-center justify-center gap-1 px-3 py-1.5 rounded-[12px] transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]',
            isActive ? 'text-[#A8551E] bg-[#F9EFE7] font-semibold' : 'text-[#78716C]'
          ]"
        >
          <UiIcon name="receipt_long" :size="20" :weight="isActive ? 600 : 500" :filled="isActive" />
          <span class="text-xs">Riwayat</span>
        </a>
      </router-link>
    </nav>

    <!-- Modal Ubah Kata Sandi -->
    <div
      v-if="showPasswordModal"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-[#1C1917]/50 backdrop-blur-[2px] p-4 overscroll-contain"
      @click="closePasswordModal"
    >
      <div class="ui-panel-elevated w-full max-w-md overflow-hidden overscroll-contain" @click.stop>
        <div class="px-6 py-4 border-b border-black/[0.06] flex items-center justify-between gap-3">
          <div>
            <h2 class="text-base font-bold text-[#1C1917]">Ubah Kata Sandi</h2>
            <p class="text-xs text-[#78716C] mt-0.5">Sandi hanya diketahui oleh Anda dan tersimpan terenkripsi.</p>
          </div>
          <button
            type="button"
            aria-label="Tutup modal"
            @click="closePasswordModal"
            class="min-h-[38px] min-w-[38px] rounded-[9px] text-[#78716C] hover:text-[#1C1917] hover:bg-[#ECE8DF] flex items-center justify-center transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]"
          >
            <UiIcon name="close" :size="18" :weight="600" />
          </button>
        </div>

        <form class="p-6 space-y-4" autocomplete="off" @submit.prevent="submitChangePassword">
          <div
            v-if="passwordError"
            role="alert"
            class="p-3 rounded-[10px] bg-[#FDF2F4] border border-[#9F1239]/20 text-xs text-[#9F1239] font-medium"
          >
            {{ passwordError }}
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-[#44403C]" for="wali-current-pw">Kata Sandi Saat Ini</label>
            <div class="relative">
              <input
                id="wali-current-pw"
                name="pw_verify_old"
                v-model="pwForm.current_password"
                :type="showCurrentPw ? 'text' : 'password'"
                :readonly="!pwUnlocked"
                required
                autocomplete="new-password"
                data-lpignore="true"
                data-1p-ignore
                placeholder="Masukkan kata sandi saat ini"
                class="ui-input pr-11"
              />
              <button
                type="button"
                @click="showCurrentPw = !showCurrentPw"
                aria-label="Tampilkan sandi saat ini"
                class="absolute right-1.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-[7px] text-[#78716C] hover:text-[#1C1917] hover:bg-[#ECE8DF] flex items-center justify-center"
              >
                <UiIcon :name="showCurrentPw ? 'visibility_off' : 'visibility'" :size="17" />
              </button>
            </div>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-[#44403C]" for="wali-new-pw">Kata Sandi Baru</label>
            <div class="relative">
              <input
                id="wali-new-pw"
                name="pw_create_new"
                v-model="pwForm.password"
                :type="showNewPw ? 'text' : 'password'"
                :readonly="!pwUnlocked"
                required
                minlength="6"
                autocomplete="new-password"
                data-lpignore="true"
                data-1p-ignore
                placeholder="Minimal 6 karakter"
                class="ui-input pr-11"
              />
              <button
                type="button"
                @click="showNewPw = !showNewPw"
                aria-label="Tampilkan sandi baru"
                class="absolute right-1.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-[7px] text-[#78716C] hover:text-[#1C1917] hover:bg-[#ECE8DF] flex items-center justify-center"
              >
                <UiIcon :name="showNewPw ? 'visibility_off' : 'visibility'" :size="17" />
              </button>
            </div>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-[#44403C]" for="wali-confirm-pw">Konfirmasi Kata Sandi Baru</label>
            <input
              id="wali-confirm-pw"
              name="pw_confirm_new"
              v-model="pwForm.password_confirmation"
              :type="showNewPw ? 'text' : 'password'"
              :readonly="!pwUnlocked"
              required
              minlength="6"
              autocomplete="new-password"
              data-lpignore="true"
              data-1p-ignore
              placeholder="Ulangi kata sandi baru"
              class="ui-input"
            />
          </div>

          <div class="pt-2 flex gap-3">
            <button type="button" class="ui-btn-secondary flex-1" @click="closePasswordModal">Batal</button>
            <button type="submit" :disabled="isSavingPassword" class="ui-btn-primary flex-1">
              {{ isSavingPassword ? 'Menyimpan...' : 'Simpan Sandi' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { getStoredUser } from '@/utils/auth';
import { notify } from '@/utils/toast';

const router = useRouter();
const user = ref(getStoredUser());

const showPasswordModal = ref(false);
const showCurrentPw = ref(false);
const showNewPw = ref(false);
const pwUnlocked = ref(false);
const isSavingPassword = ref(false);
const passwordError = ref('');
const pwForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const openPasswordModal = () => {
  passwordError.value = '';
  pwForm.current_password = '';
  pwForm.password = '';
  pwForm.password_confirmation = '';
  showCurrentPw.value = false;
  showNewPw.value = false;
  pwUnlocked.value = false;
  showPasswordModal.value = true;
  setTimeout(() => {
    pwUnlocked.value = true;
  }, 150);
};

const closePasswordModal = () => {
  showPasswordModal.value = false;
};

const submitChangePassword = async () => {
  passwordError.value = '';
  if (pwForm.password.length < 6) {
    passwordError.value = 'Kata sandi baru minimal 6 karakter.';
    return;
  }
  if (pwForm.password !== pwForm.password_confirmation) {
    passwordError.value = 'Konfirmasi kata sandi baru tidak cocok.';
    return;
  }

  isSavingPassword.value = true;
  try {
    await axios.post('/wali/change-password', pwForm);
    closePasswordModal();
    notify('Kata sandi berhasil diubah.', 'success');
  } catch (err) {
    passwordError.value =
      err.response?.data?.errors?.password?.[0] ||
      err.response?.data?.message ||
      'Gagal mengubah kata sandi.';
  } finally {
    isSavingPassword.value = false;
  }
};

const handleLogout = async () => {
  try {
    await axios.post('/logout');
  } catch (err) {
    console.error(err);
  } finally {
    localStorage.removeItem('user');
    router.push('/login');
  }
};
</script>
