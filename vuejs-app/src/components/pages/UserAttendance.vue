<template>
  <div class="content-wrapper user-attendance-page" style="min-height: 1000px; background-color: #f4f6f9;">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark font-weight-bold page-title">ប្រវត្តិវត្តមានរបស់ខ្ញុំ</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <!-- Filter Month Card -->
        <div class="card shadow-sm border-0 rounded-lg mb-4">
          <div class="card-body p-3">
            <div class="row align-items-center">
              <div class="col-md-4 d-flex align-items-center">
                <label class="font-weight-bold mb-0 mr-3 text-secondary text-nowrap">ជ្រើសរើសខែ:</label>
                <input type="month" class="form-control" v-model="selectedMonth" @change="fetchMyAttendances" />
              </div>
            </div>
          </div>
        </div>

        <!-- Attendance Table -->
        <div class="card shadow-sm border-0 rounded-lg">
          <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="text-center" style="width: 60px;">ល.រ</th>
                  <th>កាលបរិច្ឆេទ</th>
                  <th>ស្ថានភាព</th>
                  <th>ម៉ោងចូល - ម៉ោងចេញ</th>
                  <th>មូលហេតុ / បញ្ជាក់បន្ថែម</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="attendances.length === 0">
                  <td colspan="5" class="text-center py-4 text-muted">មិនមានទិន្នន័យវត្តមានសម្រាប់ខែនេះទេ</td>
                </tr>
                <tr v-for="(item, index) in attendances" :key="item.id">
                  <td class="text-center font-weight-bold">{{ index + 1 }}</td>
                  <td><span class="font-weight-bold text-dark">{{ item.date }}</span></td>
                  <td>
                    <span class="badge px-2 py-1" :class="getStatusBadgeClass(item.status)">
                      {{ formatStatusKhmer(item.status) }}
                    </span>
                  </td>
                  <td>
                    <span v-if="['ABSENT', 'PERMISSION', 'MISSION'].includes(item.status)" class="text-muted">---</span>
                    <span v-else class="font-weight-bold">
                      {{ item.check_in_time || '--:--' }} - {{ item.check_out_time || '--:--' }}
                    </span>
                  </td>
                  <td>
                    <span class="text-secondary">{{ item.note || '---' }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { apiGetMyAttendances } from '@/functions/api/attendance';

const attendances = ref([]);
const selectedMonth = ref(new Date().toISOString().slice(0, 7)); // ទម្រង់ YYYY-MM

const fetchMyAttendances = async () => {
  try {
    const res = await apiGetMyAttendances({ month: selectedMonth.value });
    console.log("My Attendances:", res.data);
    attendances.value = res.data.data || [];
  } catch (error) {
    console.error("Error fetching my attendances:", error);
  }
};

const formatStatusKhmer = (s) => ({
  'PRESENT': 'មានវត្តមាន',
  'LATE': 'មកយឺត',
  'ABSENT': 'អវត្តមាន',
  'PERMISSION': 'មានច្បាប់',
  'MISSION': 'បេសកកម្ម'
}[s] || s);

const getStatusBadgeClass = (s) => ({
  'PRESENT': 'badge-success',
  'LATE': 'badge-warning',
  'ABSENT': 'badge-danger',
  'PERMISSION': 'badge-info',
  'MISSION': 'badge-primary'
}[s] || 'badge-secondary');

onMounted(fetchMyAttendances);
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap');
.user-attendance-page, .modal, .card, table, button, input, textarea { 
  font-family: 'Battambang', sans-serif !important; 
}
.table thead th { font-size: 14px; color: #666; }
.table tbody td { font-size: 14px; vertical-align: middle; }
.rounded-lg { border-radius: 12px !important; }
.form-control { border-radius: 8px; }
</style>