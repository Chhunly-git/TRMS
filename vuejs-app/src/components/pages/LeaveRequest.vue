<template>
  <div class="content-wrapper khmer-layout">
    <!-- Header Content -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-3 align-items-center">
          <div class="col-sm-6">
            <h1 class="m-0 font-weight-bold text-dark khmer-page-title">
              <i class="fas fa-calendar-minus text-success mr-2"></i>ប្រព័ន្ធគ្រប់គ្រងការសុំច្បាប់ឈប់សម្រាក
            </h1>
          </div>
          <div class="col-sm-6 text-right">
            <button class="btn btn-dark-custom shadow-sm font-khmer" @click="openCreateModal">
              <i class="fas fa-plus-circle mr-1"></i> បង្កើតពាក្យសុំច្បាប់ថ្មី
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="content">
      <div class="container-fluid">

        <!-- 1. Stats Overview Cards -->
        <div class="row mb-3">
          <!-- Card 1 -->
          <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <div class="card stat-card shadow-sm border-0 rounded-lg h-100 cursor-pointer transition-all"
                 :class="{ 'active-stat-card border-primary': activeTab === 'my' }"
                 @click="switchTab('my')">
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted small d-block font-weight-bold">
                    {{ activeTab === 'my' ? 'សំណើផ្ទាល់ខ្លួនសរុប' : (activeTab === 'pending_review' ? 'រង់ចាំខ្ញុំពិនិត្យ & ចារ' : 'សំណើសរុបទាំងអស់') }}
                  </span>
                  <h3 class="font-weight-bold mb-0" :class="activeTab === 'pending_review' ? 'text-warning' : 'text-dark'">
                    {{ activeTab === 'my' ? (stats.my_stats?.total || 0) : (activeTab === 'pending_review' ? (stats.pending_reviews_count || 0) : (stats.all_stats?.total || 0)) }}
                  </h3>
                </div>
                <div class="stat-icon-circle" :class="activeTab === 'pending_review' ? 'bg-warning-light text-warning' : 'bg-info-light text-info'">
                  <i class="fas fa-lg" :class="activeTab === 'pending_review' ? 'fa-clipboard-check' : 'fa-file-alt'"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 2 -->
          <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <div class="card stat-card shadow-sm border-0 rounded-lg h-100 cursor-pointer transition-all"
                 :class="{ 'active-stat-card border-warning': activeTab === 'pending_review' && stats.can_review }"
                 @click="stats.can_review ? switchTab('pending_review') : null">
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted small d-block font-weight-bold">
                    {{ activeTab === 'my' ? 'កំពុងរង់ចាំពិនិត្យ' : (activeTab === 'pending_review' ? 'បានចារ & បញ្ជូនបន្ត' : 'កំពុងរង់ចាំពិនិត្យ') }}
                  </span>
                  <h3 class="font-weight-bold mb-0" :class="activeTab === 'pending_review' ? 'text-info' : 'text-warning'">
                    {{ activeTab === 'my' ? (stats.my_stats?.pending || 0) : (activeTab === 'pending_review' ? (stats.review_stats?.forwarded || 0) : (stats.all_stats?.pending || 0)) }}
                  </h3>
                </div>
                <div class="stat-icon-circle" :class="activeTab === 'pending_review' ? 'bg-info-light text-info' : 'bg-warning-light text-warning'">
                  <i class="fas fa-lg" :class="activeTab === 'pending_review' ? 'fa-share' : 'fa-clock'"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 3 -->
          <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <div class="card stat-card shadow-sm border-0 rounded-lg h-100">
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted small d-block font-weight-bold">បានឯកភាព / អនុម័ត</span>
                  <h3 class="font-weight-bold mb-0 text-success">
                    {{ activeTab === 'my' ? (stats.my_stats?.approved || 0) : (activeTab === 'pending_review' ? (stats.review_stats?.approved || 0) : (stats.all_stats?.approved || 0)) }}
                  </h3>
                </div>
                <div class="stat-icon-circle bg-success-light text-success">
                  <i class="fas fa-lg" :class="activeTab === 'pending_review' ? 'fa-check-double' : 'fa-check-circle'"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 4 -->
          <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <div class="card stat-card shadow-sm border-0 rounded-lg h-100">
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted small d-block font-weight-bold">
                    {{ activeTab === 'my' ? 'ច្បាប់ប្រចាំឆ្នាំបានប្រើ (ឆ្នាំនេះ)' : (activeTab === 'pending_review' ? 'បានបដិសេធ / ត្រឡប់' : 'បានបដិសេធ') }}
                  </span>
                  <h3 class="font-weight-bold mb-0" :class="activeTab === 'my' ? 'text-primary' : 'text-danger'">
                    <template v-if="activeTab === 'my'">
                      {{ stats.my_stats?.annual_days_used || 0 }} ថ្ងៃ
                    </template>
                    <template v-else-if="activeTab === 'pending_review'">
                      {{ stats.review_stats?.rejected || 0 }}
                    </template>
                    <template v-else>
                      {{ stats.all_stats?.rejected || 0 }}
                    </template>
                  </h3>
                </div>
                <div class="stat-icon-circle" :class="activeTab === 'my' ? 'bg-primary-light text-primary' : 'bg-danger-light text-danger'">
                  <i class="fas fa-lg" :class="activeTab === 'my' ? 'fa-calendar-day' : 'fa-ban'"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Main Card with Tabs & Table -->
        <div class="card shadow-sm border-0 rounded-lg mb-4">
          <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center flex-wrap">
            <ul class="nav nav-pills">
              <li class="nav-item">
                <a class="nav-link cursor-pointer" :class="{ active: activeTab === 'my' }" @click.prevent="switchTab('my')">
                  <i class="fas fa-user mr-1"></i> ពាក្យសុំច្បាប់របស់ខ្ញុំ
                </a>
              </li>
              <li class="nav-item" v-if="stats.can_review">
                <a class="nav-link cursor-pointer position-relative" :class="{ active: activeTab === 'pending_review' }" @click.prevent="switchTab('pending_review')">
                  <i class="fas fa-clipboard-check mr-1"></i> សំណើត្រូវពិនិត្យ & អនុម័ត
                  <span v-if="stats.pending_reviews_count > 0" class="badge badge-danger ml-1">
                    {{ stats.pending_reviews_count }}
                  </span>
                </a>
              </li>
              <li class="nav-item" v-if="userStore.isAdmin">
                <a class="nav-link cursor-pointer" :class="{ active: activeTab === 'all' }" @click.prevent="switchTab('all')">
                  <i class="fas fa-list-alt mr-1"></i> សំណើទាំងអស់ (Admin)
                </a>
              </li>
            </ul>

            <div class="card-tools mt-2 mt-sm-0">
              <button class="btn btn-dark-custom btn-sm shadow-sm font-khmer" @click="openCreateModal">
                <i class="fas fa-plus-circle mr-1"></i> បង្កើតពាក្យសុំច្បាប់ថ្មី
              </button>
            </div>
          </div>

          <!-- Card Body with Filters & Table -->
          <div class="card-body p-3">

            <!-- Filter Controls -->
            <div class="row mb-3">
              <div class="col-md-3 col-sm-6 mb-2">
                <div class="input-group input-group-sm">
                  <input type="text" v-model="filterSearch" class="form-control" placeholder="ស្វែងរកលេខកូដ មូលហេតុ ឬឈ្មោះ..." @keyup.enter="loadRequests">
                  <div class="input-group-append">
                    <button class="btn btn-primary" @click="loadRequests"><i class="fas fa-search"></i></button>
                  </div>
                </div>
              </div>

              <div class="col-md-3 col-sm-6 mb-2">
                <select v-model="filterType" class="form-control form-control-sm" @change="loadRequests">
                  <option value="">-- ប្រភេទច្បាប់ទាំងអស់ --</option>
                  <option value="ANNUAL">ច្បាប់ឈប់សម្រាកប្រចាំឆ្នាំ</option>
                  <option value="SHORT_TERM">ច្បាប់ឈប់សម្រាករយៈពេលខ្លី</option>
                  <option value="MATERNITY">ច្បាប់ឈប់សម្រាកលំហែមាតុភាព</option>
                  <option value="SICK">ច្បាប់ឈប់សម្រាកព្យាបាលជំងឺ</option>
                  <option value="PERSONAL">ច្បាប់ឈប់សម្រាកមានកិច្ចការផ្ទាល់ខ្លួន</option>
                </select>
              </div>

              <div class="col-md-3 col-sm-6 mb-2">
                <select v-model="filterStatus" class="form-control form-control-sm" @change="loadRequests">
                  <option value="">-- ស្ថានភាពទាំងអស់ --</option>
                  <option value="DRAFT">សេចក្តីព្រាង (DRAFT)</option>
                  <option value="PENDING">កំពុងរង់ចាំពិនិត្យ (PENDING)</option>
                  <option value="APPROVED">បានឯកភាព/អនុម័ត (APPROVED)</option>
                  <option value="REJECTED">បានបដិសេធ (REJECTED)</option>
                  <option value="CANCELLED">បានបោះបង់ (CANCELLED)</option>
                </select>
              </div>

              <div class="col-md-3 col-sm-6 mb-2 text-right">
                <button class="btn btn-outline-secondary btn-sm" @click="resetFilters" title="សម្អាត Filter">
                  <i class="fas fa-sync-alt mr-1"></i> កំណត់ឡើងវិញ
                </button>
              </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
              <table class="table custom-table table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <th class="text-center" style="width: 60px;">ល.រ</th>
                    <th class="text-center" style="width: 130px;">លេខកូដសំណើ</th>
                    <th v-if="activeTab !== 'my'" style="min-width: 170px;">មន្ត្រីស្នើសុំ</th>
                    <th style="min-width: 170px;">ប្រភេទច្បាប់</th>
                    <th style="min-width: 160px;">កាលបរិច្ឆេទឈប់សម្រាក</th>
                    <th class="text-center" style="width: 110px;">រយៈពេល</th>
                    <th class="text-center" style="width: 120px;">ចូលធ្វើការវិញ</th>
                    <th style="min-width: 160px;">អ្នកពិនិត្យបច្ចុប្បន្ន</th>
                    <th class="text-center" style="width: 120px;">ស្ថានភាព</th>
                    <th class="text-center" style="width: 140px;">សកម្មភាព</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading">
                    <td :colspan="activeTab !== 'my' ? 10 : 9" class="text-center py-4">
                      <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                      </div>
                      <div class="text-muted mt-2">កំពុងផ្ទុកទិន្នន័យ...</div>
                    </td>
                  </tr>

                  <tr v-else-if="requests.length === 0">
                    <td :colspan="activeTab !== 'my' ? 10 : 9" class="text-center py-5 text-muted">
                      <i class="fas fa-folder-open fa-3x mb-2 text-secondary"></i>
                      <p class="mb-0">ពុំមានទិន្នន័យពាក្យសុំច្បាប់ឡើយ</p>
                    </td>
                  </tr>

                  <tr v-for="(req, index) in requests" :key="req.id">
                    <td class="text-center font-weight-bold">{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
                    <td class="text-center">
                      <span class="badge badge-light border text-dark font-monospace">{{ req.request_number }}</span>
                    </td>
                    <td v-if="activeTab !== 'my'">
                      <div class="d-flex align-items-center">
                        <img :src="req.user?.profile_image || emptyAvatar" class="img-circle mr-2" style="width: 34px; height: 34px; object-fit: cover; border: 1px solid #dee2e6;">
                        <div>
                          <div class="font-weight-bold text-dark">{{ req.user?.name_kh || req.user?.name }}</div>
                          <small class="text-muted">{{ req.user?.position?.title_kh || req.position?.title_kh || 'មន្ត្រី' }}</small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge" :class="getLeaveTypeBadgeClass(req.leave_type)">
                        <i :class="getLeaveTypeIcon(req.leave_type)" class="mr-1"></i>
                        {{ req.leave_type_kh }}
                      </span>
                    </td>
                    <td class="text-center">
                      <div class="font-weight-bold text-dark">{{ formatDate(req.start_date) }}</div>
                      <small class="text-muted">ដល់ {{ formatDate(req.end_date) }}</small>
                    </td>
                    <td class="text-center">
                      <span class="badge badge-info px-2 py-1">
                        {{ req.duration_days }} ថ្ងៃ
                      </span>
                      <div v-if="req.is_half_day" class="badge badge-secondary mt-1">
                        {{ req.half_day_type === 'MORNING' ? 'ព្រឹក' : 'រសៀល' }}
                      </div>
                    </td>
                    <td class="text-center">
                      <span class="text-primary font-weight-bold">{{ formatDate(req.resume_date) }}</span>
                    </td>
                    <td>
                      <div v-if="req.current_approver">
                        <span class="font-weight-bold text-dark">{{ req.current_approver.name_kh || req.current_approver.name }}</span>
                        <div class="small text-muted">{{ req.current_approver.position?.title_kh || 'ថ្នាក់ដឹកនាំ' }}</div>
                      </div>
                      <div v-else-if="req.status === 'APPROVED'">
                        <span class="text-success small"><i class="fas fa-check mr-1"></i>អនុម័តរួចរាល់</span>
                      </div>
                      <div v-else-if="req.status === 'DRAFT'">
                        <span class="text-secondary small">សេចក្តីព្រាង (មិនទាន់បញ្ជូន)</span>
                      </div>
                      <div v-else>
                        <span class="text-muted small">-</span>
                      </div>
                    </td>
                    <td class="text-center">
                      <span class="badge" :class="getStatusBadgeClass(req.status)">
                        {{ req.status_kh }}
                      </span>
                    </td>
                    <td class="text-center">
                      <div class="action-buttons">
                        <!-- View Details Modal -->
                        <button class="btn btn-action btn-view" title="មើលព័ត៌មានលម្អិត & ប្រវត្តិចំណារ" @click="openViewModal(req)">
                          <i class="fas fa-eye"></i>
                        </button>


                        <!-- Action for Reviewer -->
                        <button v-if="activeTab === 'pending_review' || (canReviewRequest(req) && req.status === 'PENDING')" 
                                class="btn btn-action btn-review" 
                                title="ពិនិត្យ & ធ្វើចំណារ" 
                                @click="openReviewModal(req)">
                          <i class="fas fa-signature"></i>
                        </button>

                        <!-- Edit Draft -->
                        <button v-if="req.status === 'DRAFT' && req.user_id === userStore.id" 
                                class="btn btn-action btn-edit" 
                                title="កែសម្រួល" 
                                @click="openEditModal(req)">
                          <i class="fas fa-edit"></i>
                        </button>

                        <!-- Cancel Pending -->
                        <button v-if="req.status === 'PENDING' && req.user_id === userStore.id" 
                                class="btn btn-action btn-cancel" 
                                title="បោះបង់ពាក្យសុំ" 
                                @click="handleCancel(req)">
                          <i class="fas fa-ban"></i>
                        </button>

                        <!-- Delete Draft -->
                        <button v-if="req.status === 'DRAFT' && req.user_id === userStore.id" 
                                class="btn btn-action btn-delete" 
                                title="លុបសេចក្តីព្រាង" 
                                @click="handleDelete(req)">
                          <i class="fas fa-trash-alt"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3" v-if="pagination.total > pagination.per_page">
              <div class="text-muted small">
                បង្ហាញ {{ (pagination.current_page - 1) * pagination.per_page + 1 }} ដល់ {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} នៃសំណើសរុប {{ pagination.total }}
              </div>
              <ul class="pagination pagination-sm m-0">
                <li class="page-item" :class="{ disabled: pagination.current_page <= 1 }">
                  <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page - 1)">&laquo;</a>
                </li>
                <li v-for="p in pagination.last_page" :key="p" class="page-item" :class="{ active: p === pagination.current_page }">
                  <a class="page-link" href="#" @click.prevent="changePage(p)">{{ p }}</a>
                </li>
                <li class="page-item" :class="{ disabled: pagination.current_page >= pagination.last_page }">
                  <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page + 1)">&raquo;</a>
                </li>
              </ul>
            </div>

          </div>
        </div>

      </div>
    </section>

    <!-- ============================================================= -->
    <!-- 3. Modal: បង្កើត / កែសម្រួលពាក្យសុំច្បាប់ (Create / Edit Modal) -->
    <!-- ============================================================= -->
    <div v-if="showCreateModal" class="custom-modal-backdrop" @click.self="closeCreateModal">
      <div class="modal-dialog modal-lg my-auto" role="document" style="width: 100%; max-width: 850px;">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-dark-custom text-white">
            <h5 class="modal-title font-weight-bold">
              <i class="fas fa-calendar-plus mr-2"></i>{{ isEditing ? 'កែសម្រួលពាក្យសុំច្បាប់' : 'បង្កើតពាក្យសុំច្បាប់ថ្មី' }}
            </h5>
            <button type="button" class="close text-white" @click="closeCreateModal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form @submit.prevent="submitForm">
            <div class="modal-body">
              <div class="row">
                <!-- Leave Type -->
                <div class="col-md-6 form-group">
                  <label class="font-weight-bold">ប្រភេទច្បាប់ឈប់សម្រាក <span class="text-danger">*</span></label>
                  <select v-model="form.leave_type" class="form-control" required @change="onDatesChanged">
                    <option value="ANNUAL">ច្បាប់ឈប់សម្រាកប្រចាំឆ្នាំ (Annual)</option>
                    <option value="SHORT_TERM">ច្បាប់ឈប់សម្រាករយៈពេលខ្លី (Short-term)</option>
                    <option value="MATERNITY">ច្បាប់ឈប់សម្រាកលំហែមាតុភាព (Maternity)</option>
                    <option value="SICK">ច្បាប់ឈប់សម្រាកព្យាបាលជំងឺ (Sick)</option>
                    <option value="PERSONAL">ច្បាប់ឈប់សម្រាកមានកិច្ចការផ្ទាល់ខ្លួន (Personal)</option>
                  </select>
                </div>

                <!-- Contact Phone -->
                <div class="col-md-6 form-group">
                  <label class="font-weight-bold">លេខទូរស័ព្ទទំនាក់ទំនងអំឡុងពេលឈប់</label>
                  <input type="text" v-model="form.contact_phone" class="form-control" placeholder="ឧ. 012 345 678">
                </div>

                <!-- Half-day Checkbox -->
                <div class="col-12 mb-2">
                  <div class="custom-control custom-checkbox bg-light p-2 border rounded">
                    <input type="checkbox" class="custom-control-input" id="isHalfDay" v-model="form.is_half_day" @change="onDatesChanged">
                    <label class="custom-control-label font-weight-bold text-dark" for="isHalfDay">
                      ស្នើសុំឈប់សម្រាកកន្លះថ្ងៃ (០.៥ ថ្ងៃ)
                    </label>
                    <div v-if="form.is_half_day" class="mt-2 ml-4">
                      <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="halfMorning" value="MORNING" v-model="form.half_day_type" class="custom-control-input" @change="onDatesChanged">
                        <label class="custom-control-label" for="halfMorning">ពេលព្រឹក (MORNING)</label>
                      </div>
                      <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="halfAfternoon" value="AFTERNOON" v-model="form.half_day_type" class="custom-control-input" @change="onDatesChanged">
                        <label class="custom-control-label" for="halfAfternoon">ពេលរសៀល (AFTERNOON)</label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Start Date -->
                <div :class="form.is_half_day ? 'col-md-6' : 'col-md-4'" class="form-group">
                  <label class="font-weight-bold">{{ form.is_half_day ? 'កាលបរិច្ឆេទសុំឈប់' : 'ថ្ងៃចាប់ផ្តើមឈប់' }} <span class="text-danger">*</span></label>
                  <input type="date" v-model="form.start_date" class="form-control" required @change="onDatesChanged">
                </div>

                <!-- End Date (hidden or locked if half day) -->
                <div class="col-md-4 form-group" v-if="!form.is_half_day">
                  <label class="font-weight-bold">ថ្ងៃបញ្ចប់ការឈប់ <span class="text-danger">*</span></label>
                  <input type="date" v-model="form.end_date" class="form-control" required @change="onDatesChanged">
                </div>

                <!-- Resume Work Date -->
                <div :class="form.is_half_day ? 'col-md-6' : 'col-md-4'" class="form-group">
                  <label class="font-weight-bold">ថ្ងៃចូលបម្រើការងារវិញ <span class="text-danger">*</span></label>
                  <input type="date" v-model="form.resume_date" class="form-control" required>
                  <small class="text-muted">គណនាស្វ័យប្រវត្តិ (អាចកែបាន)</small>
                </div>

                <!-- Total Days Calculated Badge -->
                <div class="col-12 mb-3">
                  <div class="alert alert-info py-2 d-flex align-items-center justify-content-between mb-0">
                    <div>
                      <i class="fas fa-calculator mr-2"></i>
                      <span>ចំនួនថ្ងៃសុំច្បាប់សរុប៖ </span>
                      <strong class="h5 mb-0 text-primary">{{ form.duration_days }} ថ្ងៃ</strong>
                    </div>
                    <small class="text-muted" v-if="!form.is_half_day && form.leave_type !== 'MATERNITY'">
                      (មិនរាប់បញ្ចូលថ្ងៃសៅរ៍ និងអាទិត្យ)
                    </small>
                  </div>
                </div>

                <!-- Reason -->
                <div class="col-12 form-group">
                  <label class="font-weight-bold">មូលហេតុនៃការសុំច្បាប់ឈប់សម្រាក <span class="text-danger">*</span></label>
                  <textarea v-model="form.reason" class="form-control" rows="3" placeholder="សូមបញ្ជាក់មូលហេតុច្បាស់លាស់..." required></textarea>
                </div>

                <!-- Attachment -->
                <div class="col-12 form-group">
                  <label class="font-weight-bold">ឯកសារភ្ជាប់ (លិខិតពេទ្យ ឬឯកសារយោង - បើមាន)</label>
                  <input type="file" class="form-control-file" @change="onFileSelected" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                  <small class="text-muted">គាំទ្រប្រភេទ PDF, Word ឬ រូបភាព (ទំហំអតិបរមា 20MB)</small>
                </div>

                <!-- Next Approver Dropdown (with On-Leave Indicator & Skip) -->
                <div class="col-12 form-group bg-light p-3 border rounded">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="font-weight-bold text-dark mb-0">
                      <i class="fas fa-user-check text-success mr-1"></i>
                      ជ្រើសរើសថ្នាក់ដឹកនាំដែលត្រូវដាក់ជូនពិនិត្យ
                      <span class="text-danger">*</span>
                    </label>

                    <!-- Toggle Skip to Next Tier if Available -->
                    <button type="button" v-if="approverCandidates.can_skip_to_next_tier" 
                            class="btn btn-outline-warning btn-xs" 
                            @click="useNextTier = !useNextTier">
                      <i class="fas fa-forward mr-1"></i>
                      {{ useNextTier ? 'ប្តូរមកថ្នាក់ដឹកនាំបន្ទាល់ផ្ទាល់' : 'រំលងទៅកាន់ថ្នាក់បន្ទាប់ (ករណីថ្នាក់ដឹកនាំឈប់សម្រាក)' }}
                    </button>
                  </div>

                  <!-- Candidate Selection -->
                  <select v-model="form.next_approver_id" class="form-control" :required="submitNowSelected">
                    <option value="">-- សូមជ្រើសរើសថ្នាក់ដឹកនាំ --</option>
                    <template v-if="!useNextTier">
                      <option v-for="cand in approverCandidates.candidates" :key="cand.id" :value="cand.id">
                        {{ cand.name_kh || cand.name }} - {{ cand.position_title }} ({{ cand.office_name || cand.department_name || 'អគ្គនាយកដ្ឋាន' }})
                        {{ cand.is_on_leave ? ' ⚠️ [' + (cand.leave_note || 'កំពុងឈប់សម្រាក') + ']' : '' }}
                      </option>
                    </template>
                    <template v-else>
                      <option v-for="cand in approverCandidates.next_tier_candidates" :key="cand.id" :value="cand.id">
                        [ថ្នាក់បន្ទាប់] {{ cand.name_kh || cand.name }} - {{ cand.position_title }} ({{ cand.office_name || cand.department_name || 'អគ្គនាយកដ្ឋាន' }})
                        {{ cand.is_on_leave ? ' ⚠️ [' + (cand.leave_note || 'កំពុងឈប់សម្រាក') + ']' : '' }}
                      </option>
                    </template>
                  </select>

                  <div v-if="selectedApproverOnLeave" class="alert alert-warning mt-2 py-1 px-2 mb-0 small">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>ចំណាំ៖</strong> ថ្នាក់ដឹកនាំដែលលោកអ្នកបានជ្រើសរើសកំពុងមានវត្តមានឈប់សម្រាក។ លោកអ្នកអាចជ្រើសរើសថ្នាក់ដឹកនាំផ្សេង ឬចុច "រំលងទៅកាន់ថ្នាក់បន្ទាប់" បាន។
                  </div>
                </div>

              </div>
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-default" @click="closeCreateModal">បោះបង់</button>
              <div>
                <button type="submit" class="btn btn-secondary mr-2" @click="submitNowSelected = false">
                  <i class="fas fa-save mr-1"></i> រក្សាទុកជាព្រាង
                </button>
                <button type="submit" class="btn btn-primary" @click="submitNowSelected = true">
                  <i class="fas fa-paper-plane mr-1"></i> ដាក់ជូនភ្លាមៗ
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- ============================================================= -->
    <!-- 4. Modal: ថ្នាក់ដឹកនាំពិនិត្យ និងធ្វើចំណារ (Review & Action Modal) -->
    <!-- ============================================================= -->
    <div v-if="showReviewModal && currentSelectedRequest" class="custom-modal-backdrop" @click.self="closeReviewModal">
      <div class="modal-dialog modal-lg my-auto" role="document" style="width: 100%; max-width: 850px;">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title font-weight-bold">
              <i class="fas fa-file-signature mr-2"></i>ពិនិត្យ និងសម្រេចលើពាក្យសុំច្បាប់ ({{ currentSelectedRequest.request_number }})
            </h5>
            <button type="button" class="close" @click="closeReviewModal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            
            <!-- Applicant Details Banner -->
            <div class="card card-outline card-secondary mb-3 shadow-sm">
              <div class="card-body p-3">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <img :src="currentSelectedRequest.user?.profile_image || emptyAvatar" class="img-circle elevation-1" style="width: 55px; height: 55px; object-fit: cover;">
                  </div>
                  <div class="col">
                    <h5 class="mb-1 font-weight-bold text-dark">{{ currentSelectedRequest.user?.name_kh || currentSelectedRequest.user?.name }}</h5>
                    <div class="text-muted small">
                      <span class="mr-3"><i class="fas fa-id-badge mr-1"></i>{{ currentSelectedRequest.user?.position?.title_kh || 'មន្ត្រី' }}</span>
                      <span class="mr-3"><i class="fas fa-door-open mr-1"></i>{{ currentSelectedRequest.office?.name_kh || 'មិនមានការិយាល័យ' }}</span>
                      <span><i class="fas fa-building mr-1"></i>{{ currentSelectedRequest.department?.name_kh || 'មិនមាននាយកដ្ឋាន' }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Leave Info Box -->
            <div class="row mb-3">
              <div class="col-md-6">
                <ul class="list-group list-group-unbordered">
                  <li class="list-group-item py-1">
                    <b>ប្រភេទច្បាប់៖</b> <span class="float-right badge badge-primary">{{ currentSelectedRequest.leave_type_kh }}</span>
                  </li>
                  <li class="list-group-item py-1">
                    <b>កាលបរិច្ឆេទឈប់៖</b> <span class="float-right text-dark">{{ formatDate(currentSelectedRequest.start_date) }} ដល់ {{ formatDate(currentSelectedRequest.end_date) }}</span>
                  </li>
                  <li class="list-group-item py-1">
                    <b>ចំនួនថ្ងៃសរុប៖</b> <span class="float-right badge badge-info">{{ currentSelectedRequest.duration_days }} ថ្ងៃ {{ currentSelectedRequest.is_half_day ? '(' + (currentSelectedRequest.half_day_type === 'MORNING' ? 'ព្រឹក' : 'រសៀល') + ')' : '' }}</span>
                  </li>
                </ul>
              </div>
              <div class="col-md-6">
                <ul class="list-group list-group-unbordered">
                  <li class="list-group-item py-1">
                    <b>ថ្ងៃចូលធ្វើការវិញ៖</b> <span class="float-right text-success font-weight-bold">{{ formatDate(currentSelectedRequest.resume_date) }}</span>
                  </li>
                  <li class="list-group-item py-1">
                    <b>លេខទូរស័ព្ទ៖</b> <span class="float-right text-dark">{{ currentSelectedRequest.contact_phone || 'មិនមាន' }}</span>
                  </li>
                  <li class="list-group-item py-1">
                    <b>ឯកសារភ្ជាប់៖</b> 
                    <span class="float-right">
                      <a v-if="currentSelectedRequest.attachment_path" :href="getAttachmentUrl(currentSelectedRequest.id)" target="_blank" class="text-primary font-weight-bold">
                        <i class="fas fa-paperclip mr-1"></i>ទាញយកឯកសារ
                      </a>
                      <span v-else class="text-muted">មិនមាន</span>
                    </span>
                  </li>
                </ul>
              </div>
              <div class="col-12 mt-2">
                <div class="p-2 bg-light border rounded">
                  <strong>មូលហេតុ៖ </strong> {{ currentSelectedRequest.reason }}
                </div>
              </div>
            </div>

            <!-- Previous Remarks / Timeline -->
            <div class="mb-3" v-if="currentSelectedRequest.approvals && currentSelectedRequest.approvals.length > 0">
              <label class="font-weight-bold text-dark"><i class="fas fa-history mr-1"></i>ប្រវត្តិចំណារ និងការឆ្លងកន្លងមក៖</label>
              <div class="timeline timeline-inverse p-2 border rounded bg-light" style="max-height: 220px; overflow-y: auto;">
                <div v-for="appr in currentSelectedRequest.approvals" :key="appr.id">
                  <i class="fas" :class="getTimelineIconClass(appr.action)"></i>
                  <div class="timeline-item">
                    <span class="time"><i class="far fa-clock mr-1"></i>{{ formatDateTime(appr.created_at) }}</span>
                    <h3 class="timeline-header font-weight-bold">
                      {{ appr.user?.name_kh || appr.user?.name }} ({{ appr.user?.position?.title_kh || 'ថ្នាក់ដឹកនាំ' }})
                      <span class="badge ml-2" :class="getApprovalActionBadge(appr.action)">{{ appr.action_kh }}</span>
                    </h3>
                    <div class="timeline-body" v-if="appr.remarks">
                      {{ appr.remarks }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Reviewer's Remarks Input -->
            <div class="form-group">
              <label class="font-weight-bold text-dark">ចំណារ / មតិយោបល់របស់លោកអ្នក <span class="text-danger">*</span></label>
              <textarea v-model="reviewActionForm.remarks" class="form-control" rows="3" placeholder="បញ្ចូលចំណារ ឬមតិណែនាំនៅទីនេះ..."></textarea>
            </div>

            <!-- Action Select Options -->
            <div class="form-group">
              <label class="font-weight-bold text-dark">ជ្រើសរើសសកម្មភាពសម្រេច៖</label>
              <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn mr-2 mb-2" 
                        :class="reviewActionForm.action === 'FORWARD' ? 'btn-primary' : 'btn-outline-primary'" 
                        @click="setAction('FORWARD')">
                  <i class="fas fa-share mr-1"></i> បញ្ជូនបន្តទៅថ្នាក់លើ
                </button>
                <button v-if="isDirectorGeneral" type="button" class="btn mr-2 mb-2" 
                        :class="reviewActionForm.action === 'APPROVE' ? 'btn-success' : 'btn-outline-success'" 
                        @click="setAction('APPROVE')">
                  <i class="fas fa-check-double mr-1"></i> ឯកភាព / អនុម័តជាផ្លូវការ
                </button>
                <button type="button" class="btn mr-2 mb-2" 
                        :class="reviewActionForm.action === 'RETURN' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark'" 
                        @click="setAction('RETURN')">
                  <i class="fas fa-undo mr-1"></i> ប្រគល់ត្រឡប់ឱ្យកែសម្រួល
                </button>
                <button type="button" class="btn mb-2" 
                        :class="reviewActionForm.action === 'REJECT' ? 'btn-danger' : 'btn-outline-danger'" 
                        @click="setAction('REJECT')">
                  <i class="fas fa-ban mr-1"></i> បដិសេធ
                </button>
              </div>
            </div>

            <!-- If FORWARD selected: choose next candidate -->
            <div class="form-group bg-light p-3 border rounded" v-if="reviewActionForm.action === 'FORWARD'">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="font-weight-bold text-dark mb-0">ជ្រើសរើសថ្នាក់ដឹកនាំដែលត្រូវបញ្ជូនបន្តទៅ <span class="text-danger">*</span></label>
                <button type="button" v-if="reviewCandidates.can_skip_to_next_tier" class="btn btn-outline-warning btn-xs" @click="useReviewNextTier = !useReviewNextTier">
                  <i class="fas fa-forward mr-1"></i> {{ useReviewNextTier ? 'ប្តូរមកថ្នាក់ដឹកនាំបន្ទាល់ផ្ទាល់' : 'រំលងទៅថ្នាក់លើបន្ទាប់' }}
                </button>
              </div>

              <select v-model="reviewActionForm.next_approver_id" class="form-control" required>
                <option value="">-- សូមជ្រើសរើសថ្នាក់ដឹកនាំបន្ត --</option>
                <template v-if="!useReviewNextTier">
                  <option v-for="cand in reviewCandidates.candidates" :key="cand.id" :value="cand.id">
                    {{ cand.name_kh || cand.name }} - {{ cand.position_title }} ({{ cand.office_name || cand.department_name || 'អគ្គនាយកដ្ឋាន' }})
                    {{ cand.is_on_leave ? ' ⚠️ [' + (cand.leave_note || 'កំពុងឈប់សម្រាក') + ']' : '' }}
                  </option>
                </template>
                <template v-else>
                  <option v-for="cand in reviewCandidates.next_tier_candidates" :key="cand.id" :value="cand.id">
                    [ថ្នាក់បន្ទាប់] {{ cand.name_kh || cand.name }} - {{ cand.position_title }} ({{ cand.office_name || cand.department_name || 'អគ្គនាយកដ្ឋាន' }})
                    {{ cand.is_on_leave ? ' ⚠️ [' + (cand.leave_note || 'កំពុងឈប់សម្រាក') + ']' : '' }}
                  </option>
                </template>
              </select>
            </div>

            <!-- If REJECT selected: rejection reason -->
            <div class="form-group" v-if="reviewActionForm.action === 'REJECT'">
              <label class="font-weight-bold text-danger">មូលហេតុនៃការបដិសេធ <span class="text-danger">*</span></label>
              <textarea v-model="reviewActionForm.rejection_reason" class="form-control border-danger" rows="2" placeholder="សូមបញ្ជាក់មូលហេតុបដិសេធ..."></textarea>
            </div>

          </div>
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" @click="closeReviewModal">បិទ</button>
            <button type="button" class="btn btn-primary" :disabled="submittingAction" @click="submitReviewAction">
              <span v-if="submittingAction" class="spinner-border spinner-border-sm mr-1"></span>
              <i class="fas fa-check-circle mr-1"></i> អនុវត្តសកម្មភាព
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================= -->
    <!-- 5. Modal: មើលព័ត៌មានលម្អិតនៃសំណើ (System Details Modal) -->
    <!-- ============================================================= -->
    <div v-if="showViewModal && selectedViewRequest" class="custom-modal-backdrop" @click.self="closeViewModal">
      <div class="modal-dialog modal-lg my-auto" role="document" style="width: 100%; max-width: 900px;">
        <div class="modal-content border-0 shadow-lg">
          
          <!-- Header -->
          <div class="modal-header bg-dark-custom text-white d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center flex-wrap">
              <h5 class="modal-title font-weight-bold mr-3 mb-0">
                <i class="fas fa-file-invoice mr-2 text-info"></i>ព័ត៌មានលម្អិតនៃពាក្យសុំច្បាប់ឈប់សម្រាក
              </h5>
              <span class="badge badge-light border text-dark font-monospace mr-2">
                {{ selectedViewRequest.request_number }}
              </span>
              <span class="badge" :class="getStatusBadgeClass(selectedViewRequest.status)">
                {{ selectedViewRequest.status_kh }}
              </span>
            </div>
            <button type="button" class="close text-white" @click="closeViewModal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <!-- Body -->
          <div class="modal-body p-4" style="max-height: 80vh; overflow-y: auto;">

            <!-- Applicant Card & Leave Summary -->
            <div class="row mb-4">
              <!-- Officer Info -->
              <div class="col-md-6 mb-3 mb-md-0">
                <div class="card h-100 border shadow-none bg-light">
                  <div class="card-body p-3">
                    <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">
                      <i class="fas fa-user-circle mr-2 text-primary"></i>ព័ត៌មានមន្ត្រីស្នើសុំ
                    </h6>
                    <div class="d-flex align-items-start">
                      <img :src="selectedViewRequest.user?.profile_image || emptyAvatar" class="img-circle mr-3 border" style="width: 54px; height: 54px; object-fit: cover;">
                      <div class="small" style="line-height: 1.8;">
                        <div><strong>គោត្តនាម-នាម៖ </strong> {{ selectedViewRequest.user?.name_kh || selectedViewRequest.user?.name }}</div>
                        <div v-if="selectedViewRequest.user?.name_en" class="text-muted"><strong>ឈ្មោះឡាតាំង៖ </strong> {{ selectedViewRequest.user?.name_en }}</div>
                        <div><strong>អត្តលេខមន្ត្រី៖ </strong> {{ selectedViewRequest.user?.employee_code || '-' }}</div>
                        <div><strong>មុខតំណែង៖ </strong> {{ selectedViewRequest.user?.position?.title_kh || selectedViewRequest.position?.title_kh || 'មន្ត្រី' }}</div>
                        <div><strong>អង្គភាព៖ </strong> {{ selectedViewRequest.office?.name_kh || selectedViewRequest.department?.name_kh || '-' }}</div>
                        <div v-if="selectedViewRequest.office?.name_kh && selectedViewRequest.department?.name_kh">
                          <strong>នាយកដ្ឋាន៖ </strong> {{ selectedViewRequest.department?.name_kh }}
                        </div>
                        <div><strong>លេខទូរស័ព្ទ៖ </strong> {{ selectedViewRequest.contact_phone || selectedViewRequest.user?.phone || '-' }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Leave Spec -->
              <div class="col-md-6">
                <div class="card h-100 border shadow-none bg-light">
                  <div class="card-body p-3">
                    <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">
                      <i class="fas fa-calendar-alt mr-2 text-primary"></i>ព័ត៌មានច្បាប់ឈប់សម្រាក
                    </h6>
                    <div class="small" style="line-height: 1.8;">
                      <div>
                        <strong>ប្រភេទច្បាប់៖ </strong>
                        <span class="badge" :class="getLeaveTypeBadgeClass(selectedViewRequest.leave_type)">
                          <i :class="getLeaveTypeIcon(selectedViewRequest.leave_type)" class="mr-1"></i>
                          {{ selectedViewRequest.leave_type_kh }}
                        </span>
                      </div>
                      <div>
                        <strong>រយៈពេលសុំឈប់៖ </strong>
                        <span class="font-weight-bold text-primary">{{ selectedViewRequest.duration_days }} ថ្ងៃ</span>
                        <span v-if="selectedViewRequest.is_half_day" class="badge badge-secondary ml-1">
                          {{ selectedViewRequest.half_day_type === 'MORNING' ? 'ពេលព្រឹក (កន្លះថ្ងៃ)' : 'ពេលរសៀល (កន្លះថ្ងៃ)' }}
                        </span>
                      </div>
                      <div>
                        <strong>កាលបរិច្ឆេទឈប់៖ </strong>
                        <span>ចាប់ពី <strong>{{ formatDate(selectedViewRequest.start_date) }}</strong> ដល់ <strong>{{ formatDate(selectedViewRequest.end_date) }}</strong></span>
                      </div>
                      <div>
                        <strong>ចូលបម្រើការងារវិញ៖ </strong>
                        <span class="text-success font-weight-bold">{{ formatDate(selectedViewRequest.resume_date) }}</span>
                      </div>
                      <div>
                        <strong>កាលបរិច្ឆេទដាក់ពាក្យ៖ </strong>
                        <span class="text-muted">{{ formatDateTime(selectedViewRequest.created_at) }}</span>
                      </div>
                      <div v-if="selectedViewRequest.current_approver">
                        <strong>អ្នកទទួលពិនិត្យបច្ចុប្បន្ន៖ </strong>
                        <span class="font-weight-bold text-dark">{{ selectedViewRequest.current_approver.name_kh || selectedViewRequest.current_approver.name }}</span>
                        <span class="text-muted small ml-1">({{ selectedViewRequest.current_approver.position?.title_kh || 'ថ្នាក់ដឹកនាំ' }})</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Reason & Attachment -->
            <div class="row mb-4">
              <div class="col-12 mb-3">
                <div class="card border shadow-none">
                  <div class="card-header bg-white py-2">
                    <h6 class="font-weight-bold text-dark mb-0">
                      <i class="fas fa-comment-dots mr-2 text-warning"></i>មូលហេតុនៃការសុំច្បាប់
                    </h6>
                  </div>
                  <div class="card-body p-3 bg-light">
                    <p class="mb-0 text-dark" style="white-space: pre-line; line-height: 1.6;">
                      {{ selectedViewRequest.reason }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="col-12" v-if="selectedViewRequest.attachment_path">
                <div class="card border shadow-none">
                  <div class="card-header bg-white py-2">
                    <h6 class="font-weight-bold text-dark mb-0">
                      <i class="fas fa-paperclip mr-2 text-info"></i>ឯកសារភ្ជាប់
                    </h6>
                  </div>
                  <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                      <i class="fas fa-file-pdf text-danger fa-2x mr-3"></i>
                      <div>
                        <div class="font-weight-bold">{{ selectedViewRequest.attachment_name || 'ឯកសារភ្ជាប់' }}</div>
                        <small class="text-muted">ឯកសារយោងបញ្ជាក់</small>
                      </div>
                    </div>
                    <a :href="getLeaveAttachmentDownloadUrl(selectedViewRequest.id)" target="_blank" class="btn btn-outline-primary btn-sm">
                      <i class="fas fa-download mr-1"></i> ទាញយកឯកសារ
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Workflow Approval Timeline -->
            <div class="card border shadow-none mb-0">
              <div class="card-header bg-white py-2">
                <h6 class="font-weight-bold text-dark mb-0">
                  <i class="fas fa-history mr-2 text-primary"></i>ប្រវត្តិនៃការឆ្លងពិនិត្យ & ចំណារ (Workflow Timeline)
                </h6>
              </div>
              <div class="card-body p-3 bg-light">
                <div v-if="selectedViewRequest.approvals && selectedViewRequest.approvals.length > 0" class="timeline timeline-inverse m-0">
                  <div v-for="appr in selectedViewRequest.approvals" :key="appr.id">
                    <i class="fas" :class="getTimelineIconClass(appr.action)"></i>
                    <div class="timeline-item">
                      <span class="time"><i class="far fa-clock mr-1"></i>{{ formatDateTime(appr.created_at) }}</span>
                      <h3 class="timeline-header font-weight-bold">
                        {{ appr.user?.name_kh || appr.user?.name }} ({{ appr.user?.position?.title_kh || 'ថ្នាក់ដឹកនាំ' }})
                        <span class="badge ml-2" :class="getApprovalActionBadge(appr.action)">{{ appr.action_kh }}</span>
                        <span v-if="appr.forwarded_to" class="small text-muted ml-2">
                          <i class="fas fa-arrow-right mr-1"></i> ជូនទៅ៖ {{ appr.forwarded_to.name_kh || appr.forwarded_to.name }} ({{ appr.forwarded_to.position?.title_kh || 'ថ្នាក់ដឹកនាំ' }})
                        </span>
                      </h3>
                      <div class="timeline-body" v-if="appr.remarks">
                        <i class="fas fa-quote-left text-muted mr-1"></i> {{ appr.remarks }} <i class="fas fa-quote-right text-muted ml-1"></i>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center text-muted py-3">
                  <i class="fas fa-info-circle mr-1"></i> មិនទាន់មានប្រវត្តិចំណារនៅឡើយ
                </div>
              </div>
            </div>

          </div>

          <!-- Footer -->
          <div class="modal-footer bg-light justify-content-between">
            <button type="button" class="btn btn-secondary" @click="closeViewModal">
              <i class="fas fa-times mr-1"></i> បិទ
            </button>
            <button v-if="activeTab === 'pending_review' || (canReviewRequest(selectedViewRequest) && selectedViewRequest.status === 'PENDING')" 
                    type="button" class="btn btn-warning font-weight-bold" 
                    @click="closeViewModal(); openReviewModal(selectedViewRequest)">
              <i class="fas fa-signature mr-1"></i> ពិនិត្យ & ធ្វើចំណារលើសំណើនេះ
            </button>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useUserStore } from '@/stores/user';
import emptyAvatar from '@/assets/images/emptyImage.png';
import {
  apiGetLeaveRequests,
  apiGetLeaveStats,
  apiGetNextApproverCandidates,
  apiGetLeaveRequest,
  apiCreateLeaveRequest,
  apiUpdateLeaveRequest,
  apiActionLeaveRequest,
  apiCancelLeaveRequest,
  apiDeleteLeaveRequest,
  getLeaveAttachmentDownloadUrl
} from '@/functions/api/leaveRequest';
import Swal from 'sweetalert2';

const userStore = useUserStore();

// Reactive State
const activeTab = ref('my');
const loading = ref(false);
const requests = ref([]);
const stats = reactive({
  my_stats: { total: 0, draft: 0, pending: 0, approved: 0, rejected: 0, annual_days_used: 0 },
  review_stats: { pending: 0, forwarded: 0, approved: 0, rejected: 0 },
  all_stats: { total: 0, pending: 0, approved: 0, rejected: 0 },
  pending_reviews_count: 0,
  can_review: false,
  is_dg: false,
  can_final_approve: false,
});

const isDirectorGeneral = computed(() => {
  return stats.is_dg || userStore.position?.level === 1 || (userStore.isAdmin && !userStore.position_id);
});

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 15,
});

// Filters
const filterSearch = ref('');
const filterType = ref('');
const filterStatus = ref('');

// Form State (Create / Edit)
const isEditing = ref(false);
const currentEditingId = ref(null);
const submitNowSelected = ref(false);
const selectedFile = ref(null);
const useNextTier = ref(false);

const form = reactive({
  leave_type: 'ANNUAL',
  start_date: '',
  end_date: '',
  resume_date: '',
  is_half_day: false,
  half_day_type: 'FULL_DAY',
  duration_days: 1.0,
  reason: '',
  contact_phone: '',
  next_approver_id: '',
});

const approverCandidates = reactive({
  candidates: [],
  next_tier_candidates: [],
  can_skip_to_next_tier: false,
});

// Review State
const currentSelectedRequest = ref(null);
const submittingAction = ref(false);
const useReviewNextTier = ref(false);
const reviewActionForm = reactive({
  action: 'FORWARD',
  remarks: '',
  next_approver_id: '',
  rejection_reason: '',
});
const reviewCandidates = reactive({
  candidates: [],
  next_tier_candidates: [],
  can_skip_to_next_tier: false,
});

// View Details State
const showViewModal = ref(false);
const selectedViewRequest = ref(null);

// Modal Visibility State
const showCreateModal = ref(false);
const showReviewModal = ref(false);

function closeCreateModal() {
  showCreateModal.value = false;
}
function closeReviewModal() {
  showReviewModal.value = false;
  currentSelectedRequest.value = null;
}
function closeViewModal() {
  showViewModal.value = false;
  selectedViewRequest.value = null;
}

// Lifecycle
onMounted(async () => {
  initDefaultDates();
  await loadStats();
  await loadRequests();
});

// Methods
function initDefaultDates() {
  const today = new Date();
  const todayStr = today.toISOString().split('T')[0];
  form.start_date = todayStr;
  form.end_date = todayStr;
  calculateResumeDate();
}

async function loadStats() {
  try {
    const res = await apiGetLeaveStats();
    if (res.data && res.data.data) {
      Object.assign(stats, res.data.data);
    }
  } catch (err) {
    console.error("Failed to load stats:", err);
  }
}

async function loadRequests(page = 1) {
  loading.value = true;
  try {
    const params = {
      scope: activeTab.value,
      page: page,
      search: filterSearch.value,
      leave_type: filterType.value,
      status: filterStatus.value,
    };
    const res = await apiGetLeaveRequests(params);
    if (res.data && res.data.status === 'success') {
      requests.value = res.data.data;
      pagination.current_page = res.data.current_page;
      pagination.last_page = res.data.last_page;
      pagination.total = res.data.total;
      pagination.per_page = res.data.per_page;
    }
  } catch (err) {
    console.error("Failed to load requests:", err);
  } finally {
    loading.value = false;
  }
}

function switchTab(tab) {
  activeTab.value = tab;
  pagination.current_page = 1;
  loadRequests(1);
}

function changePage(page) {
  if (page >= 1 && page <= pagination.last_page) {
    loadRequests(page);
  }
}

function resetFilters() {
  filterSearch.value = '';
  filterType.value = '';
  filterStatus.value = '';
  loadRequests(1);
}

// Date and Duration Calculation
function onDatesChanged() {
  if (form.is_half_day) {
    form.end_date = form.start_date;
    form.duration_days = 0.5;
    if (form.half_day_type === 'FULL_DAY') {
      form.half_day_type = 'MORNING';
    }
  } else {
    form.half_day_type = 'FULL_DAY';
    if (form.start_date && form.end_date) {
      const start = new Date(form.start_date);
      const end = new Date(form.end_date);
      if (end < start) {
        form.end_date = form.start_date;
      }
      form.duration_days = calculateWorkingDays(form.start_date, form.end_date, form.leave_type === 'MATERNITY');
    }
  }
  calculateResumeDate();
  fetchApproverCandidates();
}

function calculateWorkingDays(startStr, endStr, countAllDays = false) {
  const start = new Date(startStr);
  const end = new Date(endStr);
  if (isNaN(start) || isNaN(end) || end < start) return 1;

  if (countAllDays) {
    const diffTime = Math.abs(end - start);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
  }

  let count = 0;
  const cur = new Date(start);
  while (cur <= end) {
    const day = cur.getDay();
    if (day !== 0 && day !== 6) { // Skip Saturday (6) and Sunday (0)
      count++;
    }
    cur.setDate(cur.getDate() + 1);
  }
  return count > 0 ? count : 1;
}

function calculateResumeDate() {
  if (!form.end_date) return;
  const end = new Date(form.end_date);
  if (isNaN(end)) return;

  const next = new Date(end);
  next.setDate(next.getDate() + 1);

  // If next day is weekend, jump to Monday
  while (next.getDay() === 0 || next.getDay() === 6) {
    next.setDate(next.getDate() + 1);
  }
  form.resume_date = next.toISOString().split('T')[0];
}

async function fetchApproverCandidates() {
  try {
    const params = {
      user_id: userStore.id,
      start_date: form.start_date,
      end_date: form.end_date || form.start_date,
    };
    const res = await apiGetNextApproverCandidates(params);
    if (res.data && res.data.data) {
      approverCandidates.candidates = res.data.data.candidates;
      approverCandidates.next_tier_candidates = res.data.data.next_tier_candidates;
      approverCandidates.can_skip_to_next_tier = res.data.data.can_skip_to_next_tier;
      
      // Auto-select first candidate if not selected
      if (!form.next_approver_id && approverCandidates.candidates.length > 0) {
        form.next_approver_id = approverCandidates.candidates[0].id;
      }
    }
  } catch (err) {
    console.error("Failed to load candidates:", err);
  }
}

const selectedApproverOnLeave = computed(() => {
  const list = useNextTier.value ? approverCandidates.next_tier_candidates : approverCandidates.candidates;
  const found = list.find(c => c.id === form.next_approver_id);
  return found ? found.is_on_leave : false;
});

function onFileSelected(e) {
  const files = e.target.files;
  if (files && files.length > 0) {
    selectedFile.value = files[0];
  }
}

// Modal open handlers
function openCreateModal() {
  isEditing.value = false;
  currentEditingId.value = null;
  selectedFile.value = null;
  useNextTier.value = false;
  initDefaultDates();
  form.leave_type = 'ANNUAL';
  form.is_half_day = false;
  form.half_day_type = 'FULL_DAY';
  form.reason = '';
  form.contact_phone = userStore.phone || '';
  form.next_approver_id = '';
  onDatesChanged();
  showCreateModal.value = true;
}

function openEditModal(req) {
  isEditing.value = true;
  currentEditingId.value = req.id;
  selectedFile.value = null;
  useNextTier.value = false;
  form.leave_type = req.leave_type;
  form.start_date = req.start_date;
  form.end_date = req.end_date;
  form.resume_date = req.resume_date;
  form.is_half_day = !!req.is_half_day;
  form.half_day_type = req.half_day_type || 'FULL_DAY';
  form.duration_days = req.duration_days;
  form.reason = req.reason;
  form.contact_phone = req.contact_phone || '';
  form.next_approver_id = req.current_approver_id || '';
  fetchApproverCandidates();
  showCreateModal.value = true;
}

async function submitForm() {
  try {
    const formData = new FormData();
    formData.append('leave_type', form.leave_type);
    formData.append('start_date', form.start_date);
    formData.append('end_date', form.end_date);
    formData.append('resume_date', form.resume_date);
    formData.append('is_half_day', form.is_half_day ? '1' : '0');
    formData.append('half_day_type', form.half_day_type);
    formData.append('duration_days', form.duration_days);
    formData.append('reason', form.reason);
    formData.append('contact_phone', form.contact_phone || '');
    formData.append('submit_now', submitNowSelected.value ? '1' : '0');

    if (form.next_approver_id) {
      formData.append('next_approver_id', form.next_approver_id);
    }
    if (selectedFile.value) {
      formData.append('attachment', selectedFile.value);
    }

    let res;
    if (isEditing.value && currentEditingId.value) {
      res = await apiUpdateLeaveRequest(currentEditingId.value, formData);
    } else {
      res = await apiCreateLeaveRequest(formData);
    }

    if (res.data && res.data.status === 'success') {
      showCreateModal.value = false;
      Swal.fire({
        icon: 'success',
        title: 'ជោគជ័យ',
        text: res.data.message || 'បានរក្សាទុកពាក្យសុំច្បាប់ដោយជោគជ័យ',
        timer: 2000,
        showConfirmButton: false,
      });
      await loadStats();
      await loadRequests(pagination.current_page);
    }
  } catch (err) {
    const msg = err.response?.data?.message || 'មានបញ្ហាក្នុងការរក្សាទុកទិន្នន័យ!';
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: msg });
  }
}

// Review & Action Modal
async function openReviewModal(req) {
  try {
    const res = await apiGetLeaveRequest(req.id);
    currentSelectedRequest.value = res.data.data;
    reviewActionForm.action = isDirectorGeneral.value ? 'APPROVE' : 'FORWARD';
    reviewActionForm.remarks = '';
    reviewActionForm.rejection_reason = '';
    reviewActionForm.next_approver_id = '';
    useReviewNextTier.value = false;

    // Load next stage candidates
    const candRes = await apiGetNextApproverCandidates({
      request_id: req.id,
      start_date: req.start_date,
      end_date: req.end_date,
    });
    if (candRes.data && candRes.data.data) {
      reviewCandidates.candidates = candRes.data.data.candidates;
      reviewCandidates.next_tier_candidates = candRes.data.data.next_tier_candidates;
      reviewCandidates.can_skip_to_next_tier = candRes.data.data.can_skip_to_next_tier;
      if (reviewCandidates.candidates.length > 0) {
        reviewActionForm.next_approver_id = reviewCandidates.candidates[0].id;
      }
    }

    showReviewModal.value = true;
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'កំហុស', text: 'មិនអាចទាញយកព័ត៌មានសំណើបានឡើយ!' });
  }
}

function setAction(act) {
  reviewActionForm.action = act;
}

async function submitReviewAction() {
  if (reviewActionForm.action === 'FORWARD' && !reviewActionForm.next_approver_id) {
    Swal.fire({ icon: 'warning', title: 'សូមជ្រើសរើស', text: 'សូមជ្រើសរើសថ្នាក់ដឹកនាំដែលត្រូវបញ្ជូនបន្តទៅ!' });
    return;
  }
  if (reviewActionForm.action === 'REJECT' && !reviewActionForm.rejection_reason) {
    Swal.fire({ icon: 'warning', title: 'សូមបញ្ចូល', text: 'សូមបញ្ជាក់មូលហេតុនៃការបដិសេធ!' });
    return;
  }

  submittingAction.value = true;
  try {
    const payload = {
      action: reviewActionForm.action,
      remarks: reviewActionForm.remarks,
      next_approver_id: reviewActionForm.next_approver_id,
      rejection_reason: reviewActionForm.rejection_reason,
    };
    const res = await apiActionLeaveRequest(currentSelectedRequest.value.id, payload);
    if (res.data && res.data.status === 'success') {
      showReviewModal.value = false;
      Swal.fire({
        icon: 'success',
        title: 'ជោគជ័យ',
        text: res.data.message || 'បានអនុវត្តសកម្មភាពដោយជោគជ័យ',
        timer: 2000,
        showConfirmButton: false,
      });
      await loadStats();
      await loadRequests(pagination.current_page);
    }
  } catch (err) {
    const msg = err.response?.data?.message || 'មានបញ្ហាក្នុងការអនុវត្តសកម្មភាព!';
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: msg });
  } finally {
    submittingAction.value = false;
  }
}

// Cancel and Delete Handlers
async function handleCancel(req) {
  const result = await Swal.fire({
    title: 'តើលោកអ្នកចង់បោះបង់ពាក្យសុំនេះមែនទេ?',
    text: 'ពាក្យសុំច្បាប់នេះនឹងត្រូវបោះបង់ចោលភ្លាមៗ។',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'បាទ/ចាស, បោះបង់',
    cancelButtonText: 'ត្រឡប់ក្រោយ',
  });

  if (result.isConfirmed) {
    try {
      const res = await apiCancelLeaveRequest(req.id);
      if (res.data && res.data.status === 'success') {
        Swal.fire({ icon: 'success', title: 'បានបោះបង់', text: res.data.message, timer: 1500, showConfirmButton: false });
        await loadStats();
        await loadRequests(pagination.current_page);
      }
    } catch (err) {
      Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: err.response?.data?.message || 'មិនអាចបោះបង់បានឡើយ!' });
    }
  }
}

async function handleDelete(req) {
  const result = await Swal.fire({
    title: 'តើលោកអ្នកប្រាកដជាចង់លុបមែនទេ?',
    text: 'សេចក្តីព្រាងពាក្យសុំនេះនឹងត្រូវលុបចេញពីប្រព័ន្ធ។',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'បាទ/ចាស, លុប',
    cancelButtonText: 'បោះបង់',
  });

  if (result.isConfirmed) {
    try {
      const res = await apiDeleteLeaveRequest(req.id);
      if (res.data && res.data.status === 'success') {
        Swal.fire({ icon: 'success', title: 'បានលុប', text: res.data.message, timer: 1500, showConfirmButton: false });
        await loadStats();
        await loadRequests(pagination.current_page);
      }
    } catch (err) {
      Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: err.response?.data?.message || 'មិនអាចលុបបានឡើយ!' });
    }
  }
}

// View Modal
async function openViewModal(req) {
  try {
    const res = await apiGetLeaveRequest(req.id);
    selectedViewRequest.value = res.data.data;
    showViewModal.value = true;
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'កំហុស', text: 'មិនអាចបើកមើលព័ត៌មានលម្អិតនៃសំណើបានឡើយ!' });
  }
}

function canReviewRequest(req) {
  if (userStore.isAdmin) return true;
  return req.current_approver_id === userStore.id;
}

// Helpers
function formatDate(dateStr) {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  if (isNaN(d)) return dateStr;
  return d.toLocaleDateString('en-GB'); // DD/MM/YYYY
}

function formatDateTime(dtStr) {
  if (!dtStr) return '-';
  const d = new Date(dtStr);
  if (isNaN(d)) return dtStr;
  return `${d.toLocaleDateString('en-GB')} ${d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
}

function formatKhmerDay(dtStr) {
  const d = dtStr ? new Date(dtStr) : new Date();
  return d.getDate();
}

function formatKhmerMonth(dtStr) {
  const d = dtStr ? new Date(dtStr) : new Date();
  const months = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'];
  return months[d.getMonth()];
}

function formatKhmerYear(dtStr) {
  const d = dtStr ? new Date(dtStr) : new Date();
  return d.getFullYear();
}

function getAttachmentUrl(id) {
  return getLeaveAttachmentDownloadUrl(id);
}

function getLeaveTypeBadgeClass(type) {
  switch (type) {
    case 'ANNUAL': return 'badge-primary';
    case 'SHORT_TERM': return 'badge-info';
    case 'MATERNITY': return 'badge-danger';
    case 'SICK': return 'badge-warning text-dark';
    case 'PERSONAL': return 'badge-secondary';
    default: return 'badge-light border';
  }
}

function getLeaveTypeIcon(type) {
  switch (type) {
    case 'ANNUAL': return 'fas fa-calendar-check';
    case 'SHORT_TERM': return 'fas fa-hourglass-half';
    case 'MATERNITY': return 'fas fa-baby';
    case 'SICK': return 'fas fa-heartbeat';
    case 'PERSONAL': return 'fas fa-user-clock';
    default: return 'fas fa-calendar';
  }
}

function getStatusBadgeClass(status) {
  switch (status) {
    case 'DRAFT': return 'badge-secondary';
    case 'PENDING': return 'badge-warning text-dark';
    case 'APPROVED': return 'badge-success';
    case 'REJECTED': return 'badge-danger';
    case 'CANCELLED': return 'badge-dark';
    default: return 'badge-light border';
  }
}

function getTimelineIconClass(action) {
  switch (action) {
    case 'SUBMITTED': return 'fa-paper-plane bg-primary';
    case 'FORWARDED': return 'fa-share bg-info';
    case 'APPROVED': return 'fa-check-double bg-success';
    case 'REJECTED': return 'fa-ban bg-danger';
    case 'RETURNED': return 'fa-undo bg-warning';
    case 'CANCELLED': return 'fa-times bg-secondary';
    default: return 'fa-circle bg-gray';
  }
}

function getApprovalActionBadge(action) {
  switch (action) {
    case 'SUBMITTED': return 'badge-primary';
    case 'FORWARDED': return 'badge-info';
    case 'APPROVED': return 'badge-success';
    case 'REJECTED': return 'badge-danger';
    case 'RETURNED': return 'badge-warning text-dark';
    case 'CANCELLED': return 'badge-secondary';
    default: return 'badge-light border';
  }
}

function getApprovalRemarkForStage(req, stage) {
  if (!req || !req.approvals) return null;
  // Look for approval from approvers in that stage
  const matching = req.approvals.filter(a => {
    const lvl = a.user?.position?.level || a.position?.level || 99;
    if (stage === 'OFFICE') return lvl >= 6 && lvl <= 8;
    if (stage === 'DEPARTMENT') return lvl >= 3 && lvl <= 5;
    if (stage === 'LEADERSHIP') return lvl <= 2;
    return false;
  });
  if (matching.length > 0) {
    const last = matching[matching.length - 1];
    return `«${last.remarks || last.action_kh}» \n(ដោយ៖ ${last.user?.name_kh || last.user?.name} - ${formatDate(last.created_at)})`;
  }
  return null;
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Kantumruy+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Moul&display=swap');

/* Apply Khmer fonts exactly like User.vue */
.khmer-layout,
.custom-table,
.modal-content,
.font-khmer {
  font-family: 'Kantumruy Pro', 'Battambang', -apple-system, BlinkMacSystemFont, sans-serif !important;
  font-size: 0.94rem;
  -webkit-font-smoothing: antialiased;
}

.khmer-page-title {
  font-family: 'Battambang', cursive, sans-serif !important;
  font-size: 1.45rem;
}

.font-muol {
  font-family: 'Khmer OS Muol Light', 'Khmer OS Moul Light', 'Moul', cursive, serif !important;
}

.font-monospace {
  font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
}

.font-weight-500 {
  font-weight: 500;
}

/* Primary Top Button */
.btn-dark-custom {
  background-color: #0c2b29;
  color: #ffffff;
  border-radius: 6px;
  font-weight: 500;
  border: none;
  padding: 7px 16px;
  transition: all 0.2s ease;
}

.btn-dark-custom:hover {
  background-color: #15423f;
  color: #ffffff;
}

.bg-dark-custom {
  background-color: #0c2b29 !important;
}

.bg-warning-custom {
  background-color: #fef3c7 !important;
  border-bottom: 1px solid #fde68a !important;
}

/* Nav Pills Styling */
.nav-pills .nav-link {
  color: #4b5563;
  font-weight: 500;
  border-radius: 6px;
  padding: 8px 16px;
  transition: all 0.2s ease;
}

.nav-pills .nav-link.active {
  background-color: #0c2b29;
  color: #ffffff;
}

/* Stat Cards */
.stat-card {
  transition: all 0.25s ease;
  border: 1px solid #e2e8f0;
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
}
.active-stat-card {
  border: 2px solid #0c2b29 !important;
  box-shadow: 0 4px 14px rgba(0,0,0,0.12) !important;
}
.stat-icon-circle {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}
.bg-primary-light { background-color: #e0e7ff; }
.bg-success-light { background-color: #dcfce7; }
.bg-warning-light { background-color: #fef3c7; }
.bg-danger-light { background-color: #fee2e2; }
.bg-secondary-light { background-color: #f1f5f9; }
.bg-info-light { background-color: #e0f2fe; }
.cursor-pointer { cursor: pointer; }

/* Custom Table */
.custom-table th {
  font-weight: 600;
  color: #374151;
  background-color: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  vertical-align: middle;
  padding: 12px 14px;
}

.custom-table td {
  vertical-align: middle;
  padding: 12px 14px;
  border-top: 1px solid #f1f5f9;
}

/* Action Buttons */
.action-buttons {
  display: inline-flex;
  gap: 6px;
  justify-content: center;
}

.btn-action {
  width: 32px;
  height: 32px;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  border: 1px solid transparent;
  transition: all 0.2s ease;
  font-size: 0.85rem;
}

.btn-view {
  color: #0284c7;
  background-color: #f0f9ff;
  border-color: #bae6fd;
}
.btn-view:hover {
  background-color: #0284c7;
  color: #ffffff;
}

.btn-print {
  color: #0c4a6e;
  background-color: #f1f5f9;
  border-color: #cbd5e1;
}
.btn-print:hover {
  background-color: #0c4a6e;
  color: #ffffff;
}

.btn-review {
  color: #d97706;
  background-color: #fffbeb;
  border-color: #fde68a;
}
.btn-review:hover {
  background-color: #d97706;
  color: #ffffff;
}

.btn-edit {
  color: #2563eb;
  background-color: #eff6ff;
  border-color: #bfdbfe;
}
.btn-edit:hover {
  background-color: #2563eb;
  color: #ffffff;
}

.btn-cancel {
  color: #ea580c;
  background-color: #fff7ed;
  border-color: #ffedd5;
}
.btn-cancel:hover {
  background-color: #ea580c;
  color: #ffffff;
}

.btn-delete {
  color: #dc2626;
  background-color: #fef2f2;
  border-color: #fecaca;
}
.btn-delete:hover {
  background-color: #dc2626;
  color: #ffffff;
}

/* Badges */
.badge {
  font-size: 0.82rem;
  font-weight: 500;
  padding: 0.35em 0.7em;
  border-radius: 4px;
}

/* Modals */
.custom-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  overflow-y: auto;
  padding: 1.5rem 1rem;
}

.modal-dialog {
  margin: auto;
  max-height: 90vh;
}

.modal-content {
  border-radius: 8px;
  max-height: 88vh;
  overflow-y: auto;
}

.modal-header {
  border-top-left-radius: calc(0.3rem - 1px);
  border-top-right-radius: calc(0.3rem - 1px);
}

.form-label {
  font-size: 0.9rem;
  margin-bottom: 0.35rem;
}

/* Bulletproof FontAwesome rendering: ALWAYS ensure correct font-family and weight */
.fas, :deep(.fas), .fa, :deep(.fa) {
  font-family: "Font Awesome 5 Free" !important;
  font-weight: 900 !important;
  font-style: normal !important;
}
.far, :deep(.far) {
  font-family: "Font Awesome 5 Free" !important;
  font-weight: 400 !important;
  font-style: normal !important;
}
.fab, :deep(.fab) {
  font-family: "Font Awesome 5 Brands" !important;
  font-weight: 400 !important;
  font-style: normal !important;
}

/* Print Styles */
@media print {
  body * {
    visibility: hidden;
  }
  #officialPrintArea, #officialPrintArea * {
    visibility: visible;
  }
  #officialPrintArea {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    margin: 0;
    padding: 20px !important;
  }
  .modal-footer, .modal-header, .btn, .close {
    display: none !important;
  }
}
</style>
