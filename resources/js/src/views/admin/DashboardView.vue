<template>
  <main class="ui-page flex flex-col gap-6">
    <!-- Page Heading -->
    <div class="ui-stagger ui-stagger-1 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-[26px] font-bold text-[#1C1917] tracking-tight">Ringkasan kas</h1>
      </div>
      <div class="flex flex-wrap gap-2.5">
        <router-link to="/admin/infak/create" class="ui-btn-primary ui-btn-icon-l">
          <UiIcon name="add_circle" :size="17" :weight="600" />
          <span>Catat infak</span>
        </router-link>
        <router-link to="/admin/laporan" class="ui-btn-secondary ui-btn-icon-l">
          <UiIcon name="account_balance" :size="17" :weight="600" />
          <span>Laporan Kas</span>
        </router-link>
      </div>
    </div>

    <!-- Action Bar: Pengingat & Verifikasi Setoran -->
    <section
      class="ui-stagger ui-stagger-2 bg-gradient-to-br from-[#292524] to-[#1C1917] text-white rounded-[16px] p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 border border-transparent shadow-[var(--shadow-navy-card)]"
    >
      <div class="flex items-start sm:items-center gap-3.5">
        <div class="w-11 h-11 rounded-[11px] bg-[#A8551E] ring-1 ring-inset ring-white/20 flex items-center justify-center shrink-0">
          <UiIcon name="notifications_active" :size="20" :weight="600" class="text-white" />
        </div>
        <div>
          <h2 class="text-base font-bold text-white">Setoran &amp; pengingat</h2>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2.5 shrink-0 w-full lg:w-auto justify-end">
        <router-link
          to="/admin/setoran"
          class="min-h-[42px] ps-3.5 pe-4 rounded-[10px] bg-white/12 hover:bg-white/20 ring-1 ring-inset ring-white/15 text-white text-xs sm:text-sm font-semibold flex items-center gap-2 transition-[background-color,transform] duration-150 ease-out active:scale-[0.96]"
        >
          <UiIcon name="qr_code_scanner" :size="17" :weight="600" />
          <span>Cek setoran</span>
        </router-link>

        <template v-if="confirmReminder">
          <button
            @click="executeReminder"
            :disabled="isSending"
            class="min-h-[42px] px-4 rounded-[10px] bg-[#A8551E] hover:bg-[#8D4415] text-white text-xs sm:text-sm font-semibold flex items-center gap-2 transition-[background-color,transform] duration-150 ease-out enabled:active:scale-[0.96] disabled:opacity-50"
          >
            {{ isSending ? 'Mengirim...' : 'Kirim ke semua guru' }}
          </button>
          <button
            @click="confirmReminder = false"
            :disabled="isSending"
            class="min-h-[42px] px-3.5 rounded-[10px] bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-semibold transition-[background-color,transform] duration-150 ease-out enabled:active:scale-[0.96]"
          >
            Batal
          </button>
        </template>
        <button
          v-else
          @click="confirmReminder = true"
          :disabled="isSending"
          class="min-h-[42px] ps-3.5 pe-4 rounded-[10px] bg-[#A8551E] hover:bg-[#8D4415] shadow-[inset_0_1px_0_0_oklch(1_0_0_/_0.18)] text-white text-xs sm:text-sm font-semibold flex items-center gap-2 transition-[background-color,transform] duration-150 ease-out enabled:active:scale-[0.96] disabled:opacity-50"
        >
          <UiIcon name="send" :size="16" :weight="600" directional />
          <span>Ingatkan guru</span>
        </button>
      </div>
    </section>

    <!-- Row 1: KPI Cards -->
    <section class="ui-stagger ui-stagger-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
      <div class="ui-panel ui-panel-interactive p-5 sm:p-6 flex flex-col justify-between min-h-[144px]">
        <div class="flex items-center justify-between gap-2">
          <span class="text-xs font-semibold text-[#78716C]">Total Kas Masuk</span>
          <div class="w-8 h-8 rounded-[8px] bg-[#F9EFE7] text-[#A8551E] flex items-center justify-center">
            <UiIcon name="account_balance_wallet" :size="17" :weight="600" />
          </div>
        </div>
        <div class="mt-4">
          <p class="text-2xl font-extrabold font-mono tabular-nums text-[#1C1917]">
            Rp {{ formatRupiah(stats.pemasukan_all_time) }}
          </p>
          <p class="text-xs text-[#78716C] mt-2 flex items-center gap-1.5">
            <UiIcon
              :name="stats.persen_pemasukan >= 0 ? 'trending_up' : 'trending_down'"
              :size="15"
              :weight="600"
              :class="stats.persen_pemasukan >= 0 ? 'text-[#A8551E]' : 'text-[#9F1239]'"
            />
            <span class="font-semibold tabular-nums" :class="stats.persen_pemasukan >= 0 ? 'text-[#A8551E]' : 'text-[#9F1239]'">
              {{ stats.persen_pemasukan >= 0 ? '+' : '' }}{{ stats.persen_pemasukan }}%
            </span>
            <span>· Bulan ini Rp {{ formatRupiah(stats.pemasukan_bulan_ini) }}</span>
          </p>
        </div>
      </div>

      <div class="ui-panel ui-panel-interactive p-5 sm:p-6 flex flex-col justify-between min-h-[144px]">
        <div class="flex items-center justify-between gap-2">
          <span class="text-xs font-semibold text-[#78716C]">Pengeluaran Bulan Ini</span>
          <div class="w-8 h-8 rounded-[8px] bg-[#FDF2F4] text-[#9F1239] flex items-center justify-center">
            <UiIcon name="money_off" :size="17" :weight="600" />
          </div>
        </div>
        <div class="mt-4">
          <p class="text-2xl font-extrabold font-mono tabular-nums text-[#1C1917]">
            Rp {{ formatRupiah(stats.pengeluaran_bulan_ini) }}
          </p>
          <p class="text-xs text-[#78716C] mt-2 flex items-center gap-1.5">
            <UiIcon
              :name="stats.persen_pengeluaran >= 0 ? 'trending_up' : 'trending_down'"
              :size="15"
              :weight="600"
              :class="stats.persen_pengeluaran >= 0 ? 'text-[#9F1239]' : 'text-[#A8551E]'"
            />
            <span class="font-semibold tabular-nums" :class="stats.persen_pengeluaran >= 0 ? 'text-[#9F1239]' : 'text-[#A8551E]'">
              {{ stats.persen_pengeluaran >= 0 ? '+' : '' }}{{ stats.persen_pengeluaran }}%
            </span>
            <span>dibanding bulan lalu</span>
          </p>
        </div>
      </div>

      <div class="ui-panel ui-panel-interactive p-5 sm:p-6 flex flex-col justify-between min-h-[144px] sm:col-span-2 lg:col-span-1">
        <div class="flex items-center justify-between gap-2">
          <span class="text-xs font-semibold text-[#78716C]">Saldo Bersih Saat Ini</span>
          <div class="w-8 h-8 rounded-[8px] bg-[#ECE8DF] text-[#292524] flex items-center justify-center">
            <UiIcon name="savings" :size="17" :weight="600" />
          </div>
        </div>
        <div class="mt-4">
          <p class="text-2xl font-extrabold font-mono tabular-nums text-[#1C1917]">
            Rp {{ formatRupiah(stats.saldo_all_time) }}
          </p>
          <p class="text-xs text-[#78716C] mt-2">Pemasukan dikurangi pengeluaran</p>
        </div>
      </div>
    </section>

    <!-- Kepatuhan Kelas Carousel -->
    <section v-if="klassesData.length > 0" class="ui-stagger ui-stagger-3 ui-panel-padded flex items-center justify-between gap-4">
      <button
        @click="prevSlide"
        :disabled="currentSlide === 0"
        aria-label="Kelas sebelumnya"
        class="min-h-[44px] min-w-[44px] rounded-[11px] border border-transparent bg-[#F7F4ED] shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)] text-[#1C1917] flex items-center justify-center shrink-0 transition-[background-color,box-shadow,transform] duration-150 ease-out enabled:active:scale-[0.96] disabled:opacity-40 disabled:cursor-not-allowed"
      >
        <UiIcon name="chevron_left" :size="20" :weight="600" directional />
      </button>

      <div class="flex-1 flex flex-col sm:flex-row items-center justify-center gap-6 px-2">
        <div class="w-[128px] h-[128px] relative shrink-0">
          <Doughnut :data="klassesData[currentSlide].pieData" :options="pieOptions" :key="'pie-' + currentSlide" />
          <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
            <span class="text-lg font-extrabold font-mono tabular-nums text-[#1C1917]">
              {{ klassesData[currentSlide].persentase }}%
            </span>
          </div>
        </div>

        <div class="flex flex-col gap-3 text-center sm:text-left">
          <div>
            <h3 class="text-base font-bold text-[#1C1917]">
              Kepatuhan {{ klassesData[currentSlide].nama_kelas }}
            </h3>
            <p class="text-xs text-[#78716C] mt-0.5">Rekap bulan {{ currentMonth }}</p>
          </div>
          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 text-sm">
            <span class="flex items-center gap-2 font-semibold text-[#1C1917] tabular-nums">
              <span class="w-2.5 h-2.5 rounded-[3px] bg-[#A8551E]"></span>
              {{ klassesData[currentSlide].lunas }} Lunas
            </span>
            <span class="flex items-center gap-2 font-semibold text-[#57534E] tabular-nums">
              <span class="w-2.5 h-2.5 rounded-[3px] bg-[#D6CFC2]"></span>
              {{ klassesData[currentSlide].nunggak }} Menunggak
            </span>
          </div>
        </div>
      </div>

      <button
        @click="nextSlide"
        :disabled="currentSlide === klassesData.length - 1"
        aria-label="Kelas berikutnya"
        class="min-h-[44px] min-w-[44px] rounded-[11px] border border-transparent bg-[#F7F4ED] shadow-[var(--shadow-border)] hover:bg-[#FDFCF9] hover:shadow-[var(--shadow-border-hover)] text-[#1C1917] flex items-center justify-center shrink-0 transition-[background-color,box-shadow,transform] duration-150 ease-out enabled:active:scale-[0.96] disabled:opacity-40 disabled:cursor-not-allowed"
      >
        <UiIcon name="chevron_right" :size="20" :weight="600" directional />
      </button>
    </section>

    <!-- Chart -->
    <section class="ui-stagger ui-stagger-4 ui-panel-padded flex flex-col gap-5">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
          <h2 class="text-lg font-bold text-[#1C1917]">Arus Kas 6 Bulan Terakhir</h2>
        </div>
        <div class="flex items-center gap-4 text-xs font-medium text-[#57534E]">
          <span class="flex items-center gap-1.5"><span class="w-3 h-1 rounded-full bg-[#A8551E]"></span> Pemasukan</span>
          <span class="flex items-center gap-1.5"><span class="w-3 h-1 rounded-full bg-[#9F1239]"></span> Pengeluaran</span>
        </div>
      </div>

      <div class="w-full overflow-x-auto pb-2" v-if="chartReady">
        <div class="min-w-[560px] h-[260px] relative">
          <Line :data="chartData" :options="chartOptions" />
        </div>
      </div>
      <div v-else class="h-[260px] bg-[#F7F4ED] rounded-[12px] animate-pulse"></div>
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
import { notify } from '@/utils/toast';

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

const stats = ref({
  pemasukan_all_time: 0,
  pemasukan_bulan_ini: 0,
  pengeluaran_bulan_ini: 0,
  saldo_all_time: 0,
  persen_pemasukan: 0,
  persen_pengeluaran: 0
});

const chartReady = ref(false);
const isSending = ref(false);
const confirmReminder = ref(false);

const executeReminder = async () => {
  isSending.value = true;
  try {
    const res = await axios.post('/push-send-reminders');
    confirmReminder.value = false;
    notify(res.data.message || 'Notifikasi pengingat berhasil dikirim ke wali kelas.', 'success');
  } catch (err) {
    console.error(err);
    notify('Gagal mengirim notifikasi pengingat.', 'error');
  } finally {
    isSending.value = false;
  }
};

const formatRupiah = (angka) => {
  return Number(angka || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

const chartData = ref({
  labels: [],
  datasets: [
    {
      label: 'Pemasukan',
      backgroundColor: 'rgba(168, 85, 30, 0.10)',
      borderColor: '#A8551E',
      borderWidth: 2,
      pointBackgroundColor: '#FDFCF9',
      pointBorderColor: '#A8551E',
      pointBorderWidth: 2,
      pointRadius: 4,
      fill: true,
      data: [],
      tension: 0.3
    },
    {
      label: 'Pengeluaran',
      backgroundColor: 'rgba(159, 18, 57, 0.0)',
      borderColor: '#9F1239',
      borderWidth: 1.75,
      pointBackgroundColor: '#FDFCF9',
      pointBorderColor: '#9F1239',
      pointBorderWidth: 1.5,
      pointRadius: 3,
      fill: false,
      data: [],
      tension: 0.3
    }
  ]
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
      backgroundColor: '#1C1917',
      padding: 10,
      cornerRadius: 8,
      callbacks: {
        label: (context) => ` ${context.label}: ${context.parsed} Siswa`
      }
    }
  }
});

onMounted(async () => {
  try {
    const resKepatuhan = await axios.get('/dashboard/kepatuhan');
    klassesData.value = resKepatuhan.data.kepatuhan_per_kelas;
    currentMonth.value = resKepatuhan.data.bulan;

    const res = await axios.get('/dashboard/statistik');
    stats.value = res.data;

    chartData.value.labels = res.data.chart.labels;
    chartData.value.datasets[0].data = res.data.chart.pemasukan;
    chartData.value.datasets[1].data = res.data.chart.pengeluaran;

    chartReady.value = true;
  } catch (err) {
    console.error('Gagal mengambil statistik', err);
  }
});

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#1C1917',
      padding: 10,
      cornerRadius: 8,
      displayColors: true,
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
      grid: { color: '#E6E1D6', drawBorder: false },
      ticks: { display: false }
    },
    x: {
      grid: { display: false, drawBorder: false }
    }
  }
});
</script>
