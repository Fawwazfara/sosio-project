<template>
  <div class="ui-page flex flex-col gap-6">
    <!-- Header & Print Action -->
    <div class="ui-stagger ui-stagger-1 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-[26px] font-bold text-[#1C1917] tracking-tight">Laporan Keuangan Bulanan</h1>
        <p class="text-sm text-[#57534E] mt-1">Rekap pemasukan per kelas, daftar pengeluaran, dan ekspor PDF periode {{ activeMonth }}.</p>
      </div>

      <button @click="cetakPdf" :disabled="isDownloading" class="ui-btn-navy ui-btn-icon-l self-start sm:self-auto">
        <UiIcon v-if="isDownloading" name="progress_activity" :size="17" :weight="600" class="animate-spin" />
        <UiIcon v-else name="picture_as_pdf" :size="17" :weight="600" />
        <span>{{ isDownloading ? 'Menyiapkan PDF...' : `Cetak PDF (${activeMonth})` }}</span>
      </button>
    </div>

    <!-- Month Switcher (Concentric pill rail) -->
    <div class="ui-stagger ui-stagger-2 flex gap-1.5 overflow-x-auto p-1 rounded-[14px] bg-[#ECE8DF] shadow-[inset_0_1px_2px_0_oklch(0.22_0.015_65_/_0.06),0_0_0_1px_oklch(0.22_0.015_65_/_0.06)] no-scrollbar">
      <button
        v-for="m in months"
        :key="m"
        @click="activeMonth = m"
        :class="[
          'min-h-[38px] px-3.5 rounded-[10px] border border-transparent text-xs font-semibold shrink-0 transition-[background-color,color,box-shadow,transform] duration-150 ease-out active:scale-[0.96]',
          activeMonth === m
            ? 'bg-[#FDFCF9] text-[#1C1917] shadow-[var(--shadow-border)]'
            : 'text-[#57534E] hover:text-[#1C1917]'
        ]"
      >
        {{ m }}
      </button>
    </div>

    <!-- Monthly Balance Summary -->
    <section class="ui-stagger ui-stagger-3 grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="ui-panel p-5">
        <p class="text-xs font-semibold text-[#78716C]">Total Pemasukan ({{ activeMonth }})</p>
        <p class="text-2xl font-extrabold font-mono tabular-nums text-[#A8551E] mt-2">
          Rp {{ formatRupiah(totalPemasukan) }}
        </p>
      </div>
      <div class="ui-panel p-5">
        <p class="text-xs font-semibold text-[#78716C]">Total Pengeluaran ({{ activeMonth }})</p>
        <p class="text-2xl font-extrabold font-mono tabular-nums text-[#9F1239] mt-2">
          Rp {{ formatRupiah(totalPengeluaran) }}
        </p>
      </div>
      <div class="ui-panel p-5">
        <p class="text-xs font-semibold text-[#78716C]">Surplus / Saldo Periode</p>
        <p class="text-2xl font-extrabold font-mono tabular-nums mt-2" :class="saldoAkhir >= 0 ? 'text-[#1C1917]' : 'text-[#9F1239]'">
          Rp {{ formatRupiah(saldoAkhir) }}
        </p>
      </div>
    </section>

    <!-- Mobile Tab Toggle (Concentric) -->
    <div class="md:hidden ui-segmented w-full">
      <button
        type="button"
        @click="mobileView = 'pemasukan'"
        :class="[
          'flex-1 ui-segmented-item text-xs',
          mobileView === 'pemasukan' ? 'ui-segmented-item-active' : 'ui-segmented-item-idle'
        ]"
      >
        Pemasukan Kelas
      </button>
      <button
        type="button"
        @click="mobileView = 'pengeluaran'"
        :class="[
          'flex-1 ui-segmented-item text-xs',
          mobileView === 'pengeluaran' ? 'ui-segmented-item-active' : 'ui-segmented-item-idle'
        ]"
      >
        Pengeluaran Kas
      </button>
    </div>

    <!-- Split Ledger Columns -->
    <div class="ui-stagger ui-stagger-4 grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
      <!-- Pemasukan per Kelas -->
      <section v-show="mobileView === 'pemasukan' || isDesktop" class="flex flex-col gap-3.5">
        <h2 class="text-base font-bold text-[#1C1917]">Pemasukan per Kelas</h2>

        <div v-if="isLoading" class="ui-panel-padded space-y-3">
          <div v-for="n in 4" :key="n" class="h-14 bg-[#F7F4ED] rounded-[10px] animate-pulse"></div>
        </div>

        <div v-else-if="pemasukanList.length === 0" class="ui-panel-padded text-center py-10 text-sm text-[#78716C]">
          Belum ada data pemasukan untuk bulan {{ activeMonth }}.
        </div>

        <div v-else class="flex flex-col gap-3">
          <div v-for="item in pemasukanList" :key="item.kelas_id" class="flex flex-col">
            <button
              type="button"
              @click="toggleDetail(item)"
              :aria-expanded="openKelasId === item.kelas_id"
              class="ui-panel ui-panel-interactive p-4 sm:p-5 flex items-center gap-3.5 text-left transition-[box-shadow,transform] duration-150 ease-out active:scale-[0.98]"
            >
              <div class="w-10 h-10 rounded-[10px] bg-[#F9EFE7] text-[#A8551E] flex items-center justify-center shrink-0">
                <UiIcon name="account_balance_wallet" :size="18" :weight="600" />
              </div>
              <span class="text-sm font-bold text-[#1C1917] flex-1">{{ item.kelas }}</span>
              <span class="text-base font-bold font-mono tabular-nums text-[#292524] shrink-0">
                Rp {{ formatRupiah(item.nominal) }}
              </span>
              <UiIcon
                name="expand_more"
                :size="18"
                :weight="600"
                class="text-[#78716C] transition-transform duration-150 ease-out shrink-0"
                :class="{ 'rotate-180': openKelasId === item.kelas_id }"
              />
            </button>

            <!-- Drill-down Rincian -->
            <div
              v-if="openKelasId === item.kelas_id"
              class="mt-2 rounded-[14px] bg-[#F7F4ED] shadow-[inset_0_1px_2px_0_oklch(0.22_0.015_65_/_0.05),0_0_0_1px_oklch(0.22_0.015_65_/_0.07)] p-4"
            >
              <div v-if="detailLoading" class="flex items-center gap-2 py-2 text-xs text-[#78716C]">
                <UiIcon name="sync" :size="16" :weight="600" class="animate-spin" />
                <span>Memuat rincian pembayaran...</span>
              </div>

              <div v-else-if="detailError" class="py-2 text-xs font-medium text-[#9F1239]">
                {{ detailError }}
              </div>

              <template v-else-if="detailData">
                <div class="flex items-center justify-between pb-2.5 mb-2 border-b border-black/[0.07]">
                  <span class="text-xs font-medium text-[#78716C] tabular-nums">
                    {{ detailData.jumlah_transaksi }} transaksi · {{ detailData.bulan }}
                  </span>
                  <span class="text-xs font-bold font-mono tabular-nums text-[#A8551E]">
                    Rp {{ formatRupiah(detailData.total) }}
                  </span>
                </div>

                <div v-if="detailData.rows.length" class="divide-y divide-black/[0.05]">
                  <div
                    v-for="(row, idx) in detailData.rows"
                    :key="idx"
                    class="py-2.5 flex items-center justify-between gap-3"
                  >
                    <div class="min-w-0">
                      <p class="text-sm font-semibold text-[#1C1917] truncate">{{ row.siswa }}</p>
                      <p class="text-xs text-[#78716C]">
                        {{ formatTanggal(row.tanggal_bayar) }} · {{ row.bulan }}
                      </p>
                    </div>
                    <span class="text-sm font-bold font-mono tabular-nums text-[#292524] shrink-0">
                      Rp {{ formatRupiah(row.jumlah) }}
                    </span>
                  </div>
                </div>

                <p v-else class="text-xs text-[#78716C] py-2">
                  Belum ada pembayaran tercatat untuk bulan ini.
                </p>
              </template>
            </div>
          </div>
        </div>
      </section>

      <!-- Pengeluaran Bulan Ini -->
      <section v-show="mobileView === 'pengeluaran' || isDesktop" class="flex flex-col gap-3.5">
        <h2 class="text-base font-bold text-[#1C1917]">Riwayat Pengeluaran</h2>

        <div v-if="isLoading" class="ui-panel-padded space-y-3">
          <div v-for="n in 3" :key="n" class="h-14 bg-[#F7F4ED] rounded-[10px] animate-pulse"></div>
        </div>

        <div v-else-if="pengeluaranList.length === 0" class="ui-panel-padded text-center py-10 text-sm text-[#78716C]">
          Tidak ada catatan pengeluaran di bulan {{ activeMonth }}.
        </div>

        <div v-else class="flex flex-col gap-3">
          <div
            v-for="item in pengeluaranList"
            :key="item.id"
            class="ui-panel p-4 sm:p-5 flex items-center gap-4"
          >
            <div class="w-12 h-14 bg-[#F7F4ED] shadow-[inset_0_1px_2px_0_oklch(0.22_0.015_65_/_0.05),0_0_0_1px_oklch(0.22_0.015_65_/_0.07)] rounded-[11px] flex flex-col items-center justify-center shrink-0">
              <span class="text-sm font-extrabold font-mono text-[#1C1917] leading-tight tabular-nums">{{ getDay(item.tanggal) }}</span>
              <span class="text-xs font-medium text-[#78716C] leading-tight">{{ getMonthShort(item.tanggal) }}</span>
            </div>

            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-[#1C1917] truncate">{{ item.keterangan }}</p>
            </div>

            <span class="text-base font-bold font-mono tabular-nums text-[#9F1239] shrink-0">
              Rp {{ formatRupiah(item.jumlah) }}
            </span>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';
import { notify } from '@/utils/toast';

const getCurrentMonthIndo = () => {
  const month = new Intl.DateTimeFormat('id-ID', { month: 'long' }).format(new Date());
  return month.charAt(0).toUpperCase() + month.slice(1);
};

const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const activeMonth = ref(getCurrentMonthIndo());

const mobileView = ref('pemasukan');
const windowWidth = ref(window.innerWidth);
const updateWidth = () => { windowWidth.value = window.innerWidth; };

const pemasukanList = ref([]);
const pengeluaranList = ref([]);
const totalPemasukan = ref(0);
const totalPengeluaran = ref(0);
const isLoading = ref(false);

const openKelasId = ref(null);
const detailData = ref(null);
const detailLoading = ref(false);
const detailError = ref('');

const toggleDetail = async (item) => {
  if (openKelasId.value === item.kelas_id) {
    openKelasId.value = null;
    detailData.value = null;
    detailError.value = '';
    return;
  }
  const requestedId = item.kelas_id;
  openKelasId.value = requestedId;
  detailData.value = null;
  detailError.value = '';
  detailLoading.value = true;
  try {
    const res = await axios.get('/laporan/detail', {
      params: { bulan: activeMonth.value, kelas_id: requestedId }
    });
    if (openKelasId.value === requestedId) detailData.value = res.data;
  } catch (err) {
    console.error('Gagal mengambil rincian laporan', err);
    if (openKelasId.value === requestedId) detailError.value = 'Gagal memuat rincian pembayaran. Coba lagi.';
  } finally {
    if (openKelasId.value === requestedId) detailLoading.value = false;
  }
};

const saldoAkhir = computed(() => totalPemasukan.value - totalPengeluaran.value);

const fetchLaporan = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get(`/laporan?bulan=${activeMonth.value}`);
    pemasukanList.value = res.data.pemasukan_per_kelas;
    pengeluaranList.value = res.data.pengeluaran_list;
    totalPemasukan.value = res.data.total_pemasukan;
    totalPengeluaran.value = res.data.total_pengeluaran;
  } catch (err) {
    console.error('Gagal mengambil laporan', err);
  } finally {
    isLoading.value = false;
  }
};

watch(activeMonth, () => {
  openKelasId.value = null;
  detailData.value = null;
  detailError.value = '';
  fetchLaporan();
});

const isDownloading = ref(false);
const cetakPdf = async () => {
  if (isDownloading.value) return;
  isDownloading.value = true;
  try {
    const response = await axios.get(`/laporan/cetak-pdf?bulan=${activeMonth.value}`, { responseType: 'blob' });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `laporan_keuangan_${activeMonth.value.toLowerCase()}_${new Date().getFullYear()}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    notify(`Laporan ${activeMonth.value} berhasil diunduh.`, 'success');
  } catch (error) {
    console.error('Gagal export PDF', error);
    notify('Gagal mengunduh file laporan PDF.', 'error');
  } finally {
    isDownloading.value = false;
  }
};

const isDesktop = computed(() => windowWidth.value >= 768);

onMounted(() => {
  window.addEventListener('resize', updateWidth);
  fetchLaporan();
});

onUnmounted(() => {
  window.removeEventListener('resize', updateWidth);
});

const getDay = (dateStr) => (dateStr ? dateStr.split('-')[2] : '');
const getMonthShort = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('id-ID', { month: 'short' }).replace('.', '');
};
const formatTanggal = (dateStr) => {
  if (!dateStr) return '-';
  const [y, , d] = dateStr.split('-');
  return `${parseInt(d, 10)} ${getMonthShort(dateStr)} ${y}`;
};
const formatRupiah = (angka) => Number(angka || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
</script>
