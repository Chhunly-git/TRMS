<template>
  <div class="content-wrapper" style="min-height: 1000px; background-color: #f4f6f9;">
    <!-- Content Header -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">ការកំណត់គណនី (Account Settings)</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item">
                <router-link :to="{ name: 'dashboard' }">Home</router-link>
              </li>
              <li class="breadcrumb-item active">Profile Settings</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <!-- Main Content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          
          <!-- Column 1: Profile Image Management (ខាងឆ្វេង) -->
          <div class="col-md-6">
            <div class="card card-primary card-outline shadow-sm h-100">
              <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-camera mr-1"></i> រូបភាពគណនី (Profile Image)</h3>
              </div>
              <div class="card-body box-profile text-center d-flex flex-column justify-content-center align-items-center pt-4 pb-4">
                
                <div class="mb-3">
                  <div class="image-wrapper d-inline-block p-1 border rounded-circle" style="border-color: #dee2e6 !important;">
                    <img 
                      class="profile-user-img img-fluid img-circle" 
                      :src="tempImage" 
                      alt="User profile picture"
                      style="width: 130px; height: 130px; object-fit: cover; border: 3px solid #fff;"
                    >
                  </div>
                </div>

                <div class="mb-3">
                  <input 
                    @change="onChangeImage" 
                    type="file" 
                    ref="fileInputRef" 
                    class="d-none"
                    accept=".jpg, .jpeg, .png"
                  />
                  <button @click="$refs.fileInputRef.click()" class="btn btn-primary btn-sm mx-1 mb-2" title="Upload Image">
                    <i class="fas fa-upload mr-1"></i> ជ្រើសរើស
                  </button>
                  <button @click="onDeleteImage()" class="btn btn-danger btn-sm mx-1 mb-2" title="Delete Image">
                    <i class="fas fa-trash mr-1"></i> លុប
                  </button>
                  <button @click="onResetImage()" class="btn btn-secondary btn-sm mx-1 mb-2" title="Reset Image">
                    <i class="fas fa-undo-alt mr-1"></i> ដើម
                  </button>
                  <button v-if="imageChanged" @click="saveProfileImage()" class="btn btn-success btn-sm mx-1 mb-2" title="Save Image">
                    <i class="fas fa-check mr-1"></i> រក្សាទុក
                  </button>
                </div>

                <h3 class="profile-username text-center font-weight-bold mb-1">
                  {{ userStore.name_kh || userStore.name || userStore.user?.name_kh || userStore.user?.name }}
                </h3>
                <p class="text-muted text-center mb-0" style="font-size: 0.95rem;">
                  {{ userStore.email || userStore.user?.email }}
                </p>
                <p class="text-primary text-center font-weight-bold mt-2 mb-0">
                  {{ userStore.position_name || userStore.user?.position_name || '---' }}
                </p>
              </div>
            </div>
          </div>

          <!-- Column 2: Password Management (ខាងស្ដាំ) -->
          <div class="col-md-6">
            <div class="card card-primary card-outline shadow-sm h-100">
              <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-lock mr-1"></i> ប្ដូរពាក្យសម្ងាត់ (Change Password)</h3>
              </div>
              <div class="card-body p-4 d-flex flex-column justify-content-center">
                <form @submit.prevent="savePassword" class="form-horizontal w-100">
                  <div v-if="!userStore.password_null" class="form-group row align-items-center">
                    <label class="col-sm-4 col-form-label font-weight-bold text-dark text-sm-right">ពាក្យសម្ងាត់បច្ចុប្បន្ន</label>
                    <div class="col-sm-8">
                      <input 
                        v-model="user.current_password" 
                        type="password" 
                        class="form-control"
                        placeholder="Current Password" 
                        :class="!!userError.current_password ? 'is-invalid' : ''" 
                      />
                      <div class="invalid-feedback">{{ userError.current_password }}</div>
                    </div>
                  </div>

                  <div class="form-group row align-items-center mt-3">
                    <label class="col-sm-4 col-form-label font-weight-bold text-dark text-sm-right">ពាក្យសម្ងាត់ថ្មី</label>
                    <div class="col-sm-8">
                      <input 
                        v-model="user.new_password" 
                        type="password" 
                        class="form-control"
                        placeholder="New Password" 
                        :class="!!userError.new_password ? 'is-invalid' : ''" 
                      />
                      <div class="invalid-feedback">{{ userError.new_password }}</div>
                    </div>
                  </div>

                  <div class="form-group row align-items-center mt-3">
                    <label class="col-sm-4 col-form-label font-weight-bold text-dark text-sm-right">បញ្ជាក់ពាក្យសម្ងាត់</label>
                    <div class="col-sm-8">
                      <input 
                        v-model="user.new_password_confirmation" 
                        type="password" 
                        class="form-control"
                        placeholder="Confirm Password" 
                      />
                    </div>
                  </div>

                  <div class="form-group row mt-4 mb-0">
                    <div class="col-sm-8 offset-sm-4">
                      <button type="button" @click="resetAllState" class="btn btn-danger mr-2 px-3">បោះបង់</button>
                      <button type="submit" class="btn btn-success px-3">រក្សាទុក</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import emptyImage from "@/assets/images/emptyImage.png";
import { CloseModal, LoadingModal, MessageModal } from "@/functions/swal";
import { useRouter } from "vue-router";

import { reactive, ref, watch, onMounted, computed } from "vue"; // 🟢 បន្ថែម computed ទីនេះ
import {
  apiChangePassword,
  apiCreatePassword,
  apiDeleteProfileImage,
  apiUpdateProfileImage,
  apiGetProfile
} from "@/functions/api/auth";
import { useUserStore } from "@/stores/user";

const router = useRouter();
const userStore = useUserStore();

// --- ទាញយកទិន្នន័យ Profile ---
onMounted(async () => {
  try {
    const response = await apiGetProfile();
    const userData = response.data.user || response.data.data || response.data;
    if (userStore.user) {
      Object.assign(userStore.user, userData);
    } else {
      Object.assign(userStore, userData);
    }
  } catch (error) {
    console.error("មិនអាចទាញយកទិន្នន័យប្រវត្តិរូបបានឡើយ:", error);
  }
});

// ==========================================
// PASSWORD & IMAGE LOGIC
// ==========================================

const user = reactive({
  current_password: "",
  new_password: "",
  new_password_confirmation: "",
});

const userError = reactive({
  current_password: "",
  new_password: "",
});

const defaultUser = JSON.parse(JSON.stringify(user));
const defaultUserError = JSON.parse(JSON.stringify(userError));

function resetAllState() {
  Object.assign(user, defaultUser);
  Object.assign(userError, defaultUserError);
}

async function savePassword() {
  try {
    LoadingModal('Saving password...');
    const response = userStore.password_null
      ? await apiCreatePassword(
          user.new_password,
          user.new_password_confirmation
        )
      : await apiChangePassword(
          user.current_password,
          user.new_password,
          user.new_password_confirmation
        );
    resetAllState();
    await MessageModal({ icon: "success", title: "Success", text: response.data.message }, () => router.push({ name: "auth.signin" }));
  } catch (error) {
    const { response } = error;
    if (!response) {
      return MessageModal({ icon: "error", title: "Error", text: error.message });
    }
    const { status, data } = response;
    if (status === 422) {
      Object.keys(userError).forEach((key) => {
        userError[key] = data.errors[key] ? data.errors[key][0] : "";
      });
      return CloseModal();
    }
    return MessageModal({ icon: "error", title: "Error", text: data.message });
  }
}

const tempImage = ref(emptyImage);
const selectedImageFile = ref(null);
const fileInputRef = ref(null);
const allowedExtensions = ["jpg", "jpeg", "png"];


const profileImage = computed(() => userStore.profile_image); // Note: Computed import included or use userStore directly if preferred
watch(
  () => userStore.profile_image,
  (nv) => (tempImage.value = nv ?? emptyImage),
  { immediate: true }
);

const imageChanged = computed(
  () => tempImage.value !== (userStore.profile_image ?? emptyImage)
);

function onChangeImage(event) {
  const files = event.target.files;
  if (files && files.length > 0) {
    const extFile = files[0].name.split(".").pop()?.toLowerCase();
    if (!allowedExtensions.includes(extFile)) {
      return MessageModal({ icon: "error", title: "Error", text: "Only jpg/jpeg and png files are allowed!" });
    }
    const reader = new FileReader();
    reader.onloadend = function () {
      const img = new Image();
      img.onload = function () {
        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");
        canvas.width = 454;
        canvas.height = 454;
        const size = Math.min(img.width, img.height);
        const x = (img.width - size) / 2;
        const y = (img.height - size) / 2;
        ctx.drawImage(img, x, y, size, size, 0, 0, 454, 454);

        canvas.toBlob((blob) => {
          if (!blob) {
            return MessageModal({ icon: "error", title: "Error", text: "Failed to process image. Please try again." });
          }
          selectedImageFile.value = new File([blob], "profile.png", { type: "image/png" });
          tempImage.value = canvas.toDataURL("image/png");
        }, "image/png");
      };
      img.src = reader.result;
    };
    reader.readAsDataURL(files[0]);
    event.target.value = null;
  }
}

function onDeleteImage() {
  selectedImageFile.value = null;
  tempImage.value = emptyImage;
}

function onResetImage() {
  selectedImageFile.value = null;
  tempImage.value = userStore.profile_image ? userStore.profile_image : emptyImage;
}

async function saveProfileImage() {
  try {
    LoadingModal('Saving profile image...');
    const isDeleting = tempImage.value === emptyImage;
    const response = isDeleting
      ? await apiDeleteProfileImage()
      : await apiUpdateProfileImage(selectedImageFile.value);
    
    userStore.profile_image = isDeleting ? null : response.data.profile_image;
    userStore.profile_thumbnail = isDeleting ? null : response.data.profile_thumbnail;
    selectedImageFile.value = null;
    
    await MessageModal({ icon: "success", title: "Success", text: response.data.message });
  } catch (error) {
    return MessageModal({ icon: "error", title: "Error", text: error.response?.data?.message || error.message });
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap');

.content-wrapper {
  font-family: 'Battambang', sans-serif !important;
  background-color: #f4f6f9;
}

/* Card Styling */
.card {
  border: none;
  border-radius: 12px;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
  transition: all 0.3s ease;
}

.card-outline.card-primary {
  border-top: 4px solid #007bff;
}

.card-header {
  background-color: transparent;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 1rem 1.25rem;
}

.card-title {
  font-size: 1.1rem;
  color: #333;
}

/* Profile Image Wrapper */
.image-wrapper {
  padding: 6px;
  background-color: #fff;
  border-radius: 50%;
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
  display: inline-block;
}

.profile-user-img {
  width: 130px;
  height: 130px;
  object-fit: cover;
  border-radius: 50%;
  border: 3px solid #fff;
}

/* Form Controls */
.form-control {
  border-radius: 8px;
  padding: 0.65rem 1rem;
  font-size: 0.95rem;
  border: 1px solid #ced4da;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
  border-color: #80bdff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
}

/* Buttons Styling */
.btn {
  border-radius: 8px;
  padding: 0.5rem 1.25rem;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-primary {
  background-color: #007bff;
  border-color: #007bff;
}

.btn-primary:hover {
  background-color: #0056b3;
  border-color: #004085;
}

.btn-success {
  background-color: #28a745;
  border-color: #28a745;
}

.btn-success:hover {
  background-color: #218838;
}

/* Text & Typography */
.profile-username {
  font-size: 1.25rem;
  color: #2c3e50;
}
</style>