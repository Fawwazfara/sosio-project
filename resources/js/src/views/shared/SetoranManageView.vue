<template>
  <div class="ui-page flex flex-col gap-6">
    <!-- Header -->
    <div class="ui-stagger ui-stagger-1 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-[26px] font-bold text-[#0F172A] tracking-tight">Konfirmasi setoran</h1>
      </div>
      <button
        @click="fetchItems"
        class="ui-btn-secondary ui-btn-icon-l self-start sm:self-auto"
      >
        <UiIcon name="sync" :size="16" :weight="600" :class="{ 'animate-spin': isLoading }" />
        <span>Muat Ulang</span>
      </button>
    </div>

    <!-- Summary Strip -->
    <section class="ui-stagger ui-stagger-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="ui-panel p-5">
        <p class="text-xs font-semibold text-[#9A6B16]">Menunggu Tunai</p>
        <p class="text-2xl font-extrabold font-mono tabular-nums text-[#0F172A] mt-2">{{ summary.waiting_offline }}</p>
        <p class="text-xs text-[#64748B] mt-1">Perlu konfirmasi wali kelas</p>
      </div>
      <div class="ui-panel p-5">
        <p class="text-xs font-semibold text-[#475569]">QRIS Menunggu</p>
        <p class="text-2xl font-extrabold font-mono tabular-nums text-[#0F172A] mt-2">{{ summary.pending_online }}</p>
        <p class="text-xs text-[#64748B] mt-1">Barcode belum dibayar</p>
      </div>
      <div class="ui-panel p-5">
        <p class="text-xs font-semibold text-[#1D4ED8]">Lunas Online (QRIS)</p>
        <p class="text-2xl font-extrabold font-mono tabular-nums text-[#1D4ED8] mt-2">{{ summary.paid_online }}</p>
        <p class="text-xs text-[#64748B] mt-1">Terverifikasi otomatis</p>
      </div>
      <div class="ui-panel p-5">
        <p class="text-xs font-semibold text-[#163A70]">Lunas Tunai Wali</p>
        <p class="text-2xl font-extrabold font-mono tabular-nums text-[#163A70] mt-2">{{ summary.paid_offline }}</p>
        <p class="text-xs text-[#64748B] mt-1">Dikonfirmasi wali kelas</p>
      </div>
    </section>

    <!-- Concentric Filter Tabs -->
    <div class="ui-stagger ui-stagger-3 ui-segmented flex-wrap w-fit">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        @click="activeStatus = tab.value"
        :class="[
          'ui-segmented-item text-xs',
          activeStatus === tab.value ? 'ui-segmented-item-active' : 'ui-segmented-item-idle hover:text-[#0F172A]'
        ]"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- List -->
    <div v-if="isLoading" class="ui-panel-padded space-y-3">
      <div v-for="n in 4" :key="n" class="h-20 bg-[#F8FAFC] rounded-[12px] animate-pulse"></div>
    </div>

    <div v-else-if="filteredItems.length === 0" class="ui-panel-padded text-center py-14">
      <div class="w-12 h-12 mx-auto rounded-[12px] bg-[#EAEFF6] text-[#64748B] flex items-center justify-center mb-3">
        <UiIcon name="inbox" :size="24" :weight="500" />
      </div>
      <p class="text-base font-bold text-[#0F172A]">Belum ada pengajuan pada kategori ini</p>
      <p class="text-sm text-[#475569] mt-1">Semua transaksi yang cocok dengan filter akan tampil di sini.</p>
    </div>

    <div v-else class="flex flex-col gap-3.5 ui-stagger ui-stagger-3">
      <div
        v-for="item in filteredItems"
        :key="item.id"
        class="ui-panel ui-panel-interactive p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4"
      >
        <div class="flex items-start gap-3.5 min-w-0">
          <div
            class="w-11 h-11 rounded-[10px] flex items-center justify-center shrink-0"
            :class="item.metode === 'online' ? 'bg-[#EFF6FF] text-[#1D4ED8]' : 'bg-[#FAF4E4] text-[#9A6B16]'"
          >
            <UiIcon :name="item.metode === 'online' ? 'qr_code_2' : 'payments'" :size="20" :weight="600" />
          </div>

          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <span class="text-xs font-mono font-bold text-[#0F172A]">{{ item.kode_referensi }}</span>
              <span class="text-xs font-semibold px-2.5 py-0.5 rounded-[7px] bg-[#EAEFF6] text-[#334155]">
                {{ item.kelas_nama }}
              </span>
              <span class="text-xs font-semibold px-2.5 py-0.5 rounded-[7px]" :class="statusClass(item.status)">
                {{ statusLabel(item.status) }}
              </span>
            </div>

            <p class="text-sm font-bold text-[#0F172A] mt-1.5">
              {{ item.siswa_nama }}
              <span class="text-xs font-normal text-[#64748B]">(Wali: {{ item.wali_nama }})</span>
            </p>
            <p class="text-xs text-[#475569] mt-1">
              Bulan: <strong class="text-[#0F172A] font-semibold">{{ item.bulan_labels.join(', ') }}</strong>
              <span v-if="item.catatan"> · Catatan: "{{ item.catatan }}"</span>
            </p>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between lg:justify-end gap-4 shrink-0 pt-3 lg:pt-0 border-t lg:border-t-0 border-black/[0.07]">
          <div class="text-left lg:text-right">
            <span class="block text-xs text-[#64748B]">Total Nominal</span>
            <span class="text-lg font-extrabold font-mono tabular-nums text-[#0F172A]">
              Rp {{ formatRupiah(item.total_nominal) }}
            </span>
          </div>

          <!-- Inline 2-step confirmation -->
          <div v-if="item.status === 'waiting_offline'" class="flex flex-wrap items-center gap-2">
            <template v-if="confirmState.id === item.id && confirmState.action === 'confirm'">
              <button
                @click="executeConfirmOffline(item)"
                :disabled="processingId === item.id"
                class="ui-btn-primary"
              >
                {{ processingId === item.id ? 'Menyimpan...' : 'Ya, Sudah Terima Tunai' }}
              </button>
              <button
                @click="clearConfirm"
                :disabled="processingId === item.id"
                class="ui-btn-secondary"
              >
                Batal
              </button>
            </template>

            <template v-else-if="confirmState.id === item.id && confirmState.action === 'reject'">
              <button
                @click="executeRejectOffline(item)"
                :disabled="processingId === item.id"
                class="ui-btn-danger"
              >
                {{ processingId === item.id ? 'Membatalkan...' : 'Ya, Batalkan Slip' }}
              </button>
              <button
                @click="clearConfirm"
                :disabled="processingId === item.id"
                class="ui-btn-secondary"
              >
                Tidak Jadi
              </button>
            </template>

            <template v-else>
              <button
                @click="askConfirm(item.id, 'confirm')"
                :disabled="processingId === item.id"
                class="ui-btn-primary ui-btn-icon-l"
              >
                <UiIcon name="check_circle" :size="17" :weight="600" />
                <span>Konfirmasi Terima Uang</span>
              </button>
              <button
                @click="askConfirm(item.id, 'reject')"
                :disabled="processingId === item.id"
                class="ui-btn-danger"
              >
                Batalkan
              </button>
            </template>
          </div>

          <span v-else-if="item.status === 'paid' && item.confirmed_by_name" class="text-xs font-medium text-[#64748B]">
            Diverifikasi: {{ item.confirmed_by_name }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { notify } from '@/utils/toast';

const isLoading = ref(true);
const processingId = ref(null);
const items = ref([]);
const summary = ref({
  waiting_offline: 0,
  pending_online: 0,
  paid_online: 0,
  paid_offline: 0,
});
const activeStatus = ref('waiting_offline');
const confirmState = reactive({ id: null, action: null });

const tabs = [
  { label: 'Menunggu Tunai Wali Kelas', value: 'waiting_offline' },
  { label: 'Semua Transaksi', value: 'all' },
  { label: 'Lunas', value: 'paid' },
  { label: 'QRIS Pending', value: 'pending' },
];

const filteredItems = computed(() => {
  if (activeStatus.value === 'all') return items.value;
  return items.value.filter(i => i.status === activeStatus.value);
});

const formatRupiah = (n) => Number(n || 0).toLocaleString('id-ID');

const statusLabel = (s) => {
  if (s === 'paid') return 'Lunas';
  if (s === 'waiting_offline') return 'Menunggu Setor Tunai';
  if (s === 'pending') return 'QRIS Pending';
  if (s === 'expired') return 'Kedaluwarsa';
  return 'Dibatalkan';
};

const statusClass = (s) => {
  if (s === 'paid') return 'bg-[#EFF6FF] text-[#1D4ED8]';
  if (s === 'waiting_offline') return 'bg-[#FAF4E4] text-[#9A6B16]';
  if (s === 'pending') return 'bg-[#EAEFF6] text-[#163A70]';
  return 'bg-[#FFF1F2] text-[#9F1239]';
};

const askConfirm = (id, action) => {
  confirmState.id = id;
  confirmState.action = action;
};

const clearConfirm = () => {
  confirmState.id = null;
  confirmState.action = null;
};

const fetchItems = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get('/pembayaran');
    items.value = res.data.items || [];
    summary.value = res.data.summary || summary.value;
  } catch (err) {
    console.error(err);
    notify('Gagal memuat daftar setoran.', 'error');
  } finally {
    isLoading.value = false;
  }
};

const executeConfirmOffline = async (item) => {
  processingId.value = item.id;
  try {
    await axios.post(`/pembayaran/${item.id}/confirm-offline`);
    clearConfirm();
    notify(`Setoran tunai ${item.kode_referensi} (${item.siswa_nama}) resmi lunas.`, 'success');
    await fetchItems();
  } catch (err) {
    notify(err.response?.data?.message || 'Gagal mengonfirmasi setoran.', 'error');
  } finally {
    processingId.value = null;
  }
};

const executeRejectOffline = async (item) => {
  processingId.value = item.id;
  try {
    await axios.post(`/pembayaran/${item.id}/reject`);
    clearConfirm();
    notify(`Pengajuan ${item.kode_referensi} telah dibatalkan.`, 'info');
    await fetchItems();
  } catch (err) {
    notify(err.response?.data?.message || 'Gagal membatalkan pengajuan.', 'error');
  } finally {
    processingId.value = null;
  }
};

onMounted(fetchItems);
</script>
