<template>
  <div class="h-screen overflow-hidden bg-[#F5F3EE] text-[#1C1917] antialiased flex flex-col md:flex-row">
    <!-- Mobile Drawer Backdrop -->
    <transition
      enter-active-class="transition-opacity duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-150 ease-out"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isDrawerOpen"
        @click="isDrawerOpen = false"
        class="fixed inset-0 bg-[#1C1917]/40 backdrop-blur-[2px] z-40 md:hidden"
      ></div>
    </transition>

    <!-- Light Architectural Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 w-[272px] h-screen bg-[#FDFCF9] text-[#1C1917] z-50 border-r border-black/[0.07] transition-transform duration-200 ease-out-expo md:relative md:translate-x-0 flex flex-col shrink-0',
        isDrawerOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <div class="px-5 h-[68px] border-b border-black/[0.07] flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-10 h-10 bg-[#292524] rounded-[10px] shadow-[0_1px_3px_oklch(0.2_0.02_55_/_0.3)] flex items-center justify-center text-white font-bold text-sm shrink-0">
            AS
          </div>
          <div class="min-w-0">
            <p class="text-sm font-bold text-[#1C1917] leading-tight truncate">Madrasah As-Sajjaad</p>
            <p class="text-xs text-[#78716C] truncate">Tata Usaha &amp; Keuangan</p>
          </div>
        </div>
        <button
          @click="isDrawerOpen = false"
          aria-label="Tutup menu navigasi"
          class="min-h-[40px] min-w-[40px] flex items-center justify-center rounded-[10px] hover:bg-[#ECE8DF] text-[#57534E] md:hidden transition-[background-color,transform] duration-150 ease-out enabled:active:scale-[0.96]"
        >
          <UiIcon name="close" :size="20" :weight="500" />
        </button>
      </div>

      <nav @click="isDrawerOpen = false" class="p-3.5 flex flex-col gap-1 overflow-y-auto overscroll-contain flex-1">
        <router-link
          v-for="item in primaryLinks"
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
                ? 'bg-[#F9EFE7] text-[#A8551E] font-semibold shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.2)]'
                : 'text-[#57534E] hover:text-[#1C1917] hover:bg-[#F7F4ED] font-medium'
            ]"
          >
            <UiIcon :name="item.icon" :size="18" :weight="isActive ? 600 : 500" :filled="isActive" />
            <span class="truncate">{{ item.label }}</span>
          </a>
        </router-link>

        <div class="h-px bg-black/[0.07] my-2 mx-2"></div>

        <router-link
          v-for="item in secondaryLinks"
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
                ? 'bg-[#F9EFE7] text-[#A8551E] font-semibold shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.2)]'
                : 'text-[#57534E] hover:text-[#1C1917] hover:bg-[#F7F4ED] font-medium'
            ]"
          >
            <UiIcon :name="item.icon" :size="18" :weight="isActive ? 600 : 500" :filled="isActive" />
            <span class="truncate">{{ item.label }}</span>
          </a>
        </router-link>

        <div class="flex-grow"></div>

        <div class="pt-3 mt-3 border-t border-black/[0.07]">
          <button
            @click="handleLogout"
            class="min-h-[42px] flex items-center gap-3 ps-3 pe-3.5 py-2 rounded-[10px] hover:bg-[#FDF2F4] text-[#9F1239] text-sm font-semibold transition-[background-color,transform] duration-150 ease-out enabled:active:scale-[0.97] w-full"
          >
            <UiIcon name="logout" :size="18" :weight="600" />
            <span>Keluar Akun</span>
          </button>
        </div>
      </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
      <header class="bg-[#FDFCF9]/90 backdrop-blur-md border-b border-black/[0.07] sticky top-0 z-30 shrink-0">
        <div class="flex justify-between items-center w-full px-4 sm:px-6 lg:px-8 h-[68px] max-w-6xl mx-auto gap-3">
          <div class="flex items-center gap-3 min-w-0">
            <button
              @click="isDrawerOpen = true"
              aria-label="Buka menu navigasi"
              class="min-h-[42px] min-w-[42px] -ml-1 rounded-[10px] hover:bg-[#ECE8DF] text-[#1C1917] flex items-center justify-center md:hidden transition-[background-color,transform] duration-150 ease-out enabled:active:scale-[0.96]"
            >
              <UiIcon name="menu" :size="20" :weight="600" />
            </button>

            <div class="w-10 h-10 rounded-[10px] bg-[#F9EFE7] shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.2)] text-[#7C3A10] font-bold text-xs flex items-center justify-center shrink-0">
              {{ initials }}
            </div>
            <div class="min-w-0">
              <p class="text-sm sm:text-base font-bold text-[#1C1917] truncate leading-tight">
                {{ user?.name || 'Admin Tata Usaha' }}
              </p>
              <p class="text-xs text-[#78716C] truncate">Administrasi Madrasah</p>
            </div>
          </div>

          <router-link
            to="/admin/setoran"
            class="min-h-[40px] ps-3 pe-3.5 rounded-[10px] border border-transparent bg-[#FDFCF9] shadow-[var(--shadow-border)] hover:shadow-[var(--shadow-border-hover)] text-xs font-semibold text-[#1C1917] flex items-center gap-2 shrink-0 transition-[box-shadow,transform] duration-150 ease-out active:scale-[0.96]"
          >
            <UiIcon name="qr_code_scanner" :size="17" :weight="600" class="text-[#A8551E]" />
            <span class="hidden sm:inline">Cek setoran</span>
          </router-link>
        </div>
      </header>

      <div class="flex-1 overflow-y-auto w-full relative">
        <router-view></router-view>
      </div>
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
const isDrawerOpen = ref(false);
const user = ref(getStoredUser());

const primaryLinks = [
  { to: '/admin/dashboard', icon: 'dashboard', label: 'Dashboard' },
  { to: '/admin/siswa', icon: 'group', label: 'Siswa & Wali' },
  { to: '/admin/setoran', icon: 'qr_code_scanner', label: 'Setoran' },
  { to: '/admin/guru', icon: 'badge', label: 'Guru' },
];

const secondaryLinks = [
  { to: '/admin/infak/create', icon: 'volunteer_activism', label: 'Catat Infak' },
  { to: '/admin/pengeluaran', icon: 'money_off', label: 'Pengeluaran Kas' },
  { to: '/admin/laporan', icon: 'picture_as_pdf', label: 'Laporan Kas' },
  { to: '/admin/pesan', icon: 'mail', label: 'Pesan WhatsApp' },
  { to: '/admin/riwayat', icon: 'history', label: 'Aktivitas' },
];

const initials = computed(() => {
  const name = user.value?.name || 'AD';
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
