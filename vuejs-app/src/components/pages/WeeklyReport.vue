<template>
  <div class="content-wrapper" style="min-height: 900px;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 class="m-0 font-khmer text-dark font-weight-bold">
              <i class="fas fa-tasks text-info mr-2"></i> តាមដានការងារ និងរបាយការណ៍ប្រចាំសប្តាហ៍
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right font-khmer">
              <li class="breadcrumb-item">
                <router-link :to="{ name: 'dashboard' }">ទំព័រដើម</router-link>
              </li>
              <li class="breadcrumb-item active">របាយការណ៍ប្រចាំសប្តាហ៍</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- Main content -->
    <section class="content font-khmer">
      <div class="container-fluid">
        
        <!-- 1. LIVE TASK TRACKING KPI WIDGETS -->
        <div class="d-flex justify-content-between align-items-center mb-2" v-if="activeTab === 'subordinates'">
          <div class="d-flex align-items-center">
            <i class="fas fa-chart-pie text-primary mr-2" style="font-size: 18px;"></i>
            <span class="font-weight-bold text-dark mr-2">ស្ថិតិកិច្ចការមន្ត្រីក្រោមឱវាទ៖</span>
            <span class="badge badge-info px-2 py-1" style="font-size: 13px; font-weight: normal;">
              <i class="fas fa-filter mr-1"></i>{{ currentFilterScopeLabel }}
            </span>
          </div>
          <button
            v-if="filterDepartmentId || filterOfficeId || filterUserId"
            class="btn btn-xs btn-outline-secondary"
            @click="resetSubordinateFilter"
            title="ត្រឡប់ទៅមើលសរុបទាំងអស់"
          >
            <i class="fas fa-undo mr-1"></i> មើលសរុបទាំងអស់
          </button>
        </div>

        <div class="row mb-3">
          <!-- 1. Total Tasks -->
          <div class="col-xl-2 col-lg-4 col-6 mb-2">
            <div class="small-box bg-gradient-info shadow-sm rounded-lg mb-0">
              <div class="inner p-2 text-center">
                <h3 class="mb-0 font-weight-bold">{{ toKhmerNum(currentTaskStats.tasks_total || 0) }}</h3>
                <p class="font-weight-bold mb-0 text-sm">ការងារសរុប</p>
                <small class="text-white-50">{{ activeTab === 'my' ? 'ការងាររបស់ខ្ញុំ' : currentFilterScopeLabel }}</small>
              </div>
              <div class="icon" style="top: 5px; right: 10px; font-size: 40px; opacity: 0.3;">
                <i class="fas fa-clipboard-list"></i>
              </div>
            </div>
          </div>

          <!-- 2. Pending / Not Started -->
          <div class="col-xl-2 col-lg-4 col-6 mb-2">
            <div class="small-box bg-gradient-secondary shadow-sm rounded-lg mb-0">
              <div class="inner p-2 text-center">
                <h3 class="mb-0 font-weight-bold">{{ toKhmerNum(currentTaskStats.tasks_pending || 0) }}</h3>
                <p class="font-weight-bold mb-0 text-sm">មិនទាន់ធ្វើ</p>
                <small class="text-white-50">រង់ចាំចាប់ផ្តើម (0%)</small>
              </div>
              <div class="icon" style="top: 5px; right: 10px; font-size: 40px; opacity: 0.3;">
                <i class="fas fa-hourglass-start"></i>
              </div>
            </div>
          </div>

          <!-- 3. In Progress -->
          <div class="col-xl-3 col-lg-4 col-6 mb-2">
            <div class="small-box bg-gradient-primary shadow-sm rounded-lg mb-0">
              <div class="inner p-2 text-center">
                <h3 class="mb-0 font-weight-bold">{{ toKhmerNum(currentTaskStats.tasks_in_progress || 0) }}</h3>
                <p class="font-weight-bold mb-0 text-sm">កំពុងធ្វើ</p>
                <small class="text-white-50">ដំណើរការអនុវត្តជាក់ស្តែង</small>
              </div>
              <div class="icon" style="top: 5px; right: 10px; font-size: 40px; opacity: 0.3;">
                <i class="fas fa-spinner fa-spin-pulse"></i>
              </div>
            </div>
          </div>

          <!-- 4. Completed -->
          <div class="col-xl-3 col-lg-6 col-6 mb-2">
            <div class="small-box bg-gradient-success shadow-sm rounded-lg mb-0">
              <div class="inner p-2 text-center">
                <h3 class="mb-0 font-weight-bold">{{ toKhmerNum(currentTaskStats.tasks_completed || 0) }}</h3>
                <p class="font-weight-bold mb-0 text-sm">បានបញ្ចប់</p>
                <small class="text-white-50">សម្រេចរួចរាល់ (100%)</small>
              </div>
              <div class="icon" style="top: 5px; right: 10px; font-size: 40px; opacity: 0.3;">
                <i class="fas fa-check-circle"></i>
              </div>
            </div>
          </div>

          <!-- 5. Completion Rate -->
          <div class="col-xl-2 col-lg-6 col-12 mb-2">
            <div class="small-box bg-gradient-warning shadow-sm rounded-lg mb-0">
              <div class="inner p-2 text-center">
                <h3 class="mb-0 font-weight-bold text-dark">{{ toKhmerNum(currentTaskStats.tasks_completion_rate || 0) }}%</h3>
                <p class="font-weight-bold mb-0 text-dark text-sm">អត្រាសម្រេច</p>
                <small class="text-dark-50">ភាគរយសម្រេចការងារ</small>
              </div>
              <div class="icon" style="top: 5px; right: 10px; font-size: 40px; opacity: 0.3;">
                <i class="fas fa-chart-line"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. NAVIGATION TABS & FILTER BAR -->
        <div class="card card-primary card-outline card-outline-tabs shadow-sm mb-4">
          <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" role="tablist">
              <!-- Tab 1: My Reports -->
              <li class="nav-item">
                <a
                  class="nav-link font-weight-bold cursor-pointer"
                  :class="{ active: activeTab === 'my' }"
                  @click="switchTab('my')"
                >
                  <i class="fas fa-user-edit mr-1 text-primary"></i> របាយការណ៍ & កិច្ចការរបស់ខ្ញុំ
                  <span class="badge badge-primary ml-1" v-if="statsData.my_stats?.total">
                    {{ toKhmerNum(statsData.my_stats.total) }} របាយការណ៍
                  </span>
                </a>
              </li>

              <!-- Tab 2: Subordinates Reports (Only if leader/admin) -->
              <li class="nav-item" v-if="statsData.can_review">
                <a
                  class="nav-link font-weight-bold cursor-pointer"
                  :class="{ active: activeTab === 'subordinates' }"
                  @click="switchTab('subordinates')"
                >
                  <i class="fas fa-users-cog mr-1 text-success"></i> តាមដានការងារមន្ត្រីក្រោមឱវាទ
                  <span class="badge badge-danger ml-1" v-if="statsData.subordinates_stats?.pending_review">
                    {{ toKhmerNum(statsData.subordinates_stats.pending_review) }} ថ្មី
                  </span>
                  <span class="badge badge-success ml-1" v-else-if="statsData.subordinates_stats?.total">
                    {{ toKhmerNum(statsData.subordinates_stats.total) }}
                  </span>
                </a>
              </li>
            </ul>
          </div>

          <div class="card-body p-3">
            <!-- Filter Bar -->
            <div class="row align-items-center mb-3">
              <!-- Left: Year & Month Filter -->
              <div class="col-md-3 col-sm-6 mb-2">
                <div class="input-group input-group-sm">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-light font-weight-bold"><i class="fas fa-calendar mr-1"></i> ឆ្នាំ</span>
                  </div>
                  <select v-model="filterYear" class="form-control" @change="onFilterChange">
                    <option v-for="y in availableYears" :key="y" :value="y">{{ toKhmerNum(y) }}</option>
                  </select>
                </div>
              </div>

              <div class="col-md-3 col-sm-6 mb-2">
                <div class="input-group input-group-sm">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-light font-weight-bold"><i class="fas fa-calendar-alt mr-1"></i> ខែ</span>
                  </div>
                  <select v-model="filterMonth" class="form-control" @change="onFilterChange">
                    <option v-for="m in 12" :key="m" :value="m">ខែ{{ getKhmerMonthName(m) }}</option>
                  </select>
                </div>
              </div>

              <div class="col-md-2 col-sm-6 mb-2">
                <div class="input-group input-group-sm">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-light font-weight-bold">សប្តាហ៍</span>
                  </div>
                  <select v-model="filterWeek" class="form-control" @change="onFilterChange">
                    <option value="">ទាំងអស់</option>
                    <option v-for="w in 5" :key="w" :value="w">សប្តាហ៍ទី{{ toKhmerNum(w) }}</option>
                  </select>
                </div>
              </div>

              <div class="col-md-2 col-sm-6 mb-2">
                <div class="input-group input-group-sm">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-light font-weight-bold">ស្ថានភាព</span>
                  </div>
                  <select v-model="filterStatus" class="form-control" @change="onFilterChange">
                    <option value="">ទាំងអស់</option>
                    <option value="DRAFT" v-if="activeTab === 'my'">សេចក្តីព្រាង</option>
                    <option value="SUBMITTED">បានដាក់ជូន</option>
                    <option value="REVIEWED">បានពិនិត្យ</option>
                  </select>
                </div>
              </div>

              <!-- Right: New Report Button (in My Reports Tab) -->
              <div class="col-md-2 col-sm-12 mb-2 text-md-right">
                <button
                  v-if="activeTab === 'my'"
                  class="btn btn-primary btn-sm btn-block shadow-sm"
                  @click="openCreateModal"
                >
                  <i class="fas fa-plus mr-1"></i> កត់ត្រារបាយការណ៍
                </button>
              </div>
            </div>

            <!-- Additional Hierarchy Filters (For Subordinates Tab) -->
            <div v-if="activeTab === 'subordinates'" class="row border-top pt-3 mb-2 bg-light p-2 rounded align-items-end">
              <!-- Department Filter (if multiple depts accessible) -->
              <div class="col-md-3 col-sm-6 mb-2" v-if="filterOptions.departments && filterOptions.departments.length > 1">
                <label class="small text-muted font-weight-bold mb-1">នាយកដ្ឋាន៖</label>
                <select v-model="filterDepartmentId" class="form-control form-control-sm" @change="onDepartmentChange">
                  <option value="">គ្រប់នាយកដ្ឋាន (សរុប)</option>
                  <option v-for="d in filterOptions.departments" :key="d.id" :value="d.id">
                    {{ d.name_kh }}
                  </option>
                </select>
              </div>

              <!-- Office Filter -->
              <div class="col-md-3 col-sm-6 mb-2" v-if="filteredOffices.length > 0">
                <label class="small text-muted font-weight-bold mb-1">ការិយាល័យ៖</label>
                <select v-model="filterOfficeId" class="form-control form-control-sm" @change="onOfficeChange">
                  <option value="">គ្រប់ការិយាល័យ (សរុប)</option>
                  <option v-for="o in filteredOffices" :key="o.id" :value="o.id">
                    {{ o.name_kh }}
                  </option>
                </select>
              </div>

              <!-- Officer Filter -->
              <div class="col-md-3 col-sm-6 mb-2" v-if="filteredOfficers.length > 0">
                <label class="small text-muted font-weight-bold mb-1">មន្ត្រីក្រោមឱវាទ៖</label>
                <select v-model="filterUserId" class="form-control form-control-sm" @change="onFilterChange">
                  <option value="">គ្រប់មន្ត្រីទាំងអស់ (សរុប)</option>
                  <option v-for="u in filteredOfficers" :key="u.id" :value="u.id">
                    {{ u.name_kh || u.name }} ({{ u.position?.title_kh || 'មន្ត្រី' }})
                  </option>
                </select>
              </div>

              <!-- Search Query -->
              <div :class="filteredOfficers.length > 0 ? 'col-md-3 col-sm-6 mb-2' : 'col-md-5 col-sm-12 mb-2'">
                <label class="small text-muted font-weight-bold mb-1">ស្វែងរកតាមឈ្មោះ/ខ្លឹមសារ៖</label>
                <div class="input-group input-group-sm">
                  <input
                    type="text"
                    v-model="searchQuery"
                    class="form-control"
                    placeholder="បញ្ចូលឈ្មោះ ឬពាក្យគន្លឹះ..."
                    @keyup.enter="onFilterChange"
                  />
                  <div class="input-group-append">
                    <button class="btn btn-default" type="button" @click="onFilterChange">
                      <i class="fas fa-search"></i>
                    </button>
                    <button
                      v-if="filterDepartmentId || filterOfficeId || filterUserId || searchQuery"
                      class="btn btn-outline-danger"
                      type="button"
                      @click="resetSubordinateFilter"
                      title="សម្អាត Filter មើលសរុបទាំងអស់"
                    >
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Loading Spinner -->
            <div v-if="loading" class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="sr-only">កំពុងដំណើរការ...</span>
              </div>
              <p class="mt-2 text-muted">កំពុងទាញយកទិន្នន័យ...</p>
            </div>

            <!-- EMPTY STATE -->
            <div v-else-if="reports.length === 0" class="text-center py-5 bg-light rounded my-3">
              <i class="fas fa-clipboard-check fa-4x text-muted mb-3"></i>
              <h5 class="text-secondary font-weight-bold">មិនទាន់មានរបាយការណ៍សម្រាប់សប្តាហ៍/ខែនេះនៅឡើយទេ</h5>
              <p class="text-muted small">
                {{ activeTab === 'my' ? 'លោកអ្នកអាចចាប់ផ្តើមកត់ត្រារបាយការណ៍សប្តាហ៍ថ្មីបានដោយចុចប៊ូតុង "កត់ត្រារបាយការណ៍" ខាងលើ។' : 'មិនទាន់មានមន្ត្រីក្រោមឱវាទណាម្នាក់បានដាក់ជូនរបាយការណ៍នៅឡើយទេ។' }}
              </p>
              <button
                v-if="activeTab === 'my'"
                class="btn btn-primary btn-sm px-3 shadow-sm mt-2"
                @click="openCreateModal"
              >
                <i class="fas fa-plus mr-1"></i> កត់ត្រារបាយការណ៍ឥឡូវនេះ
              </button>
            </div>

            <!-- 3. DATA TABLE -->
            <div v-else class="table-responsive">
              <table class="table table-hover table-striped border table-valign-middle">
                <thead class="thead-light">
                  <tr>
                    <th style="width: 45px;" class="text-center">ល.រ</th>
                    <th v-if="activeTab === 'subordinates'" style="width: 210px;">មន្ត្រីរាយការណ៍</th>
                    <th style="width: 140px;">សប្តាហ៍ / កាលបរិច្ឆេទ</th>
                    <th style="min-width: 260px;">ស្ថានភាពកិច្ចការងារ (Tasks Breakdown)</th>
                    <th style="width: 90px;" class="text-center">ឯកសារ</th>
                    <th style="width: 130px;" class="text-center">ស្ថានភាព</th>
                    <th style="width: 165px;" class="text-center">សកម្មភាព</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(report, index) in reports" :key="report.id">
                    <!-- Index -->
                    <td class="text-center text-muted font-weight-bold">
                      {{ toKhmerNum(index + 1) }}
                    </td>

                    <!-- Submitter info (Subordinates Tab) -->
                    <td v-if="activeTab === 'subordinates'">
                      <div class="d-flex align-items-center">
                        <img
                          :src="getUserAvatar(report.user)"
                          class="img-circle elevation-1 mr-2"
                          style="width: 38px; height: 38px; object-fit: cover;"
                          alt="Avatar"
                        />
                        <div>
                          <div class="font-weight-bold text-dark">
                            {{ report.user?.name_kh || report.user?.name }}
                          </div>
                          <div class="small text-primary font-weight-bold">
                            {{ report.position?.title_kh || 'មន្ត្រី' }}
                          </div>
                          <div class="small text-muted" v-if="report.office">
                            {{ report.office?.name_kh }}
                          </div>
                        </div>
                      </div>
                    </td>

                    <!-- Week & Dates -->
                    <td>
                      <span class="badge badge-info mb-1 d-inline-block">
                        សប្តាហ៍ទី{{ toKhmerNum(report.week_number) }}
                      </span>
                      <div class="small text-muted font-weight-bold">
                        {{ formatShortDate(report.start_date) }} - {{ formatShortDate(report.end_date) }}
                      </div>
                      <small class="text-muted" v-if="report.submitted_at">
                        ដាក់ជូន៖ {{ formatKhmerDateTime(report.submitted_at) }}
                      </small>
                    </td>

                    <!-- Tasks Breakdown & Status Metrics -->
                    <td>
                      <div class="font-weight-bold text-dark mb-1">
                        {{ report.title }}
                      </div>

                      <!-- Task Counters Pill Badges -->
                      <div class="d-flex flex-wrap gap-1 align-items-center mb-1">
                        <span class="badge badge-light border text-dark mr-1" title="ចំនួនការងារសរុប">
                          <i class="fas fa-tasks mr-1 text-secondary"></i> សរុប: <strong>{{ toKhmerNum(getReportTaskCounts(report).total) }}</strong>
                        </span>
                        <span class="badge badge-success mr-1" title="ការងារបានបញ្ចប់">
                          <i class="fas fa-check mr-1"></i> បញ្ចប់: <strong>{{ toKhmerNum(getReportTaskCounts(report).completed) }}</strong>
                        </span>
                        <span class="badge badge-primary mr-1" title="ការងារកំពុងធ្វើ">
                          <i class="fas fa-spinner fa-spin-pulse mr-1"></i> កំពុងធ្វើ: <strong>{{ toKhmerNum(getReportTaskCounts(report).in_progress) }}</strong>
                        </span>
                        <span class="badge badge-secondary mr-1" title="ការងារមិនទាន់ធ្វើ">
                          <i class="fas fa-clock mr-1"></i> មិនទាន់ធ្វើ: <strong>{{ toKhmerNum(getReportTaskCounts(report).pending) }}</strong>
                        </span>
                      </div>

                      <!-- Progress Bar -->
                      <div class="progress progress-xs mt-1" style="height: 6px;" :title="'សម្រេចបាន ' + getReportTaskCounts(report).rate + '%'">
                        <div
                          class="progress-bar bg-success"
                          :style="{ width: getReportTaskCounts(report).rate + '%' }"
                        ></div>
                      </div>

                      <!-- Supervisor remark snippet if reviewed -->
                      <div v-if="report.supervisor_remarks" class="small text-success mt-1">
                        <i class="fas fa-comment-dots mr-1"></i>
                        <span class="font-weight-bold">ចំណារ{{ report.reviewer?.position?.title_kh || 'ថ្នាក់លើ' }}៖</span> {{ report.supervisor_remarks }}
                      </div>

                      <!-- Leadership remark snippet if reviewed by DG/Deputy DG -->
                      <div v-if="report.leadership_remarks" class="small text-dark mt-1 p-1 rounded border border-warning" style="background-color: #fff9e6;">
                        <i class="fas fa-pen-nib text-warning mr-1"></i>
                        <span class="font-weight-bold text-dark">ចំណារ{{ (report.leadership_reviewer || report.leadershipReviewer)?.position?.title_kh || 'អគ្គនាយក' }}៖</span> {{ report.leadership_remarks }}
                      </div>
                    </td>

                    <!-- Attachment -->
                    <td class="text-center">
                      <a
                        v-if="report.attachment_path"
                        :href="getDownloadUrl(report.id)"
                        target="_blank"
                        class="btn btn-xs btn-outline-primary"
                        title="ទាញយកឯកសារភ្ជាប់"
                      >
                        <i class="fas fa-paperclip"></i>
                      </a>
                      <span v-else class="text-muted small font-italic">-</span>
                    </td>

                    <!-- Status -->
                    <td class="text-center">
                      <span :class="getStatusBadgeClass(report.status)">
                        <i class="fas mr-1" :class="getStatusIcon(report.status)"></i>
                        {{ getStatusKhmer(report.status) }}
                      </span>
                      <div v-if="report.reviewer" class="small text-muted mt-1" title="ពិនិត្យដោយថ្នាក់ដឹកនាំផ្ទាល់">
                        <i class="fas fa-user-check mr-1 text-success"></i>ពិនិត្យ៖ {{ report.reviewer.name_kh || report.reviewer.name }}
                      </div>
                      <div v-if="report.leadership_reviewer || report.leadershipReviewer" class="small text-warning mt-1 font-weight-bold" title="ចារបន្ថែមដោយថ្នាក់ដឹកនាំអគ្គនាយកដ្ឋាន">
                        <i class="fas fa-pen-nib mr-1"></i>ចារបន្ថែម៖ {{ (report.leadership_reviewer || report.leadershipReviewer).name_kh || (report.leadership_reviewer || report.leadershipReviewer).name }}
                      </div>
                    </td>

                    <!-- Actions -->
                    <td class="text-center">
                      <div class="btn-group btn-group-sm">
                        <!-- View Detail Button -->
                        <button
                          class="btn btn-outline-info"
                          @click="openViewModal(report)"
                          title="មើលលម្អិតកិច្ចការ"
                        >
                          <i class="fas fa-eye"></i>
                        </button>

                        <!-- Print Preview Button -->
                        <button
                          class="btn btn-outline-secondary"
                          @click="openPrintModal(report)"
                          title="បោះពុម្ពរបាយការណ៍"
                        >
                          <i class="fas fa-print"></i>
                        </button>

                        <!-- Review Button (For Leaders in Subordinates Tab) -->
                        <button
                          v-if="activeTab === 'subordinates' && canReviewReport(report)"
                          class="btn"
                          :class="isLeadershipUser ? 'btn-outline-warning text-dark' : 'btn-outline-success'"
                          @click="openReviewModal(report)"
                          :title="isLeadershipUser ? 'ចារបន្ថែមពីលើ' : 'ពិនិត្យ & ដាក់ចំណារ'"
                        >
                          <i class="fas" :class="isLeadershipUser ? 'fa-pen-nib mr-1 text-warning' : 'fa-stamp mr-1'"></i>
                          {{ isLeadershipUser ? 'ចារបន្ថែម' : 'ចំណារ' }}
                        </button>

                        <!-- Edit Button (Only My Draft or My Submitted if not reviewed) -->
                        <button
                          v-if="activeTab === 'my' && report.status !== 'REVIEWED'"
                          class="btn btn-outline-warning text-dark"
                          @click="openEditModal(report)"
                          title="កែសម្រួល"
                        >
                          <i class="fas fa-edit"></i>
                        </button>

                        <!-- Submit Button (If Draft) -->
                        <button
                          v-if="activeTab === 'my' && report.status === 'DRAFT'"
                          class="btn btn-outline-primary"
                          @click="confirmSubmitReport(report)"
                          title="ដាក់ជូនថ្នាក់លើ"
                        >
                          <i class="fas fa-paper-plane"></i>
                        </button>

                        <!-- Delete Button (If Draft or Admin) -->
                        <button
                          v-if="report.status === 'DRAFT' || userStore.isAdmin"
                          class="btn btn-outline-danger"
                          @click="confirmDeleteReport(report)"
                          title="លុប"
                        >
                          <i class="fas fa-trash-alt"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

          </div>
        </div>

      </div>
    </section>

    <!-- ============================================================= -->
    <!-- MODAL 1: CREATE / EDIT WEEKLY REPORT & TASKS                  -->
    <!-- ============================================================= -->
    <div v-if="showReportModal" class="custom-modal-backdrop" @click.self="closeReportModal">
      <div class="modal-dialog modal-xl modal-dialog-centered font-khmer my-auto" role="document" style="width: 100%; max-width: 1050px;">
        <div class="modal-content custom-modal-content d-flex flex-column h-100 overflow-hidden font-khmer shadow-lg">
          <div class="modal-header bg-primary text-white py-3">
            <h5 class="modal-title font-weight-bold">
              <i class="fas mr-2" :class="isEditing ? 'fa-edit' : 'fa-tasks'"></i>
              {{ isEditing ? 'កែសម្រួលកិច្ចការ និងរបាយការណ៍ប្រចាំសប្តាហ៍' : 'កត់ត្រាកិច្ចការ និងរបាយការណ៍ប្រចាំសប្តាហ៍ថ្មី' }}
            </h5>
            <button type="button" class="close text-white" @click="closeReportModal">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body p-4 flex-grow-1" style="overflow-y: auto;">
            <form @submit.prevent="saveReport(false)">
              
              <!-- Top Row: Week & Dates Selection -->
              <div class="row bg-light p-3 rounded mb-3">
                <div class="col-md-3 form-group mb-md-0">
                  <label class="font-weight-bold text-sm">ឆ្នាំ <span class="text-danger">*</span></label>
                  <select v-model="formData.year" class="form-control form-control-sm" @change="onFormWeekChange" :disabled="isEditing">
                    <option v-for="y in availableYears" :key="y" :value="y">{{ toKhmerNum(y) }}</option>
                  </select>
                </div>

                <div class="col-md-3 form-group mb-md-0">
                  <label class="font-weight-bold text-sm">ខែ <span class="text-danger">*</span></label>
                  <select v-model="formData.month" class="form-control form-control-sm" @change="onFormWeekChange" :disabled="isEditing">
                    <option v-for="m in 12" :key="m" :value="m">ខែ{{ getKhmerMonthName(m) }}</option>
                  </select>
                </div>

                <div class="col-md-3 form-group mb-md-0">
                  <label class="font-weight-bold text-sm">សប្តាហ៍ទី <span class="text-danger">*</span></label>
                  <select v-model="formData.week_number" class="form-control form-control-sm" @change="onFormWeekChange" :disabled="isEditing">
                    <option v-for="w in 5" :key="w" :value="w">សប្តាហ៍ទី{{ toKhmerNum(w) }}</option>
                  </select>
                </div>

                <div class="col-md-3 form-group mb-md-0">
                  <label class="font-weight-bold text-sm">កាលបរិច្ឆេទសប្តាហ៍ <span class="text-danger">*</span></label>
                  <div class="input-group input-group-sm">
                    <input type="date" v-model="formData.start_date" class="form-control" required />
                    <input type="date" v-model="formData.end_date" class="form-control" required />
                  </div>
                </div>
              </div>

              <!-- Report Title -->
              <div class="form-group">
                <label class="font-weight-bold">ចំណងជើងរបាយការណ៍ <span class="text-danger">*</span></label>
                <input
                  type="text"
                  v-model="formData.title"
                  class="form-control"
                  placeholder="បញ្ចូលចំណងជើងរបាយការណ៍..."
                  required
                />
              </div>

              <!-- SECTION: STRUCTURED TASK ITEMS (តាមដានកិច្ចការងារជាក់ស្តែង) -->
              <div class="card card-outline card-info shadow-sm mb-4">
                <div class="card-header py-2 d-flex align-items-center justify-content-between">
                  <h6 class="card-title font-weight-bold text-dark m-0">
                    <i class="fas fa-list-check text-info mr-2"></i> បញ្ជីកិច្ចការងារជាក់ស្តែង (Tasks List)
                  </h6>
                  <div class="card-tools d-flex align-items-center">
                    <!-- Live Summary Counters inside Modal -->
                    <span class="badge badge-light border mr-2">
                      សរុប: <strong>{{ toKhmerNum(formTaskSummary.total) }}</strong>
                    </span>
                    <span class="badge badge-secondary mr-2">
                      មិនទាន់ធ្វើ: <strong>{{ toKhmerNum(formTaskSummary.pending) }}</strong>
                    </span>
                    <span class="badge badge-primary mr-2">
                      កំពុងធ្វើ: <strong>{{ toKhmerNum(formTaskSummary.in_progress) }}</strong>
                    </span>
                    <span class="badge badge-success mr-3">
                      បានបញ្ចប់: <strong>{{ toKhmerNum(formTaskSummary.completed) }}</strong>
                    </span>

                    <!-- Add Task Button -->
                    <button
                      type="button"
                      class="btn btn-primary btn-sm shadow-sm"
                      @click="addTaskRow"
                    >
                      <i class="fas fa-plus mr-1"></i> បន្ថែមកិច្ចការ
                    </button>
                  </div>
                </div>

                <div class="card-body p-2">
                  <div v-if="formData.tasks.length === 0" class="text-center py-4 bg-light rounded text-muted">
                    <i class="fas fa-tasks fa-2x mb-2 text-secondary opacity-50"></i>
                    <div>មិនទាន់មានកិច្ចការងារត្រូវបានបញ្ចូលនៅឡើយទេ</div>
                    <small>សូមចុចប៊ូតុង <strong>«+ បន្ថែមកិច្ចការ»</strong> ខាងលើដើម្បីបញ្ចូលកិច្ចការងារដែលត្រូវតាមដាន។</small>
                  </div>

                  <div v-else class="table-responsive">
                    <table class="table table-bordered table-sm table-valign-middle mb-0">
                      <thead class="bg-light text-center small font-weight-bold">
                        <tr>
                          <th style="width: 40px;">ល.រ</th>
                          <th style="min-width: 250px;">ឈ្មោះកិច្ចការងារ <span class="text-danger">*</span></th>
                          <th style="width: 140px;">ស្ថានភាពកិច្ចការ <span class="text-danger">*</span></th>
                          <th style="width: 120px;">អាទិភាព</th>
                          <th style="width: 120px;">វឌ្ឍនភាព (%)</th>
                          <th>លទ្ធផលសម្រេចបាន / កំណត់សម្គាល់</th>
                          <th style="width: 45px;">លុប</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(task, tIndex) in formData.tasks" :key="tIndex">
                          <!-- Number -->
                          <td class="text-center font-weight-bold text-muted">{{ toKhmerNum(tIndex + 1) }}</td>

                          <!-- Task Name -->
                          <td>
                            <input
                              type="text"
                              v-model="task.task_name"
                              class="form-control form-control-sm"
                              placeholder="បញ្ចូលឈ្មោះកិច្ចការ..."
                              required
                            />
                          </td>

                          <!-- Status -->
                          <td>
                            <select
                              v-model="task.status"
                              class="form-control form-control-sm font-weight-bold"
                              :class="{
                                'text-success': task.status === 'COMPLETED',
                                'text-primary': task.status === 'IN_PROGRESS',
                                'text-secondary': task.status === 'PENDING'
                              }"
                              @change="onTaskStatusChange(task)"
                            >
                              <option value="PENDING">🟡 មិនទាន់ធ្វើ</option>
                              <option value="IN_PROGRESS">🔵 កំពុងធ្វើ</option>
                              <option value="COMPLETED">🟢 បានបញ្ចប់</option>
                            </select>
                          </td>

                          <!-- Priority -->
                          <td>
                            <select v-model="task.priority" class="form-control form-control-sm">
                              <option value="LOW">ទាប</option>
                              <option value="MEDIUM">មធ្យម</option>
                              <option value="HIGH">ខ្ពស់</option>
                              <option value="URGENT">បន្ទាន់</option>
                            </select>
                          </td>

                          <!-- Progress % -->
                          <td>
                            <div class="input-group input-group-sm">
                              <input
                                type="number"
                                v-model.number="task.progress_percent"
                                min="0"
                                max="100"
                                class="form-control text-center"
                                @input="onTaskProgressInput(task)"
                              />
                              <div class="input-group-append">
                                <span class="input-group-text">%</span>
                              </div>
                            </div>
                          </td>

                          <!-- Result Notes -->
                          <td>
                            <input
                              type="text"
                              v-model="task.result_notes"
                              class="form-control form-control-sm"
                              placeholder="លទ្ធផល ឬវឌ្ឍនភាព..."
                            />
                          </td>

                          <!-- Delete button -->
                          <td class="text-center">
                            <button
                              type="button"
                              class="btn btn-outline-danger btn-xs"
                              @click="removeTaskRow(tIndex)"
                              title="លុបកិច្ចការនេះ"
                            >
                              <i class="fas fa-trash-alt"></i>
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Collapsible General Narrative Sections (Optional summaries) -->
              <div class="card card-outline card-secondary shadow-sm mb-3">
                <div class="card-header py-2 cursor-pointer" @click="showNarrativeFields = !showNarrativeFields">
                  <h6 class="card-title font-weight-bold text-dark m-0">
                    <i class="fas fa-file-lines mr-2 text-secondary"></i> អត្ថបទសង្ខេបរួម (លទ្ធផលសម្រេច ការងារបន្ត និងបញ្ហាប្រឈម)
                  </h6>
                  <div class="card-tools">
                    <button type="button" class="btn btn-tool">
                      <i class="fas" :class="showNarrativeFields ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                  </div>
                </div>

                <div v-show="showNarrativeFields" class="card-body p-3">
                  <!-- Section 1: Completed Tasks -->
                  <div class="form-group">
                    <label class="font-weight-bold text-success text-sm">
                      <i class="fas fa-check-circle mr-1"></i> ១. លទ្ធផលការងារសម្រេចបានក្នុងសប្តាហ៍ (Completed Tasks Summary)
                    </label>
                    <textarea
                      v-model="formData.completed_tasks"
                      class="form-control form-control-sm"
                      rows="3"
                      placeholder="ប្រព័ន្ធនឹងបូកសរុបស្វ័យប្រវត្តិចេញពីកិច្ចការដែល «បានបញ្ចប់» ឬលោកអ្នកអាចសរសេរពិពណ៌នាបន្ថែមនៅទីនេះ..."
                    ></textarea>
                  </div>

                  <!-- Section 2: Planned Tasks -->
                  <div class="form-group">
                    <label class="font-weight-bold text-primary text-sm">
                      <i class="fas fa-arrow-alt-circle-right mr-1"></i> ២. ការងារដែលត្រូវធ្វើបន្តក្នុងសប្តាហ៍បន្ទាប់ (Planned Tasks Summary)
                    </label>
                    <textarea
                      v-model="formData.planned_tasks"
                      class="form-control form-control-sm"
                      rows="3"
                      placeholder="ប្រព័ន្ធនឹងបូកសរុបស្វ័យប្រវត្តិចេញពីកិច្ចការដែល «កំពុងធ្វើ» និង «មិនទាន់ធ្វើ» ឬលោកអ្នកអាចសរសេរពិពណ៌នាបន្ថែមនៅទីនេះ..."
                    ></textarea>
                  </div>

                  <!-- Section 3: Challenges & Solutions -->
                  <div class="form-group mb-0">
                    <label class="font-weight-bold text-warning text-sm">
                      <i class="fas fa-exclamation-triangle mr-1"></i> ៣. បញ្ហាប្រឈម និងសំណូមពរ / ដំណោះស្រាយ (Challenges & Requests)
                    </label>
                    <textarea
                      v-model="formData.challenges"
                      class="form-control form-control-sm"
                      rows="2"
                      placeholder="បញ្ហាជួបប្រទះក្នុងដំណើរការការងារ និងសំណូមពរការគាំទ្រពីថ្នាក់ដឹកនាំ..."
                    ></textarea>
                  </div>
                </div>
              </div>

              <!-- File Attachment -->
              <div class="form-group mb-0">
                <label class="font-weight-bold text-sm">
                  <i class="fas fa-paperclip mr-1 text-secondary"></i> ឯកសារភ្ជាប់ (PDF, Word, Excel, រូបភាព - ទំហំអតិបរមា 20MB)
                </label>
                <div class="custom-file">
                  <input
                    type="file"
                    class="custom-file-input"
                    id="customFile"
                    @change="handleFileUpload"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
                  />
                  <label class="custom-file-label" for="customFile">
                    {{ attachedFileName || (formData.attachment_name || 'ជ្រើសរើសឯកសារភ្ជាប់...') }}
                  </label>
                </div>
                <div v-if="formData.attachment_name && !attachedFile" class="mt-2 small text-muted">
                  <i class="fas fa-file mr-1"></i> ឯកសារបច្ចុប្បន្ន៖ <strong>{{ formData.attachment_name }}</strong>
                </div>
              </div>

            </form>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer sticky-modal-footer d-flex justify-content-between">
            <button type="button" class="btn btn-secondary px-3" @click="closeReportModal">
              បោះបង់
            </button>
            <div>
              <!-- Save as Draft -->
              <button
                type="button"
                class="btn btn-outline-warning mr-2"
                :disabled="submitting"
                @click="saveReport(false)"
              >
                <i class="fas fa-save mr-1"></i> រក្សាទុកជាសេចក្តីព្រាង
              </button>

              <!-- Submit to Leader -->
              <button
                type="button"
                class="btn btn-primary px-4 shadow-sm"
                :disabled="submitting"
                @click="saveReport(true)"
              >
                <i class="fas fa-paper-plane mr-1"></i> ដាក់ជូនថ្នាក់ដឹកនាំ
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================= -->
    <!-- MODAL 2: VIEW REPORT DETAILS & DETAILED TASKS LIST            -->
    <!-- ============================================================= -->
    <div v-if="showViewModal && currentReport" class="custom-modal-backdrop" @click.self="closeViewModal">
      <div class="modal-dialog modal-xl modal-dialog-centered font-khmer my-auto" role="document" style="width: 100%; max-width: 1050px;">
        <div class="modal-content custom-modal-content d-flex flex-column h-100 overflow-hidden font-khmer shadow-lg">
          <div class="modal-header bg-dark text-white py-3">
            <h5 class="modal-title font-weight-bold">
              <i class="fas fa-file-invoice mr-2 text-info"></i> {{ currentReport.title }}
            </h5>
            <button type="button" class="close text-white" @click="closeViewModal">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body p-4 flex-grow-1" style="overflow-y: auto;">
            <!-- Submitter info banner -->
            <div class="card bg-light border-0 shadow-sm mb-4">
              <div class="card-body p-3">
                <div class="row align-items-center">
                  <div class="col-md-7 d-flex align-items-center">
                    <img
                      :src="getUserAvatar(currentReport.user)"
                      class="img-circle elevation-1 mr-3"
                      style="width: 52px; height: 52px; object-fit: cover;"
                      alt="Avatar"
                    />
                    <div>
                      <h5 class="font-weight-bold text-dark mb-0">
                        {{ currentReport.user?.name_kh || currentReport.user?.name }}
                      </h5>
                      <div class="text-primary font-weight-bold small">
                        {{ currentReport.position?.title_kh || 'មន្ត្រី' }}
                      </div>
                      <div class="text-muted small">
                        {{ currentReport.department?.name_kh }}
                        <span v-if="currentReport.office"> - {{ currentReport.office?.name_kh }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-5 text-md-right mt-2 mt-md-0">
                    <div>
                      <span class="badge badge-info px-2 py-1 mr-1">
                        សប្តាហ៍ទី{{ toKhmerNum(currentReport.week_number) }} ({{ formatShortDate(currentReport.start_date) }} ដល់ {{ formatShortDate(currentReport.end_date) }})
                      </span>
                    </div>
                    <div class="mt-1">
                      <span :class="getStatusBadgeClass(currentReport.status)">
                        <i class="fas mr-1" :class="getStatusIcon(currentReport.status)"></i>
                        {{ getStatusKhmer(currentReport.status) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- TASK TRACKING METRICS BANNER FOR THIS REPORT -->
            <div class="row mb-4">
              <div class="col-md-3 col-6 text-center border-right">
                <div class="text-muted small">ការងារសរុប</div>
                <h4 class="font-weight-bold text-dark mb-0">{{ toKhmerNum(getReportTaskCounts(currentReport).total) }}</h4>
              </div>
              <div class="col-md-3 col-6 text-center border-right">
                <div class="text-muted small">មិនទាន់ធ្វើ</div>
                <h4 class="font-weight-bold text-secondary mb-0">{{ toKhmerNum(getReportTaskCounts(currentReport).pending) }}</h4>
              </div>
              <div class="col-md-3 col-6 text-center border-right">
                <div class="text-muted small">កំពុងធ្វើ</div>
                <h4 class="font-weight-bold text-primary mb-0">{{ toKhmerNum(getReportTaskCounts(currentReport).in_progress) }}</h4>
              </div>
              <div class="col-md-3 col-6 text-center">
                <div class="text-muted small">បានបញ្ចប់</div>
                <h4 class="font-weight-bold text-success mb-0">
                  {{ toKhmerNum(getReportTaskCounts(currentReport).completed) }}
                  <small class="text-muted">({{ toKhmerNum(getReportTaskCounts(currentReport).rate) }}%)</small>
                </h4>
              </div>
            </div>

            <!-- STRUCTURED TASKS TABLE -->
            <div class="card card-outline card-info shadow-sm mb-4" v-if="currentReport.tasks && currentReport.tasks.length > 0">
              <div class="card-header bg-light py-2">
                <h6 class="font-weight-bold text-dark m-0">
                  <i class="fas fa-list-check text-info mr-2"></i> តារាងកិច្ចការងារជាក់ស្តែង (Tasks Status & Progress)
                </h6>
              </div>
              <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover table-valign-middle mb-0">
                  <thead class="thead-light small">
                    <tr>
                      <th style="width: 45px;" class="text-center">ល.រ</th>
                      <th>ឈ្មោះកិច្ចការងារ</th>
                      <th style="width: 130px;" class="text-center">ស្ថានភាព</th>
                      <th style="width: 140px;">វឌ្ឍនភាព</th>
                      <th>លទ្ធផលសម្រេចបាន / កំណត់សម្គាល់</th>
                      <th v-if="currentReport.user_id === userStore.id" style="width: 100px;" class="text-center">កែប្រែស្ថានភាព</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(task, tIdx) in currentReport.tasks" :key="task.id || tIdx">
                      <td class="text-center font-weight-bold text-muted">{{ toKhmerNum(tIdx + 1) }}</td>
                      <td>
                        <div class="font-weight-bold text-dark">{{ task.task_name }}</div>
                        <div class="small text-muted" v-if="task.description">{{ task.description }}</div>
                      </td>
                      <td class="text-center">
                        <span :class="getTaskStatusBadge(task.status)">
                          {{ getTaskStatusKhmer(task.status) }}
                        </span>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="progress progress-xs w-100 mr-2" style="height: 6px;">
                            <div
                              class="progress-bar"
                              :class="{
                                'bg-success': task.status === 'COMPLETED',
                                'bg-primary': task.status === 'IN_PROGRESS',
                                'bg-secondary': task.status === 'PENDING'
                              }"
                              :style="{ width: (task.progress_percent || 0) + '%' }"
                            ></div>
                          </div>
                          <small class="font-weight-bold">{{ toKhmerNum(task.progress_percent || 0) }}%</small>
                        </div>
                      </td>
                      <td>
                        <span v-if="task.result_notes" class="text-dark small">{{ task.result_notes }}</span>
                        <span v-else class="text-muted small font-italic">-</span>
                      </td>
                      <!-- Quick action to toggle status if owner -->
                      <td v-if="currentReport.user_id === userStore.id" class="text-center">
                        <button
                          v-if="task.status !== 'COMPLETED'"
                          class="btn btn-xs btn-outline-success"
                          @click="quickUpdateTask(task, 'COMPLETED')"
                          title="ចុចដើម្បីបញ្ចប់កិច្ចការ"
                        >
                          <i class="fas fa-check mr-1"></i> បញ្ចប់
                        </button>
                        <span v-else class="badge badge-light border text-success">
                          <i class="fas fa-check-double"></i> ជោគជ័យ
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Content Narrative Summaries -->
            <div class="row mb-4">
              <div class="col-md-6 mb-3">
                <div class="card h-100 border-0 bg-light">
                  <div class="card-body p-3">
                    <h6 class="font-weight-bold text-success border-bottom pb-2">
                      <i class="fas fa-check-circle mr-1"></i> លទ្ធផលការងារសម្រេចបានក្នុងសប្តាហ៍៖
                    </h6>
                    <div class="text-dark text-pre-wrap small mt-2">
                      {{ currentReport.completed_tasks }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="card h-100 border-0 bg-light">
                  <div class="card-body p-3">
                    <h6 class="font-weight-bold text-primary border-bottom pb-2">
                      <i class="fas fa-arrow-alt-circle-right mr-1"></i> ការងារដែលត្រូវធ្វើបន្តក្នុងសប្តាហ៍បន្ទាប់៖
                    </h6>
                    <div class="text-dark text-pre-wrap small mt-2">
                      {{ currentReport.planned_tasks }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-4" v-if="currentReport.challenges">
              <h6 class="font-weight-bold text-warning border-bottom pb-2">
                <i class="fas fa-exclamation-triangle mr-1"></i> បញ្ហាប្រឈម និងសំណូមពរ / ដំណោះស្រាយ៖
              </h6>
              <div class="p-3 bg-light rounded text-dark text-pre-wrap small">
                {{ currentReport.challenges }}
              </div>
            </div>

            <!-- File Attachment -->
            <div class="mb-4" v-if="currentReport.attachment_path">
              <h6 class="font-weight-bold text-secondary border-bottom pb-2">
                <i class="fas fa-paperclip mr-1"></i> ឯកសារភ្ជាប់យោង៖
              </h6>
              <div class="p-2 border rounded d-flex align-items-center justify-content-between">
                <div>
                  <i class="fas fa-file-pdf text-danger fa-lg mr-2"></i>
                  <span class="font-weight-bold">{{ currentReport.attachment_name || 'ឯកសារភ្ជាប់' }}</span>
                </div>
                <a
                  :href="getDownloadUrl(currentReport.id)"
                  target="_blank"
                  class="btn btn-sm btn-outline-primary"
                >
                  <i class="fas fa-download mr-1"></i> ទាញយក
                </a>
              </div>
            </div>

            <!-- 1. Supervisor Remarks (ចំណារថ្នាក់ដឹកនាំផ្ទាល់) -->
            <div class="card border-success shadow-sm mb-3" v-if="currentReport.supervisor_remarks || currentReport.reviewed_by">
              <div class="card-header bg-success text-white py-2">
                <h6 class="mb-0 font-weight-bold">
                  <i class="fas fa-stamp mr-1"></i> ចំណារ{{ currentReport.reviewer?.position?.title_kh || 'ថ្នាក់ដឹកនាំផ្ទាល់' }}
                </h6>
              </div>
              <div class="card-body p-3">
                <p class="text-dark font-italic mb-2 text-pre-wrap">
                  « {{ currentReport.supervisor_remarks }} »
                </p>
                <div class="text-muted small border-top pt-2">
                  <i class="fas fa-user-check mr-1 text-success"></i> បានពិនិត្យដោយ៖
                  <strong>{{ currentReport.reviewer?.name_kh || currentReport.reviewer?.name }}</strong>
                  <span v-if="currentReport.reviewer?.position?.title_kh" class="text-primary font-weight-bold ml-1">
                    ({{ currentReport.reviewer.position.title_kh }})
                  </span>
                  <span v-if="currentReport.reviewed_at" class="ml-2">
                    កាលបរិច្ឆេទ៖ {{ formatKhmerDateTime(currentReport.reviewed_at) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- 2. Leadership Remarks (ចំណារបន្ថែមថ្នាក់ដឹកនាំអគ្គនាយកដ្ឋាន) -->
            <div class="card border-warning shadow-sm mb-3" v-if="currentReport.leadership_remarks || currentReport.leadership_reviewed_by">
              <div class="card-header bg-warning text-dark py-2">
                <h6 class="mb-0 font-weight-bold">
                  <i class="fas fa-pen-nib mr-1"></i> ចំណារបន្ថែម{{ (currentReport.leadership_reviewer || currentReport.leadershipReviewer)?.position?.title_kh || 'ថ្នាក់ដឹកនាំអគ្គនាយកដ្ឋាន' }}
                </h6>
              </div>
              <div class="card-body p-3">
                <p class="text-dark font-italic mb-2 text-pre-wrap">
                  « {{ currentReport.leadership_remarks }} »
                </p>
                <div class="text-muted small border-top pt-2">
                  <i class="fas fa-user-check mr-1 text-warning"></i> បានចារបន្ថែមដោយ៖
                  <strong>{{ (currentReport.leadership_reviewer || currentReport.leadershipReviewer)?.name_kh || (currentReport.leadership_reviewer || currentReport.leadershipReviewer)?.name }}</strong>
                  <span v-if="(currentReport.leadership_reviewer || currentReport.leadershipReviewer)?.position?.title_kh" class="text-primary font-weight-bold ml-1">
                    ({{ (currentReport.leadership_reviewer || currentReport.leadershipReviewer).position.title_kh }})
                  </span>
                  <span v-if="currentReport.leadership_reviewed_at" class="ml-2">
                    កាលបរិច្ឆេទ៖ {{ formatKhmerDateTime(currentReport.leadership_reviewed_at) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Notice for Deputy Office Head (Level 8) -->
            <div v-if="isDeputyOfficeHead && activeTab === 'subordinates'" class="alert alert-info py-2 px-3 small mb-3">
              <i class="fas fa-info-circle mr-1"></i> អនុប្រធានការិយាល័យមានសិទ្ធិត្រឹមតែតាមដាន និងពិនិត្យមើលរបាយការណ៍/កិច្ចការងារប៉ុណ្ណោះ។
            </div>

            <!-- Leader Review Input Box in View Modal (if canReviewReport) -->
            <div v-if="canReviewReport(currentReport)" class="card border-primary shadow-sm mt-3">
              <div class="card-header py-2" :class="isLeadershipUser ? 'bg-warning text-dark' : 'bg-primary text-white'">
                <h6 class="mb-0 font-weight-bold">
                  <i class="fas mr-1" :class="isLeadershipUser ? 'fa-pen-nib' : 'fa-stamp'"></i>
                  {{ isLeadershipUser ? 'ចារបន្ថែមពីលើ (ថ្នាក់ដឹកនាំអគ្គនាយកដ្ឋាន)' : 'ដាក់ចំណារថ្នាក់ដឹកនាំផ្ទាល់' }}
                </h6>
              </div>
              <div class="card-body p-3">
                <!-- If Leadership User (DG/DDG): show leadership input -->
                <div v-if="isLeadershipUser" class="form-group mb-2">
                  <label class="font-weight-bold text-sm text-dark">
                    <i class="fas fa-pen-nib text-warning mr-1"></i> ចំណារបន្ថែមរបស់អគ្គនាយក / អគ្គនាយករង៖
                  </label>
                  <textarea
                    v-model="leadershipRemarksInput"
                    class="form-control"
                    rows="3"
                    placeholder="បញ្ចូលចំណារបន្ថែមរបស់លោកអ្នកនៅទីនេះ (ចំណារប្រធានការិយាល័យ/នាយកដ្ឋាននឹងនៅរក្សាទុកដដែល)..."
                  ></textarea>
                </div>
                <!-- If Supervisor: show supervisor input -->
                <div v-else class="form-group mb-2">
                  <label class="font-weight-bold text-sm text-dark">
                    <i class="fas fa-stamp text-success mr-1"></i> ចំណាររបស់ប្រធានការិយាល័យ / ប្រធាននាយកដ្ឋាន៖
                  </label>
                  <textarea
                    v-model="reviewRemarksInput"
                    class="form-control"
                    rows="3"
                    placeholder="បញ្ចូលចំណារ ឬមតិយោបល់ណែនាំរបស់លោកអ្នកនៅទីនេះ..."
                  ></textarea>
                </div>

                <button
                  class="btn btn-sm px-3 shadow-sm"
                  :class="isLeadershipUser ? 'btn-warning font-weight-bold' : 'btn-success'"
                  :disabled="reviewing"
                  @click="saveSupervisorReview(currentReport.id)"
                >
                  <i class="fas fa-check mr-1"></i>
                  {{ isLeadershipUser ? 'រក្សាទុកចំណារបន្ថែម' : 'រក្សាទុកចំណារ & បញ្ជាក់ការពិនិត្យ' }}
                </button>
              </div>
            </div>

          </div>

          <div class="modal-footer sticky-modal-footer d-flex justify-content-between">
            <button
              class="btn btn-outline-secondary"
              @click="openPrintModal(currentReport)"
            >
              <i class="fas fa-print mr-1"></i> បោះពុម្ព
            </button>
            <button type="button" class="btn btn-secondary px-4" @click="closeViewModal">
              បិទ
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================= -->
    <!-- MODAL 3: SUPERVISOR & LEADERSHIP REVIEW MODAL                 -->
    <!-- ============================================================= -->
    <div v-if="showReviewModal && currentReport" class="custom-modal-backdrop" @click.self="closeReviewModal">
      <div class="modal-dialog modal-dialog-centered font-khmer my-auto" role="document" style="width: 100%; max-width: 680px;">
        <div class="modal-content custom-modal-content d-flex flex-column h-100 overflow-hidden font-khmer shadow-lg">
          <div class="modal-header py-3" :class="isLeadershipUser ? 'bg-warning text-dark' : 'bg-success text-white'">
            <h5 class="modal-title font-weight-bold">
              <i class="fas mr-2" :class="isLeadershipUser ? 'fa-pen-nib' : 'fa-stamp'"></i>
              {{ isLeadershipUser ? 'ចារបន្ថែមពីលើ (ថ្នាក់ដឹកនាំអគ្គនាយកដ្ឋាន)' : 'ពិនិត្យ និងដាក់ចំណារលើរបាយការណ៍' }}
            </h5>
            <button type="button" class="close" :class="isLeadershipUser ? 'text-dark' : 'text-white'" @click="closeReviewModal">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body p-4 flex-grow-1" style="overflow-y: auto;">
            <!-- Submitter info banner -->
            <div class="alert alert-light border shadow-sm mb-3">
              <div class="font-weight-bold text-dark">
                {{ currentReport.user?.name_kh || currentReport.user?.name }} ({{ currentReport.position?.title_kh }})
              </div>
              <div class="small text-muted">{{ currentReport.title }}</div>
              <div class="small text-info mt-1">
                សប្តាហ៍ទី{{ toKhmerNum(currentReport.week_number) }} ({{ formatShortDate(currentReport.start_date) }} ដល់ {{ formatShortDate(currentReport.end_date) }})
              </div>
              <!-- Tasks Summary inside review modal -->
              <div class="d-flex flex-wrap gap-1 align-items-center mt-2">
                <span class="badge badge-light border text-dark mr-1">
                  សរុប: <strong>{{ toKhmerNum(getReportTaskCounts(currentReport).total) }}</strong>
                </span>
                <span class="badge badge-success mr-1">
                  បញ្ចប់: <strong>{{ toKhmerNum(getReportTaskCounts(currentReport).completed) }}</strong>
                </span>
                <span class="badge badge-primary mr-1">
                  កំពុងធ្វើ: <strong>{{ toKhmerNum(getReportTaskCounts(currentReport).in_progress) }}</strong>
                </span>
                <span class="badge badge-secondary mr-1">
                  មិនទាន់ធ្វើ: <strong>{{ toKhmerNum(getReportTaskCounts(currentReport).pending) }}</strong>
                </span>
              </div>
            </div>

            <!-- Existing Supervisor Remark (Visible to DG/DDG if present) -->
            <div v-if="isLeadershipUser && currentReport.supervisor_remarks" class="card border-success bg-light mb-3">
              <div class="card-body p-3">
                <div class="font-weight-bold text-success small mb-1">
                  <i class="fas fa-stamp mr-1"></i> ចំណារ{{ currentReport.reviewer?.position?.title_kh || 'ថ្នាក់ដឹកនាំផ្ទាល់' }}៖
                </div>
                <div class="font-italic text-dark small text-pre-wrap">
                  « {{ currentReport.supervisor_remarks }} »
                </div>
                <div class="text-muted small mt-1 border-top pt-1">
                  ដោយ៖ <strong>{{ currentReport.reviewer?.name_kh || currentReport.reviewer?.name }}</strong>
                  <span v-if="currentReport.reviewed_at" class="ml-2">({{ formatKhmerDateTime(currentReport.reviewed_at) }})</span>
                </div>
              </div>
            </div>

            <!-- If Leadership User: Leadership remark input -->
            <div v-if="isLeadershipUser" class="form-group">
              <label class="font-weight-bold text-dark">
                <i class="fas fa-pen-nib text-warning mr-1"></i> ចំណារបន្ថែមរបស់អគ្គនាយក / អគ្គនាយករង៖
              </label>
              <textarea
                v-model="leadershipRemarksInput"
                class="form-control"
                rows="4"
                placeholder="ឧ. ឯកភាពតាមការិយាល័យ និងសូមជំរុញអនុវត្តឱ្យទាន់ផែនការ..."
              ></textarea>
              <small class="form-text text-muted">
                * ចំណារនេះនឹងត្រូវបានចារបន្ថែមពីលើ ដោយមិនបាត់បង់ចំណាររបស់ប្រធានការិយាល័យ/នាយកដ្ឋានឡើយ។
              </small>
            </div>

            <!-- If Supervisor: Supervisor remark input -->
            <div v-else class="form-group">
              <label class="font-weight-bold text-dark">
                <i class="fas fa-stamp text-success mr-1"></i> ចំណារ ឬ មតិយោបល់ណែនាំរបស់ថ្នាក់ដឹកនាំ៖
              </label>
              <textarea
                v-model="reviewRemarksInput"
                class="form-control"
                rows="4"
                placeholder="ឧ. បានឃើញ និងឯកភាពលើលទ្ធផលការងារ។ សូមបន្តតាមដានការងារ..."
              ></textarea>
            </div>
          </div>

          <div class="modal-footer sticky-modal-footer d-flex justify-content-between">
            <button type="button" class="btn btn-secondary px-3" @click="closeReviewModal">
              បោះបង់
            </button>
            <button
              type="button"
              class="btn px-4 shadow-sm"
              :class="isLeadershipUser ? 'btn-warning font-weight-bold' : 'btn-success'"
              :disabled="reviewing"
              @click="saveSupervisorReview(currentReport.id)"
            >
              <i class="fas fa-check-circle mr-1"></i>
              {{ isLeadershipUser ? 'រក្សាទុកចំណារបន្ថែម' : 'រក្សាទុកចំណារ & អនុម័ត' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================= -->
    <!-- MODAL 4: OFFICIAL KHMER PRINT PREVIEW MODAL                   -->
    <!-- ============================================================= -->
    <div v-if="showPrintModal && currentReport" class="custom-modal-backdrop" @click.self="closePrintModal">
      <div class="modal-dialog modal-xl modal-dialog-centered font-khmer my-auto" role="document" style="width: 100%; max-width: 950px;">
        <div class="modal-content custom-modal-content d-flex flex-column h-100 overflow-hidden font-khmer shadow-lg">
          <div class="modal-header bg-secondary text-white py-2 no-print">
            <h5 class="modal-title font-weight-bold">
              <i class="fas fa-print mr-2"></i> ទម្រង់បោះពុម្ពរបាយការណ៍ការងារប្រចាំសប្តាហ៍
            </h5>
            <button type="button" class="close text-white" @click="closePrintModal">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body p-5 flex-grow-1 printable-area" id="printable-report" style="overflow-y: auto; background-color: #fff; color: #000;">
            <!-- Royal Header -->
            <div class="text-center mb-4">
              <h4 class="font-khmer-moul text-uppercase mb-1" style="letter-spacing: 1px;">ព្រះរាជាណាចក្រកម្ពុជា</h4>
              <h5 class="font-khmer-moul mb-2">ជាតិ សាសនា ព្រះមហាក្សត្រ</h5>
              <div style="font-size: 18px; letter-spacing: 5px;">***</div>
            </div>

            <!-- Department / Ministry Header -->
            <div class="row mb-4">
              <div class="col-7">
                <div class="font-weight-bold" style="font-size: 15px;">អគ្គនាយកដ្ឋានបរធនបាលកិច្ច</div>
                <div class="font-weight-bold" style="font-size: 14px;">{{ currentReport.department?.name_kh || 'នាយកដ្ឋាន' }}</div>
                <div style="font-size: 13px;" v-if="currentReport.office">{{ currentReport.office?.name_kh }}</div>
              </div>
              <div class="col-5 text-right text-muted" style="font-size: 13px;">
                រាជធានីភ្នំពេញ, ថ្ងៃទី{{ toKhmerNum(currentDateKhmer.day) }} ខែ{{ currentDateKhmer.month }} ឆ្នាំ{{ toKhmerNum(currentDateKhmer.year) }}
              </div>
            </div>

            <!-- Report Title -->
            <div class="text-center my-4">
              <h4 class="font-khmer-moul text-primary mb-2" style="font-size: 18px;">
                {{ currentReport.title }}
              </h4>
              <div class="font-weight-bold text-secondary" style="font-size: 14px;">
                (អនុវត្តចាប់ពី ថ្ងៃទី{{ formatKhmerDateOnly(currentReport.start_date) }} ដល់ ថ្ងៃទី{{ formatKhmerDateOnly(currentReport.end_date) }})
              </div>
            </div>

            <!-- Submitter Details -->
            <div class="border p-3 rounded mb-4" style="background-color: #fcfcfc;">
              <div class="row">
                <div class="col-6">
                  <strong>គោត្តនាម និងនាម៖</strong> {{ currentReport.user?.name_kh || currentReport.user?.name }}
                </div>
                <div class="col-6">
                  <strong>តួនាទី៖</strong> {{ currentReport.position?.title_kh || 'មន្ត្រី' }}
                </div>
              </div>
            </div>

            <!-- PRINTABLE TASKS TABLE -->
            <div class="mb-4" v-if="currentReport.tasks && currentReport.tasks.length > 0">
              <div class="font-khmer-moul text-dark mb-2" style="font-size: 15px;">
                I. តារាងកិច្ចការងារជាក់ស្តែងដែលបានអនុវត្ត (Tasks Breakdown)៖
              </div>
              <table class="table table-bordered table-sm" style="font-size: 13px;">
                <thead class="bg-light text-center">
                  <tr>
                    <th style="width: 40px;">ល.រ</th>
                    <th>ឈ្មោះកិច្ចការងារ</th>
                    <th style="width: 110px;">ស្ថានភាព</th>
                    <th style="width: 90px;">វឌ្ឍនភាព</th>
                    <th>លទ្ធផលសម្រេចបាន / កំណត់សម្គាល់</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(task, tIdx) in currentReport.tasks" :key="tIdx">
                    <td class="text-center">{{ toKhmerNum(tIdx + 1) }}</td>
                    <td class="font-weight-bold">{{ task.task_name }}</td>
                    <td class="text-center">{{ getTaskStatusKhmer(task.status) }}</td>
                    <td class="text-center">{{ toKhmerNum(task.progress_percent || 0) }}%</td>
                    <td>{{ task.result_notes || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Section II: Narrative Completed Tasks -->
            <div class="mb-4">
              <div class="font-khmer-moul text-dark mb-2" style="font-size: 15px;">
                II. លទ្ធផលការងារសម្រេចបានក្នុងសប្តាហ៍៖
              </div>
              <div class="pl-3 text-pre-wrap" style="line-height: 1.8; font-size: 14px;">
                {{ currentReport.completed_tasks }}
              </div>
            </div>

            <!-- Section III: Planned Tasks -->
            <div class="mb-4">
              <div class="font-khmer-moul text-dark mb-2" style="font-size: 15px;">
                III. ការងារដែលត្រូវបន្តអនុវត្តក្នុងសប្តាហ៍បន្ទាប់៖
              </div>
              <div class="pl-3 text-pre-wrap" style="line-height: 1.8; font-size: 14px;">
                {{ currentReport.planned_tasks }}
              </div>
            </div>

            <!-- Section IV: Challenges -->
            <div class="mb-4" v-if="currentReport.challenges">
              <div class="font-khmer-moul text-dark mb-2" style="font-size: 15px;">
                IV. បញ្ហាប្រឈម និងសំណូមពរ៖
              </div>
              <div class="pl-3 text-pre-wrap" style="line-height: 1.8; font-size: 14px;">
                {{ currentReport.challenges }}
              </div>
            </div>

            <!-- Section V: Remarks (ចំណារ) -->
            <div class="mb-4" v-if="currentReport.supervisor_remarks || currentReport.leadership_remarks">
              <div class="font-khmer-moul text-dark mb-2" style="font-size: 15px;">
                V. ចំណារ និងមតិយោបល់របស់ថ្នាក់ដឹកនាំ៖
              </div>

              <!-- 1. Direct Supervisor Remark -->
              <div class="border rounded p-3 mb-3" style="background-color: #fcfcfc;" v-if="currentReport.supervisor_remarks">
                <div class="d-flex justify-content-between border-bottom pb-1 mb-2 font-weight-bold" style="font-size: 13px;">
                  <span>
                    <i class="fas fa-stamp mr-1 text-success"></i>
                    ចំណារ{{ currentReport.reviewer?.position?.title_kh || 'ថ្នាក់ដឹកនាំផ្ទាល់' }}៖
                  </span>
                  <span class="text-muted small" v-if="currentReport.reviewed_at">
                    កាលបរិច្ឆេទ៖ {{ formatKhmerDateTime(currentReport.reviewed_at) }}
                  </span>
                </div>
                <div class="font-italic text-pre-wrap pl-2" style="font-size: 14px; line-height: 1.8;">
                  « {{ currentReport.supervisor_remarks }} »
                </div>
                <div class="text-right font-weight-bold mt-2" style="font-size: 13px;">
                  {{ currentReport.reviewer?.name_kh || currentReport.reviewer?.name }}
                </div>
              </div>

              <!-- 2. Leadership Remark (ចារបន្ថែមពីលើ) -->
              <div class="border border-warning rounded p-3" style="background-color: #fffdf5;" v-if="currentReport.leadership_remarks">
                <div class="d-flex justify-content-between border-bottom pb-1 mb-2 font-weight-bold text-dark" style="font-size: 13px;">
                  <span>
                    <i class="fas fa-pen-nib mr-1 text-warning"></i>
                    ចំណារបន្ថែម{{ (currentReport.leadership_reviewer || currentReport.leadershipReviewer)?.position?.title_kh || 'ថ្នាក់ដឹកនាំអគ្គនាយកដ្ឋាន' }}៖
                  </span>
                  <span class="text-muted small" v-if="currentReport.leadership_reviewed_at">
                    កាលបរិច្ឆេទ៖ {{ formatKhmerDateTime(currentReport.leadership_reviewed_at) }}
                  </span>
                </div>
                <div class="font-italic text-pre-wrap pl-2" style="font-size: 14px; line-height: 1.8;">
                  « {{ currentReport.leadership_remarks }} »
                </div>
                <div class="text-right font-weight-bold mt-2" style="font-size: 13px;">
                  {{ (currentReport.leadership_reviewer || currentReport.leadershipReviewer)?.name_kh || (currentReport.leadership_reviewer || currentReport.leadershipReviewer)?.name }}
                </div>
              </div>
            </div>

            <!-- Signatures block: 3 columns if leadership remarks exist, else 2 columns -->
            <div class="row mt-5 pt-4">
              <div class="col-4 text-center" v-if="currentReport.leadership_remarks">
                <div class="font-weight-bold mb-1">បានឃើញ និងឯកភាព</div>
                <div class="small text-muted mb-5">{{ (currentReport.leadership_reviewer || currentReport.leadershipReviewer)?.position?.title_kh || 'ថ្នាក់ដឹកនាំអគ្គនាយកដ្ឋាន' }}</div>
                <div class="mt-4 font-weight-bold">
                  {{ (currentReport.leadership_reviewer || currentReport.leadershipReviewer)?.name_kh || (currentReport.leadership_reviewer || currentReport.leadershipReviewer)?.name || '........................................' }}
                </div>
              </div>
              <div :class="currentReport.leadership_remarks ? 'col-4 text-center' : 'col-6 text-center'">
                <div class="font-weight-bold mb-1">បានឃើញ និងពិនិត្យ</div>
                <div class="small text-muted mb-5">{{ currentReport.reviewer?.position?.title_kh || 'ថ្នាក់ដឹកនាំទទួលបន្ទុក' }}</div>
                <div class="mt-4 font-weight-bold">
                  {{ currentReport.reviewer?.name_kh || currentReport.reviewer?.name || '........................................' }}
                </div>
              </div>
              <div :class="currentReport.leadership_remarks ? 'col-4 text-center' : 'col-6 text-center'">
                <div class="font-weight-bold mb-1">
                  ថ្ងៃទី{{ toKhmerNum(currentDateKhmer.day) }} ខែ{{ currentDateKhmer.month }} ឆ្នាំ{{ toKhmerNum(currentDateKhmer.year) }}
                </div>
                <div class="small text-muted mb-5">ហត្ថលេខាមន្ត្រីរាយការណ៍</div>
                <div class="mt-4 font-weight-bold">
                  {{ currentReport.user?.name_kh || currentReport.user?.name }}
                </div>
              </div>
            </div>

          </div>

          <div class="modal-footer sticky-modal-footer d-flex justify-content-between no-print">
            <button type="button" class="btn btn-secondary px-3" @click="closePrintModal">
              បិទ
            </button>
            <button type="button" class="btn btn-primary px-4 shadow-sm" @click="printReport">
              <i class="fas fa-print mr-1"></i> ចុចបោះពុម្ព (Print)
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import Swal from 'sweetalert2';
import { useUserStore } from '@/stores/user';
import {
  apiGetWeeklyReports,
  apiGetWeeklyReportStats,
  apiGetWeeklyReportFilterOptions,
  apiGetWeeklyReport,
  apiCreateWeeklyReport,
  apiUpdateWeeklyReport,
  apiSubmitWeeklyReport,
  apiReviewWeeklyReport,
  apiDeleteWeeklyReport,
  apiUpdateWeeklyReportTaskStatus,
  getAttachmentDownloadUrl
} from '@/functions/api/weeklyReport';

const userStore = useUserStore();

// Navigation & Tab: 'my' | 'subordinates'
const activeTab = ref('my');

// Date & Filters
const today = new Date();
const filterYear = ref(today.getFullYear());
const filterMonth = ref(today.getMonth() + 1);
const filterWeek = ref('');
const filterStatus = ref('');
const filterDepartmentId = ref('');
const filterOfficeId = ref('');
const filterUserId = ref('');
const searchQuery = ref('');

const availableYears = [2024, 2025, 2026, 2027, 2028];

// Data state
const reports = ref([]);
const statsData = ref({
  my_stats: {
    total: 0, draft: 0, submitted: 0, reviewed: 0,
    tasks_total: 0, tasks_pending: 0, tasks_in_progress: 0, tasks_completed: 0, tasks_completion_rate: 0
  },
  subordinates_stats: {
    total: 0, pending_review: 0, reviewed: 0,
    tasks_total: 0, tasks_pending: 0, tasks_in_progress: 0, tasks_completed: 0, tasks_completion_rate: 0
  },
  can_review: false,
});

// Current Task Stats based on activeTab
const currentTaskStats = computed(() => {
  if (activeTab.value === 'subordinates') {
    return statsData.value.subordinates_stats || {};
  }
  return statsData.value.my_stats || {};
});

const filterOptions = ref({
  departments: [],
  offices: [],
  officers: [],
});

const loading = ref(false);
const submitting = ref(false);
const reviewing = ref(false);

// Modals
const showReportModal = ref(false);
const isEditing = ref(false);
const editingReportId = ref(null);
const showNarrativeFields = ref(false);

const showViewModal = ref(false);
const showReviewModal = ref(false);
const showPrintModal = ref(false);
const currentReport = ref(null);
const reviewRemarksInput = ref('');
const leadershipRemarksInput = ref('');

// Form Data for Create/Edit
const formData = reactive({
  year: today.getFullYear(),
  month: today.getMonth() + 1,
  week_number: 1,
  start_date: '',
  end_date: '',
  title: '',
  completed_tasks: '',
  planned_tasks: '',
  challenges: '',
  attachment_name: null,
  tasks: [],
});

const attachedFile = ref(null);
const attachedFileName = ref('');

// Computed task summary inside the Create/Edit form
const formTaskSummary = computed(() => {
  const list = formData.tasks || [];
  const total = list.length;
  const completed = list.filter(t => t.status === 'COMPLETED').length;
  const in_progress = list.filter(t => t.status === 'IN_PROGRESS').length;
  const pending = list.filter(t => t.status === 'PENDING').length;
  return { total, completed, in_progress, pending };
});

// Filtered offices based on selected department
const filteredOffices = computed(() => {
  if (!filterDepartmentId.value) {
    return filterOptions.value.offices || [];
  }
  return (filterOptions.value.offices || []).filter(o => o.department_id == filterDepartmentId.value);
});

// Filtered officers based on selected department and office
const filteredOfficers = computed(() => {
  let list = filterOptions.value.officers || [];
  if (filterDepartmentId.value) {
    list = list.filter(u => u.department_id == filterDepartmentId.value);
  }
  if (filterOfficeId.value) {
    list = list.filter(u => u.office_id == filterOfficeId.value);
  }
  return list;
});

// Label describing current filter scope for KPI tracking
const currentFilterScopeLabel = computed(() => {
  if (filterUserId.value) {
    const u = (filterOptions.value.officers || []).find(o => o.id == filterUserId.value);
    return u ? `មន្ត្រី៖ ${u.name_kh || u.name}` : 'មន្ត្រីជាក់លាក់';
  }
  if (filterOfficeId.value) {
    const off = (filterOptions.value.offices || []).find(o => o.id == filterOfficeId.value);
    return off ? `ការិយាល័យ៖ ${off.name_kh}` : 'ការិយាល័យជាក់លាក់';
  }
  if (filterDepartmentId.value) {
    const dep = (filterOptions.value.departments || []).find(d => d.id == filterDepartmentId.value);
    return dep ? `នាយកដ្ឋាន៖ ${dep.name_kh}` : 'នាយកដ្ឋានជាក់លាក់';
  }
  return 'គ្រប់មន្ត្រីទាំងអស់ (សរុប)';
});

// Switch Tab
function switchTab(tab) {
  activeTab.value = tab;
  fetchReports();
  fetchStats();
}

// Khmers Month Names
const khmerMonths = [
  'មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា',
  'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'
];

function getKhmerMonthName(monthNum) {
  const m = parseInt(monthNum);
  if (m >= 1 && m <= 12) return khmerMonths[m - 1];
  return monthNum;
}

// Convert numbers to Khmer digits
function toKhmerNum(num) {
  if (num === null || num === undefined || num === '') return '០';
  const khmerDigits = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];
  return String(num).replace(/[0-9]/g, d => khmerDigits[d]);
}

// Format Short Date: YYYY-MM-DD -> DD/MM
function formatShortDate(dateStr) {
  if (!dateStr) return '';
  const parts = String(dateStr).split('-');
  if (parts.length >= 3) {
    return `${toKhmerNum(parts[2])}/${toKhmerNum(parts[1])}`;
  }
  return dateStr;
}

function formatKhmerDateOnly(dateStr) {
  if (!dateStr) return '';
  const parts = String(dateStr).split('-');
  if (parts.length >= 3) {
    return `${toKhmerNum(parts[2])} ${getKhmerMonthName(parts[1])} ${toKhmerNum(parts[0])}`;
  }
  return dateStr;
}

function formatKhmerDateTime(dtStr) {
  if (!dtStr) return '';
  try {
    const d = new Date(dtStr);
    const day = toKhmerNum(String(d.getDate()).padStart(2, '0'));
    const month = getKhmerMonthName(d.getMonth() + 1);
    const year = toKhmerNum(d.getFullYear());
    const hours = toKhmerNum(String(d.getHours()).padStart(2, '0'));
    const mins = toKhmerNum(String(d.getMinutes()).padStart(2, '0'));
    return `${day} ${month} ${year}, ${hours}:${mins}`;
  } catch (e) {
    return dtStr;
  }
}

// Current Date in Khmer format for Print Header
const currentDateKhmer = computed(() => {
  const d = new Date();
  return {
    day: String(d.getDate()).padStart(2, '0'),
    month: getKhmerMonthName(d.getMonth() + 1),
    year: d.getFullYear(),
  };
});

// Helper for report task counts (from report.tasks array or accessors)
function getReportTaskCounts(report) {
  if (!report) return { total: 0, pending: 0, in_progress: 0, completed: 0, rate: 0 };
  if (Array.isArray(report.tasks) && report.tasks.length > 0) {
    const total = report.tasks.length;
    const completed = report.tasks.filter(t => t.status === 'COMPLETED').length;
    const in_progress = report.tasks.filter(t => t.status === 'IN_PROGRESS').length;
    const pending = report.tasks.filter(t => t.status === 'PENDING').length;
    const rate = total > 0 ? Math.round((completed / total) * 100) : 0;
    return { total, pending, in_progress, completed, rate };
  }
  const total = report.total_tasks_count || 0;
  const completed = report.completed_tasks_count || 0;
  const in_progress = report.in_progress_tasks_count || 0;
  const pending = report.pending_tasks_count || 0;
  const rate = report.completion_rate || 0;
  return { total, pending, in_progress, completed, rate };
}

// Status Badges & Icons
function getStatusBadgeClass(status) {
  switch (status) {
    case 'DRAFT': return 'badge badge-secondary px-2 py-1';
    case 'SUBMITTED': return 'badge badge-primary px-2 py-1';
    case 'REVIEWED': return 'badge badge-success px-2 py-1';
    default: return 'badge badge-light border px-2 py-1';
  }
}

function getStatusKhmer(status) {
  switch (status) {
    case 'DRAFT': return 'សេចក្តីព្រាង';
    case 'SUBMITTED': return 'បានដាក់ជូន';
    case 'REVIEWED': return 'បានពិនិត្យ';
    default: return status;
  }
}

function getStatusIcon(status) {
  switch (status) {
    case 'DRAFT': return 'fa-edit';
    case 'SUBMITTED': return 'fa-paper-plane';
    case 'REVIEWED': return 'fa-check-circle';
    default: return 'fa-file';
  }
}

function getTaskStatusBadge(status) {
  switch (status) {
    case 'PENDING': return 'badge badge-secondary px-2 py-1';
    case 'IN_PROGRESS': return 'badge badge-primary px-2 py-1';
    case 'COMPLETED': return 'badge badge-success px-2 py-1';
    default: return 'badge badge-light border px-2 py-1';
  }
}

function getTaskStatusKhmer(status) {
  switch (status) {
    case 'PENDING': return '🟡 មិនទាន់ធ្វើ';
    case 'IN_PROGRESS': return '🔵 កំពុងធ្វើ';
    case 'COMPLETED': return '🟢 បានបញ្ចប់';
    default: return status;
  }
}

// User Avatar helper
function getUserAvatar(u) {
  if (u?.profile_image) {
    if (u.profile_image.startsWith('http')) return u.profile_image;
    return `${import.meta.env.VITE_APP_API_URL || ''}/storage/${u.profile_image}`;
  }
  return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(u?.name_kh || u?.name || 'User') + '&background=0D8ABC&color=fff&size=128';
}

function getDownloadUrl(id) {
  return getAttachmentDownloadUrl(id);
}

// User rank computations
const currentUserPosLevel = computed(() => {
  if (userStore.isAdmin) return 0;
  return userStore.position?.level ? parseInt(userStore.position.level) : 99;
});

const isLeadershipUser = computed(() => {
  return userStore.isAdmin || currentUserPosLevel.value <= 2;
});

const isDeputyOfficeHead = computed(() => {
  return currentUserPosLevel.value === 8;
});

// Check if current user can review the viewed report
function canReviewReport(report) {
  if (!report) return false;
  if (report.user_id === userStore.id) return false;
  if (report.status === 'DRAFT') return false;
  if (userStore.isAdmin) return true;

  const myLvl = currentUserPosLevel.value;
  const authorLvl = report.position?.level ? parseInt(report.position.level) : (report.user?.position?.level ? parseInt(report.user.position.level) : 99);

  // ១. អគ្គនាយក & អគ្គនាយករង (Level 1-2): អាចចារ និងចារបន្ថែមពីលើបានលើគ្រប់របាយការណ៍ទាំងអស់
  if (myLvl <= 2) return true;

  // ២. អនុប្រធានការិយាល័យ (Level 8) និងមន្ត្រី (Level >= 9): បានត្រឹមតែមើល គ្មានសិទ្ធិចារឡើយ
  if (myLvl >= 8) return false;

  // ៣. អនុប្រធាននាយកដ្ឋាន (Level 5): គ្មានសិទ្ធិចារលើប្រធានការិយាល័យ ឬមន្ត្រីឡើយ
  if (myLvl === 5) return false;

  // ៤. ប្រធានការិយាល័យ / ស្តីទី (Level 6-7): ចារលើមន្ត្រី (Level >= 9) និងអនុប្រធានការិយាល័យ (Level 8) ក្នុងបន្ទប់ការិយាល័យរបស់ខ្លួន
  if (myLvl >= 6 && myLvl <= 7) {
    return authorLvl >= 8 &&
      report.department_id == userStore.department_id &&
      report.office_id == userStore.office_id;
  }

  // ៥. ប្រធាននាយកដ្ឋាន / ស្តីទី (Level 3-4): ចារលើប្រធានការិយាល័យ (Level 6-7), អនុប្រធាននាយកដ្ឋាន (Level 5) និងមន្ត្រីក្នុងនាយកដ្ឋាន
  if (myLvl >= 3 && myLvl <= 4) {
    return authorLvl > myLvl && report.department_id == userStore.department_id;
  }

  return false;
}

// Backward-compatibility alias
function canReviewThisReport(report) {
  return canReviewReport(report);
}

// FETCH DATA
async function fetchReports() {
  loading.value = true;
  try {
    const params = {
      scope: activeTab.value,
      year: filterYear.value,
      month: filterMonth.value,
    };
    if (filterWeek.value) params.week_number = filterWeek.value;
    if (filterStatus.value) params.status = filterStatus.value;
    if (activeTab.value === 'subordinates') {
      if (filterDepartmentId.value) params.department_id = filterDepartmentId.value;
      if (filterOfficeId.value) params.office_id = filterOfficeId.value;
      if (filterUserId.value) params.user_id = filterUserId.value;
      if (searchQuery.value) params.search = searchQuery.value;
    }

    const res = await apiGetWeeklyReports(params);
    if (res.data?.status === 'success') {
      reports.value = res.data.data || [];
    }
  } catch (err) {
    console.error('Error fetching reports:', err);
  } finally {
    loading.value = false;
  }
}

async function fetchStats() {
  try {
    const params = {
      year: filterYear.value,
      month: filterMonth.value,
    };
    if (filterWeek.value) params.week_number = filterWeek.value;
    if (activeTab.value === 'subordinates') {
      if (filterDepartmentId.value) params.department_id = filterDepartmentId.value;
      if (filterOfficeId.value) params.office_id = filterOfficeId.value;
      if (filterUserId.value) params.user_id = filterUserId.value;
    }

    const res = await apiGetWeeklyReportStats(params);
    if (res.data?.status === 'success') {
      statsData.value = res.data.data;
    }
  } catch (err) {
    console.error('Error fetching stats:', err);
  }
}

async function fetchFilterOptions() {
  try {
    const res = await apiGetWeeklyReportFilterOptions();
    if (res.data?.status === 'success') {
      filterOptions.value = res.data.data;
    }
  } catch (err) {
    console.error('Error fetching filter options:', err);
  }
}

function onFilterChange() {
  fetchReports();
  fetchStats();
}

function onDepartmentChange() {
  filterOfficeId.value = '';
  filterUserId.value = '';
  onFilterChange();
}

function onOfficeChange() {
  filterUserId.value = '';
  onFilterChange();
}

function resetSubordinateFilter() {
  filterDepartmentId.value = '';
  filterOfficeId.value = '';
  filterUserId.value = '';
  searchQuery.value = '';
  onFilterChange();
}

// Compute week dates based on selected year, month, week
function calculateWeekDates(year, month, weekNum) {
  const y = parseInt(year);
  const m = parseInt(month) - 1; // 0-indexed
  const w = parseInt(weekNum);

  const startDay = (w - 1) * 7 + 1;
  const lastDayOfMonth = new Date(y, m + 1, 0).getDate();
  const endDay = Math.min(w * 7, lastDayOfMonth);

  const pad = (n) => String(n).padStart(2, '0');
  const startDateStr = `${y}-${pad(m + 1)}-${pad(startDay)}`;
  const endDateStr = `${y}-${pad(m + 1)}-${pad(endDay)}`;

  return { startDateStr, endDateStr };
}

function onFormWeekChange() {
  if (!isEditing.value) {
    const { startDateStr, endDateStr } = calculateWeekDates(formData.year, formData.month, formData.week_number);
    formData.start_date = startDateStr;
    formData.end_date = endDateStr;
    formData.title = `របាយការណ៍ការងារប្រចាំសប្តាហ៍ទី${toKhmerNum(formData.week_number)} ខែ${getKhmerMonthName(formData.month)} ឆ្នាំ${toKhmerNum(formData.year)}`;
  }
}

// TASK ROW CONTROLS
function addTaskRow() {
  formData.tasks.push({
    task_name: '',
    description: '',
    status: 'IN_PROGRESS',
    priority: 'MEDIUM',
    progress_percent: 50,
    result_notes: '',
  });
}

function removeTaskRow(index) {
  formData.tasks.splice(index, 1);
}

function onTaskStatusChange(task) {
  if (task.status === 'COMPLETED') {
    task.progress_percent = 100;
  } else if (task.status === 'PENDING') {
    task.progress_percent = 0;
  } else if (task.status === 'IN_PROGRESS' && (task.progress_percent === 0 || task.progress_percent === 100)) {
    task.progress_percent = 50;
  }
}

function onTaskProgressInput(task) {
  if (task.progress_percent >= 100) {
    task.status = 'COMPLETED';
    task.progress_percent = 100;
  } else if (task.progress_percent <= 0) {
    task.status = 'PENDING';
    task.progress_percent = 0;
  } else {
    task.status = 'IN_PROGRESS';
  }
}

// MODAL CONTROLS: CREATE / EDIT
function openCreateModal() {
  isEditing.value = false;
  editingReportId.value = null;
  formData.year = filterYear.value;
  formData.month = filterMonth.value;

  const existingWeeks = reports.value.map(r => r.week_number);
  let nextWeek = 1;
  for (let i = 1; i <= 5; i++) {
    if (!existingWeeks.includes(i)) {
      nextWeek = i;
      break;
    }
  }
  formData.week_number = nextWeek;
  onFormWeekChange();

  formData.completed_tasks = '';
  formData.planned_tasks = '';
  formData.challenges = '';
  formData.attachment_name = null;
  attachedFile.value = null;
  attachedFileName.value = '';

  // Start with 2 sample task rows for convenience
  formData.tasks = [
    {
      task_name: '',
      description: '',
      status: 'IN_PROGRESS',
      priority: 'MEDIUM',
      progress_percent: 50,
      result_notes: '',
    }
  ];

  showReportModal.value = true;
}

function openEditModal(report) {
  isEditing.value = true;
  editingReportId.value = report.id;
  formData.year = report.year;
  formData.month = report.month;
  formData.week_number = report.week_number;
  formData.start_date = report.start_date;
  formData.end_date = report.end_date;
  formData.title = report.title;
  formData.completed_tasks = report.completed_tasks;
  formData.planned_tasks = report.planned_tasks;
  formData.challenges = report.challenges || '';
  formData.attachment_name = report.attachment_name;
  attachedFile.value = null;
  attachedFileName.value = '';

  formData.tasks = Array.isArray(report.tasks) && report.tasks.length > 0
    ? report.tasks.map(t => ({
        id: t.id,
        task_name: t.task_name,
        description: t.description || '',
        status: t.status,
        priority: t.priority || 'MEDIUM',
        progress_percent: t.progress_percent || 0,
        result_notes: t.result_notes || '',
      }))
    : [];

  showReportModal.value = true;
}

function closeReportModal() {
  showReportModal.value = false;
  isEditing.value = false;
  editingReportId.value = null;
}

function handleFileUpload(event) {
  const file = event.target.files[0];
  if (file) {
    attachedFile.value = file;
    attachedFileName.value = file.name;
  }
}

async function saveReport(submitNow = false) {
  if (!formData.title) {
    Swal.fire({
      icon: 'warning',
      title: 'សូមបញ្ចូលចំណងជើងរបាយការណ៍',
      confirmButtonText: 'យល់ព្រម',
    });
    return;
  }

  // Validate tasks
  if (formData.tasks.length === 0 && !formData.completed_tasks) {
    Swal.fire({
      icon: 'warning',
      title: 'សូមបញ្ជាក់កិច្ចការងារ',
      text: 'សូមចុច "+ បន្ថែមកិច្ចការ" ដើម្បីបញ្ចូលកិច្ចការងារដែលត្រូវតាមដាន។',
      confirmButtonText: 'យល់ព្រម',
    });
    return;
  }

  submitting.value = true;
  try {
    const data = new FormData();
    data.append('year', formData.year);
    data.append('month', formData.month);
    data.append('week_number', formData.week_number);
    data.append('start_date', formData.start_date);
    data.append('end_date', formData.end_date);
    data.append('title', formData.title);
    data.append('completed_tasks', formData.completed_tasks || '');
    data.append('planned_tasks', formData.planned_tasks || '');
    if (formData.challenges) data.append('challenges', formData.challenges);
    if (attachedFile.value) data.append('attachment', attachedFile.value);
    if (submitNow) data.append('submit_now', '1');

    // Attach structured tasks as JSON string
    data.append('tasks', JSON.stringify(formData.tasks));

    if (isEditing.value) {
      await apiUpdateWeeklyReport(editingReportId.value, data);
      Swal.fire({
        icon: 'success',
        title: 'ជោគជ័យ!',
        text: submitNow ? 'របាយការណ៍ត្រូវបានដាក់ជូនរួចរាល់' : 'បានកែសម្រួលរបាយការណ៍ដោយជោគជ័យ',
        timer: 1800,
        showConfirmButton: false,
      });
    } else {
      await apiCreateWeeklyReport(data);
      Swal.fire({
        icon: 'success',
        title: 'ជោគជ័យ!',
        text: submitNow ? 'របាយការណ៍ត្រូវបានដាក់ជូនថ្នាក់ដឹកនាំរួចរាល់' : 'បានរក្សាទុកសេចក្តីព្រាងរបាយការណ៍',
        timer: 1800,
        showConfirmButton: false,
      });
    }

    closeReportModal();
    fetchReports();
    fetchStats();
  } catch (err) {
    const msg = err.response?.data?.message || 'មានបញ្ហាក្នុងការរក្សាទុករបាយការណ៍';
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ!',
      text: msg,
      confirmButtonText: 'យល់ព្រម',
    });
  } finally {
    submitting.value = false;
  }
}

// QUICK TASK STATUS UPDATE (e.g. from view modal)
async function quickUpdateTask(task, newStatus) {
  try {
    await apiUpdateWeeklyReportTaskStatus(task.id, {
      status: newStatus,
      progress_percent: newStatus === 'COMPLETED' ? 100 : 50,
    });
    task.status = newStatus;
    task.progress_percent = newStatus === 'COMPLETED' ? 100 : 50;
    Swal.fire({
      icon: 'success',
      title: 'បានបញ្ចប់កិច្ចការ!',
      timer: 1200,
      showConfirmButton: false,
    });
    fetchReports();
    fetchStats();
  } catch (e) {
    console.error(e);
  }
}

// SUBMIT DRAFT DIRECTLY
async function confirmSubmitReport(report) {
  const result = await Swal.fire({
    title: 'តើលោកអ្នកពិតជាចង់ដាក់ជូនរបាយការណ៍នេះមែនទេ?',
    text: `សប្តាហ៍ទី${toKhmerNum(report.week_number)} នឹងត្រូវបានបញ្ជូនទៅកាន់ថ្នាក់ដឹកនាំដើម្បីពិនិត្យ។`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'បាទ/ចាស ដាក់ជូន',
    cancelButtonText: 'ទេ មិនទាន់ទេ'
  });

  if (result.isConfirmed) {
    try {
      await apiSubmitWeeklyReport(report.id);
      Swal.fire({
        icon: 'success',
        title: 'បានដាក់ជូន!',
        text: 'របាយការណ៍ត្រូវបានបញ្ជូនទៅកាន់ថ្នាក់ដឹកនាំដោយជោគជ័យ។',
        timer: 1800,
        showConfirmButton: false
      });
      fetchReports();
      fetchStats();
    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'បរាជ័យ',
        text: err.response?.data?.message || 'មិនអាចដាក់ជូនរបាយការណ៍បានទេ។',
      });
    }
  }
}

// DELETE REPORT
async function confirmDeleteReport(report) {
  const result = await Swal.fire({
    title: 'តើលោកអ្នកពិតជាចង់លុបរបាយការណ៍នេះមែនទេ?',
    text: 'ទិន្នន័យរបាយការណ៍នឹងត្រូវលុបចេញពីប្រព័ន្ធ!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'បាទ/ចាស លុប',
    cancelButtonText: 'បោះបង់'
  });

  if (result.isConfirmed) {
    try {
      await apiDeleteWeeklyReport(report.id);
      Swal.fire({
        icon: 'success',
        title: 'បានលុប!',
        text: 'របាយការណ៍ត្រូវបានលុបដោយជោគជ័យ។',
        timer: 1500,
        showConfirmButton: false
      });
      fetchReports();
      fetchStats();
    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'បរាជ័យ',
        text: err.response?.data?.message || 'មិនអាចលុបរបាយការណ៍បានឡើយ។',
      });
    }
  }
}

// MODAL: VIEW REPORT
function openViewModal(report) {
  currentReport.value = report;
  reviewRemarksInput.value = report.supervisor_remarks || '';
  leadershipRemarksInput.value = report.leadership_remarks || '';
  showViewModal.value = true;
}

function closeViewModal() {
  showViewModal.value = false;
  currentReport.value = null;
}

// MODAL: REVIEW & REMARKS
function openReviewModal(report) {
  currentReport.value = report;
  reviewRemarksInput.value = report.supervisor_remarks || '';
  leadershipRemarksInput.value = report.leadership_remarks || '';
  showReviewModal.value = true;
}

function closeReviewModal() {
  showReviewModal.value = false;
  currentReport.value = null;
}

async function saveSupervisorReview(reportId) {
  reviewing.value = true;
  try {
    const payload = {};
    if (isLeadershipUser.value) {
      payload.leadership_remarks = leadershipRemarksInput.value;
      if (!currentReport.value?.supervisor_remarks && reviewRemarksInput.value) {
        payload.supervisor_remarks = reviewRemarksInput.value;
      }
    } else {
      payload.supervisor_remarks = reviewRemarksInput.value;
    }

    const res = await apiReviewWeeklyReport(reportId, payload);

    Swal.fire({
      icon: 'success',
      title: isLeadershipUser.value ? 'បានចារបន្ថែមពីលើរួចរាល់!' : 'បានពិនិត្យ និងដាក់ចំណាររួចរាល់!',
      text: isLeadershipUser.value
        ? 'ចំណារបន្ថែមរបស់ថ្នាក់ដឹកនាំអគ្គនាយកដ្ឋានត្រូវបានកត់ត្រាដោយជោគជ័យ។'
        : 'របាយការណ៍ត្រូវបានកត់ត្រាចំណាររបស់ថ្នាក់ដឹកនាំដោយជោគជ័យ។',
      timer: 1800,
      showConfirmButton: false,
    });

    closeReviewModal();
    if (showViewModal.value && currentReport.value?.id === reportId && res.data?.data) {
      currentReport.value = res.data.data;
    }

    await fetchReports();
    await fetchStats();
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ',
      text: err.response?.data?.message || 'មិនអាចកត់ត្រាចំណារបានទេ។',
    });
  } finally {
    reviewing.value = false;
  }
}

// PRINT MODAL
function openPrintModal(report) {
  currentReport.value = report;
  showPrintModal.value = true;
}

function closePrintModal() {
  showPrintModal.value = false;
}

function printReport() {
  window.print();
}

// ON MOUNTED
onMounted(async () => {
  await fetchStats();
  await fetchFilterOptions();
  await fetchReports();
});
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}

.custom-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.55);
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  overflow-y: auto;
}

.custom-modal-content {
  border-radius: 10px;
  max-height: 92vh;
}

.sticky-modal-footer {
  position: sticky;
  bottom: 0;
  background-color: #fff;
  border-top: 1px solid #dee2e6;
  z-index: 10;
}

.text-pre-wrap {
  white-space: pre-wrap;
  word-break: break-word;
}

.font-khmer {
  font-family: 'Khmer OS Siemreap', 'Siemreap', 'Kantumruy Pro', 'Battambang', sans-serif;
}

.font-khmer-moul {
  font-family: 'Khmer OS Muol Light', 'Moul', cursive, serif;
}

/* Print styles */
@media print {
  body * {
    visibility: hidden;
  }
  #printable-report,
  #printable-report * {
    visibility: visible;
  }
  #printable-report {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    margin: 0;
    padding: 20px;
    background: #fff !important;
    color: #000 !important;
  }
  .no-print {
    display: none !important;
  }
}
</style>
