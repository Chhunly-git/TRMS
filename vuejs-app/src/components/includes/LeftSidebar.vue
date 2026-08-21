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
          <img 
            :src="getFullImageUrl(userStore.profile_thumbnail || userStore.profile_image)" 
            class="img-circle elevation-2" 
            alt="User Image"
            @error="onImageError"
            style="width: 38px; height: 38px; object-fit: cover;"
          >
        </div>
        <div class="info">
          <router-link :to="{ name: 'profile' }" class="d-block text-truncate" style="max-width: 150px;">
            {{ userStore.name_kh || userStore.name || 'មន្ត្រី' }}
          </router-link>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline"  v-if="userStore.isAdmin">
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
          <li class="nav-item"  v-if="userStore.isAdmin">
            <router-link :to="{ name: 'dashboard' }" active-class="active" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </router-link>
          </li>

          <li class="nav-header text-uppercase font-weight-bold" v-if="userStore.isAdmin" style="color: #8da39c;">
            ការគ្រប់គ្រង
          </li>

          <li class="nav-item" v-if="userStore.isAdmin">
            <router-link :to="{ name: 'departments' }" active-class="active" class="nav-link">
              <i class="nav-icon fas fa-building"></i>
              <p>នាយកដ្ឋាន</p>
            </router-link>
          </li>

          <li class="nav-item" v-if="userStore.isAdmin">
            <router-link :to="{ name: 'divisions' }" active-class="active" class="nav-link">
              <i class="nav-icon fas fa-door-open"></i>
              <p>ការិយាល័យ</p>
            </router-link>
          </li>

          <li class="nav-item" v-if="userStore.isAdmin">
            <router-link :to="{ name: 'positions' }" active-class="active" class="nav-link">
              <i class="nav-icon fas fa-id-badge"></i>
              <p>តួនាទី</p>
            </router-link>
          </li>

          <li class="nav-item" v-if="userStore.isAdmin">
            <router-link :to="{ name: 'users' }" active-class="active" class="nav-link">
              <i class="nav-icon fas fa-users-cog"></i>
              <p>អ្នកប្រើប្រាស់ / មន្ត្រី</p>
            </router-link>
          </li>

          <li class="nav-item" v-if="userStore.isAdmin">
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
import emptyImage from '@/assets/images/emptyImage.png';
import logoImage from '@/assets/images/logoImage.webp';
import { useUserStore } from '@/stores/user';

const userStore = useUserStore();

// ✅ ដូចគ្នា ១០០% ទៅនឹង getFullImageUrl ក្នុង User.vue
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
/* 1. កំណត់ Font Battambang សម្រាប់ Sidebar ទាំងមូល */
.custom-officer-sidebar {
  background-color: #112d26 !important;
  font-family: 'Battambang', cursive, sans-serif !important;
}

/* 2. ពណ៌អក្សរ Brand & User Panel */
.custom-officer-sidebar .brand-link,
.custom-officer-sidebar .user-panel {
  border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
}

.custom-officer-sidebar .brand-text,
.custom-officer-sidebar .info a {
  color: #ffffff !important;
  font-weight: 500;
}

/* 3. ពណ៌ Menu ធម្មតា */
.custom-officer-sidebar .nav-link {
  color: #cdd8d5 !important;
}

.custom-officer-sidebar .nav-link i {
  color: #a3b8b1 !important;
}

/* 4. ពណ៌ Menu ពេល Hover */
.custom-officer-sidebar .nav-link:hover {
  background-color: rgba(255, 255, 255, 0.08) !important;
  color: #ffffff !important;
}

/* 5. ពណ៌ Menu ពេល Active */
.custom-officer-sidebar .nav-link.active {
  background-color: #1e4d41 !important;
  color: #ffffff !important;
  border-left: 4px solid #e2b13c;
}

.custom-officer-sidebar .nav-link.active i {
  color: #ffffff !important;
}

/* 6. ពណ៌ប្រអប់ Search */
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