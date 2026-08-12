<template>
  <aside class="main-sidebar custom-trms-sidebar elevation-4">
    <router-link to="/" class="brand-link">
      <img :src="logoImage" alt="Chat System Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">Chat System</span>
    </router-link>

    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img :src="emptyImage" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <router-link :to="{ name: 'profile' }" class="d-block">{{ userStore.name }}</router-link>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
        <div class="sidebar-search-results">
          <div class="list-group"><a href="#" class="list-group-item">
              <div class="search-title"><strong class="text-light"></strong>N<strong
                  class="text-light"></strong>o<strong class="text-light"></strong> <strong
                  class="text-light"></strong>e<strong class="text-light"></strong>l<strong
                  class="text-light"></strong>e<strong class="text-light"></strong>m<strong
                  class="text-light"></strong>e<strong class="text-light"></strong>n<strong
                  class="text-light"></strong>t<strong class="text-light"></strong> <strong
                  class="text-light"></strong>f<strong class="text-light"></strong>o<strong
                  class="text-light"></strong>u<strong class="text-light"></strong>n<strong
                  class="text-light"></strong>d<strong class="text-light"></strong>!<strong class="text-light"></strong>
              </div>
              <div class="search-path"></div>
            </a></div>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <router-link :to="{ name: 'dashboard' }" active-class="active" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
              </p>
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
</script>

<style scoped>
/* កំណត់ពណ៌ផ្ទៃខាងក្រោយ Sidebar */
.custom-trms-sidebar {
  background-color: #0c2b29 !important;
  color: #ffffff;
}

/* ពណ៌ Header / Brand Link */
.custom-trms-sidebar .brand-link {
  border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
}

.custom-trms-sidebar .brand-text {
  color: #ffffff !important;
  font-weight: 600 !important;
}

/* ពណ៌ User Panel */
.custom-trms-sidebar .user-panel {
  border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
}

.custom-trms-sidebar .user-panel .info a {
  color: #ffffff !important;
  font-weight: 500;
}

/* ពណ៌ ប្រអប់ស្វែងរក Search Input & Button */
.custom-trms-sidebar .form-control-sidebar {
  background-color: rgba(255, 255, 255, 0.06) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  color: #ffffff !important;
}

.custom-trms-sidebar .form-control-sidebar::placeholder {
  color: #94a3b8 !important;
}

.custom-trms-sidebar .btn-sidebar {
  background-color: rgba(255, 255, 255, 0.06) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  border-left: none !important;
  color: #94a3b8 !important;
}

/* ពណ៌ Menu Link (Normal State) */
.custom-trms-sidebar .nav-sidebar .nav-link {
  color: #94a3b8 !important;
}

.custom-trms-sidebar .nav-sidebar .nav-link i {
  color: #94a3b8 !important;
}

/* ពណ៌ Menu Link (Hover State) */
.custom-trms-sidebar .nav-sidebar .nav-link:hover {
  background-color: rgba(255, 255, 255, 0.08) !important;
  color: #ffffff !important;
}

.custom-trms-sidebar .nav-sidebar .nav-link:hover i {
  color: #ffffff !important;
}

/* ពណ៌ Menu Link (Active State - ពេលកំពុងនៅលើទំព័រនោះ) */
.custom-trms-sidebar .nav-sidebar .nav-link.active {
  background-color: #144542 !important;
  color: #ffffff !important;
  box-shadow: none !important;
}

.custom-trms-sidebar .nav-sidebar .nav-link.active i {
  color: #ffffff !important;
}
</style>