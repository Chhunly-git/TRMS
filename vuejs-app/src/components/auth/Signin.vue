<template>
  <div class="login-wrapper">
    <!-- ផ្នែកខាងឆ្វេង៖ រូបភាពអាគារ -->
    <div 
      class="bg-section" 
      :style="{ backgroundImage: `url(${bgImage})` }"
    >
      <div class="bg-overlay"></div>
    </div>

    <!-- ផ្នែកខាងស្តាំ៖ ផ្ទៃ Login ទំនើប -->
    <div class="form-section">
      <div class="login-card">
        <!-- Logo & Header -->
        <div class="brand-header text-center mb-4">
          <div class="logo-circle mx-auto mb-2">
            <img :src="logoImage" alt="Logo" width="80" height="80" class="object-contain" />
          </div>
          <h2 class="brand-title">ប្រព័ន្ធគ្រប់គ្រងទិន្នន័យមន្ត្រី</h2>
          <p class="brand-subtitle"></p>
        </div>

        <form @submit.prevent="signIn">
          <!-- Email Input -->
          <div class="form-group mb-3">
            <label class="form-label">អ៊ីមែល / Email</label>
            <div class="custom-input-group">
              <input 
                type="email" 
                v-model="user.email" 
                class="form-control custom-input" 
                placeholder="enter@example.com"
                :class="{ 'is-invalid': !!userError.email }" 
              />
              <span class="input-icon"><i class="fas fa-envelope"></i></span>
            </div>
            <div v-if="userError.email" class="invalid-feedback d-block">
              {{ userError.email }}
            </div>
          </div>

          <!-- Password Input -->
          <div class="form-group mb-4">
            <label class="form-label">ពាក្យសម្ងាត់ / Password</label>
            <div class="custom-input-group">
              <input 
                type="password" 
                v-model="user.password" 
                class="form-control custom-input" 
                placeholder="••••••••" 
                autocomplete="current-password"
                :class="{ 'is-invalid': !!userError.password }" 
              />
              <span class="input-icon"><i class="fas fa-lock"></i></span>
            </div>
            <div v-if="userError.password" class="invalid-feedback d-block">
              {{ userError.password }}
            </div>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn btn-primary-theme btn-block w-100">
            <span>ចូលប្រព័ន្ធ / Sign In</span> <i class="fas fa-arrow-right ml-2"></i>
          </button>
        </form>

        <!-- Divider -->
        <div class="divider my-4">
          <span>ឬ / OR</span>
        </div>

        <!-- Google Login
        <button @click="googleSignIn()" type="button" class="btn btn-google btn-block w-100 mb-4">
          <i class="fab fa-google mr-2"></i> Sign in with Google
        </button> -->

        <!-- Links -->
        <div class="text-center auth-links">
          <router-link :to="{ name: 'auth.reset-password' }" class="d-block mb-1 link-item">
            ភ្លេចពាក្យសម្ងាត់? (Forgot password?)
          </router-link>
          <!-- <router-link :to="{ name: 'auth.signup' }" class="d-block link-item">
            ចុះឈ្មោះគណនីថ្មី (Register membership)
          </router-link> -->
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from "vue-router";
import { reactive } from "vue";
import { apiSignIn } from "@/functions/api/auth";
import { LoadingModal, MessageModal, CloseModal } from "@/functions/swal";
import { useUserStore } from "@/stores/user";
import { apiGoogleOAuthRedirect } from "@/functions/api/google-oauth";
import bgImage from '@/assets/images/bkg.jpg';
import logoImage from '@/assets/images/logoImage.webp';

const router = useRouter();
const userStore = useUserStore();

const user = reactive({
  email: "",
  password: "",
});

const userError = reactive({
  email: "",
  password: "",
});

const defaultUser = JSON.parse(JSON.stringify(user));
const defaultUserError = JSON.parse(JSON.stringify(userError));

function resetAllState() {
  Object.assign(user, defaultUser);
  Object.assign(userError, defaultUserError);
}

async function signIn() {
  try {
    LoadingModal('Signing In...');
    const response = await apiSignIn(user);
    const { data } = response;
    userStore.setState(data.user);
    userStore.setSanctumToken(data.token);
    resetAllState();
    router.replace({ name: "dashboard" });
    return CloseModal();
  } catch (error) {
    const { response } = error;
    if (!response) {
      return MessageModal({ icon: "error", title: "Error", text: error.message });
    }
    const { status, data } = response;
    if (status === 422) {
      Object.keys(userError).forEach((key) => {
        userError[key] = data.errors[key]
          ? data.errors[key][0]
          : "";
      });
      return CloseModal();
    }
    return MessageModal({ icon: "error", title: "Error", text: data.message });
  }
}

const googleSignIn = async () => {
  try {
    LoadingModal();
    const response = await apiGoogleOAuthRedirect();
    window.location.href = response.data.redirect_url;
  } catch (error) {
    return MessageModal({ icon: "error", title: "Error", text: error.response?.data?.message || error.message });
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.login-wrapper {
  display: flex;
  width: 100vw;
  height: 100vh;
  overflow: hidden;
  font-family: 'Kantumruy Pro', 'Inter', sans-serif;
}

/* ផ្នែកខាងឆ្វេង */
.bg-section {
  flex: 1;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  position: relative;
}

.bg-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to right, rgba(0, 0, 0, 0.1), rgba(12, 43, 41, 0.4));
}

/* ផ្នែកខាងស្តាំ */
.form-section {
  width: 460px;
  background-color: #0c2b29; /* ពណ៌បៃតងចាស់ស៊ីគ្នានឹង Background */
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 30px;
}

.login-card {
  width: 100%;
  max-width: 380px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  padding: 32px 28px;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
}

/* Header */
.logo-circle {
  width: 80px;
  height: 80px;
  background-color: #e6f2f1;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  color: #0c2b29;
}

.brand-title {
  font-size: 24px;
  font-weight: 700;
  color: #0c2b29;
  letter-spacing: 1px;
  margin: 0;
}

.brand-subtitle {
  font-size: 13px;
  color: #64748b;
  margin-top: 2px;
}

/* Form inputs */
.form-label {
  font-size: 12px;
  font-weight: 600;
  color: #334155;
  margin-bottom: 6px;
}

.custom-input-group {
  position: relative;
}

.custom-input {
  width: 100%;
  height: 44px;
  padding: 10px 16px 10px 40px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background-color: #f8fafc;
  font-size: 14px;
  transition: all 0.2s ease;
}

.custom-input:focus {
  background-color: #ffffff;
  border-color: #0c2b29;
  box-shadow: 0 0 0 3px rgba(12, 43, 41, 0.15);
  outline: none;
}

.input-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 14px;
}

/* Buttons */
.btn-primary-theme {
  background-color: #0c2b29;
  color: #ffffff;
  height: 44px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 14px;
  border: none;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-primary-theme:hover {
  background-color: #144542;
  box-shadow: 0 4px 12px rgba(12, 43, 41, 0.3);
}

.btn-google {
  background-color: #ffffff;
  color: #334155;
  border: 1px solid #cbd5e1;
  height: 42px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-google:hover {
  background-color: #f1f5f9;
  border-color: #94a3b8;
}

/* Divider */
.divider {
  display: flex;
  align-items: center;
  text-align: center;
  color: #94a3b8;
  font-size: 12px;
}

.divider::before, .divider::after {
  content: '';
  flex: 1;
  border-bottom: 1px solid #e2e8f0;
}

.divider span {
  padding: 0 10px;
}

/* Links */
.link-item {
  color: #0c2b29;
  font-size: 12px;
  font-weight: 500;
  text-decoration: none;
  transition: color 0.2s;
}

.link-item:hover {
  color: #1c6b66;
  text-decoration: underline;
}

@media (max-width: 992px) {
  .bg-section {
    display: none;
  }
  .form-section {
    width: 100%;
  }
}
</style>