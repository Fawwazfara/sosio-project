<template>
  <div class="ui-page flex flex-col gap-6">
    <div class="ui-stagger ui-stagger-1">
      <h1 class="text-2xl sm:text-[26px] font-bold text-[#0F172A] tracking-tight">Akun guru</h1>
    </div>

    <div class="ui-stagger ui-stagger-2 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <!-- Create Teacher Form -->
      <section class="lg:col-span-5 ui-panel-padded space-y-5">
        <h2 class="text-base font-bold text-[#0F172A] pb-3 border-b border-black/[0.07]">Tambah Akun Guru</h2>

        <form @submit.prevent="saveGuru" class="space-y-4">
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-[#334155]" for="guruName">Nama Lengkap</label>
            <input id="guruName" v-model="form.fullName" type="text" required placeholder="Contoh: Ustadz Ahmad" class="ui-input">
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-[#334155]" for="guruEmail">Email Login</label>
            <input id="guruEmail" v-model="form.email" type="email" required placeholder="nama@assajjad.com" class="ui-input">
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-[#334155]" for="guruPass">Kata Sandi Awal</label>
            <div class="relative">
              <input
                id="guruPass"
                v-model="form.password"
                :type="showPasswordInput ? 'text' : 'password'"
                required
                minlength="6"
                placeholder="Minimal 6 karakter"
                class="ui-input pr-12"
              >
              <button
                type="button"
                @click="showPasswordInput = !showPasswordInput"
                aria-label="Tampilkan atau sembunyikan kata sandi"
                class="absolute right-1.5 top-1/2 -translate-y-1/2 min-h-[36px] min-w-[36px] flex items-center justify-center rounded-[8px] text-[#64748B] hover:text-[#0F172A] hover:bg-[#EAEFF6] transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]"
              >
                <span class="ui-icon-swap">
                  <span :class="showPasswordInput ? 'ui-icon-swap-shown' : 'ui-icon-swap-hidden'">
                    <UiIcon name="visibility" :size="18" :weight="500" />
                  </span>
                  <span :class="showPasswordInput ? 'ui-icon-swap-hidden' : 'ui-icon-swap-shown'">
                    <UiIcon name="visibility_off" :size="18" :weight="500" />
                  </span>
                </span>
              </button>
            </div>
          </div>

          <div class="pt-1">
            <label class="block text-xs font-semibold text-[#334155] mb-2">Tugaskan Kelas Binaan</label>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="k in availableKelas"
                :key="k.id"
                type="button"
                @click="toggleKelas(k.id)"
                :class="[
                  'min-h-[38px] px-3.5 rounded-[10px] border border-transparent text-xs font-semibold transition-[background-color,color,box-shadow,transform] duration-150 ease-out active:scale-[0.96]',
                  form.kelas.includes(k.id)
                    ? 'bg-[#163A70] text-white shadow-[var(--shadow-btn-navy)]'
                    : 'bg-[#F8FAFC] text-[#0F172A] shadow-[var(--shadow-border)] hover:bg-[#FFFFFF] hover:shadow-[var(--shadow-border-hover)]'
                ]"
              >
                {{ k.nama_kelas }}
              </button>
            </div>
          </div>

          <button type="submit" :disabled="isSubmitting" class="ui-btn-primary ui-btn-icon-l w-full mt-2">
            <UiIcon v-if="isSubmitting" name="progress_activity" :size="17" :weight="600" class="animate-spin" />
            <UiIcon v-else name="person_add" :size="17" :weight="600" />
            <span>{{ isSubmitting ? 'Menyimpan...' : 'Buat Akun Guru' }}</span>
          </button>
        </form>
      </section>

      <!-- Teachers List -->
      <section class="lg:col-span-7 flex flex-col gap-3.5">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-bold text-[#0F172A]">Daftar Guru Terdaftar (<span class="tabular-nums">{{ teachers.length }}</span>)</h2>
        </div>

        <div v-if="teachers.length === 0" class="ui-panel-padded text-center py-10 text-sm text-[#64748B]">
          Belum ada akun guru terdaftar.
        </div>

        <div
          v-for="teacher in teachers"
          :key="teacher.id"
          class="ui-panel ui-panel-interactive p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
        >
          <div class="flex items-start gap-3.5 min-w-0">
            <div class="w-11 h-11 rounded-[11px] bg-[#EFF6FF] shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.2)] text-[#1E3A8A] font-bold text-sm flex items-center justify-center shrink-0">
              {{ getInitials(teacher.name) }}
            </div>
            <div class="min-w-0">
              <h3 class="text-sm font-bold text-[#0F172A] truncate">{{ teacher.name }}</h3>
              <p class="text-xs font-mono text-[#64748B] truncate">{{ teacher.email }}</p>
              <div class="flex flex-wrap gap-1.5 mt-2">
                <span
                  v-for="cls in teacher.kelas"
                  :key="cls.id || cls"
                  class="text-xs font-semibold px-2.5 py-0.5 rounded-[7px] bg-[#EAEFF6] text-[#334155]"
                >
                  {{ cls.nama_kelas || cls }}
                </span>
                <span v-if="!teacher.kelas || teacher.kelas.length === 0" class="text-xs text-[#64748B]">Belum ada kelas</span>
              </div>
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-2 shrink-0 pt-3 sm:pt-0 border-t sm:border-t-0 border-black/[0.07]">
            <button @click="viewPassword(teacher)" class="ui-btn-secondary ui-btn-icon-l">
              <UiIcon name="lock_reset" :size="16" :weight="600" />
              <span>Reset Sandi</span>
            </button>

            <template v-if="confirmDeleteId === teacher.id">
              <button @click="executeDeleteTeacher(teacher)" class="ui-btn-danger">Ya, Hapus</button>
              <button @click="confirmDeleteId = null" class="ui-btn-secondary">Batal</button>
            </template>
            <button
              v-else
              @click="confirmDeleteId = teacher.id"
              aria-label="Hapus akun guru"
              class="min-h-[44px] min-w-[44px] rounded-[10px] border border-transparent bg-[#FFF1F2] hover:bg-[#FFE4E6] shadow-[var(--shadow-btn-danger)] text-[#9F1239] flex items-center justify-center transition-[background-color,transform] duration-150 ease-out active:scale-[0.96]"
            >
              <UiIcon name="delete" :size="17" :weight="600" />
            </button>
          </div>
        </div>
      </section>
    </div>

    <!-- Reset Password Modal -->
    <div v-if="modalTeacher" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0F172A]/45 backdrop-blur-[2px] p-4 overscroll-contain" @click="closeModal">
      <div class="ui-panel-elevated w-full max-w-md p-6 space-y-5 overscroll-contain" @click.stop>
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-base font-bold text-[#0F172A]">Reset Kata Sandi</h3>
            <p class="text-xs text-[#64748B] mt-0.5">{{ modalTeacher.name }} ({{ modalTeacher.email }})</p>
          </div>
          <button
            @click="closeModal"
            aria-label="Tutup modal"
            class="min-h-[38px] min-w-[38px] rounded-[9px] text-[#64748B] hover:text-[#0F172A] hover:bg-[#EAEFF6] flex items-center justify-center transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]"
          >
            <UiIcon name="close" :size="18" :weight="600" />
          </button>
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-semibold text-[#334155]" for="newPassInput">Kata Sandi Baru (Minimal 6 karakter)</label>
          <div class="relative">
            <input
              id="newPassInput"
              v-model="newPassword"
              :type="showNewPassword ? 'text' : 'password'"
              placeholder="Masukkan kata sandi baru"
              class="ui-input pr-12"
            >
            <button
              type="button"
              @click="showNewPassword = !showNewPassword"
              aria-label="Tampilkan atau sembunyikan kata sandi baru"
              class="absolute right-1.5 top-1/2 -translate-y-1/2 min-h-[36px] min-w-[36px] flex items-center justify-center rounded-[8px] text-[#64748B] hover:text-[#0F172A] hover:bg-[#EAEFF6] transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]"
            >
              <span class="ui-icon-swap">
                <span :class="showNewPassword ? 'ui-icon-swap-shown' : 'ui-icon-swap-hidden'">
                  <UiIcon name="visibility" :size="18" :weight="500" />
                </span>
                <span :class="showNewPassword ? 'ui-icon-swap-hidden' : 'ui-icon-swap-shown'">
                  <UiIcon name="visibility_off" :size="18" :weight="500" />
                </span>
              </span>
            </button>
          </div>
        </div>

        <div class="flex gap-3">
          <button type="button" @click="closeModal" class="ui-btn-secondary flex-1">Batal</button>
          <button type="button" @click="resetPassword" :disabled="isResetting" class="ui-btn-primary flex-1">
            {{ isResetting ? 'Menyimpan...' : 'Simpan Sandi Baru' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { notify } from '@/utils/toast';

const availableKelas = ref([]);
const teachers = ref([]);

const showPasswordInput = ref(false);
const isSubmitting = ref(false);
const confirmDeleteId = ref(null);

const form = reactive({
  fullName: '',
  email: '',
  password: '',
  kelas: []
});

const modalTeacher = ref(null);
const newPassword = ref('');
const showNewPassword = ref(false);
const isResetting = ref(false);

onMounted(async () => {
  try {
    const resKelas = await axios.get('/kelas');
    availableKelas.value = resKelas.data;
    fetchTeachers();
  } catch (error) {
    console.error('Gagal mengambil data', error);
  }
});

const fetchTeachers = async () => {
  try {
    const res = await axios.get('/guru');
    teachers.value = res.data;
  } catch (error) {
    console.error('Gagal memuat daftar guru', error);
  }
};

const toggleKelas = (kelasId) => {
  const index = form.kelas.indexOf(kelasId);
  if (index === -1) form.kelas.push(kelasId);
  else form.kelas.splice(index, 1);
};

const getInitials = (name) => {
  const parts = String(name || 'GR').split(' ');
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
  return parts[0].substring(0, 2).toUpperCase();
};

const saveGuru = async () => {
  isSubmitting.value = true;
  try {
    await axios.post('/guru', {
      fullName: form.fullName,
      email: form.email,
      password: form.password,
      kelas_ids: form.kelas
    });
    form.fullName = '';
    form.email = '';
    form.password = '';
    form.kelas = [];
    showPasswordInput.value = false;
    notify('Akun guru berhasil dibuat.', 'success');
    fetchTeachers();
  } catch (error) {
    console.error('Gagal menambah guru', error);
    const msg = error.response?.data?.errors
      ? Object.values(error.response.data.errors).flat().join(', ')
      : (error.response?.data?.message || 'Gagal membuat akun guru.');
    notify(msg, 'error');
  } finally {
    isSubmitting.value = false;
  }
};

const executeDeleteTeacher = async (teacher) => {
  try {
    await axios.delete(`/guru/${teacher.id}`);
    teachers.value = teachers.value.filter(t => t.id !== teacher.id);
    confirmDeleteId.value = null;
    notify(`Akun ${teacher.name} berhasil dihapus.`, 'info');
  } catch (error) {
    console.error('Gagal menghapus guru', error);
    notify('Gagal menghapus akun guru.', 'error');
  }
};

const viewPassword = (teacher) => {
  modalTeacher.value = teacher;
  newPassword.value = '';
  showNewPassword.value = false;
};

const closeModal = () => {
  modalTeacher.value = null;
};

const resetPassword = async () => {
  if (!newPassword.value || newPassword.value.length < 6) {
    notify('Kata sandi baru minimal 6 karakter.', 'warning');
    return;
  }

  isResetting.value = true;
  try {
    await axios.put(`/guru/${modalTeacher.value.id}/reset-password`, {
      new_password: newPassword.value
    });
    notify('Kata sandi berhasil diperbarui.', 'success');
    closeModal();
  } catch (error) {
    console.error('Gagal mereset password', error);
    const msg = error.response?.data?.message || 'Gagal mereset kata sandi.';
    notify(msg, 'error');
  } finally {
    isResetting.value = false;
  }
};
</script>
