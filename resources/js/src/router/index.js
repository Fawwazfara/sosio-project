import { getRoleHome, getStoredUser } from '../utils/auth';
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

router.beforeEach((to) => {
  const user = getStoredUser();
  const home = getRoleHome(user?.role);
  if (!home) {
    localStorage.removeItem('user');
    return to.path === '/login' ? true : '/login';
  }
  if (to.path === '/login') return home;
  const namespace = home.split('/')[1];
  if (user.role === 'admin' && to.path.startsWith('/guru')) return true;
  if (to.path.split('/')[1] !== namespace) return home;
  return true;
});

export default router;
