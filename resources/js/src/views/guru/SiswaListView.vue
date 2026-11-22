<template>
  <div class="ui-page flex flex-col gap-6">
    <!-- Page Header & Filter -->
    <div class="ui-stagger ui-stagger-1 flex flex-col md:flex-row md:items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-[26px] font-bold text-[#1C1917] tracking-tight">Kelas &amp; Siswa Binaan</h1>
      </div>

      <div class="w-full md:w-64" v-if="classes.length > 0">
        <label class="block text-xs font-semibold text-[#44403C] mb-1.5" for="class-select">Filter Kelas</label>
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
          <UiIcon name="expand_more" :size="17" :weight="600" class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-[#78716C]" />
        </div>
      </div>
    </div>

    <!-- Data Table Container -->
    <div class="ui-stagger ui-stagger-2 ui-panel overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap min-w-max">
          <thead>
            <tr class="bg-[#F7F4ED] border-b border-black/[0.07]">
              <th class="px-3 py-3.5 text-xs font-bold text-[#78716C] border-r border-black/[0.07] text-center w-12">#</th>
              <th class="sticky left-0 z-20 bg-[#F7F4ED] px-4 py-3.5 text-xs font-bold text-[#1C1917] min-w-[180px] border-r border-black/[0.07]">Nama Siswa</th>
              <th class="px-3 py-3.5 text-xs font-bold text-[#78716C] text-center w-14" v-for="month in months" :key="month">{{ month }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-black/[0.05]">
            <tr
              v-for="(student, index) in filteredStudents"
              :key="student.id"
              class="hover:bg-[#F7F4ED] transition-colors duration-100 ease-out group"
            >
              <td class="px-3 py-3.5 text-sm font-mono tabular-nums text-[#78716C] border-r border-black/[0.05] text-center">{{ index + 1 }}</td>
              <td class="sticky left-0 z-10 bg-[#FDFCF9] group-hover:bg-[#F7F4ED] px-4 py-3.5 text-sm font-semibold text-[#1C1917] border-r border-black/[0.05] transition-colors duration-100 ease-out">
                <div class="flex items-center justify-between gap-2.5">
                  <span class="truncate max-w-[160px] sm:max-w-[220px]">{{ student.name }}</span>
                  <button
                    class="min-h-[34px] min-w-[34px] text-[#A8551E] hover:bg-[#F9EFE7] rounded-[8px] inline-flex items-center justify-center shrink-0 transition-[background-color,transform] duration-150 ease-out enabled:active:scale-[0.96]"
                    aria-label="Info kontak wali siswa"
                    @click.stop="openModal(student)"
                  >
                    <UiIcon name="info" :size="17" :weight="600" />
                  </button>
                </div>
              </td>
              <td class="px-2.5 py-3.5 text-center" v-for="(status, mIndex) in student.payments" :key="mIndex">
                <span class="inline-flex items-center justify-center">
                  <UiIcon v-if="status" name="check_circle" :size="18" :weight="600" filled class="text-[#A8551E]" />
                  <UiIcon v-else name="cancel" :size="18" :weight="400" class="text-[#A8A29E]" />
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
      class="fixed inset-0 z-[100] flex items-center justify-center bg-[#1C1917]/45 backdrop-blur-[2px] p-4 overscroll-contain"
      @click="closeModal"
    >
      <div class="ui-panel-elevated w-full max-w-md overflow-hidden overscroll-contain" @click.stop>
        <div class="px-6 py-4 border-b border-black/[0.07] flex justify-between items-center">
          <h3 class="text-base font-bold text-[#1C1917]">Info Siswa: {{ modalStudent.name }}</h3>
          <button
            class="min-h-[38px] min-w-[38px] text-[#78716C] hover:text-[#1C1917] hover:bg-[#ECE8DF] rounded-[9px] flex items-center justify-center transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]"
            aria-label="Tutup modal"
            @click="closeModal"
          >
            <UiIcon name="close" :size="18" :weight="600" />
          </button>
        </div>
        <div class="p-6 space-y-4 text-sm">
          <div class="flex justify-between gap-3">
            <span class="text-[#78716C]">Blok / Alamat</span>
            <span class="font-semibold text-[#1C1917] text-right">{{ modalStudent.blok || '-' }}</span>
          </div>
          <div class="flex justify-between gap-3">
            <span class="text-[#78716C]">Nama Wali</span>
            <span class="font-semibold text-[#1C1917] text-right">{{ modalStudent.waliName || '-' }}</span>
          </div>
          <div class="flex justify-between gap-3">
            <span class="text-[#78716C]">No WhatsApp</span>
            <a
              v-if="modalStudent.phone"
              class="text-[#A8551E] font-semibold inline-flex items-center gap-1.5 hover:underline"
              :href="'https://wa.me/' + modalStudent.phone.replace(/\D/g, '')"
              target="_blank"
            >
              <span class="font-mono tabular-nums">{{ modalStudent.phone }}</span>
              <UiIcon name="open_in_new" :size="14" :weight="600" />
            </a>
            <span v-else class="text-[#78716C]">-</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

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
