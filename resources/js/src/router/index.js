import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '../views/auth/LoginView.vue';
import AdminLayout from '../components/admin/AdminLayout.vue';
import DashboardView from '../views/admin/DashboardView.vue';
import SiswaListView from '../views/admin/SiswaListView.vue';
import SiswaFormView from '../views/admin/SiswaFormView.vue';
import InfakFormView from '../views/admin/InfakFormView.vue';
import GuruListView from '../views/admin/GuruListView.vue';
import PengeluaranView from '../views/admin/PengeluaranView.vue';
import LaporanView from '../views/admin/LaporanView.vue';
import PesanView from '../views/admin/PesanView.vue';
import RiwayatView from '../views/admin/RiwayatView.vue';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/',
      redirect: '/login',
    },
    {
      path: '/admin',
      component: AdminLayout,
      children: [
        { path: '', redirect: '/admin/dashboard' },
        { path: 'dashboard', name: 'admin.dashboard', component: DashboardView },
        { path: 'siswa', name: 'admin.siswa', component: SiswaListView },
        { path: 'siswa/create', name: 'admin.siswa.create', component: SiswaFormView },
        { path: 'siswa/:id/edit', name: 'admin.siswa.edit', component: SiswaFormView },
        { path: 'infak/create', name: 'admin.infak.create', component: InfakFormView },
        {
          path: 'setoran',
          name: 'admin.setoran',
          component: () => import('../views/shared/SetoranManageView.vue'),
        },
        { path: 'guru', name: 'admin.guru', component: GuruListView },
        { path: 'pengeluaran', name: 'admin.pengeluaran', component: PengeluaranView },
        { path: 'laporan', name: 'admin.laporan', component: LaporanView },
        { path: 'pesan', name: 'admin.pesan', component: PesanView },
        { path: 'riwayat', name: 'admin.riwayat', component: RiwayatView },
      ],
    },
    {
      path: '/guru',
      component: () => import('../components/guru/GuruLayout.vue'),
      children: [
        { path: '', redirect: '/guru/dashboard' },
        {
          path: 'dashboard',
          name: 'guru.dashboard',
          component: () => import('../views/guru/DashboardView.vue'),
        },
        {
          path: 'siswa',
          name: 'guru.siswa',
          component: () => import('../views/guru/SiswaListView.vue'),
        },
        {
          path: 'setoran',
          name: 'guru.setoran',
          component: () => import('../views/shared/SetoranManageView.vue'),
        },
        {
          path: 'pesan',
          name: 'guru.pesan',
          component: () => import('../views/guru/PesanView.vue'),
        },
      ],
    },
    {
      path: '/wali',
      component: () => import('../components/wali/WaliLayout.vue'),
      children: [
        { path: '', redirect: '/wali/dashboard' },
        {
          path: 'dashboard',
          name: 'wali.dashboard',
          component: () => import('../views/wali/DashboardView.vue'),
        },
        {
          path: 'bayar',
          name: 'wali.bayar',
          component: () => import('../views/wali/BayarView.vue'),
        },
        {
          path: 'riwayat',
          name: 'wali.riwayat',
          component: () => import('../views/wali/RiwayatView.vue'),
        },
      ],
    },
  ],
});

router.beforeEach((to, from, next) => {
  const publicPages = ['/login'];
  const authRequired = !publicPages.includes(to.path);
  let user = null;
  try {
    user = JSON.parse(localStorage.getItem('user') || 'null');
  } catch {
    user = null;
  }

  if (authRequired && !user) {
    return next('/login');
  }

  if (to.path === '/login' && user?.role) {
    if (user.role === 'admin') return next('/admin/dashboard');
    if (user.role === 'guru') return next('/guru/dashboard');
    if (user.role === 'wali') return next('/wali/dashboard');
  }

  // Enforce role namespace on client router
  if (user?.role === 'wali' && (to.path.startsWith('/admin') || to.path.startsWith('/guru'))) {
    return next('/wali/dashboard');
  }
  if (user?.role === 'guru' && (to.path.startsWith('/admin') || to.path.startsWith('/wali'))) {
    return next('/guru/dashboard');
  }
  if (user?.role === 'admin' && to.path.startsWith('/wali')) {
    return next('/admin/dashboard');
  }

  next();
});

export default router;
