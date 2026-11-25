<template>
  <div class="flex flex-col gap-6">
    <div class="ui-stagger ui-stagger-1 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-[26px] font-bold text-[#172b4d] tracking-tight">Riwayat Pembayaran</h1>
      </div>

      <div class="ui-segmented flex-wrap w-fit">
        <button
          v-for="f in filters"
          :key="f.value"
          @click="activeFilter = f.value"
          :class="[
            'ui-segmented-item text-xs',
            activeFilter === f.value ? 'ui-segmented-item-active' : 'ui-segmented-item-idle hover:text-[#172b4d]'
          ]"
        >
          {{ f.label }}
        </button>
      </div>
    </div>

    <div v-if="isLoading" class="ui-panel-padded space-y-3">
      <div v-for="n in 3" :key="n" class="h-20 bg-[#f3f8fe] rounded-[12px] animate-pulse"></div>
    </div>

    <div v-else-if="filteredItems.length === 0" class="ui-panel-padded text-center py-14 ui-stagger ui-stagger-2">
      <div class="w-12 h-12 mx-auto rounded-[12px] bg-[#e6eff9] text-[#52657d] flex items-center justify-center mb-3">
        <UiIcon name="receipt_long" :size="24" :weight="500" />
      </div>
      <p class="text-base font-bold text-[#172b4d]">Belum ada transaksi</p>
      <p class="text-sm text-[#52657d] mt-1">Buka menu Bayar Infak untuk membayar.</p>
    </div>

    <div v-else class="flex flex-col gap-3.5 ui-stagger ui-stagger-2">
      <div
        v-for="item in filteredItems"
        :key="item.id"
        class="ui-panel ui-panel-interactive p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
      >
        <div class="flex items-start gap-3.5">
          <div
            class="w-11 h-11 rounded-[10px] flex items-center justify-center shrink-0"
            :class="item.metode === 'online' ? 'bg-[#e8f2ff] text-[#1264c7]' : 'bg-[#fff3df] text-[#aa480e]'"
          >
            <UiIcon :name="item.metode === 'online' ? 'qr_code_2' : 'payments'" :size="20" :weight="600" />
          </div>
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <span class="text-xs font-mono font-bold text-[#172b4d]">{{ item.kode_referensi }}</span>
              <span
                class="text-xs font-semibold px-2.5 py-0.5 rounded-[7px]"
                :class="statusClass(item.status)"
              >
                {{ statusLabel(item.status) }}
              </span>
            </div>
            <p class="text-sm font-bold text-[#172b4d] mt-1.5">
              {{ item.siswa_nama }} · {{ item.kelas_nama }}
            </p>
            <p class="text-xs text-[#52657d] mt-1">
              Bulan: {{ item.bulan_labels.join(', ') }} · {{ formatDate(item.created_at) }}
            </p>
          </div>
        </div>

        <div class="flex sm:flex-col items-center sm:items-end justify-between gap-2 shrink-0 pt-3 sm:pt-0 border-t sm:border-t-0 border-black/[0.07]">
          <p class="text-base font-extrabold font-mono tabular-nums text-[#172b4d]">
            Rp {{ formatRupiah(item.total_nominal) }}
          </p>
          <router-link
            v-if="item.status === 'pending' || item.status === 'waiting_offline'"
            :to="{ path: '/wali/bayar', query: { siswa_id: item.siswa_id, ref: item.kode_referensi } }"
            class="min-h-[36px] inline-flex items-center gap-1.5 text-xs font-semibold text-[#1264c7] hover:underline"
          >
            <span>{{ item.metode === 'online' ? 'Lanjutkan Scan QRIS' : 'Lihat Slip Setoran' }}</span>
            <UiIcon name="arrow_forward" :size="14" :weight="600" directional />
          </router-link>
          <span v-else-if="item.status === 'paid' && item.confirmed_by_name" class="text-xs text-[#52657d]">
            Diverifikasi: {{ item.confirmed_by_name }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const isLoading = ref(true);
const items = ref([]);
const activeFilter = ref('all');

const filters = [
  { label: 'Semua', value: 'all' },
  { label: 'Online QRIS', value: 'online' },
  { label: 'Offline Wali Kelas', value: 'offline' },
];

const filteredItems = computed(() => {
  if (activeFilter.value === 'all') return items.value;
  return items.value.filter(i => i.metode === activeFilter.value);
});

const formatRupiah = (n) => Number(n || 0).toLocaleString('id-ID');
const formatDate = (iso) => {
  if (!iso) return '-';
  return new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const statusLabel = (s) => {
  if (s === 'paid') return 'Lunas';
  if (s === 'waiting_offline') return 'Menunggu Wali Kelas';
  if (s === 'pending') return 'Menunggu QRIS';
  if (s === 'expired') return 'Kedaluwarsa';
  return 'Dibatalkan';
};

const statusClass = (s) => {
  if (s === 'paid') return 'bg-[#e8f2ff] text-[#1264c7]';
  if (s === 'waiting_offline') return 'bg-[#fff3df] text-[#aa480e]';
  if (s === 'pending') return 'bg-[#e6eff9] text-[#17385f]';
  return 'bg-[#FDF2F4] text-[#9F1239]';
};

onMounted(async () => {
  try {
    const res = await axios.get('/wali/riwayat');
    items.value = res.data || [];
  } catch (err) {
    console.error(err);
  } finally {
    isLoading.value = false;
  }
});
</script>
