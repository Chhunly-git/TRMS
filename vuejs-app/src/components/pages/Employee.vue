<!-- src/views/employees/EmployeeIndex.vue -->
<template>
  <div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>គ្រប់គ្រងទិន្នន័យបុគ្គលិក</h1>
          </div>
          <div class="col-sm-6 text-right">
            <button class="btn btn-primary" @click="openModal('CREATE')">
              <i class="fas fa-plus mr-1"></i> បង្កើតបុគ្គលិកថ្មី
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Search & Filter Card -->
        <div class="card card-outline card-primary">
          <div class="card-body">
            <div class="row">
              <div class="col-md-4 form-group">
                <input
                  type="text"
                  class="form-control"
                  v-model="filters.search"
                  placeholder="ស្វែងរកតាមឈ្មោះ, អត្តលេខ, ទូរស័ព្ទ..."
                  @keyup.enter="fetchEmployees(1)"
                />
              </div>
              <div class="col-md-3 form-group">
                <select class="form-control" v-model="filters.employee_type" @change="fetchEmployees(1)">
                  <option value="">-- គ្រប់ប្រភេទបុគ្គលិក --</option>
                  <option value="CIVIL_SERVICE">មន្ត្រីមុខងារសាធារណៈ</option>
                  <option value="STATUTORY">មន្ត្រីលក្ខន្តិកៈ</option>
                  <option value="CONTRACT">បុគ្គលិកជាប់កិច្ចសន្យា</option>
                  <option value="OTHER">ផ្សេងទៀត</option>
                </select>
              </div>
              <div class="col-md-3 form-group">
                <select class="form-control" v-model="filters.status" @change="fetchEmployees(1)">
                  <option value="">-- គ្រប់ស្ថានភាព --</option>
                  <option value="ACTIVE">ACTIVE</option>
                  <option value="DISABLED">DISABLED</option>
                  <option value="RETIRED">RETIRED</option>
                  <option value="RESIGNED">RESIGNED</option>
                </select>
              </div>
              <div class="col-md-2 form-group">
                <button class="btn btn-secondary btn-block" @click="resetFilters">
                  <i class="fas fa-undo mr-1"></i> កំណត់ឡើងវិញ
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Data Table Card -->
        <div class="card">
          <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped text-nowrap">
              <thead>
                <tr>
                  <th>#</th>
                  <th>រូបថត</th>
                  <th>ឈ្មោះខ្មែរ / ឡាតាំង</th>
                  <th>ប្រភេទ</th>
                  <th>អង្គភាព / តួនាទី</th>
                  <th>ទំនាក់ទំនង</th>
                  <th>ស្ថានភាព</th>
                  <th class="text-center">សកម្មភាព</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="8" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                  </td>
                </tr>
                <tr v-else-if="employees.length === 0">
                  <td colspan="8" class="text-center py-4 text-muted">មិនមានទិន្នន័យឡើយ</td>
                </tr>
                <tr v-for="(emp, index) in employees" :key="emp.id">
                  <td>{{ pagination.from + index }}</td>
                  <td>
                    <img
                      :src="emp.user?.profile_image || '/img/default-avatar.png'"
                      alt="Avatar"
                      class="img-circle img-size-32 mr-2"
                      style="object-fit: cover; width: 35px; height: 35px;"
                    />
                  </td>
                  <td>
                    <strong>{{ emp.name_kh }}</strong>
                    <div class="text-muted small">{{ emp.name_en || '-' }}</div>
                  </td>
                  <td>
                    <span class="badge badge-info">{{ formatEmployeeType(emp.employee_type) }}</span>
                  </td>
                  <td>
                    <div>{{ emp.department?.name_kh || '-' }}</div>
                    <small class="text-muted">{{ emp.position?.title_kh || emp.position?.name_kh || '-' }}</small>
                  </td>
                  <td>
                    <div><i class="fas fa-phone-alt mr-1 text-xs"></i>{{ emp.phone || '-' }}</div>
                    <small class="text-muted"><i class="fas fa-envelope mr-1 text-xs"></i>{{ emp.email || '-' }}</small>
                  </td>
                  <td>
                    <span :class="getStatusBadge(emp.status)">{{ emp.status }}</span>
                  </td>
                  <td class="text-center">
                    <button class="btn btn-xs btn-info mr-1" title="មើលព័ត៌មាន" @click="viewEmployee(emp)">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-xs btn-warning mr-1" title="កែសម្រួល" @click="openModal('EDIT', emp)">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button
                      class="btn btn-xs mr-1"
                      :class="emp.status === 'ACTIVE' ? 'btn-secondary' : 'btn-success'"
                      :title="emp.status === 'ACTIVE' ? 'Disable' : 'Enable'"
                      @click="handleToggleStatus(emp.id)"
                    >
                      <i :class="emp.status === 'ACTIVE' ? 'fas fa-ban' : 'fas fa-check'"></i>
                    </button>
                    <button class="btn btn-xs btn-danger" title="លុប" @click="handleDelete(emp.id)">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination Footer -->
          <div class="card-footer clearfix" v-if="pagination.total > pagination.per_page">
            <div class="float-left">
              បង្ហាញពី {{ pagination.from }} ដល់ {{ pagination.to }} នៃ {{ pagination.total }} ជួរ
            </div>
            <ul class="pagination pagination-sm m-0 float-right">
              <li class="page-item" :class="{ disabled: !pagination.prev_page_url }">
                <a class="page-link" href="#" @click.prevent="fetchEmployees(pagination.current_page - 1)">&laquo;</a>
              </li>
              <li
                v-for="page in pagination.last_page"
                :key="page"
                class="page-item"
                :class="{ active: pagination.current_page === page }"
              >
                <a class="page-link" href="#" @click.prevent="fetchEmployees(page)">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: !pagination.next_page_url }">
                <a class="page-link" href="#" @click.prevent="fetchEmployees(pagination.current_page + 1)">&raquo;</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Create/Edit Modal (Vue Controlled) -->
    <div
      class="modal fade"
      :class="{ 'show d-block': isModalOpen }"
      tabindex="-1"
      role="dialog"
      v-if="isModalOpen"
      style="background: rgba(0,0,0,0.5);"
    >
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ modalMode === 'CREATE' ? 'បង្កើតបុគ្គលិកថ្មី' : 'កែសម្រួលព័ត៌មានបុគ្គលិក' }}</h5>
            <button type="button" class="close" @click="closeModal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form @submit.prevent="handleSubmit">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6 form-group">
                  <label>ប្រភេទបុគ្គលិក <span class="text-danger">*</span></label>
                  <select class="form-control" v-model="form.employee_type" required>
                    <option value="CIVIL_SERVICE">មន្ត្រីមុខងារសាធារណៈ</option>
                    <option value="STATUTORY">មន្ត្រីលក្ខន្តិកៈ</option>
                    <option value="CONTRACT">បុគ្គលិកជាប់កិច្ចសន្យា</option>
                    <option value="OTHER">ផ្សេងទៀត</option>
                  </select>
                </div>
                <div class="col-md-6 form-group">
                  <label>ភេទ <span class="text-danger">*</span></label>
                  <select class="form-control" v-model="form.gender" required>
                    <option value="ប្រុស">ប្រុស</option>
                    <option value="ស្រី">ស្រី</option>
                  </select>
                </div>
                <div class="col-md-6 form-group">
                  <label>ឈ្មោះជាភាសាខ្មែរ <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" v-model="form.name_kh" required />
                </div>
                <div class="col-md-6 form-group">
                  <label>ឈ្មោះជាអក្សរឡាតាំង</label>
                  <input type="text" class="form-control" v-model="form.name_en" />
                </div>
                <div class="col-md-6 form-group">
                  <label>លេខទូរស័ព្ទ</label>
                  <input type="text" class="form-control" v-model="form.phone" />
                </div>
                <div class="col-md-6 form-group">
                  <label>អ៊ីម៉ែល</label>
                  <input type="email" class="form-control" v-model="form.email" />
                </div>
                <div class="col-md-6 form-group">
                  <label>លេខអត្តសញ្ញាណប័ណ្ណ</label>
                  <input type="text" class="form-control" v-model="form.national_id" />
                </div>
                <div class="col-md-6 form-group">
                  <label>ថ្ងៃខែឆ្នាំកំណើត</label>
                  <input type="date" class="form-control" v-model="form.dob" />
                </div>
              </div>

              <!-- Create Linked User Section (Only for CREATE mode) -->
              <div v-if="modalMode === 'CREATE'" class="border-top pt-3 mt-2">
                <div class="custom-control custom-checkbox mb-2">
                  <input
                    type="checkbox"
                    class="custom-control-input"
                    id="createUserCheck"
                    v-model="form.create_user_account"
                  />
                  <label class="custom-control-label" for="createUserCheck">បង្កើត User Account សម្រាប់ Login</label>
                </div>
                <div v-if="form.create_user_account" class="row">
                  <div class="col-md-6 form-group">
                    <label>ពាក្យសម្ងាត់ (Password) <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" v-model="form.user_password" required />
                  </div>
                  <div class="col-md-6 form-group">
                    <label>កម្រិតសិទ្ធិ (Level)</label>
                    <select class="form-control" v-model="form.user_level">
                      <option value="USER">USER</option>
                      <option value="ADMIN">ADMIN</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeModal">បោះបង់</button>
              <button type="submit" class="btn btn-primary" :disabled="submitting">
                <i v-if="submitting" class="fas fa-spinner fa-spin mr-1"></i> រក្សាទុក
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Details Modal (Vue Controlled) -->
    <div
      class="modal fade"
      :class="{ 'show d-block': isViewModalOpen }"
      tabindex="-1"
      role="dialog"
      v-if="isViewModalOpen"
      style="background: rgba(0,0,0,0.5);"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">ព័ត៌មានលម្អិតបុគ្គលិក</h5>
            <button type="button" class="close" @click="isViewModalOpen = false">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body" v-if="selectedEmployee">
            <div class="text-center mb-3">
              <img
                :src="selectedEmployee.user?.profile_image || '/img/default-avatar.png'"
                class="img-circle elevation-2"
                style="width: 80px; height: 80px; object-fit: cover;"
                alt="Avatar"
              />
              <h5 class="mt-2 mb-0">{{ selectedEmployee.name_kh }}</h5>
              <span class="text-muted">{{ selectedEmployee.name_en || '-' }}</span>
            </div>
            <ul class="list-group list-group-unbordered mb-3">
              <li class="list-group-item">
                <b>ប្រភេទបុគ្គលិក:</b> <span class="float-right">{{ formatEmployeeType(selectedEmployee.employee_type) }}</span>
              </li>
              <li class="list-group-item">
                <b>ភេទ:</b> <span class="float-right">{{ selectedEmployee.gender }}</span>
              </li>
              <li class="list-group-item">
                <b>ថ្ងៃខែឆ្នាំកំណើត:</b> <span class="float-right">{{ selectedEmployee.dob || '-' }}</span>
              </li>
              <li class="list-group-item">
                <b>ទូរស័ព្ទ:</b> <span class="float-right">{{ selectedEmployee.phone || '-' }}</span>
              </li>
              <li class="list-group-item">
                <b>អ៊ីម៉ែល:</b> <span class="float-right">{{ selectedEmployee.email || '-' }}</span>
              </li>
              <li class="list-group-item">
                <b>អត្តសញ្ញាណប័ណ្ណ:</b> <span class="float-right">{{ selectedEmployee.national_id || '-' }}</span>
              </li>
              <li class="list-group-item">
                <b>ស្ថានភាព:</b> <span class="float-right" :class="getStatusBadge(selectedEmployee.status)">{{ selectedEmployee.status }}</span>
              </li>
            </ul>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="isViewModalOpen = false">បិទ</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import {
  apiGetEmployees,
  apiCreateEmployee,
  apiUpdateEmployee,
  apiToggleEmployeeStatus,
  apiDeleteEmployee
} from '@/functions/api/employee';

// State
const loading = ref(false);
const submitting = ref(false);
const employees = ref([]);
const modalMode = ref('CREATE');
const currentEditId = ref(null);
const isModalOpen = ref(false);
const isViewModalOpen = ref(false);
const selectedEmployee = ref(null);

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0,
  per_page: 15,
  prev_page_url: null,
  next_page_url: null
});

const filters = reactive({
  search: '',
  employee_type: '',
  status: ''
});

const form = reactive({
  employee_type: 'CIVIL_SERVICE',
  gender: 'ប្រុស',
  name_kh: '',
  name_en: '',
  phone: '',
  email: '',
  national_id: '',
  dob: '',
  create_user_account: false,
  user_password: '',
  user_level: 'USER'
});

// Lifecycle
onMounted(() => {
  fetchEmployees();
});

// Methods
async function fetchEmployees(page = 1) {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.per_page,
      search: filters.search || undefined,
      employee_type: filters.employee_type || undefined,
      status: filters.status || undefined
    };

    const res = await apiGetEmployees(params);
    const paginatedData = res.data.data;

    employees.value = paginatedData.data || [];
    pagination.current_page = paginatedData.current_page || 1;
    pagination.last_page = paginatedData.last_page || 1;
    pagination.from = paginatedData.from || 0;
    pagination.to = paginatedData.to || 0;
    pagination.total = paginatedData.total || 0;
    pagination.prev_page_url = paginatedData.prev_page_url;
    pagination.next_page_url = paginatedData.next_page_url;
  } catch (error) {
    console.error('Failed to fetch employees:', error);
  } finally {
    loading.value = false;
  }
}

function resetFilters() {
  filters.search = '';
  filters.employee_type = '';
  filters.status = '';
  fetchEmployees(1);
}

function openModal(mode, employee = null) {
  modalMode.value = mode;
  if (mode === 'CREATE') {
    currentEditId.value = null;
    Object.assign(form, {
      employee_type: 'CIVIL_SERVICE',
      gender: 'ប្រុស',
      name_kh: '',
      name_en: '',
      phone: '',
      email: '',
      national_id: '',
      dob: '',
      create_user_account: false,
      user_password: '',
      user_level: 'USER'
    });
  } else if (mode === 'EDIT' && employee) {
    currentEditId.value = employee.id;
    Object.assign(form, {
      employee_type: employee.employee_type,
      gender: employee.gender,
      name_kh: employee.name_kh,
      name_en: employee.name_en,
      phone: employee.phone,
      email: employee.email,
      national_id: employee.national_id,
      dob: employee.dob,
      create_user_account: false
    });
  }
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
}

function viewEmployee(employee) {
  selectedEmployee.value = employee;
  isViewModalOpen.value = true;
}

async function handleSubmit() {
  submitting.value = true;
  try {
    if (modalMode.value === 'CREATE') {
      await apiCreateEmployee(form);
    } else {
      await apiUpdateEmployee(currentEditId.value, form);
    }
    closeModal();
    fetchEmployees(pagination.current_page);
  } catch (error) {
    console.error('Submission failed:', error);
    alert(error.response?.data?.message || 'ប្រតិបត្តិការបរាជ័យ');
  } finally {
    submitting.value = false;
  }
}

async function handleToggleStatus(id) {
  try {
    await apiToggleEmployeeStatus(id);
    fetchEmployees(pagination.current_page);
  } catch (error) {
    console.error('Status toggle failed:', error);
  }
}

async function handleDelete(id) {
  if (!confirm('តើអ្នកពិតជាចង់លុបទិន្នន័យបុគ្គលិកនេះមែនទេ?')) return;
  try {
    await apiDeleteEmployee(id);
    fetchEmployees(pagination.current_page);
  } catch (error) {
    console.error('Delete failed:', error);
  }
}

// Helpers
function formatEmployeeType(type) {
  const map = {
    CIVIL_SERVICE: 'មុខងារសាធារណៈ',
    STATUTORY: 'លក្ខន្តិកៈ',
    CONTRACT: 'ជាប់កិច្ចសន្យា',
    OTHER: 'ផ្សេងទៀត'
  };
  return map[type] || type;
}

function getStatusBadge(status) {
  const map = {
    ACTIVE: 'badge badge-success',
    DISABLED: 'badge badge-danger',
    RETIRED: 'badge badge-secondary',
    RESIGNED: 'badge badge-warning'
  };
  return map[status] || 'badge badge-light';
}
</script>