<template>
  <div class="content-wrapper" style="min-height: 1416px">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Positions (គ្រប់គ្រងតួនាទី)</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item">
                <router-link :to="{ name: 'dashboard' }">Home</router-link>
              </li>
              <li class="breadcrumb-item active">Positions</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <CustomTablePaginated 
          :title="'Positions List'" 
          :data="positions" 
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

  <!-- Position Modal -->
  <div class="modal fade" ref="positionModal" aria-modal="true" role="dialog">
    <form @submit.prevent="savePosition">
      <div class="modal-dialog modal-md">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{ position.id ? 'Edit Position' : 'Create Position' }}</h4>
            <button type="button" class="close" @click="hideModal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <!-- Title (KH) -->
            <div class="form-group">
              <label>Title (KH) <span class="text-danger">*</span></label>
              <input 
                type="text" 
                class="form-control" 
                :value="position.title_kh"
                @input="handleInputKH"
                :class="{ 'is-invalid': !!positionError.title_kh }" 
                placeholder="ឧ. ប្រធាននាយកដ្ឋាន" 
              />
              <div class="invalid-feedback">{{ positionError.title_kh }}</div>
            </div>

            <!-- Title (EN) -->
            <div class="form-group">
              <label>Title (EN)</label>
              <input 
                type="text" 
                class="form-control" 
                :value="position.title_en"
                @input="handleInputEN"
                :class="{ 'is-invalid': !!positionError.title_en }" 
                placeholder="e.g. Head of Department" 
              />
              <div class="invalid-feedback">{{ positionError.title_en }}</div>
            </div>

            <!-- Level -->
            <div class="form-group">
              <label>Level <span class="text-danger">*</span></label>
              <input 
                type="number" 
                class="form-control" 
                v-model.number="position.level"
                :class="{ 'is-invalid': !!positionError.level }" 
                placeholder="ឧ. 1, 2, 3..." 
              />
              <div class="invalid-feedback">{{ positionError.level }}</div>
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

// Import API Helpers ពី position.js
import {
  apiGetPositions,
  apiCreatePosition,
  apiUpdatePosition,
  apiDeletePosition,
} from "@/functions/api/position";

const positionModal = ref(null);
const positions = ref([]);

// Pagination & Filter States
const currentPage = ref(1);
const pageSize = ref(25);
const total = ref(0);
const lastPage = ref(1);
const keyword = ref("");

const position = reactive({
  id: null,
  title_kh: "",
  title_en: "",
  level: 1,
});

const positionError = reactive({
  title_kh: "",
  title_en: "",
  level: "",
});

const defaultPosition = JSON.parse(JSON.stringify(position));
const defaultPositionError = JSON.parse(JSON.stringify(positionError));

function resetAllState() {
  Object.assign(position, defaultPosition);
  Object.assign(positionError, defaultPositionError);
}

// Function ជំនួយសម្រាប់សម្អាត \s 
function cleanText(str) {
  if (!str || typeof str !== "string") return str;
  return str.replaceAll('\\s', ' ').trim();
}

// Force clean DOM Input ពេលកំពុងវាយអក្សរ
function handleInputKH(event) {
  const val = event.target.value.replaceAll('\\s', ' ');
  position.title_kh = val;
  event.target.value = val;
}

function handleInputEN(event) {
  const val = event.target.value.replaceAll('\\s', ' ');
  position.title_en = val;
  event.target.value = val;
}

// Table Columns definition
const columns = [
  {
    header: "ID",
    accessorKey: "id",
  },
  {
    header: "Level",
    accessorKey: "level",
  },
  {
    header: "Title (KH)",
    accessorKey: "title_kh",
    cell: ({ row: { original: { title_kh } } }) => title_kh ? cleanText(title_kh) : "---",
  },
  {
    header: "Title (EN)",
    accessorKey: "title_en",
    cell: ({ row: { original: { title_en } } }) => title_en ? cleanText(title_en) : "---",
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
        // Delete button
        h(
          "button",
          {
            onClick: () => removePosition(id),
            class: "btn btn-sm btn-outline-danger mx-1",
          },
          h("i", { class: "fa fa-trash" })
        ),
        // Edit button
        h(
          "button",
          {
            onClick: () => viewPosition(id),
            class: "btn btn-sm btn-outline-secondary mx-1",
          },
          h("i", { class: "fa fa-pen" })
        ),
      ],
    enableSorting: false,
  },
];

onMounted(async () => {
  $(positionModal.value).on("hide.bs.modal", function () {
    resetAllState();
  });
  try {
    LoadingModal();
    await generatePositions();
    return CloseModal();
  } catch (error) {
    return MessageModal({
      icon: "error",
      title: "Error",
      text: error.response?.data?.message || error.message,
    });
  }
});

// Get Positions list from API helper
async function generatePositions() {
  const response = await apiGetPositions();

  const dataList = Array.isArray(response.data)
    ? response.data
    : (response.data.data || []);

  // សម្អាតទិន្នន័យចាស់ដែលមានជាប់ \s មុនពេលបង្ហាញលើតារាង
  const cleanedList = dataList.map(item => ({
    ...item,
    title_kh: cleanText(item.title_kh),
    title_en: cleanText(item.title_en)
  }));

  positions.value = cleanedList;
  total.value = response.data.total || cleanedList.length;
  lastPage.value = response.data.last_page || 1;
}

// Client-side quick search / Filter
async function handleSearchChange(searchKeyword) {
  if (!searchKeyword) {
    await generatePositions();
    return;
  }
  const query = searchKeyword.toLowerCase();
  positions.value = positions.value.filter(
    (p) =>
      p.title_kh?.toLowerCase().includes(query) ||
      p.title_en?.toLowerCase().includes(query)
  );
}

// Save or Update Position
async function savePosition() {
  try {
    LoadingModal();
    
    // សម្អាត \s មួយតង់ទៀតមុនពេលបញ្ជូនទៅ Backend API
    const payload = {
      ...position,
      title_kh: cleanText(position.title_kh),
      title_en: cleanText(position.title_en),
    };

    let response = null;

    if (position.id) {
      response = await apiUpdatePosition(position.id, payload);
    } else {
      response = await apiCreatePosition(payload);
    }

    await generatePositions();
    hideModal();

    return MessageModal({
      icon: "success",
      title: "Success",
      text: response.data.message || "Position saved successfully",
    });
  } catch (error) {
    const { response } = error;
    if (!response) {
      return MessageModal({ icon: "error", title: "Error", text: error.message });
    }
    const { status, data } = response;

    // Catch Validation errors (422)
    if (status === 422) {
      Object.keys(positionError).forEach((key) => {
        positionError[key] = data.errors[key] ? data.errors[key][0] : "";
      });
      return CloseModal();
    }
    return MessageModal({ icon: "error", title: "Error", text: data.message });
  }
}

// Show position detail in Modal for edit
function viewPosition(id) {
  const found = positions.value.find((p) => p.id === id);
  if (found) {
    Object.assign(position, {
      id: found.id,
      title_kh: cleanText(found.title_kh),
      title_en: cleanText(found.title_en),
      level: found.level
    });
    showModal();
  }
}

// Remove Position via API
async function removePosition(id) {
  Swal.fire({
    icon: "warning",
    title: "Delete Position",
    text: "Are you sure you want to delete this position?",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    confirmButtonText: "Yes, delete it!",
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        LoadingModal();
        const response = await apiDeletePosition(id);
        await generatePositions();

        return MessageModal({
          icon: "success",
          title: "Success",
          text: response.data.message || "Position deleted successfully",
        });
      } catch (error) {
        return MessageModal({
          icon: "error",
          title: "Error",
          text: error.response?.data?.message || error.message
        });
      }
    }
  });
}

function showModal() {
  $(positionModal.value).modal("show");
}

function hideModal() {
  $(positionModal.value).modal("hide");
}
</script>

<style scoped>
/* ១. កំណត់ Font Battambang សម្រាប់ Component ទាំងមូល */
.content-wrapper,
.modal-content {
  font-family: 'Battambang', cursive, sans-serif !important;
}

/* ២. Header Title & Breadcrumb */
.content-header h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #112d26; /* ពណ៌បៃតងចាស់ */
}

.breadcrumb-item a {
  color: #1e4d41;
  text-decoration: none;
  font-weight: 500;
}

/* ៣. Custom Card & Table Styling */
:deep(.card) {
  border: none !important;
  border-radius: 12px !important;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
  overflow: hidden;
}

:deep(.card-header) {
  background-color: #112d26 !important; /* ពណ៌ក្បាលតារាងបៃតងចាស់ */
  color: #ffffff !important;
  font-weight: 600;
  border-bottom: none !important;
}

:deep(.table thead th) {
  background-color: #f4f7f6 !important;
  color: #112d26 !important;
  font-weight: 700;
  border-bottom: 2px solid #d2dedb !important;
  text-transform: uppercase;
  font-size: 0.85rem;
}

:deep(.table tbody tr:hover) {
  background-color: #f0f7f4 !important; /* ពណ៌ពេល Hover លើជួរដេក */
}

/* ៤. ប៊ូតុង Action Buttons */
.btn-primary,
:deep(.btn-primary) {
  background-color: #1e4d41 !important;
  border-color: #1e4d41 !important;
  border-radius: 6px !important;
  font-weight: 500;
  transition: all 0.2s ease-in-out;
}

.btn-primary:hover,
:deep(.btn-primary:hover) {
  background-color: #112d26 !important;
  border-color: #112d26 !important;
  transform: translateY(-1px);
}

.btn-success,
:deep(.btn-success) {
  background-color: #28a745 !important;
  border-radius: 6px !important;
  font-weight: 500;
}

.btn-outline-danger {
  border-radius: 6px !important;
  border-color: #e74c3c !important;
  color: #e74c3c !important;
}

.btn-outline-danger:hover {
  background-color: #e74c3c !important;
  color: #ffffff !important;
}

.btn-outline-secondary {
  border-radius: 6px !important;
}

/* ៥. Modal Popup Styling */
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

.modal-header .close:hover {
  opacity: 1;
}

/* ៦. Form Input Styling */
.form-control {
  border-radius: 6px !important;
  border: 1px solid #ced4da;
  padding: 0.5rem 0.75rem;
  transition: border-color 0.2s, box-shadow 0.2s;
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