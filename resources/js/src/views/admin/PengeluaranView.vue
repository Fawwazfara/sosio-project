<template>
  <div class="ui-page flex flex-col gap-6">
    <!-- Header & Month Filter -->
    <div class="ui-stagger ui-stagger-1 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-[26px] font-bold text-[#1C1917] tracking-tight">Catatan Pengeluaran Kas</h1>
        <p class="text-sm text-[#57534E] mt-1">Catat operasional dan pemeliharaan madrasah secara transparan.</p>
      </div>
      <div class="ui-panel px-4 py-3">
        <span class="block text-xs text-[#78716C]">Total Terfilter ({{ activeMonth }})</span>
        <span class="text-lg font-extrabold font-mono tabular-nums text-[#9F1239]">Rp {{ formatRupiah(totalPengeluaran) }}</span>
      </div>
    </div>

    <!-- Month Filter Strip (Concentric segmented pill rail) -->
    <div class="ui-stagger ui-stagger-2 flex gap-1.5 overflow-x-auto p-1 rounded-[14px] bg-[#ECE8DF] shadow-[inset_0_1px_2px_0_oklch(0.22_0.015_65_/_0.06),0_0_0_1px_oklch(0.22_0.015_65_/_0.06)] no-scrollbar">
      <button
        v-for="m in filterOptions"
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

    <div class="ui-stagger ui-stagger-3 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <!-- Form Pengeluaran -->
      <section class="lg:col-span-5 ui-panel-padded space-y-5">
        <h2 class="text-base font-bold text-[#1C1917] pb-3 border-b border-black/[0.07]">Catat Pengeluaran Baru</h2>

        <form @submit.prevent="submitPengeluaran" class="space-y-4">
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-[#44403C]" for="keperluan">Keperluan / Keterangan</label>
            <input id="keperluan" v-model="form.keperluan" type="text" required placeholder="Contoh: Pembelian spidol &amp; penghapus kelas" class="ui-input">
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-[#44403C]" for="nominalKeluar">Nominal (Rp)</label>
            <input id="nominalKeluar" v-model.number="form.nominal" type="number" min="500" required placeholder="50000" class="ui-input font-mono font-bold tabular-nums">
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-[#44403C]" for="tanggalKeluar">Tanggal</label>
            <input id="tanggalKeluar" v-model="form.tanggal" type="date" required class="ui-input">
          </div>

          <button type="submit" :disabled="isSubmitting" class="ui-btn-primary ui-btn-icon-l w-full">
            <UiIcon v-if="isSubmitting" name="progress_activity" :size="17" :weight="600" class="animate-spin" />
            <UiIcon v-else name="add" :size="17" :weight="600" />
            <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan Pengeluaran' }}</span>
          </button>
        </form>
      </section>

      <!-- List Riwayat -->
      <section class="lg:col-span-7 flex flex-col gap-3.5">
        <h2 class="text-base font-bold text-[#1C1917]">Daftar Pengeluaran (<span class="tabular-nums">{{ filteredRiwayat.length }}</span>)</h2>

        <div v-if="filteredRiwayat.length === 0" class="ui-panel-padded text-center py-14">
          <div class="w-12 h-12 mx-auto rounded-[12px] bg-[#ECE8DF] text-[#78716C] flex items-center justify-center mb-3">
            <UiIcon name="money_off" :size="24" :weight="500" />
          </div>
          <p class="text-base font-bold text-[#1C1917]">Tidak ada catatan pengeluaran</p>
          <p class="text-sm text-[#57534E] mt-1">Pilih bulan lain atau catat pengeluaran baru di panel sebelah.</p>
        </div>

        <div
          v-for="item in filteredRiwayat"
          :key="item.id"
          class="ui-panel ui-panel-interactive p-4 sm:p-5 flex items-center justify-between gap-4"
        >
          <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-12 h-14 rounded-[11px] bg-[#F7F4ED] shadow-[inset_0_1px_2px_0_oklch(0.22_0.015_65_/_0.05),0_0_0_1px_oklch(0.22_0.015_65_/_0.07)] flex flex-col items-center justify-center shrink-0">
              <span class="text-sm font-extrabold font-mono text-[#1C1917] leading-tight tabular-nums">{{ getDay(item.tanggal) }}</span>
              <span class="text-xs font-medium text-[#78716C] leading-tight">{{ getMonthShort(item.tanggal) }}</span>
            </div>
            <div class="min-w-0">
              <p class="text-sm font-bold text-[#1C1917] truncate">{{ item.keterangan }}</p>
              <p class="text-xs font-mono font-bold text-[#9F1239] mt-1 tabular-nums">- Rp {{ formatRupiah(item.jumlah) }}</p>
            </div>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <button
              @click="confirmDelete(item)"
              aria-label="Hapus catatan pengeluaran"
              class="min-h-[42px] min-w-[42px] rounded-[10px] border border-transparent bg-[#FDF2F4] hover:bg-[#FCE4E9] shadow-[var(--shadow-btn-danger)] text-[#9F1239] flex items-center justify-center transition-[background-color,transform] duration-150 ease-out active:scale-[0.96]"
            >
              <UiIcon name="delete" :size="18" :weight="600" />
            </button>
          </div>
        </div>

        <div class="pt-2 text-center">
          <router-link to="/admin/riwayat" class="text-sm font-semibold text-[#A8551E] hover:underline inline-flex items-center gap-1">
            <span>Lihat Semua Riwayat Aktivitas</span>
            <UiIcon name="arrow_forward" :size="14" :weight="600" directional />
          </router-link>
        </div>
      </section>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="itemToDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-[#1C1917]/45 backdrop-blur-[2px] p-4 overscroll-contain" @click="cancelDelete">
      <div class="ui-panel-elevated w-full max-w-sm p-6 text-center overscroll-contain" @click.stop>
        <div class="mx-auto w-14 h-14 bg-[#FDF2F4] shadow-[var(--shadow-btn-danger)] rounded-full flex items-center justify-center mb-4 text-[#9F1239]">
          <UiIcon name="warning" :size="24" :weight="600" />
        </div>
        <h3 class="text-lg font-bold text-[#1C1917]">Hapus Pengeluaran?</h3>
        <p class="text-sm text-[#57534E] mt-2 mb-6 leading-relaxed">
          Hapus catatan <strong>{{ itemToDelete.keterangan }}</strong> senilai <strong class="font-mono tabular-nums">Rp {{ formatRupiah(itemToDelete.jumlah) }}</strong>?
        </p>
        <div class="flex gap-3">
          <button class="ui-btn-secondary flex-1" @click="cancelDelete">Batal</button>
          <button class="ui-btn-danger flex-1" @click="executeDelete">Ya, Hapus</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { notify } from '@/utils/toast';

const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const filterOptions = ['Semua', ...months];
const activeMonth = ref(months[new Date().getMonth()]);
const isSubmitting = ref(false);

const form = reactive({
  keperluan: '',
  nominal: '',
  tanggal: ''
});

const riwayat = ref([]);

const fetchPengeluaran = async () => {
  try {
    const res = await axios.get('/pengeluaran');
    riwayat.value = res.data;
  } catch (error) {
    console.error('Gagal mengambil data pengeluaran', error);
  }
};

onMounted(() => {
  form.tanggal = new Date().toISOString().split('T')[0];
  fetchPengeluaran();
});

const filteredRiwayat = computed(() => {
  if (activeMonth.value === 'Semua') return riwayat.value;
  return riwayat.value.filter(item => {
    const itemMonthIndex = new Date(item.tanggal).getMonth();
    return months[itemMonthIndex] === activeMonth.value;
  });
});

const totalPengeluaran = computed(() => {
  return filteredRiwayat.value.reduce((acc, curr) => acc + parseInt(curr.jumlah || 0, 10), 0);
});

const getDay = (dateStr) => {
  if (!dateStr) return '';
  return dateStr.split('-')[2] || dateStr.split('T')[0].split('-')[2];
};

const getMonthShort = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('id-ID', { month: 'short' }).replace('.', '');
};

const formatRupiah = (angka) => Number(angka || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');

const submitPengeluaran = async () => {
  isSubmitting.value = true;
  try {
    await axios.post('/pengeluaran', {
      keperluan: form.keperluan,
      nominal: form.nominal,
      tanggal: form.tanggal
    });
    fetchPengeluaran();
    form.keperluan = '';
    form.nominal = '';
    form.tanggal = new Date().toISOString().split('T')[0];
    notify('Catatan pengeluaran berhasil disimpan.', 'success');
  } catch (error) {
    console.error('Gagal menambah pengeluaran', error);
    notify('Gagal mencatat pengeluaran.', 'error');
  } finally {
    isSubmitting.value = false;
  }
};

const itemToDelete = ref(null);
const confirmDelete = (item) => { itemToDelete.value = item; };
const cancelDelete = () => { itemToDelete.value = null; };

const executeDelete = async () => {
  if (!itemToDelete.value) return;
  try {
    await axios.delete(`/pengeluaran/${itemToDelete.value.id}`);
    fetchPengeluaran();
    notify('Catatan pengeluaran telah dihapus.', 'info');
  } catch (error) {
    console.error('Gagal menghapus pengeluaran', error);
    notify('Gagal menghapus pengeluaran.', 'error');
  } finally {
    itemToDelete.value = null;
  }
};
</script>
