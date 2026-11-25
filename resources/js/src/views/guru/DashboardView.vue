<template>
  <main class="edu-dashboard">
    <div class="edu-page-heading">
      <div>
        <h1>Dashboard kelas</h1>
      </div>
      <router-link to="/guru/siswa" class="ui-btn-secondary"
        ><UiIcon name="groups" :size="17" /> Lihat siswa</router-link
      >
    </div>
    <div v-if="loadError" role="alert" class="edu-data-error">
      {{ loadError }}<button @click="loadDashboard">Coba lagi</button>
    </div>
    <div class="edu-dashboard-grid">
      <div class="edu-main-column">
        <EducationHero
          :name="firstName"
          action="Periksa setoran tunai"
          to="/guru/setoran"
          image="/images/education/indonesia-learning.jpg"
          image-alt="Siswi berhijab belajar bersama di sekolah di Pandeglang, Banten"
        />
        <section aria-labelledby="guru-stat-title">
          <div class="edu-section-title">
            <h2 id="guru-stat-title">Ringkasan kelas</h2>
            <span>{{ currentMonth || 'Bulan ini' }}</span>
          </div>
          <div class="edu-stats-grid">
            <article class="edu-stat edu-stat-blue">
              <span class="edu-stat-icon"
                ><UiIcon name="account_balance_wallet" :size="21"
              /></span>
              <p>Infak bulan ini</p>
              <strong>Rp {{ formatRupiah(stats.pemasukan_bulan_ini) }}</strong
              ><small
                >{{ stats.persen_pemasukan >= 0 ? '+' : ''
                }}{{ stats.persen_pemasukan }}% dari bulan lalu</small
              >
            </article>
            <article class="edu-stat edu-stat-mint">
              <span class="edu-stat-icon"
                ><UiIcon name="groups" :size="21"
              /></span>
              <p>Jumlah santri</p>
              <strong
                >{{ totalSiswa }}
                <span class="edu-stat-unit">santri</span></strong
              ><small>Tersebar di {{ klassesData.length }} kelas</small>
            </article>
            <article class="edu-stat edu-stat-teal">
              <span class="edu-stat-icon"
                ><UiIcon name="check_circle" :size="21"
              /></span>
              <p>Lunas bulan ini</p>
              <strong
                >{{ totalSiswa - totalNunggak }}
                <span class="edu-stat-unit">santri</span></strong
              ><small>{{ totalNunggak }} santri belum membayar</small>
            </article>
          </div>
        </section>
        <section>
          <div class="edu-section-title">
            <h2>Kelas binaan</h2>
            <router-link to="/guru/siswa"
              >Lihat semua <UiIcon name="arrow_forward" :size="15"
            /></router-link>
          </div>
          <div class="edu-class-cards">
            <router-link
              v-for="(kelas, index) in klassesData"
              :key="kelas.nama_kelas"
              to="/guru/siswa"
              class="edu-class-card"
              :class="index % 2 === 0 ? 'edu-class-mint' : 'edu-class-blue'"
              ><span class="edu-class-card-symbol"
                ><UiIcon name="school" :size="24"
              /></span>
              <div>
                <strong>{{ kelas.nama_kelas }}</strong
                ><span
                  >{{ kelas.lunas + kelas.nunggak }} santri ·
                  {{ kelas.lunas }} lunas bulan ini</span
                >
                <div class="edu-progress-track">
                  <i
                    :style="{
                      width: kelas.persentase + '%',
                    }"
                  ></i>
                </div>
              </div>
              <span class="edu-class-card-end"
                ><b>{{ kelas.persentase }}%</b
                ><UiIcon name="arrow_forward" :size="18" /></span
            ></router-link>
            <p v-if="!klassesData.length" class="edu-empty">
              {{
                chartReady
                  ? 'Belum ada kelas binaan yang terdaftar.'
                  : 'Memuat kelas binaan…'
              }}
            </p>
          </div>
        </section>
        <section class="edu-chart-card">
          <div class="edu-section-title">
            <div>
              <h2>Pemasukan kelas</h2>
              <p>6 bulan terakhir</p>
            </div>
            <span class="edu-small-badge">Infak kelas</span>
          </div>
          <div v-if="chartReady" class="edu-chart">
            <Line :data="chartData" :options="chartOptions" />
          </div>
          <div v-else class="edu-chart-placeholder" role="status">
            {{
              loadError ? 'Grafik belum tersedia' : 'Memuat pemasukan kelas…'
            }}
          </div>
        </section>
      </div>
      <aside class="edu-right-column" aria-label="Ringkasan wali kelas">
        <section class="edu-side-card edu-profile-card">
          <span class="edu-profile-avatar">{{ initials }}</span>
          <h2>{{ user?.name || 'Wali kelas' }}</h2>
          <span class="edu-profile-label"
            ><UiIcon name="school" :size="14" /> {{ klassesData.length }} kelas
            binaan</span
          >
          <div class="edu-profile-divider"></div>
          <div class="edu-section-title">
            <h2>Kepatuhan infak</h2>
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
        </section>
        <section v-if="totalNunggak > 0" class="edu-attention-card">
          <span class="edu-attention-icon"
            ><UiIcon name="notifications_active" :size="23"
          /></span>
          <h2>{{ totalNunggak }} santri belum lunas</h2>
          <p>Infak {{ currentMonth }}. Cek setoran sebelum mengingatkan.</p>
          <router-link to="/guru/siswa" class="ui-btn-secondary"
            >Periksa daftar<UiIcon name="arrow_forward" :size="17"
          /></router-link>
        </section>
      </aside>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import EducationHero from '@/components/shared/EducationHero.vue';
import { getStoredUser } from '@/utils/auth';

const user = getStoredUser();
const firstName =
  user?.name?.split(' ').slice(0, 2).join(' ') || 'Sahabat madrasah';
const initials = (user?.name || 'AS')
  .split(' ')
  .map((word) => word[0])
  .slice(0, 2)
  .join('')
  .toUpperCase();
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

const currentSlide = ref(0);
const klassesData = ref([]);
const totalNunggak = ref(0);
const totalSiswa = computed(() =>
  klassesData.value.reduce(
    (sum, kelas) => sum + kelas.lunas + kelas.nunggak,
    0,
  ),
);
const currentMonth = ref('');

const stats = ref({
  pemasukan_bulan_ini: 0,
  persen_pemasukan: 0,
});

const chartReady = ref(false);

const formatRupiah = (angka) => {
  return Number(angka || 0)
    .toString()
    .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

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
    totalNunggak.value = resKepatuhan.data.total_belum_bayar_bulan_ini;
    currentMonth.value = resKepatuhan.data.bulan;

    const resStat = await axios.get('/dashboard/statistik');
    stats.value = resStat.data;

    chartData.value.labels = resStat.data.chart.labels;
    chartData.value.datasets[0].data = resStat.data.chart.pemasukan;

    chartReady.value = true;
  } catch (e) {
    loadError.value = 'Ringkasan belum dapat dimuat. Silakan coba lagi.';
    console.error('Gagal memuat dashboard data', e);
  }
};
onMounted(loadDashboard);

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

const chartData = ref({
  labels: [],
  datasets: [
    {
      label: 'Pemasukan Kelas',
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
  ],
});

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#172B4D',
      padding: 10,
      cornerRadius: 8,
      displayColors: false,
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
</script>
