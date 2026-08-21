<template>
  <div class="content-wrapper" style="min-height: 1175px;">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 font-weight-bold" style="color: #112d26;">Dashboard (ផ្ទាំងសង្ខេបព័ត៌មាន)</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item">
                <router-link :to="{ name: 'dashboard' }">Home</router-link>
              </li>
              <li class="breadcrumb-item active">Dashboard</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container-fluid">
        
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
          <i class="fas fa-spinner fa-spin fa-3x text-success"></i>
          <p class="mt-2">កំពុងទាញយកទិន្នន័យ...</p>
        </div>

        <div v-else>
          <!-- Statistics Row -->
          <div class="row">
            <div class="col-lg-3 col-6">
              <div class="small-box bg-info rounded-lg shadow-sm">
                <div class="inner">
                  <h3>{{ stats.totalEmployees || 0 }}</h3>
                  <p>មន្ត្រីសរុប</p>
                </div>
                <div class="icon">
                  <i class="fas fa-users"></i>
                </div>
                <router-link to="/users" class="small-box-footer">
                  មើលលម្អិត <i class="fas fa-arrow-circle-right"></i>
                </router-link>
              </div>
            </div>
            
            <div class="col-lg-3 col-6">
              <div class="small-box bg-success rounded-lg shadow-sm">
                <div class="inner">
                  <h3>{{ stats.civilServants || 0 }}</h3>
                  <p>មន្រ្តីមុខងារសាធារណៈ</p>
                </div>
                <div class="icon">
                  <i class="fas fa-building"></i>
                </div>
                <router-link to="/users" class="small-box-footer">
                  មើលលម្អិត <i class="fas fa-arrow-circle-right"></i>
                </router-link>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box bg-warning rounded-lg shadow-sm">
                <div class="inner text-white">
                  <h3>{{ stats.statutory || 0 }}</h3>
                  <p>មន្រ្តីលក្ខន្តិកៈ</p>
                </div>
                <div class="icon">
                  <i class="fas fa-user-tie"></i>
                </div>
                <router-link to="/users" class="small-box-footer" style="color: white !important;">
                  មើលលម្អិត <i class="fas fa-arrow-circle-right"></i>
                </router-link>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box bg-danger rounded-lg shadow-sm">
                <div class="inner">
                  <h3>{{ stats.contractStaff || 0 }}</h3>
                  <p>មន្ត្រីកិច្ចសន្យា</p>
                </div>
                <div class="icon">
                  <i class="fas fa-file-signature"></i>
                </div>
                <a href="#" class="small-box-footer">
                  មើលលម្អិត <i class="fas fa-arrow-circle-right"></i>
                </a>
              </div>
            </div>
          </div>

          <!-- Birthday and Info Row -->
          <div class="row mt-3">
            <div class="col-md-6">
              <div class="card card-outline card-primary shadow-sm rounded-lg border-top-3 border-primary">
                <div class="card-header bg-white">
                  <h3 class="card-title font-weight-bold">
                    <i class="fas fa-birthday-cake text-warning mr-2"></i>
                    ខួបកំណើតមន្ត្រីក្នុងខែនេះ
                  </h3>
                  <div class="card-tools">
                    <span class="badge badge-danger">{{ birthdays.length }} នាក់</span>
                  </div>
                </div>
                
                <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                  <ul class="products-list product-list-in-card pl-3 pr-3">
                    <li v-if="birthdays.length === 0" class="item text-center py-4 text-muted">
                      មិនមានមន្ត្រីដែលមានខួបកំណើតក្នុងខែនេះទេ
                    </li>

                    <li v-for="user in birthdays" :key="user.id" class="item align-items-center d-flex">
                      <div class="product-img">
                        <img :src="getFullImageUrl(user.profile_thumbnail || user.profile_image)" alt="User Image" class="img-circle border" style="width: 50px; height: 50px; object-fit: cover;">
                      </div>
                      <div class="product-info ml-3 flex-grow-1">
                        <span class="product-title font-weight-bold">
                          {{ user.name_kh }}
                          <span v-if="user.is_today" class="badge badge-danger float-right blink-text">ថ្ងៃនេះ 🎂</span>
                          <span v-else class="badge badge-info float-right">{{ user.dob_formatted }}</span>
                        </span>
                        <span class="product-description text-muted">
                          {{ user.position_name }} | ទើបមានអាយុ {{ user.age }} ឆ្នាំ
                        </span>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card widget-user shadow-sm rounded-lg">
                <div class="widget-user-header text-white" style="background: url('https://adminlte.io/themes/v3/dist/img/photo1.png') center center;">
                  <h3 class="widget-user-username text-right font-weight-bold">{{ userStore.user?.name || 'Administrator' }}</h3>
                  <h5 class="widget-user-desc text-right">{{ userStore.user?.level || 'គ្រប់គ្រងប្រព័ន្ធ' }}</h5>
                </div>
                <div class="widget-user-image">
                  <img class="img-circle border-white" :src="getFullImageUrl(userStore.user?.profile_image)" @error="onImageError" alt="User Avatar">
                </div>
                <div class="card-footer bg-white pt-5">
                  <div class="row">
                    <div class="col-sm-12 text-center">
                      <h5 class="text-muted mb-3">សូមស្វាគមន៍មកកាន់ប្រព័ន្ធគ្រប់គ្រងទិន្នន័យមន្ត្រី!</h5>
                      <p class="text-sm">អ្នកអាចប្រើប្រាស់ម៉ឺនុយខាងឆ្វេងដើម្បីចូលទៅកាន់មុខងារនានា ដូចជាការគ្រប់គ្រងបុគ្គលិក នាយកដ្ឋាន ការិយាល័យ និងតួនាទី។</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useUserStore } from "@/stores/user";
import { apiGetDashboardStats } from '@/functions/api/dashboard';

const userStore = useUserStore();
const defaultAvatar = 'https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg';

const loading = ref(true);
const stats = ref({});
const birthdays = ref([]);

const getFullImageUrl = (path) => {
  if (!path) return defaultAvatar;
  if (path.startsWith('http')) return path;
  
  const backendBase = (import.meta.env.VITE_APP_API_URL || 'http://localhost:8000').replace(/\/api\/?$/, '');
  let cleanPath = path.replace(/^\//, '');
  if (!cleanPath.startsWith('storage/')) cleanPath = `storage/${cleanPath}`;
  
  return `${backendBase}/${cleanPath}`;
};

const onImageError = (event) => {
  event.target.src = defaultAvatar;
};

const fetchDashboardData = async () => {
  loading.value = true;
  try {
    const res = await apiGetDashboardStats();
    stats.value = res.data.stats || {};
    birthdays.value = res.data.birthdays || [];
  } catch (error) {
    console.error("Error fetching dashboard stats:", error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDashboardData();
});
</script>

<style scoped>
.content-wrapper {
  font-family: 'Battambang', cursive, sans-serif !important;
  background-color: #f4f6f9;
}
.rounded-lg { border-radius: 12px !important; }
.small-box { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.small-box:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.1) !important; }
.border-top-3 { border-top-width: 3px !important; }
.blink-text { animation: blinker 1.5s linear infinite; }
@keyframes blinker { 50% { opacity: 0.5; } }
</style>