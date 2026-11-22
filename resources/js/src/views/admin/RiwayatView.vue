<template>
  <div class="ui-page flex flex-col gap-6">
    <!-- Header & Search -->
    <div class="ui-stagger ui-stagger-1 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-[26px] font-bold text-[#1C1917] tracking-tight">Riwayat aktivitas</h1>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <template v-if="confirmingClear">
          <button @click="executeClearLog" class="ui-btn-danger">Ya, Kosongkan Semua Log</button>
          <button @click="confirmingClear = false" class="ui-btn-secondary">Batal</button>
        </template>
        <button v-else @click="confirmingClear = true" class="ui-btn-danger ui-btn-icon-l">
          <UiIcon name="delete_sweep" :size="17" :weight="600" />
          <span>Bersihkan Log</span>
        </button>
      </div>
    </div>

    <!-- Search Input -->
    <div class="ui-stagger ui-stagger-2 relative max-w-md">
      <UiIcon name="search" :size="18" :weight="500" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#78716C]" />
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Cari nama siswa, nominal, atau aktivitas..."
        class="ui-input pl-10"
      >
    </div>

    <!-- Timeline Groups -->
    <div v-if="isLoading" class="ui-panel-padded space-y-4">
      <div v-for="n in 4" :key="n" class="h-16 bg-[#F7F4ED] rounded-[12px] animate-pulse"></div>
    </div>

    <div v-else-if="groupedLogs.length === 0" class="ui-panel-padded text-center py-14 ui-stagger ui-stagger-2">
      <div class="w-12 h-12 mx-auto rounded-[12px] bg-[#ECE8DF] text-[#78716C] flex items-center justify-center mb-3">
        <UiIcon name="history" :size="24" :weight="500" />
      </div>
      <p class="text-base font-bold text-[#1C1917]">Belum ada log aktivitas ditemukan</p>
      <p class="text-sm text-[#57534E] mt-1">Setiap perubahan transaksi dan data siswa akan otomatis terekam di sini.</p>
    </div>

    <div v-else class="flex flex-col gap-6 ui-stagger ui-stagger-3">
      <section v-for="group in groupedLogs" :key="group.dateLabel" class="flex flex-col gap-3">
        <h2 class="text-sm font-bold text-[#44403C] pb-1.5 border-b border-black/[0.07]">
          {{ group.dateLabel }}
        </h2>

        <div class="ui-panel divide-y divide-black/[0.05]">
          <div
            v-for="item in group.items"
            :key="item.id"
            class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
          >
            <div class="flex items-start gap-3.5 min-w-0">
              <div
                class="w-10 h-10 rounded-[10px] text-white flex items-center justify-center shrink-0"
                :class="getIconBgColor(item.type)"
              >
                <UiIcon :name="getIconName(item.type)" :size="18" :weight="600" />
              </div>
              <div class="min-w-0">
                <p class="text-sm font-bold text-[#1C1917]">{{ item.title }}</p>
                <p class="text-xs text-[#57534E] mt-0.5 leading-relaxed">{{ item.description }}</p>
              </div>
            </div>

            <div class="flex sm:flex-col items-center sm:items-end justify-between gap-1 shrink-0 pl-13 sm:pl-0">
              <span
                v-if="item.amount"
                class="text-sm font-extrabold font-mono tabular-nums"
                :class="item.type === 'expense' ? 'text-[#9F1239]' : 'text-[#A8551E]'"
              >
                {{ item.type === 'expense' ? '-' : '+' }} Rp {{ formatRupiah(item.amount) }}
              </span>
              <span class="text-xs font-mono text-[#78716C] tabular-nums">{{ item.time }}</span>
            </div>
          </div>
        </div>
      </section>

      <div v-if="hasMorePages" class="flex justify-center pt-2">
        <button @click="loadMore" :disabled="isLoadingMore" class="ui-btn-secondary ui-btn-icon-l px-6">
          <UiIcon v-if="isLoadingMore" name="sync" :size="17" :weight="600" class="animate-spin" />
          <UiIcon v-else name="keyboard_arrow_down" :size="17" :weight="600" />
          <span>{{ isLoadingMore ? 'Memuat...' : 'Muat Lebih Banyak' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import { notify } from '@/utils/toast';

const searchQuery = ref('');
const isLoading = ref(false);
const isLoadingMore = ref(false);
const confirmingClear = ref(false);
const allLogs = ref([]);
const currentPage = ref(1);
const hasMorePages = ref(true);

const fetchLogs = async (page = 1, append = false) => {
  if (append) isLoadingMore.value = true;
  else isLoading.value = true;

  try {
    const params = { per_page: 30, page };
    if (searchQuery.value.trim()) params.search = searchQuery.value.trim();

    const res = await axios.get('/log-aktivitas', { params });
    const newLogs = res.data.data || res.data;

    if (append) allLogs.value = [...allLogs.value, ...newLogs];
    else allLogs.value = newLogs;

    hasMorePages.value = res.data.current_page < res.data.last_page;
    currentPage.value = page;
  } catch (error) {
    console.error('Gagal mengambil riwayat', error);
    hasMorePages.value = false;
  } finally {
    isLoading.value = false;
    isLoadingMore.value = false;
  }
};

const loadMore = () => {
  if (!isLoadingMore.value && hasMorePages.value) {
    fetchLogs(currentPage.value + 1, true);
  }
};

let searchTimeout = null;
watch(searchQuery, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchLogs(1, false);
  }, 300);
});

onMounted(() => {
  fetchLogs(1, false);
});

const groupedLogs = computed(() => {
  if (!allLogs.value.length) return [];
  const groups = {};
  allLogs.value.forEach(log => {
    const date = new Date(log.created_at);
    const dateStr = date.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    const timeStr = date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

    if (!groups[dateStr]) groups[dateStr] = { dateLabel: dateStr, items: [] };
    groups[dateStr].items.push({
      id: log.id,
      type: log.type,
      title: log.title,
      description: log.description,
      time: timeStr,
      amount: log.amount
    });
  });
  return Object.values(groups);
});

const getIconName = (type) => {
  switch (type) {
    case 'income': return 'account_balance_wallet';
    case 'expense': return 'money_off';
    case 'system': return 'person_add';
    default: return 'history';
  }
};

const getIconBgColor = (type) => {
  switch (type) {
    case 'income': return 'bg-[#A8551E]';
    case 'expense': return 'bg-[#9F1239]';
    case 'system': return 'bg-[#57534E]';
    default: return 'bg-[#292524]';
  }
};

const formatRupiah = (angka) => Number(angka || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');

const executeClearLog = async () => {
  try {
    await axios.post('/log-aktivitas/clear');
    allLogs.value = [];
    confirmingClear.value = false;
    notify('Log aktivitas berhasil dikosongkan.', 'info');
  } catch (error) {
    console.error('Gagal menghapus log', error);
    notify('Gagal menghapus log aktivitas.', 'error');
  }
};
</script>
