<template>
  <div class="officer-select-component position-relative" ref="rootRef">
    <!-- Trigger Button -->
    <div
      class="form-control form-control-sm officer-trigger d-flex align-items-center justify-content-between cursor-pointer"
      :class="{
        'border-primary ring-focus': isOpen,
        'is-invalid': invalid,
        'bg-light-disabled cursor-not-allowed': disabled
      }"
      @click="toggleDropdown"
      tabindex="0"
      @keydown.down.prevent="openAndFocusSearch"
      @keydown.enter.prevent="toggleDropdown"
      @keydown.esc="closeDropdown"
    >
      <!-- Selected Officer Display -->
      <div v-if="selectedOfficer" class="d-flex align-items-center text-truncate mr-2 flex-grow-1">
        <div class="avatar-mini mr-2 flex-shrink-0">
          <img
            v-if="selectedOfficer.profile_image"
            :src="selectedOfficer.profile_image"
            class="rounded-circle"
            style="width: 24px; height: 24px; object-fit: cover;"
          />
          <div
            v-else
            class="rounded-circle avatar-placeholder-mini bg-indigo text-white font-weight-bold text-center"
            style="width: 24px; height: 24px; line-height: 24px; font-size: 11px;"
          >
            {{ (selectedOfficer.name_kh || selectedOfficer.name || 'M').charAt(0) }}
          </div>
        </div>
        <div class="text-truncate">
          <span class="font-weight-bold text-dark font-13 mr-1">
            {{ selectedOfficer.name_kh || selectedOfficer.name }}
          </span>
          <span class="badge badge-light border text-xs mr-1" v-if="selectedOfficer.position?.title_kh">
            {{ selectedOfficer.position.title_kh }}
          </span>
          <span class="text-muted text-xs font-italic" v-if="selectedOfficer.department?.name_kh">
            - {{ selectedOfficer.department.name_kh }}
          </span>
        </div>
      </div>

      <!-- Placeholder -->
      <div v-else class="text-muted font-13 text-truncate">
        <i class="fas fa-search text-muted mr-1"></i>
        <span>{{ placeholder || '-- ជ្រើសរើស ឬស្វែងរកមន្ត្រី --' }}</span>
      </div>

      <!-- Action Icons (Clear / Dropdown Chevron) -->
      <div class="d-flex align-items-center ml-2 flex-shrink-0">
        <button
          v-if="selectedOfficer && !disabled"
          type="button"
          class="btn btn-link btn-xs text-muted p-0 mr-1 hover-text-danger"
          @click.stop="clearSelection"
          title="សម្អាតជម្រើស"
        >
          <i class="fas fa-times-circle"></i>
        </button>
        <i
          class="fas font-11 text-muted transition-transform"
          :class="isOpen ? 'fa-chevron-up text-primary' : 'fa-chevron-down'"
        ></i>
      </div>
    </div>

    <!-- Dropdown Menu -->
    <div
      v-if="isOpen"
      class="officer-dropdown-menu shadow-lg bg-white rounded border mt-1 position-absolute"
      style="z-index: 1060; left: 0; right: 0; min-width: 320px; width: 100%;"
    >
      <!-- Search Input Box -->
      <div class="p-2 border-bottom bg-light">
        <div class="input-group input-group-sm">
          <div class="input-group-prepend">
            <span class="input-group-text bg-white border-right-0">
              <i class="fas fa-search text-muted"></i>
            </span>
          </div>
          <input
            ref="searchInputRef"
            type="text"
            v-model="searchQuery"
            class="form-control border-left-0 font-khmer font-13"
            placeholder="ស្វែងរកតាមឈ្មោះ, តួនាទី, នាយកដ្ឋាន..."
            @keydown.esc="closeDropdown"
            @keydown.down="focusNextOption"
          />
          <div class="input-group-append" v-if="searchQuery">
            <button class="btn btn-white border border-left-0" type="button" @click="searchQuery = ''">
              <i class="fas fa-times text-muted font-11"></i>
            </button>
          </div>
        </div>

        <!-- Filter category pills -->
        <div class="d-flex flex-wrap gap-1 mt-2">
          <button
            type="button"
            class="badge-pill-btn btn btn-xs"
            :class="categoryFilter === 'ALL' ? 'btn-primary text-white' : 'btn-light text-dark border'"
            @click="categoryFilter = 'ALL'"
          >
            ទាំងអស់ ({{ sortedOfficers.length }})
          </button>
          <button
            type="button"
            class="badge-pill-btn btn btn-xs"
            :class="categoryFilter === 'LEADERSHIP' ? 'btn-indigo text-white' : 'btn-light text-dark border'"
            @click="categoryFilter = 'LEADERSHIP'"
          >
            ថ្នាក់ដឹកនាំ
          </button>
          <button
            type="button"
            class="badge-pill-btn btn btn-xs"
            :class="categoryFilter === 'DEPARTMENT' ? 'btn-info text-white' : 'btn-light text-dark border'"
            @click="categoryFilter = 'DEPARTMENT'"
          >
            នាយកដ្ឋាន
          </button>
          <button
            type="button"
            class="badge-pill-btn btn btn-xs"
            :class="categoryFilter === 'OFFICE' ? 'btn-secondary text-white' : 'btn-light text-dark border'"
            @click="categoryFilter = 'OFFICE'"
          >
            ការិយាល័យ/មន្ត្រី
          </button>
        </div>
      </div>

      <!-- Result Count Info -->
      <div class="px-3 py-1 bg-white border-bottom d-flex justify-content-between align-items-center text-xs text-muted">
        <span>លំដាប់ថ្នាក់៖ <strong>តាមតួនាទី (Position Level)</strong></span>
        <span>រកឃើញ <strong>{{ filteredOfficers.length }}</strong> នាក់</span>
      </div>

      <!-- Officers List -->
      <div class="officers-list-scrollable" style="max-height: 280px; overflow-y: auto;">
        <!-- Empty State -->
        <div v-if="filteredOfficers.length === 0" class="text-center py-4 text-muted text-sm font-khmer">
          <i class="fas fa-user-slash fa-2x mb-2 text-black-50 d-block"></i>
          <span>មិនមានមន្ត្រីត្រូវនឹងការស្វែងរកឡើយ</span>
        </div>

        <!-- Officer Item -->
        <div
          v-for="u in filteredOfficers"
          :key="u.id"
          class="officer-option-item px-3 py-2 cursor-pointer border-bottom d-flex align-items-center justify-content-between"
          :class="{
            'bg-light-selected border-left-selected': u.id === modelValue,
            'opacity-50 cursor-not-allowed bg-light': isOfficerDisabled(u.id)
          }"
          @click="selectOfficer(u)"
        >
          <!-- Left info -->
          <div class="d-flex align-items-center text-truncate mr-2 flex-grow-1">
            <!-- Avatar -->
            <div class="avatar-sm mr-2 flex-shrink-0">
              <img
                v-if="u.profile_image"
                :src="u.profile_image"
                class="rounded-circle border"
                style="width: 32px; height: 32px; object-fit: cover;"
              />
              <div
                v-else
                class="rounded-circle avatar-placeholder bg-light-indigo text-indigo font-weight-bold text-center"
                style="width: 32px; height: 32px; line-height: 32px; font-size: 13px;"
              >
                {{ (u.name_kh || u.name || 'M').charAt(0) }}
              </div>
            </div>

            <!-- Details -->
            <div class="text-truncate">
              <div class="d-flex align-items-center">
                <span class="font-weight-bold text-dark font-13 mr-2">
                  {{ u.name_kh || u.name }}
                </span>
                <span v-if="u.name_en" class="text-muted text-xs font-italic mr-2">
                  ({{ u.name_en }})
                </span>
                <!-- Position Level Badge -->
                <span class="badge" :class="getPositionBadgeClass(u.position?.level)">
                  {{ u.position?.title_kh || 'មន្ត្រី' }}
                </span>
              </div>
              <div class="text-muted text-xs mt-0-5 text-truncate">
                <i class="fas fa-building text-black-50 mr-1" v-if="u.department"></i>
                <span v-if="u.department">{{ u.department.name_kh }}</span>
                <span v-if="u.office" class="text-muted font-italic"> ({{ u.office.name_kh }})</span>
              </div>
            </div>
          </div>

          <!-- Right badges / Checkmark -->
          <div class="flex-shrink-0 text-right">
            <span v-if="isOfficerDisabled(u.id)" class="badge badge-secondary text-xs px-2 py-1">
              <i class="fas fa-check mr-1"></i>មានក្នុងក្រុមរួចរាល់
            </span>
            <span v-else-if="u.id === modelValue" class="badge badge-success text-xs px-2 py-1">
              <i class="fas fa-check-circle mr-1"></i>បានជ្រើសរើស
            </span>
            <span v-else class="btn btn-outline-primary btn-xs py-0 px-2 font-11">
              ជ្រើសរើស
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  modelValue: {
    type: [Number, String],
    default: null,
  },
  officers: {
    type: Array,
    default: () => [],
  },
  disabledUserIds: {
    type: Array,
    default: () => [],
  },
  placeholder: {
    type: String,
    default: '-- ជ្រើសរើស ឬស្វែងរកមន្ត្រី --',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  invalid: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue', 'change']);

// State
const isOpen = ref(false);
const searchQuery = ref('');
const categoryFilter = ref('ALL'); // 'ALL', 'LEADERSHIP', 'DEPARTMENT', 'OFFICE'
const rootRef = ref(null);
const searchInputRef = ref(null);

// Currently selected officer object
const selectedOfficer = computed(() => {
  if (!props.modelValue) return null;
  return props.officers.find(o => String(o.id) === String(props.modelValue)) || null;
});

// Check if an officer is in disabled list
const isOfficerDisabled = (userId) => {
  if (!props.disabledUserIds || props.disabledUserIds.length === 0) return false;
  return props.disabledUserIds.some(id => String(id) === String(userId));
};

// Sort all officers primarily by Position Level (Ascending: 1, 2, 3...) then by Name
const sortedOfficers = computed(() => {
  return [...props.officers].sort((a, b) => {
    const levelA = a.position?.level != null ? Number(a.position.level) : 999;
    const levelB = b.position?.level != null ? Number(b.position.level) : 999;
    if (levelA !== levelB) {
      return levelA - levelB;
    }
    // Secondary sort: Department ID
    const deptA = a.department_id ?? 999;
    const deptB = b.department_id ?? 999;
    if (deptA !== deptB) {
      return deptA - deptB;
    }
    // Tertiary sort: Name Khmer
    const nameA = a.name_kh || a.name || '';
    const nameB = b.name_kh || b.name || '';
    return nameA.localeCompare(nameB, 'km');
  });
});

// Filter by search query and category
const filteredOfficers = computed(() => {
  let list = sortedOfficers.value;

  // 1. Category Filter
  if (categoryFilter.value === 'LEADERSHIP') {
    // Level 1 to 2 (អគ្គនាយក, អគ្គនាយករង)
    list = list.filter(u => u.position && Number(u.position.level) <= 2);
  } else if (categoryFilter.value === 'DEPARTMENT') {
    // Level 3 to 5 (ប្រធាននាយកដ្ឋាន, អនុប្រធាននាយកដ្ឋាន)
    list = list.filter(u => u.position && Number(u.position.level) >= 3 && Number(u.position.level) <= 5);
  } else if (categoryFilter.value === 'OFFICE') {
    // Level 6 and above (ប្រធានការិយាល័យ, អនុប្រធាន, មន្ត្រី)
    list = list.filter(u => !u.position || Number(u.position.level) >= 6);
  }

  // 2. Search Text Filter
  const q = searchQuery.value.trim().toLowerCase();
  if (!q) return list;

  return list.filter(u => {
    const nameKh = (u.name_kh || '').toLowerCase();
    const nameEn = (u.name_en || u.name || '').toLowerCase();
    const pos = (u.position?.title_kh || '').toLowerCase();
    const dept = (u.department?.name_kh || '').toLowerCase();
    const office = (u.office?.name_kh || '').toLowerCase();
    const email = (u.email || '').toLowerCase();

    return (
      nameKh.includes(q) ||
      nameEn.includes(q) ||
      pos.includes(q) ||
      dept.includes(q) ||
      office.includes(q) ||
      email.includes(q)
    );
  });
});

// Helpers
const getPositionBadgeClass = (level) => {
  const lvl = level != null ? Number(level) : 999;
  if (lvl <= 2) return 'badge-danger text-white'; // ថ្នាក់ដឹកនាំកំពូល
  if (lvl <= 4) return 'badge-warning text-dark'; // ប្រធាននាយកដ្ឋាន
  if (lvl <= 5) return 'badge-primary text-white'; // អនុប្រធាននាយកដ្ឋាន
  if (lvl <= 6) return 'badge-info text-white'; // ប្រធានការិយាល័យ
  if (lvl <= 8) return 'badge-teal text-white'; // អនុប្រធានការិយាល័យ
  return 'badge-light border text-muted'; // មន្ត្រី
};

// Actions
const toggleDropdown = () => {
  if (props.disabled) return;
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    searchQuery.value = '';
    nextTick(() => {
      searchInputRef.value?.focus();
    });
  }
};

const openAndFocusSearch = () => {
  if (props.disabled) return;
  isOpen.value = true;
  nextTick(() => {
    searchInputRef.value?.focus();
  });
};

const closeDropdown = () => {
  isOpen.value = false;
};

const selectOfficer = (officer) => {
  if (isOfficerDisabled(officer.id)) return;
  emit('update:modelValue', officer.id);
  emit('change', officer);
  closeDropdown();
};

const clearSelection = () => {
  emit('update:modelValue', null);
  emit('change', null);
};

// Click outside handling
const handleOutsideClick = (e) => {
  if (rootRef.value && !rootRef.value.contains(e.target)) {
    closeDropdown();
  }
};

onMounted(() => {
  document.addEventListener('click', handleOutsideClick);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleOutsideClick);
});
</script>

<style scoped>
.officer-select-component {
  font-family: 'Kantumruy Pro', 'Battambang', system-ui, sans-serif;
  user-select: none;
}

.officer-trigger {
  min-height: 38px;
  background-color: #fff;
  transition: all 0.15s ease-in-out;
}

.officer-trigger:hover:not(.cursor-not-allowed) {
  border-color: #6610f2;
}

.ring-focus {
  border-color: #6610f2 !important;
  box-shadow: 0 0 0 0.2rem rgba(102, 16, 242, 0.18) !important;
}

.bg-light-disabled {
  background-color: #e9ecef !important;
}

.transition-transform {
  transition: transform 0.2s ease;
}

.badge-pill-btn {
  font-size: 11px;
  border-radius: 12px;
  padding: 2px 8px;
  font-weight: 500;
}

.btn-indigo {
  background-color: #6610f2;
  color: #fff;
}
.btn-indigo:hover {
  background-color: #520dc2;
  color: #fff;
}

.bg-light-indigo {
  background-color: #eef2ff;
}

.text-indigo {
  color: #6610f2 !important;
}

.badge-teal {
  background-color: #20c997;
}

.bg-light-selected {
  background-color: #f0f7ff !important;
}

.border-left-selected {
  border-left: 3px solid #007bff !important;
}

.officer-option-item {
  transition: background-color 0.15s;
}

.officer-option-item:hover:not(.cursor-not-allowed) {
  background-color: #f8f9fa;
}

.hover-text-danger:hover {
  color: #dc3545 !important;
}

.cursor-pointer {
  cursor: pointer;
}

.cursor-not-allowed {
  cursor: not-allowed;
}

.officers-list-scrollable::-webkit-scrollbar {
  width: 6px;
}
.officers-list-scrollable::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 3px;
}
.officers-list-scrollable::-webkit-scrollbar-thumb:hover {
  background-color: #94a3b8;
}
</style>
