<template>
  <div class="login-page">
    <header class="login-brand">
      <span class="brand-mark" aria-hidden="true">AS</span>
      <span>Madrasah As-Sajjaad</span>
    </header>

    <main class="login-main">
      <section class="login-card" aria-labelledby="login-title">
        <div class="login-heading">
          <h1 id="login-title">Masuk</h1>
          <p>Portal infak madrasah</p>
        </div>

        <form @submit.prevent="handleLogin" :aria-busy="isLoading" class="login-form">
          <div v-if="errorMessage" id="login-error" role="alert" class="login-error">{{ errorMessage }}</div>
          <div class="login-field">
            <label for="email">Username, email, atau NIS</label>
            <input id="email" v-model="form.email" name="email" type="text" required autocomplete="username" autocapitalize="none" :spellcheck="false" class="ui-input" :aria-describedby="errorMessage ? 'login-error' : undefined" />
          </div>
          <div class="login-field">
            <label for="password">Kata sandi</label>
            <div class="password-field">
              <input id="password" v-model="form.password" name="password" :type="showPassword ? 'text' : 'password'" required autocomplete="current-password" class="ui-input" :aria-describedby="errorMessage ? 'login-error' : undefined" />
              <button type="button" class="password-toggle" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'" :aria-pressed="showPassword" aria-controls="password">
                <UiIcon :name="showPassword ? 'visibility_off' : 'visibility'" :size="19" />
              </button>
            </div>
          </div>
          <label class="remember-field"><input v-model="form.remember" type="checkbox" name="remember" /> Ingat saya</label>
          <button type="submit" :disabled="isLoading" class="ui-btn-primary w-full">{{ isLoading ? 'Memproses…' : 'Masuk' }}<UiIcon v-if="!isLoading" name="arrow_forward" :size="18" directional /></button>
        </form>
        <p class="login-help">Butuh akses? Hubungi tata usaha.</p>
      </section>
    </main>
    <footer class="login-footer">Madrasah As-Sajjaad · Infak &amp; Kas</footer>
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
    const apiOrigin = new URL(axios.defaults.baseURL || '/api', window.location.origin).origin;
    await axios.get('/sanctum/csrf-cookie', { baseURL: apiOrigin });
    const { data } = await axios.post('/login', { ...form, email: form.email.trim() });
    const home = getRoleHome(data.user?.role);
    if (!home) throw new Error('Unsupported account role');
    localStorage.setItem('user', JSON.stringify(data.user));
    await router.replace(home);
  } catch (error) {
    const status = error.response?.status;
    errorMessage.value = status === 422 || status === 401
      ? 'Username, email, NIS, atau kata sandi salah.'
      : status === 419
        ? 'Sesi berakhir. Silakan masuk lagi.'
        : 'Tidak dapat masuk. Silakan coba lagi.';
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.login-page { min-height: 100svh; display: flex; flex-direction: column; background: #F5F3EE; color: #1C1917; }
.login-brand { display: flex; align-items: center; gap: 12px; padding: 28px 40px; font-size: 14px; font-weight: 600; }
.brand-mark { display: grid; place-items: center; width: 36px; height: 36px; border-radius: 9px; background: #292524; color: #FDFCF9; font-size: 13px; letter-spacing: -.03em; box-shadow: var(--shadow-btn-navy); }
.login-main { flex: 1; display: grid; place-items: center; padding: 32px 20px 64px; }
.login-card { width: 100%; max-width: 420px; padding: 40px; background: #FDFCF9; border: 1px solid transparent; border-radius: 16px; box-shadow: var(--shadow-elevated); }
.login-heading { margin-bottom: 32px; }
.login-heading h1 { font-size: 28px; line-height: 1.2; font-weight: 700; letter-spacing: -.03em; color: #1C1917; }
.login-heading p { margin-top: 8px; font-size: 14px; color: #78716C; }
.login-form { display: flex; flex-direction: column; gap: 22px; }
.login-field { display: flex; flex-direction: column; gap: 8px; }
.login-field label { font-size: 13px; font-weight: 600; color: #44403C; }
.password-field { position: relative; }
.password-field input { padding-right: 48px; }
.password-toggle { position: absolute; right: 1px; top: 1px; min-width: 44px; min-height: 44px; display: grid; place-items: center; color: #78716C; border-radius: 7px; }
.remember-field { min-height: 44px; display: flex; align-items: center; gap: 10px; font-size: 13px; color: #57534E; cursor: pointer; margin: -8px 0; }
.remember-field input { width: 16px; height: 16px; accent-color: #A8551E; }
.login-error { padding: 12px; background: #FDF2F4; border: 1px solid rgba(159, 18, 57, 0.22); color: #9F1239; border-radius: 10px; font-size: 13px; line-height: 1.5; }
.login-help { margin-top: 28px; padding-top: 24px; border-top: 1px solid #E6E1D6; color: #78716C; font-size: 12px; text-align: center; }
.login-footer { text-align: center; padding: 20px; color: #78716C; font-size: 12px; }
@media (hover: hover) { .password-toggle:hover { color: #1C1917; background: #ECE8DF; } }
@media (max-width: 480px) { .login-brand { padding: 24px; } .login-card { padding: 28px 24px; } .login-main { padding-top: 20px; padding-bottom: 32px; } }
</style>
