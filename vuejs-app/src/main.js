// 1. Import CSS Packages ដោយផ្ទាល់ក្នុង JS
import '@fortawesome/fontawesome-free/css/all.min.css';
import 'icheck-bootstrap/icheck-bootstrap.min.css';
import 'admin-lte/dist/css/adminlte.min.css';
import './main.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import 'admin-lte/dist/js/adminlte.min.js';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import { useUserStore } from '@/stores/user';
import { apiVerify } from '@/functions/api/auth';

const app = createApp(App);

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

app.use(pinia);
app.use(router);

// Axios Interceptor
axios.interceptors.request.use((config) => {
  const userStore = useUserStore();
  const token = userStore.getSanctumToken();
  if (token && !config.headers.Authorization) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Router Guard
router.beforeEach(async (to, from) => {
  const userStore = useUserStore();
  const { guarded } = to.meta;

  if (guarded === undefined) return true;

  try {
    const response = await apiVerify();
    const { data } = response;
    userStore.setState(data.user);
  } catch (error) {
    userStore.reset();
  }

  if (guarded && !userStore.isAuthenticated) {
    if (to.name !== 'auth.signin') return { name: 'auth.signin' };
  }

  if (!guarded && userStore.isAuthenticated) {
    if (to.name !== 'dashboard') return { name: 'dashboard' };
  }

  return true;
});

app.mount('#app');