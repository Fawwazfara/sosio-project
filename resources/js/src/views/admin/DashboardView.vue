<template>
  <main class="edu-dashboard">
    <div class="edu-page-heading">
      <div>
        <h1>Dashboard</h1>
      </div>
      <router-link to="/admin/laporan" class="ui-btn-secondary"
        ><UiIcon name="picture_as_pdf" :size="17" /> Laporan kas</router-link
      >
    </div>
    <div v-if="loadError" role="alert" class="edu-data-error">
      {{ loadError }}<button @click="loadDashboard">Coba lagi</button>
    </div>
    <div class="edu-dashboard-grid">
      <div class="edu-main-column">
        <EducationHero
          :name="firstName"
          action="Kelola siswa & wali"
          to="/admin/siswa"
        />
        <section aria-labelledby="admin-stat-title">
          <div class="edu-section-title">
            <h2 id="admin-stat-title">Ringkasan kas</h2>
          </div>
          <div class="edu-stats-grid">
            <article class="edu-stat edu-stat-blue">
              <span class="edu-stat-icon"
                ><UiIcon name="account_balance_wallet" :size="21"
              /></span>
              <p>Kas masuk</p>
              <strong>Rp {{ formatRupiah(stats.pemasukan_all_time) }}</strong
              ><small
                ><UiIcon name="trending_up" :size="14" /> Bulan ini Rp
                {{ formatRupiah(stats.pemasukan_bulan_ini) }}</small
              >
            </article>
            <article class="edu-stat edu-stat-peach">
              <span class="edu-stat-icon"
                ><UiIcon name="money_off" :size="21"
              /></span>
              <p>Pengeluaran bulan ini</p>
              <strong>Rp {{ formatRupiah(stats.pengeluaran_bulan_ini) }}</strong
              ><small
                >{{ stats.persen_pengeluaran >= 0 ? '+' : ''
                }}{{ stats.persen_pengeluaran }}% dari bulan lalu</small
              >
            </article>
            <article class="edu-stat edu-stat-teal">
              <span class="edu-stat-icon"
                ><UiIcon name="savings" :size="21"
              /></span>
              <p>Saldo kas</p>
              <strong>Rp {{ formatRupiah(stats.saldo_all_time) }}</strong>
            </article>
          </div>
        </section>
        <section class="edu-chart-card">
          <div class="edu-section-title">
            <div>
              <h2>Arus kas</h2>
              <p>6 bulan terakhir</p>
            </div>
            <div class="edu-chart-legend">
              <span><i class="bg-blue-500"></i>Masuk</span
              ><span><i class="bg-[#C03961]"></i>Keluar</span>
            </div>
          </div>
          <div v-if="chartReady" class="edu-chart">
            <Line :data="chartData" :options="chartOptions" />
          </div>
          <div v-else class="edu-chart-placeholder" role="status">
            {{ loadError ? 'Grafik belum tersedia' : 'Memuat ringkasan kas…' }}
          </div>
        </section>
        <section>
          <div class="edu-section-title">
            <h2>Akses cepat</h2>
          </div>
          <div class="edu-quick-grid">
            <router-link to="/admin/siswa" class="edu-quick-link"
              ><span class="edu-quick-icon bg-blue-100 text-blue-600"
                ><UiIcon name="groups" :size="22" /></span
              ><span><strong>Siswa & wali</strong></span
              ><UiIcon name="arrow_forward" :size="18" /></router-link
            ><router-link to="/admin/guru" class="edu-quick-link"
              ><span class="edu-quick-icon bg-teal-100 text-teal-600"
                ><UiIcon name="badge" :size="22" /></span
              ><span><strong>Data guru</strong></span
              ><UiIcon name="arrow_forward" :size="18"
            /></router-link>
          </div>
        </section>
      </div>
      <aside class="edu-right-column" aria-label="Pantauan dan tindakan">
        <section class="edu-side-card">
          <div class="edu-section-title">
            <h2>Kepatuhan infak</h2>
            <span class="edu-small-badge">{{
              currentMonth || 'Bulan ini'
            }}</span>
          </div>
          <template v-if="klassesData.length"
            ><div class="edu-donut">
              <Doughnut
                :data="klassesData[currentSlide].pieData"
                :options="pieOptions"
                :key="'pie-' + currentSlide"
              />
              <div>
                <strong
                  >{{ klassesData[currentSlide].persentase
                  }}<small>%</small></strong
                ><span>Sudah lunas</span>
              </div>
            </div>
            <div class="edu-class-switch">
              <button
                @click="prevSlide"
                :disabled="currentSlide === 0"
                aria-label="Kelas sebelumnya"
              >
                <UiIcon name="chevron_left" :size="18" /></button
              ><strong>{{ klassesData[currentSlide].nama_kelas }}</strong
              ><button
                @click="nextSlide"
                :disabled="currentSlide === klassesData.length - 1"
                aria-label="Kelas berikutnya"
              >
                <UiIcon name="chevron_right" :size="18" />
              </button>
            </div>
            <div class="edu-payment-counts">
              <span
                ><i class="bg-blue-500"></i
                >{{ klassesData[currentSlide].lunas }} lunas</span
              ><span
                ><i class="bg-slate-200"></i
                >{{ klassesData[currentSlide].nunggak }} belum</span
              >
            </div></template
          >
          <p v-else class="edu-empty">
            {{
              loadError ? 'Data kelas belum tersedia.' : 'Memuat data kelas…'
            }}
          </p>
        </section>
        <section class="edu-side-card">
          <div class="edu-section-title">
            <h2>Pantauan kelas</h2>
            <router-link to="/admin/siswa" aria-label="Lihat semua siswa"
              ><UiIcon name="arrow_forward" :size="17"
            /></router-link>
          </div>
          <div class="edu-class-list">
            <router-link
              v-for="(kelas, index) in klassesData.slice(0, 4)"
              :key="kelas.nama_kelas"
              to="/admin/siswa"
              ><span
                class="edu-class-number"
                :class="['class-tone-' + index]"
                >{{ index + 1 }}</span
              ><span
                ><strong>{{ kelas.nama_kelas }}</strong
                ><small
                  >{{ kelas.lunas + kelas.nunggak }} siswa ·
                  {{ kelas.lunas }} lunas</small
                ></span
              ><b>{{ kelas.persentase }}%</b></router-link
            >
          </div>
        </section>
        <section class="edu-reminder-card">
          <span class="edu-reminder-icon"
            ><UiIcon name="notifications_active" :size="25"
          /></span>
          <h2>Setoran &amp; pengingat</h2>
          <router-link to="/admin/setoran" class="ui-btn-primary"
            >Cek setoran<UiIcon name="arrow_forward" :size="17"
          /></router-link>
          <div v-if="confirmReminder" class="edu-confirm-actions">
            <p>Kirim pengingat ke semua guru?</p>
            <button
              @click="executeReminder"
              :disabled="isSending"
              class="ui-btn-secondary"
            >
              {{ isSending ? 'Mengirim…' : 'Ya, kirim pengingat' }}</button
            ><button
              @click="confirmReminder = false"
              :disabled="isSending"
              class="edu-text-button"
            >
              Batal
            </button>
          </div>
          <button
            v-else
            @click="confirmReminder = true"
            class="edu-text-button"
          >
            <UiIcon name="send" :size="15" /> Ingatkan guru
          </button>
        </section>
      </aside>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import EducationHero from '@/components/shared/EducationHero.vue';
import { getStoredUser } from '@/utils/auth';

const user = getStoredUser();
const firstName =
  user?.name?.split(' ').slice(0, 2).join(' ') || 'Sahabat madrasah';
const loadError = ref('');
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  ArcElement,
  Title,
  Tooltip,
  Filler,
} from 'chart.js';
import { Line, Doughnut } from 'vue-chartjs';
import { notify } from '@/utils/toast';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  ArcElement,
  Title,
  Tooltip,
  Filler,
);

const stats = ref({
  pemasukan_all_time: 0,
  pemasukan_bulan_ini: 0,
  pengeluaran_bulan_ini: 0,
  saldo_all_time: 0,
  persen_pemasukan: 0,
  persen_pengeluaran: 0,
});

const chartReady = ref(false);
const isSending = ref(false);
const confirmReminder = ref(false);

const executeReminder = async () => {
  isSending.value = true;
  try {
    const res = await axios.post('/push-send-reminders');
    confirmReminder.value = false;
    notify(
      res.data.message ||
        'Notifikasi pengingat berhasil dikirim ke wali kelas.',
      'success',
    );
  } catch (err) {
    console.error(err);
    notify('Gagal mengirim notifikasi pengingat.', 'error');
  } finally {
    isSending.value = false;
  }
};

const formatRupiah = (angka) => {
  return Number(angka || 0)
    .toString()
    .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

const chartData = ref({
  labels: [],
  datasets: [
    {
      label: 'Pemasukan',
      backgroundColor: 'rgba(36, 133, 245, 0.10)',
      borderColor: '#176FE0',
      borderWidth: 2,
      pointBackgroundColor: '#FFFFFF',
      pointBorderColor: '#176FE0',
      pointBorderWidth: 2,
      pointRadius: 4,
      fill: true,
      data: [],
      tension: 0.3,
    },
    {
      label: 'Pengeluaran',
      backgroundColor: 'rgba(241, 142, 156, 0.0)',
      borderColor: '#C03961',
      borderWidth: 1.75,
      pointBackgroundColor: '#FFFFFF',
      pointBorderColor: '#C03961',
      pointBorderWidth: 1.5,
      pointRadius: 3,
      fill: false,
      data: [],
      tension: 0.3,
    },
  ],
});

const currentSlide = ref(0);
const klassesData = ref([]);
const currentMonth = ref('');

const nextSlide = () => {
  if (currentSlide.value < klassesData.value.length - 1) {
    currentSlide.value++;
  }
};

const prevSlide = () => {
  if (currentSlide.value > 0) {
    currentSlide.value--;
  }
};

const pieOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  cutout: '72%',
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#172B4D',
      padding: 10,
      cornerRadius: 8,
      callbacks: {
        label: (context) => ` ${context.label}: ${context.parsed} Siswa`,
      },
    },
  },
});

const loadDashboard = async () => {
  loadError.value = '';
  try {
    const resKepatuhan = await axios.get('/dashboard/kepatuhan');
    klassesData.value = resKepatuhan.data.kepatuhan_per_kelas.map((kelas) => ({
      ...kelas,
      pieData: {
        ...kelas.pieData,
        datasets: kelas.pieData.datasets.map((dataset) => ({
          ...dataset,
          backgroundColor: ['#176FE0', '#E4EEFA'],
        })),
      },
    }));
    currentMonth.value = resKepatuhan.data.bulan;

    const res = await axios.get('/dashboard/statistik');
    stats.value = res.data;

    chartData.value.labels = res.data.chart.labels;
    chartData.value.datasets[0].data = res.data.chart.pemasukan;
    chartData.value.datasets[1].data = res.data.chart.pengeluaran;

    chartReady.value = true;
  } catch (err) {
    loadError.value = 'Ringkasan belum dapat dimuat. Silakan coba lagi.';
    console.error('Gagal mengambil statistik', err);
  }
};
onMounted(loadDashboard);

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#172B4D',
      padding: 10,
      cornerRadius: 8,
      displayColors: true,
      callbacks: {
        label: (context) => {
          let label = context.dataset.label ? `${context.dataset.label}: ` : '';
          if (context.parsed.y !== null) {
            label += new Intl.NumberFormat('id-ID', {
              style: 'currency',
              currency: 'IDR',
            }).format(context.parsed.y);
          }
          return label;
        },
      },
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: { color: '#EAF0F8', drawBorder: false },
      ticks: { display: false },
    },
    x: {
      grid: { display: false, drawBorder: false },
    },
  },
});
</script>
