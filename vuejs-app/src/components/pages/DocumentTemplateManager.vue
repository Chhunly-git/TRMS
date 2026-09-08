<template>
  <div class="content-wrapper" style="min-height: 900px;">
    <!-- Header -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 font-khmer text-dark font-weight-bold">
              <i class="fas fa-file-invoice text-success mr-2"></i> គ្រប់គ្រងគំរូឯកសារ
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right font-khmer">
              <li class="breadcrumb-item">
                <router-link :to="{ name: 'dashboard' }">ទំព័រដើម</router-link>
              </li>
              <li class="breadcrumb-item active">គ្រប់គ្រងគំរូឯកសារ</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="content font-khmer">
      <div class="container-fluid">
        <!-- Top Toolbar Card -->
        <div class="card shadow-sm border-0 mb-4 rounded-lg">
          <div class="card-body">
            <div class="row align-items-center">
              <!-- Search Box -->
              <div class="col-md-4 mb-2 mb-md-0">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-right-0">
                      <i class="fas fa-search text-muted"></i>
                    </span>
                  </div>
                  <input
                    type="text"
                    class="form-control border-left-0"
                    placeholder="ស្វែងរកតាមចំណងជើង ឬឈ្មោះឯកសារ..."
                    v-model="searchQuery"
                    @input="onSearchInput"
                  />
                  <div class="input-group-append" v-if="searchQuery">
                    <button class="btn btn-outline-secondary" type="button" @click="clearSearch">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Category Filter -->
              <div class="col-md-3 mb-2 mb-md-0">
                <select class="form-control" v-model="selectedCategory" @change="fetchTemplates">
                  <option value="ALL">-- ប្រភេទឯកសារទាំងអស់ --</option>
                  <option value="LEAVE_REQUEST">គំរូស្នើសុំច្បាប់ (Leave Request)</option>
                  <option value="MISSION">គំរូបេសកកម្ម (Mission Request)</option>
                  <option value="RESIGNATION">គំរូលាលែង/ផ្ទេរការងារ (Resignation/Transfer)</option>
                  <option value="GENERAL_FORM">ទម្រង់បែបបទផ្សេងៗ (General Form)</option>
                  <option value="OTHER">ឯកសារផ្សេងៗ (Other)</option>
                </select>
              </div>

              <!-- Action Buttons -->
              <div class="col-md-5 text-md-right">
                <router-link :to="{ name: 'document-templates' }" class="btn btn-outline-info mr-2 shadow-sm">
                  <i class="fas fa-eye mr-1"></i> ទិដ្ឋភាពមន្ត្រី (User View)
                </router-link>
                <button class="btn btn-success shadow-sm" @click="openCreateModal">
                  <i class="fas fa-plus-circle mr-1"></i> បញ្ចូលគំរូឯកសារថ្មី
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Table Card -->
        <div class="card shadow-sm border-0 rounded-lg">
          <div class="card-header bg-white border-bottom-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
            <h5 class="card-title font-weight-bold text-dark m-0">
              <i class="fas fa-list text-primary mr-2"></i> បញ្ជីគំរូឯកសារ (សរុប៖ {{ totalItems }})
            </h5>
            <div class="card-tools">
              <button class="btn btn-tool" @click="fetchTemplates" title="ផ្ទុកឡើងវិញ">
                <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
              </button>
            </div>
          </div>

          <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead class="bg-light text-muted text-uppercase text-xs">
                <tr>
                  <th class="text-center" style="width: 60px;">ល.រ</th>
                  <th style="min-width: 220px;">ចំណងជើង និងការពិពណ៌នា</th>
                  <th style="min-width: 140px;">ប្រភេទ</th>
                  <th style="min-width: 180px;">ឯកសារភ្ជាប់</th>
                  <th style="min-width: 120px;">អ្នកបញ្ចូល</th>
                  <th style="min-width: 120px;">កាលបរិច្ឆេទ</th>
                  <th class="text-center" style="width: 150px;">សកម្មភាព</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="7" class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-success mr-2" role="status"></div>
                    កំពុងផ្ទុកទិន្នន័យ...
                  </td>
                </tr>

                <tr v-else-if="templates.length === 0">
                  <td colspan="7" class="text-center py-5 text-muted">
                    <i class="fas fa-folder-open fa-3x mb-3 text-secondary d-block"></i>
                    មិនមានទិន្នន័យគំរូឯកសារឡើយ
                  </td>
                </tr>

                <tr v-for="(item, index) in templates" :key="item.id" v-else>
                  <td class="text-center font-weight-bold text-secondary">
                    {{ (currentPage - 1) * perPage + index + 1 }}
                  </td>
                  <td>
                    <div class="font-weight-bold text-dark mb-1">
                      {{ item.title }}
                    </div>
                    <small class="text-muted d-block text-truncate" style="max-width: 320px;" :title="item.description">
                      {{ item.description || '--- គ្មានការពិពណ៌នា ---' }}
                    </small>
                  </td>
                  <td>
                    <span class="badge" :class="getCategoryBadgeClass(item.category)">
                      {{ getCategoryLabel(item.category) }}
                    </span>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <i :class="getFileIcon(item.file_name)" class="fa-2x mr-2"></i>
                      <div>
                        <div class="text-truncate font-weight-500" style="max-width: 180px;" :title="item.file_name">
                          {{ item.file_name || 'ឯកសារ' }}
                        </div>
                        <small class="text-muted">{{ item.file_size || '---' }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <small class="text-dark font-weight-500">
                      {{ item.creator?.name_kh || item.creator?.name || 'Admin' }}
                    </small>
                  </td>
                  <td>
                    <small class="text-muted">{{ formatDate(item.created_at) }}</small>
                  </td>
                  <td class="text-center">
                    <div class="btn-group btn-group-sm">
                      <a
                        :href="getFileDownloadUrl(item.file_path)"
                        target="_blank"
                        class="btn btn-outline-primary"
                        title="ទាញយក ឬមើលឯកសារ"
                        download
                      >
                        <i class="fas fa-download"></i>
                      </a>
                      <button
                        class="btn btn-outline-success"
                        @click="openEditModal(item)"
                        title="កែប្រែ"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                      <button
                        class="btn btn-outline-danger"
                        @click="confirmDelete(item)"
                        title="លុប"
                      >
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination Footer -->
          <div class="card-footer bg-white border-top d-flex flex-column flex-sm-row justify-content-between align-items-center py-3" v-if="totalPages > 1">
            <div class="text-muted small mb-2 mb-sm-0">
              ទំព័រទី <strong>{{ currentPage }}</strong> នៃ <strong>{{ totalPages }}</strong> (សរុប {{ totalItems }} ឯកសារ)
            </div>
            <ul class="pagination pagination-sm m-0">
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <button class="page-link" @click="changePage(currentPage - 1)">
                  <i class="fas fa-chevron-left"></i>
                </button>
              </li>
              <li
                class="page-item"
                v-for="page in visiblePages"
                :key="page"
                :class="{ active: currentPage === page, disabled: page === '...' }"
              >
                <button class="page-link" @click="page !== '...' && changePage(page)">
                  {{ page }}
                </button>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <button class="page-link" @click="changePage(currentPage + 1)">
                  <i class="fas fa-chevron-right"></i>
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Create / Edit Modal -->
    <div v-if="showModal" class="custom-modal-backdrop" @click.self="closeModal">
      <div class="modal-dialog modal-lg modal-dialog-centered font-khmer my-auto" role="document" style="width: 100%; max-width: 800px;">
        <div class="modal-content border-0 shadow">
          <div class="modal-header" :class="isEditMode ? 'bg-success text-white' : 'bg-primary text-white'">
            <h5 class="modal-title font-weight-bold" id="templateModalLabel">
              <i :class="isEditMode ? 'fas fa-edit' : 'fas fa-plus-circle'" class="mr-2"></i>
              {{ isEditMode ? 'កែប្រែគំរូឯកសារ' : 'បញ្ចូលគំរូឯកសារថ្មី' }}
            </h5>
            <button type="button" class="close text-white" @click="closeModal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <form @submit.prevent="saveTemplate">
            <div class="modal-body p-4">
              <!-- Title -->
              <div class="form-group">
                <label class="font-weight-bold">
                  ចំណងជើងឯកសារ <span class="text-danger">*</span>
                </label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.title"
                  placeholder="ឧ. ពាក្យសុំច្បាប់ឈប់សម្រាកប្រចាំឆ្នាំ (Annual Leave Form)"
                  required
                />
              </div>

              <!-- Category -->
              <div class="form-group">
                <label class="font-weight-bold">
                  ប្រភេទឯកសារ <span class="text-danger">*</span>
                </label>
                <select class="form-control" v-model="form.category" required>
                  <option value="LEAVE_REQUEST">គំរូស្នើសុំច្បាប់ (Leave Request)</option>
                  <option value="MISSION">គំរូបេសកកម្ម (Mission Request)</option>
                  <option value="RESIGNATION">គំរូលាលែង/ផ្ទេរការងារ (Resignation/Transfer)</option>
                  <option value="GENERAL_FORM">ទម្រង់បែបបទផ្សេងៗ (General Form)</option>
                  <option value="OTHER">ឯកសារផ្សេងៗ (Other)</option>
                </select>
              </div>

              <!-- Description -->
              <div class="form-group">
                <label class="font-weight-bold">ការពិពណ៌នាបន្ថែម</label>
                <textarea
                  class="form-control"
                  rows="3"
                  v-model="form.description"
                  placeholder="បញ្ចូលការណែនាំសង្ខេប ឬព័ត៌មានលម្អិតអំពីឯកសារនេះ..."
                ></textarea>
              </div>

              <!-- File Upload -->
              <div class="form-group">
                <label class="font-weight-bold">
                  ឯកសារភ្ជាប់ {{ isEditMode ? '(ទុកនៅទទេបើមិនចង់ប្តូរ)' : '' }}
                  <span class="text-danger" v-if="!isEditMode">*</span>
                </label>
                <div class="custom-file">
                  <input
                    type="file"
                    class="custom-file-input"
                    id="templateFile"
                    @change="onFileSelected"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,image/*"
                    :required="!isEditMode"
                  />
                  <label class="custom-file-label" for="templateFile">
                    {{ selectedFileName || 'ជ្រើសរើសឯកសារ (PDF, Word, Excel, Images...)' }}
                  </label>
                </div>
                <small class="form-text text-muted">
                  អនុញ្ញាត៖ PDF, DOC, DOCX, XLS, XLSX, PPT, ZIP, រូបភាព (ទំហំអតិបរមា 50MB)
                </small>

                <!-- Current file preview if in edit mode -->
                <div v-if="isEditMode && currentTemplate?.file_name && !selectedFile" class="mt-2 p-2 bg-light border rounded d-flex align-items-center">
                  <i :class="getFileIcon(currentTemplate.file_name)" class="fa-lg mr-2"></i>
                  <div class="flex-grow-1 text-truncate">
                    <span class="font-weight-500">{{ currentTemplate.file_name }}</span>
                    <small class="text-muted ml-2">({{ currentTemplate.file_size }})</small>
                  </div>
                  <a :href="getFileDownloadUrl(currentTemplate.file_path)" target="_blank" class="btn btn-xs btn-outline-info">
                    <i class="fas fa-eye"></i> មើលឯកសារ
                  </a>
                </div>
              </div>
            </div>

            <div class="modal-footer bg-light">
              <button type="button" class="btn btn-secondary shadow-sm" @click="closeModal">
                <i class="fas fa-times mr-1"></i> បោះបង់
              </button>
              <button type="submit" class="btn btn-success shadow-sm" :disabled="saving">
                <i class="fas fa-spinner fa-spin mr-1" v-if="saving"></i>
                <i class="fas fa-save mr-1" v-else></i>
                {{ isEditMode ? 'កែប្រែ' : 'រក្សាទុក' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import Swal from 'sweetalert2';
import {
  apiGetManageDocumentTemplates,
  apiCreateDocumentTemplate,
  apiUpdateDocumentTemplate,
  apiDeleteDocumentTemplate
} from '@/functions/api/documentTemplate';

// State
const loading = ref(false);
const saving = ref(false);
const templates = ref([]);
const totalItems = ref(0);
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(10);
const searchQuery = ref('');
const selectedCategory = ref('ALL');
let searchTimeout = null;

// Modal & Form State
const showModal = ref(false);
const isEditMode = ref(false);
const currentTemplate = ref(null);
const selectedFile = ref(null);
const selectedFileName = ref('');

const form = ref({
  title: '',
  category: 'LEAVE_REQUEST',
  description: ''
});

// Fetch templates
const fetchTemplates = async () => {
  loading.value = true;
  try {
    const params = {
      page: currentPage.value,
      per_page: perPage.value,
      category: selectedCategory.value,
      search: searchQuery.value.trim()
    };
    const res = await apiGetManageDocumentTemplates(params);
    if (res.data?.status === 'success') {
      const paged = res.data.data;
      templates.value = paged.data || [];
      totalItems.value = paged.total || 0;
      totalPages.value = paged.last_page || 1;
      currentPage.value = paged.current_page || 1;
    }
  } catch (err) {
    console.error('Failed to fetch templates:', err);
    Swal.fire('បរាជ័យ', 'មិនអាចទាញយកបញ្ជីគំរូឯកសារបានឡើយ', 'error');
  } finally {
    loading.value = false;
  }
};

// Search handling with debounce
const onSearchInput = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    currentPage.value = 1;
    fetchTemplates();
  }, 400);
};

const clearSearch = () => {
  searchQuery.value = '';
  currentPage.value = 1;
  fetchTemplates();
};

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    fetchTemplates();
  }
};

// Visible pagination pages generator
const visiblePages = computed(() => {
  const pages = [];
  const current = currentPage.value;
  const total = totalPages.value;

  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    pages.push(1);
    if (current > 3) pages.push('...');
    const start = Math.max(2, current - 1);
    const end = Math.min(total - 1, current + 1);
    for (let i = start; i <= end; i++) pages.push(i);
    if (current < total - 2) pages.push('...');
    pages.push(total);
  }
  return pages;
});

// Modal Operations
const openCreateModal = () => {
  isEditMode.value = false;
  currentTemplate.value = null;
  selectedFile.value = null;
  selectedFileName.value = '';
  form.value = {
    title: '',
    category: 'LEAVE_REQUEST',
    description: ''
  };
  showModal.value = true;
};

const openEditModal = (item) => {
  isEditMode.value = true;
  currentTemplate.value = item;
  selectedFile.value = null;
  selectedFileName.value = '';
  form.value = {
    title: item.title,
    category: item.category || 'LEAVE_REQUEST',
    description: item.description || ''
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const onFileSelected = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedFile.value = file;
    selectedFileName.value = file.name;
  } else {
    selectedFile.value = null;
    selectedFileName.value = '';
  }
};

// Save Template (Create or Update)
const saveTemplate = async () => {
  if (!form.value.title.trim()) {
    Swal.fire('សូមបញ្ចូលព័ត៌មាន', 'សូមបញ្ចូលចំណងជើងឯកសារ', 'warning');
    return;
  }

  if (!isEditMode.value && !selectedFile.value) {
    Swal.fire('សូមបញ្ចូលឯកសារ', 'សូមជ្រើសរើសឯកសារភ្ជាប់សម្រាប់គំរូនេះ', 'warning');
    return;
  }

  saving.value = true;
  const formData = new FormData();
  formData.append('title', form.value.title.trim());
  formData.append('category', form.value.category);
  formData.append('description', form.value.description ? form.value.description.trim() : '');

  if (selectedFile.value) {
    formData.append('file', selectedFile.value);
  }

  try {
    let res;
    if (isEditMode.value) {
      res = await apiUpdateDocumentTemplate(currentTemplate.value.id, formData);
    } else {
      res = await apiCreateDocumentTemplate(formData);
    }

    if (res.data?.status === 'success') {
      Swal.fire('ជោគជ័យ', res.data?.message || 'ប្រតិបត្តិការជោគជ័យ', 'success');
      closeModal();
      fetchTemplates();
    } else {
      Swal.fire('កំហុស', res.data?.message || 'មានបញ្ហាក្នុងការរក្សាទុក', 'error');
    }
  } catch (err) {
    console.error('Error saving template:', err);
    Swal.fire('កំហុស', err.response?.data?.message || 'បរាជ័យក្នុងការរក្សាទុកគំរូឯកសារ', 'error');
  } finally {
    saving.value = false;
  }
};

// Delete Template
const confirmDelete = (item) => {
  Swal.fire({
    title: 'តើលោកអ្នកពិតជាចង់លុប?',
    text: `គំរូឯកសារ "${item.title}" នឹងត្រូវលុបចេញពីប្រព័ន្ធ!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'បាទ/ចាស លុប',
    cancelButtonText: 'បោះបង់',
    reverseButtons: true
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const res = await apiDeleteDocumentTemplate(item.id);
        if (res.data?.status === 'success') {
          Swal.fire('បានលុប!', res.data?.message || 'គំរូឯកសារត្រូវបានលុបជោគជ័យ', 'success');
          fetchTemplates();
        }
      } catch (err) {
        console.error('Error deleting template:', err);
        Swal.fire('កំហុស', err.response?.data?.message || 'បរាជ័យក្នុងការលុបឯកសារ', 'error');
      }
    }
  });
};

// Helper: Format Date
const formatDate = (dateStr) => {
  if (!dateStr) return '---';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;
  return d.toLocaleDateString('km-KH', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  });
};

// Helper: Get File Download URL
const getFileDownloadUrl = (filePath) => {
  if (!filePath) return '#';
  if (filePath.startsWith('http://') || filePath.startsWith('https://')) return filePath;
  const baseUrl = (import.meta.env.VITE_APP_API_URL || 'http://localhost:8000').replace(/\/api\/?$/, '');
  return `${baseUrl}/storage/${filePath}`;
};

// Helper: File Icon
const getFileIcon = (fileName) => {
  if (!fileName) return 'fas fa-file text-secondary';
  const ext = fileName.split('.').pop().toLowerCase();
  if (ext === 'pdf') return 'fas fa-file-pdf text-danger';
  if (['doc', 'docx'].includes(ext)) return 'fas fa-file-word text-primary';
  if (['xls', 'xlsx'].includes(ext)) return 'fas fa-file-excel text-success';
  if (['ppt', 'pptx'].includes(ext)) return 'fas fa-file-powerpoint text-warning';
  if (['zip', 'rar', '7z'].includes(ext)) return 'fas fa-file-archive text-secondary';
  if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) return 'fas fa-file-image text-info';
  return 'fas fa-file-alt text-secondary';
};

// Helper: Category Labels & Badges
const getCategoryLabel = (category) => {
  switch (category) {
    case 'LEAVE_REQUEST':
      return 'គំរូស្នើសុំច្បាប់';
    case 'MISSION':
      return 'គំរូបេសកកម្ម';
    case 'RESIGNATION':
      return 'គំរូលាលែង/ផ្ទេរ';
    case 'GENERAL_FORM':
      return 'ទម្រង់ទូទៅ';
    case 'OTHER':
      return 'ផ្សេងៗ';
    default:
      return category || 'ទូទៅ';
  }
};

const getCategoryBadgeClass = (category) => {
  switch (category) {
    case 'LEAVE_REQUEST':
      return 'badge-primary';
    case 'MISSION':
      return 'badge-warning text-dark';
    case 'RESIGNATION':
      return 'badge-danger';
    case 'GENERAL_FORM':
      return 'badge-success';
    case 'OTHER':
      return 'badge-secondary';
    default:
      return 'badge-info';
  }
};

onMounted(() => {
  fetchTemplates();
});
</script>

<style scoped>
.font-khmer {
  font-family: 'Kantumruy Pro', 'Hanuman', 'Siemreap', sans-serif !important;
}
.font-weight-500 {
  font-weight: 500;
}
.table td, .table th {
  vertical-align: middle;
}
.badge {
  font-size: 85%;
  font-weight: 500;
  padding: 0.35em 0.6em;
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
  overflow-y: auto;
  padding: 1rem;
}
.modal-content {
  border-radius: 0.5rem;
}
.modal-header {
  border-top-left-radius: 0.5rem;
  border-top-right-radius: 0.5rem;
}
</style>
