<template>
  <div class="content-wrapper attendance-page" style="min-height: 1000px; background-color: #f4f6f9;">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark font-weight-bold page-title">គ្រប់គ្រងវត្តមានមន្ត្រី</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="card shadow-sm border-0 rounded-lg mb-4">
          <div class="card-body p-3">
            <div class="row align-items-center">
              <div class="col-md-3">
                <input type="date" class="form-control" v-model="selectedDate" @change="fetchAttendances" />
              </div>
              <div class="col-md-9 text-md-right">
                <button @click="fetchAttendances" class="btn btn-primary px-4 shadow-sm">
                  <i class="fas fa-sync-alt mr-1"></i> ផ្ទុកទិន្នន័យ
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="card shadow-sm border-0 rounded-lg">
          <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="text-center">ល.រ</th>
                  <th>ឈ្មោះ</th>
                  <th>តួនាទី</th>
                  <th class="text-center">អវត្តមាន</th>
                  <th class="text-center">ច្បាប់</th>
                  <th class="text-center">បេសកកម្ម</th>
                  <th>ម៉ោងវត្តមាន</th>
                  <th class="text-center">សកម្មភាព</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in attendances" :key="item.user_id">
                  <td class="text-center font-weight-bold">{{ index + 1 }}</td>
                  <td><div class="font-weight-bold text-dark">{{ item.user?.name_kh || item.user?.name }}</div></td>
                  <td><span class="text-secondary">{{ item.user?.position?.title_kh || '---' }}</span></td>
                  
                  <td class="text-center">
                    <input type="checkbox" :checked="item.status === 'ABSENT'" @change="handleSpecialStatus(item, 'ABSENT')" class="custom-checkbox checkbox-danger">
                  </td>
                  <td class="text-center">
                    <input type="checkbox" :checked="item.status === 'PERMISSION'" @change="handleSpecialStatus(item, 'PERMISSION')" class="custom-checkbox checkbox-info">
                  </td>
                  <td class="text-center">
                    <input type="checkbox" :checked="item.status === 'MISSION'" @change="handleSpecialStatus(item, 'MISSION')" class="custom-checkbox checkbox-primary">
                  </td>

                  <td>
                    <span v-if="item.status === 'ABSENT'" class="badge badge-danger px-2 py-1">អវត្តមាន: {{ item.note }}</span>
                    <span v-else-if="item.status === 'PERMISSION'" class="badge badge-info px-2 py-1">មានច្បាប់: {{ item.note }}</span>
                    <span v-else-if="item.status === 'MISSION'" class="badge badge-primary px-2 py-1">បេសកកម្ម: {{ item.note }}</span>
                    <span v-else>
                      <span class="badge px-2 py-1 mr-1" :class="item.status === 'LATE' ? 'badge-warning' : 'badge-success'">
                        {{ formatStatusKhmer(item.status) }}
                      </span> 
                      <span class="font-weight-bold">{{ item.check_in_time || '08:00' }}</span>
                      <small v-if="item.note" class="text-info d-block"><i class="fas fa-comment-dots mr-1"></i>{{ item.note }}</small>
                    </span>
                  </td>

                  <td class="text-center">
                    <button @click="openModal(item)" class="btn btn-sm btn-outline-primary px-3 rounded-pill">
                      <i class="fas fa-clock mr-1"></i> កត់ត្រាម៉ោង
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" ref="attendanceModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <form @submit.prevent="saveAttendance" class="w-100">
          <div class="modal-content border-0 shadow-lg rounded-lg">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>កត់ត្រាវត្តមានមន្ត្រី</h5>
              <button type="button" class="close text-white" @click="closeModal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
              <div class="row" v-if="['PRESENT', 'LATE'].includes(form.status)">
                <div class="col-6">
                  <label class="font-weight-bold text-secondary">ម៉ោងចូល:</label>
                  <input type="time" class="form-control" v-model="form.check_in_time" required>
                </div>
                <div class="col-6">
                  <label class="font-weight-bold text-secondary">ម៉ោងចេញ:</label>
                  <input type="time" class="form-control" v-model="form.check_out_time">
                </div>
              </div>

              <div class="form-group mt-3">
                <label class="font-weight-bold text-secondary">
                  {{ ['ABSENT', 'PERMISSION', 'MISSION'].includes(form.status) ? 'មូលហេតុ (ចាំបាច់):' : 'បញ្ជាក់បន្ថែម (មិនบังคับ):' }}
                </label>
                <textarea class="form-control" v-model="form.note" rows="3" :required="['ABSENT', 'PERMISSION', 'MISSION'].includes(form.status)" placeholder="សរសេរមូលហេតុ..."></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary px-4">រក្សាទុក</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import $ from 'jquery';
import Swal from 'sweetalert2';
import { apiGetAttendances, apiSaveAttendance } from '@/functions/api/attendance';

const attendances = ref([]);
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const attendanceModal = ref(null);
const form = reactive({ user_id: null, date: '', status: 'PRESENT', check_in_time: '', check_out_time: '', note: '' });

const fetchAttendances = async () => {
  const res = await apiGetAttendances({ date: selectedDate.value });
  let data = res.data.data;
  
  data.sort((a, b) => {
    const levelA = a.user?.position?.level ?? 99;
    const levelB = b.user?.position?.level ?? 99;
    return levelA - levelB;
  });

  // បង្ខំឱ្យ Vue Update Component ថ្មីដោយប្រើ Spread Operator (...)
  attendances.value = [...data];
};

const handleSpecialStatus = (item, status) => {
  if (item.status === status) {
    const payload = { user_id: item.user_id, date: selectedDate.value, status: 'PRESENT', check_in_time: '08:00', note: '' };
    apiSaveAttendance(payload).then(() => fetchAttendances());
  } else {
    form.user_id = item.user_id;
    form.date = selectedDate.value;
    form.status = status;
    form.check_in_time = null;
    form.check_out_time = null;
    form.note = '';
    $(attendanceModal.value).modal('show');
  }
};

const openModal = (item) => {
  form.user_id = item.user_id;
  form.date = selectedDate.value;
  form.status = ['ABSENT', 'PERMISSION', 'MISSION'].includes(item.status) ? 'PRESENT' : item.status;
  form.check_in_time = item.check_in_time || '08:00';
  form.check_out_time = item.check_out_time || '';
  form.note = item.note || '';
  $(attendanceModal.value).modal('show');
};

const closeModal = () => $(attendanceModal.value).modal('hide');

const saveAttendance = async () => {
  if (form.check_in_time) {
    // កាត់យកតែ ៥ តួដំបូង (HH:mm) ឧ. "09:00:15" 变为 "09:00"
    const timeOnly = form.check_in_time.substring(0, 5);

    if (timeOnly > '09:00') {
      form.status = 'LATE';       
    } else {
      form.status = 'PRESENT';
    }
  }

  await apiSaveAttendance(form);
  closeModal();
  fetchAttendances();
  Swal.fire({ icon: 'success', title: 'រក្សាទុកជោគជ័យ!', showConfirmButton: false, timer: 1500 });
};

const formatStatusKhmer = (s) => ({'PRESENT':'មានវត្តមាន','LATE':'មកយឺត','ABSENT':'អវត្តមាន','PERMISSION':'ច្បាប់','MISSION':'បេសកកម្ម'}[s] || s);

onMounted(fetchAttendances);
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap');
.attendance-page, .modal, .card, table, button, input, textarea { font-family: 'Battambang', sans-serif !important; }
.custom-checkbox { width: 20px; height: 20px; cursor: pointer; }
.checkbox-danger { accent-color: #dc3545; }
.checkbox-info { accent-color: #17a2b8; }
.checkbox-primary { accent-color: #007bff; }



</style>