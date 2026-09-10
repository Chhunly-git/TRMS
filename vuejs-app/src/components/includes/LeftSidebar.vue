<template>
  <aside class="main-sidebar custom-officer-sidebar elevation-4">
    <!-- Brand Logo -->
    <router-link to="/" class="brand-link">
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
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- ផ្ទាំងគ្រប់គ្រង (Dashboard) -->
          <li class="nav-item" v-if="userStore.can('dashboard')">
            <router-link :to="{ name: 'dashboard' }" active-class="active" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </router-link>
          </li>

          <!-- ព័ត៌មានផ្ទាល់ខ្លួន -->
          <li class="nav-item" v-if="userStore.can('profile')">
            <router-link :to="{ name: 'profile' }" active-class="active" class="nav-link">
              <i class="nav-icon fas fa-id-card text-success"></i>
              <p>ព័ត៌មានផ្ទាល់ខ្លួន</p>
            </router-link>
          </li>

          <!-- វត្តមានរបស់ខ្ញុំ -->
          <li class="nav-item" v-if="userStore.can('my-attendances')">
            <router-link :to="{ name: 'my-attendances' }" active-class="active" class="nav-link">
              <i class="nav-icon fas fa-calendar-check"></i>
              <p>វត្តមាន</p>
            </router-link>
          </li>

          <!-- គំរូឯកសារសម្រាប់ទាញយក -->
          <li class="nav-item" v-if="userStore.can('document-templates')">
            <router-link :to="{ name: 'document-templates' }" active-class="active" class="nav-link">
              <i class="nav-icon fas fa-folder-open text-info"></i>
              <p>គំរូឯកសារ</p>
            </router-link>
          </li>

          <!-- កាលវិភាគការងារ -->
          <li class="nav-item" v-if="userStore.can('work-schedules')">
            <router-link :to="{ name: 'work-schedules' }" active-class="active" class="nav-link">
              <i class="nav-icon fas fa-calendar-alt text-warning"></i>
              <p>កាលវិភាគការងារ</p>
            </router-link>
          </li>

          <!-- បឋមកថា ផ្នែកគ្រប់គ្រង -->
          <li class="nav-header text-uppercase font-weight-bold" v-if="userStore.hasAnyAdminPermission" style="color: #8da39c;">
            ការគ្រប់គ្រង
          </li>

          <!-- គ្រប់គ្រងគំរូឯកសារ -->
          <li class="nav-item" v-if="userStore.can('manage-document-templates')">
            <router-link :to="{ name: 'manage-document-templates' }" active-class="active" class="nav-link">
              <i class="nav-icon fas fa-file-invoice text-warning"></i>
              <p>គ្រប់គ្រងគំរូឯកសារ</p>
            </router-link>
          </li>

          <!-- អ្នកប្រើប្រាស់ / មន្ត្រី -->
          <li class="nav-item" v-if="userStore.can('users')">
            <router-link :to="{ name: 'users' }" active-class="active" class="nav-link">
              <i class="nav-icon fas fa-users-cog"></i>
              <p>អ្នកប្រើប្រាស់ / មន្ត្រី</p>
            </router-link>
          </li>

          <!-- គ្រប់គ្រងវត្តមាន -->
          <li class="nav-item" v-if="userStore.can('attendances')">
            <router-link :to="{ name: 'attendances' }" class="nav-link">
              <i class="nav-icon fas fa-calendar-check"></i>
              <p>គ្រប់គ្រងវត្តមាន</p>
            </router-link>
          </li>

          <!-- នាយកដ្ឋាន -->
          <li class="nav-item" v-if="userStore.can('departments')">
            <router-link :to="{ name: 'departments' }" active-class="active" class="nav-link">
              <i class="nav-icon fas fa-building"></i>
              <p>នាយកដ្ឋាន</p>
            </router-link>
          </li>

          <!-- ការិយាល័យ -->
          <li class="nav-item" v-if="userStore.can('divisions')">
            <router-link :to="{ name: 'divisions' }" active-class="active" class="nav-link">
              <i class="nav-icon fas fa-door-open"></i>
              <p>ការិយាល័យ</p>
            </router-link>
          </li>

          <!-- តួនាទី -->
          <li class="nav-item" v-if="userStore.can('positions')">
            <router-link :to="{ name: 'positions' }" active-class="active" class="nav-link">
              <i class="nav-icon fas fa-id-badge"></i>
              <p>តួនាទី</p>
            </router-link>
          </li>

          <!-- Backups -->
          <li class="nav-item" v-if="userStore.can('backups')">
            <router-link :to="{ name: 'backups' }" active-class="active" class="nav-link">
              <i class="nav-icon fas fa-database"></i>
              <p>Backups</p>
            </router-link>
          </li>
        </ul>
      </nav>
    </div>
  </aside>
</template>

<script setup>
import { onMounted } from 'vue';
import emptyImage from '@/assets/images/emptyImage.png';
import logoImage from '@/assets/images/logoImage.webp';
import { useUserStore } from '@/stores/user';
import { apiGetMyProfile } from '@/functions/api/user'; // ហៅ API ទាញយក Profile ផ្ទាល់ខ្លួន

const userStore = useUserStore();

// 🟢 ពេលម៉ោន LeftSidebar ឡើងវិញ ត្រូវធានាថាទិន្នន័យ Profile និងសិទ្ធិប្រើប្រាស់ស្ថិតស្ថេរ
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
  font-family: 'Battambang', cursive, sans-serif !important;
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
}

.custom-officer-sidebar .nav-link i {
  color: #a3b8b1 !important;
}

.custom-officer-sidebar .nav-link:hover {
  background-color: rgba(255, 255, 255, 0.08) !important;
  color: #ffffff !important;
}

.custom-officer-sidebar .nav-link.active {
  background-color: #1e4d41 !important;
  color: #ffffff !important;
  border-left: 4px solid #e2b13c;
}

.custom-officer-sidebar .nav-link.active i {
  color: #ffffff !important;
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