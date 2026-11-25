<template>
  <div class="min-h-screen bg-[#f2f7fd] text-[#172b4d] flex flex-col">
    <!-- Top Institutional Bar -->
    <header class="bg-[#ffffff]/90 backdrop-blur-md border-b border-black/[0.07] sticky top-0 z-30">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 h-[68px] flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
          <UiIcon name="school" :size="34" class="shrink-0 text-[#1667c9]" />
          <div class="min-w-0">
            <p class="text-sm sm:text-base font-bold text-[#172b4d] truncate leading-tight">Madrasah As-Sajjaad</p>
            <p class="text-xs text-[#52657d] truncate">Portal Wali</p>
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
                isActive ? 'ui-segmented-item-active text-[#1264c7]' : 'ui-segmented-item-idle hover:text-[#172b4d]'
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
                  ? 'bg-[#1264c7] text-white shadow-[var(--shadow-btn-primary)]'
                  : 'ui-segmented-item-idle hover:text-[#172b4d]'
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
                isActive ? 'ui-segmented-item-active text-[#1264c7]' : 'ui-segmented-item-idle hover:text-[#172b4d]'
              ]"
            >
              <UiIcon name="receipt_long" :size="17" :weight="isActive ? 600 : 500" :filled="isActive" />
              <span>Riwayat</span>
            </a>
          </router-link>
        </nav>

        <div class="flex items-center gap-3">
          <div class="hidden sm:flex flex-col text-right">
            <span class="text-xs font-bold text-[#172b4d]">{{ user?.name || 'Wali Siswa' }}</span>
            <span class="text-xs text-[#52657d]">Akun Wali Murid</span>
          </div>
          <button
            @click="handleLogout"
            title="Keluar dari akun"
            class="min-h-[40px] ps-3 pe-3.5 rounded-[10px] border border-transparent bg-[#ffffff] shadow-[var(--shadow-border)] hover:shadow-[var(--shadow-border-hover)] hover:bg-[#FDF2F4] hover:text-[#9F1239] text-xs font-semibold text-[#354c68] flex items-center gap-1.5 transition-[background-color,color,box-shadow,transform] duration-150 ease-out enabled:active:scale-[0.96]"
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
    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-[72px] bg-[#ffffff]/95 backdrop-blur-md border-t border-black/[0.07] px-3 flex justify-around items-center z-40">
      <router-link to="/wali/dashboard" custom v-slot="{ href, navigate, isActive }">
        <a
          :href="href"
          @click="navigate"
          :class="[
            'min-h-[48px] min-w-[78px] flex flex-col items-center justify-center gap-1 px-3 py-1.5 rounded-[12px] transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]',
            isActive ? 'text-[#1264c7] bg-[#e8f2ff] font-semibold' : 'text-[#52657d]'
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
          class="min-h-[48px] px-5 py-1.5 flex flex-col items-center justify-center gap-1 rounded-[12px] bg-[#1264c7] text-white font-semibold shadow-[var(--shadow-btn-primary)] transition-transform duration-150 ease-out active:scale-[0.96]"
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
            isActive ? 'text-[#1264c7] bg-[#e8f2ff] font-semibold' : 'text-[#52657d]'
          ]"
        >
          <UiIcon name="receipt_long" :size="20" :weight="isActive ? 600 : 500" :filled="isActive" />
          <span class="text-xs">Riwayat</span>
        </a>
      </router-link>
    </nav>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { getStoredUser } from '@/utils/auth';

const router = useRouter();
const user = ref(getStoredUser());

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
