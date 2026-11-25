<template>
  <div
    class="education-shell"
    :class="{ 'is-sidebar-collapsed': sidebarCollapsed && !isMobile }"
  >
    <div
      v-if="drawerOpen"
      class="edu-backdrop"
      @click="drawerOpen = false"
    ></div>
    <aside
      id="education-navigation"
      ref="sidebar"
      class="edu-sidebar"
      :class="{ 'is-open': drawerOpen }"
      aria-label="Navigasi utama"
      :role="drawerActive ? 'dialog' : undefined"
      :aria-modal="drawerActive ? true : undefined"
      :inert="isMobile && !drawerOpen ? '' : undefined"
    >
      <router-link
        :to="`${base}/dashboard`"
        class="edu-brand"
        aria-label="As-Sajjaad, buka dashboard"
        @click="drawerOpen = false"
      >
        <UiIcon name="school" :size="34" class="edu-brand-mark" />
        <span
          ><strong>As-Sajjaad<span class="text-blue-500">.</span></strong></span
        >
      </router-link>
      <button
        ref="drawerClose"
        class="edu-close"
        aria-label="Tutup menu navigasi"
        @click="drawerOpen = false"
      >
        <UiIcon name="close" />
      </button>
      <nav class="edu-nav" @click="drawerOpen = false">
        <router-link
          v-for="item in links"
          :key="item.to"
          :to="item.to"
          active-class="is-active"
          :aria-label="item.label"
          :title="sidebarCollapsed && !isMobile ? item.label : undefined"
        >
          <UiIcon :name="item.icon" :size="20" /><span>{{ item.label }}</span>
        </router-link>
      </nav>
      <div class="edu-sidebar-bottom">
        <div class="edu-sidebar-user">
          <span class="edu-avatar">{{ initials }}</span>
          <div>
            <strong>{{ user?.name || 'Pengguna' }}</strong
            ><small>{{ isAdmin ? 'Administrator' : 'Wali kelas' }}</small>
          </div>
          <button @click="handleLogout" aria-label="Keluar akun">
            <UiIcon name="logout" :size="19" />
          </button>
        </div>
      </div>
    </aside>
    <div class="edu-workspace" :inert="drawerActive ? '' : undefined">
      <header class="edu-topbar">
        <button
          v-if="!isMobile"
          class="edu-collapse-toggle"
          :aria-label="sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'"
          :title="sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'"
          :aria-expanded="!sidebarCollapsed"
          aria-controls="education-navigation"
          @click="sidebarCollapsed = !sidebarCollapsed"
        >
          <UiIcon
            :name="sidebarCollapsed ? 'chevron_right' : 'chevron_left'"
            :size="22"
            :weight="600"
          />
        </button>
        <button
          ref="menuButton"
          class="edu-menu"
          aria-label="Buka menu navigasi"
          aria-controls="education-navigation"
          :aria-expanded="drawerOpen"
          @click="drawerOpen = !drawerOpen"
        >
          <UiIcon name="menu" :size="22" />
        </button>
        <router-link :to="`${base}/siswa`" class="edu-search-link"
          ><UiIcon name="search" :size="19" /><span>Cari siswa</span
          ><span class="edu-search-arrow"
            ><UiIcon name="arrow_forward" :size="15" /></span
        ></router-link>
        <div class="edu-topbar-right">
          <span class="edu-today">{{ today }}</span>
          <router-link
            :to="`${base}/pesan`"
            class="edu-notification"
            aria-label="Buka pesan"
            ><UiIcon name="mail" :size="20"
          /></router-link>
          <span class="edu-avatar edu-top-avatar">{{ initials }}</span>
        </div>
      </header>
      <div ref="scrollArea" class="edu-scroll-area"><router-view /></div>
    </div>
    <nav
      v-if="!isAdmin"
      class="edu-bottom-nav"
      aria-label="Navigasi seluler"
      :inert="drawerActive ? '' : undefined"
    >
      <router-link
        v-for="item in links"
        :key="item.to"
        :to="item.to"
        active-class="is-active"
        ><UiIcon :name="item.icon" :size="21" /><span>{{
          item.shortLabel || item.label
        }}</span></router-link
      >
    </nav>
  </div>
</template>

<script setup>
import {
  computed,
  ref,
  watch,
  nextTick,
  onMounted,
  onBeforeUnmount,
} from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { getStoredUser } from '@/utils/auth';
import { subscribeToPushNotifications } from '@/utils/pushHelper';

const props = defineProps({ role: { type: String, required: true } });
const router = useRouter();
const route = useRoute();
const scrollArea = ref(null);
watch(
  () => route.fullPath,
  async () => {
    await nextTick();
    scrollArea.value?.scrollTo(0, 0);
  },
);
const drawerOpen = ref(false);
const sidebarCollapsed = ref(false);
try {
  sidebarCollapsed.value = localStorage.getItem('sidebar-collapsed') === 'true';
} catch {
  /* Keep the default when browser storage is unavailable. */
}
watch(sidebarCollapsed, (collapsed) => {
  try {
    localStorage.setItem('sidebar-collapsed', String(collapsed));
  } catch {
    /* The current layout still works without persistence. */
  }
});
const mobileQuery = window.matchMedia('(max-width: 767px)');
const isMobile = ref(mobileQuery.matches);
const drawerActive = computed(() => drawerOpen.value && isMobile.value);
const sidebar = ref(null);
const menuButton = ref(null);
const drawerClose = ref(null);
watch(drawerActive, async (active) => {
  await nextTick();
  if (active) drawerClose.value?.focus();
  else if (isMobile.value) menuButton.value?.focus();
});
const user = getStoredUser();
const isAdmin = computed(() => props.role === 'admin');
const base = computed(() => (isAdmin.value ? '/admin' : '/guru'));
const initials = (user?.name || 'AS')
  .split(' ')
  .map((word) => word[0])
  .slice(0, 2)
  .join('')
  .toUpperCase();
const today = new Intl.DateTimeFormat('id-ID', {
  day: 'numeric',
  month: 'long',
  year: 'numeric',
}).format(new Date());
const links = computed(() =>
  isAdmin.value
    ? [
        { to: '/admin/dashboard', icon: 'dashboard', label: 'Dashboard' },
        { to: '/admin/siswa', icon: 'groups', label: 'Siswa & wali' },
        { to: '/admin/guru', icon: 'badge', label: 'Data guru' },
        {
          to: '/admin/setoran',
          icon: 'payments',
          label: 'Setoran masuk',
        },
        {
          to: '/admin/infak/create',
          icon: 'volunteer_activism',
          label: 'Catat infak',
        },
        {
          to: '/admin/pengeluaran',
          icon: 'account_balance_wallet',
          label: 'Pengeluaran',
        },
        {
          to: '/admin/laporan',
          icon: 'picture_as_pdf',
          label: 'Laporan kas',
        },
        { to: '/admin/pesan', icon: 'mail', label: 'Pesan WhatsApp' },
        {
          to: '/admin/riwayat',
          icon: 'history',
          label: 'Riwayat aktivitas',
        },
      ]
    : [
        {
          to: '/guru/dashboard',
          icon: 'dashboard',
          label: 'Dashboard',
          shortLabel: 'Beranda',
        },
        {
          to: '/guru/siswa',
          icon: 'groups',
          label: 'Kelas & siswa',
          shortLabel: 'Siswa',
        },
        {
          to: '/guru/setoran',
          icon: 'payments',
          label: 'Setoran tunai',
          shortLabel: 'Setoran',
        },
        {
          to: '/guru/pesan',
          icon: 'mail',
          label: 'Pesan admin',
          shortLabel: 'Pesan',
        },
      ],
);
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
const onKeydown = (event) => {
  if (!drawerActive.value) return;
  if (event.key === 'Escape') drawerOpen.value = false;
  if (event.key === 'Tab') {
    const controls = sidebar.value.querySelectorAll(
      'a[href], button:not(:disabled)',
    );
    const first = controls[0];
    const last = controls[controls.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }
};
const onViewportChange = (event) => {
  isMobile.value = event.matches;
  if (!event.matches) drawerOpen.value = false;
};
onMounted(() => {
  subscribeToPushNotifications();
  window.addEventListener('keydown', onKeydown);
  mobileQuery.addEventListener('change', onViewportChange);
});
onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeydown);
  mobileQuery.removeEventListener('change', onViewportChange);
});
</script>
