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

// ✅ Router Guard ដែលបានកែសម្រួល
router.beforeEach((to, from) => {
  const userStore = useUserStore();
  const token = userStore.getSanctumToken();
  const isAuth = userStore.isAuthenticated || !!token;
  const { guarded } = to.meta;

  if (guarded === undefined) return true;

  // ១. បើ Route ត្រូវការសិទ្ធិ (guarded: true) តែមិនទាន់ Login -> បញ្ជូនទៅ Signin
  if (guarded && !isAuth) {
    if (to.name !== 'auth.signin') return { name: 'auth.signin' };
  }

  // ២. បើបាន Login រួចហើយ តែព្យាយាមចូលទំព័រ Public/Signin -> បញ្ជូនទៅ Dashboard
  if (!guarded && isAuth) {
    if (to.name !== 'dashboard') return { name: 'dashboard' };
  }

  return true;
});

app.mount('#app');