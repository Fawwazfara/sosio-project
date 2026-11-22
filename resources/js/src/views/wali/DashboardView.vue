<template>
  <div class="flex flex-col gap-6 sm:gap-8">
    <!-- Loading -->
    <div v-if="isLoading" class="ui-panel-padded space-y-4">
      <div class="h-6 w-56 bg-[#ECE8DF] rounded-[8px] animate-pulse"></div>
      <div class="h-4 w-80 bg-[#F5F3EE] rounded-[8px] animate-pulse"></div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 pt-4">
        <div v-for="n in 8" :key="n" class="h-28 bg-[#F7F4ED] rounded-[12px] animate-pulse"></div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="students.length === 0" class="ui-panel-padded text-center py-14 ui-stagger ui-stagger-1">
      <div class="w-12 h-12 mx-auto rounded-[12px] bg-[#ECE8DF] text-[#78716C] flex items-center justify-center mb-3.5">
        <UiIcon name="person_off" :size="24" :weight="500" />
      </div>
      <h1 class="text-xl font-bold text-[#1C1917] tracking-tight">Belum ada siswa</h1>
      <p class="text-sm text-[#57534E] mt-2">Hubungi tata usaha untuk menautkan data siswa.</p>
    </div>

    <template v-else>
      <!-- Child Switcher (jika wali punya lebih dari 1 anak, concentric 4px padding + 10px radius = 14px outer) -->
      <div v-if="students.length > 1" class="ui-stagger ui-stagger-1 flex flex-wrap gap-2.5 p-1.5 rounded-[16px] bg-[#ECE8DF] w-fit shadow-[inset_0_1px_2px_0_oklch(0.22_0.015_65_/_0.06),0_0_0_1px_oklch(0.22_0.015_65_/_0.06)]">
        <button
          v-for="(s, idx) in students"
          :key="s.id"
          @click="selectedIdx = idx"
          :class="[
            'min-h-[48px] ps-3.5 pe-4 py-2 rounded-[10px] border border-transparent text-left transition-[background-color,color,box-shadow,transform] duration-150 ease-out active:scale-[0.96] flex items-center gap-3',
            selectedIdx === idx
              ? 'bg-[#292524] text-white shadow-[var(--shadow-btn-navy)]'
              : 'bg-[#FDFCF9]/80 text-[#1C1917] hover:bg-[#FDFCF9] shadow-[var(--shadow-border)]'
          ]"
        >
          <UiIcon name="school" :size="18" :weight="600" :class="selectedIdx === idx ? 'text-[#E7C0A1]' : 'text-[#A8551E]'" />
          <div>
            <p class="text-sm font-bold leading-tight">{{ s.nama_lengkap }}</p>
            <p class="text-xs mt-0.5 tabular-nums" :class="selectedIdx === idx ? 'text-[#D6CFC2]' : 'text-[#78716C]'">
              {{ s.kelas }} · NIS {{ s.nis }}
            </p>
          </div>
        </button>
      </div>

      <!-- Student Summary Hero Card -->
      <section class="ui-stagger ui-stagger-1 ui-panel-padded relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 pb-6 border-b border-black/[0.07]">
          <div>
            <h1 class="text-2xl sm:text-[26px] font-bold text-[#1C1917] tracking-tight">
              {{ activeStudent.nama_lengkap }}
            </h1>
            <p class="text-sm text-[#57534E] mt-1">
              {{ activeStudent.kelas }} · Wali Kelas: <strong class="text-[#1C1917] font-semibold">{{ activeStudent.wali_kelas }}</strong>
            </p>
            <div class="flex flex-wrap items-center gap-2 mt-3">
              <span class="text-xs font-mono font-semibold px-2.5 py-1 rounded-[8px] bg-[#F9EFE7] text-[#7C3A10] shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.2)] tabular-nums">
                NIS {{ activeStudent.nis }}
              </span>
              <span class="text-xs font-medium px-2.5 py-1 rounded-[8px] bg-[#F7F4ED] text-[#57534E] shadow-[inset_0_0_0_1px_oklch(0.22_0.015_65_/_0.08)]">
                Tahun Ajaran {{ academicYear }}
              </span>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row sm:items-center gap-3 shrink-0">
            <div class="px-4 py-2.5 rounded-[12px] bg-[#F7F4ED] shadow-[inset_0_1px_2px_0_oklch(0.22_0.015_65_/_0.05),0_0_0_1px_oklch(0.22_0.015_65_/_0.07)]">
              <span class="block text-xs font-medium text-[#78716C]">Progress Tahunan</span>
              <span class="text-base font-bold font-mono tabular-nums text-[#A8551E] mt-0.5 block">
                {{ activeStudent.paid_count }} / 12 Bulan Lunas
              </span>
            </div>
            <router-link
              :to="{ path: '/wali/bayar', query: { siswa_id: activeStudent.id } }"
              class="ui-btn-primary ui-btn-icon-l px-5"
            >
              <UiIcon name="qr_code_2" :size="18" :weight="600" />
              <span>Bayar Infak</span>
            </router-link>
          </div>
        </div>

        <!-- Progress Bar (transition-[width] instead of transition-all) -->
        <div class="pt-5">
          <div class="flex items-center justify-between text-xs text-[#57534E] mb-2">
            <span>Ketercapaian tahun ajaran</span>
            <span class="font-mono font-bold text-[#1C1917] tabular-nums">
              {{ Math.round((activeStudent.paid_count / 12) * 100) }}%
            </span>
          </div>
          <div class="w-full h-2.5 bg-[#ECE8DF] rounded-full overflow-hidden shadow-[inset_0_1px_1px_oklch(0.22_0.015_65_/_0.07)]">
            <div
              class="h-full bg-[#A8551E] rounded-full transition-[width] duration-300 ease-out-expo"
              :style="{ width: Math.round((activeStudent.paid_count / 12) * 100) + '%' }"
            ></div>
          </div>
        </div>
      </section>

      <!-- Active / Pending Payments Notice -->
      <section v-if="activeStudent.active_payments.length > 0" class="ui-stagger ui-stagger-2 flex flex-col gap-3.5">
        <h2 class="text-lg font-bold text-[#1C1917] tracking-tight">Pembayaran aktif</h2>
        <div
          v-for="p in activeStudent.active_payments"
          :key="p.id"
          class="rounded-[16px] border border-transparent p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
          :class="p.metode === 'online'
            ? 'bg-[#F9EFE7] shadow-[0_0_0_1px_oklch(0.55_0.13_42_/_0.22),0_2px_6px_-1px_oklch(0.45_0.1_42_/_0.08)]'
            : 'bg-[#FAF4E4] shadow-[0_0_0_1px_oklch(0.62_0.12_75_/_0.28),0_2px_6px_-1px_oklch(0.55_0.1_75_/_0.08)]'"
        >
          <div class="flex items-start gap-3.5">
            <div
              class="w-11 h-11 rounded-[10px] flex items-center justify-center shrink-0"
              :class="p.metode === 'online' ? 'bg-[#A8551E] text-white' : 'bg-[#9A6B16] text-white'"
            >
              <UiIcon :name="p.metode === 'online' ? 'qr_code_scanner' : 'payments'" :size="20" :weight="600" />
            </div>
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-mono font-bold text-[#1C1917]">{{ p.kode_referensi }}</span>
                <span
                  class="text-xs font-semibold px-2.5 py-0.5 rounded-[7px]"
                  :class="p.metode === 'online' ? 'bg-[#F2DFCF] text-[#7C3A10]' : 'bg-[#F0DFB0] text-[#6E4A0B]'"
                >
                  {{ p.metode === 'online' ? 'Menunggu Scan QRIS' : 'Menunggu Penyerahan Tunai' }}
                </span>
              </div>
              <p class="text-sm text-[#1C1917] font-semibold mt-1.5">
                Bulan: {{ p.bulan_labels.join(', ') }} ·
                <span class="font-mono tabular-nums">Rp {{ formatRupiah(p.total_nominal) }}</span>
              </p>
              <p v-if="p.metode === 'offline'" class="text-xs text-[#6E4A0B] mt-1">
                Serahkan uang tunai ke <strong>{{ p.wali_kelas_nama }}</strong> lalu tunjukkan kode slip ini.
              </p>
            </div>
          </div>

          <router-link
            :to="{ path: '/wali/bayar', query: { siswa_id: activeStudent.id, ref: p.kode_referensi } }"
            class="ui-btn-navy ui-btn-icon-l shrink-0"
          >
            <UiIcon :name="p.metode === 'online' ? 'qr_code_2' : 'receipt'" :size="17" :weight="600" />
            <span>{{ p.metode === 'online' ? 'Buka QRIS' : 'Lihat Slip' }}</span>
          </router-link>
        </div>
      </section>

      <!-- 12-Month Academic Ledger Grid -->
      <section class="ui-stagger ui-stagger-3 ui-panel-padded">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
          <div>
            <h2 class="text-lg font-bold text-[#1C1917] tracking-tight">Kartu Kontrol Infak ({{ academicYear }})</h2>
            <p class="text-sm text-[#57534E] mt-0.5">Pilih bulan untuk membayar.</p>
          </div>
          <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-[#57534E]">
            <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-[3px] bg-[#A8551E]"></span> Lunas</span>
            <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-[3px] bg-[#9A6B16]"></span> Proses</span>
            <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-[3px] border border-dashed border-[#D6D0C4]"></span> Belum Bayar</span>
          </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
          <button
            v-for="m in activeStudent.months"
            :key="m.english"
            type="button"
            :disabled="m.paid || Boolean(m.pending)"
            @click="!m.paid && !m.pending ? goPayMonth(m.english) : null"
            :class="[
              'rounded-[8px] p-3 sm:p-4 text-left flex flex-col justify-between min-h-[116px] transition-[background-color,box-shadow,border-color,transform] duration-150 ease-out',
              m.paid
                ? 'bg-[#F9EFE7] border border-transparent shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.22)] cursor-default'
                : m.pending
                  ? 'bg-[#FAF4E4] border border-transparent shadow-[inset_0_0_0_1px_oklch(0.62_0.12_75_/_0.28)] cursor-default'
                  : 'bg-[#F7F4ED] border border-dashed border-[#D6D0C4] hover:border-[#A8551E] hover:bg-[#F9EFE7]/60 hover:shadow-[var(--shadow-border)] enabled:active:scale-[0.96] cursor-pointer'
            ]"
          >
            <div class="flex flex-wrap items-center justify-between gap-2 w-full">
              <span class="text-sm font-bold text-[#1C1917]">{{ m.label }}</span>
              <span
                v-if="m.paid"
                class="text-xs font-semibold px-2 py-0.5 rounded-[6px] bg-[#A8551E] text-white"
              >
                Lunas
              </span>
              <span
                v-else-if="m.pending"
                class="text-xs font-semibold px-2 py-0.5 rounded-[6px] bg-[#9A6B16] text-white"
              >
                {{ m.pending.metode === 'online' ? 'QRIS' : 'Tunai' }}
              </span>
              <span
                v-else
                class="text-xs font-semibold text-[#9F1239]"
              >
                Belum
              </span>
            </div>

            <div class="mt-4 w-full">
              <template v-if="m.paid">
                <p class="text-sm font-bold font-mono tabular-nums text-[#7C3A10]">Rp {{ formatRupiah(m.jumlah) }}</p>
                <p class="text-xs text-[#57534E] mt-1">
                  {{ formatMethodLabel(m.metode_bayar) }} · {{ m.tanggal_bayar }}
                </p>
              </template>
              <template v-else-if="m.pending">
                <p class="text-xs font-semibold text-[#7D5714]">
                  {{ m.pending.metode === 'online' ? 'Menunggu scan QRIS' : 'Menunggu wali kelas' }}
                </p>
                <p class="text-xs font-mono text-[#6E4A0B] mt-1 tabular-nums">{{ m.pending.kode_referensi }}</p>
              </template>
              <template v-else>
                <p class="text-xs font-semibold text-[#A8551E] inline-flex items-center gap-1.5">
                  <span>Bayar bulan ini</span>
                  <UiIcon name="arrow_forward" :size="14" :weight="600" directional />
                </p>
              </template>
            </div>
          </button>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const isLoading = ref(true);
const students = ref([]);
const academicYear = ref('');
const selectedIdx = ref(0);

const activeStudent = computed(() => students.value[selectedIdx.value] || null);

const formatRupiah = (n) => Number(n || 0).toLocaleString('id-ID');

const formatMethodLabel = (m) => {
  if (m === 'online_qris') return 'QRIS Online';
  if (m === 'offline_wali') return 'Tunai Wali Kelas';
  return 'Tata Usaha';
};

const goPayMonth = (monthEnglish) => {
  router.push({
    path: '/wali/bayar',
    query: {
      siswa_id: activeStudent.value.id,
      month: monthEnglish,
    },
  });
};

onMounted(async () => {
  try {
    const res = await axios.get('/wali/dashboard');
    students.value = res.data.students || [];
    academicYear.value = res.data.academic_year || '';
  } catch (err) {
    console.error(err);
  } finally {
    isLoading.value = false;
  }
});
</script>
