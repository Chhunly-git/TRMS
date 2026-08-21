<template>
  <div class="content-wrapper khmer-layout">
    <!-- Header -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-3 align-items-center">
          <div class="col-sm-6">
            <h1 class="m-0 font-weight-bold text-dark khmer-page-title">
              <i class="fas fa-users-cog mr-2 text-success"></i>គ្រប់គ្រងព័ត៌មានមន្ត្រី
            </h1>
          </div>
          <div class="col-sm-6 text-right">
            <button class="btn btn-dark-custom shadow-sm font-khmer" @click="openCreateModal">
              <i class="fas fa-user-plus mr-1"></i> បន្ថែមមន្ត្រីថ្មី
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Filter Card -->
        <div class="card card-outline card-success shadow-sm mb-4 border-0">
          <div class="card-body">
            <div class="row g-3 align-items-center">
              <div class="col-md-3">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-white border-right-0"><i
                        class="fas fa-search text-muted"></i></span>
                  </div>
                  <input type="text" class="form-control border-left-0 font-khmer" v-model="filters.keyword"
                    placeholder="ឈ្មោះ, អ៊ីមែល, អត្តលេខ, ទូរស័ព្ទ..." @input="handleSearch" />
                </div>
              </div>
              <div class="col-md-3">
                <select class="form-control font-khmer" v-model="filters.employee_type" @change="fetchUsers(1)">
                  <option value="">-- ប្រភេទមន្ត្រីទាំងអស់ --</option>
                  <option value="CIVIL_SERVICE">មន្ត្រីរាជការ</option>
                  <option value="STATUTORY">មន្ត្រីលក្ខន្តិកៈ</option>
                  <option value="CONTRACT">មន្ត្រីកិច្ចសន្យា</option>
                  <option value="OTHER">ផ្សេងៗ</option>
                </select>
              </div>
              <div class="col-md-2">
                <select class="form-control font-khmer" v-model="filters.status" @change="fetchUsers(1)">
                  <option value="">-- ស្ថានភាព --</option>
                  <option value="ENABLED">សកម្ម (ENABLED)</option>
                  <option value="DISABLED">ផ្អាក (DISABLED)</option>
                </select>
              </div>
              <div class="col-md-2">
                <select class="form-control font-khmer" v-model="filters.level" @change="fetchUsers(1)">
                  <option value="">-- កម្រិតសិទ្ធិ --</option>
                  <option value="ADMIN">ADMIN</option>
                  <option value="USER">USER</option>
                </select>
              </div>
              <div class="col-md-2 text-right">
                <button class="btn btn-secondary w-100 font-khmer" @click="resetFilters">
                  <i class="fas fa-redo-alt mr-1"></i> សម្អាត
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Table Card -->
        <div class="card shadow-sm border-0">
          <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0 custom-table">
              <thead class="bg-light">
                <tr>
                  <th style="width: 50px" class="text-center">#</th>
                  <th style="width: 70px;">រូបថត</th>
                  <th>អត្តលេខ</th>
                  <th>ឈ្មោះ</th>
                  <!-- <th class="text-center" style="width: 80px;">ភេទ</th> -->
                  <!-- <th>ប្រភេទ</th> -->
                  <th>នាយកដ្ឋាន / ការិយាល័យ / តួនាទី</th>
                  <th>ទំនាក់ទំនង</th>
                  <th class="text-center" style="width: 100px;">ស្ថានភាព</th>
                  <th style="width: 130px" class="text-center">សកម្មភាព</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="10" class="text-center py-5 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x text-success"></i>
                    <p class="mt-2 mb-0 font-khmer">កំពុងទាញយកទិន្នន័យ...</p>
                  </td>
                </tr>
                <tr v-else-if="users.length === 0">
                  <td colspan="10" class="text-center py-5 text-muted">
                    <i class="fas fa-folder-open fa-3x mb-2 text-secondary"></i>
                    <p class="mb-0 font-khmer">មិនមានទិន្នន័យឡើយ</p>
                  </td>
                </tr>
                <tr v-else v-for="(item, index) in users" :key="item.id">
                  <td class="text-center font-weight-bold text-muted">
                    {{ (pagination.currentPage - 1) * pagination.perPage + index + 1 }}
                  </td>
                  <td>
                    <img :src="getFullImageUrl(item.profile_thumbnail || item.profile_image)" alt="Avatar"
                      class="img-circle elevation-1 border avatar-img" @error="onImageError" />
                  </td>
                  <td>
                    <div class="font-weight-bold text-dark">{{ item.employee_code || '---' }}</div>
                    <small class="text-muted">MEF: {{ item.mef_card_number || '---' }}</small><br>
                    <span :class="getEmployeeTypeBadge(item.employee_type)">
                      {{ formatEmployeeType(item.employee_type) }}
                    </span>
                  </td>
                  <td>
                    <div class="font-weight-bold text-dark name-khmer">{{ item.name_kh || item.name || '---' }}</div>
                    <small class="text-muted">{{ item.name_en || '---' }}</small>
                  </td>
                  <!-- <td class="text-center">
                    <span :class="getGenderBadge(item.gender)">
                      {{ formatGender(item.gender) }}
                    </span>
                  </td> --> 
                  <!-- <td>
                    <span :class="getEmployeeTypeBadge(item.employee_type)">
                      {{ formatEmployeeType(item.employee_type) }}
                    </span>
                  </td> -->
                  <td>
                    <div class="font-weight-500">{{ item.department?.name_kh || item.department?.name || '---' }}</div>
                    <small class="text-muted d-block">
                      {{ item.position?.title_kh || item.position?.name || '---' }}
                      <span v-if="item.office"> ({{ item.office?.name_kh || item.office?.name }})</span>
                    </small>
                  </td>
                  <td>
                    <div><i class="fas fa-phone-alt mr-1 text-muted small"></i>{{ item.phone || '---' }}</div>
                    <small class="text-muted"><i class="fas fa-envelope mr-1 small"></i>{{ item.email }}</small>
                  </td>
                  <td class="text-center">
                    <span :class="item.status === 'ENABLED' ? 'badge badge-success' : 'badge badge-danger'">
                      {{ item.status === 'ENABLED' ? 'សកម្ម' : 'ផ្អាក' }}
                    </span>
                    <div class="mt-1">
                      <span class="badge badge-light border text-muted">{{ item.level }}</span>
                    </div>
                  </td>
                  <td class="text-center">
                    <div class="action-buttons">
                      <button class="btn btn-action btn-edit" title="កែសម្រួល" @click="openEditModal(item)">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button
                        :class="item.status === 'ENABLED' ? 'btn btn-action btn-disable' : 'btn btn-action btn-enable'"
                        :title="item.status === 'ENABLED' ? 'ផ្អាកដំណើរការ' : 'បើកដំណើរការ'"
                        @click="toggleStatus(item)">
                        <i :class="item.status === 'ENABLED' ? 'fas fa-user-slash' : 'fas fa-user-check'"></i>
                      </button>
                      <button class="btn btn-action btn-delete" title="លុប" @click="removeUser(item.id)">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="card-footer bg-white d-flex justify-content-between align-items-center"
            v-if="pagination.total > 0">
            <span class="text-muted small font-khmer">
              បង្ហាញ {{ users.length }} នៃទិន្នន័យសរុប {{ pagination.total }}
            </span>
            <ul class="pagination pagination-sm m-0">
              <li class="page-item" :class="{ disabled: pagination.currentPage === 1 }">
                <a class="page-link" href="#" @click.prevent="changePage(pagination.currentPage - 1)">&laquo;</a>
              </li>
              <li class="page-item" v-for="page in pagination.lastPage" :key="page"
                :class="{ active: pagination.currentPage === page }">
                <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: pagination.currentPage === pagination.lastPage }">
                <a class="page-link" href="#" @click.prevent="changePage(pagination.currentPage + 1)">&raquo;</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Modal Form (Create / Edit) -->
    <div v-if="showModal" class="custom-modal-backdrop">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content shadow-lg border-0 font-khmer">
          <div class="modal-header bg-success text-white py-3">
            <h5 class="modal-title font-weight-bold font-khmer">
              <i class="fas mr-2" :class="isEditMode ? 'fa-user-edit' : 'fa-user-plus'"></i>
              {{ isEditMode ? 'កែសម្រួលព័ត៌មានលម្អិតមន្ត្រី' : 'បញ្ចូលព័ត៌មានមន្ត្រីថ្មី' }}
            </h5>
            <button type="button" class="close text-white" @click="closeModal">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <form @submit.prevent="submitForm">
            <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
              <!-- ផ្នែកទី ១៖ ព័ត៌មានគណនី & សិទ្ធិ -->
              <h6 class="text-success font-weight-bold mb-3 border-bottom pb-2 font-khmer">
                <i class="fas fa-user-shield mr-1"></i> ១. ព័ត៌មានគណនី & សិទ្ធិប្រើប្រាស់
              </h6>
              <div class="row mb-3 align-items-center">
                <div class="col-md-3 text-center mb-3 mb-md-0">
                  <div class="avatar-preview-container">
                    <img :src="imagePreviewUrl || defaultAvatar" alt="Avatar Preview"
                      class="img-thumbnail rounded-circle border shadow-sm"
                      style="width: 110px; height: 110px; object-fit: cover;" @error="onImageError" />
                  </div>
                  <label class="btn btn-sm btn-outline-secondary mt-2 mb-0 font-khmer">
                    <i class="fas fa-camera mr-1"></i> រើសរូបថត
                    <input type="file" class="d-none" accept="image/*" @change="onFileChange" />
                  </label>
                </div>
                <div class="col-md-9">
                  <div class="row">
                    <div class="col-md-6 form-group">
                      <label class="form-label font-weight-bold">អ៊ីមែល / Email <span
                          class="text-danger">*</span></label>
                      <input type="email" class="form-control" v-model="form.email" required
                        placeholder="example@mef.gov.kh" />
                    </div>
                    <div class="col-md-6 form-group">
                      <label class="form-label font-weight-bold">
                        ពាក្យសម្ងាត់ <span v-if="!isEditMode" class="text-danger">*</span>
                        <small v-if="isEditMode" class="text-muted font-khmer">(ទុកទទេបើមិនចង់ប្តូរ)</small>
                      </label>
                      <input type="password" class="form-control" v-model="form.password" :required="!isEditMode"
                        placeholder="••••••••" />
                    </div>
                    <div class="col-md-6 form-group">
                      <label class="form-label font-weight-bold">កម្រិតសិទ្ធិ</label>
                      <select class="form-control" v-model="form.level">
                        <option value="USER">USER</option>
                        <option value="ADMIN">ADMIN</option>
                      </select>
                    </div>
                    <div class="col-md-6 form-group">
                      <label class="form-label font-weight-bold">ស្ថានភាព</label>
                      <select class="form-control" v-model="form.status">
                        <option value="ENABLED">សកម្ម (ENABLED)</option>
                        <option value="DISABLED">ផ្អាក (DISABLED)</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ផ្នែកទី ២៖ អង្គភាព & តួនាទី -->
              <h6 class="text-success font-weight-bold mb-3 border-bottom pb-2 font-khmer">
                <i class="fas fa-sitemap mr-1"></i> ២. អង្គភាព & តួនាទី
              </h6>
              <div class="row mb-3">
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">អត្តលេខមន្ត្រី</label>
                  <input type="text" class="form-control" v-model="form.employee_code" placeholder="ឧ. MEF-0001" />
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">លេខប័ណ្ណ MEF</label>
                  <input type="text" class="form-control" v-model="form.mef_card_number" placeholder="ឧ. MEF-CARD-01" />
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">ប្រភេទមន្ត្រី</label>
                  <select class="form-control" v-model="form.employee_type">
                    <option value="CIVIL_SERVICE">មន្ត្រីរាជការ</option>
                    <option value="STATUTORY">មន្ត្រីលក្ខន្តិកៈ</option>
                    <option value="CONTRACT">មន្ត្រីកិច្ចសន្យា</option>
                    <option value="OTHER">ផ្សេងៗ</option>
                  </select>
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">តួនាទី / Position</label>
                  <select class="form-control" v-model="form.position_id">
                    <option value="">-- ជ្រើសរើសតួនាទី --</option>
                    <!-- ប្ដូរពី pos.name_kh ទៅ pos.title_kh វិញ -->
                    <option v-for="pos in positions" :key="pos.id" :value="pos.id">
                      {{ pos.title_kh || pos.title_en }}
                    </option>
                  </select>
                </div>
                <div class="col-md-6 form-group">
                  <label class="form-label font-weight-bold">នាយកដ្ឋាន / Department</label>
                  <select class="form-control" v-model="form.department_id" @change="onDepartmentChange">
                    <option value="">-- ជ្រើសរើសនាយកដ្ឋាន --</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                      {{ dept.name_kh || dept.name }}
                    </option>
                  </select>
                </div>
                <div class="col-md-6 form-group">
                  <label class="form-label font-weight-bold">ការិយាល័យ / Office</label>
                  <select class="form-control" v-model="form.office_id" :disabled="!form.department_id">
                    <option value="">-- ជ្រើសរើសការិយាល័យ --</option>
                    <option v-for="off in departmentOffices" :key="off.id" :value="off.id">
                      {{ off.name_kh || off.name }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- ផ្នែកទី ៣៖ ព័ត៌មានផ្ទាល់ខ្លួន -->
              <h6 class="text-success font-weight-bold mb-3 border-bottom pb-2 font-khmer">
                <i class="fas fa-id-badge mr-1"></i> ៣. ព័ត៌មានផ្ទាល់ខ្លួន
              </h6>
              <div class="row mb-3">
                <div class="col-md-4 form-group">
                  <label class="form-label font-weight-bold">ឈ្មោះជាភាសាខ្មែរ <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" v-model="form.name_kh" required placeholder="ឧ. សុខ សាន" />
                </div>
                <div class="col-md-4 form-group">
                  <label class="form-label font-weight-bold">ឈ្មោះជាឡាតាំង / English</label>
                  <input type="text" class="form-control" v-model="form.name_en" placeholder="ឧ. SOK SAN" />
                </div>
                <div class="col-md-2 form-group">
                  <label class="form-label font-weight-bold">ភេទ</label>
                  <select class="form-control" v-model="form.gender">
                    <option value="MALE">ប្រុស (Male)</option>
                    <option value="FEMALE">ស្រី (Female)</option>
                  </select>
                </div>
                <div class="col-md-2 form-group">
                  <label class="form-label font-weight-bold">ស្ថានភាពគ្រួសារ</label>
                  <select class="form-control" v-model="form.marital_status">
                    <option value="SINGLE">នៅលីវ</option>
                    <option value="MARRIED">រៀបការរួច</option>
                    <option value="DIVORCED">ពោះម៉ាយ / មេម៉ាយ</option>
                  </select>
                </div>
                <div class="col-md-4 form-group">
                  <label class="form-label font-weight-bold">ថ្ងៃខែឆ្នាំកំណើត (DOB)</label>
                  <input type="date" class="form-control" v-model="form.dob" />
                </div>
                <div class="col-md-4 form-group">
                  <label class="form-label font-weight-bold">លេខទូរស័ព្ទ</label>
                  <input type="text" class="form-control" v-model="form.phone" placeholder="012 345 678" />
                </div>
                <div class="col-md-4 form-group">
                  <label class="form-label font-weight-bold">ទីកន្លែងកំណើត</label>
                  <input type="text" class="form-control" v-model="form.birth_place" placeholder="ខេត្ត/រាជធានីកំណើត" />
                </div>
                <div class="col-md-12 form-group">
                  <label class="form-label font-weight-bold">អាសយដ្ឋានបច្ចុប្បន្ន</label>
                  <textarea class="form-control" rows="2" v-model="form.current_address"
                    placeholder="ផ្ទះលេខ, ផ្លូវ, ភូមិ/ឃុំ, ស្រុក/ខណ្ឌ, ខេត្ត/រាជធានី..."></textarea>
                </div>
              </div>

              <!-- ផ្នែកទី ៤៖ អត្តសញ្ញាណប័ណ្ណ & លិខិតឆ្លងដែន -->
              <h6 class="text-success font-weight-bold mb-3 border-bottom pb-2 font-khmer">
                <i class="fas fa-passport mr-1"></i> ៤. អត្តសញ្ញាណប័ណ្ណ & លិខិតឆ្លងដែន
              </h6>
              <div class="row">
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">លេខអត្តសញ្ញាណប័ណ្ណ</label>
                  <input type="text" class="form-control" v-model="form.national_id_number"
                    placeholder="លេខ ៩ ឬ ១០ ខ្ទង់" />
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">ថ្ងៃផុតកំណត់អត្តសញ្ញាណប័ណ្ណ</label>
                  <input type="date" class="form-control" v-model="form.national_id_expired_date" />
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">លេខលិខិតឆ្លងដែន</label>
                  <input type="text" class="form-control" v-model="form.passport_number" placeholder="ឧ. N1234567" />
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">ថ្ងៃផុតកំណត់លិខិតឆ្លងដែន</label>
                  <input type="date" class="form-control" v-model="form.passport_expired_date" />
                </div>
              </div>
            </div>

            <div class="modal-footer bg-light py-2">
              <button type="button" class="btn btn-secondary font-khmer" @click="closeModal">បោះបង់</button>
              <button type="submit" class="btn btn-success font-khmer" :disabled="saving">
                <i class="fas fa-save mr-1"></i> {{ saving ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកទិន្នន័យ' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue';
import Swal from 'sweetalert2';
import {
  apiGetUsers,
  apiReadUser,
  apiCreateUser,
  apiUpdateUser,
  apiToggleUserStatus,
  apiDeleteUser,
} from '@/functions/api/user';
import { apiGetDepartments } from '@/functions/api/department';
import { apiGetOfficesByDepartment } from '@/functions/api/office';
import { apiGetPositions } from '@/functions/api/position';

const defaultAvatar = 'https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg';

const users = ref([]);
const departments = ref([]);
const departmentOffices = ref([]);
const positions = ref([]);
const loading = ref(false);
const saving = ref(false);
const showModal = ref(false);
const isEditMode = ref(false);
const currentUserId = ref(null);
const selectedFile = ref(null);
const imagePreviewUrl = ref(null);

const filters = reactive({
  keyword: '',
  employee_type: '',
  status: '',
  level: '',
});

const pagination = reactive({
  currentPage: 1,
  lastPage: 1,
  perPage: 15,
  total: 0,
});

const defaultFormData = {
  name: '',
  name_kh: '',
  name_en: '',
  email: '',
  password: '',
  level: 'USER',
  status: 'ENABLED',
  department_id: '',
  office_id: '',
  position_id: '',
  employee_code: '',
  mef_card_number: '',
  employee_type: 'CIVIL_SERVICE',
  gender: 'MALE',
  marital_status: 'SINGLE',
  dob: '',
  birth_place: '',
  current_address: '',
  phone: '',
  national_id_number: '',
  national_id_expired_date: '',
  passport_number: '',
  passport_expired_date: '',
};

const form = reactive({ ...defaultFormData });

let searchTimeout = null;

const getFullImageUrl = (path) => {
  if (!path) return defaultAvatar;

  if (
    path.startsWith('http://') ||
    path.startsWith('https://') ||
    path.startsWith('blob:') ||
    path.startsWith('data:')
  ) {
    return path;
  }

  const backendBase = (import.meta.env.VITE_APP_API_URL || 'http://localhost:8000')
    .replace(/\/api\/?$/, '');

  let cleanPath = path.replace(/^\//, '');

  if (cleanPath.includes('users/profile-images/') && !cleanPath.includes('thumbnails/')) {
    cleanPath = cleanPath.replace('users/profile-images/', 'users/profile-images/thumbnails/');
  }

  if (!cleanPath.startsWith('storage/') && !cleanPath.startsWith('uploads/')) {
    cleanPath = `storage/${cleanPath}`;
  }

  return `${backendBase}/${cleanPath}`;
};

const onImageError = (event) => {
  event.target.src = defaultAvatar;
};

const onFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedFile.value = file;
    imagePreviewUrl.value = URL.createObjectURL(file);
  }
};

const formatGender = (gender) => {
  if (!gender) return '---';
  const g = String(gender).trim().toUpperCase();
  if (g === 'MALE' || g === 'M') return 'ប្រុស';
  if (g === 'FEMALE' || g === 'F') return 'ស្រី';
  return gender;
};

const getGenderBadge = (gender) => {
  if (!gender) return 'text-muted';
  const g = String(gender).trim().toUpperCase();
  if (g === 'MALE' || g === 'M') return 'text-primary font-weight-500';
  if (g === 'FEMALE' || g === 'F') return 'text-danger font-weight-500';
  return 'text-muted';
};

const formatEmployeeType = (type) => {
  switch (type) {
    case 'CIVIL_SERVICE':
      return 'មន្ត្រីរាជការស៊ីវិល';
    case 'STATUTORY':
      return 'មន្ត្រីលក្ខន្តិកៈ';
    case 'CONTRACT':
      return 'មន្ត្រីកិច្ចសន្យា';
    case 'OTHER':
      return 'ផ្សេងៗ';
    default:
      return type || '---';
  }
};

const getEmployeeTypeBadge = (type) => {
  switch (type) {
    case 'CIVIL_SERVICE':
      return 'badge badge-primary';
    case 'STATUTORY':
      return 'badge badge-info';
    case 'CONTRACT':
      return 'badge badge-warning text-dark';
    case 'OTHER':
      return 'badge badge-secondary';
    default:
      return 'badge badge-light border';
  }
};

const fetchDropdowns = async () => {
  try {
    const [deptRes, posRes] = await Promise.allSettled([
      apiGetDepartments(),
      apiGetPositions(),
    ]);
    if (deptRes.status === 'fulfilled') {
      departments.value = deptRes.value.data.data || deptRes.value.data || [];
    }
    if (posRes.status === 'fulfilled') {
      positions.value = posRes.value.data.data || posRes.value.data || [];
    }
  } catch (error) {
    console.error('Error fetching dropdowns:', error);
  }
};

const onDepartmentChange = async () => {
  form.office_id = '';
  departmentOffices.value = [];
  if (form.department_id) {
    try {
      const res = await apiGetOfficesByDepartment(form.department_id);
      departmentOffices.value = res.data.data || res.data || [];
    } catch (error) {
      console.error('Error fetching offices:', error);
    }
  }
};

const fetchUsers = async (page = 1) => {
  loading.value = true;
  try {
    const res = await apiGetUsers({
      page,
      per_page: pagination.perPage,
      keyword: filters.keyword,
      employee_type: filters.employee_type,
      status: filters.status,
      level: filters.level,
    });

    users.value = res.data.users || res.data.data || [];

    if (res.data.meta) {
      pagination.currentPage = res.data.meta.current_page;
      pagination.lastPage = res.data.meta.last_page;
      pagination.perPage = res.data.meta.per_page;
      pagination.total = res.data.meta.total;
    }
  } catch (error) {
    Swal.fire('Error', error.response?.data?.message || 'មិនអាចទាញយកបញ្ជីមន្ត្រីបានឡើយ', 'error');
  } finally {
    loading.value = false;
  }
};

const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchUsers(1);
  }, 400);
};

const resetFilters = () => {
  filters.keyword = '';
  filters.employee_type = '';
  filters.status = '';
  filters.level = '';
  fetchUsers(1);
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.lastPage) {
    fetchUsers(page);
  }
};

const openCreateModal = () => {
  isEditMode.value = false;
  currentUserId.value = null;
  selectedFile.value = null;
  imagePreviewUrl.value = null;
  departmentOffices.value = [];
  Object.assign(form, defaultFormData);
  showModal.value = true;
};

const openEditModal = async (user) => {
  isEditMode.value = true;
  currentUserId.value = user.id;
  selectedFile.value = null;

  try {
    const res = await apiReadUser(user.id);
    const u = res.data.user || res.data;

    imagePreviewUrl.value = getFullImageUrl(u.profile_thumbnail || u.profile_image);

    if (u.department_id) {
      try {
        const offRes = await apiGetOfficesByDepartment(u.department_id);
        departmentOffices.value = offRes.data.data || offRes.data || [];
      } catch (err) {
        departmentOffices.value = [];
      }
    } else {
      departmentOffices.value = [];
    }

    let mappedGender = 'MALE';
    if (u.gender) {
      const g = String(u.gender).trim().toUpperCase();
      if (g === 'FEMALE' || g === 'F') {
        mappedGender = 'FEMALE';
      } else {
        mappedGender = 'MALE';
      }
    }

    let mappedMarital = 'SINGLE';
    if (u.marital_status) {
      const ms = String(u.marital_status).trim().toUpperCase();
      if (['SINGLE', 'MARRIED', 'DIVORCED'].includes(ms)) {
        mappedMarital = ms;
      }
    }

    Object.assign(form, {
      name: u.name || '',
      name_kh: u.name_kh || u.name || '',
      name_en: u.name_en || '',
      email: u.email || '',
      password: '',
      level: u.level || 'USER',
      status: u.status || 'ENABLED',
      department_id: u.department_id ? Number(u.department_id) : '',
      office_id: u.office_id ? Number(u.office_id) : '',
      position_id: u.position_id ? Number(u.position_id) : '',
      employee_code: u.employee_code || '',
      mef_card_number: u.mef_card_number || '',
      employee_type: u.employee_type || 'CIVIL_SERVICE',
      gender: mappedGender,
      marital_status: mappedMarital,
      dob: u.dob ? String(u.dob).split('T')[0] : '',
      birth_place: u.birth_place || '',
      current_address: u.current_address || '',
      phone: u.phone || '',
      national_id_number: u.national_id_number || '',
      national_id_expired_date: u.national_id_expired_date ? String(u.national_id_expired_date).split('T')[0] : '',
      passport_number: u.passport_number || '',
      passport_expired_date: u.passport_expired_date ? String(u.passport_expired_date).split('T')[0] : '',
    });
    showModal.value = true;
  } catch (error) {
    Swal.fire('បរាជ័យ', error.response?.data?.message || 'មិនអាចទាញយកព័ត៌មានលម្អិតបានឡើយ', 'error');
  }
};

const closeModal = () => {
  showModal.value = false;
};

const submitForm = async () => {
  saving.value = true;
  form.name = form.name_kh || form.name_en;

  const payload = new FormData();

  Object.keys(form).forEach((key) => {
    const val = form[key];
    // កែសម្រួលត្រង់នេះ៖ ដក && val !== '' ចេញ
    if (val !== null && val !== undefined) {
      payload.append(key, val);
    }
  });

  if (selectedFile.value) {
    payload.append('profile_image', selectedFile.value);
  }

  try {
    if (isEditMode.value) {
      payload.append('_method', 'PUT');
      const res = await apiUpdateUser(currentUserId.value, payload);
      Swal.fire('ជោគជ័យ', res.data?.message || 'ព័ត៌មានមន្ត្រីត្រូវបានកែសម្រួលរួចរាល់', 'success');
    } else {
      const res = await apiCreateUser(payload);
      Swal.fire('ជោគជ័យ', res.data?.message || 'បានបញ្ចូលមន្ត្រីថ្មីដោយជោគជ័យ', 'success');
    }
    closeModal();
    fetchUsers(pagination.currentPage);
  } catch (error) {
    const errors = error.response?.data?.errors;
    let errorMsg = error.response?.data?.message || 'មានបញ្ហាក្នុងការរក្សាទុក';
    if (errors) {
      errorMsg = Object.values(errors).flat().join('<br>');
    }
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ',
      html: errorMsg,
    });
  } finally {
    saving.value = false;
  }
};

const toggleStatus = async (user) => {
  const nextStatus = user.status === 'ENABLED' ? 'DISABLED' : 'ENABLED';
  const actionText = nextStatus === 'ENABLED' ? 'បើកដំណើរការ' : 'ផ្អាកដំណើរការ';

  const result = await Swal.fire({
    title: `តើអ្នកចង់${actionText}គណនីនេះមែនទេ?`,
    text: `គណនី ${user.email} នឹងត្រូវប្តូរទៅជា ${nextStatus}`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: nextStatus === 'ENABLED' ? '#28a745' : '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: `យល់ព្រម ${actionText}`,
    cancelButtonText: 'បោះបង់',
  });

  if (result.isConfirmed) {
    try {
      await apiToggleUserStatus(user.id);
      user.status = nextStatus;
      Swal.fire('ជោគជ័យ', `គណនីត្រូវបាន${actionText}រួចរាល់`, 'success');
    } catch (error) {
      Swal.fire('បរាជ័យ', error.response?.data?.message || 'មិនអាចផ្លាស់ប្តូរស្ថានភាពបានឡើយ', 'error');
    }
  }
};

const removeUser = async (id) => {
  const result = await Swal.fire({
    title: 'តើអ្នកប្រាកដថាចង់លុបទិន្នន័យនេះ?',
    text: 'ទិន្នន័យដែលបានលុបមិនអាចទាញយកមកវិញបានទេ!',
    icon: 'error',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'យល់ព្រមលុប',
    cancelButtonText: 'បោះបង់',
  });

  if (result.isConfirmed) {
    try {
      await apiDeleteUser(id);
      Swal.fire('ជោគជ័យ', 'ទិន្នន័យត្រូវបានលុបរួចរាល់', 'success');
      fetchUsers(pagination.currentPage);
    } catch (error) {
      Swal.fire('បរាជ័យ', error.response?.data?.message || 'មិនអាចលុបទិន្នន័យបានឡើយ', 'error');
    }
  }
};

onMounted(() => {
  fetchDropdowns();
  fetchUsers();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Kantumruy+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap');

.khmer-layout,
.custom-table,
.modal-content,
.font-khmer {
  font-family: 'Kantumruy Pro', 'Battambang', -apple-system, BlinkMacSystemFont, sans-serif !important;
  font-size: 0.94rem;
  -webkit-font-smoothing: antialiased;
}

.khmer-page-title {
  font-family: 'Battambang', cursive, sans-serif !important;
  font-size: 1.45rem;
}

.name-khmer {
  font-family: 'Kantumruy Pro', sans-serif !important;
  font-weight: 600;
  color: #1b3d36;
}

.font-weight-500 {
  font-weight: 500;
}

.btn-dark-custom {
  background-color: #0c2b29;
  color: #ffffff;
  border-radius: 6px;
  font-weight: 500;
  border: none;
  padding: 7px 16px;
  transition: all 0.2s ease;
}

.btn-dark-custom:hover {
  background-color: #15423f;
  color: #ffffff;
}

.custom-table th {
  font-weight: 600;
  color: #374151;
  background-color: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  vertical-align: middle;
  padding: 12px 14px;
}

.custom-table td {
  vertical-align: middle;
  padding: 12px 14px;
  border-top: 1px solid #f1f5f9;
}

.avatar-img {
  width: 44px;
  height: 44px;
  object-fit: cover;
  border-color: #e5e7eb !important;
}

.action-buttons {
  display: inline-flex;
  gap: 6px;
  justify-content: center;
}

.btn-action {
  width: 32px;
  height: 32px;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  border: 1px solid transparent;
  transition: all 0.2s ease;
  font-size: 0.85rem;
}

.btn-edit {
  color: #0284c7;
  background-color: #f0f9ff;
  border-color: #bae6fd;
}

.btn-edit:hover {
  background-color: #0284c7;
  color: #ffffff;
}

.btn-disable {
  color: #d97706;
  background-color: #fffbeb;
  border-color: #fde68a;
}

.btn-disable:hover {
  background-color: #d97706;
  color: #ffffff;
}

.btn-enable {
  color: #16a34a;
  background-color: #f0fdf4;
  border-color: #bbf7d0;
}

.btn-enable:hover {
  background-color: #16a34a;
  color: #ffffff;
}

.btn-delete {
  color: #dc2626;
  background-color: #fef2f2;
  border-color: #fecaca;
}

.btn-delete:hover {
  background-color: #dc2626;
  color: #ffffff;
}

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
}

.modal-header {
  border-top-left-radius: calc(0.3rem - 1px);
  border-top-right-radius: calc(0.3rem - 1px);
}

.form-label {
  font-size: 0.9rem;
  margin-bottom: 0.35rem;
}
</style>