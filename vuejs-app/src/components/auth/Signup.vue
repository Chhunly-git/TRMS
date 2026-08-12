<template>
  <div class="login-wrapper">
    <!-- ផ្នែកខាងឆ្វេង៖ រូបភាពអាគារ -->
    <div 
      class="bg-section" 
      :style="{ backgroundImage: `url(${bgImage})` }"
    >
      <div class="bg-overlay"></div>
    </div>

    <!-- ផ្នែកខាងស្តាំ៖ ផ្ទៃ SignUp ទំនើប -->
    <div class="form-section">
      <div class="login-card">
        <!-- Logo & Header -->
        <div class="brand-header text-center mb-4">
          <div class="logo-circle mx-auto mb-2">
            <img :src="logoImage" alt="Logo" width="80" height="80" class="object-contain" />
          </div>
          <h2 class="brand-title">ប្រព័ន្ធគ្រប់គ្រងទិន្នន័យមន្ត្រី</h2>
          <p class="brand-subtitle">ចុះឈ្មោះបង្កើតគណនីថ្មី (Sign up)</p>
        </div>

        <form @submit.prevent="signUp">
          <!-- Name Input -->
          <div class="form-group mb-3">
            <label class="form-label">ឈ្មោះ / Name</label>
            <div class="custom-input-group">
              <input 
                type="text" 
                v-model="user.name" 
                class="form-control custom-input" 
                placeholder="John Doe"
                :class="{ 'is-invalid': !!userError.name }" 
              />
              <span class="input-icon"><i class="fas fa-user"></i></span>
            </div>
            <div v-if="userError.name" class="invalid-feedback d-block">
              {{ userError.name }}
            </div>
          </div>

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
          <div class="form-group mb-3">
            <label class="form-label">ពាក្យសម្ងាត់ / Password</label>
            <div class="custom-input-group">
              <input 
                type="password" 
                v-model="user.password" 
                class="form-control custom-input" 
                placeholder="••••••••" 
                autocomplete="new-password"
                :class="{ 'is-invalid': !!userError.password }" 
              />
              <span class="input-icon"><i class="fas fa-lock"></i></span>
            </div>
            <div v-if="userError.password" class="invalid-feedback d-block">
              {{ userError.password }}
            </div>
          </div>

          <!-- Confirm Password Input -->
          <div class="form-group mb-4">
            <label class="form-label">បញ្ជាក់ពាក្យសម្ងាត់ / Confirm Password</label>
            <div class="custom-input-group">
              <input 
                type="password" 
                v-model="user.password_confirmation" 
                class="form-control custom-input" 
                placeholder="••••••••" 
                autocomplete="new-password"
              />
              <span class="input-icon"><i class="fas fa-lock"></i></span>
            </div>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn btn-primary-theme btn-block w-100">
            <span>ចុះឈ្មោះ / Sign Up</span> <i class="fas fa-user-plus ml-2"></i>
          </button>
        </form>

        <!-- Divider -->
        <div class="divider my-4">
          <span>ឬ / OR</span>
        </div>

        <!-- Google SignUp -->
        <button @click="googleSignUp()" type="button" class="btn btn-google btn-block w-100 mb-3">
          <i class="fab fa-google mr-2"></i> Sign up with Google
        </button>

        <!-- Links -->
        <div class="text-center auth-links">
          <router-link :to="{ name: 'auth.signin' }" class="d-block link-item">
            មានគណនីរួចហើយ? (I already have an account)
          </router-link>
        </div>

        <!-- Verification Resend Section -->
        <div v-if="signedUpEmail" class="resend-box mt-3 p-3 text-center">
          <p class="mb-1 text-sm">បានចុះឈ្មោះជាមួយ <strong>{{ signedUpEmail }}</strong></p>
          <p class="mb-2 text-muted text-xs">មិនបានទទួលអ៊ីមែលផ្ទៀងផ្ទាត់?</p>
          <button @click="sendVerificationEmail" class="btn btn-outline-secondary btn-sm w-100">
            ផ្ញើសារផ្ទៀងផ្ទាត់ឡើងវិញ (Resend Email)
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { apiSignUp, apiSendVerificationEmail } from "@/functions/api/auth";
import { LoadingModal, MessageModal, CloseModal } from "@/functions/swal";
import { apiGoogleOAuthRedirect } from "@/functions/api/google-oauth";
import bgImage from '@/assets/images/bkg.jpg';
import logoImage from '@/assets/images/logoImage.webp';

const user = reactive({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const userError = reactive({
  name: "",
  email: "",
  password: "",
});

const defaultUser = JSON.parse(JSON.stringify(user));
const defaultUserError = JSON.parse(JSON.stringify(userError));

function resetAllState() {
  Object.assign(user, defaultUser);
  Object.assign(userError, defaultUserError);
}

async function signUp() {
  resetSignedUpEmail();
  try {
    LoadingModal('Signing Up...');
    await apiSignUp(user);
    signedUpEmail.value = user.email;
    resetAllState();
    return MessageModal({
      icon: "success",
      title: "Success",
      text: "Your account has been created successfully."
    });
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

const signedUpEmail = ref("");
async function sendVerificationEmail() {
  try {
    LoadingModal('Requesting verification email...');
    const response = await apiSendVerificationEmail(signedUpEmail.value);
    const { data } = response;
    return MessageModal({
      icon: "success",
      title: "Success",
      text: data.message
    });
  } catch (error) {
    const { response } = error;
    if (!response) {
      return MessageModal({ icon: "error", title: "Error", text: error.message });
    }
    const { data } = response;
    return MessageModal({ icon: "error", title: "Error", text: data.message });
  }
}
function resetSignedUpEmail() {
  signedUpEmail.value = "";
}

const googleSignUp = async () => {
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
  background-color: #0c2b29;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 30px;
  overflow-y: auto;
}

.login-card {
  width: 100%;
  max-width: 380px;
  max-height: calc(100vh - 40px); /* ការពារកុំឱ្យវែងហួសអេក្រង់ */
  overflow-y: auto;              /* បើវែងពេកនឹងមាន scrollbar ស្អាត */
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  padding: 20px 24px;            /* បន្ថយ Padding ជុំវិញ (ពី 28px មក 20px) */
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
}

/* Header */
.logo-circle {
  width: 60px;
  height: 60px;
  background-color: #e6f2f1;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  color: #0c2b29;
}

.brand-title {
  font-size: 22px;
  font-weight: 700;
  color: #0c2b29;
  letter-spacing: 0.5px;
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
  margin-bottom: 4px;
}

.custom-input-group {
  position: relative;
}

.custom-input {
  width: 100%;
  height: 42px;
  padding: 8px 16px 8px 40px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background-color: #f8fafc;
  font-size: 13px;
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
  font-size: 13px;
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

/* Resend Box */
.resend-box {
  background-color: #f1f5f9;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
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
/* បន្ថយ Margin របស់ Form Header */
.brand-header {
  margin-bottom: 1rem !important; /* បន្ថយចន្លោះខាងក្រោម Header */
}

/* បន្ថយ Margin របស់ Input Groups */
.form-group {
  margin-bottom: 0.75rem !important; /* បន្ថយពី mb-3/mb-4 មកចន្លោះល្មម */
}

/* បង្រួម Divider */
.divider {
  margin-top: 1rem !important;
  margin-bottom: 1rem !important;
}

/* សម្រួល Style Scrollbar ឱ្យមើលទៅ Modern */
.login-card::-webkit-scrollbar {
  width: 4px;
}
.login-card::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
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