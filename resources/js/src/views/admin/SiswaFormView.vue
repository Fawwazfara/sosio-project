<template>
  <div class="ui-page flex flex-col gap-6">
    <!-- Header -->
    <div class="ui-stagger ui-stagger-1 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <router-link
          to="/admin/siswa"
          class="text-sm font-semibold text-[#52657d] hover:text-[#172b4d] inline-flex items-center gap-1.5 mb-2 transition-[color,transform] duration-150 ease-out active:scale-[0.97]"
        >
          <UiIcon name="arrow_back" :size="16" :weight="600" directional />
          <span>Kembali ke Daftar Siswa</span>
        </router-link>
        <h1 class="text-2xl sm:text-[26px] font-bold text-[#172b4d] tracking-tight">
          {{ isEdit ? 'Ubah Data Siswa &amp; Status Infak' : 'Tambah Data Siswa Baru' }}
        </h1>
      </div>
    </div>

    <div
      v-if="isSuccess"
      role="status"
      class="rounded-[14px] border border-transparent bg-[#e8f2ff] shadow-[0_0_0_1px_oklch(0.55_0.13_42_/_0.22)] p-5 flex items-center gap-3 text-[#144b88]"
    >
      <UiIcon name="check_circle" :size="22" :weight="600" filled class="text-[#1264c7]" />
      <p class="text-sm font-semibold">Data siswa tersimpan.</p>
    </div>

    <form @submit.prevent="saveSiswa" class="ui-stagger ui-stagger-2 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <!-- Left: Biodata & Kontak Wali -->
      <div class="lg:col-span-7 flex flex-col gap-6">
        <section class="ui-panel-padded space-y-5">
          <h2 class="text-base font-bold text-[#172b4d] pb-3 border-b border-black/[0.06]">Biodata Siswa</h2>

          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-[#354c68]" for="namaLengkap">Nama Lengkap Siswa</label>
            <input id="namaLengkap" v-model="form.namaLengkap" type="text" required placeholder="Contoh: Ahmad Fauzan" class="ui-input">
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#354c68]" for="kelasId">Kelas</label>
              <div class="relative">
                <select id="kelasId" v-model="form.kelas_id" required class="ui-select">
                  <option value="" disabled>Pilih Kelas</option>
                  <option v-for="k in kelasi" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                </select>
                <UiIcon name="expand_more" :size="17" :weight="600" class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-[#52657d]" />
              </div>
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#354c68]" for="blok">Blok / RT-RW</label>
              <input id="blok" v-model="form.blok" type="text" placeholder="Contoh: Blok B2 / RT 04" class="ui-input">
            </div>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-[#354c68]" for="alamat">Alamat Lengkap</label>
            <textarea id="alamat" v-model="form.alamat" rows="3" placeholder="Alamat tempat tinggal siswa" class="ui-input min-h-[88px] py-3"></textarea>
          </div>
        </section>

        <section class="ui-panel-padded space-y-5">
          <h2 class="text-base font-bold text-[#172b4d] pb-3 border-b border-black/[0.06]">Kontak Orang Tua / Wali</h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#354c68]" for="namaWali1">Nama Wali 1 (Utama)</label>
              <input id="namaWali1" v-model="form.namaWali1" type="text" placeholder="Nama ayah / ibu" class="ui-input">
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#354c68]" for="waWali1">No WhatsApp Wali 1</label>
              <input id="waWali1" v-model="form.waWali1" type="tel" placeholder="08xxxxxxxxxx" class="ui-input font-mono tabular-nums">
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-black/[0.06]">
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#354c68]" for="namaWali2">Nama Wali 2 (Cadangan)</label>
              <input id="namaWali2" v-model="form.namaWali2" type="text" placeholder="Opsional" class="ui-input">
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-[#354c68]" for="waWali2">No WhatsApp Wali 2</label>
              <input id="waWali2" v-model="form.waWali2" type="tel" placeholder="Opsional" class="ui-input font-mono tabular-nums">
            </div>
          </div>
        </section>
      </div>

      <!-- Right: Matrix Infak (Edit Mode) & Submit Action -->
      <div class="lg:col-span-5 flex flex-col gap-6">
        <section v-if="isEdit" class="ui-panel-padded space-y-4">
          <div class="flex items-center justify-between gap-2 pb-3 border-b border-black/[0.06]">
            <div>
              <h2 class="text-base font-bold text-[#172b4d]">Status Infak {{ academicYearLabel }}</h2>
              <p class="text-xs text-[#52657d] mt-0.5">
                {{ isKeluar ? 'Mode baca saja: riwayat pembayaran tetap tersimpan.' : 'Ketuk bulan untuk ubah status; Opsi untuk pindah bulan.' }}
              </p>
            </div>
            <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-[8px] bg-[#e8f2ff] text-[#1264c7] tabular-nums">
              {{ paidMonthsCount }}/12
            </span>
          </div>

          <div
            v-if="isKeluar"
            class="p-3.5 rounded-[11px] bg-[#FDF2F4] shadow-[inset_0_0_0_1px_oklch(0.52_0.18_15_/_0.22)] text-xs text-[#9F1239] space-y-2"
          >
            <div class="flex items-start gap-2">
              <UiIcon name="person_off" :size="16" :weight="600" class="shrink-0 mt-0.5" />
              <div>
                <p class="font-bold">Siswa Berstatus Keluar ({{ keluarDateFormatted }})</p>
                <p class="mt-0.5 leading-relaxed">
                  Riwayat pembayaran, laporan bulanan, dan grafik tetap terjaga, namun input infak baru dinonaktifkan.
                </p>
                <p v-if="lastInfakSummary" class="mt-1 font-semibold">
                  Infak Terakhir: {{ lastInfakSummary }}
                </p>
              </div>
            </div>
            <button
              type="button"
              :disabled="isReactivating"
              @click="reactivateSiswa"
              class="ui-btn-secondary !min-h-[34px] !py-1 text-xs w-full"
            >
              <UiIcon name="refresh" :size="14" :weight="600" />
              <span>{{ isReactivating ? 'Mengaktifkan...' : 'Aktifkan Kembali Siswa' }}</span>
            </button>
          </div>

          <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5">
            <div
              v-for="cell in monthCells"
              :key="cell.short"
              class="flex flex-col gap-1"
            >
              <button
                type="button"
                :disabled="isKeluar"
                @click="toggleMonthDraft(cell)"
                :class="[
                  'min-h-[54px] px-2.5 py-2 rounded-[10px] border border-transparent text-center flex flex-col items-center justify-center transition-[background-color,color,box-shadow,transform] duration-150 ease-out',
                  isKeluar ? 'cursor-not-allowed opacity-85' : 'active:scale-[0.96]',
                  cell.draftPaid === true
                    ? 'bg-[#1264c7] text-[#ffffff] shadow-[var(--shadow-btn-primary)]'
                    : cell.draftPaid === false
                      ? 'bg-[#FDF2F4] text-[#9F1239] shadow-[var(--shadow-btn-danger)]'
                      : cell.paid
                        ? 'bg-[#e8f2ff] text-[#144b88] shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.22)]'
                        : 'bg-[#f3f8fe] text-[#52657d] shadow-[var(--shadow-border)] hover:bg-[#ffffff] hover:shadow-[var(--shadow-border-hover)]'
                ]"
              >
                <span class="text-xs font-bold">{{ cell.short }}</span>
                <span class="text-xs mt-0.5">
                  {{ cell.draftPaid === true ? '+Lunas' : cell.draftPaid === false ? '-Hapus' : cell.paid ? 'Lunas' : (isKeluar ? '-' : 'Belum') }}
                </span>
              </button>
              <button
                v-if="cell.paid && !isKeluar"
                type="button"
                @click="openMonthModal(cell)"
                class="text-xs text-[#1264c7] hover:underline py-0.5 font-medium"
              >
                Opsi
              </button>
            </div>
          </div>

          <div v-if="hasDraftChanges && !isKeluar" class="p-3.5 rounded-[11px] bg-[#fff3df] shadow-[inset_0_0_0_1px_oklch(0.62_0.12_75_/_0.28)] text-xs text-[#9a3d0a]">
            <p class="font-bold">Perubahan belum disimpan:</p>
            <p class="font-mono mt-1">{{ draftChangesSummary.join(', ') }}</p>
          </div>
        </section>

        <section class="ui-panel-padded flex flex-col gap-3">
          <button type="submit" :disabled="isSubmitting" class="ui-btn-primary ui-btn-icon-l w-full">
            <UiIcon v-if="isSubmitting" name="progress_activity" :size="17" :weight="600" class="animate-spin" />
            <UiIcon v-else name="save" :size="17" :weight="600" />
            <span>{{ isSubmitting ? 'Menyimpan...' : (isEdit ? 'Simpan perubahan' : 'Simpan siswa') }}</span>
          </button>
          <router-link to="/admin/siswa" class="ui-btn-secondary w-full">Batal</router-link>
        </section>
      </div>
    </form>

    <!-- Month Option Modal -->
    <div v-if="showMonthModal && activeModalMonth" class="fixed inset-0 z-50 flex items-center justify-center bg-[#172b4d]/45 backdrop-blur-[2px] p-4 overscroll-contain" @click="closeMonthModal">
      <div class="ui-panel-elevated w-full max-w-sm p-6 space-y-4 overscroll-contain" @click.stop>
        <div class="flex items-center justify-between">
          <h3 class="text-base font-bold text-[#172b4d]">Opsi Bulan {{ monthFullName(activeModalMonth.short) }}</h3>
          <button
            type="button"
            @click="closeMonthModal"
            aria-label="Tutup modal"
            class="min-h-[38px] min-w-[38px] rounded-[9px] text-[#52657d] hover:text-[#172b4d] hover:bg-[#e6eff9] flex items-center justify-center transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]"
          >
            <UiIcon name="close" :size="18" :weight="600" />
          </button>
        </div>

        <div class="flex flex-col gap-2.5">
          <button type="button" @click="modalActionToggle" class="ui-btn-danger ui-btn-icon-l w-full">
            <UiIcon name="delete" :size="16" :weight="600" />
            <span>Tandai Hapus Pembayaran</span>
          </button>
          <button type="button" @click="startMoveMonth" class="ui-btn-secondary ui-btn-icon-l w-full">
            <UiIcon name="swap_horiz" :size="16" :weight="600" />
            <span>Pindahkan ke Bulan Lain</span>
          </button>
        </div>

        <div v-if="isMovingMonth" class="p-4 rounded-[12px] bg-[#f3f8fe] shadow-[inset_0_1px_2px_0_oklch(0.22_0.015_65_/_0.05),0_0_0_1px_oklch(0.22_0.015_65_/_0.07)] space-y-3">
          <label class="block text-xs font-semibold text-[#172b4d]" for="moveMonthSelect">Pilih bulan tujuan:</label>
          <div class="relative">
            <select id="moveMonthSelect" v-model="targetMoveMonth" class="ui-select">
              <option value="" disabled>Pilih bulan tujuan</option>
              <option v-for="m in monthsList.filter(m => !paidByMonth[m] && m !== activeModalMonth?.short)" :key="m" :value="m">
                {{ m }}
              </option>
            </select>
            <UiIcon name="expand_more" :size="17" :weight="600" class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-[#52657d]" />
          </div>
          <div class="flex justify-end gap-2">
            <button type="button" @click="isMovingMonth = false" class="ui-btn-secondary">Batal</button>
            <button type="button" @click="confirmMoveMonth" class="ui-btn-primary">Pindahkan</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { notify } from '@/utils/toast';
import { formatTanggalIndo, formatLastInfak } from '@/utils/studentStatus';

const router = useRouter();
const route = useRoute();

const isSubmitting = ref(false);
const isSuccess = ref(false);
const kelasi = ref([]);
const studentStatus = ref('aktif');
const tanggalKeluar = ref(null);
const lastInfak = ref(null);
const isReactivating = ref(false);

const isKeluar = computed(() => studentStatus.value === 'keluar');
const keluarDateFormatted = computed(() => formatTanggalIndo(tanggalKeluar.value));
const lastInfakSummary = computed(() => (lastInfak.value ? formatLastInfak(lastInfak.value) : ''));

const studentId = computed(() => route.params.id);
const isEdit = computed(() => !!studentId.value);

onMounted(async () => {
  try {
    const res = await axios.get('/kelas');
    kelasi.value = res.data;

    if (isEdit.value) {
      const studentRes = await axios.get(`/siswa/${studentId.value}`);
      const s = studentRes.data;
      form.namaLengkap = s.nama_lengkap;
      form.kelas_id = s.kelas_id;
      form.alamat = s.alamat || '';
      form.blok = s.blok || '';
      form.namaWali1 = s.nama_wali_1 || '';
      form.waWali1 = s.wa_wali_1 || '';
      form.namaWali2 = s.nama_wali_2 || '';
      form.waWali2 = s.wa_wali_2 || '';
      studentStatus.value = s.status || 'aktif';
      tanggalKeluar.value = s.tanggal_keluar || null;
      lastInfak.value = s.last_infak || null;
      await refreshInfak();
    }
  } catch (err) {
    console.error('Gagal mengambil data', err);
    notify('Gagal memuat data siswa. Silakan coba lagi.', 'error');
  }
});

const monthsList = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
const academicYearLabel = (() => {
  const now = new Date();
  const start = now.getMonth() >= 6 ? now.getFullYear() : now.getFullYear() - 1;
  return `${start}/${start + 1}`;
})();

const monthNameMap = {
  Jul: 'July', Agu: 'August', Sep: 'September', Okt: 'October',
  Nov: 'November', Des: 'December', Jan: 'January', Feb: 'February',
  Mar: 'March', Apr: 'April', Mei: 'May', Jun: 'June'
};

const engToShortMap = Object.fromEntries(
  Object.entries(monthNameMap).map(([short, full]) => [full, short])
);

const paidByMonth = ref({});
const draftMonths = ref({});

const monthCells = computed(() =>
  monthsList.map((short) => {
    const record = paidByMonth.value[short] || null;
    const draft = draftMonths.value[short];
    return {
      short,
      paid: !!record,
      record,
      draftPaid: draft === undefined || draft === null ? null : draft
    };
  })
);

const hasDraftChanges = computed(() =>
  Object.values(draftMonths.value).some(v => v !== null && v !== undefined)
);

const draftChangesSummary = computed(() => {
  const summary = [];
  Object.entries(draftMonths.value).forEach(([short, val]) => {
    if (val === true) summary.push(`+ ${short}`);
    if (val === false) summary.push(`- ${short}`);
  });
  return summary;
});

const paidMonthsCount = computed(() =>
  monthCells.value.filter(c => c.paid && c.draftPaid !== false).length
);

const monthFullName = (short) => monthNameMap[short] || short;

const refreshInfak = async () => {
  try {
    const res = await axios.get(`/siswa/${studentId.value}/infak`);
    const map = {};
    (res.data || []).forEach((i) => {
      const short = engToShortMap[i.bulan] || i.bulan;
      if (!map[short]) map[short] = i;
    });
    paidByMonth.value = map;
    draftMonths.value = {};
  } catch (error) {
    console.error('Gagal memuat riwayat infak', error);
  }
};

const toggleMonthDraft = (cell) => {
  if (isKeluar.value) return;
  if (cell.draftPaid !== null) {
    draftMonths.value[cell.short] = null;
  } else if (cell.paid) {
    draftMonths.value[cell.short] = false;
  } else {
    draftMonths.value[cell.short] = true;
  }
};

const reactivateSiswa = async () => {
  if (!isEdit.value || isReactivating.value) return;
  isReactivating.value = true;
  try {
    await axios.post(`/siswa/${studentId.value}/aktifkan`);
    studentStatus.value = 'aktif';
    tanggalKeluar.value = null;
    notify('Siswa berhasil diaktifkan kembali.', 'success');
  } catch (error) {
    console.error('Gagal mengaktifkan siswa', error);
    notify(error.response?.data?.message || 'Gagal mengaktifkan kembali siswa.', 'error');
  } finally {
    isReactivating.value = false;
  }
};

const showMonthModal = ref(false);
const activeModalMonth = ref(null);
const isMovingMonth = ref(false);
const targetMoveMonth = ref('');

const openMonthModal = (cell) => {
  activeModalMonth.value = cell;
  isMovingMonth.value = false;
  targetMoveMonth.value = '';
  showMonthModal.value = true;
};

const closeMonthModal = () => {
  showMonthModal.value = false;
  activeModalMonth.value = null;
  isMovingMonth.value = false;
  targetMoveMonth.value = '';
};

const modalActionToggle = () => {
  if (!activeModalMonth.value) return;
  const short = activeModalMonth.value.short;
  draftMonths.value[short] = activeModalMonth.value.paid ? false : true;
  closeMonthModal();
};

const startMoveMonth = () => {
  isMovingMonth.value = true;
};

const confirmMoveMonth = () => {
  if (!activeModalMonth.value || !targetMoveMonth.value) return;
  draftMonths.value[activeModalMonth.value.short] = false;
  draftMonths.value[targetMoveMonth.value] = true;
  closeMonthModal();
};

const form = reactive({
  namaLengkap: '',
  alamat: '',
  blok: '',
  kelas_id: '',
  namaWali1: '',
  waWali1: '',
  namaWali2: '',
  waWali2: ''
});

const saveSiswa = async () => {
  isSubmitting.value = true;
  try {
    const payload = {
      nama_lengkap: form.namaLengkap,
      kelas_id: form.kelas_id,
      alamat: form.alamat,
      blok: form.blok,
      nama_wali_1: form.namaWali1,
      wa_wali_1: form.waWali1,
      nama_wali_2: form.namaWali2,
      wa_wali_2: form.waWali2
    };

    if (isEdit.value) {
      await axios.put(`/siswa/${studentId.value}`, payload);
    } else {
      await axios.post('/siswa', payload);
    }

    if (isEdit.value && hasDraftChanges.value) {
      await applyDraftInfakChanges();
    }

    isSuccess.value = true;
    setTimeout(() => {
      router.push('/admin/siswa');
    }, 1200);
  } catch (error) {
    console.error('Failed to save siswa:', error);
    notify(`Gagal menyimpan data siswa: ${extractError(error)}`, 'error');
  } finally {
    isSubmitting.value = false;
  }
};

const applyDraftInfakChanges = async () => {
  const changes = Object.entries(draftMonths.value).filter(([, v]) => v !== null && v !== undefined);
  if (changes.length === 0) return;

  const toAdd = changes.filter(([, v]) => v === true).map(([short]) => short);
  const toDelete = changes.filter(([, v]) => v === false).map(([short]) => short);

  const currentYear = new Date().getFullYear();
  const akademikStart = new Date().getMonth() >= 6 ? currentYear : currentYear - 1;

  if (toAdd.length > 0 || toDelete.length > 0) {
    let nominal = 30000;
    const latestRecord = Object.values(paidByMonth.value)[0];
    if (latestRecord) nominal = Number(latestRecord.jumlah) || 30000;
    const today = new Date().toISOString().split('T')[0];

    const currentPaidMonths = Object.keys(paidByMonth.value);
    const finalMonths = currentPaidMonths
      .filter(m => draftMonths.value[m] !== false)
      .concat(toAdd);

    const uniqueFinalMonths = [...new Set(finalMonths)];

    await axios.post('/infak/sync', {
      siswa_id: studentId.value,
      kelas_id: form.kelas_id,
      tahun: akademikStart,
      months: uniqueFinalMonths,
      nominal,
      tanggal_bayar: today,
      update_existing: true
    });
  }

  draftMonths.value = {};
  await refreshInfak();
};

const extractError = (error) => {
  const data = error.response?.data;
  if (!data) return 'Koneksi gagal atau sesi berakhir.';
  if (data.message && !data.errors) return data.message;
  if (data.errors) return Object.values(data.errors).flat().join(', ');
  return 'Terjadi kesalahan.';
};
</script>
