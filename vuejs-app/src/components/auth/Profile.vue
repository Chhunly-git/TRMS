<template>
  <div class="content-wrapper" style="min-height: 1000px; background-color: #f4f6f9;">
    <!-- Content Header -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Profile (ព័ត៌មានគណនី)</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item">
                <router-link :to="{ name: 'dashboard' }">Home</router-link>
              </li>
              <li class="breadcrumb-item active">Profile</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          
          <!-- Left Column: Profile Image & Info -->
          <div class="col-md-4">
            <div class="card card-primary card-outline shadow-sm">
              <div class="card-body box-profile text-center pt-4 pb-4">
                
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

                <div class="mb-4">
                  <input 
                    @change="onChangeImage" 
                    type="file" 
                    ref="fileInputRef" 
                    class="d-none"
                    accept=".jpg, .jpeg, .png"
                  />
                  <button @click="$refs.fileInputRef.click()" class="btn btn-primary btn-sm mx-1" title="Upload Image">
                    <i class="fas fa-upload"></i>
                  </button>
                  <button @click="onDeleteImage()" class="btn btn-danger btn-sm mx-1" title="Delete Image">
                    <i class="fas fa-trash"></i>
                  </button>
                  <button @click="onResetImage()" class="btn btn-secondary btn-sm mx-1" title="Reset Image">
                    <i class="fas fa-undo-alt"></i>
                  </button>
                  <button v-if="imageChanged" @click="saveProfileImage()" class="btn btn-success btn-sm mx-1" title="Save Image">
                    <i class="fas fa-check"></i>
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

          <!-- Right Column: Tabs (Info & Settings) -->
          <div class="col-md-8">
            <div class="card shadow-sm">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item">
                    <a class="nav-link font-weight-bold" :class="{ active: activeTab === 'info' }" @click.prevent="activeTab = 'info'" href="#">
                      <i class="fas fa-user mr-1"></i> ព័ត៌មានផ្ទាល់ខ្លួន
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link font-weight-bold" :class="{ active: activeTab === 'password' }" @click.prevent="activeTab = 'password'" href="#">
                      <i class="fas fa-lock mr-1"></i> ប្ដូរពាក្យសម្ងាត់
                    </a>
                  </li>
                </ul>
              </div>
              
              <div class="card-body p-4">
                <div class="tab-content">
                  
                  <!-- TAB 1: ព័ត៌មានផ្ទាល់ខ្លួន (Personal Info) -->
                  <div class="tab-pane" :class="{ active: activeTab === 'info' }">
                    <div class="row">
                      
                      <div class="col-md-6 mb-3">
                        <strong class="text-muted"><i class="fas fa-id-badge mr-1"></i> អត្តលេខមន្ត្រី</strong>
                        <p class="text-dark mb-0 mt-1 font-weight-500">{{ userStore.employee_code || userStore.user?.employee_code || '---' }}</p>
                        <hr class="mt-2 mb-2">
                      </div>

                      <div class="col-md-6 mb-3">
                        <strong class="text-muted"><i class="fas fa-user-tie mr-1"></i> ប្រភេទមន្ត្រី</strong>
                        <p class="text-dark mb-0 mt-1 font-weight-500">{{ formatEmployeeType(userStore.employee_type || userStore.user?.employee_type) }}</p>
                        <hr class="mt-2 mb-2">
                      </div>

                      <div class="col-md-6 mb-3">
                        <strong class="text-muted"><i class="fas fa-venus-mars mr-1"></i> ភេទ</strong>
                        <p class="text-dark mb-0 mt-1 font-weight-500">{{ formatGender(userStore.gender || userStore.user?.gender) }}</p>
                        <hr class="mt-2 mb-2">
                      </div>

                      <div class="col-md-6 mb-3">
                        <strong class="text-muted"><i class="fas fa-ring mr-1"></i> ស្ថានភាពគ្រួសារ</strong>
                        <p class="text-dark mb-0 mt-1 font-weight-500">{{ formatMaritalStatus(userStore.marital_status || userStore.user?.marital_status) }}</p>
                        <hr class="mt-2 mb-2">
                      </div>

                      <div class="col-md-6 mb-3">
                        <strong class="text-muted"><i class="fas fa-calendar-alt mr-1"></i> ថ្ងៃខែឆ្នាំកំណើត</strong>
                        <p class="text-dark mb-0 mt-1 font-weight-500">{{ formatDate(userStore.dob || userStore.user?.dob) }}</p>
                        <hr class="mt-2 mb-2">
                      </div>

                      <div class="col-md-6 mb-3">
                        <strong class="text-muted"><i class="fas fa-phone mr-1"></i> លេខទូរស័ព្ទ</strong>
                        <p class="text-dark mb-0 mt-1 font-weight-500">{{ userStore.phone || userStore.user?.phone || '---' }}</p>
                        <hr class="mt-2 mb-2">
                      </div>

                      <div class="col-md-12 mb-3">
                        <strong class="text-muted"><i class="fas fa-map-marked-alt mr-1"></i> ទីកន្លែងកំណើត</strong>
                        <p class="text-dark mb-0 mt-1 font-weight-500">{{ userStore.birth_place || userStore.user?.birth_place || '---' }}</p>
                        <hr class="mt-2 mb-2">
                      </div>

                      <!-- ព័ត៌មានអត្តសញ្ញាណប័ណ្ណ និង លិខិតឆ្លងដែន -->
                      <div class="col-md-6 mb-3">
                        <strong class="text-muted"><i class="fas fa-id-card mr-1"></i> លេខអត្តសញ្ញាណប័ណ្ណ</strong>
                        <p class="text-dark mb-0 mt-1 font-weight-500">{{ userStore.national_id_number || userStore.user?.national_id_number || '---' }}</p>
                        <hr class="mt-2 mb-2">
                      </div>
                      
                      <div class="col-md-6 mb-3">
                        <strong class="text-muted"><i class="fas fa-calendar-times mr-1"></i> ថ្ងៃផុតកំណត់ (អ.ត.ស)</strong>
                        <p class="text-dark mb-0 mt-1 font-weight-500">{{ formatDate(userStore.national_id_expired_date || userStore.user?.national_id_expired_date) }}</p>
                        <hr class="mt-2 mb-2">
                      </div>

                      <div class="col-md-6 mb-3">
                        <strong class="text-muted"><i class="fas fa-passport mr-1"></i> លេខលិខិតឆ្លងដែន</strong>
                        <p class="text-dark mb-0 mt-1 font-weight-500">{{ userStore.passport_number || userStore.user?.passport_number || '---' }}</p>
                        <hr class="mt-2 mb-2">
                      </div>

                      <div class="col-md-6 mb-3">
                        <strong class="text-muted"><i class="fas fa-credit-card mr-1"></i> លេខកាត MEF</strong>
                        <p class="text-dark mb-0 mt-1 font-weight-500">{{ userStore.mef_card_number || userStore.user?.mef_card_number || '---' }}</p>
                        <hr class="mt-2 mb-2">
                      </div>

                      <!-- ព័ត៌មានទីកន្លែងការងារ និងអាសយដ្ឋាន -->
                      <div class="col-md-6 mb-3">
                        <strong class="text-muted"><i class="fas fa-building mr-1"></i> នាយកដ្ឋាន</strong>
                        <p class="text-dark mb-0 mt-1 font-weight-500">{{ userStore.department_name || userStore.user?.department_name || '---' }}</p>
                        <hr class="mt-2 mb-2">
                      </div>

                      <div class="col-md-6 mb-3">
                        <strong class="text-muted"><i class="fas fa-door-open mr-1"></i> ការិយាល័យ</strong>
                        <p class="text-dark mb-0 mt-1 font-weight-500">{{ userStore.office_name || userStore.user?.office_name || '---' }}</p>
                        <hr class="mt-2 mb-2">
                      </div>

                      <div class="col-md-12 mb-3">
                        <strong class="text-muted"><i class="fas fa-map-marker-alt mr-1"></i> អាសយដ្ឋានបច្ចុប្បន្ន</strong>
                        <p class="text-dark mb-0 mt-1 font-weight-500">{{ userStore.current_address || userStore.user?.current_address || '---' }}</p>
                        <hr class="mt-2 mb-2">
                      </div>

                    </div>
                  </div>

                  <!-- TAB 2: ប្ដូរពាក្យសម្ងាត់ (Password Settings) -->
                  <div class="tab-pane" :class="{ active: activeTab === 'password' }">
                    <form @submit.prevent="savePassword" class="form-horizontal">
                      <div v-if="!userStore.password_null" class="form-group row align-items-center">
                        <label class="col-sm-4 col-form-label font-weight-bold text-dark">Current Password</label>
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
                        <label class="col-sm-4 col-form-label font-weight-bold text-dark">New Password</label>
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
                        <label class="col-sm-4 col-form-label font-weight-bold text-dark">Confirm Password</label>
                        <div class="col-sm-8">
                          <input 
                            v-model="user.new_password_confirmation" 
                            type="password" 
                            class="form-control"
                            placeholder="Confirm Password" 
                          />
                        </div>
                      </div>

                      <div class="form-group row mt-4">
                        <div class="col-sm-8 offset-sm-4">
                          <button type="button" @click="resetAllState" class="btn btn-danger mr-2 px-4">Cancel</button>
                          <button type="submit" class="btn btn-outline-primary px-4">Save Password</button>
                        </div>
                      </div>
                    </form>
                  </div>

                </div>
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
import { computed, reactive, ref, watch, onMounted } from "vue"; 
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

// --- State សម្រាប់បញ្ជា Tab ---
const activeTab = ref('info');

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
// FORMATTERS (បំប្លែងទិន្នន័យទៅជាភាសាខ្មែរ/កាត់ម៉ោង)
// ==========================================

// កាត់យកតែថ្ងៃខែឆ្នាំចេញពីទម្រង់ YYYY-MM-DDTHH:MM:SSZ
const formatDate = (dateString) => {
  if (!dateString) return '---';
  return String(dateString).split('T')[0];
};

const formatGender = (gender) => {
  if (!gender) return '---';
  const g = String(gender).trim().toUpperCase();
  if (g === 'MALE' || g === 'M') return 'ប្រុស';
  if (g === 'FEMALE' || g === 'F') return 'ស្រី';
  return gender;
};

const formatMaritalStatus = (status) => {
  if (!status) return '---';
  const s = String(status).trim().toUpperCase();
  if (s === 'SINGLE') return 'នៅលីវ';
  if (s === 'MARRIED') return 'រៀបការរួច';
  if (s === 'DIVORCED') return 'លែងលះ';
  return status;
};

const formatEmployeeType = (type) => {
  if (!type) return '---';
  const t = String(type).trim().toUpperCase();
  if (t === 'CIVIL_SERVICE') return 'មន្ត្រីរាជការស៊ីវិល';
  if (t === 'STATUTORY') return 'មន្ត្រីលក្ខន្តិកៈ';
  if (t === 'CONTRACT') return 'មន្ត្រីកិច្ចសន្យា';
  if (t === 'OTHER') return 'ផ្សេងៗ';
  return type;
};

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

const profileImage = computed(() => userStore.profile_image);
watch(
  () => profileImage.value,
  (nv) => (tempImage.value = nv ?? emptyImage),
  { immediate: true }
);

const imageChanged = computed(
  () => tempImage.value !== (profileImage.value ?? emptyImage)
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
.content-wrapper {
  font-family: 'Battambang', cursive, sans-serif !important;
}

.card-outline.card-primary {
  border-top: 3px solid #007bff;
}

.image-wrapper {
  padding: 5px;
  background-color: #fff;
  border-radius: 50%;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.nav-pills .nav-link {
  color: #495057;
  border-radius: 0;
}

.nav-pills .nav-link.active {
  background-color: transparent;
  color: #007bff;
  border-bottom: 3px solid #007bff;
}

.font-weight-500 {
  font-weight: 500;
}

.form-control {
  border-radius: 4px;
}

.btn-outline-primary {
  color: #007bff;
  border-color: #007bff;
  background-color: transparent;
}

.btn-outline-primary:hover {
  color: #fff;
  background-color: #007bff;
}
</style>