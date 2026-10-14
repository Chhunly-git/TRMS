<template>
  <aside class="main-sidebar custom-officer-sidebar elevation-4">
    <!-- Brand Logo -->
    <router-link to="/dashboard" class="brand-link">
      <img :src="logoImage" alt="TRMS Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">TRMS</span>
    </router-link>

    <!-- Sidebar Content -->
    <div class="sidebar">
      <!-- User Panel -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
        <div class="image">
          <img :src="getFullImageUrl(userStore.profile_thumbnail || userStore.profile_image)"
            class="img-circle elevation-2" alt="User Image" @error="onImageError"
            style="width: 38px; height: 38px; object-fit: cover;">
        </div>
        <div class="info">
          <router-link :to="{ name: 'setting' }" class="d-block text-truncate" style="max-width: 150px;">
            {{ userStore.name_kh || userStore.name || 'មន្ត្រី' }}
          </router-link>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline" v-if="userStore.hasAnyAdminPermission">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="ស្វែងរក..." aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
          <!-- ផ្ទាំងគ្រប់គ្រង (Dashboard) -->
          <li class="nav-item mb-2" v-if="userStore.can('dashboard')">
            <router-link :to="{ name: 'dashboard' }" active-class="active" class="nav-link font-weight-bold">
              <i class="nav-icon fas fa-tachometer-alt text-warning"></i>
              <p>ផ្ទាំងគ្រប់គ្រង (Dashboard)</p>
            </router-link>
          </li>

          <!-- ==================== ១. ការគ្រប់គ្រងទិន្នន័យមន្រ្តី ==================== -->
          <li class="nav-item" :class="{ 'menu-open': openGroups.officers }">
            <a
              href="javascript:void(0)"
              class="nav-link group-header"
              :class="{ 'active': isOfficerGroupActive }"
              @click.stop.prevent="toggleGroup('officers')"
            >
              <i class="nav-icon fas fa-user-tie text-success"></i>
              <p>
                ១. គ្រប់គ្រងទិន្នន័យមន្រ្តី
                <i class="right fas fa-angle-left transition-transform" :class="{ 'rotate-90': openGroups.officers }"></i>
              </p>
            </a>
            <ul class="nav nav-treeview" :style="{ display: openGroups.officers ? 'block' : 'none' }">
              <!-- ព័ត៌មានផ្ទាល់ខ្លួន -->
              <li class="nav-item" v-if="userStore.can('profile')">
                <router-link :to="{ name: 'profile' }" active-class="active" class="nav-link sub-link">
                  <i class="far fa-circle nav-icon text-info"></i>
                  <p>ព័ត៌មានផ្ទាល់ខ្លួន</p>
                </router-link>
              </li>

              <!-- អ្នកប្រើប្រាស់ / មន្ត្រី (Admin / HR) -->
              <li class="nav-item" v-if="userStore.can('users')">
                <router-link :to="{ name: 'users' }" active-class="active" class="nav-link sub-link">
                  <i class="far fa-circle nav-icon text-primary"></i>
                  <p>បញ្ជីមន្ត្រី / អ្នកប្រើប្រាស់</p>
                </router-link>
              </li>

              <!-- គ្រប់គ្រងវត្តមាន (Admin) -->
              <li class="nav-item" v-if="userStore.can('attendances')">
                <router-link :to="{ name: 'attendances' }" active-class="active" class="nav-link sub-link">
                  <i class="far fa-circle nav-icon text-success"></i>
                  <p>គ្រប់គ្រងវត្តមាន</p>
                </router-link>
              </li>

              <!-- វត្តមានរបស់ខ្ញុំ (User) -->
              <li class="nav-item" v-else-if="userStore.can('my-attendances')">
                <router-link :to="{ name: 'my-attendances' }" active-class="active" class="nav-link sub-link">
                  <i class="far fa-circle nav-icon text-success"></i>
                  <p>វត្តមានរបស់ខ្ញុំ</p>
                </router-link>
              </li>

              <!-- ច្បាប់ឈប់សម្រាក -->
              <li class="nav-item" v-if="userStore.can('leave-requests')">
                <router-link :to="{ name: 'leave-requests' }" active-class="active" class="nav-link sub-link">
                  <i class="far fa-circle nav-icon text-warning"></i>
                  <p>ច្បាប់ឈប់សម្រាក</p>
                </router-link>
              </li>

              <!-- របាយការណ៍ប្រចាំសប្តាហ៍ -->
              <li class="nav-item" v-if="userStore.can('weekly-reports')">
                <router-link :to="{ name: 'weekly-reports' }" active-class="active" class="nav-link sub-link">
                  <i class="far fa-circle nav-icon text-teal"></i>
                  <p>របាយការណ៍ប្រចាំសប្តាហ៍</p>
                </router-link>
              </li>

              <!-- កាលវិភាគការងារ -->
              <li class="nav-item" v-if="userStore.can('work-schedules')">
                <router-link :to="{ name: 'work-schedules' }" active-class="active" class="nav-link sub-link">
                  <i class="far fa-circle nav-icon text-secondary"></i>
                  <p>កាលវិភាគការងារ</p>
                </router-link>
              </li>
            </ul>
          </li>

          <!-- ==================== ២. ការគ្រប់គ្រងឯកសារ ==================== -->
          <li class="nav-item" :class="{ 'menu-open': openGroups.documents }">
            <a
              href="javascript:void(0)"
              class="nav-link group-header"
              :class="{ 'active': isDocumentGroupActive }"
              @click.stop.prevent="toggleGroup('documents')"
            >
              <i class="nav-icon fas fa-folder-open text-warning"></i>
              <p>
                ២. ការគ្រប់គ្រងឯកសារ
                <i class="right fas fa-angle-left transition-transform" :class="{ 'rotate-90': openGroups.documents }"></i>
              </p>
            </a>
            <ul class="nav nav-treeview" :style="{ display: openGroups.documents ? 'block' : 'none' }">
              <!-- ឯកសារចូល -->
              <li class="nav-item" v-if="userStore.can('inbound-documents')">
                <router-link :to="{ name: 'inbound-documents' }" class="nav-link sub-link" :class="{ active: isAllDocsActive }">
                  <i class="far fa-circle nav-icon text-primary"></i>
                  <p>ឯកសារចូល</p>
                </router-link>
              </li>

              <!-- អ្នកទទួលឯកសារ (ចុះបញ្ជី & ទទួលឯកសារ) -->
              <li class="nav-item" v-if="userStore.can('inbound-documents-receptionist')">
                <router-link :to="{ name: 'inbound-documents', query: { tab: 'reception' } }" class="nav-link sub-link" :class="{ active: isReceptionActive }">
                  <i class="far fa-circle nav-icon text-info"></i>
                  <p>អ្នកទទួលឯកសារ</p>
                </router-link>
              </li>

              <!-- ជំនួយការអគ្គនាយក -->
              <li class="nav-item" v-if="userStore.can('inbound-documents-assistant')">
                <router-link :to="{ name: 'inbound-documents', query: { tab: 'assistant_inbox' } }" class="nav-link sub-link" :class="{ active: isAssistantActive }">
                  <i class="far fa-circle nav-icon text-danger"></i>
                  <p>ជំនួយការអគ្គនាយក</p>
                </router-link>
              </li>

              <!-- គ្រប់គ្រងគំរូឯកសារ (Admin) -->
              <li class="nav-item" v-if="userStore.can('manage-document-templates')">
                <router-link :to="{ name: 'manage-document-templates' }" active-class="active" class="nav-link sub-link">
                  <i class="far fa-circle nav-icon text-warning"></i>
                  <p>គ្រប់គ្រងគំរូឯកសារ</p>
                </router-link>
              </li>

              <!-- គំរូឯកសារទាញយក (User) -->
              <li class="nav-item" v-else-if="userStore.can('document-templates')">
                <router-link :to="{ name: 'document-templates' }" active-class="active" class="nav-link sub-link">
                  <i class="far fa-circle nav-icon text-info"></i>
                  <p>គំរូឯកសារ</p>
                </router-link>
              </li>
            </ul>
          </li>

          <!-- ==================== ៣. បន្ទប់ប្រជុំ & ការកក់ ==================== -->
          <li class="nav-item" :class="{ 'menu-open': openGroups.meeting_rooms }">
            <a
              href="javascript:void(0)"
              class="nav-link group-header"
              :class="{ 'active': isMeetingRoomGroupActive }"
              @click.stop.prevent="toggleGroup('meeting_rooms')"
            >
              <i class="nav-icon fas fa-door-open text-primary"></i>
              <p>
                ៣. បន្ទប់ប្រជុំ & ការកក់
                <i class="right fas fa-angle-left transition-transform" :class="{ 'rotate-90': openGroups.meeting_rooms }"></i>
              </p>
            </a>
            <ul class="nav nav-treeview" :style="{ display: openGroups.meeting_rooms ? 'block' : 'none' }">
              <!-- បន្ទប់ប្រជុំ & ការកក់បន្ទប់ (User / All) -->
              <li class="nav-item" v-if="userStore.can('meeting-rooms')">
                <router-link :to="{ name: 'meeting-rooms' }" active-class="active" class="nav-link sub-link">
                  <i class="far fa-circle nav-icon text-success"></i>
                  <p>បន្ទប់ប្រជុំ & ការកក់</p>
                </router-link>
              </li>

              <!-- គ្រប់គ្រងបន្ទប់ប្រជុំ (Admin / Manager) -->
              <li class="nav-item" v-if="userStore.can('manage-meeting-rooms')">
                <router-link :to="{ name: 'manage-meeting-rooms' }" active-class="active" class="nav-link sub-link">
                  <i class="far fa-circle nav-icon text-warning"></i>
                  <p>គ្រប់គ្រងបន្ទប់ប្រជុំ</p>
                </router-link>
              </li>
            </ul>
          </li>

          <!-- ==================== ៤. ការកំណត់ ==================== -->
          <li class="nav-item" :class="{ 'menu-open': openGroups.settings }">
            <a
              href="javascript:void(0)"
              class="nav-link group-header"
              :class="{ 'active': isSettingGroupActive }"
              @click.stop.prevent="toggleGroup('settings')"
            >
              <i class="nav-icon fas fa-cogs text-info"></i>
              <p>
                ៤. ការកំណត់
                <i class="right fas fa-angle-left transition-transform" :class="{ 'rotate-90': openGroups.settings }"></i>
              </p>
            </a>
            <ul class="nav nav-treeview" :style="{ display: openGroups.settings ? 'block' : 'none' }">
              <!-- ការកំណត់គណនី -->
              <li class="nav-item">
                <router-link :to="{ name: 'setting' }" active-class="active" class="nav-link sub-link">
                  <i class="far fa-circle nav-icon text-info"></i>
                  <p>ការកំណត់គណនី</p>
                </router-link>
              </li>

              <!-- នាយកដ្ឋាន (Admin) -->
              <li class="nav-item" v-if="userStore.can('departments')">
                <router-link :to="{ name: 'departments' }" active-class="active" class="nav-link sub-link">
                  <i class="far fa-circle nav-icon text-primary"></i>
                  <p>នាយកដ្ឋាន</p>
                </router-link>
              </li>

              <!-- ការិយាល័យ (Admin) -->
              <li class="nav-item" v-if="userStore.can('divisions')">
                <router-link :to="{ name: 'divisions' }" active-class="active" class="nav-link sub-link">
                  <i class="far fa-circle nav-icon text-teal"></i>
                  <p>ការិយាល័យ</p>
                </router-link>
              </li>

              <!-- មុខតំណែង (Admin) -->
              <li class="nav-item" v-if="userStore.can('positions')">
                <router-link :to="{ name: 'positions' }" active-class="active" class="nav-link sub-link">
                  <i class="far fa-circle nav-icon text-warning"></i>
                  <p>មុខតំណែង</p>
                </router-link>
              </li>

              <!-- Backups (Admin) -->
              <li class="nav-item" v-if="userStore.can('backups')">
                <router-link :to="{ name: 'backups' }" active-class="active" class="nav-link sub-link">
                  <i class="far fa-circle nav-icon text-danger"></i>
                  <p>ការបម្រុងទុក (Backups)</p>
                </router-link>
              </li>
            </ul>
          </li>
        </ul>
      </nav>
    </div>
  </aside>
</template>

<script setup>
import { computed, reactive, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import emptyImage from '@/assets/images/emptyImage.png';
import logoImage from '@/assets/images/logoImage.webp';
import { useUserStore } from '@/stores/user';
import { apiGetMyProfile } from '@/functions/api/user';

const route = useRoute();
const userStore = useUserStore();

// Accordion open states - collapsed by default for a clean layout
const openGroups = reactive({
  officers: false,
  documents: false,
  meeting_rooms: false,
  settings: false,
});

const toggleGroup = (group) => {
  openGroups[group] = !openGroups[group];
};

// Route checks for Inbound Documents sub-tabs
const isAllDocsActive = computed(() => {
  return route.name === 'inbound-documents' && (!route.query.tab || route.query.tab === 'all');
});

const isReceptionActive = computed(() => {
  return route.name === 'inbound-documents' && route.query.tab === 'reception';
});

const isAssistantActive = computed(() => {
  return route.name === 'inbound-documents' && route.query.tab === 'assistant_inbox';
});

// Computed active status for parent group headers
const isOfficerGroupActive = computed(() => {
  return [
    'profile',
    'my-profile',
    'users',
    'user.detail',
    'attendances',
    'my-attendances',
    'leave-requests',
    'weekly-reports',
    'work-schedules'
  ].includes(route.name);
});

const isDocumentGroupActive = computed(() => {
  return [
    'inbound-documents',
    'inbound-documents.routing-slip',
    'document-templates',
    'manage-document-templates'
  ].includes(route.name);
});

const isMeetingRoomGroupActive = computed(() => {
  return ['meeting-rooms', 'manage-meeting-rooms'].includes(route.name);
});

const isSettingGroupActive = computed(() => {
  return ['setting', 'departments', 'divisions', 'positions', 'backups'].includes(route.name);
});

// Auto-expand active group on route change
watch(
  () => route.name,
  () => {
    if (isOfficerGroupActive.value) openGroups.officers = true;
    if (isDocumentGroupActive.value) openGroups.documents = true;
    if (isMeetingRoomGroupActive.value) openGroups.meeting_rooms = true;
    if (isSettingGroupActive.value) openGroups.settings = true;
  },
  { immediate: true }
);

// Restore profile and permissions on mount
onMounted(async () => {
  if (!userStore.id || !userStore.permissions || userStore.permissions.length === 0) {
    try {
      const res = await apiGetMyProfile();
      const myData = res.data.user || res.data.data || res.data;
      if (myData) {
        userStore.setState(myData);
      }
    } catch (err) {
      console.error("Failed to restore profile/permissions in sidebar:", err);
    }
  }
});

const getFullImageUrl = (path) => {
  if (!path) return emptyImage;

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
  event.target.src = emptyImage;
};
</script>

<style scoped>
.custom-officer-sidebar {
  background-color: #112d26 !important;
  font-family: 'Kantumruy Pro', 'Battambang', cursive, sans-serif !important;
}

.custom-officer-sidebar .brand-link,
.custom-officer-sidebar .user-panel {
  border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
}

.custom-officer-sidebar .brand-text,
.custom-officer-sidebar .info a {
  color: #ffffff !important;
  font-weight: 500;
}

.custom-officer-sidebar .nav-link {
  color: #cdd8d5 !important;
  transition: all 0.2s ease;
}

.custom-officer-sidebar .nav-link i {
  color: #a3b8b1 !important;
}

.custom-officer-sidebar .group-header {
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  border-radius: 4px;
}

.custom-officer-sidebar .group-header:hover {
  background-color: rgba(255, 255, 255, 0.08) !important;
  color: #ffffff !important;
}

.custom-officer-sidebar .group-header.active {
  background-color: rgba(226, 177, 60, 0.12) !important;
  color: #e2b13c !important;
}

.custom-officer-sidebar .group-header.active i.nav-icon {
  color: #e2b13c !important;
}

.custom-officer-sidebar .nav-treeview {
  padding-left: 0.5rem;
  transition: max-height 0.3s ease;
}

.custom-officer-sidebar .sub-link {
  font-size: 0.88rem;
  padding-top: 0.35rem !important;
  padding-bottom: 0.35rem !important;
  border-radius: 4px;
}

.custom-officer-sidebar .sub-link:hover {
  background-color: rgba(255, 255, 255, 0.06) !important;
  color: #ffffff !important;
}

.custom-officer-sidebar .sub-link.active {
  background-color: #1e4d41 !important;
  color: #ffffff !important;
  border-left: 3px solid #e2b13c;
  font-weight: 600;
}

.custom-officer-sidebar .sub-link.active i {
  color: #e2b13c !important;
}

.custom-officer-sidebar .nav-link.active {
  background-color: #1e4d41 !important;
  color: #ffffff !important;
  border-left: 4px solid #e2b13c;
}

.custom-officer-sidebar .nav-link.active i {
  color: #ffffff !important;
}

.transition-transform {
  transition: transform 0.25s ease;
}

.rotate-90 {
  transform: rotate(-90deg);
}

.custom-officer-sidebar .form-control-sidebar,
.custom-officer-sidebar .btn-sidebar {
  background-color: #183a31 !important;
  border-color: #214d41 !important;
  color: #ffffff !important;
}

.custom-officer-sidebar .form-control-sidebar::placeholder {
  color: #8da39c !important;
}
</style>