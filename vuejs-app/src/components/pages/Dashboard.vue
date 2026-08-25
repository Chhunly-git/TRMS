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
          <!-- Statistics Row (Users Stats) -->
          <div class="row">
            <div class="col-lg-3 col-6">
              <div class="small-box bg-info rounded-lg shadow-sm">
                <div class="inner">
                  <h3>{{ stats.totalEmployees || 0 }} <small class="text-white">ស្រី {{ stats.fEmployees || 0 }}</small></h3>
                  <p>មន្ត្រីសរុប</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <router-link to="/users" class="small-box-footer">មើលលម្អិត <i class="fas fa-arrow-circle-right"></i></router-link>
              </div>
            </div>
            
            <div class="col-lg-3 col-6">
              <div class="small-box bg-success rounded-lg shadow-sm">
                <div class="inner">
                  <h3>{{ stats.civilServants || 0 }} <small class="text-white">ស្រី {{ stats.fcivilServants || 0 }}</small></h3>
                  <p>មន្រ្តីមុខងារសាធារណៈ</p>
                </div>
                <div class="icon"><i class="fas fa-building"></i></div>
                <router-link to="/users" class="small-box-footer">មើលលម្អិត <i class="fas fa-arrow-circle-right"></i></router-link>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box bg-warning rounded-lg shadow-sm">
                <div class="inner text-white">
                  <h3>{{ stats.statutory || 0 }} <small class="text-white">ស្រី {{ stats.fstatutory || 0 }}</small></h3>
                  <p>មន្រ្តីលក្ខន្តិកៈ</p>
                </div>
                <div class="icon"><i class="fas fa-user-tie"></i></div>
                <router-link to="/users" class="small-box-footer" style="color: white !important;">មើលលម្អិត <i class="fas fa-arrow-circle-right"></i></router-link>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box bg-danger rounded-lg shadow-sm">
                <div class="inner">
                  <h3>{{ stats.contractStaff || 0 }} <small class="text-white">ស្រី {{ stats.fcontractStaff || 0 }}</small></h3>
                  <p>មន្ត្រីជាប់កិច្ចសន្យា</p>
                </div>
                <div class="icon"><i class="fas fa-file-signature"></i></div>
                <router-link to="/users" class="small-box-footer">មើលលម្អិត <i class="fas fa-arrow-circle-right"></i></router-link>
              </div>
            </div>
          </div>

          

          <!-- Attendance Lists Row (តាមទម្រង់ដើម: អវត្តមាន/ច្បាប់, មកយឺត, បេសកកម្ម) -->
         <!-- Date Filter f or Attendance Summary -->
         <div class="row">

            <div class="card-body py-2 px-3">
              <div class="row align-items-center">
                <div class="col-md-4 d-flex align-items-center flex-nowrap">
                  <label class="font-weight-bold mb-0 mr-3 text-secondary">កាលបរិច្ឆេទ:</label>
                  <input type="date" class="form-control" v-model="selectedDate" @change="fetchAttendanceSummary" style="width: 170px;"/>
                </div>
              </div>
            </div>
          
         </div>
        
          <div class="row">
            
            <!-- 1. អវត្តមាន / ច្បាប់ -->
            <div class="col-md-4">
              <div class="card card-outline card-danger shadow-sm rounded-lg border-top-3 border-danger">
                <div class="card-header bg-white">
                  <h3 class="card-title font-weight-bold text-danger"><i class="fas fa-user-times mr-2"></i> អវត្តមាន / ច្បាប់</h3>
                  <div class="card-tools"><span class="badge badge-danger">{{ absentOrPermissionList.length }} នាក់</span></div>
                </div>
                <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                  <ul class="products-list product-list-in-card">
                    <li v-if="absentOrPermissionList.length === 0" class="item text-center py-4 text-muted">មិនមានទិន្នន័យទេ</li>
                    <li v-for="item in absentOrPermissionList" :key="item.id" class="item align-items-center d-flex">
                      <div class="product-img">
                        <img :src="getFullImageUrl(item.user?.profile_thumbnail || item.user?.profile_image)" class="img-circle border" style="width: 45px; height: 45px; object-fit: cover;">
                      </div>
                      <div class="product-info ml-3 flex-grow-1">
                        <span class="product-title font-weight-bold">
                          {{ item.user?.name_kh || item.user?.name }}
                          <span class="badge float-right" :class="item.status === 'PERMISSION' ? 'badge-info' : 'badge-danger'">
                            {{ item.status === 'PERMISSION' ? 'មានច្បាប់' : 'អវត្តមាន' }}
                          </span>
                        </span><br>
                        <span class="product-description text-muted">មូលហេតុ: {{ item.note || '---' }}</span>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- 2. មកយឺត -->
            <div class="col-md-4">
              <div class="card card-outline card-warning shadow-sm rounded-lg border-top-3 border-warning">
                <div class="card-header bg-white">
                  <h3 class="card-title font-weight-bold text-warning"><i class="fas fa-clock mr-2"></i> មកយឺត</h3>
                  <div class="card-tools"><span class="badge badge-warning">{{ lateList.length }} នាក់</span></div>
                </div>
                <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                  <ul class="products-list product-list-in-card">
                    <li v-if="lateList.length === 0" class="item text-center py-4 text-muted">មិនមានមន្ត្រីមកយឺតទេ</li>
                    <li v-for="item in lateList" :key="item.id" class="item align-items-center d-flex">
                      <div class="product-img">
                        <img :src="getFullImageUrl(item.user?.profile_thumbnail || item.user?.profile_image)" class="img-circle border" style="width: 45px; height: 45px; object-fit: cover;">
                      </div>
                      <div class="product-info ml-3 flex-grow-1">
                        <span class="product-title font-weight-bold">{{ item.user?.name_kh || item.user?.name }}</span>
                        <span class="product-description text-muted">ម៉ោងចូល: {{ item.check_in_time || '--:--' }} <br> មូលហេតុ: {{ item.note || '---' }}</span>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- 3. បេសកកម្ម -->
            <div class="col-md-4">
              <div class="card card-outline card-primary shadow-sm rounded-lg border-top-3 border-primary">
                <div class="card-header bg-white">
                  <h3 class="card-title font-weight-bold text-primary"><i class="fas fa-plane-departure mr-2"></i> បេសកកម្ម</h3>
                  <div class="card-tools"><span class="badge badge-primary">{{ missionList.length }} នាក់</span></div>
                </div>
                <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                  <ul class="products-list product-list-in-card ">
                    <li v-if="missionList.length === 0" class="item text-center py-4 text-muted">មិនមានមន្ត្រីជាប់បេសកកម្មទេ</li>
                    <li v-for="item in missionList" :key="item.id" class="item align-items-center d-flex">
                      <div class="product-img">
                        <img :src="getFullImageUrl(item.user?.profile_thumbnail || item.user?.profile_image)" class="img-circle border" style="width: 45px; height: 45px; object-fit: cover;">
                      </div>
                      <div class="product-info ml-3 flex-grow-1">
                        <span class="product-title font-weight-bold">{{ item.user?.name_kh || item.user?.name }}</span>
                        <span class="product-description text-muted">ទីកន្លែង/ភារកិច្ច: {{ item.note || '---' }}</span>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

          </div>

          <!-- Birthday Row -->
          <div class="row mt-3">
            <div class="col-md-12">
              <div class="card card-outline card-primary shadow-sm rounded-lg border-top-3 border-primary">
                <div class="card-header bg-white">
                  <h3 class="card-title font-weight-bold"><i class="fas fa-birthday-cake text-warning mr-2"></i> ខួបកំណើតមន្ត្រីក្នុងខែនេះ</h3>
                  <div class="card-tools"><span class="badge badge-danger">{{ birthdays.length }} នាក់</span></div>
                </div>
                <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                  <ul class="products-list product-list-in-card">
                    <li v-if="birthdays.length === 0" class="item text-center py-4 text-muted">មិនមានមន្ត្រីដែលមានខួបកំណើតក្នុងខែនេះទេ</li>
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
                        <span class="product-description text-muted">{{ user.position_name }} | ទើបមានអាយុ {{ user.age }} ឆ្នាំ</span>
                      </div>
                    </li>
                  </ul>
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
import axios from 'axios';

const userStore = useUserStore();
const defaultAvatar = 'https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg';

const loading = ref(true);
const stats = ref({});
const birthdays = ref([]);
const selectedDate = ref(new Date().toISOString().split('T')[0]); // កាលបរិច្ឆេទសម្រាប់ Filter វត្តមាន
const absentOrPermissionList = ref([]);
const lateList = ref([]);
const missionList = ref([]);

const getFullImageUrl = (path) => {
  if (!path) return defaultAvatar;
  if (path.startsWith('http')) return path;
  
  const backendBase = (import.meta.env.VITE_APP_API_URL || 'http://localhost:8000').replace(/\/api\/?$/, '');
  let cleanPath = path.replace(/^\//, '');
  if (!cleanPath.startsWith('storage/')) cleanPath = `storage/${cleanPath}`;
  
  return `${backendBase}/${cleanPath}`;
};

const fetchDashboardData = async () => {
  loading.value = true;
  try {
    const res = await apiGetDashboardStats();
    stats.value = res.data.stats || {};
    birthdays.value = res.data.birthdays || [];

    await fetchAttendanceSummary();
  } catch (error) {
    console.error("Error fetching dashboard data:", error);
  } finally {
    loading.value = false;
  }
};

// ទាញយកស្ថិតិវត្តមានតាមកាលបរិច្ឆេទដែលបានជ្រើសរើស
const fetchAttendanceSummary = async () => {
  try {
    const attendanceRes = await axios.get(`${import.meta.env.VITE_APP_API_URL}/manage/get-dashboard-summary?date=${selectedDate.value}`);
    const allAttendances = attendanceRes.data.attendances || [];
    
    // 1. អវត្តមាន ឬ មានច្បាប់ (ABSENT & PERMISSION)
    absentOrPermissionList.value = allAttendances.filter(item => {
      const s = String(item.status).toUpperCase();
      return s === 'ABSENT' || s === 'PERMISSION';
    });

    // 2. មកយឺត (LATE)
   lateList.value = allAttendances.filter(item => {
  const status = String(item.status).toUpperCase();
  const checkIn = item.check_in_time ? item.check_in_time.substring(0, 5) : null;

  // ប្រសិនបើ Status ជា LATE ផ្ទាល់ ឬក៏មានម៉ោងចូលហើយ ធំជាង 09:00 ដាច់ខាត ទើបឱ្យចូលក្នុងបញ្ជី LATE
  if (status === 'LATE' || (checkIn && checkIn > '09:00')) {
    return true;
  }
  return false;
});

    // 3. បេសកកម្ម (MISSION)
    missionList.value = allAttendances.filter(item => String(item.status).toUpperCase() === 'MISSION');

  } catch (error) {
    console.error("Error fetching attendance summary:", error);
  }
};

onMounted(() => {
  fetchDashboardData();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap');

.content-wrapper, .card, table, button, input, textarea {
  font-family: 'Battambang', sans-serif !important;
  background-color: #f4f6f9;
}
.rounded-lg { border-radius: 12px !important; }
.small-box { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.small-box:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.1) !important; }
.border-top-3 { border-top-width: 3px !important; }
.blink-text { animation: blinker 1.5s linear infinite; }
@keyframes blinker { 50% { opacity: 0.5; } }




/* រៀបចំចន្លោះគម្លាត item ក្នុង List ឱ្យមានសោភ័ណភាព */
.products-list .item {
  padding: 12px 5px !important;
  border-bottom: 1px solid #f0f0f0;
}

.products-list .item:last-child {
  border-bottom: none;
}

.rounded-lg { border-radius: 12px !important; }
.small-box { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.small-box:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.1) !important; }
.border-top-3 { border-top-width: 3px !important; }
.blink-text { animation: blinker 1.5s linear infinite; }
@keyframes blinker { 50% { opacity: 0.5; } }
</style>