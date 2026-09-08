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
              <div class="col-md-3 mb-2 mb-md-0">
                <input type="date" class="form-control" v-model="selectedDate" @change="fetchAttendances" />
              </div>
              <div class="col-md-4 mb-2 mb-md-0">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-white border-right-0">
                      <i class="fas fa-search text-muted"></i>
                    </span>
                  </div>
                  <input type="text" class="form-control border-left-0" v-model="searchQuery" placeholder="ស្វែងរកឈ្មោះមន្ត្រី..." />
                </div>
              </div>
              <div class="col-md-5 text-md-right">
                <button @click="openImportModal" class="btn btn-success px-3 shadow-sm mr-2">
                  <i class="fas fa-file-excel mr-1"></i> Import Excel
                </button>
                <button @click="fetchAttendances" class="btn btn-primary px-3 shadow-sm">
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
                  <th class="text-center" style="width: 140px;">ម៉ោងធ្វើការ</th>
                  <th class="text-center">សកម្មភាព</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="filteredAttendances.length === 0">
                  <td colspan="9" class="text-center py-4 text-muted">
                    មិនមានទិន្នន័យមន្ត្រីដែលត្រូវនឹងការស្វែងរកឡើយ
                  </td>
                </tr>
                <tr v-else v-for="(item, index) in filteredAttendances" :key="item.user_id">
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
                      <span v-if="item.check_out_time" class="text-secondary small"> - {{ item.check_out_time }}</span>
                      <small v-if="item.note" class="text-info d-block"><i class="fas fa-comment-dots mr-1"></i>{{ item.note }}</small>
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
                <div class="col-12 mt-2" v-if="modalCalculatedWorkingHours">
                  <div class="alert alert-success py-1 px-3 mb-0 small d-flex align-items-center">
                    <i class="fas fa-stopwatch mr-2"></i>
                    <span>ម៉ោងធ្វើការសរុប៖ <strong>{{ modalCalculatedWorkingHours }}</strong></span>
                  </div>
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

    <!-- Import Excel Modal -->
    <div v-if="showImportModal" class="custom-modal-backdrop" @click.self="closeImportModal">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable my-auto" role="document" style="width: 100%; max-width: 1050px;">
        <div class="modal-content border-0 shadow-lg rounded-lg font-khmer">
          <div class="modal-header bg-success text-white py-3">
            <h5 class="modal-title font-weight-bold m-0">
              <i class="fas fa-file-excel mr-2"></i> Import វត្តមានមន្ត្រីពី File Excel
            </h5>
            <button type="button" class="close text-white" @click="closeImportModal">
              <span>&times;</span>
            </button>
          </div>

          <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
            <!-- Step Instructions & Template Download -->
            <div class="alert alert-light border shadow-xs mb-4">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h6 class="font-weight-bold text-dark mb-1">
                    <i class="fas fa-info-circle text-primary mr-1"></i> សេចក្តីណែនាំអំពីការ Import វត្តមាន
                  </h6>
                  <p class="text-muted small mb-0">
                    សូមទាញយកគំរូទម្រង់ Excel (.xlsx) ដែលមានរាយនាមមន្ត្រីរួចជាស្រេច រួចបំពេញព័ត៌មានស្ថានភាព ឬម៉ោងចូល/ចេញ និង Upload ចូលមកវិញ។
                  </p>
                </div>
                <div class="col-md-4 text-md-right mt-2 mt-md-0">
                  <button @click="downloadExcelTemplate" class="btn btn-outline-success btn-sm shadow-xs font-weight-bold">
                    <i class="fas fa-download mr-1"></i> ទាញយកគំរូ Excel (.xlsx)
                  </button>
                </div>
              </div>
            </div>

            <!-- Upload Box -->
            <div class="card border-dashed p-4 text-center bg-light rounded-lg mb-4 cursor-pointer" @click="triggerFileInput">
              <input
                type="file"
                ref="fileInputRef"
                class="d-none"
                accept=".xlsx,.xls,.csv"
                @change="handleFileSelected"
              />
              <div v-if="!importFileName">
                <i class="fas fa-file-excel fa-3x text-success mb-2"></i>
                <h6 class="font-weight-bold text-dark mb-1">ចុចទីនេះដើម្បីជ្រើសរើស File Excel (.xlsx, .xls, .csv)</h6>
                <small class="text-muted">គាំទ្រឯកសារ Excel និង CSV ដែលបានបំពេញទិន្នន័យវត្តមាន</small>
              </div>
              <div v-else class="d-flex align-items-center justify-content-center">
                <i class="fas fa-file-excel fa-2x text-success mr-2"></i>
                <span class="font-weight-bold text-dark mr-3">{{ importFileName }}</span>
                <button type="button" class="btn btn-xs btn-outline-secondary" @click.stop="resetFileSelection">
                  <i class="fas fa-times mr-1"></i> ប្តូរ File ផ្សេង
                </button>
              </div>
            </div>

            <!-- Loading during file parsing -->
            <div v-if="importingFile" class="text-center py-4 text-muted">
              <div class="spinner-border spinner-border-sm text-success mr-2" role="status"></div>
              កំពុងអានទិន្នន័យពី File Excel...
            </div>

            <!-- Preview Results Section -->
            <div v-if="parsedRecords.length > 0">
              <!-- Summary Counters -->
              <div class="row mb-3">
                <div class="col-sm-4 mb-2">
                  <div class="p-2 border rounded bg-white text-center">
                    <span class="text-muted small d-block">ទិន្នន័យសរុបក្នុង File</span>
                    <strong class="h5 text-dark m-0">{{ parsedRecords.length }} នាក់</strong>
                  </div>
                </div>
                <div class="col-sm-4 mb-2">
                  <div class="p-2 border rounded bg-white text-center">
                    <span class="text-muted small d-block">ត្រឹមត្រូវអាច Import បាន</span>
                    <strong class="h5 text-success m-0">{{ validRecordsCount }} នាក់</strong>
                  </div>
                </div>
                <div class="col-sm-4 mb-2">
                  <div class="p-2 border rounded bg-white text-center">
                    <span class="text-muted small d-block">មិនត្រូវគ្នា / មានបញ្ហា</span>
                    <strong class="h5 text-danger m-0">{{ invalidRecordsCount }} នាក់</strong>
                  </div>
                </div>
              </div>

              <!-- Preview Table -->
              <div class="table-responsive border rounded bg-white" style="max-height: 320px;">
                <table class="table table-sm table-hover table-striped mb-0 text-sm align-middle">
                  <thead class="bg-light sticky-top">
                    <tr>
                      <th class="text-center" style="width: 40px;">#</th>
                      <th>កូដមន្ត្រី</th>
                      <th>ឈ្មោះមន្ត្រី</th>
                      <th>កាលបរិច្ឆេទ</th>
                      <th class="text-center">ស្ថានភាព</th>
                      <th class="text-center">ម៉ោងចូល - ចេញ</th>
                      <th>មូលហេតុ / សម្គាល់</th>
                      <th class="text-center">ផ្ទៀងផ្ទាត់</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="rec in parsedRecords" :key="rec.row_index" :class="{ 'table-danger': !rec.isValid }">
                      <td class="text-center text-muted font-weight-bold">{{ rec.row_index }}</td>
                      <td>
                        <span class="font-weight-500">{{ rec.employee_code || '---' }}</span>
                      </td>
                      <td>
                        <strong :class="rec.isValid ? 'text-dark' : 'text-danger'">{{ rec.name || '---' }}</strong>
                      </td>
                      <td>{{ rec.date }}</td>
                      <td class="text-center">
                        <span class="badge px-2 py-1" :class="getStatusBadgeClass(rec.status)">
                          {{ formatStatusKhmer(rec.status) }}
                        </span>
                      </td>
                      <td class="text-center">
                        <span v-if="['PRESENT', 'LATE'].includes(rec.status)">
                          {{ rec.check_in_time || '08:00' }} - {{ rec.check_out_time || '17:00' }}
                        </span>
                        <span v-else class="text-muted">---</span>
                      </td>
                      <td>
                        <small class="text-muted text-truncate d-block" style="max-width: 200px;" :title="rec.note">
                          {{ rec.note || '---' }}
                        </small>
                      </td>
                      <td class="text-center">
                        <span v-if="rec.isValid" class="badge badge-success px-2 py-1">
                          <i class="fas fa-check mr-1"></i> ត្រឹមត្រូវ
                        </span>
                        <span v-else class="badge badge-danger px-2 py-1" :title="rec.errorMessage">
                          <i class="fas fa-exclamation-triangle mr-1"></i> {{ rec.errorMessage }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="modal-footer bg-light justify-content-between">
            <button type="button" class="btn btn-secondary px-3" @click="closeImportModal">
              <i class="fas fa-times mr-1"></i> បោះបង់
            </button>
            <button
              type="button"
              class="btn btn-success px-4 font-weight-bold shadow-sm"
              :disabled="validRecordsCount === 0 || isSubmittingImport"
              @click="confirmImport"
            >
              <i class="fas fa-spinner fa-spin mr-1" v-if="isSubmittingImport"></i>
              <i class="fas fa-file-import mr-1" v-else></i>
              បញ្ជាក់ និងរក្សាទុក ({{ validRecordsCount }} នាក់)
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import $ from 'jquery';
import Swal from 'sweetalert2';
import * as XLSX from 'xlsx';
import { apiGetAttendances, apiSaveAttendance, apiImportAttendances } from '@/functions/api/attendance';

const attendances = ref([]);
const searchQuery = ref('');
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const attendanceModal = ref(null);
const form = reactive({ user_id: null, date: '', status: 'PRESENT', check_in_time: '', check_out_time: '', note: '' });

const filteredAttendances = computed(() => {
  if (!searchQuery.value.trim()) {
    return attendances.value;
  }
  const q = searchQuery.value.toLowerCase().trim();
  return attendances.value.filter(item => {
    const nameKh = (item.user?.name_kh || '').toLowerCase();
    const nameEn = (item.user?.name_en || '').toLowerCase();
    const name = (item.user?.name || '').toLowerCase();
    return nameKh.includes(q) || nameEn.includes(q) || name.includes(q);
  });
});

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

// គណនាម៉ោងធ្វើការ
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

const modalCalculatedWorkingHours = computed(() => {
  return calculateWorkingHours(form.check_in_time, form.check_out_time, form.status);
});

// ========================
// EXCEL IMPORT FUNCTIONALITY
// ========================
const showImportModal = ref(false);
const fileInputRef = ref(null);
const importFileName = ref('');
const importingFile = ref(false);
const isSubmittingImport = ref(false);
const parsedRecords = ref([]);

const validRecordsCount = computed(() => parsedRecords.value.filter(r => r.isValid).length);
const invalidRecordsCount = computed(() => parsedRecords.value.filter(r => !r.isValid).length);

const openImportModal = () => {
  importFileName.value = '';
  parsedRecords.value = [];
  importingFile.value = false;
  isSubmittingImport.value = false;
  showImportModal.value = true;
};

const closeImportModal = () => {
  showImportModal.value = false;
};

const triggerFileInput = () => {
  if (fileInputRef.value) {
    fileInputRef.value.click();
  }
};

const resetFileSelection = () => {
  importFileName.value = '';
  parsedRecords.value = [];
  if (fileInputRef.value) {
    fileInputRef.value.value = '';
  }
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'PRESENT': return 'badge-success';
    case 'LATE': return 'badge-warning';
    case 'ABSENT': return 'badge-danger';
    case 'PERMISSION': return 'badge-info';
    case 'MISSION': return 'badge-primary';
    default: return 'badge-secondary';
  }
};

// 1. Download Sample Excel Template (.xlsx)
const downloadExcelTemplate = () => {
  const data = attendances.value.map(item => ({
    'employee_code': item.user?.employee_code || (item.user?.id ? `ID-${item.user.id}` : ''),
    'name': item.user?.name_kh || item.user?.name || '',
    'date': selectedDate.value,
    'status': item.status || 'PRESENT',
    'check_in_time': item.check_in_time || '08:00',
    'check_out_time': item.check_out_time || '17:00',
    'note': item.note || ''
  }));

  if (data.length === 0) {
    data.push({
      'employee_code': 'EMP001',
      'name': 'ឈ្មោះមន្ត្រីគំរូ',
      'date': selectedDate.value,
      'status': 'PRESENT',
      'check_in_time': '08:00',
      'check_out_time': '17:00',
      'note': ''
    });
  }

  const worksheet = XLSX.utils.json_to_sheet(data);
  worksheet['!cols'] = [
    { wch: 18 }, // employee_code
    { wch: 26 }, // name
    { wch: 14 }, // date
    { wch: 16 }, // status
    { wch: 14 }, // check_in_time
    { wch: 14 }, // check_out_time
    { wch: 30 }  // note
  ];

  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Attendance');
  XLSX.writeFile(workbook, `Attendance_Template_${selectedDate.value}.xlsx`);
};

// 2. Parse uploaded Excel file
const handleFileSelected = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  importFileName.value = file.name;
  importingFile.value = true;
  parsedRecords.value = [];

  const reader = new FileReader();
  reader.onload = (e) => {
    try {
      const data = new Uint8Array(e.target.result);
      const workbook = XLSX.read(data, { type: 'array' });
      const firstSheetName = workbook.SheetNames[0];
      const worksheet = workbook.Sheets[firstSheetName];
      const rawJson = XLSX.utils.sheet_to_json(worksheet, { defval: '' });

      const records = [];

      rawJson.forEach((row, index) => {
        const empCode = String(row['employee_code'] || row['កូដមន្ត្រី'] || row['Code'] || row['code'] || '').trim();
        const rowName = String(row['name'] || row['ឈ្មោះ'] || row['ឈ្មោះមន្ត្រី'] || '').trim();
        let rowDate = String(row['date'] || row['កាលបរិច្ឆេទ'] || selectedDate.value).trim();

        // Handle Excel numeric date serial
        if (typeof row['date'] === 'number') {
          const jsDate = new Date(Math.round((row['date'] - 25569) * 86400 * 1000));
          rowDate = jsDate.toISOString().split('T')[0];
        }

        let rowStatus = String(row['status'] || row['ស្ថានភាព'] || 'PRESENT').trim().toUpperCase();
        if (rowStatus === 'មានវត្តមាន' || rowStatus === 'វត្តមាន') rowStatus = 'PRESENT';
        else if (rowStatus === 'មកយឺត' || rowStatus === 'យឺត') rowStatus = 'LATE';
        else if (rowStatus === 'អវត្តមាន') rowStatus = 'ABSENT';
        else if (rowStatus === 'ច្បាប់' || rowStatus === 'មានច្បាប់') rowStatus = 'PERMISSION';
        else if (rowStatus === 'បេសកកម្ម') rowStatus = 'MISSION';

        let checkIn = String(row['check_in_time'] || row['ម៉ោងចូល'] || '').trim();
        let checkOut = String(row['check_out_time'] || row['ម៉ោងចេញ'] || '').trim();
        const note = String(row['note'] || row['មូលហេតុ'] || row['សម្គាល់'] || '').trim();

        // Convert Excel fractional time if any
        if (typeof row['check_in_time'] === 'number') {
          const totalMinutes = Math.round(row['check_in_time'] * 24 * 60);
          const h = String(Math.floor(totalMinutes / 60)).padStart(2, '0');
          const m = String(totalMinutes % 60).padStart(2, '0');
          checkIn = `${h}:${m}`;
        }
        if (typeof row['check_out_time'] === 'number') {
          const totalMinutes = Math.round(row['check_out_time'] * 24 * 60);
          const h = String(Math.floor(totalMinutes / 60)).padStart(2, '0');
          const m = String(totalMinutes % 60).padStart(2, '0');
          checkOut = `${h}:${m}`;
        }

        // Match user from current attendances list
        const matchedItem = attendances.value.find(item => {
          const u = item.user;
          if (!u) return false;
          if (empCode) {
            if (String(u.employee_code).toLowerCase() === empCode.toLowerCase()) return true;
            if (String(u.id) === empCode || `ID-${u.id}`.toLowerCase() === empCode.toLowerCase()) return true;
          }
          if (rowName) {
            if (String(u.name_kh).toLowerCase() === rowName.toLowerCase()) return true;
            if (String(u.name).toLowerCase() === rowName.toLowerCase()) return true;
          }
          return false;
        });

        const isValid = !!matchedItem;

        records.push({
          row_index: index + 1,
          employee_code: empCode,
          name: matchedItem ? (matchedItem.user?.name_kh || matchedItem.user?.name) : rowName,
          user_id: matchedItem ? matchedItem.user_id : null,
          date: rowDate || selectedDate.value,
          status: rowStatus,
          check_in_time: checkIn,
          check_out_time: checkOut,
          note: note,
          isValid: isValid,
          errorMessage: isValid ? '' : 'រកមិនឃើញមន្ត្រីតាមកូដ ឬឈ្មោះនេះឡើយ'
        });
      });

      parsedRecords.value = records;
    } catch (err) {
      console.error('Failed to parse Excel file:', err);
      Swal.fire('កំហុស', 'មិនអាចអាន File Excel នេះបានឡើយ សូមពិនិត្យមើលទម្រង់ File ម្តងទៀត', 'error');
    } finally {
      importingFile.value = false;
    }
  };

  reader.readAsArrayBuffer(file);
};

// 3. Confirm and Send to API
const confirmImport = async () => {
  const validRecords = parsedRecords.value.filter(r => r.isValid && r.user_id);
  if (validRecords.length === 0) {
    Swal.fire('គ្មានទិន្នន័យ', 'មិនមានទិន្នន័យត្រឹមត្រូវសម្រាប់ Import ឡើយ', 'warning');
    return;
  }

  isSubmittingImport.value = true;
  try {
    const payload = {
      records: validRecords.map(r => ({
        user_id: r.user_id,
        date: r.date,
        status: r.status,
        check_in_time: r.check_in_time || null,
        check_out_time: r.check_out_time || null,
        note: r.note || null
      }))
    };

    const res = await apiImportAttendances(payload);
    if (res.data?.success) {
      Swal.fire({
        icon: 'success',
        title: 'ជោគជ័យ!',
        text: res.data.message || `បាន Import វត្តមានដោយជោគជ័យ`,
        timer: 2000,
        showConfirmButton: false
      });
      closeImportModal();
      await fetchAttendances();
    } else {
      Swal.fire('កំហុស', res.data?.message || 'មានបញ្ហាក្នុងការ Import វត្តមាន', 'error');
    }
  } catch (err) {
    console.error('Error importing attendances:', err);
    Swal.fire('កំហុស', err.response?.data?.message || 'បរាជ័យក្នុងការ Import វត្តមាន', 'error');
  } finally {
    isSubmittingImport.value = false;
  }
};

onMounted(fetchAttendances);
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap');
.attendance-page, .modal, .card, table, button, input, textarea { font-family: 'Battambang', sans-serif !important; }
.custom-checkbox { width: 20px; height: 20px; cursor: pointer; }
.checkbox-danger { accent-color: #dc3545; }
.checkbox-info { accent-color: #17a2b8; }
.checkbox-primary { accent-color: #007bff; }

.custom-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  overflow-y: auto;
  padding: 1rem;
}

.border-dashed {
  border: 2px dashed #28a745 !important;
  background-color: #f8fff9 !important;
  transition: all 0.2s ease;
}
.border-dashed:hover {
  background-color: #f0fff2 !important;
  border-color: #218838 !important;
}
.cursor-pointer {
  cursor: pointer;
}
.font-weight-500 {
  font-weight: 500;
}
</style>