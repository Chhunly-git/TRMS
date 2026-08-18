<template>
  <div class="content-wrapper" style="min-height: 1416px">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Divisions / Offices (គ្រប់គ្រងការិយាល័យ)</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item">
                <router-link :to="{ name: 'dashboard' }">Home</router-link>
              </li>
              <li class="breadcrumb-item active">Offices</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <CustomTablePaginated 
          :title="'Offices List'" 
          :data="offices" 
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

  <!-- Office Modal -->
  <div class="modal fade" ref="officeModal" aria-modal="true" role="dialog">
    <form @submit.prevent="saveOffice">
      <div class="modal-dialog modal-md">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{ office.id ? 'Edit Office' : 'Create Office' }}</h4>
            <button type="button" class="close" @click="hideModal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <!-- Department Selection -->
            <div class="form-group">
              <label>Department (នាយកដ្ឋាន) <span class="text-danger">*</span></label>
              <select 
                class="form-control" 
                v-model="office.department_id"
                :class="{ 'is-invalid': !!officeError.department_id }"
              >
                <option :value="null" disabled>-- សូមជ្រើសរើសនាយកដ្ឋាន --</option>
                <option 
                  v-for="dept in departments" 
                  :key="dept.id" 
                  :value="dept.id"
                >
                  {{ dept.name_kh }} ({{ dept.code }})
                </option>
              </select>
              <div class="invalid-feedback">{{ officeError.department_id }}</div>
            </div>

            <!-- Code -->
            <div class="form-group">
              <label>Code (កូដសម្គាល់) <span class="text-danger">*</span></label>
              <input 
                type="text" 
                class="form-control" 
                :value="office.code"
                @input="handleInputCode"
                :class="{ 'is-invalid': !!officeError.code }" 
                placeholder="ឧ. IT-DEV, HR-ADM..." 
              />
              <div class="invalid-feedback">{{ officeError.code }}</div>
            </div>

            <!-- Name (KH) -->
            <div class="form-group">
              <label>Name (KH) <span class="text-danger">*</span></label>
              <input 
                type="text" 
                class="form-control" 
                :value="office.name_kh"
                @input="handleInputNameKH"
                :class="{ 'is-invalid': !!officeError.name_kh }" 
                placeholder="ឧ. ការិយាល័យអភិវឌ្ឍន៍ប្រព័ន្ធ" 
              />
              <div class="invalid-feedback">{{ officeError.name_kh }}</div>
            </div>

            <!-- Name (EN) -->
            <div class="form-group">
              <label>Name (EN)</label>
              <input 
                type="text" 
                class="form-control" 
                :value="office.name_en"
                @input="handleInputNameEN"
                :class="{ 'is-invalid': !!officeError.name_en }" 
                placeholder="e.g. System Development Office" 
              />
              <div class="invalid-feedback">{{ officeError.name_en }}</div>
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

// Import API Helpers
import {
  apiGetOffices,
  apiCreateOffice,
  apiUpdateOffice,
  apiDeleteOffice,
} from "@/functions/api/office";
import { apiGetDepartments } from "@/functions/api/department";

const officeModal = ref(null);
const offices = ref([]);
const departments = ref([]);

// Pagination & Filter States
const currentPage = ref(1);
const pageSize = ref(25);
const total = ref(0);
const lastPage = ref(1);
const keyword = ref("");

const office = reactive({
  id: null,
  department_id: null,
  code: "",
  name_kh: "",
  name_en: "",
});

const officeError = reactive({
  department_id: "",
  code: "",
  name_kh: "",
  name_en: "",
});

const defaultOffice = JSON.parse(JSON.stringify(office));
const defaultOfficeError = JSON.parse(JSON.stringify(officeError));

function resetAllState() {
  Object.assign(office, defaultOffice);
  Object.assign(officeError, defaultOfficeError);
}

// Function សម្អាត \s
function cleanText(str) {
  if (!str || typeof str !== "string") return str;
  return str.replaceAll('\\s', ' ').trim();
}

// Input Handlers ការពារ \s ពេលវាយ
function handleInputCode(event) {
  const val = event.target.value.replaceAll('\\s', ' ').toUpperCase();
  office.code = val;
  event.target.value = val;
}

function handleInputNameKH(event) {
  const val = event.target.value.replaceAll('\\s', ' ');
  office.name_kh = val;
  event.target.value = val;
}

function handleInputNameEN(event) {
  const val = event.target.value.replaceAll('\\s', ' ');
  office.name_en = val;
  event.target.value = val;
}

// Table Columns configuration
const columns = [
  {
    header: "ID",
    accessorKey: "id",
  },
  {
    header: "Department",
    accessorKey: "department.name_kh",
    cell: ({ row: { original: { department } } }) => 
      department ? h("span", { class: "badge badge-success font-weight-normal px-2 py-1" }, cleanText(department.name_kh)) : "---",
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
          onClick: () => removeOffice(id),
          class: "btn btn-sm btn-outline-danger mx-1",
          title: "Delete",
        },
        h("i", { class: "fa fa-trash" })
      ),
      h(
        "button",
        {
          onClick: () => viewOffice(id),
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
  $(officeModal.value).on("hide.bs.modal", function () {
    resetAllState();
  });
  try {
    LoadingModal();
    await Promise.all([generateOffices(), loadDepartments()]);
    return CloseModal();
  } catch (error) {
    return MessageModal({
      icon: "error",
      title: "Error",
      text: error.response?.data?.message || error.message,
    });
  }
});

// Fetch Dropdown Departments
async function loadDepartments() {
  const response = await apiGetDepartments();
  departments.value = Array.isArray(response.data) ? response.data : (response.data.data || []);
}

// Fetch Offices list
async function generateOffices() {
  const response = await apiGetOffices();
  const dataList = Array.isArray(response.data) ? response.data : (response.data.data || []);

  offices.value = dataList.map(item => ({
    ...item,
    code: cleanText(item.code),
    name_kh: cleanText(item.name_kh),
    name_en: cleanText(item.name_en),
    department: item.department ? {
      ...item.department,
      name_kh: cleanText(item.department.name_kh),
    } : null,
  }));

  total.value = response.data.total || dataList.length;
  lastPage.value = response.data.last_page || 1;
}

// Client-side quick search
async function handleSearchChange(searchKeyword) {
  if (!searchKeyword) {
    await generateOffices();
    return;
  }
  const query = searchKeyword.toLowerCase();
  offices.value = offices.value.filter(
    (o) =>
      o.code?.toLowerCase().includes(query) ||
      o.name_kh?.toLowerCase().includes(query) ||
      o.name_en?.toLowerCase().includes(query) ||
      o.department?.name_kh?.toLowerCase().includes(query)
  );
}

// Save / Update Office
async function saveOffice() {
  try {
    LoadingModal();

    const payload = {
      ...office,
      code: cleanText(office.code),
      name_kh: cleanText(office.name_kh),
      name_en: cleanText(office.name_en),
    };

    let response = null;
    if (office.id) {
      response = await apiUpdateOffice(office.id, payload);
    } else {
      response = await apiCreateOffice(payload);
    }

    await generateOffices();
    hideModal();

    return MessageModal({
      icon: "success",
      title: "Success",
      text: response.data.message || "Office saved successfully",
    });
  } catch (error) {
    const { response } = error;
    if (!response) {
      return MessageModal({ icon: "error", title: "Error", text: error.message });
    }
    const { status, data } = response;
    if (status === 422) {
      Object.keys(officeError).forEach((key) => {
        officeError[key] = data.errors[key] ? data.errors[key][0] : "";
      });
      return CloseModal();
    }
    return MessageModal({ icon: "error", title: "Error", text: data.message });
  }
}

// View / Edit Office
function viewOffice(id) {
  const found = offices.value.find((o) => o.id === id);
  if (found) {
    Object.assign(office, {
      id: found.id,
      department_id: found.department_id,
      code: cleanText(found.code),
      name_kh: cleanText(found.name_kh),
      name_en: cleanText(found.name_en),
    });
    showModal();
  }
}

// Delete Office
async function removeOffice(id) {
  Swal.fire({
    icon: "warning",
    title: "Delete Office",
    text: "Are you sure you want to delete this office?",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    confirmButtonText: "Yes, delete it!",
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        LoadingModal();
        const response = await apiDeleteOffice(id);
        await generateOffices();

        return MessageModal({
          icon: "success",
          title: "Success",
          text: response.data.message || "Office deleted successfully",
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
  $(officeModal.value).modal("show");
}

function hideModal() {
  $(officeModal.value).modal("hide");
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