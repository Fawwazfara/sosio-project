<template>
  <main class="ui-page flex flex-col gap-6">
    <!-- User Welcome -->
    <div class="ui-stagger ui-stagger-1 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-[26px] font-bold text-[#0F172A] tracking-tight">Dashboard Wali Kelas</h1>
      </div>
      <div class="flex flex-wrap gap-2.5">
        <router-link to="/guru/setoran" class="ui-btn-primary ui-btn-icon-l">
          <UiIcon name="payments" :size="17" :weight="600" />
          <span>Cek Setoran Tunai</span>
        </router-link>
        <router-link to="/guru/siswa" class="ui-btn-secondary ui-btn-icon-l">
          <UiIcon name="groups" :size="17" :weight="600" />
          <span>Lihat Siswa Kelas</span>
        </router-link>
      </div>
    </div>

    <!-- Notice Banner -->
    <section v-if="totalNunggak > 0"
      class="ui-stagger ui-stagger-2 rounded-[16px] border border-transparent bg-[#FAF4E4] shadow-[0_0_0_1px_oklch(0.62_0.12_75_/_0.26),0_2px_6px_-1px_oklch(0.55_0.1_75_/_0.07)] p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
    >
      <div class="flex items-start gap-3.5">
        <div class="w-11 h-11 rounded-[10px] bg-[#9A6B16] text-white flex items-center justify-center shrink-0">
          <UiIcon name="warning" :size="20" :weight="600" />
        </div>
        <div>
          <h2 class="text-base font-bold text-[#6E4A0B]">
            <span class="tabular-nums">{{ totalNunggak }}</span> Siswa di kelas Anda belum membayar infak bulan {{ currentMonth }}
          </h2>
          <p class="text-sm text-[#7D5714] mt-1">
            Periksa setoran tunai sebelum mengirim pengingat.
          </p>
        </div>
      </div>
      <router-link to="/guru/siswa" class="ui-btn-secondary shrink-0">
        Periksa Daftar
      </router-link>
    </section>

    <!-- Financial & Compliance Cards -->
    <section class="ui-stagger ui-stagger-3 grid grid-cols-1 md:grid-cols-2 gap-5">
      <div class="ui-panel-padded ui-panel-interactive flex flex-col justify-between min-h-[172px]">
        <div class="flex items-center justify-between gap-2">
          <span class="text-xs font-semibold text-[#64748B]">Total Infak Kelas (Bulan Ini)</span>
          <div class="w-9 h-9 rounded-[9px] bg-[#EFF6FF] text-[#1D4ED8] flex items-center justify-center">
            <UiIcon name="account_balance_wallet" :size="18" :weight="600" />
          </div>
        </div>
        <div class="mt-4">
          <p class="text-3xl font-extrabold font-mono tabular-nums text-[#0F172A]">
            Rp {{ formatRupiah(stats.pemasukan_bulan_ini) }}
          </p>
          <p class="text-xs font-semibold mt-2.5 flex items-center gap-1.5 tabular-nums" :class="stats.persen_pemasukan >= 0 ? 'text-[#1D4ED8]' : 'text-[#9F1239]'">
            <UiIcon :name="stats.persen_pemasukan >= 0 ? 'trending_up' : 'trending_down'" :size="16" :weight="600" />
            <span>{{ stats.persen_pemasukan >= 0 ? '+' : '' }}{{ stats.persen_pemasukan }}% dari bulan lalu</span>
          </p>
        </div>
      </div>

      <!-- Kepatuhan Kelas Carousel -->
      <div class="ui-panel-padded flex items-center justify-between gap-3" v-if="klassesData.length > 0">
        <button
          @click="prevSlide"
          :disabled="currentSlide === 0"
          aria-label="Kelas sebelumnya"
          class="min-h-[44px] min-w-[44px] rounded-[11px] border border-transparent bg-[#F8FAFC] shadow-[var(--shadow-border)] hover:bg-[#FFFFFF] hover:shadow-[var(--shadow-border-hover)] text-[#0F172A] flex items-center justify-center shrink-0 transition-[background-color,box-shadow,transform] duration-150 ease-out enabled:active:scale-[0.96] disabled:opacity-40 disabled:cursor-not-allowed"
        >
          <UiIcon name="chevron_left" :size="20" :weight="600" directional />
        </button>

        <div class="flex-1 flex flex-col sm:flex-row items-center justify-center gap-5 px-2">
          <div class="w-[120px] h-[120px] relative shrink-0">
            <Doughnut :data="klassesData[currentSlide].pieData" :options="pieOptions" :key="'pie-' + currentSlide" />
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
              <span class="text-base font-extrabold font-mono tabular-nums text-[#0F172A]">
                {{ klassesData[currentSlide].persentase }}%
              </span>
            </div>
          </div>
          <div class="flex flex-col gap-2.5 text-center sm:text-left">
            <h3 class="text-base font-bold text-[#0F172A]">
              Kepatuhan {{ klassesData[currentSlide].nama_kelas }}
            </h3>
            <div class="flex flex-col gap-1.5 text-sm">
              <span class="flex items-center justify-center sm:justify-start gap-2 font-semibold text-[#0F172A] tabular-nums">
                <span class="w-2.5 h-2.5 rounded-[3px] bg-[#1D4ED8]"></span>
                {{ klassesData[currentSlide].lunas }} Lunas
              </span>
              <span class="flex items-center justify-center sm:justify-start gap-2 font-semibold text-[#475569] tabular-nums">
                <span class="w-2.5 h-2.5 rounded-[3px] bg-[#CBD5E1]"></span>
                {{ klassesData[currentSlide].nunggak }} Menunggak
              </span>
            </div>
          </div>
        </div>

        <button
          @click="nextSlide"
          :disabled="currentSlide === klassesData.length - 1"
          aria-label="Kelas berikutnya"
          class="min-h-[44px] min-w-[44px] rounded-[11px] border border-transparent bg-[#F8FAFC] shadow-[var(--shadow-border)] hover:bg-[#FFFFFF] hover:shadow-[var(--shadow-border-hover)] text-[#0F172A] flex items-center justify-center shrink-0 transition-[background-color,box-shadow,transform] duration-150 ease-out enabled:active:scale-[0.96] disabled:opacity-40 disabled:cursor-not-allowed"
        >
          <UiIcon name="chevron_right" :size="20" :weight="600" directional />
        </button>
      </div>
    </section>

    <!-- Chart -->
    <section class="ui-stagger ui-stagger-4 ui-panel-padded flex flex-col gap-4">
      <div>
        <h2 class="text-lg font-bold text-[#0F172A]">Grafik Pemasukan Kelas (6 Bulan Terakhir)</h2>
        <p class="text-xs text-[#64748B] mt-0.5">Tren pemasukan infak bulanan kelas binaan Anda</p>
      </div>

      <div class="w-full overflow-x-auto pb-2" v-if="chartReady">
        <div class="min-w-[540px] h-[250px] relative">
          <Line :data="chartData" :options="chartOptions" />
        </div>
      </div>
      <div v-else class="h-[250px] bg-[#F8FAFC] rounded-[12px] animate-pulse"></div>
    </section>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  ArcElement,
  Title,
  Tooltip,
  Filler
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
  Filler
);

const currentSlide = ref(0);
const klassesData = ref([]);
const totalNunggak = ref(0);
const currentMonth = ref('');

const stats = ref({
  pemasukan_bulan_ini: 0,
  persen_pemasukan: 0
});

const chartReady = ref(false);

const formatRupiah = (angka) => {
  return Number(angka || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

onMounted(async () => {
  try {
    const resKepatuhan = await axios.get('/dashboard/kepatuhan');
    klassesData.value = resKepatuhan.data.kepatuhan_per_kelas;
    totalNunggak.value = resKepatuhan.data.total_belum_bayar_bulan_ini;
    currentMonth.value = resKepatuhan.data.bulan;

    const resStat = await axios.get('/dashboard/statistik');
    stats.value = resStat.data;

    chartData.value.labels = resStat.data.chart.labels;
    chartData.value.datasets[0].data = resStat.data.chart.pemasukan;

    chartReady.value = true;
  } catch (e) {
    console.error('Gagal memuat dashboard data', e);
  }
});

const pieOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  cutout: '72%',
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#0F172A',
      padding: 10,
      cornerRadius: 8,
      callbacks: {
        label: (context) => ` ${context.label}: ${context.parsed} Siswa`
      }
    }
  }
});

const chartData = ref({
  labels: [],
  datasets: [
    {
      label: 'Pemasukan Kelas',
      backgroundColor: 'rgba(29, 78, 216, 0.06)',
      borderColor: '#1D4ED8',
      borderWidth: 2,
      pointBackgroundColor: '#FFFFFF',
      pointBorderColor: '#1D4ED8',
      pointBorderWidth: 2,
      pointRadius: 4,
      fill: true,
      data: [],
      tension: 0.3
    }
  ]
});

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#0F172A',
      padding: 10,
      cornerRadius: 8,
      displayColors: false,
      callbacks: {
        label: (context) => {
          let label = context.dataset.label ? `${context.dataset.label}: ` : '';
          if (context.parsed.y !== null) {
            label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
          }
          return label;
        }
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: { color: '#E2E8F0', drawBorder: false },
      ticks: { display: false }
    },
    x: {
      grid: { display: false, drawBorder: false }
    }
  }
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
