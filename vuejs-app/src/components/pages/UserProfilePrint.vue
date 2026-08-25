<template>
  <div class="content-wrapper print-page" style="background-color: #f4f6f9; min-height: 1000px;">
    <!-- Action buttons (លាក់ពេលព្រីនចេញ) -->
    <section class="content-header print-hide">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark font-weight-bold page-title">បោះពុម្ពប្រវត្តិរូបមន្ត្រី</h1>
          </div>
          <div class="col-sm-6 text-right">
            <button @click="printProfile" class="btn btn-primary px-4 shadow-sm">
              <i class="fas fa-print mr-1"></i> បោះពុម្ព (Print A4)
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Printable A4 Area -->
    <section class="content">
      <div class="container-fluid d-flex justify-content-center">
        <div class="a4-sheet bg-white p-5 shadow-sm">
          <!-- Header Section -->
          <div class="row align-items-start mb-2">
            <div class="col-3 text-center">
              <img :src="ministryLogo" alt="Logo" class="ministry-logo mb-1" />
              <div class="tr-title">និយ័តករបរធនបាលកិច្ច</div>
              <div class="ga-title">នាយកដ្ឋានកិច្ចការទូទៅ</div>
            </div>
            <div class="col-6 text-center" style="top: -20px;">
              <div class="kingdom-title">ព្រះរាជាណាចក្រកម្ពុជា</div>
              <div class="kingdom-title">ជាតិ សាសនា ព្រះមហាក្សត្រ</div>
            </div>
            <div class="col-3 text-center">
              <!-- Profile Photo -->
              <img :src="userStore.profile_image || emptyImage" alt="Profile" class="profile-photo border" />
            </div>
          </div>

          <!-- Title Section -->
          <div class="text-center mb-3">
            <h4 class="main-doc-title">ប្រវត្តិរូបសង្ខេប</h4>
          </div>

          <!-- Section 1: Personal Info -->
          <div class="section-title mb-3">១. ព័ត៌មានផ្ទាល់ខ្លួន</div>

          <div class="profile-info-grid">
            <div class="row mb-2" v-if="userStore?.employee_type === 'CIVIL_SERVICE'">
              <div class="col-md-4"><span class="label-title">អត្តលេខមន្ត្រីរាជការ</span>: <span class="label-value">{{ userStore.employee_code || '---' }}</span></div>
              <div class="col-md-4"><span class="label-title">លេខប័ណ្ណសម្គាល់មន្ត្រី</span>: <span class="label-value">{{ userStore.mef_card_number || '---' }}</span></div>
            </div>

            <div class="row mb-2">
              <div class="col-md-6"><span class="label-title">គោត្តនាម និងនាម</span>: <span class="label-value">{{ userStore.name_kh || userStore.name || '---' }}</span></div>
              <div class="col-md-6"><span class="label-title">អក្សរឡាតាំង</span>: <span class="label-value text-uppercase">{{ userStore.name_en || '---' }}</span></div>
            </div>

            <div class="row mb-2">
              <div class="col-md-4"><span class="label-title">ភេទ</span>: <span class="label-value">{{ formatGender(userStore.gender) }}</span></div>
              <div class="col-md-4"><span class="label-title">ថ្ងៃខែឆ្នាំកំណើត</span>: <span class="label-value">{{ formatDate(userStore.dob) }}</span></div>
              <div class="col-md-4"><span class="label-title">ស្ថានភាពគ្រួសារ</span>: <span class="label-value">{{ formatMaritalStatus(userStore.marital_status) }}</span></div>
            </div>

            <div class="row mb-2">
              <div class="col-12"><span class="label-title">ទីកន្លែងកំណើត</span>: <span class="label-value">{{ userStore.birth_place || '---' }}</span></div>
            </div>

            <div class="row mb-2">
              <div class="col-12"><span class="label-title">អាសយដ្ឋានបច្ចុប្បន្ន</span>: <span class="label-value">{{ userStore.current_address || '---' }}</span></div>
            </div>

            <div class="row mb-2">
              <div class="col-12"><span class="label-title">អ៊ីមែល</span>: <span class="label-value">{{ userStore.email || '---' }}</span></div>
            </div>

            <div class="row mb-2">
              <div class="col-12"><span class="label-title">លេខទូរសព្ទ</span>: <span class="label-value">{{ userStore.phone || '---' }}</span></div>
            </div>

            <div class="row mb-2">
              <div class="col-6"><span class="label-title">អត្តសញ្ញាណប័ណ្ណ</span>: <span class="label-value">{{ userStore.national_id_number || '---' }}</span></div>
              <div class="col-6"><span class="label-title">កាលបរិច្ឆេទផុតកំណត់</span>: <span class="label-value">{{ formatDate(userStore.national_id_expired_date) }}</span></div>
            </div>

            <div class="row mb-2">
              <div class="col-6"><span class="label-title">លិខិតឆ្លងដែន</span>: <span class="label-value">{{ userStore.passport_number || '---' }}</span></div>
              <div class="col-6"><span class="label-title">កាលបរិច្ឆេទផុតកំណត់</span>: <span class="label-value">{{ formatDate(userStore.passport_expired_date) }}</span></div>
            </div>
          </div>

          <!-- Section 2: Career / Function Status -->
          <div class="section-title">២. ព័ត៌មានអំពីស្ថានភាពមុខងារ</div>
          <div class="section-title mb-3 ml-3">ក. ចូលបម្រើការងារដំបូង</div>
          
          <div class="row mb-2">
            <div class="col-md-3"><span class="label-title">កាលបរិច្ឆេទចូលបម្រើការងារដំបូង</span></div>
            <div class="col-md-3"> : <span class="label-value">{{ userStore.first_service_date || '---' }}</span></div>
            <div class="col-md-2"><span class="label-title">កាលបរិច្ឆេទតាំងស៊ុប់</span></div>
            <div class="col-md-3"> : <span class="label-value">{{ userStore.first_appointment_date || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-md-3"><span class="label-title">ក្របខណ្ឌ ឋានន្តរស័ក្តិ និងថ្នាក់</span></div>
            <div class="col-md-3">: <span class="label-value text-uppercase">{{ userStore.initial_framework || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-md-3"><span class="label-title">មុខតំណែង</span></div>
            <div class="col-md-3">: <span class="label-value text-uppercase">{{ userStore.initial_position || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-md-3"><span class="label-title">ក្រសួង/ស្ថាប័ន</span></div>
            <div class="col-md-9">: <span class="label-value text-uppercase">{{ userStore.initial_ministry || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-md-3"><span class="label-title">អង្គភាព</span></div>
            <div class="col-md-9">: <span class="label-value text-uppercase">{{ userStore.initial_unit || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-md-3"><span class="label-title">នាយកដ្ឋាន/អង្គភាព/មន្ទីរ</span></div>
            <div class="col-md-3">: <span class="label-value text-uppercase">{{ userStore.initial_department || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-md-3"><span class="label-title">ការិយាល័យ</span></div>
            <div class="col-md-3">: <span class="label-value text-uppercase">{{ userStore.initial_office || '---' }}</span></div>
          </div>

          <div class="section-title mb-3 ml-3">ខ. ស្ថានភាពមុខងារបច្ចុប្បន្ន</div>
          
          <div class="row mb-2">
            <div class="col-md-3"><span class="label-title">ក្របខណ្ឌ ឋានន្តរស័ក្តិ និងថ្នាក់</span></div>
            <div class="col-md-2"> : <span class="label-value">{{ userStore.current_framework || '---' }}</span></div>
            <div class="col-md-5"><span class="label-title">កាលបរិច្ឆេទប្តូរក្របខណ្ឌ ឋានន្តរស័ក្តិ និងថ្នាក់ចុងក្រោយ</span></div>
            <div class="col-md-2"> : <span class="label-value">{{ userStore.current_appointment_date || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-md-4"><span class="label-title">មុខតំណែង</span></div>
            <div class="col-md-3">: <span class="label-value text-uppercase">{{ userStore.position?.title_kh || userStore.position?.name || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-md-4"><span class="label-title">កាលបរិច្ឆេទទទួលមុខតំណែងចុងក្រោយ</span></div>
            <div class="col-md-3">: <span class="label-value text-uppercase">{{ userStore.current_position_date || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-md-4"><span class="label-title">នាយកដ្ឋាន</span></div>
            <div class="col-md-3">: <span class="label-value text-uppercase">{{ userStore.department?.name_kh || userStore.department?.name || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-md-4"><span class="label-title">ការិយាល័យ</span></div>
            <div class="col-md-3">: <span class="label-value text-uppercase">{{ userStore.office?.name_kh || userStore.office?.name || '---' }}</span></div>
          </div>

          <div class="section-title mb-3 ml-3" v-if="userStore.employee_type === 'CIVIL_SERVICE'">គ. តួនាទីបន្ថែមលើមុខងារបច្ចុប្បន្ន</div>
          <div class="section-title mb-3 ml-3" v-if="userStore.employee_type === 'CIVIL_SERVICE'">ឃ. ស្ថានភាពស្ថិតនៅក្រៅក្របខណ្ឌដើម</div>
          <div class="section-title mb-3 ml-3" v-if="userStore.employee_type === 'CIVIL_SERVICE'">ង. ស្ថានភាពស្ថិតនៅក្នុងភាពទំនេរគ្មានបៀវត្ស</div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useUserStore } from "@/stores/user";
import { apiReadUser, apiGetMyProfile } from "@/functions/api/user";
import emptyImage from "@/assets/images/emptyImage.png";
import ministryLogo from "@/assets/images/logoImage.webp";

const route = useRoute();
const userStore = useUserStore();

onMounted(async () => {
  try {
    const userId = route.params.id; 
    let response;

    if (userId) {
      response = await apiReadUser(userId);
    } else {
      response = await apiGetMyProfile();
    }

    const userData = response.data.user || response.data.data || response.data;
    
    if (userData && typeof userStore.setState === 'function') {
      userStore.setState(userData);
    }
  } catch (error) {
    console.error("មិនអាចទាញយកទិន្នន័យ Profile បានឡើយ:", error);
  }
});

const formatDate = (dateString) => {
  if (!dateString) return '---';
  return String(dateString).split('T')[0];
};

const formatGender = (gender) => {
  if (!gender) return '---';
  const g = String(gender).trim().toLowerCase();
  if (g === 'male' || g === 'm') return 'ប្រុស';
  if (g === 'female' || g === 'f') return 'ស្រី';
  return gender;
};

const formatMaritalStatus = (status) => {
  if (!status) return '---';
  const s = String(status).trim().toLowerCase();
  if (s === 'single') return 'នៅលីវ';
  if (s === 'married') return 'រៀបការរួច';
  if (s === 'divorced') return 'លែងលះ';
  if (s === 'widowed') return 'មេម៉ាយ/ពោះម៉ាយ';
  return status;
};

const printProfile = () => {
  window.print();
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@300;400;700&display=swap');

@font-face {
  font-family: 'Khmer OS Moul Light';
  src: url('@/assets/fonts/KhmerOSMoulLight.ttf') format('truetype');
}

@font-face {
  font-family: 'Khmer OS Siemreap';
  src: url('@/assets/fonts/KhmerOSSiemreap.ttf') format('truetype');
}

.print-page,
.content-header {
  font-family: 'Khmer OS Siemreap', sans-serif !important;
  color: #000;
}

.page-title {
  font-family: 'Khmer OS Siemreap', sans-serif !important;
  font-size: 18px;
}

.kingdom-title,
.tr-title,
.ga-title,
.main-doc-title,
.section-title {
  font-family: 'Khmer OS Moul Light', serif !important;
}

.a4-sheet {
  font-family: 'Khmer OS Siemreap', sans-serif !important;
  color: #000;
  width: 210mm;
  min-height: 297mm;
  padding: 15mm 20mm;
  margin: 0 auto;
  background: white;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  box-sizing: border-box;
}

.tr-title {
  font-size: 12px;
  line-height: 1.3;
}

.ga-title {
  font-size: 12px;
  line-height: 1.3;
}

.kingdom-title {
  font-size: 16px;
  line-height: 1.5;
}

.main-doc-title {
  font-size: 14px;
  margin-bottom: 2px;
}

.ministry-logo {
  width: 80px;
  height: 80px;
  object-fit: contain;
}

.profile-photo {
  width: 95px;
  height: 115px;
  object-fit: cover;
  border-radius: 2px;
}

.section-title {
  font-size: 12px;
  padding-bottom: 2px;
  margin-top: 15px;
}

.label-title {
  font-size: 12px;
  font-weight: bold;
}

.label-value {
  font-size: 12px;
}

@media print {
  .print-hide {
    display: none !important;
  }

  .content-wrapper,
  body,
  html {
    background-color: white !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .a4-sheet {
    width: 100% !important;
    min-height: 100vh !important;
    box-shadow: none !important;
    margin: 0 !important;
    padding: 15mm 15mm !important;
  }

  @page {
    size: A4 portrait;
    margin: 0mm;
  }
}
</style>