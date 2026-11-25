<template>
  <div class="login-page">
    <main class="login-main">
      <div class="login-shell">
        <section class="login-story" aria-label="Madrasah As-Sajjaad">
          <img
            src="/images/education/indonesia-classroom.jpg"
            alt="Guru dan siswi berhijab belajar di Pandeglang, Banten"
            fetchpriority="high"
            width="1600"
            height="1067"
          />
          <div class="login-story-shade"></div>
          <div class="login-story-copy">
            <div class="login-photo-brand">
              <UiIcon name="school" :size="32" />
              <strong>As-Sajjaad</strong>
            </div>
            <h2>Infak madrasah.</h2>
          </div>
        </section>
        <section class="login-card" aria-labelledby="login-title">
          <div class="login-heading"><h1 id="login-title">Masuk</h1></div>
          <form
            @submit.prevent="handleLogin"
            :aria-busy="isLoading"
            class="login-form"
          >
            <div
              v-if="errorMessage"
              id="login-error"
              role="alert"
              class="login-error"
            >
              {{ errorMessage }}
            </div>
            <div class="login-field">
              <label for="email">Username, email, atau NIS</label>
              <div class="login-input-wrap">
                <UiIcon name="group" :size="19" /><input
                  id="email"
                  v-model="form.email"
                  name="email"
                  type="text"
                  required
                  autocomplete="username"
                  autocapitalize="none"
                  :spellcheck="false"
                  class="ui-input"
                  :aria-describedby="errorMessage ? 'login-error' : undefined"
                />
              </div>
            </div>
            <div class="login-field">
              <label for="password">Kata sandi</label>
              <div class="login-input-wrap password-field">
                <UiIcon name="lock_reset" :size="19" /><input
                  id="password"
                  v-model="form.password"
                  name="password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  autocomplete="current-password"
                  class="ui-input"
                  :aria-describedby="errorMessage ? 'login-error' : undefined"
                /><button
                  type="button"
                  class="password-toggle"
                  @click="showPassword = !showPassword"
                  :aria-label="
                    showPassword
                      ? 'Sembunyikan kata sandi'
                      : 'Tampilkan kata sandi'
                  "
                  :aria-pressed="showPassword"
                  aria-controls="password"
                >
                  <UiIcon
                    :name="showPassword ? 'visibility_off' : 'visibility'"
                    :size="19"
                  />
                </button>
              </div>
            </div>
            <label class="remember-field"
              ><input
                v-model="form.remember"
                type="checkbox"
                name="remember"
              />Ingat saya</label
            >
            <button
              type="submit"
              :disabled="isLoading"
              class="ui-btn-primary login-submit"
            >
              {{ isLoading ? 'Memproses…' : 'Masuk'
              }}<UiIcon
                v-if="!isLoading"
                name="arrow_forward"
                :size="19"
                directional
              />
            </button>
          </form>
          <p class="login-help">Bantuan akses: <strong>tata usaha.</strong></p>
        </section>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { getRoleHome } from '@/utils/auth';

const router = useRouter();
const showPassword = ref(false);
const errorMessage = ref('');
const isLoading = ref(false);
const form = reactive({ email: '', password: '', remember: false });

const handleLogin = async () => {
  if (isLoading.value) return;
  isLoading.value = true;
  errorMessage.value = '';
  try {
    const apiOrigin = new URL(
      axios.defaults.baseURL || '/api',
      window.location.origin,
    ).origin;
    await axios.get('/sanctum/csrf-cookie', { baseURL: apiOrigin });
    const { data } = await axios.post('/login', {
      ...form,
      email: form.email.trim(),
    });
    const home = getRoleHome(data.user?.role);
    if (!home) throw new Error('Unsupported account role');
    localStorage.setItem('user', JSON.stringify(data.user));
    await router.replace(home);
  } catch (error) {
    const status = error.response?.status;
    errorMessage.value =
      status === 422 || status === 401
        ? 'Username, email, NIS, atau kata sandi salah.'
        : status === 419
          ? 'Sesi berakhir. Silakan masuk lagi.'
          : 'Tidak dapat masuk. Silakan coba lagi.';
  } finally {
    isLoading.value = false;
  }
};
</script>
