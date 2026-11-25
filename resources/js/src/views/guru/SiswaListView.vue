<template>
  <div class="ui-page flex flex-col gap-6">
    <!-- Page Header & Filter -->
    <div class="ui-stagger ui-stagger-1 flex flex-col md:flex-row md:items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-[26px] font-bold text-[#172b4d] tracking-tight">Kelas &amp; Siswa Binaan</h1>
      </div>

      <div class="w-full md:w-64" v-if="classes.length > 0">
        <label class="block text-xs font-semibold text-[#354c68] mb-1.5" for="class-select">Filter Kelas</label>
        <div class="relative">
          <select
            v-model="selectedClass"
            @change="filterStudents"
            id="class-select"
            class="ui-select"
          >
            <option value="">Semua Kelas</option>
            <option v-for="c in classes" :key="c.id" :value="c.nama_kelas">{{ c.nama_kelas }}</option>
          </select>
          <UiIcon name="expand_more" :size="17" :weight="600" class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-[#52657d]" />
        </div>
      </div>
    </div>

    <!-- Data Table Container -->
    <div class="ui-stagger ui-stagger-2 ui-panel overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap min-w-max">
          <thead>
            <tr class="bg-[#f3f8fe] border-b border-black/[0.07]">
              <th class="px-3 py-3.5 text-xs font-bold text-[#52657d] border-r border-black/[0.07] text-center w-12">#</th>
              <th class="sticky left-0 z-20 bg-[#f3f8fe] px-4 py-3.5 text-xs font-bold text-[#172b4d] min-w-[180px] border-r border-black/[0.07]">Nama Siswa</th>
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
                <div class="flex items-center justify-between gap-2.5">
                  <div class="min-w-0">
                    <p
                      :class="[
                        'truncate max-w-[160px] sm:max-w-[220px]',
                        student.status === 'keluar' ? 'line-through text-[#52657d]' : ''
                      ]"
                    >
                      {{ student.name }}
                    </p>
                    <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                      <span class="text-[11px] font-medium text-[#52657d]">{{ student.kelas }}</span>
                      <span
                        v-if="student.status === 'keluar'"
                        class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#e6eff9] text-[#52657d]"
                      >
                        Keluar{{ student.keluar_label ? ' · ' + student.keluar_label : '' }}
                      </span>
                    </div>
                  </div>
                  <button
                    class="min-h-[34px] min-w-[34px] text-[#1264c7] hover:bg-[#e8f2ff] rounded-[8px] inline-flex items-center justify-center shrink-0 transition-[background-color,transform] duration-150 ease-out enabled:active:scale-[0.96]"
                    aria-label="Info kontak wali siswa"
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

    <!-- Info Modal -->
    <div
      v-if="modalStudent"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-[#172b4d]/45 backdrop-blur-[2px] p-4 overscroll-contain"
      @click="closeModal"
    >
      <div class="ui-panel-elevated w-full max-w-md overflow-hidden overscroll-contain" @click.stop>
        <div class="px-6 py-4 border-b border-black/[0.07] flex justify-between items-center">
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
            <p class="text-xs text-[#52657d]">{{ modalStudent.kelas }}</p>
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
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { formatTanggalIndo, formatLastInfak, isMonthAfterExit } from '@/utils/studentStatus';

const currentAcadStart = (() => {
  const now = new Date();
  return now.getMonth() >= 6 ? now.getFullYear() : now.getFullYear() - 1;
})();

const months = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];

const classes = ref([]);
const students = ref([]);
const filteredStudents = ref([]);
const selectedClass = ref('');

const fetchClasses = async () => {
  try {
    const res = await axios.get('/kelas');
    classes.value = res.data;
  } catch (e) {
    console.error('Gagal mengambil kelas', e);
  }
};

const fetchStudents = async () => {
  try {
    const res = await axios.get('/siswa');
    students.value = res.data;
    filteredStudents.value = res.data;
  } catch (e) {
    console.error('Gagal mengambil siswa', e);
  }
};

onMounted(() => {
  fetchClasses();
  fetchStudents();
});

const filterStudents = () => {
  if (selectedClass.value === '') {
    filteredStudents.value = students.value;
  } else {
    filteredStudents.value = students.value.filter(s => s.kelas === selectedClass.value);
  }
};

const modalStudent = ref(null);

const openModal = (student) => {
  modalStudent.value = student;
};

const closeModal = () => {
  modalStudent.value = null;
};
</script>
