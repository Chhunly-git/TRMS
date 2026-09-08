<template>
  <div class="content-wrapper" style="min-height: 900px;">
    <!-- Header -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 font-khmer text-dark font-weight-bold">
              <i class="fas fa-folder-open text-success mr-2"></i> គំរូឯកសារ និងទម្រង់បែបបទ
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right font-khmer">
              <li class="breadcrumb-item">
                <router-link :to="{ name: 'dashboard' }">ទំព័រដើម</router-link>
              </li>
              <li class="breadcrumb-item active">គំរូឯកសារ</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="content font-khmer">
      <div class="container-fluid">
        <!-- Hero Search & Filter Section -->
        <div class="card shadow-sm border-0 mb-4 rounded-lg bg-gradient-light">
          <div class="card-body p-4">
            <div class="row align-items-center">
              <div class="col-lg-7 mb-3 mb-lg-0">
                <h4 class="font-weight-bold text-dark mb-1">
                  ទាញយកគំរូឯកសារ និងទម្រង់បែបបទផ្លូវការ
                </h4>
                <p class="text-muted mb-0">
                  ជ្រើសរើស និងទាញយកគំរូឯកសារស្នើសុំច្បាប់ បេសកកម្ម និងទម្រង់បែបបទរដ្ឋបាលផ្សេងៗសម្រាប់បំពេញ
                </p>
              </div>

              <!-- Admin shortcut button if admin -->
              <div class="col-lg-5 text-lg-right" v-if="userStore.isAdmin">
                <router-link :to="{ name: 'manage-document-templates' }" class="btn btn-success shadow-sm">
                  <i class="fas fa-cog mr-1"></i> គ្រប់គ្រងគំរូឯកសារ (Admin)
                </router-link>
              </div>
            </div>

            <hr class="my-3 border-light">

            <!-- Search bar & View mode toggle -->
            <div class="row align-items-center">
              <div class="col-md-7 mb-2 mb-md-0">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-white border-right-0">
                      <i class="fas fa-search text-muted"></i>
                    </span>
                  </div>
                  <input
                    type="text"
                    class="form-control border-left-0"
                    placeholder="ស្វែងរកតាមឈ្មោះឯកសារ ឬពាក្យគន្លឹះ..."
                    v-model="searchQuery"
                    @input="onSearchInput"
                  />
                  <div class="input-group-append" v-if="searchQuery">
                    <button class="btn btn-outline-secondary bg-white" type="button" @click="clearSearch">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- View Switcher (Cards vs Table) -->
              <div class="col-md-5 text-md-right d-flex justify-content-md-end align-items-center">
                <span class="text-muted mr-2 small">ទិដ្ឋភាព៖</span>
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                  <button
                    class="btn btn-sm"
                    :class="viewMode === 'grid' ? 'btn-success' : 'btn-outline-secondary'"
                    @click="viewMode = 'grid'"
                    title="ទិដ្ឋភាពកាត"
                  >
                    <i class="fas fa-th-large mr-1"></i> កាត
                  </button>
                  <button
                    class="btn btn-sm"
                    :class="viewMode === 'table' ? 'btn-success' : 'btn-outline-secondary'"
                    @click="viewMode = 'table'"
                    title="ទិដ្ឋភាពតារាង"
                  >
                    <i class="fas fa-list mr-1"></i> តារាង
                  </button>
                </div>
              </div>
            </div>

            <!-- Category Filter Pills -->
            <div class="category-pills mt-3 d-flex flex-wrap">
              <button
                class="btn btn-sm mr-2 mb-2 rounded-pill px-3 transition"
                :class="selectedCategory === 'ALL' ? 'btn-success' : 'btn-light border text-muted'"
                @click="filterCategory('ALL')"
              >
                <i class="fas fa-layer-group mr-1"></i> ទាំងអស់ ({{ totalAllCount }})
              </button>
              <button
                class="btn btn-sm mr-2 mb-2 rounded-pill px-3 transition"
                :class="selectedCategory === 'LEAVE_REQUEST' ? 'btn-success' : 'btn-light border text-muted'"
                @click="filterCategory('LEAVE_REQUEST')"
              >
                <i class="fas fa-calendar-minus mr-1 text-primary"></i> គំរូស្នើសុំច្បាប់
              </button>
              <button
                class="btn btn-sm mr-2 mb-2 rounded-pill px-3 transition"
                :class="selectedCategory === 'MISSION' ? 'btn-success' : 'btn-light border text-muted'"
                @click="filterCategory('MISSION')"
              >
                <i class="fas fa-plane-departure mr-1 text-warning"></i> គំរូបេសកកម្ម
              </button>
              <button
                class="btn btn-sm mr-2 mb-2 rounded-pill px-3 transition"
                :class="selectedCategory === 'RESIGNATION' ? 'btn-success' : 'btn-light border text-muted'"
                @click="filterCategory('RESIGNATION')"
              >
                <i class="fas fa-user-times mr-1 text-danger"></i> គំរូលាលែង/ផ្ទេរ
              </button>
              <button
                class="btn btn-sm mr-2 mb-2 rounded-pill px-3 transition"
                :class="selectedCategory === 'GENERAL_FORM' ? 'btn-success' : 'btn-light border text-muted'"
                @click="filterCategory('GENERAL_FORM')"
              >
                <i class="fas fa-file-invoice mr-1 text-info"></i> ទម្រង់បែបបទផ្សេងៗ
              </button>
              <button
                class="btn btn-sm mr-2 mb-2 rounded-pill px-3 transition"
                :class="selectedCategory === 'OTHER' ? 'btn-success' : 'btn-light border text-muted'"
                @click="filterCategory('OTHER')"
              >
                <i class="fas fa-paperclip mr-1 text-secondary"></i> ផ្សេងៗ
              </button>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-success" style="width: 3rem; height: 3rem;" role="status"></div>
          <p class="text-muted mt-2">កំពុងផ្ទុកគំរូឯកសារ...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="templates.length === 0" class="card shadow-sm border-0 py-5 text-center rounded-lg">
          <div class="card-body">
            <i class="fas fa-folder-open fa-4x text-muted mb-3 d-block"></i>
            <h5 class="text-dark font-weight-bold">មិនមានឯកសារគំរូនៅក្នុងប្រភេទនេះឡើយ</h5>
            <p class="text-muted mb-0">សូមសាកល្បងស្វែងរកជាមួយពាក្យគន្លឹះផ្សេង ឬជ្រើសរើសប្រភេទឯកសារទាំងអស់</p>
          </div>
        </div>

        <!-- Content: Grid View -->
        <div v-else-if="viewMode === 'grid'" class="row">
          <div
            v-for="item in templates"
            :key="item.id"
            class="col-xl-4 col-lg-6 col-md-6 mb-4"
          >
            <div class="card h-100 shadow-sm border-0 rounded-lg template-card transition">
              <div class="card-body p-4 d-flex flex-column">
                <!-- Header: Icon & Category -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div class="file-icon-wrapper rounded p-2 bg-light shadow-xs">
                    <i :class="getFileIcon(item.file_name)" class="fa-3x"></i>
                  </div>
                  <span class="badge" :class="getCategoryBadgeClass(item.category)">
                    {{ getCategoryLabel(item.category) }}
                  </span>
                </div>

                <!-- Title & Description -->
                <h5 class="font-weight-bold text-dark mb-2 line-clamp-2" :title="item.title">
                  {{ item.title }}
                </h5>
                <p class="text-muted small mb-3 flex-grow-1 line-clamp-3" :title="item.description">
                  {{ item.description || 'គំរូឯកសារផ្លូវការសម្រាប់ទាញយក និងបំពេញ...' }}
                </p>

                <!-- File Info Footer -->
                <div class="border-top pt-3 mt-auto">
                  <div class="d-flex justify-content-between text-muted text-xs mb-3">
                    <span class="text-truncate mr-2" :title="item.file_name">
                      <i class="fas fa-file mr-1"></i> {{ item.file_name }}
                    </span>
                    <span class="font-weight-500 whitespace-nowrap">
                      {{ item.file_size || '---' }}
                    </span>
                  </div>

                  <!-- Actions -->
                  <div class="row no-gutters">
                    <div class="col-6 pr-1">
                      <a
                        :href="getFileDownloadUrl(item.file_path)"
                        target="_blank"
                        class="btn btn-outline-info btn-sm btn-block shadow-xs"
                        title="មើលឯកសារជាមុន"
                      >
                        <i class="fas fa-eye mr-1"></i> មើលជាមុន
                      </a>
                    </div>
                    <div class="col-6 pl-1">
                      <a
                        :href="getFileDownloadUrl(item.file_path)"
                        target="_blank"
                        class="btn btn-success btn-sm btn-block shadow-xs"
                        download
                        title="ទាញយកឯកសារនេះ"
                      >
                        <i class="fas fa-download mr-1"></i> ទាញយក
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Content: Table View -->
        <div v-else class="card shadow-sm border-0 rounded-lg mb-4">
          <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead class="bg-light text-muted text-uppercase text-xs">
                <tr>
                  <th class="text-center" style="width: 60px;">ល.រ</th>
                  <th style="min-width: 250px;">ចំណងជើង និងការពិពណ៌នា</th>
                  <th style="min-width: 140px;">ប្រភេទ</th>
                  <th style="min-width: 180px;">ឈ្មោះឯកសារ & ទំហំ</th>
                  <th style="min-width: 120px;">កាលបរិច្ឆេទ</th>
                  <th class="text-center" style="width: 160px;">សកម្មភាព</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in templates" :key="item.id">
                  <td class="text-center font-weight-bold text-secondary">
                    {{ (currentPage - 1) * perPage + index + 1 }}
                  </td>
                  <td>
                    <div class="font-weight-bold text-dark mb-1">
                      {{ item.title }}
                    </div>
                    <small class="text-muted d-block text-truncate" style="max-width: 350px;" :title="item.description">
                      {{ item.description || '---' }}
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
                        <div class="text-truncate font-weight-500" style="max-width: 200px;" :title="item.file_name">
                          {{ item.file_name || 'ឯកសារ' }}
                        </div>
                        <small class="text-muted">{{ item.file_size || '---' }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <small class="text-muted">{{ formatDate(item.created_at) }}</small>
                  </td>
                  <td class="text-center">
                    <div class="btn-group btn-group-sm">
                      <a
                        :href="getFileDownloadUrl(item.file_path)"
                        target="_blank"
                        class="btn btn-outline-info"
                        title="មើលឯកសារ"
                      >
                        <i class="fas fa-eye mr-1"></i> មើល
                      </a>
                      <a
                        :href="getFileDownloadUrl(item.file_path)"
                        target="_blank"
                        class="btn btn-success"
                        title="ទាញយក"
                        download
                      >
                        <i class="fas fa-download mr-1"></i> ទាញយក
                      </a>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center pb-4" v-if="totalPages > 1">
          <div class="text-muted small">
            បង្ហាញទំព័រទី <strong>{{ currentPage }}</strong> នៃ <strong>{{ totalPages }}</strong> (សរុប {{ totalItems }} ឯកសារ)
          </div>
          <ul class="pagination pagination-sm m-0 shadow-sm">
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
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { apiGetDocumentTemplates } from '@/functions/api/documentTemplate';
import { useUserStore } from '@/stores/user';

const userStore = useUserStore();

// State
const loading = ref(false);
const templates = ref([]);
const totalItems = ref(0);
const totalAllCount = ref(0);
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(12);
const searchQuery = ref('');
const selectedCategory = ref('ALL');
const viewMode = ref('grid'); // 'grid' or 'table'
let searchTimeout = null;

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
    const res = await apiGetDocumentTemplates(params);
    if (res.data?.status === 'success') {
      const paged = res.data.data;
      templates.value = paged.data || [];
      totalItems.value = paged.total || 0;
      totalPages.value = paged.last_page || 1;
      currentPage.value = paged.current_page || 1;

      if (selectedCategory.value === 'ALL' && !searchQuery.value) {
        totalAllCount.value = paged.total || 0;
      }
    }
  } catch (err) {
    console.error('Failed to fetch document templates:', err);
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
  }, 350);
};

const clearSearch = () => {
  searchQuery.value = '';
  currentPage.value = 1;
  fetchTemplates();
};

const filterCategory = (category) => {
  selectedCategory.value = category;
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
      return 'ទម្រង់បែបបទផ្សេងៗ';
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
.transition {
  transition: all 0.25s ease-in-out;
}
.template-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.badge {
  font-size: 85%;
  font-weight: 500;
  padding: 0.35em 0.6em;
}
.whitespace-nowrap {
  white-space: nowrap;
}
</style>
