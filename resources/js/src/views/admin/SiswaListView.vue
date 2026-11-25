<template>
  <div class="ui-page flex flex-col gap-6">
    <!-- Page Header & Actions -->
    <div class="ui-stagger ui-stagger-1 flex flex-col lg:flex-row lg:items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-[26px] font-bold text-[#172b4d] tracking-tight">Siswa &amp; wali</h1>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <router-link to="/admin/infak/create" class="ui-btn-primary ui-btn-icon-l">
          <UiIcon name="add_circle" :size="17" :weight="600" />
          <span>Catat Infak</span>
        </router-link>
        <router-link to="/admin/siswa/create" class="ui-btn-secondary ui-btn-icon-l">
          <UiIcon name="person_add" :size="17" :weight="600" />
          <span>Tambah Siswa</span>
        </router-link>
        <button @click="exportSiswa" class="ui-btn-secondary ui-btn-icon-l">
          <UiIcon name="download" :size="16" :weight="600" />
          <span>Ekspor CSV</span>
        </button>
        <button @click="cetakFormSetoran" :disabled="!currentClassObj" class="ui-btn-secondary ui-btn-icon-l">
          <UiIcon name="print" :size="16" :weight="600" />
          <span>Form Setoran</span>
        </button>

        <template v-if="confirmBlast">
          <button
            @click="executeBlastWA"
            :disabled="isBlasting"
            class="ui-btn-danger"
          >
            {{ isBlasting ? 'Mengirim...' : `Kirim WA (Jeda ${blastDelay} dtk)` }}
          </button>
          <button @click="confirmBlast = false" class="ui-btn-secondary">Batal</button>
        </template>
        <button v-else @click="prepareBlastWA" :disabled="isBlasting" class="ui-btn-navy ui-btn-icon-l">
          <UiIcon name="campaign" :size="16" :weight="600" />
          <span>Blast WA Tunggakan</span>
        </button>
      </div>
    </div>

    <!-- Progress Blast WA -->
    <div
      v-if="isBlasting && blastProgress"
      class="ui-panel-padded bg-[#e8f2ff] shadow-[0_0_0_1px_oklch(0.55_0.13_42_/_0.22)]"
    >
      <div class="flex items-center justify-between text-sm font-semibold text-[#144b88] mb-2">
        <span>Mengirim antrean WhatsApp bertahap (jangan tutup tab)...</span>
        <span class="font-mono tabular-nums">{{ blastProgress.sent }} / {{ blastProgress.total }}</span>
      </div>
      <div class="w-full h-2.5 bg-[#d5e8ff] rounded-full overflow-hidden">
        <div
          class="h-full bg-[#1264c7] rounded-full transition-[width] duration-300 ease-out-expo"
          :style="{ width: blastProgress.total ? Math.round((blastProgress.sent / blastProgress.total) * 100) + '%' : '0%' }"
        ></div>
      </div>
    </div>

    <!-- Class Switcher Strip -->
    <div class="ui-stagger ui-stagger-2 ui-panel p-4 flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <button
          @click="navigateClass(-1)"
          :disabled="!hasPrevClass"
          aria-label="Kelas sebelumnya"
          class="min-h-[44px] min-w-[44px] rounded-[11px] border border-transparent bg-[#f3f8fe] shadow-[var(--shadow-border)] hover:bg-[#ffffff] hover:shadow-[var(--shadow-border-hover)] text-[#172b4d] flex items-center justify-center transition-[background-color,box-shadow,transform] duration-150 ease-out enabled:active:scale-[0.96] disabled:opacity-40 disabled:cursor-not-allowed"
        >
          <UiIcon name="chevron_left" :size="20" :weight="600" directional />
        </button>

        <div class="text-center min-w-0">
          <span class="block text-xs text-[#52657d]">Kelas Aktif (gunakan panah ← →)</span>
          <span class="text-lg font-bold text-[#172b4d]">{{ currentClassName }}</span>
          <span v-if="filteredStudents.length > 0" class="block text-[11px] text-[#52657d] font-medium">
            {{ activeCount }} aktif<template v-if="keluarCount > 0"> · {{ keluarCount }} keluar</template>
          </span>
        </div>

        <button
          @click="navigateClass(1)"
          :disabled="!hasNextClass"
          aria-label="Kelas berikutnya"
          class="min-h-[44px] min-w-[44px] rounded-[11px] border border-transparent bg-[#f3f8fe] shadow-[var(--shadow-border)] hover:bg-[#ffffff] hover:shadow-[var(--shadow-border-hover)] text-[#172b4d] flex items-center justify-center transition-[background-color,box-shadow,transform] duration-150 ease-out enabled:active:scale-[0.96] disabled:opacity-40 disabled:cursor-not-allowed"
        >
          <UiIcon name="chevron_right" :size="20" :weight="600" directional />
        </button>
      </div>

      <div class="flex flex-wrap gap-1.5 p-1 rounded-[13px] bg-[#e6eff9]">
        <button
          v-for="(c, idx) in classes"
          :key="c.id"
          @click="currentClassIndex = idx"
          :class="[
            'min-h-[36px] px-3 rounded-[9px] border border-transparent text-xs font-semibold transition-[background-color,color,box-shadow,transform] duration-150 ease-out active:scale-[0.96]',
            currentClassIndex === idx
              ? 'bg-[#17385f] text-[#ffffff] shadow-[var(--shadow-btn-navy)]'
              : 'text-[#52657d] hover:text-[#172b4d] hover:bg-[#ffffff]/80'
          ]"
        >
          {{ c.nama_kelas }}
        </button>
      </div>
    </div>

    <!-- Data Table Container -->
    <div class="ui-stagger ui-stagger-3 ui-panel overflow-hidden">
      <div v-if="isLoading" class="p-8 space-y-3">
        <div v-for="n in 5" :key="n" class="h-12 bg-[#f3f8fe] rounded-[10px] animate-pulse"></div>
      </div>

      <div v-else-if="filteredStudents.length === 0" class="p-14 text-center">
        <div class="w-12 h-12 mx-auto rounded-[12px] bg-[#e6eff9] text-[#52657d] flex items-center justify-center mb-3">
          <UiIcon name="group_off" :size="24" :weight="500" />
        </div>
        <p class="text-base font-bold text-[#172b4d]">Belum ada siswa di {{ currentClassName }}</p>
        <p class="text-sm text-[#52657d] mt-1">Tambah siswa atau pilih kelas lain.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap min-w-max">
          <thead>
            <tr class="bg-[#f3f8fe] border-b border-black/[0.06]">
              <th class="px-3 py-3.5 text-xs font-bold text-[#52657d] border-r border-black/[0.06] text-center w-12">#</th>
              <th class="sticky left-0 z-20 bg-[#f3f8fe] px-4 py-3.5 text-xs font-bold text-[#172b4d] min-w-[200px] border-r border-black/[0.06]">Nama Siswa &amp; Akun Wali</th>
              <th class="px-3 py-3.5 text-xs font-bold text-[#52657d] text-center w-14" v-for="month in months" :key="month">{{ month }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-black/[0.05]">
            <tr
              v-for="(student, index) in filteredStudents"
              :key="student.id"
              :class="[
                'hover:bg-[#f3f8fe] transition-colors duration-100 ease-out group',
                student.status === 'keluar' ? 'bg-[#f3f8fe]/65' : ''
              ]"
            >
              <td class="px-3 py-3.5 text-sm font-mono tabular-nums text-[#52657d] border-r border-black/[0.05] text-center">{{ index + 1 }}</td>
              <td
                :class="[
                  'sticky left-0 z-10 group-hover:bg-[#f3f8fe] px-4 py-3.5 text-sm font-semibold border-r border-black/[0.05] transition-colors duration-100 ease-out',
                  student.status === 'keluar' ? 'bg-[#f3f8fe] text-[#52657d]' : 'bg-[#ffffff] text-[#172b4d]'
                ]"
              >
                <div class="flex items-center justify-between gap-3">
                  <div class="min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                      <p
                        :class="[
                          'truncate max-w-[180px] sm:max-w-[240px]',
                          student.status === 'keluar' ? 'line-through text-[#52657d]' : ''
                        ]"
                      >
                        {{ student.name }}
                      </p>
                      <span
                        v-if="student.status === 'keluar'"
                        class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#e6eff9] text-[#52657d]"
                      >
                        Keluar{{ student.keluar_label ? ' · ' + student.keluar_label : '' }}
                      </span>
                    </div>
                    <p class="text-xs font-mono text-[#52657d] tabular-nums">
                      NIS {{ student.nis || '-' }} · {{ student.wali_account ? 'Akun Wali Aktif' : 'Belum Ada Akun Wali' }}
                    </p>
                  </div>
                  <button
                    class="min-h-[36px] min-w-[36px] rounded-[9px] border border-transparent bg-[#f3f8fe] shadow-[var(--shadow-border)] hover:bg-[#ffffff] hover:shadow-[var(--shadow-border-hover)] text-[#1264c7] inline-flex items-center justify-center shrink-0 transition-[background-color,box-shadow,transform] duration-150 ease-out active:scale-[0.96]"
                    aria-label="Detail &amp; Akun Wali Siswa"
                    @click.stop="openModal(student)"
                  >
                    <UiIcon name="info" :size="17" :weight="600" />
                  </button>
                </div>
              </td>
              <td class="px-2.5 py-3.5 text-center" v-for="(status, mIndex) in student.payments" :key="mIndex">
                <span class="inline-flex items-center justify-center">
                  <UiIcon v-if="status" name="check_circle" :size="18" :weight="600" filled class="text-[#1264c7]" />
                  <span v-else-if="isMonthAfterExit(student, mIndex, currentAcadStart)" class="text-xs font-mono text-[#61758f]" title="Siswa sudah keluar">—</span>
                  <UiIcon v-else name="cancel" :size="18" :weight="400" class="text-[#61758f]" />
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Info & Provisioning Modal -->
    <div
      v-if="modalStudent"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-[#172b4d]/45 backdrop-blur-[2px] p-4 overscroll-contain"
      @click="closeModal"
    >
      <div class="ui-panel-elevated w-full max-w-md overflow-hidden overscroll-contain max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="px-6 py-4 border-b border-black/[0.06] flex justify-between items-center">
          <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <h3 :class="['text-base font-bold', modalStudent.status === 'keluar' ? 'line-through text-[#52657d]' : 'text-[#172b4d]']">
                {{ modalStudent.name }}
              </h3>
              <span
                :class="[
                  'text-[10px] font-bold px-2 py-0.5 rounded-full',
                  modalStudent.status === 'keluar' ? 'bg-[#FDF2F4] text-[#9F1239]' : 'bg-[#e8f2ff] text-[#1264c7]'
                ]"
              >
                {{ modalStudent.status === 'keluar' ? 'Keluar' : 'Aktif' }}
              </span>
            </div>
            <p class="text-xs text-[#52657d] font-mono tabular-nums">NIS {{ modalStudent.nis || '-' }}</p>
          </div>
          <button
            class="min-h-[38px] min-w-[38px] text-[#52657d] hover:text-[#172b4d] hover:bg-[#e6eff9] rounded-[9px] flex items-center justify-center transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]"
            aria-label="Tutup modal"
            @click="closeModal"
          >
            <UiIcon name="close" :size="18" :weight="600" />
          </button>
        </div>

        <div class="p-6 space-y-4 text-sm">
          <div v-if="modalStudent.status === 'keluar'" class="flex justify-between gap-3">
            <span class="text-[#52657d]">Keluar Pada</span>
            <span class="font-semibold text-[#9F1239] text-right">
              {{ modalStudent.tanggal_keluar ? formatTanggalIndo(modalStudent.tanggal_keluar) : (modalStudent.keluar_label || '-') }}
            </span>
          </div>
          <div class="flex justify-between gap-3">
            <span class="text-[#52657d]">Infak Terakhir</span>
            <span class="font-semibold text-[#172b4d] text-right">{{ formatLastInfak(modalStudent.last_infak) }}</span>
          </div>
          <div class="flex justify-between gap-3">
            <span class="text-[#52657d]">Blok / Alamat</span>
            <span class="font-semibold text-[#172b4d] text-right">{{ modalStudent.blok || '-' }}</span>
          </div>
          <div class="flex justify-between gap-3">
            <span class="text-[#52657d]">Nama Wali</span>
            <span class="font-semibold text-[#172b4d] text-right">{{ modalStudent.waliName || '-' }}</span>
          </div>
          <div class="flex justify-between gap-3">
            <span class="text-[#52657d]">No WhatsApp</span>
            <a
              v-if="modalStudent.phone"
              class="text-[#1264c7] font-semibold inline-flex items-center gap-1.5 hover:underline"
              :href="'https://wa.me/' + modalStudent.phone.replace(/\D/g, '')"
              target="_blank"
            >
              <span class="font-mono tabular-nums">{{ modalStudent.phone }}</span>
              <UiIcon name="open_in_new" :size="14" :weight="600" />
            </a>
            <span v-else class="text-[#52657d]">-</span>
          </div>

          <!-- Akun Portal Wali -->
          <div class="p-4 rounded-[14px] bg-[#f3f8fe] shadow-[inset_0_1px_2px_0_oklch(0.22_0.015_65_/_0.05),0_0_0_1px_oklch(0.22_0.015_65_/_0.07)] space-y-3">
            <div class="flex items-center justify-between gap-2">
              <span class="text-xs font-bold text-[#172b4d]">Akun Portal Orang Tua</span>
              <span
                class="text-xs font-semibold px-2.5 py-0.5 rounded-[7px]"
                :class="modalStudent.wali_account ? 'bg-[#e8f2ff] text-[#1264c7]' : 'bg-[#fff3df] text-[#aa480e]'"
              >
                {{ modalStudent.wali_account ? 'Aktif' : 'Belum Dibuat' }}
              </span>
            </div>
            <p v-if="modalStudent.wali_account" class="text-xs font-mono text-[#52657d]">
              Login: {{ modalStudent.wali_account.email }} (atau NIS {{ modalStudent.nis }})
            </p>
            <p v-if="provisionedCredential" class="text-xs font-mono bg-[#ffffff] shadow-[var(--shadow-border)] text-[#144b88] p-3 rounded-[10px] leading-relaxed">
              Siap digunakan!<br>
              Email: <strong>{{ provisionedCredential.email }}</strong> · NIS: <strong>{{ provisionedCredential.nis }}</strong> · Sandi: <strong>{{ provisionedCredential.password }}</strong>
            </p>
            <button
              type="button"
              @click="provisionWaliAccount(modalStudent)"
              :disabled="isProvisioning"
              class="ui-btn-navy ui-btn-icon-l w-full"
            >
              <UiIcon name="key" :size="16" :weight="600" />
              <span>{{ isProvisioning ? 'Memproses...' : (modalStudent.wali_account ? 'Reset Sandi Akun Wali (wali123)' : 'Buat Akun Portal Wali') }}</span>
            </button>
          </div>

          <div class="pt-3 border-t border-black/[0.06] flex flex-col gap-2.5">
            <div class="flex flex-col sm:flex-row gap-2.5">
              <button
                v-if="modalStudent.status !== 'keluar'"
                type="button"
                class="ui-btn-danger ui-btn-icon-l flex-1"
                @click="openKeluarModal(modalStudent)"
              >
                <UiIcon name="person_off" :size="16" :weight="600" />
                <span>Tandai Keluar</span>
              </button>
              <button
                v-else
                type="button"
                class="ui-btn-primary ui-btn-icon-l flex-1"
                :disabled="isUpdatingStatus"
                @click="reactivateStudent(modalStudent)"
              >
                <UiIcon name="refresh" :size="16" :weight="600" />
                <span>{{ isUpdatingStatus ? 'Mengaktifkan...' : 'Aktifkan Kembali' }}</span>
              </button>

              <router-link :to="`/admin/siswa/${modalStudent.id}/edit`" class="ui-btn-secondary ui-btn-icon-l flex-1">
                <UiIcon name="edit" :size="16" :weight="600" />
                <span>{{ modalStudent.status === 'keluar' ? 'Lihat Data & Riwayat' : 'Edit & Infak' }}</span>
              </router-link>
            </div>

            <button
              v-if="modalStudent.status !== 'keluar' && !modalStudent.has_infak"
              type="button"
              class="text-xs font-semibold text-[#9F1239] hover:underline self-center py-1"
              @click="confirmDelete(modalStudent)"
            >
              Hapus Permanen (Belum Ada Riwayat Infak)
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Tandai Keluar Confirmation Modal -->
    <div
      v-if="studentToExit"
      class="fixed inset-0 z-[110] flex items-center justify-center bg-[#172b4d]/50 backdrop-blur-[2px] p-4 overscroll-contain"
      @click="cancelKeluar"
    >
      <div class="ui-panel-elevated w-full max-w-md p-6 overscroll-contain" @click.stop>
        <div class="mx-auto w-14 h-14 bg-[#fff3df] text-[#aa480e] rounded-full flex items-center justify-center mb-4">
          <UiIcon name="person_off" :size="24" :weight="600" />
        </div>
        <h3 class="text-lg font-bold text-[#172b4d] text-center">Tandai Siswa Keluar?</h3>
        <p class="text-sm text-[#52657d] mt-2 leading-relaxed text-center">
          Nama <strong>{{ studentToExit.name }}</strong> akan dicoret di daftar kelas dan tidak bisa diinput infak baru. Seluruh riwayat pembayaran, laporan bulanan, dan grafik tetap terjaga.
        </p>

        <div class="mt-4 flex flex-col gap-1.5">
          <label class="text-xs font-semibold text-[#354c68]" for="tanggalKeluarInput">Tanggal Keluar</label>
          <input id="tanggalKeluarInput" v-model="exitDate" type="date" class="ui-input" />
        </div>

        <div class="flex gap-3 mt-6">
          <button type="button" class="ui-btn-secondary flex-1" @click="cancelKeluar">Batal</button>
          <button
            type="button"
            class="ui-btn-danger flex-1"
            :disabled="isUpdatingStatus"
            @click="executeMarkKeluar"
          >
            {{ isUpdatingStatus ? 'Menyimpan...' : 'Ya, Tandai Keluar' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal (only for students with 0 infak) -->
    <div
      v-if="studentToDelete"
      class="fixed inset-0 z-[110] flex items-center justify-center bg-[#172b4d]/50 backdrop-blur-[2px] p-4 overscroll-contain"
      @click="cancelDelete"
    >
      <div class="ui-panel-elevated w-full max-w-sm p-6 text-center overscroll-contain" @click.stop>
        <div class="mx-auto w-14 h-14 bg-[#FDF2F4] shadow-[var(--shadow-btn-danger)] rounded-full flex items-center justify-center mb-4 text-[#9F1239]">
          <UiIcon name="warning" :size="24" :weight="600" />
        </div>
        <h3 class="text-lg font-bold text-[#172b4d]">Hapus Siswa Permanen?</h3>
        <p class="text-sm text-[#52657d] mt-2 mb-6 leading-relaxed">
          Data <strong>{{ studentToDelete.name }}</strong> akan dihapus permanen karena belum memiliki riwayat pembayaran infak.
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
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';
import { drainOutbox } from '../../utils/waOutbox';
import { notify } from '@/utils/toast';
import { formatTanggalIndo, formatLastInfak, isMonthAfterExit } from '@/utils/studentStatus';

const months = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];

const kelasOrder = [
  'TK', 'Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4A', 'Kelas 4B',
  'Kelas 5', 'Kelas 6', 'Kelas 7', 'Kelas 8', 'Kelas 9', 'Kelas Ulya', 'Bambim'
];

const students = ref([]);
const classes = ref([]);
const isLoading = ref(true);
const isBlasting = ref(false);
const confirmBlast = ref(false);
const blastProgress = ref(null);
const blastDelay = ref(60);
const modalStudent = ref(null);
const studentToDelete = ref(null);
const studentToExit = ref(null);
const exitDate = ref(new Date().toISOString().split('T')[0]);
const isUpdatingStatus = ref(false);
const isProvisioning = ref(false);
const provisionedCredential = ref(null);

const currentClassIndex = ref(0);

const fetchClasses = async () => {
  try {
    const res = await axios.get('/kelas');
    classes.value = res.data;

    classes.value.sort((a, b) => {
      const idxA = kelasOrder.indexOf(a.nama_kelas);
      const idxB = kelasOrder.indexOf(b.nama_kelas);
      return (idxA === -1 ? 99 : idxA) - (idxB === -1 ? 99 : idxB);
    });

    const tkIndex = classes.value.findIndex(c => c.nama_kelas === 'TK');
    if (tkIndex !== -1) {
      currentClassIndex.value = tkIndex;
    }
  } catch (e) {
    console.error('Gagal mengambil kelas', e);
  }
};

const fetchStudents = async () => {
  isLoading.value = true;
  try {
    const params = currentClassObj.value ? { kelas_id: currentClassObj.value.id } : {};
    const response = await axios.get('/siswa', { params });
    students.value = response.data;
  } catch (error) {
    console.error('Failed to fetch students:', error);
  } finally {
    isLoading.value = false;
  }
};

watch(currentClassIndex, () => {
  fetchStudents();
});

onMounted(() => {
  fetchClasses();
  fetchStudents();
  window.addEventListener('keydown', handleKeydown);
});

const handleKeydown = (e) => {
  if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') return;
  if (e.key === 'ArrowLeft') {
    e.preventDefault();
    navigateClass(-1);
  } else if (e.key === 'ArrowRight') {
    e.preventDefault();
    navigateClass(1);
  }
};

const currentClassObj = computed(() => classes.value[currentClassIndex.value] || null);
const currentClassName = computed(() => currentClassObj.value?.nama_kelas || 'Belum ada kelas');
const hasPrevClass = computed(() => currentClassIndex.value > 0);
const hasNextClass = computed(() => currentClassIndex.value < classes.value.length - 1);

const navigateClass = (direction) => {
  const newIndex = currentClassIndex.value + direction;
  if (newIndex >= 0 && newIndex < classes.value.length) {
    currentClassIndex.value = newIndex;
  }
};

const filteredStudents = computed(() => {
  if (!currentClassObj.value) return [];
  return students.value.filter(s => s.kelas === currentClassObj.value.nama_kelas);
});

const activeCount = computed(() => filteredStudents.value.filter(s => s.status !== 'keluar').length);
const keluarCount = computed(() => filteredStudents.value.filter(s => s.status === 'keluar').length);

const currentAcadStart = (() => {
  const now = new Date();
  return now.getMonth() >= 6 ? now.getFullYear() : now.getFullYear() - 1;
})();

const exportSiswa = async () => {
  try {
    const response = await axios.get('/siswa/export', { responseType: 'blob' });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `backup_siswa_${new Date().getTime()}.csv`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    notify('File CSV siswa berhasil diunduh.', 'success');
  } catch (error) {
    console.error('Gagal export', error);
    notify('Gagal mengunduh file backup.', 'error');
  }
};

const cetakFormSetoran = () => {
  if (!currentClassObj.value) return;
  window.open(`/api/kelas/${currentClassObj.value.id}/form-setoran`, '_blank');
};

const prepareBlastWA = async () => {
  if (isBlasting.value) return;
  try {
    const st = await axios.get('/wa/status');
    blastDelay.value = st.data.delay || 60;
  } catch (e) {
    blastDelay.value = 60;
  }
  confirmBlast.value = true;
};

const executeBlastWA = async () => {
  confirmBlast.value = false;
  isBlasting.value = true;
  blastProgress.value = null;
  try {
    const res = await axios.post('/infak/blast-wa');
    const queued = res.data.queued ?? 0;
    const remaining = res.data.remaining ?? 0;

    if (queued === 0 && remaining === 0) {
      notify(res.data.message || 'Tidak ada pesan baru untuk dikirim.', 'info');
      return;
    }

    await drainOutbox((p) => { blastProgress.value = p; });
    const sent = blastProgress.value?.sent ?? 0;
    notify(`Blast WA selesai. ${sent} pesan WhatsApp berhasil dikirim.`, 'success');
  } catch (error) {
    console.error('Gagal blast WA', error);
    notify(error.response?.data?.message || 'Terjadi kesalahan saat mengirim Blast WA.', 'error');
  } finally {
    isBlasting.value = false;
    blastProgress.value = null;
  }
};

const openModal = (student) => {
  modalStudent.value = student;
  provisionedCredential.value = null;
};

const closeModal = () => {
  modalStudent.value = null;
  provisionedCredential.value = null;
};

const provisionWaliAccount = async (student) => {
  isProvisioning.value = true;
  try {
    const res = await axios.post(`/siswa/${student.id}/wali-account`, {
      password: 'wali123',
    });
    const acc = res.data.account;
    student.wali_account = { id: acc.id, name: acc.name, email: acc.email };
    provisionedCredential.value = acc;
    notify('Akun portal wali siswa siap digunakan.', 'success');
  } catch (err) {
    notify(err.response?.data?.message || 'Gagal menyiapkan akun wali siswa.', 'error');
  } finally {
    isProvisioning.value = false;
  }
};

const openKeluarModal = (student) => {
  studentToExit.value = student;
  exitDate.value = new Date().toISOString().split('T')[0];
  closeModal();
};

const cancelKeluar = () => {
  studentToExit.value = null;
};

const executeMarkKeluar = async () => {
  if (!studentToExit.value || isUpdatingStatus.value) return;
  isUpdatingStatus.value = true;
  try {
    await axios.post(`/siswa/${studentToExit.value.id}/keluar`, {
      tanggal_keluar: exitDate.value || new Date().toISOString().split('T')[0],
    });
    studentToExit.value = null;
    notify('Siswa ditandai keluar. Riwayat infak tetap tersimpan.', 'success');
    await fetchStudents();
  } catch (error) {
    console.error('Failed to mark student keluar:', error);
    notify(error.response?.data?.message || 'Gagal menandai siswa keluar.', 'error');
  } finally {
    isUpdatingStatus.value = false;
  }
};

const reactivateStudent = async (student) => {
  if (!student || isUpdatingStatus.value) return;
  isUpdatingStatus.value = true;
  try {
    await axios.post(`/siswa/${student.id}/aktifkan`);
    closeModal();
    notify('Siswa berhasil diaktifkan kembali.', 'success');
    await fetchStudents();
  } catch (error) {
    console.error('Failed to reactivate student:', error);
    notify(error.response?.data?.message || 'Gagal mengaktifkan kembali siswa.', 'error');
  } finally {
    isUpdatingStatus.value = false;
  }
};

const confirmDelete = (student) => {
  studentToDelete.value = student;
  closeModal();
};

const cancelDelete = () => {
  studentToDelete.value = null;
};

const executeDelete = async () => {
  if (!studentToDelete.value) return;
  try {
    const res = await axios.delete(`/siswa/${studentToDelete.value.id}`);
    if (res.data?.action === 'keluar') {
      notify(res.data.message || 'Siswa ditandai keluar.', 'info');
      await fetchStudents();
    } else {
      students.value = students.value.filter(s => s.id !== studentToDelete.value.id);
      notify('Data siswa berhasil dihapus permanen.', 'success');
    }
  } catch (error) {
    console.error('Failed to delete student:', error);
    notify(error.response?.data?.message || 'Gagal menghapus siswa.', 'error');
  } finally {
    studentToDelete.value = null;
  }
};

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown);
});
</script>
