<template>
  <div class="content-wrapper" style="min-height: 1416px">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Departments (គ្រប់គ្រងនាយកដ្ឋាន)</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item">
                <router-link :to="{ name: 'dashboard' }">Home</router-link>
              </li>
              <li class="breadcrumb-item active">Departments</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <CustomTablePaginated 
          :title="'Departments List'" 
          :data="departments" 
          :columns="columns"
          v-model:currentPage="currentPage" 
          v-model:lastPage="lastPage" 
          v-model:total="total"
          v-model:pageSize="pageSize" 
          v-model:keyword="keyword" 
          @search-change="handleSearchChange" 
        />
      </div>
    </section>
  </div>

  <!-- Department Modal -->
  <div class="modal fade" ref="departmentModal" aria-modal="true" role="dialog">
    <form @submit.prevent="saveDepartment">
      <div class="modal-dialog modal-md">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{ department.id ? 'Edit Department' : 'Create Department' }}</h4>
            <button type="button" class="close" @click="hideModal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <!-- Code -->
            <div class="form-group">
              <label>Code (កូដសម្គាល់) <span class="text-danger">*</span></label>
              <input 
                type="text" 
                class="form-control" 
                :value="department.code"
                @input="handleInputCode"
                :class="{ 'is-invalid': !!departmentError.code }" 
                placeholder="ឧ. DIT, DAF..." 
              />
              <div class="invalid-feedback">{{ departmentError.code }}</div>
            </div>

            <!-- Name (KH) -->
            <div class="form-group">
              <label>Name (KH) <span class="text-danger">*</span></label>
              <input 
                type="text" 
                class="form-control" 
                :value="department.name_kh"
                @input="handleInputNameKH"
                :class="{ 'is-invalid': !!departmentError.name_kh }" 
                placeholder="ឧ. នាយកដ្ឋានព័ត៌មានវិទ្យា" 
              />
              <div class="invalid-feedback">{{ departmentError.name_kh }}</div>
            </div>

            <!-- Name (EN) -->
            <div class="form-group">
              <label>Name (EN)</label>
              <input 
                type="text" 
                class="form-control" 
                :value="department.name_en"
                @input="handleInputNameEN"
                :class="{ 'is-invalid': !!departmentError.name_en }" 
                placeholder="e.g. Department of Information Technology" 
              />
              <div class="invalid-feedback">{{ departmentError.name_en }}</div>
            </div>
          </div>
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" @click="hideModal">
              Close
            </button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import $ from "jquery";
import Swal from "sweetalert2";
import { CloseModal, LoadingModal, MessageModal } from "@/functions/swal";
import { onMounted, ref, h, reactive } from "vue";
import CustomTablePaginated from "@/components/includes/controls/CustomTablePaginated.vue";

// Import API Helpers ពី department.js
import {
  apiGetDepartments,
  apiCreateDepartment,
  apiUpdateDepartment,
  apiDeleteDepartment,
  apiReadDepartment,
} from "@/functions/api/department";

const departmentModal = ref(null);
const departments = ref([]);

// Pagination & Filter States
const currentPage = ref(1);
const pageSize = ref(25);
const total = ref(0);
const lastPage = ref(1);
const keyword = ref("");

const department = reactive({
  id: null,
  code: "",
  name_kh: "",
  name_en: "",
});

const departmentError = reactive({
  code: "",
  name_kh: "",
  name_en: "",
});

const defaultDepartment = JSON.parse(JSON.stringify(department));
const defaultDepartmentError = JSON.parse(JSON.stringify(departmentError));

function resetAllState() {
  Object.assign(department, defaultDepartment);
  Object.assign(departmentError, defaultDepartmentError);
}

// Clean text helper (កែប្រើ Regex)
function cleanText(str) {
  if (!str || typeof str !== "string") return str;
  return str.replace(/\s+/g, ' ').trim();
}

// Input Handlers (កែប្រើ Regex)
function handleInputCode(event) {
  const val = event.target.value.replace(/\s+/g, ' ').toUpperCase();
  department.code = val;
  event.target.value = val;
}

function handleInputNameKH(event) {
  const val = event.target.value.replace(/\s+/g, ' ');
  department.name_kh = val;
  event.target.value = val;
}

function handleInputNameEN(event) {
  const val = event.target.value.replace(/\s+/g, ' ');
  department.name_en = val;
  event.target.value = val;
}

// Columns Configuration
const columns = [
  {
    header: "ID",
    accessorKey: "id",
  },
  {
    header: "Code",
    accessorKey: "code",
    cell: ({ row: { original: { code } } }) => h("span", { class: "badge badge-info" }, code || "---"),
  },
  {
    header: "Name (KH)",
    accessorKey: "name_kh",
    cell: ({ row: { original: { name_kh } } }) => name_kh ? cleanText(name_kh) : "---",
  },
  {
    header: "Name (EN)",
    accessorKey: "name_en",
    cell: ({ row: { original: { name_en } } }) => name_en ? cleanText(name_en) : "---",
  },
  {
    header: "Offices Count",
    accessorKey: "offices_count",
    cell: ({ row: { original: { offices_count } } }) => h(
      "span", 
      { class: "badge badge-secondary" }, 
      `${offices_count || 0} ការិយាល័យ`
    ),
  },
  {
    accessorKey: "action",
    header: () => [
      "Actions",
      h(
        "button",
        {
          onClick: () => showModal(),
          class: "btn btn-sm btn-success ml-3",
        },
        "Create"
      ),
    ],
    cell: ({
      row: {
        original: { id },
      },
    }) => [
      h(
        "button",
        {
          onClick: () => removeDepartment(id),
          class: "btn btn-sm btn-outline-danger mx-1",
          title: "Delete",
        },
        h("i", { class: "fa fa-trash" })
      ),
      h(
        "button",
        {
          onClick: () => viewDepartment(id),
          class: "btn btn-sm btn-outline-secondary mx-1",
          title: "Edit",
        },
        h("i", { class: "fa fa-pen" })
      ),
    ],
    enableSorting: false,
  },
];

onMounted(async () => {
  $(departmentModal.value).on("hide.bs.modal", function () {
    resetAllState();
  });
  try {
    LoadingModal();
    await generateDepartments();
    return CloseModal();
  } catch (error) {
    return MessageModal({
      icon: "error",
      title: "Error",
      text: error.response?.data?.message || error.message,
    });
  }
});

async function generateDepartments() {
  const response = await apiGetDepartments();
  const dataList = Array.isArray(response.data) ? response.data : (response.data.data || []);

  departments.value = dataList.map((item) => ({
    ...item,
    code: cleanText(item.code),
    name_kh: cleanText(item.name_kh),
    name_en: cleanText(item.name_en),
  }));

  total.value = response.data.total || dataList.length;
  lastPage.value = response.data.last_page || 1;
}

async function handleSearchChange(searchKeyword) {
  if (!searchKeyword) {
    await generateDepartments();
    return;
  }
  const query = searchKeyword.toLowerCase();
  departments.value = departments.value.filter(
    (d) =>
      d.code?.toLowerCase().includes(query) ||
      d.name_kh?.toLowerCase().includes(query) ||
      d.name_en?.toLowerCase().includes(query)
  );
}

async function saveDepartment() {
  try {
    LoadingModal();

    const payload = {
      ...department,
      code: cleanText(department.code),
      name_kh: cleanText(department.name_kh),
      name_en: cleanText(department.name_en),
    };

    let response = null;
    if (department.id) {
      response = await apiUpdateDepartment(department.id, payload);
    } else {
      response = await apiCreateDepartment(payload);
    }

    await generateDepartments();
    hideModal();

    return MessageModal({
      icon: "success",
      title: "Success",
      text: response.data.message || "Department saved successfully",
    });
  } catch (error) {
    const { response } = error;
    if (!response) {
      return MessageModal({ icon: "error", title: "Error", text: error.message });
    }
    const { status, data } = response;
    if (status === 422) {
      Object.keys(departmentError).forEach((key) => {
        departmentError[key] = data.errors[key] ? data.errors[key][0] : "";
      });
      return CloseModal();
    }
    return MessageModal({ icon: "error", title: "Error", text: data.message });
  }
}

// កែសម្រួលការចាប់យកទិន្នន័យ (ID) ត្រង់នេះ
async function viewDepartment(id) {
  try {
    LoadingModal();
    const response = await apiReadDepartment(id);
    
    // បន្ថែមការឆែក response.data.data 
    const item = response.data.data || response.data;
    
    Object.assign(department, {
      id: item.id,
      code: cleanText(item.code),
      name_kh: cleanText(item.name_kh),
      name_en: cleanText(item.name_en),
    });
    
    showModal();
    return CloseModal();
  } catch (error) {
    return MessageModal({
      icon: "error",
      title: "Error",
      text: error.response?.data?.message || error.message,
    });
  }
}

async function removeDepartment(id) {
  Swal.fire({
    icon: "warning",
    title: "Delete Department",
    text: "Are you sure you want to delete this department?",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    confirmButtonText: "Yes, delete it!",
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        LoadingModal();
        const response = await apiDeleteDepartment(id);
        await generateDepartments();

        return MessageModal({
          icon: "success",
          title: "Success",
          text: response.data.message || "Department deleted successfully",
        });
      } catch (error) {
        return MessageModal({
          icon: "error",
          title: "Error",
          text: error.response?.data?.message || error.message,
        });
      }
    }
  });
}

function showModal() {
  $(departmentModal.value).modal("show");
}

function hideModal() {
  $(departmentModal.value).modal("hide");
}
</script>

<style scoped>
.content-wrapper,
.modal-content {
  font-family: 'Battambang', cursive, sans-serif !important;
}

.content-header h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #112d26;
}

.breadcrumb-item a {
  color: #1e4d41;
  text-decoration: none;
  font-weight: 500;
}

:deep(.card) {
  border: none !important;
  border-radius: 12px !important;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
  overflow: hidden;
}

:deep(.card-header) {
  background-color: #112d26 !important;
  color: #ffffff !important;
  font-weight: 600;
  border-bottom: none !important;
}

:deep(.table thead th) {
  background-color: #f4f7f6 !important;
  color: #112d26 !important;
  font-weight: 700;
  border-bottom: 2px solid #d2dedb !important;
  font-size: 0.85rem;
}

:deep(.table tbody tr:hover) {
  background-color: #f0f7f4 !important;
}

.btn-primary,
:deep(.btn-primary) {
  background-color: #1e4d41 !important;
  border-color: #1e4d41 !important;
  border-radius: 6px !important;
  font-weight: 500;
}

.btn-primary:hover,
:deep(.btn-primary:hover) {
  background-color: #112d26 !important;
  border-color: #112d26 !important;
}

.btn-success,
:deep(.btn-success) {
  background-color: #28a745 !important;
  border-radius: 6px !important;
  font-weight: 500;
}

.modal-content {
  border-radius: 12px !important;
  border: none !important;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2) !important;
}

.modal-header {
  background-color: #112d26 !important;
  color: #ffffff !important;
  border-top-left-radius: 12px !important;
  border-top-right-radius: 12px !important;
}

.modal-header .modal-title {
  font-size: 1.2rem;
  font-weight: 600;
}

.modal-header .close {
  color: #ffffff !important;
  opacity: 0.8;
}

.form-control {
  border-radius: 6px !important;
  border: 1px solid #ced4da;
  padding: 0.5rem 0.75rem;
}

.form-control:focus {
  border-color: #1e4d41 !important;
  box-shadow: 0 0 0 0.2rem rgba(30, 77, 65, 0.25) !important;
}

label {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.3rem;
}
</style>