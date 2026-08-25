<template>
  <div class="content-wrapper" style="min-height: 1000px; background-color: #f4f6f9; padding-bottom: 40px;">
    
    <!-- Cover Banner Header -->
    <div class="profile-cover-banner position-relative">
      <div class="container-fluid d-flex justify-content-between align-items-center py-4 px-4">
        <h1 class="text-white font-weight-bold m-0" style="font-size: 20px;">
          <i class="fas id-badge mr-2"></i>ព័ត៌មានប្រវត្តិរូបផ្ទាល់ខ្លួន
        </h1>
        <!-- <router-link :to="{ name: 'user.detail', params: { id: userStore.id } }" class="btn btn-light btn-sm px-3 shadow-sm font-weight-bold">
          <i class="fas fa-print text-primary mr-1"></i> បោះពុម្ពប្រវត្តិរូប (Print)
        </router-link> -->
      </div>
    </div>

    <!-- Main Content -->
    <section class="content px-3" style="margin-top: -30px;">
      <div class="container-fluid">
        <div class="row">
          
          <!-- ផ្នែកខាងឆ្វេង៖ Profile Card ទំនើប (បន្ថែម d-flex flex-column) -->
          <div class="col-lg-4 d-flex flex-column">
            <div class="card border-0 shadow-sm rounded-lg text-center p-4 bg-white mb-4 flex-fill d-flex flex-column justify-content-between profile-card-left">
              <div>
                <div class="position-relative d-inline-block mx-auto mb-3 mt-2">
                  <img 
                    class="rounded-circle shadow border border-white" 
                    :src="userStore.profile_image || emptyImage" 
                    alt="Profile"
                    style="width: 130px; height: 130px; object-fit: cover; border-width: 4px !important;"
                  >
                  <span class="position-absolute bottom-0 right-0 p-2 bg-success border border-white rounded-circle"></span>
                </div>
                
                <h4 class="font-weight-bold text-dark mb-1">{{ userStore.name_kh || userStore.name || '---' }}</h4>
                <p class="text-muted small mb-2">{{ userStore.name_en || '---' }}</p>
                
                <div class="mb-3">
                  <span class="badge badge-primary px-3 py-2 rounded-pill font-weight-normal" style="font-size: 12px;">
                    {{ userStore.position?.title_kh || userStore.position?.name || 'មន្ត្រីរាជការ' }}
                  </span>
                </div>
              </div>

              <!-- ផ្នែកខាងក្រោមនៃកាតខាងឆ្វេង នឹងរុញមកក្រោមស្មើគ្នា -->
              <div class="border-top pt-3 text-left mt-auto">
                <div class="d-flex justify-content-between mb-2 small">
                  <span class="text-muted text-nowrap mr-2"><i class="fas fa-id-card mr-1 text-primary"></i> អត្តលេខ:</span>
                  <strong class="text-dark text-right">{{ userStore.employee_code || '---' }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                  <span class="text-muted text-nowrap mr-2"><i class="fas fa-building mr-1 text-primary"></i> នាយកដ្ឋាន:</span>
                  <strong class="text-dark text-right text-truncate-custom">{{ userStore.department?.name_kh || '---' }}</strong>
                </div>
                <div class="d-flex justify-content-between small">
                  <span class="text-muted text-nowrap mr-2"><i class="fas fa-envelope mr-1 text-primary"></i> អ៊ីមែល:</span>
                  <strong class="text-dark text-right text-truncate-custom">{{ userStore.email || '---' }}</strong>
                </div>
              </div>
            </div>
          </div>

          <!-- ផ្នែកខាងស្តាំ៖ ព័ត៌មានលម្អិតបែប Modern Section Cards -->
          <div class="col-lg-8">
            
            <!-- Card 1: ព័ត៌មានផ្ទាល់ខ្លួន -->
            <div class="card border-0 shadow-sm rounded-lg bg-white mb-4">
              <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="font-weight-bold text-primary m-0 d-flex align-items-center">
                  <div class="icon-circle bg-light-primary text-primary mr-2">
                    <i class="fas fa-user"></i>
                  </div>
                  ១. ព័ត៌មានផ្ទាល់ខ្លួន
                </h5>
              </div>
              <div class="card-body px-4 py-3">
                <div class="row" v-if="userStore.employee_type === 'CIVIL_SERVICE'">
                  <div class="col-md-6 mb-3">
                    <span class="text-muted small d-block">អត្តលេខមន្ត្រីរាជការ</span>
                    <strong class="text-dark">{{ userStore.employee_code || '---' }}</strong>
                  </div>
                  <div class="col-md-6 mb-3">
                    <span class="text-muted small d-block">លេខប័ណ្ណសម្គាល់មន្ត្រី</span>
                    <strong class="text-dark">{{ userStore.mef_card_number || '---' }}</strong>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block">ភេទ</span>
                    <strong class="text-dark">{{ formatGender(userStore.gender) }}</strong>
                  </div>
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block">ថ្ងៃខែឆ្នាំកំណើត</span>
                    <strong class="text-dark">{{ formatDate(userStore.dob) }}</strong>
                  </div>
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block">ស្ថានភាពគ្រួសារ</span>
                    <strong class="text-dark">{{ formatMaritalStatus(userStore.marital_status) }}</strong>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12 mb-3">
                    <span class="text-muted small d-block">ទីកន្លែងកំណើត</span>
                    <strong class="text-dark">{{ userStore.birth_place || '---' }}</strong>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12 mb-3">
                    <span class="text-muted small d-block">អាសយដ្ឋានបច្ចុប្បន្ន</span>
                    <strong class="text-dark">{{ userStore.current_address || '---' }}</strong>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-2">
                    <span class="text-muted small d-block">អ៊ីមែល</span>
                    <strong class="text-dark">{{ userStore.email || '---' }}</strong>
                  </div>
                  <div class="col-md-6 mb-2">
                    <span class="text-muted small d-block">លេខទូរសព្ទ</span>
                    <strong class="text-dark">{{ userStore.phone || '---' }}</strong>
                  </div>
                </div>
              </div>
            </div>

            <!-- Card 2: ព័ត៌មានស្ថានភាពមុខងារ -->
            <div class="card border-0 shadow-sm rounded-lg bg-white mb-4">
              <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="font-weight-bold text-primary m-0 d-flex align-items-center">
                  <div class="icon-circle bg-light-primary text-primary mr-2">
                    <i class="fas fa-briefcase"></i>
                  </div>
                  ២. ព័ត៌មានអំពីស្ថានភាពមុខងារ
                </h5>
              </div>
              <div class="card-body px-4 py-3">
                <h6 class="font-weight-bold text-secondary mb-3" style="font-size: 13px;">ក. ចូលបម្រើការងារដំបូង</h6>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <span class="text-muted small d-block">កាលបរិច្ឆេទចូលបម្រើការងារ</span>
                    <strong class="text-dark">{{ userStore.first_service_date || '---' }}</strong>
                  </div>
                  <div class="col-md-6 mb-3">
                    <span class="text-muted small d-block">កាលបរិច្ឆេទតាំងស៊ុប់</span>
                    <strong class="text-dark">{{ userStore.first_appointment_date || '---' }}</strong>
                  </div>
                  <div class="col-12 mb-3">
                    <span class="text-muted small d-block">ក្របខណ្ឌ ឋានន្តរស័ក្តិ និងថ្នាក់</span>
                    <strong class="text-dark text-uppercase">{{ userStore.initial_framework || '---' }}</strong>
                  </div>
                </div>

                <hr class="my-2">

                <h6 class="font-weight-bold text-secondary mb-3 mt-3" style="font-size: 13px;">ខ. ស្ថានភាពមុខងារបច្ចុប្បន្ន</h6>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <span class="text-muted small d-block">នាយកដ្ឋាន</span>
                    <strong class="text-dark text-uppercase">{{ userStore.department?.name_kh || '---' }}</strong>
                  </div>
                  <div class="col-md-6 mb-3">
                    <span class="text-muted small d-block">ការិយាល័យ</span>
                    <strong class="text-dark text-uppercase">{{ userStore.office?.name_kh || '---' }}</strong>
                  </div>
                  <div class="col-12 mb-2">
                    <span class="text-muted small d-block">មុខតំណែង</span>
                    <strong class="text-dark text-uppercase">{{ userStore.position?.title_kh || userStore.position?.name || '---' }}</strong>
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
import { onMounted } from 'vue';
import { useUserStore } from "@/stores/user";
import { apiGetMyProfile } from "@/functions/api/user";
import emptyImage from "@/assets/images/emptyImage.png";

const userStore = useUserStore();

onMounted(async () => {
  try {
    const response = await apiGetMyProfile();
    const userData = response.data.user || response.data.data || response.data;
    if (userData && typeof userStore.setState === 'function') {
      userStore.setState(userData);
    }
  } catch (error) {
    console.error("មិនអាចទាញយកទិន្នន័យ Profile បានឡើយ:", error);
  }
});

const formatDate = (dateString) => !dateString ? '---' : String(dateString).split('T')[0];
const formatGender = (gender) => {
  if (!gender) return '---';
  const g = String(gender).trim().toUpperCase();
  return (g === 'MALE' || g === 'M') ? 'ប្រុស' : (g === 'FEMALE' || g === 'F') ? 'ស្រី' : gender;
};
const formatMaritalStatus = (status) => {
  if (!status) return '---';
  const s = String(status).trim().toUpperCase();
  return s === 'SINGLE' ? 'នៅលីវ' : s === 'MARRIED' ? 'រៀបការរួច' : s === 'DIVORCED' ? 'លែងលះ' : status;
};
</script>
<style scoped>
.content-wrapper {
  font-family: 'Battambang', sans-serif !important;
}

.profile-cover-banner {
  background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
  height: 120px;
  border-radius: 0 0 15px 15px;
}

.rounded-lg {
  border-radius: 14px !important;
}

.icon-circle {
  width: 32px;
  height: 32px;
  background: rgba(0, 123, 255, 0.1);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  font-size: 14px;
}

/* 🟢 កែសម្រួលកាតខាងឆ្វេងឱ្យទូលាយល្មម មិនបាច់បុកគ្នាជាមួយអក្សរវែង */
.profile-card-left {
  word-break: break-word;
  overflow: hidden;
}

/* 🟢 ការពារអក្សរក្នុង Text-truncate មិនឱ្យដាច់ ព្រមទាំងផ្តល់កន្លែងគ្រប់គ្រាន់ */
.text-truncate-custom {
  white-space: normal !important;
  word-break: break-word;
}
</style>