import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './src/router';
import App from './src/App.vue';
import UiIcon from './src/components/shared/UiIcon.vue';
import '../css/app.css';
import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;
window.axios.defaults.baseURL = import.meta.env.VITE_API_BASE_URL || '/api';

window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            if (router.currentRoute.value.path !== '/login') {
                localStorage.removeItem('user');
                router.push('/login');
            }
        }
        return Promise.reject(error);
    }
);

const app = createApp(App);

app.component('UiIcon', UiIcon);
app.use(createPinia());
app.use(router);

app.mount('#app');
