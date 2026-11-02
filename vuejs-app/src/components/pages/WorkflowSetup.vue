<template>
  <div class="content-wrapper workflow-setup-page">
    <!-- Content Header -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-7">
            <h1 class="m-0 font-weight-bold text-dark khmer-font">
              <i class="fas fa-project-diagram text-primary mr-2"></i>កំណត់លំហូរឯកសារ (Document Workflow Setup)
            </h1>
            <p class="text-muted text-sm mb-0 mt-1">
              កំណត់ <strong class="text-primary">View Type Group</strong> (សិទ្ធិមើលឯកសារ) និង <strong class="text-success">Sending Type Group</strong> (សិទ្ធិផ្ញើ/ឆ្លងឯកសារ) សម្រាប់មន្ត្រីម្នាក់ៗ
            </p>
          </div>
          <div class="col-sm-5 text-right">
            <button
              v-if="selectedUserIds.length > 0"
              class="btn btn-warning shadow-sm font-weight-bold px-3 py-2 mr-2"
              @click="openBatchModal"
            >
              <i class="fas fa-layer-group mr-1"></i> កំណត់រួមគ្នា ({{ selectedUserIds.length }} នាក់)
            </button>
            <button class="btn btn-outline-secondary shadow-sm px-3 py-2" @click="fetchUsers">
              <i class="fas fa-sync-alt mr-1" :class="{ 'fa-spin': loading }"></i> ផ្ទុកឡើងវិញ
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Summary Stats Cards -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-white border shadow-sm rounded-lg p-3 mb-3">
              <div class="inner">
                <h3 class="font-weight-bold text-dark mb-1">{{ stats.total_users || 0 }}</h3>
                <p class="text-muted mb-0 font-weight-500">មន្ត្រីសកម្មសរុប</p>
              </div>
              <div class="icon text-primary opacity-25">
                <i class="fas fa-users"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-white border shadow-sm rounded-lg p-3 mb-3">
              <div class="inner">
                <h3 class="font-weight-bold text-success mb-1">{{ stats.configured_users || 0 }}</h3>
                <p class="text-muted mb-0 font-weight-500">បានកំណត់ Workflow រួច</p>
              </div>
              <div class="icon text-success opacity-25">
                <i class="fas fa-user-check"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-white border shadow-sm rounded-lg p-3 mb-3">
              <div class="inner">
                <h3 class="font-weight-bold text-info mb-1">{{ stats.view_custom || 0 }}</h3>
                <p class="text-muted mb-0 font-weight-500">View Type: កំណត់ដោយដៃ (Custom)</p>
              </div>
              <div class="icon text-info opacity-25">
                <i class="fas fa-eye"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-white border shadow-sm rounded-lg p-3 mb-3">
              <div class="inner">
                <h3 class="font-weight-bold text-indigo mb-1">{{ stats.send_custom || 0 }}</h3>
                <p class="text-muted mb-0 font-weight-500">Sending Type: កំណត់ដោយដៃ (Custom)</p>
              </div>
              <div class="icon text-indigo opacity-25">
                <i class="fas fa-paper-plane"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter Card -->
        <div class="card card-outline card-primary shadow-sm mb-3">
          <div class="card-body p-3">
            <div class="row align-items-center">
              <!-- Search Name/Email/Position -->
              <div class="col-md-3 mb-2 mb-md-0">
                <div class="input-group input-group-sm">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-right-0">
                      <i class="fas fa-search text-muted"></i>
                    </span>
                  </div>
                  <input
                    type="text"
                    v-model="filters.search"
                    @keyup.enter="applyFilters"
                    class="form-control form-control-sm border-left-0"
                    placeholder="ស្វែងរកឈ្មោះ, មុខតំណែង, Email..."
                  />
                </div>
              </div>

              <!-- Department Filter -->
              <div class="col-md-2 mb-2 mb-md-0">
                <select
                  v-model="filters.department_id"
                  @change="onDepartmentFilterChange"
                  class="form-control form-control-sm custom-select"
                >
                  <option value="">-- គ្រប់នាយកដ្ឋាន --</option>
                  <option v-for="dept in options.departments" :key="dept.id" :value="dept.id">
                    {{ dept.name_kh || dept.name }}
                  </option>
                </select>
              </div>

              <!-- Office Filter -->
              <div class="col-md-2 mb-2 mb-md-0">
                <select
                  v-model="filters.office_id"
                  @change="applyFilters"
                  class="form-control form-control-sm custom-select"
                >
                  <option value="">-- គ្រប់ការិយាល័យ --</option>
                  <option v-for="off in filteredOffices" :key="off.id" :value="off.id">
                    {{ off.name_kh || off.name }}
                  </option>
                </select>
              </div>

              <!-- View Type Filter -->
              <div class="col-md-2 mb-2 mb-md-0">
                <select
                  v-model="filters.view_type"
                  @change="applyFilters"
                  class="form-control form-control-sm custom-select"
                >
                  <option value="">-- សិទ្ធិមើល (View Group) --</option>
                  <option value="ALL">ទាំងអស់ (ALL)</option>
                  <option value="DEPARTMENT">ក្នុងនាយកដ្ឋាន</option>
                  <option value="OFFICE">ក្នុងការិយាល័យ</option>
                  <option value="SUBORDINATES">មន្ត្រីក្រោមឱវាទ</option>
                  <option value="SELF_ONLY">ផ្ទាល់ខ្លួន (Self Only)</option>
                  <option value="CUSTOM">កំណត់ដោយដៃ (Custom)</option>
                  <option value="UNSET">មិនទាន់កំណត់ (Default)</option>
                </select>
              </div>

              <!-- Send Type Filter -->
              <div class="col-md-2 mb-2 mb-md-0">
                <select
                  v-model="filters.send_type"
                  @change="applyFilters"
                  class="form-control form-control-sm custom-select"
                >
                  <option value="">-- សិទ្ធិផ្ញើ (Sending Group) --</option>
                  <option value="ALL">ទាំងអស់ (ALL)</option>
                  <option value="DEPARTMENT">ក្នុងនាយកដ្ឋាន</option>
                  <option value="OFFICE">ក្នុងការិយាល័យ</option>
                  <option value="SUBORDINATES">មន្ត្រីក្រោមឱវាទ</option>
                  <option value="CUSTOM">កំណត់ដោយដៃ (Custom)</option>
                  <option value="UNSET">មិនទាន់កំណត់ (Default)</option>
                </select>
              </div>

              <!-- Action buttons -->
              <div class="col-md-1 text-right">
                <button
                  type="button"
                  class="btn btn-sm btn-outline-secondary"
                  @click="resetFilters"
                  title="ជម្រះ Filter"
                >
                  <i class="fas fa-undo"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Users Workflow Table Card -->
        <div class="card shadow-sm border-0">
          <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <span class="font-weight-bold text-dark mr-3">
                <i class="fas fa-users-cog text-secondary mr-1"></i> បញ្ជីមន្ត្រី និងការកំណត់ Workflow ({{ pagination.total }} នាក់)
              </span>
              <span v-if="selectedUserIds.length > 0" class="badge badge-warning py-1 px-2 font-weight-bold">
                បានជ្រើសរើស {{ selectedUserIds.length }} នាក់
              </span>
            </div>
            <div>
              <div class="d-inline-flex align-items-center text-sm text-muted">
                <span>បង្ហាញក្នុងមួយទំព័រ:</span>
                <select v-model="pagination.per_page" @change="fetchUsers(1)" class="form-control form-control-sm ml-2" style="width: 75px;">
                  <option :value="15">15</option>
                  <option :value="25">25</option>
                  <option :value="50">50</option>
                  <option :value="100">100</option>
                </select>
              </div>
            </div>
          </div>

          <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead class="bg-light text-secondary text-xs font-weight-bold text-uppercase">
                <tr>
                  <th style="width: 40px;" class="text-center">
                    <input
                      type="checkbox"
                      :checked="isAllSelected"
                      @change="toggleSelectAll"
                      title="ជ្រើសរើសទាំងអស់លើទំព័រនេះ"
                    />
                  </th>
                  <th>មន្ត្រី / មុខតំណែង (តាមលំដាប់ Level)</th>
                  <th>នាយកដ្ឋាន & ការិយាល័យ</th>
                  <th>
                    <span class="text-primary font-weight-bold">
                      <i class="fas fa-eye mr-1"></i> ១. View Type Group (សិទ្ធិមើល)
                    </span>
                  </th>
                  <th>
                    <span class="text-success font-weight-bold">
                      <i class="fas fa-paper-plane mr-1"></i> ២. Sending Type Group (សិទ្ធិផ្ញើ/ឆ្លង)
                    </span>
                  </th>
                  <th>កំណត់សម្គាល់</th>
                  <th style="width: 130px;" class="text-center">សកម្មភាព</th>
                </tr>
              </thead>
              <tbody>
                <!-- Loading State -->
                <tr v-if="loading">
                  <td colspan="7" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                      <span class="sr-only">កំពុងផ្ទុក...</span>
                    </div>
                    <div class="text-muted mt-2 text-sm">កំពុងផ្ទុកទិន្នន័យ...</div>
                  </td>
                </tr>

                <!-- Empty State -->
                <tr v-else-if="users.length === 0">
                  <td colspan="7" class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
                    <p class="mb-0">មិនមានទិន្នន័យមន្ត្រីត្រូវនឹងលក្ខខណ្ឌស្វែងរកឡើយ</p>
                  </td>
                </tr>

                <!-- Data Rows -->
                <tr v-else v-for="user in users" :key="user.id">
                  <td class="text-center">
                    <input
                      type="checkbox"
                      :value="user.id"
                      v-model="selectedUserIds"
                    />
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <img
                        v-if="user.profile_image"
                        :src="user.profile_image"
                        class="rounded-circle mr-2 border"
                        style="width: 36px; height: 36px; object-fit: cover;"
                      />
                      <div
                        v-else
                        class="avatar-placeholder rounded-circle bg-light-primary text-primary font-weight-bold text-center mr-2 border"
                        style="width: 36px; height: 36px; line-height: 36px; font-size: 13px;"
                      >
                        {{ (user.name_kh || user.name || 'U').charAt(0) }}
                      </div>
                      <div>
                        <div class="font-weight-bold text-dark">
                          {{ user.name_kh || user.name }}
                          <span
                            v-if="user.position?.level"
                            class="badge ml-1 px-2 py-0"
                            :class="getPositionLevelBadgeClass(user.position.level)"
                            style="font-size: 11px;"
                          >
                            <i class="fas fa-crown text-warning mr-1" v-if="Number(user.position.level) === 1"></i>
                            Level {{ user.position.level }}
                          </span>
                        </div>
                        <div class="text-xs text-muted">
                          {{ user.position ? (user.position.title_kh || user.position.title_en || user.position.name_kh || user.position.name) : 'មន្ត្រី' }}
                          <span v-if="user.email" class="ml-1 text-secondary">({{ user.email }})</span>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="text-sm font-weight-500 text-dark">
                      {{ user.department ? (user.department.name_kh || user.department.name) : '-' }}
                    </div>
                    <div class="text-xs text-muted" v-if="user.office">
                      {{ user.office.name_kh || user.office.name }}
                    </div>
                  </td>

                  <!-- View Type Group Badge -->
                  <td>
                    <div v-if="user.workflow_config">
                      <span class="badge px-2 py-1" :class="getViewBadgeClass(user.workflow_config.view_type)">
                        <i class="fas mr-1" :class="getViewBadgeIcon(user.workflow_config.view_type)"></i>
                        {{ getViewTypeLabel(user.workflow_config.view_type) }}
                      </span>
                      <div
                        v-if="user.workflow_config.view_type === 'CUSTOM'"
                        class="text-xs text-muted mt-1"
                      >
                        <span v-if="user.workflow_config.view_dept_ids?.length" class="mr-1">
                          <i class="fas fa-building text-secondary"></i> {{ user.workflow_config.view_dept_ids.length }} នាយកដ្ឋាន
                        </span>
                        <span v-if="user.workflow_config.view_office_ids?.length" class="mr-1">
                          <i class="fas fa-door-open text-secondary"></i> {{ user.workflow_config.view_office_ids.length }} ការិយាល័យ
                        </span>
                        <span v-if="user.workflow_config.view_user_ids?.length" class="mr-1">
                          <i class="fas fa-user text-secondary"></i> {{ user.workflow_config.view_user_ids.length }} មន្ត្រី
                        </span>
                        <span v-if="user.workflow_config.view_working_group_ids?.length">
                          <i class="fas fa-users-cog text-secondary"></i> {{ user.workflow_config.view_working_group_ids.length }} ក្រុម
                        </span>
                      </div>
                    </div>
                    <div v-else>
                      <span class="badge badge-light text-muted border px-2 py-1">
                        <i class="fas fa-sitemap mr-1"></i> តាមឋានានុក្រមទូទៅ (Default)
                      </span>
                    </div>
                  </td>

                  <!-- Sending Type Group Badge -->
                  <td>
                    <div v-if="user.workflow_config">
                      <span class="badge px-2 py-1" :class="getSendBadgeClass(user.workflow_config.send_type)">
                        <i class="fas mr-1" :class="getSendBadgeIcon(user.workflow_config.send_type)"></i>
                        {{ getSendTypeLabel(user.workflow_config.send_type) }}
                      </span>
                      <div
                        v-if="user.workflow_config.send_type === 'CUSTOM'"
                        class="text-xs text-muted mt-1"
                      >
                        <span v-if="user.workflow_config.send_dept_ids?.length" class="mr-1">
                          <i class="fas fa-building text-secondary"></i> {{ user.workflow_config.send_dept_ids.length }} នាយកដ្ឋាន
                        </span>
                        <span v-if="user.workflow_config.send_office_ids?.length" class="mr-1">
                          <i class="fas fa-door-open text-secondary"></i> {{ user.workflow_config.send_office_ids.length }} ការិយាល័យ
                        </span>
                        <span v-if="user.workflow_config.send_user_ids?.length" class="mr-1">
                          <i class="fas fa-user text-secondary"></i> {{ user.workflow_config.send_user_ids.length }} មន្ត្រី
                        </span>
                        <span v-if="user.workflow_config.send_working_group_ids?.length">
                          <i class="fas fa-users-cog text-secondary"></i> {{ user.workflow_config.send_working_group_ids.length }} ក្រុម
                        </span>
                      </div>
                    </div>
                    <div v-else>
                      <span class="badge badge-light text-muted border px-2 py-1">
                        <i class="fas fa-sitemap mr-1"></i> តាមឋានានុក្រមទូទៅ (Default)
                      </span>
                    </div>
                  </td>

                  <!-- Notes -->
                  <td>
                    <span v-if="user.workflow_config?.notes" class="text-xs text-muted" :title="user.workflow_config.notes">
                      {{ truncate(user.workflow_config.notes, 30) }}
                    </span>
                    <span v-else class="text-xs text-muted">-</span>
                  </td>

                  <!-- Actions -->
                  <td class="text-center">
                    <div class="btn-group btn-group-sm">
                      <button
                        type="button"
                        class="btn btn-primary btn-sm px-2"
                        @click="openEditModal(user)"
                        title="កំណត់ Workflow"
                      >
                        <i class="fas fa-cog mr-1"></i> កំណត់
                      </button>
                      <button
                        v-if="user.workflow_config"
                        type="button"
                        class="btn btn-outline-danger btn-sm px-2"
                        @click="resetUserConfig(user)"
                        title="កំណត់លំនាំដើមឡើងវិញ"
                      >
                        <i class="fas fa-undo"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination Footer -->
          <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center" v-if="pagination.total > 0">
            <div class="text-sm text-muted">
              បង្ហាញពី <strong>{{ pagination.from || 0 }}</strong> ដល់ <strong>{{ pagination.to || 0 }}</strong> នៃចំនួនសរុប <strong>{{ pagination.total }}</strong> នាក់
            </div>
            <ul class="pagination pagination-sm m-0">
              <li class="page-item" :class="{ disabled: pagination.current_page <= 1 }">
                <a class="page-link" href="javascript:void(0)" @click="fetchUsers(pagination.current_page - 1)">
                  <i class="fas fa-chevron-left"></i>
                </a>
              </li>
              <li
                v-for="p in visiblePages"
                :key="p"
                class="page-item"
                :class="{ active: p === pagination.current_page, disabled: p === '...' }"
              >
                <a class="page-link" href="javascript:void(0)" @click="p !== '...' && fetchUsers(p)">{{ p }}</a>
              </li>
              <li class="page-item" :class="{ disabled: pagination.current_page >= pagination.last_page }">
                <a class="page-link" href="javascript:void(0)" @click="fetchUsers(pagination.current_page + 1)">
                  <i class="fas fa-chevron-right"></i>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== MODAL: EDIT SINGLE USER WORKFLOW ==================== -->
    <div
      class="modal fade"
      :class="{ show: showEditModal, 'd-block': showEditModal }"
      tabindex="-1"
      role="dialog"
      v-if="showEditModal"
      style="background: rgba(0,0,0,0.55); z-index: 1050; overflow-y: auto;"
    >
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content shadow-lg border-0 rounded-lg">
          <!-- Header -->
          <div class="modal-header bg-primary text-white py-3">
            <h5 class="modal-title font-weight-bold">
              <i class="fas fa-project-diagram mr-2"></i> កំណត់ Workflow ឯកសារសម្រាប់មន្ត្រី
            </h5>
            <button type="button" class="close text-white" @click="closeEditModal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <!-- Body -->
          <div class="modal-body p-4">
            <!-- User Summary Box -->
            <div class="card bg-light border p-3 mb-4 rounded-lg">
              <div class="d-flex align-items-center">
                <img
                  v-if="editingUser?.profile_image"
                  :src="editingUser.profile_image"
                  class="rounded-circle mr-3 border"
                  style="width: 50px; height: 50px; object-fit: cover;"
                />
                <div
                  v-else
                  class="avatar-placeholder rounded-circle bg-primary text-white font-weight-bold text-center mr-3"
                  style="width: 50px; height: 50px; line-height: 50px; font-size: 18px;"
                >
                  {{ (editingUser?.name_kh || editingUser?.name || 'U').charAt(0) }}
                </div>
                <div>
                  <h5 class="font-weight-bold text-dark mb-0">
                    {{ editingUser?.name_kh || editingUser?.name }}
                    <span class="badge badge-primary text-xs ml-2">Level {{ editingUser?.position?.level || '-' }}</span>
                  </h5>
                  <div class="text-muted text-sm mt-1">
                    <span><i class="fas fa-id-badge mr-1"></i> {{ editingUser?.position?.title_kh || editingUser?.position?.title_en || editingUser?.position?.name_kh || 'មន្ត្រី' }}</span>
                    <span class="mx-2">•</span>
                    <span><i class="fas fa-building mr-1"></i> {{ editingUser?.department?.name_kh || editingUser?.department?.name || 'គ្មាននាយកដ្ឋាន' }}</span>
                    <span v-if="editingUser?.office" class="mx-2">•</span>
                    <span v-if="editingUser?.office"><i class="fas fa-door-open mr-1"></i> {{ editingUser.office.name_kh || editingUser.office.name }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Two Main Sections -->
            <div class="row">
              <!-- ================= SECTION 1: VIEW TYPE GROUP ================= -->
              <div class="col-lg-6 mb-4 mb-lg-0 border-right">
                <div class="d-flex align-items-center mb-3">
                  <span class="badge badge-primary p-2 mr-2">
                    <i class="fas fa-eye fa-lg"></i>
                  </span>
                  <div>
                    <h5 class="font-weight-bold text-primary mb-0">១. View Type Group (សិទ្ធិមើលឯកសារ)</h5>
                    <small class="text-muted">កំណត់ឯកសារដែលមន្ត្រីរូបនេះអាចមើលឃើញក្នុងប្រព័ន្ធ</small>
                  </div>
                </div>

                <div v-if="Number(editingUser?.position?.level) === 1" class="alert alert-warning py-2 text-xs mb-3">
                  <i class="fas fa-crown text-warning mr-1"></i> <strong>ចំណាំ៖</strong> ឯកឧត្តមអគ្គនាយកមានសិទ្ធិមើលឃើញគ្រប់ឯកសារទាំងអស់ក្នុងប្រព័ន្ធដោយស្វ័យប្រវត្តិតាមលក្ខន្តិកៈច្បាប់។
                </div>

                <!-- View Type Radio Options -->
                <div class="view-type-options mb-3">
                  <div
                    v-for="vt in viewTypeOptions"
                    :key="vt.value"
                    class="card mb-2 option-card cursor-pointer"
                    :class="{ 'border-primary bg-light-primary-soft': editForm.view_type === vt.value }"
                    @click="editForm.view_type = vt.value"
                  >
                    <div class="card-body p-3 d-flex align-items-center">
                      <input
                        type="radio"
                        :value="vt.value"
                        v-model="editForm.view_type"
                        class="mr-3"
                      />
                      <div class="flex-grow-1">
                        <div class="font-weight-bold text-dark text-sm">
                          {{ vt.label_kh }}
                        </div>
                        <div class="text-xs text-muted mt-1">{{ vt.description }}</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Custom View Details (if CUSTOM selected) -->
                <div v-if="editForm.view_type === 'CUSTOM'" class="card border-primary p-3 bg-light rounded-lg">
                  <h6 class="font-weight-bold text-primary text-sm mb-3">
                    <i class="fas fa-sliders-h mr-1"></i> ជ្រើសរើសគោលដៅអនុញ្ញាតឱ្យមើល (Custom View Selection):
                  </h6>

                  <!-- Multi-select Departments -->
                  <div class="form-group mb-3">
                    <label class="text-xs font-weight-bold text-secondary mb-1">
                      <i class="fas fa-building mr-1"></i> នាយកដ្ឋានដែលអាចមើលឃើញ ({{ editForm.view_dept_ids.length }})
                    </label>
                    <div class="custom-selection-box border p-2 bg-white rounded" style="max-height: 120px; overflow-y: auto;">
                      <div
                        v-for="d in options.departments"
                        :key="d.id"
                        class="custom-control custom-checkbox mb-1"
                      >
                        <input
                          type="checkbox"
                          class="custom-control-input"
                          :id="'v_dept_' + d.id"
                          :value="d.id"
                          v-model="editForm.view_dept_ids"
                        />
                        <label class="custom-control-label text-xs font-weight-500" :for="'v_dept_' + d.id">
                          {{ d.name_kh || d.name }}
                        </label>
                      </div>
                    </div>
                  </div>

                  <!-- Multi-select Offices -->
                  <div class="form-group mb-3">
                    <label class="text-xs font-weight-bold text-secondary mb-1">
                      <i class="fas fa-door-open mr-1"></i> ការិយាល័យដែលអាចមើលឃើញ ({{ editForm.view_office_ids.length }})
                    </label>
                    <div class="custom-selection-box border p-2 bg-white rounded" style="max-height: 120px; overflow-y: auto;">
                      <div
                        v-for="o in options.offices"
                        :key="o.id"
                        class="custom-control custom-checkbox mb-1"
                      >
                        <input
                          type="checkbox"
                          class="custom-control-input"
                          :id="'v_off_' + o.id"
                          :value="o.id"
                          v-model="editForm.view_office_ids"
                        />
                        <label class="custom-control-label text-xs font-weight-500" :for="'v_off_' + o.id">
                          {{ o.name_kh || o.name }}
                        </label>
                      </div>
                    </div>
                  </div>

                  <!-- Multi-select Users -->
                  <div class="form-group mb-0">
                    <label class="text-xs font-weight-bold text-secondary mb-1">
                      <i class="fas fa-user-friends mr-1"></i> មន្ត្រីជាក់លាក់ដែលអាចមើលឃើញ ({{ editForm.view_user_ids.length }})
                    </label>
                    <div class="input-group input-group-sm mb-1">
                      <input
                        type="text"
                        v-model="userSearchQuery"
                        class="form-control form-control-sm"
                        placeholder="ស្វែងរកឈ្មោះមន្ត្រី..."
                      />
                    </div>
                    <div class="custom-selection-box border p-2 bg-white rounded" style="max-height: 140px; overflow-y: auto;">
                      <div
                        v-for="u in filteredUsersForView"
                        :key="u.id"
                        class="custom-control custom-checkbox mb-1"
                      >
                        <input
                          type="checkbox"
                          class="custom-control-input"
                          :id="'v_usr_' + u.id"
                          :value="u.id"
                          v-model="editForm.view_user_ids"
                        />
                        <label class="custom-control-label text-xs font-weight-500" :for="'v_usr_' + u.id">
                          {{ u.name_kh || u.name }} <span class="text-muted">({{ u.position?.title_kh || u.position?.title_en || 'មន្ត្រី' }})</span>
                        </label>
                      </div>
                    </div>
                  </div>

                  <!-- Multi-select Working Groups for View -->
                  <div class="form-group mb-0 mt-3" v-if="options.working_groups?.length">
                    <label class="text-xs font-weight-bold text-secondary mb-1">
                      <i class="fas fa-users-cog mr-1"></i> ក្រុមការងារដែលអាចមើលឃើញ ({{ editForm.view_working_group_ids.length }})
                    </label>
                    <div class="custom-selection-box border p-2 bg-white rounded" style="max-height: 110px; overflow-y: auto;">
                      <div
                        v-for="wg in options.working_groups"
                        :key="wg.id"
                        class="custom-control custom-checkbox mb-1"
                      >
                        <input
                          type="checkbox"
                          class="custom-control-input"
                          :id="'v_wg_' + wg.id"
                          :value="wg.id"
                          v-model="editForm.view_working_group_ids"
                        />
                        <label class="custom-control-label text-xs font-weight-500" :for="'v_wg_' + wg.id">
                          {{ wg.name_kh || wg.name_en || wg.name }} <span v-if="wg.code" class="text-muted">({{ wg.code }})</span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ================= SECTION 2: SENDING TYPE GROUP ================= -->
              <div class="col-lg-6">
                <div class="d-flex align-items-center mb-3">
                  <span class="badge badge-success p-2 mr-2">
                    <i class="fas fa-paper-plane fa-lg"></i>
                  </span>
                  <div>
                    <h5 class="font-weight-bold text-success mb-0">២. Sending Type Group (សិទ្ធិផ្ញើ/បញ្ជូន)</h5>
                    <small class="text-muted">កំណត់គោលដៅដែលមន្ត្រីរូបនេះអាចបញ្ជូន/ឆ្លងឯកសារទៅបាន</small>
                  </div>
                </div>

                <!-- Send Type Radio Options -->
                <div class="send-type-options mb-3">
                  <div
                    v-for="st in sendTypeOptions"
                    :key="st.value"
                    class="card mb-2 option-card cursor-pointer"
                    :class="{ 'border-success bg-light-success-soft': editForm.send_type === st.value }"
                    @click="editForm.send_type = st.value"
                  >
                    <div class="card-body p-3 d-flex align-items-center">
                      <input
                        type="radio"
                        :value="st.value"
                        v-model="editForm.send_type"
                        class="mr-3"
                      />
                      <div class="flex-grow-1">
                        <div class="font-weight-bold text-dark text-sm">
                          {{ st.label_kh }}
                        </div>
                        <div class="text-xs text-muted mt-1">{{ st.description }}</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Custom Send Details (if CUSTOM selected) -->
                <div v-if="editForm.send_type === 'CUSTOM'" class="card border-success p-3 bg-light rounded-lg">
                  <h6 class="font-weight-bold text-success text-sm mb-3">
                    <i class="fas fa-sliders-h mr-1"></i> ជ្រើសរើសគោលដៅអនុញ្ញាតឱ្យបញ្ជូនទៅបាន (Custom Sending Targets):
                  </h6>

                  <!-- Multi-select Departments -->
                  <div class="form-group mb-3">
                    <label class="text-xs font-weight-bold text-secondary mb-1">
                      <i class="fas fa-building mr-1"></i> នាយកដ្ឋានដែលអាចបញ្ជូនទៅបាន ({{ editForm.send_dept_ids.length }})
                    </label>
                    <div class="custom-selection-box border p-2 bg-white rounded" style="max-height: 110px; overflow-y: auto;">
                      <div
                        v-for="d in options.departments"
                        :key="d.id"
                        class="custom-control custom-checkbox mb-1"
                      >
                        <input
                          type="checkbox"
                          class="custom-control-input"
                          :id="'s_dept_' + d.id"
                          :value="d.id"
                          v-model="editForm.send_dept_ids"
                        />
                        <label class="custom-control-label text-xs font-weight-500" :for="'s_dept_' + d.id">
                          {{ d.name_kh || d.name }}
                        </label>
                      </div>
                    </div>
                  </div>

                  <!-- Multi-select Offices -->
                  <div class="form-group mb-3">
                    <label class="text-xs font-weight-bold text-secondary mb-1">
                      <i class="fas fa-door-open mr-1"></i> ការិយាល័យដែលអាចបញ្ជូនទៅបាន ({{ editForm.send_office_ids.length }})
                    </label>
                    <div class="custom-selection-box border p-2 bg-white rounded" style="max-height: 110px; overflow-y: auto;">
                      <div
                        v-for="o in options.offices"
                        :key="o.id"
                        class="custom-control custom-checkbox mb-1"
                      >
                        <input
                          type="checkbox"
                          class="custom-control-input"
                          :id="'s_off_' + o.id"
                          :value="o.id"
                          v-model="editForm.send_office_ids"
                        />
                        <label class="custom-control-label text-xs font-weight-500" :for="'s_off_' + o.id">
                          {{ o.name_kh || o.name }}
                        </label>
                      </div>
                    </div>
                  </div>

                  <!-- Multi-select Officers -->
                  <div class="form-group mb-3">
                    <label class="text-xs font-weight-bold text-secondary mb-1">
                      <i class="fas fa-user-friends mr-1"></i> មន្ត្រីដែលអាចបញ្ជូនទៅបាន ({{ editForm.send_user_ids.length }})
                    </label>
                    <div class="input-group input-group-sm mb-1">
                      <input
                        type="text"
                        v-model="sendUserSearchQuery"
                        class="form-control form-control-sm"
                        placeholder="ស្វែងរកឈ្មោះមន្ត្រី..."
                      />
                    </div>
                    <div class="custom-selection-box border p-2 bg-white rounded" style="max-height: 120px; overflow-y: auto;">
                      <div
                        v-for="u in filteredUsersForSend"
                        :key="u.id"
                        class="custom-control custom-checkbox mb-1"
                      >
                        <input
                          type="checkbox"
                          class="custom-control-input"
                          :id="'s_usr_' + u.id"
                          :value="u.id"
                          v-model="editForm.send_user_ids"
                        />
                        <label class="custom-control-label text-xs font-weight-500" :for="'s_usr_' + u.id">
                          {{ u.name_kh || u.name }} <span class="text-muted">({{ u.position?.title_kh || u.position?.title_en || 'មន្ត្រី' }})</span>
                        </label>
                      </div>
                    </div>
                  </div>

                  <!-- Multi-select Working Groups -->
                  <div class="form-group mb-0" v-if="options.working_groups?.length">
                    <label class="text-xs font-weight-bold text-secondary mb-1">
                      <i class="fas fa-users-cog mr-1"></i> ក្រុមការងារដែលអាចបញ្ជូនទៅបាន ({{ editForm.send_working_group_ids.length }})
                    </label>
                    <div class="custom-selection-box border p-2 bg-white rounded" style="max-height: 100px; overflow-y: auto;">
                      <div
                        v-for="wg in options.working_groups"
                        :key="wg.id"
                        class="custom-control custom-checkbox mb-1"
                      >
                        <input
                          type="checkbox"
                          class="custom-control-input"
                          :id="'s_wg_' + wg.id"
                          :value="wg.id"
                          v-model="editForm.send_working_group_ids"
                        />
                        <label class="custom-control-label text-xs font-weight-500" :for="'s_wg_' + wg.id">
                          {{ wg.name_kh || wg.name_en || wg.name }} <span v-if="wg.code" class="text-muted">({{ wg.code }})</span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div class="form-group mt-3">
              <label class="font-weight-600 text-sm">កំណត់សម្គាល់បន្ថែម (Notes)</label>
              <textarea
                v-model="editForm.notes"
                class="form-control"
                rows="2"
                placeholder="មូលហេតុ ឬការកំណត់ពិសេសសម្រាប់មន្ត្រីរូបនេះ..."
              ></textarea>
            </div>
          </div>

          <!-- Footer -->
          <div class="modal-footer bg-light py-2">
            <button type="button" class="btn btn-secondary px-3" @click="closeEditModal">
              បោះបង់
            </button>
            <button
              type="button"
              class="btn btn-primary px-4 font-weight-bold"
              @click="submitSingleConfig"
              :disabled="submitting"
            >
              <i class="fas fa-save mr-1" v-if="!submitting"></i>
              <i class="fas fa-spinner fa-spin mr-1" v-else></i>
              រក្សាទុកការកំណត់
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== MODAL: BATCH WORKFLOW UPDATE ==================== -->
    <div
      class="modal fade"
      :class="{ show: showBatchModal, 'd-block': showBatchModal }"
      tabindex="-1"
      role="dialog"
      v-if="showBatchModal"
      style="background: rgba(0,0,0,0.55); z-index: 1050; overflow-y: auto;"
    >
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0 rounded-lg">
          <div class="modal-header bg-warning text-dark py-3">
            <h5 class="modal-title font-weight-bold">
              <i class="fas fa-layer-group mr-2"></i> កំណត់ Workflow រួមគ្នាសម្រាប់មន្ត្រី ({{ selectedUserIds.length }} នាក់)
            </h5>
            <button type="button" class="close" @click="closeBatchModal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body p-4">
            <div class="alert alert-info text-sm py-2">
              <i class="fas fa-info-circle mr-1"></i>
              ការកំណត់នេះនឹងត្រូវបានអនុវត្តលើមន្ត្រីទាំង <strong>{{ selectedUserIds.length }}</strong> នាក់ដែលលោកអ្នកបានជ្រើសរើស។
            </div>

            <!-- View Type Group -->
            <div class="form-group mb-4">
              <label class="font-weight-bold text-primary">
                <i class="fas fa-eye mr-1"></i> ១. View Type Group (សិទ្ធិមើលឯកសារ)
              </label>
              <select v-model="batchForm.view_type" class="form-control custom-select">
                <option value="">-- រក្សាទុកដូចដើម (កុំកែប្រែ) --</option>
                <option value="ALL">ទាំងអស់ (គ្រប់ឯកសារក្នុងប្រព័ន្ធ)</option>
                <option value="DEPARTMENT">ក្នុងនាយកដ្ឋាន</option>
                <option value="OFFICE">ក្នុងការិយាល័យ</option>
                <option value="SUBORDINATES">មន្ត្រីក្រោមឱវាទ & ខ្លួនឯង</option>
                <option value="SELF_ONLY">ផ្ទាល់ខ្លួន (Self Only)</option>
              </select>
            </div>

            <!-- Send Type Group -->
            <div class="form-group mb-4">
              <label class="font-weight-bold text-success">
                <i class="fas fa-paper-plane mr-1"></i> ២. Sending Type Group (សិទ្ធិផ្ញើ/ឆ្លងឯកសារ)
              </label>
              <select v-model="batchForm.send_type" class="form-control custom-select">
                <option value="">-- រក្សាទុកដូចដើម (កុំកែប្រែ) --</option>
                <option value="ALL">ទាំងអស់ (អាចផ្ញើទៅគ្រប់ទីកន្លែង)</option>
                <option value="DEPARTMENT">ក្នុងនាយកដ្ឋាន</option>
                <option value="OFFICE">ក្នុងការិយាល័យ</option>
                <option value="SUBORDINATES">មន្ត្រីក្រោមឱវាទប៉ុណ្ណោះ</option>
              </select>
            </div>

            <!-- Notes -->
            <div class="form-group mb-0">
              <label class="font-weight-600 text-sm">កំណត់សម្គាល់រួម</label>
              <input
                type="text"
                v-model="batchForm.notes"
                class="form-control"
                placeholder="ឧ. កំណត់រួមគ្នាដោយរដ្ឋបាល..."
              />
            </div>
          </div>

          <div class="modal-footer bg-light py-2">
            <button type="button" class="btn btn-secondary px-3" @click="closeBatchModal">បោះបង់</button>
            <button
              type="button"
              class="btn btn-warning px-4 font-weight-bold"
              @click="submitBatchConfig"
              :disabled="submitting || (!batchForm.view_type && !batchForm.send_type)"
            >
              <i class="fas fa-check-circle mr-1" v-if="!submitting"></i>
              <i class="fas fa-spinner fa-spin mr-1" v-else></i>
              អនុវត្តការកំណត់
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
import {
  apiGetWorkflowConfigs,
  apiGetWorkflowOptions,
  apiSaveWorkflowConfig,
  apiBatchSaveWorkflowConfigs,
  apiResetWorkflowConfig,
} from '@/functions/api/workflowConfig';

// State
const loading = ref(false);
const submitting = ref(false);
const users = ref([]);
const stats = ref({});
const options = reactive({
  departments: [],
  offices: [],
  users: [],
  working_groups: [],
});

const filters = reactive({
  search: '',
  department_id: '',
  office_id: '',
  view_type: '',
  send_type: '',
});

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 25,
  total: 0,
  from: 0,
  to: 0,
});

// Selection
const selectedUserIds = ref([]);

// Modals
const showEditModal = ref(false);
const showBatchModal = ref(false);
const editingUser = ref(null);

// Forms
const editForm = reactive({
  user_id: null,
  view_type: 'DEPARTMENT',
  view_dept_ids: [],
  view_office_ids: [],
  view_user_ids: [],
  view_working_group_ids: [],
  send_type: 'DEPARTMENT',
  send_dept_ids: [],
  send_office_ids: [],
  send_user_ids: [],
  send_working_group_ids: [],
  notes: '',
});

const batchForm = reactive({
  view_type: '',
  send_type: '',
  notes: '',
});

// Filter queries inside modals
const userSearchQuery = ref('');
const sendUserSearchQuery = ref('');

// Options definitions
const viewTypeOptions = [
  { value: 'ALL', label_kh: 'ទាំងអស់ (គ្រប់ឯកសារក្នុងប្រព័ន្ធ)', description: 'អាចមើលឃើញគ្រប់ឯកសារទាំងអស់ក្នុងស្ថាប័ន' },
  { value: 'DEPARTMENT', label_kh: 'ក្នុងនាយកដ្ឋាន', description: 'អាចមើលឃើញឯកសារទាំងអស់ដែលពាក់ព័ន្ធនឹងនាយកដ្ឋានរបស់ខ្លួន' },
  { value: 'OFFICE', label_kh: 'ក្នុងការិយាល័យ', description: 'អាចមើលឃើញឯកសារទាំងអស់ដែលពាក់ព័ន្ធនឹងការិយាល័យរបស់ខ្លួន' },
  { value: 'SUBORDINATES', label_kh: 'មន្ត្រីក្រោមឱវាទ & ខ្លួនឯង', description: 'អាចមើលឃើញឯកសាររបស់ខ្លួន និងមន្ត្រីក្រោមឱវាទ' },
  { value: 'SELF_ONLY', label_kh: 'ផ្ទាល់ខ្លួន (Self Only)', description: 'អាចមើលឃើញតែឯកសារដែលបញ្ជូនចំឈ្មោះខ្លួន ឬខ្លួនបានចុះ/ទទួល' },
  { value: 'CUSTOM', label_kh: 'កំណត់ដោយដៃ (Custom Selection)', description: 'ជ្រើសរើសនាយកដ្ឋាន ការិយាល័យ ឬមន្ត្រីជាក់លាក់' },
];

const sendTypeOptions = [
  { value: 'ALL', label_kh: 'ទាំងអស់ (អាចផ្ញើទៅគ្រប់ទីកន្លែង)', description: 'អាចបញ្ជូន/ឆ្លងឯកសារទៅគ្រប់នាយកដ្ឋាន ការិយាល័យ និងមន្ត្រីទាំងអស់' },
  { value: 'DEPARTMENT', label_kh: 'ក្នុងនាយកដ្ឋាន', description: 'អាចបញ្ជូនទៅកាន់ការិយាល័យ និងមន្ត្រីក្នុងនាយកដ្ឋានរបស់ខ្លួនប៉ុណ្ណោះ' },
  { value: 'OFFICE', label_kh: 'ក្នុងការិយាល័យ', description: 'អាចបញ្ជូនទៅកាន់មន្ត្រីក្នុងការិយាល័យរបស់ខ្លួនប៉ុណ្ណោះ' },
  { value: 'SUBORDINATES', label_kh: 'មន្ត្រីក្រោមឱវាទប៉ុណ្ណោះ', description: 'អាចបញ្ជូនបន្តទៅកាន់តែមន្ត្រីដែលមានឋានៈក្រោមឱវាទ' },
  { value: 'CUSTOM', label_kh: 'កំណត់ដោយដៃ (Custom Recipients)', description: 'ជ្រើសរើសនាយកដ្ឋាន ការិយាល័យ មន្ត្រី ឬក្រុមការងារជាក់លាក់' },
];

// Computed
const filteredOffices = computed(() => {
  if (!filters.department_id) return options.offices;
  return options.offices.filter(o => o.department_id === Number(filters.department_id));
});

const isAllSelected = computed(() => {
  return users.value.length > 0 && users.value.every(u => selectedUserIds.value.includes(u.id));
});

const filteredUsersForView = computed(() => {
  if (!userSearchQuery.value) return options.users;
  const q = userSearchQuery.value.toLowerCase();
  return options.users.filter(u =>
    (u.name_kh && u.name_kh.toLowerCase().includes(q)) ||
    (u.name && u.name.toLowerCase().includes(q))
  );
});

const filteredUsersForSend = computed(() => {
  if (!sendUserSearchQuery.value) return options.users;
  const q = sendUserSearchQuery.value.toLowerCase();
  return options.users.filter(u =>
    (u.name_kh && u.name_kh.toLowerCase().includes(q)) ||
    (u.name && u.name.toLowerCase().includes(q))
  );
});

const visiblePages = computed(() => {
  const current = pagination.current_page;
  const last = pagination.last_page;
  const delta = 2;
  const range = [];
  for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
    range.push(i);
  }
  if (current - delta > 2) range.unshift('...');
  if (current + delta < last - 1) range.push('...');
  range.unshift(1);
  if (last > 1) range.push(last);
  return range;
});

// Helpers
const truncate = (text, len = 25) => {
  if (!text) return '';
  return text.length > len ? text.substring(0, len) + '...' : text;
};

const getPositionLevelBadgeClass = (level) => {
  const lvl = Number(level);
  if (lvl === 1) return 'badge-danger font-weight-bold';
  if (lvl === 2) return 'badge-warning font-weight-bold text-dark';
  if (lvl <= 4) return 'badge-primary text-white';
  if (lvl <= 8) return 'badge-info text-white';
  return 'badge-light border text-muted';
};

const getViewTypeLabel = (type) => {
  const match = viewTypeOptions.find(o => o.value === type);
  return match ? match.label_kh : type;
};

const getSendTypeLabel = (type) => {
  const match = sendTypeOptions.find(o => o.value === type);
  return match ? match.label_kh : type;
};

const getViewBadgeClass = (type) => {
  switch (type) {
    case 'ALL': return 'badge-primary';
    case 'DEPARTMENT': return 'badge-success';
    case 'OFFICE': return 'badge-info';
    case 'SUBORDINATES': return 'badge-warning';
    case 'SELF_ONLY': return 'badge-secondary';
    case 'CUSTOM': return 'badge-indigo';
    default: return 'badge-light border';
  }
};

const getViewBadgeIcon = (type) => {
  switch (type) {
    case 'ALL': return 'fa-globe';
    case 'DEPARTMENT': return 'fa-building';
    case 'OFFICE': return 'fa-door-open';
    case 'SUBORDINATES': return 'fa-users';
    case 'SELF_ONLY': return 'fa-user-lock';
    case 'CUSTOM': return 'fa-sliders-h';
    default: return 'fa-eye';
  }
};

const getSendBadgeClass = (type) => {
  switch (type) {
    case 'ALL': return 'badge-primary';
    case 'DEPARTMENT': return 'badge-success';
    case 'OFFICE': return 'badge-info';
    case 'SUBORDINATES': return 'badge-warning';
    case 'CUSTOM': return 'badge-indigo';
    default: return 'badge-light border';
  }
};

const getSendBadgeIcon = (type) => {
  switch (type) {
    case 'ALL': return 'fa-share-alt';
    case 'DEPARTMENT': return 'fa-building';
    case 'OFFICE': return 'fa-door-open';
    case 'SUBORDINATES': return 'fa-level-down-alt';
    case 'CUSTOM': return 'fa-sliders-h';
    default: return 'fa-paper-plane';
  }
};

// Selection methods
const toggleSelectAll = (e) => {
  if (e.target.checked) {
    const pageIds = users.value.map(u => u.id);
    const combined = new Set([...selectedUserIds.value, ...pageIds]);
    selectedUserIds.value = Array.from(combined);
  } else {
    const pageIds = users.value.map(u => u.id);
    selectedUserIds.value = selectedUserIds.value.filter(id => !pageIds.includes(id));
  }
};

// Filter methods
const onDepartmentFilterChange = () => {
  filters.office_id = '';
  applyFilters();
};

const applyFilters = () => {
  fetchUsers(1);
};

const resetFilters = () => {
  filters.search = '';
  filters.department_id = '';
  filters.office_id = '';
  filters.view_type = '';
  filters.send_type = '';
  fetchUsers(1);
};

// Data Fetching
const fetchOptions = async () => {
  try {
    const res = await apiGetWorkflowOptions();
    if (res.data?.success) {
      options.departments = res.data.departments || [];
      options.offices = res.data.offices || [];
      options.users = res.data.users || [];
      options.working_groups = res.data.working_groups || [];
    }
  } catch (err) {
    console.error('Failed to load workflow options:', err);
  }
};

const fetchUsers = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.per_page,
      search: filters.search || undefined,
      department_id: filters.department_id || undefined,
      office_id: filters.office_id || undefined,
      view_type: filters.view_type || undefined,
      send_type: filters.send_type || undefined,
    };
    const res = await apiGetWorkflowConfigs(params);
    if (res.data?.success) {
      users.value = res.data.data.data || [];
      stats.value = res.data.stats || {};
      pagination.current_page = res.data.data.current_page || 1;
      pagination.last_page = res.data.data.last_page || 1;
      pagination.total = res.data.data.total || 0;
      pagination.from = res.data.data.from || 0;
      pagination.to = res.data.data.to || 0;
    }
  } catch (err) {
    console.error('Failed to load users workflow:', err);
    Swal.fire({
      icon: 'error',
      title: 'មានបញ្ហា!',
      text: 'មិនអាចទាញយកទិន្នន័យបានឡើយ។ សូមព្យាយាមម្តងទៀត។',
    });
  } finally {
    loading.value = false;
  }
};

// Edit Single User Modal
const openEditModal = (user) => {
  editingUser.value = user;
  editForm.user_id = user.id;

  const cfg = user.workflow_config;
  if (cfg) {
    editForm.view_type = cfg.view_type || 'DEPARTMENT';
    editForm.view_dept_ids = cfg.view_dept_ids ? [...cfg.view_dept_ids] : [];
    editForm.view_office_ids = cfg.view_office_ids ? [...cfg.view_office_ids] : [];
    editForm.view_user_ids = cfg.view_user_ids ? [...cfg.view_user_ids] : [];
    editForm.view_working_group_ids = cfg.view_working_group_ids ? [...cfg.view_working_group_ids] : [];
    editForm.send_type = cfg.send_type || 'DEPARTMENT';
    editForm.send_dept_ids = cfg.send_dept_ids ? [...cfg.send_dept_ids] : [];
    editForm.send_office_ids = cfg.send_office_ids ? [...cfg.send_office_ids] : [];
    editForm.send_user_ids = cfg.send_user_ids ? [...cfg.send_user_ids] : [];
    editForm.send_working_group_ids = cfg.send_working_group_ids ? [...cfg.send_working_group_ids] : [];
    editForm.notes = cfg.notes || '';
  } else {
    // Intelligent defaults based on position level
    const level = user.position?.level ? Number(user.position.level) : 99;
    if (level === 1) {
      editForm.view_type = 'ALL';
      editForm.send_type = 'ALL';
    } else if (level === 2) {
      editForm.view_type = 'CUSTOM';
      editForm.send_type = 'ALL';
    } else if (level <= 5) {
      editForm.view_type = 'DEPARTMENT';
      editForm.send_type = 'DEPARTMENT';
    } else if (level <= 8) {
      editForm.view_type = 'OFFICE';
      editForm.send_type = 'OFFICE';
    } else {
      editForm.view_type = 'SUBORDINATES';
      editForm.send_type = 'SUBORDINATES';
    }
    editForm.view_dept_ids = [];
    editForm.view_office_ids = [];
    editForm.view_user_ids = [];
    editForm.view_working_group_ids = [];
    editForm.send_dept_ids = [];
    editForm.send_office_ids = [];
    editForm.send_user_ids = [];
    editForm.send_working_group_ids = [];
    editForm.notes = '';
  }

  showEditModal.value = true;
};

const closeEditModal = () => {
  showEditModal.value = false;
  editingUser.value = null;
};

const submitSingleConfig = async () => {
  submitting.value = true;
  try {
    const payload = {
      user_id: editForm.user_id,
      view_type: editForm.view_type,
      view_dept_ids: editForm.view_dept_ids,
      view_office_ids: editForm.view_office_ids,
      view_user_ids: editForm.view_user_ids,
      view_working_group_ids: editForm.view_working_group_ids,
      send_type: editForm.send_type,
      send_dept_ids: editForm.send_dept_ids,
      send_office_ids: editForm.send_office_ids,
      send_user_ids: editForm.send_user_ids,
      send_working_group_ids: editForm.send_working_group_ids,
      notes: editForm.notes,
    };

    const res = await apiSaveWorkflowConfig(payload);
    if (res.data?.success) {
      Swal.fire({
        icon: 'success',
        title: 'ជោគជ័យ!',
        text: 'បានរក្សាទុកការកំណត់ Workflow ដោយជោគជ័យ',
        timer: 1500,
        showConfirmButton: false,
      });
      closeEditModal();
      fetchUsers(pagination.current_page);
    }
  } catch (err) {
    console.error('Failed to save workflow config:', err);
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ!',
      text: err.response?.data?.message || 'មិនអាចរក្សាទុកការកំណត់បានឡើយ',
    });
  } finally {
    submitting.value = false;
  }
};

const resetUserConfig = async (user) => {
  const result = await Swal.fire({
    title: 'តើលោកអ្នកចង់កំណត់ឡើងវិញមែនទេ?',
    text: `លុបការកំណត់ Workflow ជាក់លាក់របស់មន្ត្រី "${user.name_kh || user.name}" ហើយត្រឡប់ទៅតាមឋានានុក្រមធម្មជាតិ?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'យល់ព្រមលុប',
    cancelButtonText: 'បោះបង់',
  });

  if (result.isConfirmed) {
    try {
      const res = await apiResetWorkflowConfig(user.id);
      if (res.data?.success) {
        Swal.fire({
          icon: 'success',
          title: 'បានលុបរួចរាល់!',
          text: res.data.message,
          timer: 1500,
          showConfirmButton: false,
        });
        fetchUsers(pagination.current_page);
      }
    } catch (err) {
      console.error('Failed to reset workflow config:', err);
      Swal.fire({
        icon: 'error',
        title: 'មានបញ្ហា!',
        text: 'មិនអាចកំណត់ឡើងវិញបានឡើយ',
      });
    }
  }
};

// Batch Update
const openBatchModal = () => {
  batchForm.view_type = '';
  batchForm.send_type = '';
  batchForm.notes = '';
  showBatchModal.value = true;
};

const closeBatchModal = () => {
  showBatchModal.value = false;
};

const submitBatchConfig = async () => {
  submitting.value = true;
  try {
    const payload = {
      user_ids: selectedUserIds.value,
      view_type: batchForm.view_type || undefined,
      send_type: batchForm.send_type || undefined,
      notes: batchForm.notes || undefined,
    };

    const res = await apiBatchSaveWorkflowConfigs(payload);
    if (res.data?.success) {
      Swal.fire({
        icon: 'success',
        title: 'ជោគជ័យ!',
        text: res.data.message,
        timer: 1800,
        showConfirmButton: false,
      });
      closeBatchModal();
      selectedUserIds.value = [];
      fetchUsers(pagination.current_page);
    }
  } catch (err) {
    console.error('Failed batch workflow update:', err);
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ!',
      text: err.response?.data?.message || 'មានបញ្ហាក្នុងការកំណត់រួមគ្នា',
    });
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  fetchOptions();
  fetchUsers();
});
</script>

<style scoped>
.workflow-setup-page {
  font-family: 'Kantumruy Pro', 'Hanuman', sans-serif, -apple-system;
}

.option-card {
  transition: all 0.2s ease-in-out;
  border-radius: 8px;
}

.option-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.cursor-pointer {
  cursor: pointer;
}

.bg-light-primary-soft {
  background-color: #ebf5ff !important;
}

.bg-light-success-soft {
  background-color: #eafaf1 !important;
}

.badge-indigo {
  background-color: #6610f2;
  color: #fff;
}

.custom-selection-box {
  scrollbar-width: thin;
}

.custom-selection-box::-webkit-scrollbar {
  width: 5px;
}

.custom-selection-box::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
</style>
