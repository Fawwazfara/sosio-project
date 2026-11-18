<template>
  <div class="flex h-screen bg-[#F2F4F8] text-[#0F172A] overflow-hidden relative">
    <!-- Mobile Bottom Navigation -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-[72px] bg-[#FFFFFF]/95 backdrop-blur-md border-t border-black/[0.07] px-2 flex justify-around items-center z-50">
      <router-link
        v-for="item in navLinks"
        :key="item.to"
        :to="item.to"
        custom
        v-slot="{ href, navigate, isActive }"
      >
        <a
          :href="href"
          @click="navigate"
          :class="[
            'min-h-[48px] min-w-[68px] flex flex-col items-center justify-center gap-1 rounded-[12px] transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]',
            isActive ? 'text-[#1D4ED8] bg-[#EFF6FF] font-semibold' : 'text-[#64748B]'
          ]"
        >
          <UiIcon :name="item.icon" :size="20" :weight="isActive ? 600 : 500" :filled="isActive" />
          <span class="text-xs">{{ item.shortLabel }}</span>
        </a>
      </router-link>
    </nav>

    <!-- Light Architectural Sidebar -->
    <aside class="hidden md:flex flex-col w-[268px] bg-[#FFFFFF] text-[#0F172A] border-r border-black/[0.07] flex-shrink-0 z-20">
      <div class="px-5 h-[68px] border-b border-black/[0.07] flex items-center gap-3">
        <div class="w-10 h-10 bg-[#163A70] rounded-[10px] shadow-[0_1px_3px_oklch(0.2_0.02_55_/_0.3)] flex items-center justify-center text-white font-bold text-sm shrink-0">
          AS
        </div>
        <div class="min-w-0">
          <p class="text-sm font-bold text-[#0F172A] leading-tight truncate">Madrasah As-Sajjaad</p>
          <p class="text-xs text-[#64748B] truncate">Panel Wali Kelas</p>
        </div>
      </div>

      <nav class="flex-1 p-3.5 flex flex-col gap-1 overflow-y-auto overscroll-contain">
        <router-link
          v-for="item in mainLinks"
          :key="item.to"
          :to="item.to"
          custom
          v-slot="{ href, navigate, isActive }"
        >
          <a
            :href="href"
            @click="navigate"
            :class="[
              'min-h-[42px] flex items-center gap-3 ps-3 pe-3.5 py-2 rounded-[10px] text-sm transition-[background-color,color,box-shadow,transform] duration-150 ease-out active:scale-[0.97]',
              isActive
                ? 'bg-[#EFF6FF] text-[#1D4ED8] font-semibold shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.2)]'
                : 'text-[#475569] hover:text-[#0F172A] hover:bg-[#F8FAFC] font-medium'
            ]"
          >
            <UiIcon :name="item.icon" :size="18" :weight="isActive ? 600 : 500" :filled="isActive" />
            <span class="truncate">{{ item.label }}</span>
          </a>
        </router-link>

        <div class="h-px bg-black/[0.07] my-2 mx-2"></div>

        <router-link to="/guru/pesan" custom v-slot="{ href, navigate, isActive }">
          <a
            :href="href"
            @click="navigate"
            :class="[
              'min-h-[42px] flex items-center gap-3 ps-3 pe-3.5 py-2 rounded-[10px] text-sm transition-[background-color,color,box-shadow,transform] duration-150 ease-out active:scale-[0.97]',
              isActive
                ? 'bg-[#EFF6FF] text-[#1D4ED8] font-semibold shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.2)]'
                : 'text-[#475569] hover:text-[#0F172A] hover:bg-[#F8FAFC] font-medium'
            ]"
          >
            <UiIcon name="mail" :size="18" :weight="isActive ? 600 : 500" :filled="isActive" />
            <span>Pesan Admin</span>
          </a>
        </router-link>

        <div class="flex-grow"></div>

        <div class="pt-3 mt-3 border-t border-black/[0.07]">
          <button
            @click="handleLogout"
            class="min-h-[42px] flex items-center gap-3 ps-3 pe-3.5 py-2 rounded-[10px] hover:bg-[#FFF1F2] text-[#9F1239] text-sm font-semibold transition-[background-color,transform] duration-150 ease-out enabled:active:scale-[0.97] w-full"
          >
            <UiIcon name="logout" :size="18" :weight="600" />
            <span>Keluar Akun</span>
          </button>
        </div>
      </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 h-full relative">
      <!-- Mobile Header -->
      <header class="md:hidden flex h-[64px] bg-[#FFFFFF]/90 backdrop-blur-md border-b border-black/[0.07] flex-shrink-0 items-center justify-between px-4 z-10 sticky top-0">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-9 h-9 bg-[#163A70] rounded-[9px] flex items-center justify-center text-white text-xs font-bold shrink-0">
            {{ initials }}
          </div>
          <div class="min-w-0">
            <p class="text-sm font-bold text-[#0F172A] truncate capitalize">
              {{ $route.name ? $route.name.replace('guru.', '') : 'Dashboard' }}
            </p>
            <p class="text-xs text-[#64748B] truncate">{{ user?.name || 'Wali Kelas' }}</p>
          </div>
        </div>
        <button
          @click="handleLogout"
          aria-label="Keluar"
          class="min-h-[40px] min-w-[40px] flex items-center justify-center rounded-[10px] hover:bg-[#FFF1F2] text-[#9F1239] transition-[background-color,transform] duration-150 ease-out enabled:active:scale-[0.96]"
        >
          <UiIcon name="logout" :size="19" :weight="600" />
        </button>
      </header>

      <!-- Desktop Header -->
      <header class="hidden md:flex h-[68px] bg-[#FFFFFF]/90 backdrop-blur-md border-b border-black/[0.07] flex-shrink-0 items-center justify-between px-6 lg:px-8 z-10 sticky top-0">
        <div>
          <p class="text-base font-bold text-[#0F172A] leading-tight">Panel Wali Kelas</p>
          <p class="text-xs text-[#64748B]">Pantauan kelas dan verifikasi setoran tunai orang tua</p>
        </div>
        <div class="flex items-center gap-3">
          <router-link
            to="/guru/setoran"
            class="min-h-[40px] ps-3 pe-3.5 rounded-[10px] border border-transparent bg-[#FFFFFF] shadow-[var(--shadow-border)] hover:shadow-[var(--shadow-border-hover)] text-xs font-semibold text-[#0F172A] flex items-center gap-2 transition-[box-shadow,transform] duration-150 ease-out active:scale-[0.96]"
          >
            <UiIcon name="payments" :size="17" :weight="600" class="text-[#1D4ED8]" />
            Konfirmasi Tunai Wali
          </router-link>
          <div class="flex items-center gap-2.5 pl-3 border-l border-black/[0.07]">
            <div class="w-9 h-9 rounded-[9px] bg-[#EFF6FF] shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.2)] text-[#1E3A8A] flex items-center justify-center font-bold text-xs">
              {{ initials }}
            </div>
            <div class="flex flex-col">
              <span class="text-xs font-bold text-[#0F172A]">{{ user?.name || 'Akun Guru' }}</span>
              <span class="text-xs text-[#64748B]">Wali Kelas</span>
            </div>
          </div>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto relative">
        <router-view></router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { subscribeToPushNotifications } from '@/utils/pushHelper';
import { getStoredUser } from '@/utils/auth';

const router = useRouter();
const user = ref(getStoredUser());

const mainLinks = [
  { to: '/guru/dashboard', icon: 'dashboard', label: 'Dashboard' },
  { to: '/guru/siswa', icon: 'groups', label: 'Kelas & Siswa' },
  { to: '/guru/setoran', icon: 'payments', label: 'Setoran Tunai Wali' },
];

const navLinks = [
  { to: '/guru/dashboard', icon: 'dashboard', shortLabel: 'Beranda' },
  { to: '/guru/siswa', icon: 'groups', shortLabel: 'Siswa' },
  { to: '/guru/setoran', icon: 'payments', shortLabel: 'Setoran' },
  { to: '/guru/pesan', icon: 'mail', shortLabel: 'Pesan' },
];

const initials = computed(() => {
  const name = user.value?.name || 'GR';
  return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
});

const handleLogout = async () => {
  try {
    await axios.post('/logout');
  } catch (error) {
    console.error('Logout error', error);
  } finally {
    localStorage.removeItem('user');
    router.push('/login');
  }
};

onMounted(() => {
  subscribeToPushNotifications();
});
</script>
