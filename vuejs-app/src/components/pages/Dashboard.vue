<template>
  <div class="content-wrapper khmer-layout">
    <!-- Header Section -->
    <div class="content-header pb-1">
      <div class="container-fluid">
        <div class="row align-items-center mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 font-weight-bold text-dark khmer-page-title">
              <i class="fas fa-tachometer-alt text-primary mr-2"></i>ផ្ទាំងគ្រប់គ្រង
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right font-khmer mb-0">
              <li class="breadcrumb-item">
                <router-link :to="{ name: 'dashboard' }"><i class="fas fa-home mr-1"></i>ទំព័រដើម</router-link>
              </li>
              <li class="breadcrumb-item active">Dashboard</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Section -->
    <div class="content">
      <div class="container-fluid">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5 my-5">
          <div class="spinner-border text-success" style="width: 3.5rem; height: 3.5rem;" role="status">
            <span class="sr-only">កំពុងដំណើរការ...</span>
          </div>
          <h5 class="mt-3 font-khmer text-muted">កំពុងទាញយកទិន្នន័យប្រព័ន្ធ TRMS...</h5>
        </div>

        <div v-else>
          <!-- 1. Hero Welcome & Role Adaptive Switcher Banner -->
          <div class="hero-banner card shadow-sm border-0 rounded-xl mb-4 overflow-hidden">
            <div class="hero-bg-accent"></div>
            <div class="card-body p-4 position-relative">
              <div class="row align-items-center">
                <!-- User Profile & Greeting -->
                <div class="col-xl-7 col-lg-7 col-md-12 mb-3 mb-lg-0">
                  <div class="d-flex align-items-center flex-wrap flex-sm-nowrap">
                    <div class="avatar-wrapper position-relative mr-3 mb-2 mb-sm-0">
                      <img
                        :src="getFullImageUrl(userContext.profile_image)"
                        alt="Profile"
                        class="hero-avatar img-circle shadow border border-2 border-white"
                      />
                      <span class="status-indicator-dot" :class="myAttendanceToday ? 'bg-success' : 'bg-warning'"></span>
                    </div>
                    <div>
                      <div class="d-flex align-items-center flex-wrap">
                        <span class="greeting-badge px-2 py-1 rounded font-weight-bold mr-2 mb-1">
                          <i class="fas fa-sun text-warning mr-1"></i>{{ userContext.greeting_kh || 'សួស្តី' }}
                        </span>
                        <h3 class="font-weight-bold mb-1 text-white user-name-kh">
                          {{ userContext.name_kh || userContext.name_en || userStore.name_kh || userStore.name }}
                        </h3>
                      </div>
                      <div class="d-flex align-items-center flex-wrap text-white-50 small mt-1 font-khmer">
                        <span class="badge badge-light-translucent mr-2 mb-1">
                          <i class="fas fa-id-badge mr-1"></i>{{ userContext.position_title || 'មន្ត្រី' }}
                        </span>
                        <span v-if="userContext.department_name" class="badge badge-light-translucent mr-2 mb-1">
                          <i class="fas fa-building mr-1"></i>{{ userContext.department_name }}
                        </span>
                        <span v-if="userContext.office_name" class="badge badge-light-translucent mr-2 mb-1">
                          <i class="fas fa-door-open mr-1"></i>{{ userContext.office_name }}
                        </span>
                      </div>
                      <div class="text-white-80 small mt-2 font-khmer">
                        <i class="far fa-calendar-alt text-warning mr-1"></i>{{ userContext.today_date_kh || currentDateKhmer }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- View Switcher & Action Controls -->
                <div class="col-xl-5 col-lg-5 col-md-12 text-lg-right">
                  <!-- Mode Switcher (For Admins and Leadership) -->
                  <div v-if="canSwitchView" class="mb-3">
                    <div class="btn-group view-switcher-group shadow-sm p-1 rounded-pill bg-white-translucent" role="group">
                      <button
                        type="button"
                        class="btn btn-sm rounded-pill px-3 py-1 font-khmer font-weight-bold transition-all"
                        :class="activeViewMode === 'institution' ? 'btn-light text-dark shadow-sm' : 'btn-transparent text-white'"
                        @click="activeViewMode = 'institution'"
                      >
                        <i class="fas fa-landmark mr-1 text-primary"></i> ទិដ្ឋភាពស្ថាប័ន
                      </button>
                      <button
                        type="button"
                        class="btn btn-sm rounded-pill px-3 py-1 font-khmer font-weight-bold transition-all"
                        :class="activeViewMode === 'personal' ? 'btn-light text-dark shadow-sm' : 'btn-transparent text-white'"
                        @click="activeViewMode = 'personal'"
                      >
                        <i class="fas fa-user-check mr-1 text-success"></i> កិច្ចការផ្ទាល់ខ្លួន
                      </button>
                    </div>
                  </div>

                  <!-- Quick Action Buttons -->
                  <div class="quick-action-bar d-flex flex-wrap justify-content-lg-end">
                    <router-link
                      to="/inbound-documents"
                      class="btn btn-sm btn-quick-action mr-2 mb-2 shadow-sm font-khmer"
                      title="គ្រប់គ្រង និងតាមដានឯកសារចូល"
                    >
                      <i class="fas fa-file-import text-info mr-1"></i> ឯកសារចូល
                      <span v-if="inboundStats.my_todo_count > 0" class="badge badge-danger ml-1">
                        {{ inboundStats.my_todo_count }}
                      </span>
                    </router-link>

                    <router-link
                      to="/leave-requests"
                      class="btn btn-sm btn-quick-action mr-2 mb-2 shadow-sm font-khmer"
                      title="ដាក់ពាក្យស្នើសុំច្បាប់ឈប់សម្រាក"
                    >
                      <i class="fas fa-plane-departure text-warning mr-1"></i> ការសុំច្បាប់
                    </router-link>

                    <router-link
                      to="/weekly-reports"
                      class="btn btn-sm btn-quick-action mr-2 mb-2 shadow-sm font-khmer"
                      title="របាយការណ៍ការងារប្រចាំសប្តាហ៍"
                    >
                      <i class="fas fa-clipboard-check text-success mr-1"></i> របាយការណ៍សប្តាហ៍
                    </router-link>

                    <router-link
                      to="/meeting-rooms"
                      class="btn btn-sm btn-quick-action mr-2 mb-2 shadow-sm font-khmer"
                      title="កក់បន្ទប់ប្រជុំ"
                    >
                      <i class="fas fa-door-open text-primary mr-1"></i> បន្ទប់ប្រជុំ
                    </router-link>

                    <router-link
                      to="/my-profile"
                      class="btn btn-sm btn-quick-action mb-2 shadow-sm font-khmer"
                      title="មើលប្រវត្តិរូប និងបោះពុម្ព"
                    >
                      <i class="fas fa-id-card text-secondary mr-1"></i> ប្រវត្តិរូប
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 2. KPI Summary Cards (Role-Adaptive) -->
          <!-- A. Institution View Mode (Admin / Leadership) -->
          <div v-if="activeViewMode === 'institution'" class="row mb-4">
            <!-- Card 1: ឯកសារចូលកំពុងដំណើរការ -->
            <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
              <div class="card stat-card shadow-sm border-0 rounded-xl h-100 stat-card-blue">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <span class="stat-label text-muted small font-weight-bold d-block font-khmer">ឯកសារចូលកំពុងដំណើរការ</span>
                      <h2 class="stat-number font-weight-bold mb-0 text-dark">
                        {{ inboundStats.admin?.total_active || 0 }}
                        <small class="text-muted text-xs">/ {{ inboundStats.admin?.total_all || 0 }}</small>
                      </h2>
                    </div>
                    <div class="stat-icon-box bg-blue-subtle text-primary">
                      <i class="fas fa-folder-open fa-lg"></i>
                    </div>
                  </div>
                  <div class="stat-footer-tags mt-2 pt-2 border-top d-flex flex-wrap gap-1 font-khmer small">
                    <span class="badge badge-light text-muted mr-1" title="រង់ចាំពិនិត្យនៅទទួល/ជំនួយការ">
                      ទទួល: {{ inboundStats.admin?.reception_pending || 0 }}
                    </span>
                    <span class="badge badge-light text-muted mr-1" title="រង់ចាំចំណារ ឯកឧត្តមប្រតិភូ">
                      រង់ចាំចំណារ: {{ inboundStats.admin?.dg_pending || 0 }}
                    </span>
                    <span class="badge badge-light text-muted" title="កំពុងរៀបចំឆ្លើយតប">
                      ឆ្លើយតប: {{ inboundStats.admin?.in_response || 0 }}
                    </span>
                  </div>
                </div>
                <router-link to="/inbound-documents" class="stat-card-footer px-3 py-1 font-khmer text-xs d-flex justify-content-between align-items-center">
                  <span>គ្រប់គ្រងឯកសារចូល</span>
                  <i class="fas fa-arrow-right"></i>
                </router-link>
              </div>
            </div>

            <!-- Card 2: ឯកសារហួសកំណត់ និងជិតដល់កំណត់ -->
            <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
              <div class="card stat-card shadow-sm border-0 rounded-xl h-100" :class="(inboundStats.admin?.system_overdue > 0) ? 'stat-card-danger' : 'stat-card-warning'">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <span class="stat-label text-muted small font-weight-bold d-block font-khmer">ឯកសារហួស / ជិតដល់កំណត់</span>
                      <h2 class="stat-number font-weight-bold mb-0" :class="(inboundStats.admin?.system_overdue > 0) ? 'text-danger' : 'text-warning'">
                        {{ inboundStats.admin?.system_overdue || 0 }}
                        <small class="text-xs text-muted">ហួសកំណត់</small>
                      </h2>
                    </div>
                    <div class="stat-icon-box" :class="(inboundStats.admin?.system_overdue > 0) ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning'">
                      <i class="fas fa-hourglass-half fa-lg"></i>
                    </div>
                  </div>
                  <div class="stat-footer-tags mt-2 pt-2 border-top d-flex justify-content-between align-items-center font-khmer small">
                    <span class="text-muted">
                      <i class="fas fa-exclamation-circle mr-1 text-warning"></i>ជិតដល់ (≤ 3 ថ្ងៃ):
                      <strong>{{ inboundStats.admin?.system_due_soon || 0 }}</strong>
                    </span>
                    <span v-if="inboundStats.admin?.system_overdue > 0" class="badge badge-danger blink-badge">
                      បន្ទាន់
                    </span>
                  </div>
                </div>
                <router-link to="/inbound-documents" class="stat-card-footer px-3 py-1 font-khmer text-xs d-flex justify-content-between align-items-center">
                  <span>ពិនិត្យឯកសារបន្ទាន់</span>
                  <i class="fas fa-arrow-right"></i>
                </router-link>
              </div>
            </div>

            <!-- Card 3: សំណើរង់ចាំការអនុម័ត (ច្បាប់, បន្ទប់ប្រជុំ, របាយការណ៍) -->
            <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
              <div class="card stat-card shadow-sm border-0 rounded-xl h-100 stat-card-amber">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <span class="stat-label text-muted small font-weight-bold d-block font-khmer">សំណើរង់ចាំការអនុម័ត</span>
                      <h2 class="stat-number font-weight-bold mb-0 text-dark">
                        {{ totalAdminPendingApprovals }}
                        <small class="text-xs text-muted">សំណើ</small>
                      </h2>
                    </div>
                    <div class="stat-icon-box bg-warning-subtle text-warning">
                      <i class="fas fa-tasks fa-lg"></i>
                    </div>
                  </div>
                  <div class="stat-footer-tags mt-2 pt-2 border-top d-flex flex-wrap gap-1 font-khmer small">
                    <span class="badge badge-light text-muted mr-1">
                      ច្បាប់: {{ leaveStats.pending_approvals_count || 0 }}
                    </span>
                    <span class="badge badge-light text-muted mr-1">
                      បន្ទប់: {{ roomStats.pending_room_bookings_count || 0 }}
                    </span>
                    <span class="badge badge-light text-muted">
                      របាយការណ៍: {{ weeklyReportStats.pending_reviews_count || 0 }}
                    </span>
                  </div>
                </div>
                <router-link to="/leave-requests" class="stat-card-footer px-3 py-1 font-khmer text-xs d-flex justify-content-between align-items-center">
                  <span>មើលសំណើទាំងអស់</span>
                  <i class="fas fa-arrow-right"></i>
                </router-link>
              </div>
            </div>

            <!-- Card 4: វត្តមានមន្ត្រីថ្ងៃនេះ -->
            <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
              <div class="card stat-card shadow-sm border-0 rounded-xl h-100 stat-card-green">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <span class="stat-label text-muted small font-weight-bold d-block font-khmer">វត្តមានមន្ត្រីថ្ងៃនេះ</span>
                      <h2 class="stat-number font-weight-bold mb-0 text-success">
                        {{ attendanceStats.summary_today?.present || 0 }}
                        <small class="text-muted text-xs">/ {{ attendanceStats.summary_today?.total_employees || hrStats.totalEmployees || 0 }} នាក់</small>
                      </h2>
                    </div>
                    <div class="stat-icon-box bg-success-subtle text-success">
                      <i class="fas fa-user-check fa-lg"></i>
                    </div>
                  </div>
                  <div class="stat-footer-tags mt-2 pt-2 border-top d-flex flex-wrap font-khmer small justify-content-between">
                    <span class="text-warning small" title="មកយឺត">
                      <i class="fas fa-clock mr-1"></i>យឺត: {{ attendanceStats.summary_today?.late || 0 }}
                    </span>
                    <span class="text-danger small" title="អវត្តមាន ឬមានច្បាប់">
                      <i class="fas fa-user-times mr-1"></i>ច្បាប់/អវត្ត: {{ attendanceStats.summary_today?.absent_permission || 0 }}
                    </span>
                    <span class="text-info small" title="ជាប់បេសកកម្ម">
                      <i class="fas fa-plane mr-1"></i>បេសកកម្ម: {{ attendanceStats.summary_today?.mission || 0 }}
                    </span>
                  </div>
                </div>
                <a href="#attendance-explorer" class="stat-card-footer px-3 py-1 font-khmer text-xs d-flex justify-content-between align-items-center">
                  <span>មើលបញ្ជីវត្តមានលម្អិត</span>
                  <i class="fas fa-arrow-down"></i>
                </a>
              </div>
            </div>
          </div>

          <!-- B. Personal Workspace Mode (User & Officer Focus) -->
          <div v-else class="row mb-4">
            <!-- Card 1: ឯកសារត្រូវចាត់ចែង (My To-Do) -->
            <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
              <div class="card stat-card shadow-sm border-0 rounded-xl h-100 stat-card-blue">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <span class="stat-label text-muted small font-weight-bold d-block font-khmer">ឯកសារត្រូវចាត់ចែង (To-Do)</span>
                      <h2 class="stat-number font-weight-bold mb-0 text-primary">
                        {{ inboundStats.my_todo_count || 0 }}
                        <small class="text-muted text-xs">ឯកសារ</small>
                      </h2>
                    </div>
                    <div class="stat-icon-box bg-blue-subtle text-primary">
                      <i class="fas fa-file-signature fa-lg"></i>
                    </div>
                  </div>
                  <div class="stat-footer-tags mt-2 pt-2 border-top d-flex justify-content-between align-items-center font-khmer small">
                    <span class="text-muted">
                      បានរួចរាល់៖ <strong>{{ inboundStats.my_done_count || 0 }}</strong>
                    </span>
                    <span v-if="inboundStats.my_todo_count > 0" class="badge badge-primary">
                      សកម្ម
                    </span>
                  </div>
                </div>
                <router-link :to="{ name: 'inbound-documents', query: { tab: 'my_todo' } }" class="stat-card-footer px-3 py-1 font-khmer text-xs d-flex justify-content-between align-items-center">
                  <span>ចាត់ចែងឯកសារ</span>
                  <i class="fas fa-arrow-right"></i>
                </router-link>
              </div>
            </div>

            <!-- Card 2: ឯកសារជិតដល់ / ហួសកំណត់របស់ខ្ញុំ -->
            <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
              <div class="card stat-card shadow-sm border-0 rounded-xl h-100" :class="(inboundStats.my_overdue_count > 0) ? 'stat-card-danger' : 'stat-card-teal'">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <span class="stat-label text-muted small font-weight-bold d-block font-khmer">កាលកំណត់ឯកសាររបស់ខ្ញុំ</span>
                      <h2 class="stat-number font-weight-bold mb-0" :class="(inboundStats.my_overdue_count > 0) ? 'text-danger' : 'text-info'">
                        {{ inboundStats.my_overdue_count || 0 }}
                        <small class="text-xs text-muted">ហួសកំណត់</small>
                      </h2>
                    </div>
                    <div class="stat-icon-box" :class="(inboundStats.my_overdue_count > 0) ? 'bg-danger-subtle text-danger' : 'bg-info-subtle text-info'">
                      <i class="fas fa-stopwatch fa-lg"></i>
                    </div>
                  </div>
                  <div class="stat-footer-tags mt-2 pt-2 border-top d-flex justify-content-between align-items-center font-khmer small">
                    <span class="text-muted">
                      <i class="fas fa-clock mr-1 text-warning"></i>ជិតដល់ (≤ 3 ថ្ងៃ):
                      <strong>{{ inboundStats.my_due_soon_count || 0 }}</strong>
                    </span>
                    <span v-if="inboundStats.my_overdue_count > 0" class="badge badge-danger">
                      ត្រូវដោះស្រាយ
                    </span>
                    <span v-else class="badge badge-success">
                      ទាន់ពេល
                    </span>
                  </div>
                </div>
                <router-link :to="{ name: 'inbound-documents', query: { tab: 'my_todo', deadline_filter: (inboundStats.my_overdue_count > 0 ? 'overdue' : 'due_soon') } }" class="stat-card-footer px-3 py-1 font-khmer text-xs d-flex justify-content-between align-items-center">
                  <span>ពិនិត្យកាលកំណត់</span>
                  <i class="fas fa-arrow-right"></i>
                </router-link>
              </div>
            </div>

            <!-- Card 3: ច្បាប់ឈប់សម្រាករបស់ខ្ញុំ -->
            <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
              <div class="card stat-card shadow-sm border-0 rounded-xl h-100 stat-card-amber">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <span class="stat-label text-muted small font-weight-bold d-block font-khmer">ច្បាប់ឈប់សម្រាកក្នុងឆ្នាំនេះ</span>
                      <h2 class="stat-number font-weight-bold mb-0 text-dark">
                        {{ leaveStats.my_approved_days_year || 0 }}
                        <small class="text-xs text-muted">ថ្ងៃអនុម័ត</small>
                      </h2>
                    </div>
                    <div class="stat-icon-box bg-warning-subtle text-warning">
                      <i class="fas fa-calendar-alt fa-lg"></i>
                    </div>
                  </div>
                  <div class="stat-footer-tags mt-2 pt-2 border-top d-flex justify-content-between align-items-center font-khmer small">
                    <span class="text-muted">
                      រង់ចាំអនុម័ត៖ <strong>{{ leaveStats.my_pending_count || 0 }}</strong>
                    </span>
                    <router-link to="/leave-requests" class="badge badge-warning text-dark">
                      + ស្នើសុំ
                    </router-link>
                  </div>
                </div>
                <router-link to="/leave-requests" class="stat-card-footer px-3 py-1 font-khmer text-xs d-flex justify-content-between align-items-center">
                  <span>ប្រវត្តិច្បាប់របស់ខ្ញុំ</span>
                  <i class="fas fa-arrow-right"></i>
                </router-link>
              </div>
            </div>

            <!-- Card 4: របាយការណ៍សប្តាហ៍នេះ -->
            <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
              <div class="card stat-card shadow-sm border-0 rounded-xl h-100 stat-card-purple">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <span class="stat-label text-muted small font-weight-bold d-block font-khmer">របាយការណ៍សប្តាហ៍</span>
                      <h2 class="stat-number font-weight-bold mb-0 text-dark">
                        សប្តាហ៍ {{ weeklyReportStats.current_week_number || currentWeekNumber }}
                      </h2>
                    </div>
                    <div class="stat-icon-box bg-purple-subtle text-purple">
                      <i class="fas fa-clipboard-list fa-lg"></i>
                    </div>
                  </div>
                  <div class="stat-footer-tags mt-2 pt-2 border-top d-flex justify-content-between align-items-center font-khmer small">
                    <span v-if="weeklyReportStats.my_report_this_week" class="badge badge-success">
                      <i class="fas fa-check-circle mr-1"></i>បានដាក់ស្នើ
                    </span>
                    <span v-else class="badge badge-secondary">
                      <i class="fas fa-edit mr-1"></i>មិនទាន់ដាក់ស្នើ
                    </span>
                    <router-link to="/weekly-reports" class="text-primary small font-weight-bold">
                      ចូលរៀបចំ
                    </router-link>
                  </div>
                </div>
                <router-link to="/weekly-reports" class="stat-card-footer px-3 py-1 font-khmer text-xs d-flex justify-content-between align-items-center">
                  <span>ផ្ទាំងរបាយការណ៍សប្តាហ៍</span>
                  <i class="fas fa-arrow-right"></i>
                </router-link>
              </div>
            </div>
          </div>

          <!-- 3. Main Dashboard Workspace Layout (2 Columns) -->
          <div class="row">
            <!-- Left Column: Core Workflows (Inbound Documents, Room Bookings, Attendance Explorer) -->
            <div class="col-lg-8">
              
              <!-- 3.1 Inbound Documents Focus Table -->
              <div class="card card-outline card-primary shadow-sm rounded-xl mb-4 border-top-3">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap">
                  <div class="d-flex align-items-center">
                    <div class="header-icon-circle bg-blue-subtle text-primary mr-2">
                      <i class="fas fa-file-import"></i>
                    </div>
                    <div>
                      <h5 class="card-title font-weight-bold mb-0 font-khmer text-dark">
                        {{ activeViewMode === 'personal' ? 'ឯកសារចូលដែលត្រូវចាត់ចែង (My To-Do)' : 'ឯកសារចូលថ្មីៗក្នុងស្ថាប័ន' }}
                      </h5>
                      <span class="text-muted small font-khmer d-block">
                        {{ activeViewMode === 'personal' ? 'បញ្ជីឯកសាររង់ចាំចំណារ ឬការឆ្លើយតបរបស់អ្នក' : 'បញ្ជីឯកសារទើបបានចុះបញ្ជី និងដំណើរការក្នុងប្រព័ន្ធ' }}
                      </span>
                    </div>
                  </div>
                  <div class="card-tools mt-2 mt-sm-0">
                    <router-link to="/inbound-documents" class="btn btn-sm btn-outline-primary rounded-pill font-khmer px-3">
                      មើលឯកសារទាំងអស់ <i class="fas fa-arrow-right ml-1"></i>
                    </router-link>
                  </div>
                </div>

                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 font-khmer">
                      <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                          <th style="width: 140px;">លេខលិខិត</th>
                          <th>កម្មវត្ថុ / ស្ថាប័នចេញ</th>
                          <th style="width: 110px;">ភាពបន្ទាន់</th>
                          <th style="width: 130px;">កាលកំណត់</th>
                          <th style="width: 150px;">ស្ថានភាព</th>
                          <th class="text-center" style="width: 100px;">សកម្មភាព</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="displayedInboundDocs.length === 0">
                          <td colspan="6" class="text-center py-5 text-muted">
                            <div class="py-3">
                              <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                              <p class="mb-0 font-weight-bold">
                                {{ activeViewMode === 'personal' ? 'មិនមានឯកសាររង់ចាំការចាត់ចែងនៅឡើយទេ (កិច្ចការរបស់អ្នករួចរាល់ទាំងអស់!)' : 'មិនទាន់មានឯកសារចូលថ្មីនៅឡើយទេ' }}
                              </p>
                              <small class="text-muted">ប្រព័ន្ធនឹងបង្ហាញឯកសារដោយស្វ័យប្រវត្តិនៅពេលមានលំហូរឯកសារថ្មី</small>
                            </div>
                          </td>
                        </tr>
                        <tr v-for="doc in displayedInboundDocs" :key="doc.id">
                          <!-- Document Number -->
                          <td>
                            <div class="font-weight-bold text-dark text-nowrap">
                              <span class="badge badge-secondary mr-1">ទូទៅ</span>{{ doc.general_inbound_number || '---' }}
                            </div>
                            <div v-if="doc.dg_inbound_number" class="small text-muted text-nowrap mt-1">
                              <span class="badge badge-info mr-1">ឯ.ឧ</span>{{ doc.dg_inbound_number }}
                            </div>
                          </td>

                          <!-- Title & Sender -->
                          <td>
                            <div class="font-weight-bold text-dark line-clamp-2" :title="doc.title">
                              {{ doc.title }}
                            </div>
                            <div class="small text-muted mt-1">
                              <i class="fas fa-university text-secondary mr-1"></i>{{ doc.sender_organization || 'មិនបញ្ជាក់' }}
                            </div>
                          </td>

                          <!-- Urgency -->
                          <td>
                            <span class="badge" :class="getUrgencyBadgeClass(doc.urgency)">
                              {{ getUrgencyLabel(doc.urgency) }}
                            </span>
                          </td>

                          <!-- Deadline Status -->
                          <td>
                            <div v-if="doc.deadline">
                              <span class="badge" :class="getDeadlineBadgeClass(doc)">
                                {{ getDeadlineLabel(doc) }}
                              </span>
                              <div class="text-xs text-muted mt-1">
                                {{ formatDateShort(doc.deadline) }}
                              </div>
                            </div>
                            <div v-else>
                              <span class="badge badge-light text-muted">គ្មានកំណត់</span>
                            </div>
                          </td>

                          <!-- Status -->
                          <td>
                            <span class="badge" :class="getStatusBadgeClass(doc.status)">
                              {{ doc.status_kh || getStatusLabel(doc.status) }}
                            </span>
                          </td>

                          <!-- Action -->
                          <td class="text-center">
                            <div class="btn-group">
                              <router-link
                                :to="{ name: 'inbound-documents.routing-slip', params: { id: doc.id } }"
                                class="btn btn-xs btn-outline-info rounded-pill px-2"
                                title="បោះពុម្ព ឬមើលបណ្ណបញ្ជូនសារ (Routing Slip)"
                              >
                                <i class="fas fa-route"></i> បណ្ណ
                              </router-link>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- 3.2 Today's Meeting Rooms & Daily Schedules -->
              <div class="card card-outline card-success shadow-sm rounded-xl mb-4 border-top-3">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap">
                  <div class="d-flex align-items-center">
                    <div class="header-icon-circle bg-success-subtle text-success mr-2">
                      <i class="fas fa-door-open"></i>
                    </div>
                    <div>
                      <h5 class="card-title font-weight-bold mb-0 font-khmer text-dark">
                        កាលវិភាគប្រើប្រាស់បន្ទប់ប្រជុំ និងកម្មវិធីថ្ងៃនេះ
                      </h5>
                      <span class="text-muted small font-khmer d-block">
                        ការកក់បន្ទប់ប្រជុំដែលបានអនុម័ត និងកាលវិភាគការងារប្រចាំថ្ងៃ
                      </span>
                    </div>
                  </div>
                  <div class="card-tools mt-2 mt-sm-0">
                    <router-link to="/meeting-rooms" class="btn btn-sm btn-outline-success rounded-pill font-khmer px-3 mr-2">
                      <i class="fas fa-plus-circle mr-1"></i> កក់បន្ទប់
                    </router-link>
                    <router-link to="/work-schedules" class="btn btn-sm btn-outline-secondary rounded-pill font-khmer px-3">
                      <i class="fas fa-calendar-alt mr-1"></i> កាលវិភាគ
                    </router-link>
                  </div>
                </div>

                <div class="card-body p-3">
                  <!-- Bookings List -->
                  <div v-if="roomStats.today_bookings && roomStats.today_bookings.length > 0" class="row">
                    <div
                      v-for="booking in roomStats.today_bookings"
                      :key="booking.id"
                      class="col-md-6 mb-3"
                    >
                      <div class="booking-card border rounded-lg p-3 h-100 bg-white shadow-xs position-relative hover-lift transition-all">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                          <span class="time-badge px-2 py-1 rounded bg-light text-primary font-weight-bold small font-khmer">
                            <i class="far fa-clock mr-1"></i>{{ formatTime(booking.start_time) }} - {{ formatTime(booking.end_time) }}
                          </span>
                          <span class="badge badge-success-subtle text-success font-khmer">
                            បានអនុម័ត
                          </span>
                        </div>
                        <h6 class="font-weight-bold text-dark font-khmer mb-1">
                          {{ booking.title }}
                        </h6>
                        <div class="text-muted small font-khmer mb-2">
                          <i class="fas fa-map-marker-alt text-danger mr-1"></i>
                          <strong class="text-dark">{{ booking.room?.name || booking.preferred_room?.name || 'បន្ទប់ប្រជុំ' }}</strong>
                          <span v-if="booking.participants_count" class="badge badge-light ml-2">
                            <i class="fas fa-users mr-1"></i>{{ booking.participants_count }} នាក់
                          </span>
                        </div>
                        <div class="d-flex align-items-center mt-2 pt-2 border-top text-muted small font-khmer">
                          <img
                            :src="getFullImageUrl(booking.user?.profile_image)"
                            class="img-circle mr-2 border"
                            style="width: 24px; height: 24px; object-fit: cover;"
                          />
                          <span>អ្នករៀបចំ: <strong>{{ booking.user?.name_kh || booking.user?.name || 'មន្ត្រី' }}</strong></span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Schedules List if no room bookings -->
                  <div v-else-if="roomStats.today_schedules && roomStats.today_schedules.length > 0" class="row">
                    <div
                      v-for="sched in roomStats.today_schedules"
                      :key="sched.id"
                      class="col-md-6 mb-3"
                    >
                      <div class="booking-card border rounded-lg p-3 h-100 bg-white shadow-xs font-khmer">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                          <span class="badge badge-info">{{ sched.type || 'កម្មវិធី' }}</span>
                          <span class="text-muted small">{{ formatTime(sched.start_time) }}</span>
                        </div>
                        <h6 class="font-weight-bold text-dark mb-1">{{ sched.title }}</h6>
                        <p class="text-muted small mb-0"><i class="fas fa-map-marker-alt mr-1 text-danger"></i>{{ sched.location || 'ទីស្តីការក្រសួង' }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Empty State -->
                  <div v-else class="text-center py-4 text-muted font-khmer">
                    <i class="fas fa-calendar-day fa-3x text-light-gray mb-2"></i>
                    <p class="mb-1 font-weight-bold text-secondary">មិនទាន់មានការកក់បន្ទប់ប្រជុំសម្រាប់ថ្ងៃនេះទេ</p>
                    <small class="text-muted">លោកអ្នកអាចចុច "កក់បន្ទប់" ដើម្បីស្នើសុំប្រើប្រាស់បន្ទប់ប្រជុំ</small>
                  </div>
                </div>
              </div>

              <!-- 3.3 Attendance Details by Date (អវត្តមាន/ច្បាប់, មកយឺត, បេសកកម្ម) -->
              <div id="attendance-explorer" class="card card-outline card-secondary shadow-sm rounded-xl mb-4 border-top-3">
                <div class="card-header bg-white py-3">
                  <div class="row align-items-center">
                    <div class="col-md-6 d-flex align-items-center mb-2 mb-md-0">
                      <div class="header-icon-circle bg-secondary-subtle text-secondary mr-2">
                        <i class="fas fa-user-clock"></i>
                      </div>
                      <div>
                        <h5 class="card-title font-weight-bold mb-0 font-khmer text-dark">
                          តារាងតាមដានវត្តមានមន្ត្រីប្រចាំថ្ងៃ
                        </h5>
                        <span class="text-muted small font-khmer d-block">
                          ពិនិត្យអវត្តមាន មានច្បាប់ មកយឺត និងចុះបេសកកម្ម
                        </span>
                      </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-center justify-content-md-end flex-nowrap">
                      <label class="font-weight-bold mb-0 mr-2 font-khmer text-secondary small text-nowrap">
                        <i class="far fa-calendar-alt mr-1"></i>កាលបរិច្ឆេទ:
                      </label>
                      <input
                        type="date"
                        class="form-control form-control-sm rounded-pill font-khmer"
                        v-model="selectedDate"
                        @change="fetchAttendanceSummary"
                        style="width: 170px;"
                      />
                    </div>
                  </div>
                </div>

                <div class="card-body p-3">
                  <div class="row">
                    <!-- 1. អវត្តមាន / ច្បាប់ -->
                    <div class="col-md-4 mb-3 mb-md-0">
                      <div class="card card-outline card-danger shadow-xs rounded-lg h-100 border-top-3">
                        <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                          <h6 class="card-title font-weight-bold text-danger font-khmer mb-0 text-sm">
                            <i class="fas fa-user-times mr-1"></i> អវត្តមាន / ច្បាប់
                          </h6>
                          <span class="badge badge-danger rounded-pill">{{ absentOrPermissionList.length }} នាក់</span>
                        </div>
                        <div class="card-body p-0 custom-scrollbar" style="max-height: 320px; overflow-y: auto;">
                          <ul class="products-list product-list-in-card px-2">
                            <li v-if="absentOrPermissionList.length === 0" class="item text-center py-4 text-muted font-khmer small">
                              មិនមានទិន្នន័យអវត្តមានទេ
                            </li>
                            <li v-for="item in absentOrPermissionList" :key="item.id" class="item d-flex align-items-center py-2">
                              <img
                                :src="getFullImageUrl(item.user?.profile_thumbnail || item.user?.profile_image)"
                                class="img-circle border mr-2 flex-shrink-0"
                                style="width: 40px; height: 40px; object-fit: cover;"
                              />
                              <div class="flex-grow-1 min-w-0 font-khmer">
                                <div class="d-flex justify-content-between align-items-center">
                                  <span class="font-weight-bold text-dark small text-truncate">
                                    {{ item.user?.name_kh || item.user?.name }}
                                  </span>
                                  <span class="badge badge-xs" :class="item.status === 'PERMISSION' ? 'badge-info' : 'badge-danger'">
                                    {{ item.status === 'PERMISSION' ? 'មានច្បាប់' : 'អវត្តមាន' }}
                                  </span>
                                </div>
                                <span class="text-muted text-xs d-block text-truncate">
                                  មូលហេតុ: {{ item.note || '---' }}
                                </span>
                              </div>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>

                    <!-- 2. មកយឺត -->
                    <div class="col-md-4 mb-3 mb-md-0">
                      <div class="card card-outline card-warning shadow-xs rounded-lg h-100 border-top-3">
                        <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                          <h6 class="card-title font-weight-bold text-warning font-khmer mb-0 text-sm">
                            <i class="fas fa-clock mr-1"></i> មកយឺត
                          </h6>
                          <span class="badge badge-warning rounded-pill">{{ lateList.length }} នាក់</span>
                        </div>
                        <div class="card-body p-0 custom-scrollbar" style="max-height: 320px; overflow-y: auto;">
                          <ul class="products-list product-list-in-card px-2">
                            <li v-if="lateList.length === 0" class="item text-center py-4 text-muted font-khmer small">
                              មិនមានមន្ត្រីមកយឺតទេ
                            </li>
                            <li v-for="item in lateList" :key="item.id" class="item d-flex align-items-center py-2">
                              <img
                                :src="getFullImageUrl(item.user?.profile_thumbnail || item.user?.profile_image)"
                                class="img-circle border mr-2 flex-shrink-0"
                                style="width: 40px; height: 40px; object-fit: cover;"
                              />
                              <div class="flex-grow-1 min-w-0 font-khmer">
                                <div class="d-flex justify-content-between align-items-center">
                                  <span class="font-weight-bold text-dark small text-truncate">
                                    {{ item.user?.name_kh || item.user?.name }}
                                  </span>
                                  <span class="badge badge-warning text-dark text-xs">
                                    {{ formatTime(item.check_in_time) }}
                                  </span>
                                </div>
                                <span class="text-muted text-xs d-block text-truncate">
                                  មូលហេតុ: {{ item.note || '---' }}
                                </span>
                              </div>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>

                    <!-- 3. បេសកកម្ម -->
                    <div class="col-md-4">
                      <div class="card card-outline card-primary shadow-xs rounded-lg h-100 border-top-3">
                        <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                          <h6 class="card-title font-weight-bold text-primary font-khmer mb-0 text-sm">
                            <i class="fas fa-plane-departure mr-1"></i> បេសកកម្ម
                          </h6>
                          <span class="badge badge-primary rounded-pill">{{ missionList.length }} នាក់</span>
                        </div>
                        <div class="card-body p-0 custom-scrollbar" style="max-height: 320px; overflow-y: auto;">
                          <ul class="products-list product-list-in-card px-2">
                            <li v-if="missionList.length === 0" class="item text-center py-4 text-muted font-khmer small">
                              មិនមានមន្ត្រីជាប់បេសកកម្មទេ
                            </li>
                            <li v-for="item in missionList" :key="item.id" class="item d-flex align-items-center py-2">
                              <img
                                :src="getFullImageUrl(item.user?.profile_thumbnail || item.user?.profile_image)"
                                class="img-circle border mr-2 flex-shrink-0"
                                style="width: 40px; height: 40px; object-fit: cover;"
                              />
                              <div class="flex-grow-1 min-w-0 font-khmer">
                                <div class="d-flex justify-content-between align-items-center">
                                  <span class="font-weight-bold text-dark small text-truncate">
                                    {{ item.user?.name_kh || item.user?.name }}
                                  </span>
                                  <span class="badge badge-primary text-xs">បេសកកម្ម</span>
                                </div>
                                <span class="text-muted text-xs d-block text-truncate">
                                  ទីកន្លែង: {{ item.note || '---' }}
                                </span>
                              </div>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>

            </div>

            <!-- Right Column: Personal Status, Weekly Report, Leave Widget, Staff HR Stats, Birthdays -->
            <div class="col-lg-4">
              
              <!-- 3.4 My Attendance Status Card Today -->
              <div class="card shadow-sm border-0 rounded-xl mb-4 stat-card-teal overflow-hidden">
                <div class="card-body p-3 font-khmer">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="font-weight-bold text-dark small">
                      <i class="fas fa-fingerprint text-info mr-1"></i>វត្តមានផ្ទាល់ខ្លួនថ្ងៃនេះ
                    </span>
                    <router-link to="/my-attendances" class="text-info small font-weight-bold">
                      មើលប្រវត្តិ <i class="fas fa-chevron-right ml-1"></i>
                    </router-link>
                  </div>

                  <div class="d-flex align-items-center p-3 rounded-lg bg-white shadow-xs">
                    <div class="checkin-status-circle mr-3" :class="myAttendanceToday ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'">
                      <i :class="myAttendanceToday ? 'fas fa-check-circle fa-2x' : 'fas fa-clock fa-2x'"></i>
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="font-weight-bold mb-1" :class="myAttendanceToday ? 'text-success' : 'text-warning'">
                        {{ myAttendanceToday ? 'បានចុះវត្តមានរួចរាល់' : 'មិនទាន់ចុះវត្តមានថ្ងៃនេះ' }}
                      </h6>
                      <div class="text-muted small" v-if="myAttendanceToday">
                        <span>ម៉ោងចូល៖ <strong>{{ formatTime(myAttendanceToday.check_in_time) }}</strong></span>
                        <span v-if="myAttendanceToday.check_out_time" class="ml-2">
                          | ម៉ោងចេញ៖ <strong>{{ formatTime(myAttendanceToday.check_out_time) }}</strong>
                        </span>
                      </div>
                      <div class="text-muted small" v-else>
                        សូមចុចម៉ាស៊ីនស្កេន ឬទាក់ទងរដ្ឋបាល
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- 3.5 Weekly Report Progress Widget -->
              <div class="card card-outline card-info shadow-sm rounded-xl mb-4 border-top-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                  <h6 class="card-title font-weight-bold mb-0 font-khmer text-dark">
                    <i class="fas fa-clipboard-check text-info mr-2"></i>របាយការណ៍ការងារប្រចាំសប្តាហ៍
                  </h6>
                  <span class="badge badge-info rounded-pill font-khmer">
                    សប្តាហ៍ទី {{ weeklyReportStats.current_week_number || currentWeekNumber }}
                  </span>
                </div>
                <div class="card-body p-3 font-khmer">
                  <!-- User's Weekly Report Status -->
                  <div class="p-3 rounded-lg bg-light mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <span class="text-muted small font-weight-bold">ស្ថានភាពរបាយការណ៍សប្តាហ៍នេះ:</span>
                      <span v-if="weeklyReportStats.my_report_this_week" class="badge badge-success font-khmer">
                        {{ weeklyReportStats.my_report_this_week.status === 'APPROVED' ? 'បានអនុម័ត' : 'បានដាក់ស្នើ' }}
                      </span>
                      <span v-else class="badge badge-warning text-dark font-khmer">
                        មិនទាន់ដាក់ស្នើ
                      </span>
                    </div>
                    <p class="text-muted small mb-0" v-if="weeklyReportStats.my_report_this_week">
                      កាលបរិច្ឆេទដាក់ស្នើ៖ {{ formatDateShort(weeklyReportStats.my_report_this_week.submitted_at) }}
                    </p>
                    <p class="text-muted small mb-0" v-else>
                      កុំភ្លេចបំពេញ និងដាក់ស្នើរបាយការណ៍ឱ្យបានទាន់ពេលកំណត់
                    </p>
                  </div>

                  <!-- Admin / Supervisor Extra: Pending Reviews -->
                  <div v-if="weeklyReportStats.pending_reviews_count > 0" class="alert alert-warning py-2 px-3 small font-khmer mb-3 rounded-lg">
                    <i class="fas fa-bell mr-1"></i> មានរបាយការណ៍រង់ចាំការពិនិត្យចំនួន <strong>{{ weeklyReportStats.pending_reviews_count }}</strong> ច្បាប់
                  </div>

                  <router-link to="/weekly-reports" class="btn btn-block btn-outline-info rounded-pill font-khmer">
                    <i class="fas fa-pen-nib mr-1"></i> ចូលទៅកាន់របាយការណ៍សប្តាហ៍
                  </router-link>
                </div>
              </div>

              <!-- 3.6 Leave Requests / Who is on leave today -->
              <div class="card card-outline card-warning shadow-sm rounded-xl mb-4 border-top-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                  <h6 class="card-title font-weight-bold mb-0 font-khmer text-dark">
                    <i class="fas fa-calendar-minus text-warning mr-2"></i>ច្បាប់ឈប់សម្រាកថ្ងៃនេះ
                  </h6>
                  <span class="badge badge-warning rounded-pill text-dark font-khmer">
                    {{ leaveStats.on_leave_today_count || 0 }} នាក់
                  </span>
                </div>
                <div class="card-body p-0">
                  <ul class="products-list product-list-in-card px-3">
                    <li v-if="!leaveStats.on_leave_today_list || leaveStats.on_leave_today_list.length === 0" class="item text-center py-4 text-muted font-khmer small">
                      មិនមានមន្ត្រីឈប់សម្រាកនៅថ្ងៃនេះទេ
                    </li>
                    <li v-for="leave in leaveStats.on_leave_today_list" :key="leave.id" class="item d-flex align-items-center py-2">
                      <img
                        :src="getFullImageUrl(leave.user?.profile_image)"
                        class="img-circle border mr-2 flex-shrink-0"
                        style="width: 38px; height: 38px; object-fit: cover;"
                      />
                      <div class="flex-grow-1 min-w-0 font-khmer">
                        <div class="d-flex justify-content-between align-items-center">
                          <span class="font-weight-bold text-dark small text-truncate">
                            {{ leave.user?.name_kh || leave.user?.name }}
                          </span>
                          <span class="badge badge-info text-xs">{{ leave.leave_type || 'ច្បាប់' }}</span>
                        </div>
                        <span class="text-muted text-xs d-block">
                          {{ formatDateShort(leave.start_date) }} ដល់ {{ formatDateShort(leave.end_date) }} ({{ leave.duration_days }} ថ្ងៃ)
                        </span>
                      </div>
                    </li>
                  </ul>
                  <div class="p-3 border-top text-center font-khmer">
                    <router-link to="/leave-requests" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 font-weight-bold">
                      <i class="fas fa-paper-plane mr-1"></i> ស្នើសុំច្បាប់ឈប់សម្រាក
                    </router-link>
                  </div>
                </div>
              </div>

              <!-- 3.7 Staff & HR Structure Widget (For Admin / Info) -->
              <div class="card card-outline card-primary shadow-sm rounded-xl mb-4 border-top-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                  <h6 class="card-title font-weight-bold mb-0 font-khmer text-dark">
                    <i class="fas fa-users-cog text-primary mr-2"></i>ស្ថិតិធនធានមនុស្សសរុប
                  </h6>
                  <router-link to="/users" class="btn btn-xs btn-outline-primary rounded-pill px-2 font-khmer">
                    មើលបញ្ជី <i class="fas fa-arrow-right ml-1"></i>
                  </router-link>
                </div>
                <div class="card-body p-3 font-khmer">
                  <!-- Total Staff -->
                  <div class="d-flex justify-content-between align-items-center p-2 mb-2 rounded bg-light">
                    <span class="font-weight-bold text-dark">
                      <i class="fas fa-users text-primary mr-2"></i>មន្ត្រីសរុបក្នុងប្រព័ន្ធ
                    </span>
                    <span class="font-weight-bold text-dark">
                      {{ hrStats.totalEmployees || 0 }} នាក់
                      <small class="text-muted font-normal">(ស្រី {{ hrStats.fEmployees || 0 }})</small>
                    </span>
                  </div>

                  <!-- Civil Service -->
                  <div class="d-flex justify-content-between align-items-center p-2 mb-2 rounded bg-light">
                    <span class="text-secondary small">
                      <i class="fas fa-building text-success mr-2"></i>មន្ត្រីមុខងារសាធារណៈ
                    </span>
                    <span class="font-weight-bold small text-dark">
                      {{ hrStats.civilServants || 0 }} នាក់
                      <small class="text-muted font-normal">(ស្រី {{ hrStats.fcivilServants || 0 }})</small>
                    </span>
                  </div>

                  <!-- Statutory -->
                  <div class="d-flex justify-content-between align-items-center p-2 mb-2 rounded bg-light">
                    <span class="text-secondary small">
                      <i class="fas fa-user-tie text-warning mr-2"></i>មន្ត្រីលក្ខន្តិកៈ
                    </span>
                    <span class="font-weight-bold small text-dark">
                      {{ hrStats.statutory || 0 }} នាក់
                      <small class="text-muted font-normal">(ស្រី {{ hrStats.fstatutory || 0 }})</small>
                    </span>
                  </div>

                  <!-- Contract Staff -->
                  <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light">
                    <span class="text-secondary small">
                      <i class="fas fa-file-signature text-danger mr-2"></i>មន្ត្រីជាប់កិច្ចសន្យា
                    </span>
                    <span class="font-weight-bold small text-dark">
                      {{ hrStats.contractStaff || 0 }} នាក់
                      <small class="text-muted font-normal">(ស្រី {{ hrStats.fcontractStaff || 0 }})</small>
                    </span>
                  </div>
                </div>
              </div>

              <!-- 3.8 Birthdays This Month Widget 🎂 -->
              <div class="card card-outline card-danger shadow-sm rounded-xl mb-4 border-top-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                  <h6 class="card-title font-weight-bold mb-0 font-khmer text-dark">
                    <i class="fas fa-birthday-cake text-warning mr-2"></i>ខួបកំណើតមន្ត្រីក្នុងខែនេះ
                  </h6>
                  <span class="badge badge-danger rounded-pill font-khmer">
                    {{ birthdays.length }} នាក់
                  </span>
                </div>
                <div class="card-body p-0">
                  <ul class="products-list product-list-in-card px-3 custom-scrollbar" style="max-height: 320px; overflow-y: auto;">
                    <li v-if="birthdays.length === 0" class="item text-center py-4 text-muted font-khmer small">
                      មិនមានមន្ត្រីដែលមានខួបកំណើតក្នុងខែនេះទេ
                    </li>
                    <li v-for="user in birthdays" :key="user.id" class="item d-flex align-items-center py-2" :class="{ 'birthday-today-highlight': user.is_today }">
                      <div class="position-relative mr-2 flex-shrink-0">
                        <img
                          :src="getFullImageUrl(user.profile_image)"
                          class="img-circle border"
                          style="width: 44px; height: 44px; object-fit: cover;"
                        />
                        <span v-if="user.is_today" class="birthday-cake-badge" title="ខួបកំណើតថ្ងៃនេះ!">🎂</span>
                      </div>
                      <div class="flex-grow-1 min-w-0 font-khmer">
                        <div class="d-flex justify-content-between align-items-center">
                          <span class="font-weight-bold text-dark small text-truncate">
                            {{ user.name_kh }}
                          </span>
                          <span v-if="user.is_today" class="badge badge-danger blink-text text-xs">
                            🎉 ថ្ងៃនេះ!
                          </span>
                          <span v-else class="badge badge-light border text-xs">
                            {{ user.dob_formatted }}
                          </span>
                        </div>
                        <span class="text-muted text-xs d-block text-truncate">
                          {{ user.position_name }} <span v-if="user.age">| អាយុ {{ user.age }} ឆ្នាំ</span>
                        </span>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useUserStore } from "@/stores/user";
import { apiGetDashboardStats } from '@/functions/api/dashboard';
import axios from 'axios';

const userStore = useUserStore();
const defaultAvatar = 'https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg';

// State Variables
const loading = ref(true);
const activeViewMode = ref('personal'); // 'personal' or 'institution'
const userContext = ref({});
const inboundStats = ref({
  my_todo_count: 0,
  my_done_count: 0,
  my_due_soon_count: 0,
  my_overdue_count: 0,
  recent_todo_docs: [],
  admin: {}
});
const leaveStats = ref({
  my_pending_count: 0,
  my_approved_days_year: 0,
  my_recent_leaves: [],
  pending_approvals_count: 0,
  on_leave_today_count: 0,
  on_leave_today_list: []
});
const weeklyReportStats = ref({
  current_week_number: 1,
  current_year: new Date().getFullYear(),
  my_report_this_week: null,
  pending_reviews_count: 0,
  submitted_this_week_count: 0
});
const roomStats = ref({
  today_bookings: [],
  today_schedules: [],
  pending_room_bookings_count: 0
});
const attendanceStats = ref({
  my_attendance: null,
  summary_today: {}
});
const hrStats = ref({});
const birthdays = ref([]);

// Attendance Explorer State
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const absentOrPermissionList = ref([]);
const lateList = ref([]);
const missionList = ref([]);

// Computed Properties
const canSwitchView = computed(() => {
  return userContext.value.is_admin || userContext.value.is_leadership || userStore.isAdmin || userStore.hasAnyAdminPermission;
});

const totalAdminPendingApprovals = computed(() => {
  const l = Number(leaveStats.value.pending_approvals_count) || 0;
  const r = Number(roomStats.value.pending_room_bookings_count) || 0;
  const w = Number(weeklyReportStats.value.pending_reviews_count) || 0;
  return l + r + w;
});

const myAttendanceToday = computed(() => {
  return attendanceStats.value.my_attendance;
});

const displayedInboundDocs = computed(() => {
  if (activeViewMode.value === 'personal') {
    return inboundStats.value.recent_todo_docs || [];
  }
  return inboundStats.value.admin?.recent_inbound || inboundStats.value.recent_todo_docs || [];
});

const currentWeekNumber = computed(() => {
  const now = new Date();
  const onejan = new Date(now.getFullYear(), 0, 1);
  return Math.ceil((((now.getTime() - onejan.getTime()) / 86400000) + onejan.getDay() + 1) / 7);
});

const currentDateKhmer = computed(() => {
  const now = new Date();
  const khmerDays = ['អាទិត្យ', 'ចន្ទ', 'អង្គារ', 'ពុធ', 'ព្រហស្បតិ៍', 'សុក្រ', 'សៅរ៍'];
  const khmerMonths = ['', 'មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'];
  return `ថ្ងៃ${khmerDays[now.getDay()]} ទី${String(now.getDate()).padStart(2, '0')} ខែ${khmerMonths[now.getMonth() + 1]} ឆ្នាំ${now.getFullYear()}`;
});

// Helper Functions
const getFullImageUrl = (path) => {
  if (!path) return defaultAvatar;
  if (path.startsWith('http')) return path;

  const backendBase = (import.meta.env.VITE_APP_API_URL || 'http://localhost:8000').replace(/\/api\/?$/, '');
  let cleanPath = path.replace(/^\//, '');
  if (!cleanPath.startsWith('storage/')) cleanPath = `storage/${cleanPath}`;

  return `${backendBase}/${cleanPath}`;
};

const formatTime = (timeStr) => {
  if (!timeStr) return '--:--';
  if (timeStr.length > 5 && timeStr.includes(':')) {
    return timeStr.substring(0, 5);
  }
  return timeStr;
};

const formatDateShort = (dateStr) => {
  if (!dateStr) return '---';
  try {
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-GB'); // DD/MM/YYYY
  } catch (e) {
    return dateStr;
  }
};

const getUrgencyBadgeClass = (urgency) => {
  switch (urgency) {
    case 'MOST_URGENT':
      return 'badge-danger blink-badge font-weight-bold';
    case 'URGENT':
      return 'badge-danger';
    case 'HIGH':
      return 'badge-warning text-dark';
    case 'NORMAL':
    default:
      return 'badge-light border text-muted';
  }
};

const getUrgencyLabel = (urgency) => {
  switch (urgency) {
    case 'MOST_URGENT':
      return 'បន្ទាន់បំផុត';
    case 'URGENT':
      return 'បន្ទាន់ណាស់';
    case 'HIGH':
      return 'បន្ទាន់';
    case 'NORMAL':
    default:
      return 'ធម្មតា';
  }
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'RECEPTION_DRAFT':
      return 'badge-secondary';
    case 'SUBMITTED_TO_ASSISTANT':
      return 'badge-info';
    case 'SUBMITTED_TO_DG':
      return 'badge-primary';
    case 'DG_ANNOTATED':
      return 'badge-teal text-white';
    case 'IN_RESPONSE_PROGRESS':
      return 'badge-warning text-dark';
    case 'DISPATCHED':
      return 'badge-indigo text-white';
    case 'COMPLETED':
      return 'badge-success';
    case 'CANCELLED':
      return 'badge-dark';
    default:
      return 'badge-light border';
  }
};

const getStatusLabel = (status) => {
  switch (status) {
    case 'RECEPTION_DRAFT':
      return 'ទទួលដំបូង';
    case 'SUBMITTED_TO_ASSISTANT':
      return 'ជូនជំនួយការ';
    case 'SUBMITTED_TO_DG':
      return 'ជូន ឯ.ឧ. ប្រតិភូ';
    case 'DG_ANNOTATED':
      return 'មានចំណារ ឯ.ឧ. ប្រតិភូ';
    case 'IN_RESPONSE_PROGRESS':
      return 'កំពុងរៀបចំឆ្លើយតប';
    case 'DISPATCHED':
      return 'បានចេញលិខិត';
    case 'COMPLETED':
      return 'បានបញ្ចប់';
    case 'CANCELLED':
      return 'បានបដិសេធ';
    default:
      return status || '---';
  }
};

const getDeadlineBadgeClass = (doc) => {
  if (!doc.deadline) return 'badge-light text-muted';
  if (doc.deadline_status === 'OVERDUE') return 'badge-danger blink-badge';
  if (doc.deadline_status === 'DUE_SOON') return 'badge-warning text-dark';
  return 'badge-success-subtle text-success border border-success';
};

const getDeadlineLabel = (doc) => {
  if (doc.deadline_remaining_kh) return doc.deadline_remaining_kh;
  if (!doc.deadline) return 'គ្មានកាលកំណត់';
  return 'តាមកាលកំណត់';
};

// API Calls
const fetchDashboardData = async () => {
  loading.value = true;
  try {
    const res = await apiGetDashboardStats();
    const data = res.data;

    userContext.value = data.user_context || {};
    inboundStats.value = data.inbound_stats || {};
    leaveStats.value = data.leave_stats || {};
    weeklyReportStats.value = data.weekly_report_stats || {};
    roomStats.value = data.room_stats || {};
    attendanceStats.value = data.attendance_stats || {};
    hrStats.value = data.hr_stats || data.stats || {};
    birthdays.value = data.birthdays || [];

    // Default view mode: If user is admin/leadership, default to institution view, else personal
    if (userContext.value.is_admin || userContext.value.is_leadership || userStore.isAdmin) {
      activeViewMode.value = 'institution';
    } else {
      activeViewMode.value = 'personal';
    }

    await fetchAttendanceSummary();
  } catch (error) {
    console.error("Error fetching dashboard data:", error);
  } finally {
    loading.value = false;
  }
};

const fetchAttendanceSummary = async () => {
  try {
    const attendanceRes = await axios.get(
      `${import.meta.env.VITE_APP_API_URL}/manage/get-dashboard-summary?date=${selectedDate.value}`
    );
    const allAttendances = attendanceRes.data.attendances || [];

    // 1. អវត្តមាន ឬ មានច្បាប់ (ABSENT & PERMISSION)
    absentOrPermissionList.value = allAttendances.filter(item => {
      const s = String(item.status).toUpperCase();
      return s === 'ABSENT' || s === 'PERMISSION';
    });

    // 2. មកយឺត (LATE or > 09:00)
    lateList.value = allAttendances.filter(item => {
      const status = String(item.status).toUpperCase();
      const checkIn = item.check_in_time ? item.check_in_time.substring(0, 5) : null;
      if (status === 'LATE' || (checkIn && checkIn > '09:00')) {
        return true;
      }
      return false;
    });

    // 3. បេសកកម្ម (MISSION)
    missionList.value = allAttendances.filter(item => String(item.status).toUpperCase() === 'MISSION');
  } catch (error) {
    console.error("Error fetching attendance summary:", error);
  }
};

onMounted(() => {
  fetchDashboardData();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap');

.khmer-layout, .font-khmer {
  font-family: 'Battambang', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
}

.content-wrapper {
  background-color: #f1f5f9;
}

.rounded-xl {
  border-radius: 14px !important;
}

.rounded-lg {
  border-radius: 10px !important;
}

/* Hero Welcome Banner */
.hero-banner {
  background: linear-gradient(135deg, #0d3b30 0%, #155e4b 60%, #1a6f59 100%);
  color: white;
  position: relative;
  box-shadow: 0 10px 25px -5px rgba(13, 59, 48, 0.25) !important;
}

.hero-bg-accent {
  position: absolute;
  top: -50px;
  right: -50px;
  width: 250px;
  height: 250px;
  background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 70%);
  border-radius: 50%;
  pointer-events: none;
}

.hero-avatar {
  width: 76px;
  height: 76px;
  object-fit: cover;
}

.status-indicator-dot {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  border: 2px solid white;
}

.greeting-badge {
  background: rgba(255, 255, 255, 0.15);
  font-size: 13px;
  color: #fef08a;
  backdrop-filter: blur(4px);
}

.user-name-kh {
  font-size: 1.5rem;
  letter-spacing: 0.2px;
}

.badge-light-translucent {
  background: rgba(255, 255, 255, 0.18);
  color: #f8fafc;
  font-weight: 500;
  backdrop-filter: blur(4px);
}

.bg-white-translucent {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(8px);
}

.quick-action-bar .btn-quick-action {
  background: rgba(255, 255, 255, 0.95);
  color: #1e293b;
  border-radius: 20px;
  font-weight: 600;
  padding: 5px 14px;
  transition: all 0.2s ease;
  border: none;
}

.quick-action-bar .btn-quick-action:hover {
  background: #ffffff;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  color: #0f172a;
}

/* Stat Cards */
.stat-card {
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  background-color: #ffffff;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.08) !important;
}

.stat-icon-box {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.stat-card-footer {
  background-color: #f8fafc;
  border-top: 1px solid #f1f5f9;
  color: #64748b;
  text-decoration: none;
  border-bottom-left-radius: 14px;
  border-bottom-right-radius: 14px;
  transition: background-color 0.2s ease, color 0.2s ease;
}

.stat-card:hover .stat-card-footer {
  background-color: #f1f5f9;
  color: #0f172a;
}

/* Color Accents */
.bg-blue-subtle { background-color: #e0f2fe !important; }
.bg-danger-subtle { background-color: #ffe4e6 !important; }
.bg-warning-subtle { background-color: #fef3c7 !important; }
.bg-success-subtle { background-color: #dcfce7 !important; }
.bg-purple-subtle { background-color: #ede9fe !important; }
.bg-secondary-subtle { background-color: #f1f5f9 !important; }

.badge-teal { background-color: #0d9488 !important; }
.badge-indigo { background-color: #4f46e5 !important; }
.badge-xs { font-size: 10px; padding: 2px 6px; }

.text-purple { color: #7c3aed !important; }
.text-xs { font-size: 11px; }

.header-icon-circle {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
}

.border-top-3 {
  border-top-width: 3px !important;
}

/* Booking Card */
.booking-card {
  border-color: #e2e8f0 !important;
}

.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.checkin-status-circle {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Birthday Highlight */
.birthday-today-highlight {
  background-color: #fff1f2;
  border-radius: 8px;
}

.birthday-cake-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  font-size: 14px;
}

.blink-badge {
  animation: pulse-danger 1.8s infinite;
}

@keyframes pulse-danger {
  0% { opacity: 1; }
  50% { opacity: 0.6; }
  100% { opacity: 1; }
}

.blink-text {
  animation: blinker 1.5s linear infinite;
}

@keyframes blinker {
  50% { opacity: 0.5; }
}

/* Products list */
.products-list .item {
  padding: 10px 4px !important;
  border-bottom: 1px solid #f1f5f9;
}

.products-list .item:last-child {
  border-bottom: none;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>