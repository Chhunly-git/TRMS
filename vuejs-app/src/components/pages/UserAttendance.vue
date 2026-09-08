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

        <!-- Attendance Summary Counts -->
        <div class="row mb-4">
          <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm rounded-lg bg-success text-white h-100">
              <div class="card-body d-flex align-items-center justify-content-between p-3">
                <div>
                  <h6 class="text-white-50 mb-1 small font-weight-bold">វត្តមាន (PRESENT)</h6>
                  <h3 class="font-weight-bold mb-0">{{ stats.PRESENT }} ថ្ងៃ</h3>
                </div>
                <div class="icon-circle-lg bg-white-10">
                  <i class="fas fa-check-circle fa-2x"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm rounded-lg bg-warning text-dark h-100">
              <div class="card-body d-flex align-items-center justify-content-between p-3">
                <div>
                  <h6 class="text-dark-50 mb-1 small font-weight-bold">មកយឺត (LATE)</h6>
                  <h3 class="font-weight-bold mb-0">{{ stats.LATE }} ថ្ងៃ</h3>
                </div>
                <div class="icon-circle-lg bg-dark-10">
                  <i class="fas fa-exclamation-circle fa-2x"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm rounded-lg bg-danger text-white h-100">
              <div class="card-body d-flex align-items-center justify-content-between p-3">
                <div>
                  <h6 class="text-white-50 mb-1 small font-weight-bold">អវត្តមាន (ABSENT)</h6>
                  <h3 class="font-weight-bold mb-0">{{ stats.ABSENT }} ថ្ងៃ</h3>
                </div>
                <div class="icon-circle-lg bg-white-10">
                  <i class="fas fa-times-circle fa-2x"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm rounded-lg bg-info text-white h-100">
              <div class="card-body d-flex align-items-center justify-content-between p-3">
                <div>
                  <h6 class="text-white-50 mb-1 small font-weight-bold">ច្បាប់ (PERMISSION)</h6>
                  <h3 class="font-weight-bold mb-0">{{ stats.PERMISSION }} ថ្ងៃ</h3>
                </div>
                <div class="icon-circle-lg bg-white-10">
                  <i class="fas fa-file-alt fa-2x"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm rounded-lg bg-primary text-white h-100">
              <div class="card-body d-flex align-items-center justify-content-between p-3">
                <div>
                  <h6 class="text-white-50 mb-1 small font-weight-bold">បេសកកម្ម (MISSION)</h6>
                  <h3 class="font-weight-bold mb-0">{{ stats.MISSION }} ថ្ងៃ</h3>
                </div>
                <div class="icon-circle-lg bg-white-10">
                  <i class="fas fa-paper-plane fa-2x"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm rounded-lg text-white h-100" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
              <div class="card-body d-flex align-items-center justify-content-between p-3">
                <div>
                  <h6 class="text-white-50 mb-1 small font-weight-bold">ម៉ោងសរុប (TOTAL HOURS)</h6>
                  <h4 class="font-weight-bold mb-0">{{ totalWorkingHoursFormatted }}</h4>
                </div>
                <div class="icon-circle-lg bg-white-10">
                  <i class="fas fa-stopwatch fa-2x"></i>
                </div>
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
                  <th class="text-center">ម៉ោងធ្វើការ</th>
                  <th>មូលហេតុ / បញ្ជាក់បន្ថែម</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="attendances.length === 0">
                  <td colspan="6" class="text-center py-4 text-muted">មិនមានទិន្នន័យវត្តមានសម្រាប់ខែនេះទេ</td>
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
                  <td class="text-center">
                    <span v-if="getWorkingHoursDisplay(item)" class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                      <i class="fas fa-stopwatch text-success mr-1"></i> {{ getWorkingHoursDisplay(item) }}
                    </span>
                    <span v-else-if="item.check_in_time && !item.check_out_time && ['PRESENT', 'LATE'].includes(item.status)" class="badge badge-light text-muted px-2 py-1">
                      <i class="fas fa-hourglass-half text-warning mr-1"></i> កំពុងធ្វើការ
                    </span>
                    <span v-else class="text-muted">---</span>
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
import { ref, onMounted, computed } from 'vue';
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

const stats = computed(() => {
  const counts = {
    PRESENT: 0,
    LATE: 0,
    ABSENT: 0,
    PERMISSION: 0,
    MISSION: 0
  };
  attendances.value.forEach(item => {
    if (counts[item.status] !== undefined) {
      counts[item.status]++;
    }
  });
  return counts;
});

// គណនាម៉ោងធ្វើការក្នុងមួយថ្ងៃ
const calculateWorkingHours = (checkIn, checkOut, status) => {
  if (!['PRESENT', 'LATE'].includes(status)) return null;
  if (!checkIn || !checkOut) return null;

  try {
    const inParts = String(checkIn).split(':').map(Number);
    const outParts = String(checkOut).split(':').map(Number);
    const inH = inParts[0], inM = inParts[1] || 0;
    const outH = outParts[0], outM = outParts[1] || 0;

    if (isNaN(inH) || isNaN(inM) || isNaN(outH) || isNaN(outM)) return null;

    const startMinutes = inH * 60 + inM;
    const endMinutes = outH * 60 + outM;

    if (endMinutes <= startMinutes) return null;

    const diffMinutes = endMinutes - startMinutes;
    const hours = Math.floor(diffMinutes / 60);
    const mins = diffMinutes % 60;

    if (mins === 0) {
      return `${hours} ម៉ោង`;
    }
    return `${hours} ម៉ោង ${mins} នាទី`;
  } catch (e) {
    return null;
  }
};

const getWorkingHoursDisplay = (item) => {
  if (item.working_hours_formatted) {
    return item.working_hours_formatted;
  }
  return calculateWorkingHours(item.check_in_time, item.check_out_time, item.status);
};

// គណនាម៉ោងធ្វើការសរុបប្រចាំខែ
const totalWorkingHoursFormatted = computed(() => {
  let totalMinutes = 0;
  attendances.value.forEach(item => {
    if (!['PRESENT', 'LATE'].includes(item.status)) return;
    if (!item.check_in_time || !item.check_out_time) return;
    try {
      const inParts = String(item.check_in_time).split(':').map(Number);
      const outParts = String(item.check_out_time).split(':').map(Number);
      const inH = inParts[0], inM = inParts[1] || 0;
      const outH = outParts[0], outM = outParts[1] || 0;
      if (isNaN(inH) || isNaN(inM) || isNaN(outH) || isNaN(outM)) return;
      const diff = (outH * 60 + outM) - (inH * 60 + inM);
      if (diff > 0) totalMinutes += diff;
    } catch (e) {}
  });

  const hours = Math.floor(totalMinutes / 60);
  const mins = totalMinutes % 60;
  if (totalMinutes === 0) return '0 ម៉ោង';
  if (mins === 0) return `${hours} ម៉ោង`;
  return `${hours} ម៉ោង ${mins} នាទី`;
});

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

/* Styles for summary cards */
.bg-white-10 {
  background-color: rgba(255, 255, 255, 0.15) !important;
}
.bg-dark-10 {
  background-color: rgba(0, 0, 0, 0.08) !important;
}
.icon-circle-lg {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}
.text-white-50 {
  color: rgba(255, 255, 255, 0.7) !important;
}
.text-dark-50 {
  color: rgba(0, 0, 0, 0.6) !important;
}
</style>