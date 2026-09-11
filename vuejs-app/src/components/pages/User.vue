<template>
  <div class="content-wrapper khmer-layout">
    <!-- Header -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-3 align-items-center">
          <div class="col-sm-6">
            <h1 class="m-0 font-weight-bold text-dark khmer-page-title">
              <i class="fas fa-users-cog mr-2 text-success"></i>គ្រប់គ្រងព័ត៌មានមន្ត្រី
            </h1>
          </div>
          <div class="col-sm-6 text-right">
            <button class="btn btn-dark-custom shadow-sm font-khmer" @click="openCreateModal">
              <i class="fas fa-user-plus mr-1"></i> បន្ថែមមន្ត្រីថ្មី
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Officer Status Summary Cards -->
        <div class="row mb-3">
          <!-- ទាំងអស់ (All) -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card shadow-sm border-0 rounded-lg h-100 cursor-pointer transition-all"
                 :class="{ 'active-stat-card border-primary': filters.officer_status === '' }"
                 @click="filterByOfficerStatus('')">
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted small d-block font-weight-bold">មន្ត្រីទាំងអស់</span>
                  <h3 class="font-weight-bold mb-0 text-dark">{{ officerStats.ALL || 0 }}</h3>
                </div>
                <div class="stat-icon-circle bg-light text-primary">
                  <i class="fas fa-users fa-lg"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- កំពុងបម្រើការងារ (Active) -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card shadow-sm border-0 rounded-lg h-100 cursor-pointer transition-all"
                 :class="{ 'active-stat-card border-success': filters.officer_status === 'ACTIVE' }"
                 @click="filterByOfficerStatus('ACTIVE')">
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-success small d-block font-weight-bold">កំពុងបម្រើការ</span>
                  <h3 class="font-weight-bold mb-0 text-success">{{ officerStats.ACTIVE || 0 }}</h3>
                </div>
                <div class="stat-icon-circle bg-success-light text-success">
                  <i class="fas fa-user-check fa-lg"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- លាឈប់ (Resigned) -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card shadow-sm border-0 rounded-lg h-100 cursor-pointer transition-all"
                 :class="{ 'active-stat-card border-danger': filters.officer_status === 'RESIGNED' }"
                 @click="filterByOfficerStatus('RESIGNED')">
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-danger small d-block font-weight-bold">លាឈប់</span>
                  <h3 class="font-weight-bold mb-0 text-danger">{{ officerStats.RESIGNED || 0 }}</h3>
                </div>
                <div class="stat-icon-circle bg-danger-light text-danger">
                  <i class="fas fa-user-times fa-lg"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- ចូលនិវត្តន៍ (Retired) -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card shadow-sm border-0 rounded-lg h-100 cursor-pointer transition-all"
                 :class="{ 'active-stat-card border-secondary': filters.officer_status === 'RETIRED' }"
                 @click="filterByOfficerStatus('RETIRED')">
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-secondary small d-block font-weight-bold">ចូលនិវត្តន៍</span>
                  <h3 class="font-weight-bold mb-0 text-secondary">{{ officerStats.RETIRED || 0 }}</h3>
                </div>
                <div class="stat-icon-circle bg-secondary-light text-secondary">
                  <i class="fas fa-user-clock fa-lg"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- ផ្លាស់ប្តូរ (Transferred) -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card shadow-sm border-0 rounded-lg h-100 cursor-pointer transition-all"
                 :class="{ 'active-stat-card border-info': filters.officer_status === 'TRANSFERRED' }"
                 @click="filterByOfficerStatus('TRANSFERRED')">
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-info small d-block font-weight-bold">ផ្លាស់ប្តូរ</span>
                  <h3 class="font-weight-bold mb-0 text-info">{{ officerStats.TRANSFERRED || 0 }}</h3>
                </div>
                <div class="stat-icon-circle bg-info-light text-info">
                  <i class="fas fa-exchange-alt fa-lg"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- ព្យួរការងារ (Suspended) -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card shadow-sm border-0 rounded-lg h-100 cursor-pointer transition-all"
                 :class="{ 'active-stat-card border-warning': filters.officer_status === 'SUSPENDED' }"
                 @click="filterByOfficerStatus('SUSPENDED')">
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-warning small d-block font-weight-bold">ព្យួរការងារ</span>
                  <h3 class="font-weight-bold mb-0 text-warning">{{ officerStats.SUSPENDED || 0 }}</h3>
                </div>
                <div class="stat-icon-circle bg-warning-light text-warning">
                  <i class="fas fa-pause-circle fa-lg"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter Card -->
        <div class="card card-outline card-success shadow-sm mb-4 border-0">
          <div class="card-body">
            <div class="row g-3 align-items-center">
              <div class="col-md-3">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-white border-right-0"><i
                        class="fas fa-search text-muted"></i></span>
                  </div>
                  <input type="text" class="form-control border-left-0 font-khmer" v-model="filters.keyword"
                    placeholder="ឈ្មោះ, អ៊ីមែល, អត្តលេខ, ទូរស័ព្ទ..." @input="handleSearch" />
                </div>
              </div>
              <div class="col-md-2">
                <select class="form-control font-khmer" v-model="filters.officer_status" @change="fetchUsers(1)">
                  <option value="">-- ស្ថានភាពមន្ត្រី (ទាំងអស់) --</option>
                  <option value="ACTIVE">កំពុងបម្រើការងារ</option>
                  <option value="RESIGNED">លាឈប់</option>
                  <option value="RETIRED">ចូលនិវត្តន៍</option>
                  <option value="TRANSFERRED">ផ្លាស់ប្តូរ</option>
                  <option value="SUSPENDED">ព្យួរការងារ</option>
                  <option value="OTHER">ផ្សេងៗ</option>
                </select>
              </div>
              <div class="col-md-2">
                <select class="form-control font-khmer" v-model="filters.employee_type" @change="fetchUsers(1)">
                  <option value="">-- ប្រភេទមន្ត្រីទាំងអស់ --</option>
                  <option value="CIVIL_SERVICE">មន្ត្រីមុខងារសាធារណៈ</option>
                  <option value="STATUTORY">មន្ត្រីលក្ខន្តិកៈ</option>
                  <option value="CONTRACT">មន្ត្រីជាប់កិច្ចសន្យា</option>
                  <option value="OTHER">ផ្សេងៗ</option>
                </select>
              </div>
              <div class="col-md-2">
                <select class="form-control font-khmer" v-model="filters.status" @change="fetchUsers(1)">
                  <option value="">-- ស្ថានភាពគណនី --</option>
                  <option value="ENABLED">សកម្ម (ENABLED)</option>
                  <option value="DISABLED">ផ្អាក (DISABLED)</option>
                </select>
              </div>
              <div class="col-md-2">
                <select class="form-control font-khmer" v-model="filters.level" @change="fetchUsers(1)">
                  <option value="">-- កម្រិតសិទ្ធិ --</option>
                  <option value="ADMIN">ADMIN</option>
                  <option value="USER">USER</option>
                </select>
              </div>
              <div class="col-md-1 text-right">
                <button class="btn btn-secondary w-100 font-khmer px-2" @click="resetFilters" title="សម្អាត Filter">
                  <i class="fas fa-redo-alt"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Table Card -->
        <div class="card shadow-sm border-0">
          <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0 custom-table">
              <thead class="bg-light">
                <tr>
                  <th style="width: 50px" class="text-center">#</th>
                  <th style="width: 70px;">រូបថត</th>
                  <th>អត្តលេខ</th>
                  <th>ឈ្មោះ</th>
                  <th>នាយកដ្ឋាន / ការិយាល័យ / តួនាទី</th>
                  <th>ទំនាក់ទំនង</th>
                  <th class="text-center" style="width: 100px;">ស្ថានភាព</th>
                  <th style="width: 130px" class="text-center">សកម្មភាព</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="8" class="text-center py-5 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x text-success"></i>
                    <p class="mt-2 mb-0 font-khmer">កំពុងទាញយកទិន្នន័យ...</p>
                  </td>
                </tr>
                <tr v-else-if="users.length === 0">
                  <td colspan="8" class="text-center py-5 text-muted">
                    <i class="fas fa-folder-open fa-3x mb-2 text-secondary"></i>
                    <p class="mb-0 font-khmer">មិនមានទិន្នន័យឡើយ</p>
                  </td>
                </tr>
                <tr v-else v-for="(item, index) in users" :key="item.id">
                  <td class="text-center font-weight-bold text-muted">
                    {{ (pagination.currentPage - 1) * pagination.perPage + index + 1 }}
                  </td>
                  <td>
                    <img :src="getFullImageUrl(item.profile_thumbnail || item.profile_image)" alt="Avatar"
                      class="img-circle elevation-1 border avatar-img" @error="onImageError" />
                  </td>
                  <td>
                    <div class="font-weight-bold text-dark" v-if="item.employee_type === 'CIVIL_SERVICE'">{{ item.employee_code || '---' }}</div>
                    <small class="text-muted" v-if="item.employee_type === 'CIVIL_SERVICE'">MEF: {{ item.mef_card_number || '---' }}</small><br>
                    <span :class="getEmployeeTypeBadge(item.employee_type)">
                      {{ formatEmployeeType(item.employee_type) }}
                    </span>
                  </td>
                  <td>
                    <div class="font-weight-bold text-dark name-khmer">{{ item.name_kh || item.name || '---' }}</div>
                    <small class="text-muted">{{ item.name_en || '---' }}</small>
                    <div class="mt-1" v-if="item.service_duration_formatted">
                      <span class="badge badge-light border text-primary" title="អតីតភាពការងារ / រយៈពេលបម្រើការងារ">
                        <i class="fas fa-business-time mr-1"></i>{{ item.service_duration_formatted }}
                      </span>
                    </div>
                  </td>
                  <td>
                    <div class="font-weight-500">{{ item.department?.name_kh || item.department?.name || 'និយ័តករបរធនបាលកិច្ច' }}</div>
                    <small class="text-muted d-block">
                      {{ item.position?.title_kh || item.position?.name || '---' }}
                      <span v-if="item.office"> ({{ item.office?.name_kh || item.office?.name }})</span>
                    </small>
                  </td>
                  <td>
                    <div><i class="fas fa-phone-volume" style="font-size: small;"></i> {{ item.phone || '---' }}</div>
                    <small class="text-muted"><i class="fas fa-envelope mr-1 small"></i>{{ item.email }}</small>
                  </td>
                  <td class="text-center">
                    <!-- ស្ថានភាពមន្ត្រី (Officer Status) -->
                    <span :class="getOfficerStatusBadgeClass(item.officer_status)">
                      <i :class="getOfficerStatusIcon(item.officer_status)" class="mr-1"></i>
                      {{ formatOfficerStatus(item.officer_status) }}
                    </span>
                    <small v-if="item.officer_status_date && item.officer_status !== 'ACTIVE'" class="text-muted d-block mt-1 font-size-xs">
                      <i class="far fa-calendar-alt mr-1"></i>{{ formatDate(item.officer_status_date) }}
                    </small>
                    <small v-if="item.officer_status_reason && item.officer_status !== 'ACTIVE'" class="text-secondary d-block font-size-xs text-truncate" style="max-width: 130px; margin: 0 auto;" :title="item.officer_status_reason">
                      {{ item.officer_status_reason }}
                    </small>

                    <!-- ស្ថានភាពគណនី (Login Status) -->
                    <div class="mt-1">
                      <span :class="item.status === 'ENABLED' ? 'badge badge-pill badge-light border text-success' : 'badge badge-pill badge-light border text-danger'" style="font-size: 10px;">
                        {{ item.status === 'ENABLED' ? 'គណនី: សកម្ម' : 'គណនី: ផ្អាក' }}
                      </span>
                    </div>
                  </td>
                  <td class="text-center">
                    <div class="action-buttons">
                      <button class="btn btn-action btn-print text-primary" title="បោះពុម្ពប្រវត្តិរូប" @click="printUserProfile(item.id)">
                        <i class="fas fa-print"></i>
                      </button>
                      <button 
                        v-if="userStore.isAdmin || item.level !== 'ADMIN'"
                        class="btn btn-action btn-permission text-warning" 
                        title="កំណត់សិទ្ធិប្រើប្រាស់ម៉ឺនុយ" 
                        @click="openPermissionModal(item)">
                        <i class="fas fa-shield-alt"></i>
                      </button>
                      <button 
                        v-if="userStore.isAdmin || item.level !== 'ADMIN'"
                        class="btn btn-action btn-edit" 
                        title="កែសម្រួល" 
                        @click="openEditModal(item)">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button
                        v-if="userStore.isAdmin || item.level !== 'ADMIN'"
                        :class="item.status === 'ENABLED' ? 'btn btn-action btn-disable' : 'btn btn-action btn-enable'"
                        :title="item.status === 'ENABLED' ? 'ផ្អាកដំណើរការ' : 'បើកដំណើរការ'"
                        @click="toggleStatus(item)">
                        <i :class="item.status === 'ENABLED' ? 'fas fa-user-slash' : 'fas fa-user-check'"></i>
                      </button>
                      <button 
                        v-if="userStore.isAdmin || item.level !== 'ADMIN'"
                        class="btn btn-action btn-delete" 
                        title="លុប" 
                        @click="removeUser(item.id)">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="card-footer bg-white d-flex justify-content-between align-items-center"
            v-if="pagination.total > 0">
            <span class="text-muted small font-khmer">
              បង្ហាញ {{ users.length }} នៃទិន្នន័យសរុប {{ pagination.total }}
            </span>
            <ul class="pagination pagination-sm m-0">
              <li class="page-item" :class="{ disabled: pagination.currentPage === 1 }">
                <a class="page-link" href="#" @click.prevent="changePage(pagination.currentPage - 1)">&laquo;</a>
              </li>
              <li class="page-item" v-for="page in pagination.lastPage" :key="page"
                :class="{ active: pagination.currentPage === page }">
                <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: pagination.currentPage === pagination.lastPage }">
                <a class="page-link" href="#" @click.prevent="changePage(pagination.currentPage + 1)">&raquo;</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Modal Form (Create / Edit) -->
    <div v-if="showModal" class="custom-modal-backdrop" @click.self="closeModal">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable my-auto" role="document">
        <div class="modal-content shadow-lg border-0 font-khmer overflow-hidden" style="max-height: calc(100vh - 60px); display: flex; flex-direction: column;">
          <form @submit.prevent="submitForm" class="d-flex flex-column h-100 overflow-hidden" style="max-height: calc(100vh - 60px);">
            <div class="modal-header bg-success text-white py-3 flex-shrink-0">
              <h5 class="modal-title font-weight-bold font-khmer mb-0">
                <i class="fas mr-2" :class="isEditMode ? 'fa-user-edit' : 'fa-user-plus'"></i>
                {{ isEditMode ? 'កែសម្រួលព័ត៌មានលម្អិតមន្ត្រី' : 'បញ្ចូលព័ត៌មានមន្ត្រីថ្មី' }}
              </h5>
              <button type="button" class="close text-white" @click="closeModal">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>

            <div class="modal-body p-4 flex-grow-1" style="overflow-y: auto;">
              <!-- ផ្នែកទី ១៖ ព័ត៌មានគណនី & សិទ្ធិ -->
              <h6 class="text-success font-weight-bold mb-3 border-bottom pb-2 font-khmer">
                <i class="fas fa-user-shield mr-1"></i> ១. ព័ត៌មានគណនី & សិទ្ធិប្រើប្រាស់
              </h6>
              <div class="row mb-3 align-items-center">
                <div class="col-md-3 text-center mb-3 mb-md-0">
                  <div class="avatar-preview-container">
                    <img :src="imagePreviewUrl || defaultAvatar" alt="Avatar Preview"
                      class="img-thumbnail rounded-circle border shadow-sm"
                      style="width: 110px; height: 110px; object-fit: cover;" @error="onImageError" />
                  </div>
                  <label class="btn btn-sm btn-outline-secondary mt-2 mb-0 font-khmer">
                    <i class="fas fa-camera mr-1"></i> រើសរូបថត
                    <input type="file" class="d-none" accept="image/*" @change="onFileChange" />
                  </label>
                </div>
                <div class="col-md-9">
                  <div class="row">
                    <div class="col-md-6 form-group">
                      <label class="form-label font-weight-bold">អ៊ីមែល / Email <span
                          class="text-danger">*</span></label>
                      <input type="email" class="form-control" v-model="form.email" required
                        placeholder="example@mef.gov.kh" />
                    </div>
                    <div class="col-md-6 form-group">
                      <label class="form-label font-weight-bold">
                        ពាក្យសម្ងាត់ <span v-if="!isEditMode" class="text-danger">*</span>
                        <small v-if="isEditMode" class="text-muted font-khmer">(ទុកទទេបើមិនចង់ប្តូរ)</small>
                      </label>
                      <input type="password" class="form-control" v-model="form.password" :required="!isEditMode"
                        placeholder="••••••••" />
                    </div>
                    <div class="col-md-6 form-group">
                      <label class="form-label font-weight-bold">កម្រិតសិទ្ធិ</label>
                      <select class="form-control" v-model="form.level">
                        <option value="USER">USER</option>
                        <option value="ADMIN">ADMIN</option>
                      </select>
                    </div>
                    <div class="col-md-6 form-group">
                      <label class="form-label font-weight-bold">ស្ថានភាព</label>
                      <select class="form-control" v-model="form.status">
                        <option value="ENABLED">សកម្ម (ENABLED)</option>
                        <option value="DISABLED">ផ្អាក (DISABLED)</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              <h6 class="text-success font-weight-bold mb-3 border-bottom pb-2 font-khmer">
                <i class="fas fa-user-tag mr-1"></i> ប្រភេទ & ស្ថានភាពមន្ត្រី
              </h6>
              <div class="row mb-3">
                <div class="col-md-4 form-group">
                  <label class="form-label font-weight-bold">ប្រភេទមន្ត្រី</label>
                  <select class="form-control" v-model="form.employee_type">
                    <option value="CIVIL_SERVICE">មន្រ្តីមុខងារសារធារណៈ</option>
                    <option value="STATUTORY">មន្ត្រីលក្ខន្តិកៈ</option>
                    <option value="CONTRACT">មន្ត្រីជាប់កិច្ចសន្យា</option>
                    <option value="OTHER">ផ្សេងៗ</option>
                  </select>
                </div>
                <div class="col-md-4 form-group" v-if="form.employee_type === 'CIVIL_SERVICE'">
                  <label class="form-label font-weight-bold">អត្តលេខមន្ត្រីរាជ្យការ</label>
                  <input type="text" class="form-control" v-model="form.employee_code" placeholder="ឧ. MEF-0001" />
                </div>
                <div class="col-md-4 form-group" v-if="form.employee_type === 'CIVIL_SERVICE'">
                  <label class="form-label font-weight-bold">លេខប័ណ្ណសម្គាល់មន្រ្តីកសហវ</label>
                  <input type="text" class="form-control" v-model="form.mef_card_number" placeholder="ឧ. MEF-CARD-01" />
                </div>

                <div class="col-md-4 form-group">
                  <label class="form-label font-weight-bold">ស្ថានភាពមន្ត្រី <span class="text-danger">*</span></label>
                  <select class="form-control" v-model="form.officer_status" @change="onOfficerStatusChange">
                    <option value="ACTIVE">កំពុងបម្រើការងារ (ACTIVE)</option>
                    <option value="RESIGNED">លាឈប់ (RESIGNED)</option>
                    <option value="RETIRED">ចូលនិវត្តន៍ (RETIRED)</option>
                    <option value="TRANSFERRED">ផ្លាស់ប្តូរ (TRANSFERRED)</option>
                    <option value="SUSPENDED">ព្យួរការងារ (SUSPENDED)</option>
                    <option value="OTHER">ផ្សេងៗ (OTHER)</option>
                  </select>
                </div>
                <div class="col-md-4 form-group" v-if="form.officer_status !== 'ACTIVE'">
                  <label class="form-label font-weight-bold">កាលបរិច្ឆេទប្រែប្រួលស្ថានភាព</label>
                  <input type="date" class="form-control" v-model="form.officer_status_date" />
                </div>
                <div class="col-md-4 form-group" v-if="form.officer_status !== 'ACTIVE'">
                  <label class="form-label font-weight-bold">មូលហេតុ / ឯកសារយោង</label>
                  <input type="text" class="form-control" v-model="form.officer_status_reason" placeholder="ឧ. លិខិតលេខ... ឬមូលហេតុ" />
                </div>
                <div class="col-12" v-if="modalPreviewDuration">
                  <div class="alert alert-info py-2 px-3 mb-0 small d-flex align-items-center">
                    <i class="fas fa-business-time mr-2 fa-lg text-primary"></i>
                    <span>រយៈពេលនៃការធ្វើការងារ (អតីតភាពការងារ)៖ <strong>{{ modalPreviewDuration }}</strong></span>
                  </div>
                </div>
              </div>
 <!-- ផ្នែកទី ៣៖ ព័ត៌មានផ្ទាល់ខ្លួន -->
              <h5 class="text-success font-weight-bold mb-3 border-bottom pb-2 font-khmer">
                <i class="fas fa-id-badge mr-1"></i> ព័ត៌មានផ្ទាល់ខ្លួន
              </h5>
              <div class="row">
                <div class="col-md-4 form-group">
                  <label class="form-label font-weight-bold">គោត្តនាម និងនាម <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" v-model="form.name_kh" required placeholder="ឧ. សុខ សាន" />
                </div>
                <div class="col-md-4 form-group">
                  <label class="form-label font-weight-bold">ជាអក្សរឡាតាំង</label>
                  <input type="text" class="form-control" v-model="form.name_en" placeholder="ឧ. SOK SAN" />
                </div>
                <div class="col-md-2 form-group">
                  <label class="form-label font-weight-bold">ភេទ</label>
                  <select class="form-control" v-model="form.gender">
                    <option value="MALE">ប្រុស (Male)</option>
                    <option value="FEMALE">ស្រី (Female)</option>
                  </select>
                </div>
                 <div class="col-md-2 form-group">
                  <label class="form-label font-weight-bold">ថ្ងៃខែឆ្នាំកំណើត</label>
                  <input type="date" class="form-control" v-model="form.dob" />
                </div>
                <div class="col-md-2 form-group">
                  <label class="form-label font-weight-bold">ស្ថានភាពគ្រួសារ</label>
                  <select class="form-control" v-model="form.marital_status">
                    <option value="SINGLE">នៅលីវ</option>
                    <option value="MARRIED">រៀបការ</option>
                    
                  </select>
                </div>
                
                <div class="col-md-10 form-group">
                  <label class="form-label font-weight-bold">ទីកន្លែងកំណើត</label>
                  <input type="text" class="form-control" v-model="form.birth_place" placeholder="ខេត្ត/រាជធានីកំណើត" />
                </div>
                <div class="col-md-12 form-group">
                  <label class="form-label font-weight-bold">អាសយដ្ឋានបច្ចុប្បន្ន</label>
                  <textarea class="form-control" rows="2" v-model="form.current_address"
                    placeholder="ផ្ទះលេខ, ផ្លូវ, ភូមិ/ឃុំ, ស្រុក/ខណ្ឌ, ខេត្ត/រាជធានី..."></textarea>
                </div>
              </div>
              <div class="col-md-4 form-group">
                  <label class="form-label font-weight-bold">លេខទូរស័ព្ទ</label>
                  <input type="text" class="form-control" v-model="form.phone" placeholder="012 345 678" />
                </div>
              <div class="row mb-3">
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">លេខអត្តសញ្ញាណប័ណ្ណ</label>
                  <input type="text" class="form-control" v-model="form.national_id_number"
                    placeholder="លេខ ៩ ឬ ១០ ខ្ទង់" />
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">ថ្ងៃផុតកំណត់អត្តសញ្ញាណប័ណ្ណ</label>
                  <input type="date" class="form-control" v-model="form.national_id_expired_date" />
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">លេខលិខិតឆ្លងដែន</label>
                  <input type="text" class="form-control" v-model="form.passport_number" placeholder="ឧ. N1234567" />
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">ថ្ងៃផុតកំណត់លិខិតឆ្លងដែន</label>
                  <input type="date" class="form-control" v-model="form.passport_expired_date" />
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6 form-group">
                  <label class="form-label font-weight-bold">ឯកសារអត្តសញ្ញាណបណ្ណ (File Upload)</label>
                  <div class="d-flex align-items-center">
                    <input type="file" class="form-control-file" @change="onNationalIdFileChange" accept="image/*,.pdf" />
                    <a v-if="form.national_id_file_preview" :href="getFullImageUrl(form.national_id_file_preview)" target="_blank" class="btn btn-xs btn-outline-success ml-2" style="white-space: nowrap;">
                      <i class="fas fa-eye mr-1"></i> មើលឯកសារចាស់
                    </a>
                  </div>
                </div>
                <div class="col-md-6 form-group">
                  <label class="form-label font-weight-bold">ឯកសារលិខិតឆ្លងដែន (File Upload)</label>
                  <div class="d-flex align-items-center">
                    <input type="file" class="form-control-file" @change="onPassportFileChange" accept="image/*,.pdf" />
                    <a v-if="form.passport_file_preview" :href="getFullImageUrl(form.passport_file_preview)" target="_blank" class="btn btn-xs btn-outline-success ml-2" style="white-space: nowrap;">
                      <i class="fas fa-eye mr-1"></i> មើលឯកសារចាស់
                    </a>
                  </div>
                </div>
              </div>





              <!-- ផ្នែកទី ២៖ អង្គភាព & តួនាទី -->
              <h5 class="text-success font-weight-bold mb-3  pb-2 font-khmer">
                <i class="fas fa-sitemap mr-1"></i> ព័ត៌មានអំពីស្ថានភាពមុខងារ
              </h5>
              <h6 class="text-success font-weight-bold mb-3 border-bottom pb-2 font-khmer ">
                ក.ចូលបម្រើការងារដំបូង
              </h6>
               <div class="row mb-3">
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">កាលបរិច្ឆេទចូលបម្រើការងារដំបូង</label>
                  <input type="date" class="form-control" v-model="form.first_service_date" />
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">កាលបរិច្ឆេទតាំងស៊ុប/ទទួលស្គាល់</label>
                  <input type="date" class="form-control" v-model="form.first_appointment_date" />
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">ក្របខណ្ឌ ឋានន្តរស័ក្តិ ប្រភេទ និងថ្នាក់</label>
                  <input type="text" class="form-control" v-model="form.initial_framework" placeholder="ក្របខណ្ឌដំបូង" />
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">មុខតំណែង</label>
                  <input type="text" class="form-control" v-model="form.initial_position" placeholder="មុខតំណែងដំបូង" />
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">ក្រសួង/ស្ថាប័ន</label>
                  <input type="text" class="form-control" v-model="form.initial_ministry" placeholder="ក្រសួងដំបូង" />
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">អង្គភាព</label>
                  <input type="text" class="form-control" v-model="form.initial_unit" placeholder="អង្គភាពដំបូង" />
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">នាយកដ្ឋាន/អង្គភាព/មន្ទីរ</label>
                  <input type="text" class="form-control" v-model="form.initial_department" placeholder="នាយកដ្ឋានដំបូង" />
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">ការិយាល័យ</label>
                  <input type="text" class="form-control" v-model="form.initial_office" placeholder="ការិយាល័យដំបូង" />
                </div>
              </div>
               <h6 class="text-success font-weight-bold mb-3 border-bottom pb-2 font-khmer ">
                ខ.ស្ថានភាពមុខងារបច្ចុប្បន្ន
              </h6>
               <div class="row">
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">ក្របខណ្ឌ ឋានន្តរស័ក្តិ ប្រភេទ និងថ្នាក់</label>
                  <input type="text" class="form-control" v-model="form.current_framework" placeholder="ក្របខណ្ឌបច្ចុប្បន្ន" />
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">កាលបរិច្ឆេទប្តូរក្របខណ្ឌចុងក្រោយ</label>
                  <input type="date" class="form-control" v-model="form.current_appointment_date" />
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">មុខតំណែង</label>
                  <select class="form-control" v-model="form.position_id">
                    <option value="">-- ជ្រើសរើសមុខតំណែង --</option>
                    <option v-for="pos in positions" :key="pos.id" :value="pos.id">
                      {{ pos.title_kh || pos.title_en }}
                    </option>
                  </select>
                </div>
                <div class="col-md-3 form-group">
                  <label class="form-label font-weight-bold">កាលបរិច្ឆេទទទួលតំណែងចុងក្រោយ</label>
                  <input type="date" class="form-control" v-model="form.current_position_date" />
                </div>
                <div class="col-md-6 form-group">
                  <label class="form-label font-weight-bold">នាយកដ្ឋាន / Department</label>
                  <select class="form-control" v-model="form.department_id" @change="onDepartmentChange">
                    <option value="">-- ជ្រើសរើសនាយកដ្ឋាន --</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                      {{ dept.name_kh || dept.name }}
                    </option>
                  </select>
                </div>
                <div class="col-md-6 form-group">
                  <label class="form-label font-weight-bold">ការិយាល័យ / Office</label>
                  <select class="form-control" v-model="form.office_id" :disabled="!form.department_id">
                    <option value="">-- ជ្រើសរើសការិយាល័យ --</option>
                    <option v-for="off in departmentOffices" :key="off.id" :value="off.id">
                      {{ off.name_kh || off.name }}
                    </option>
                  </select>
                </div>
                
                
              </div>

              <!-- ផ្នែកទី ៥៖ ៣. ប្រវត្តិការងារ (សម្រាប់មន្ត្រីទាំងអស់) -->
              <div class="mt-4">
                <h5 class="text-success font-weight-bold mb-3 border-bottom pb-2 font-khmer">
                  <i class="fas fa-history mr-1"></i> ៣. ប្រវត្តិការងារ
                </h5>

                <!-- ក. ក្នុងវិស័យមុខងារសាធារណៈ -->
                <div class="card card-outline card-success border-top-0 border-left-0 border-right-0 shadow-none bg-light mb-4">
                  <div class="card-header bg-transparent border-bottom-0 pl-0 d-flex justify-content-between align-items-center">
                    <h6 class="text-success font-weight-bold mb-0">
                      <i class="fas fa-building mr-1"></i> ក. ក្នុងវិស័យមុខងារសាធារណៈ
                    </h6>
                    <button type="button" class="btn btn-xs btn-outline-success font-khmer rounded-pill px-3" @click="addPublicWorkRow">
                      <i class="fas fa-plus mr-1"></i> បន្ថែមជួរដេក
                    </button>
                  </div>
                  <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-sm mb-0 bg-white font-khmer">
                      <thead class="bg-light">
                        <tr>
                          <th style="width: 50px" class="text-center">ល.រ</th>
                          <th style="width: 140px">ថ្ងៃចូលការងារ</th>
                          <th style="width: 140px">ថ្ងៃបញ្ចប់ការងារ</th>
                          <th>ក្រសួង/ស្ថាប័ន</th>
                          <th>អង្គភាព</th>
                          <th>មុខតំណែង</th>
                          <th>ផ្សេងៗ</th>
                          <th style="width: 60px" class="text-center">សកម្មភាព</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="!form.public_work_histories || form.public_work_histories.length === 0">
                          <td colspan="8" class="text-center text-muted py-2">មិនទាន់មានទិន្នន័យឡើយ</td>
                        </tr>
                        <tr v-else v-for="(row, idx) in form.public_work_histories" :key="idx">
                          <td class="text-center align-middle font-weight-bold">{{ idx + 1 }}</td>
                          <td><input type="date" class="form-control form-control-sm" v-model="row.start_date" /></td>
                          <td><input type="date" class="form-control form-control-sm" v-model="row.end_date" /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.ministry" placeholder="ឧ. ក្រសួងសេដ្ឋកិច្ច..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.unit" placeholder="ឧ. អគ្គនាយកដ្ឋាន..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.position" placeholder="ឧ. អនុប្រធាន..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.note" placeholder="សម្គាល់ផ្សេងៗ" /></td>
                          <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="removePublicWorkRow(idx)">
                              <i class="fas fa-trash-alt"></i>
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- ខ. ក្នុងវិស័យឯកជន -->
                <div class="card card-outline card-success border-top-0 border-left-0 border-right-0 shadow-none bg-light mb-4">
                  <div class="card-header bg-transparent border-bottom-0 pl-0 d-flex justify-content-between align-items-center">
                    <h6 class="text-success font-weight-bold mb-0">
                      <i class="fas fa-store mr-1"></i> ខ. ក្នុងវិស័យឯកជន
                    </h6>
                    <button type="button" class="btn btn-xs btn-outline-success font-khmer rounded-pill px-3" @click="addPrivateWorkRow">
                      <i class="fas fa-plus mr-1"></i> បន្ថែមជួរដេក
                    </button>
                  </div>
                  <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-sm mb-0 bg-white font-khmer">
                      <thead class="bg-light">
                        <tr>
                          <th style="width: 50px" class="text-center">ល.រ</th>
                          <th style="width: 140px">ថ្ងៃចូលការងារ</th>
                          <th style="width: 140px">ថ្ងៃបញ្ចប់ការងារ</th>
                          <th>គ្រឹះស្ថាន/អង្គភាព</th>
                          <th>មុខតំណែង</th>
                          <th>ជំនាញ/បច្ចេកទេស</th>
                          <th>ផ្សេងៗ</th>
                          <th style="width: 60px" class="text-center">សកម្មភាព</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="!form.private_work_histories || form.private_work_histories.length === 0">
                          <td colspan="8" class="text-center text-muted py-2">មិនទាន់មានទិន្នន័យឡើយ</td>
                        </tr>
                        <tr v-else v-for="(row, idx) in form.private_work_histories" :key="idx">
                          <td class="text-center align-middle font-weight-bold">{{ idx + 1 }}</td>
                          <td><input type="date" class="form-control form-control-sm" v-model="row.start_date" /></td>
                          <td><input type="date" class="form-control form-control-sm" v-model="row.end_date" /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.company" placeholder="ឧ. ក្រុមហ៊ុន..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.position" placeholder="ឧ. អ្នកគ្រប់គ្រង..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.skill" placeholder="ឧ. អភិវឌ្ឍន៍កម្មវិធី..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.note" placeholder="សម្គាល់ផ្សេងៗ" /></td>
                          <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="removePrivateWorkRow(idx)">
                              <i class="fas fa-trash-alt"></i>
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

              </div>
              
              <!-- ផ្នែកទី ៦៖ ៤. គ្រឿងឥស្សរិយយស ប័ណ្ណសរសើរ ឬទណ្ឌកម្មវិន័យ (សម្រាប់មន្ត្រីទាំងអស់) -->
              <div class="mt-4">
                <h5 class="text-success font-weight-bold mb-3 border-bottom pb-2 font-khmer">
                  <i class="fas fa-award mr-1"></i> ៤. គ្រឿងឥស្សរិយយស ប័ណ្ណសរសើរ ឬទណ្ឌកម្មវិន័យ
                </h5>

                <!-- ក. គ្រឿងឥស្សរិយយស ប័ណ្ណសរសើរ -->
                <div class="card card-outline card-success border-top-0 border-left-0 border-right-0 shadow-none bg-light mb-4">
                  <div class="card-header bg-transparent border-bottom-0 pl-0 d-flex justify-content-between align-items-center">
                    <h6 class="text-success font-weight-bold mb-0">
                      <i class="fas fa-medal mr-1"></i> ក. គ្រឿងឥស្សរិយយស ប័ណ្ណសរសើរ
                    </h6>
                    <button type="button" class="btn btn-xs btn-outline-success font-khmer rounded-pill px-3" @click="addDecorationRow">
                      <i class="fas fa-plus mr-1"></i> បន្ថែមជួរដេក
                    </button>
                  </div>
                  <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-sm mb-0 bg-white font-khmer">
                      <thead class="bg-light">
                        <tr>
                          <th style="width: 50px" class="text-center">ល.រ</th>
                          <th style="width: 180px">លេខឯកសារ</th>
                          <th style="width: 140px">កាលបរិច្ឆេទ</th>
                          <th>ស្ថាប័ន/អង្គភាព (ស្នើសុំ)</th>
                          <th>ខ្លឹមសារ</th>
                          <th>ប្រភេទ (មេដាយ/ប័ណ្ណសរសើរ)</th>
                          <th style="width: 60px" class="text-center">សកម្មភាព</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="!form.user_decorations || form.user_decorations.length === 0">
                          <td colspan="7" class="text-center text-muted py-2">មិនទាន់មានទិន្នន័យឡើយ</td>
                        </tr>
                        <tr v-else v-for="(row, idx) in form.user_decorations" :key="idx">
                          <td class="text-center align-middle font-weight-bold">{{ idx + 1 }}</td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.document_number" placeholder="ឧ. នស/រកត/..." /></td>
                          <td><input type="date" class="form-control form-control-sm" v-model="row.date" /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.institution" placeholder="ឧ. ទីស្តីការគណៈរដ្ឋមន្ត្រី..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.content" placeholder="ខ្លឹមសារសម្រង់សម្រាយ..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.type" placeholder="ឧ. មេដាយការងារ ថ្នាក់ប្រាក់..." /></td>
                          <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="removeDecorationRow(idx)">
                              <i class="fas fa-trash-alt"></i>
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- ខ. ទណ្ឌកម្មវិន័យ -->
                <div class="card card-outline card-success border-top-0 border-left-0 border-right-0 shadow-none bg-light mb-4">
                  <div class="card-header bg-transparent border-bottom-0 pl-0 d-flex justify-content-between align-items-center">
                    <h6 class="text-success font-weight-bold mb-0">
                      <i class="fas fa-exclamation-triangle mr-1"></i> ខ. ទណ្ឌកម្មវិន័យ
                    </h6>
                    <button type="button" class="btn btn-xs btn-outline-success font-khmer rounded-pill px-3" @click="addDisciplineRow">
                      <i class="fas fa-plus mr-1"></i> បន្ថែមជួរដេក
                    </button>
                  </div>
                  <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-sm mb-0 bg-white font-khmer">
                      <thead class="bg-light">
                        <tr>
                          <th style="width: 50px" class="text-center">ល.រ</th>
                          <th style="width: 180px">លេខឯកសារ</th>
                          <th style="width: 140px">កាលបរិច្ឆេទ</th>
                          <th>ស្ថាប័ន/អង្គភាព (ស្នើសុំ)</th>
                          <th>ខ្លឹមសារ</th>
                          <th>ប្រភេទ/កម្រិតវិន័យ</th>
                          <th style="width: 60px" class="text-center">សកម្មភាព</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="!form.disciplinary_actions || form.disciplinary_actions.length === 0">
                          <td colspan="7" class="text-center text-muted py-2">មិនទាន់មានទិន្នន័យឡើយ</td>
                        </tr>
                        <tr v-else v-for="(row, idx) in form.disciplinary_actions" :key="idx">
                          <td class="text-center align-middle font-weight-bold">{{ idx + 1 }}</td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.document_number" placeholder="ឧ. លេខ..." /></td>
                          <td><input type="date" class="form-control form-control-sm" v-model="row.date" /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.institution" placeholder="ស្ថាប័នដាក់ពិន័យ..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.content" placeholder="ខ្លឹមសារសម្រង់សម្រាយ..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.type" placeholder="ឧ. ព្រមានជាលាយលក្ខណ៍អក្សរ..." /></td>
                          <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="removeDisciplineRow(idx)">
                              <i class="fas fa-trash-alt"></i>
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

              </div>

              <!-- ផ្នែកទី ៧៖ ៥. កម្រិតវប្បធម៌ទូទៅ ការបណ្តុះបណ្តាលវិជ្ជាជីវៈ និងការបណ្តុះបណ្តាលបន្ត -->
              <div class="mt-4">
                <h5 class="text-success font-weight-bold mb-3 border-bottom pb-2 font-khmer">
                  <i class="fas fa-graduation-cap mr-1"></i> ៥. កម្រិតវប្បធម៌ទូទៅ ការបណ្តុះបណ្តាលវិជ្ជាជីវៈ និងការបណ្តុះបណ្តាលបន្ត
                </h5>

                <!-- ៥.ក កម្រិតវប្បធម៌ទូទៅ -->
                <div class="card card-outline card-success border-top-0 border-left-0 border-right-0 shadow-none bg-light mb-4">
                  <div class="card-header bg-transparent border-bottom-0 pl-0 d-flex justify-content-between align-items-center">
                    <h6 class="text-success font-weight-bold mb-0">
                      <i class="fas fa-school mr-1"></i> ក. កម្រិតវប្បធម៌ទូទៅ
                    </h6>
                    <button type="button" class="btn btn-xs btn-outline-success font-khmer rounded-pill px-3" @click="addEducationRow('GENERAL_EDUCATION')">
                      <i class="fas fa-plus mr-1"></i> បន្ថែមជួរដេក
                    </button>
                  </div>
                  <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-sm mb-0 bg-white font-khmer">
                      <thead class="bg-light">
                        <tr>
                          <th style="width: 50px" class="text-center">ល.រ</th>
                          <th>កម្រិតសិក្សា (ឧ. ថ្នាក់ទី...)</th>
                          <th>គ្រឹះស្ថានសិក្សា</th>
                          <th>សញ្ញាបត្រទទួលបាន</th>
                          <th style="width: 140px">ថ្ងៃចូលសិក្សា</th>
                          <th style="width: 140px">ថ្ងៃបញ្ចប់</th>
                          <th style="width: 150px">File សញ្ញាបត្រ</th>
                          <th style="width: 60px" class="text-center">សកម្មភាព</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="!form.general_educations || form.general_educations.length === 0">
                          <td colspan="8" class="text-center text-muted py-2">មិនទាន់មានទិន្នន័យឡើយ</td>
                        </tr>
                        <tr v-else v-for="(row, idx) in form.general_educations" :key="idx">
                          <td class="text-center align-middle font-weight-bold">{{ idx + 1 }}</td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.course_level" placeholder="ឧ. ថ្នាក់ទី១២..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.institution" placeholder="ឧ. វិទ្យាល័យ..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.degree" placeholder="ឧ. សញ្ញាបត្រ..." /></td>
                          <td><input type="date" class="form-control form-control-sm" v-model="row.start_date" /></td>
                          <td><input type="date" class="form-control form-control-sm" v-model="row.end_date" /></td>
                          <td class="align-middle">
                            <div class="d-flex align-items-center">
                              <input type="file" :id="'file_input_general_' + idx" class="d-none" @change="handleEducationFileChange($event, row)" accept=".pdf,.png,.jpg,.jpeg" />
                              <button type="button" class="btn btn-xs btn-outline-secondary font-khmer mr-1" @click="triggerFileInput('general', idx)">
                                <i class="fas fa-file-upload"></i> ជ្រើសរើស
                              </button>
                              <span v-if="row.file" class="small text-truncate text-success" style="max-width: 80px;" :title="row.file.name">{{ row.file.name }}</span>
                              <span v-else-if="row.certificate_file" class="small text-truncate" style="max-width: 80px;">
                                <a :href="getFullImageUrl(row.certificate_file)" target="_blank" class="text-primary font-weight-bold">មានឯកសារ</a>
                              </span>
                              <span v-else class="small text-muted">គ្មាន File</span>
                            </div>
                          </td>
                          <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="removeEducationRow('GENERAL_EDUCATION', idx)">
                              <i class="fas fa-trash-alt"></i>
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- ៥.ខ កម្រិតសញ្ញាបត្រ -->
                <div class="card card-outline card-success border-top-0 border-left-0 border-right-0 shadow-none bg-light mb-4">
                  <div class="card-header bg-transparent border-bottom-0 pl-0 d-flex justify-content-between align-items-center">
                    <h6 class="text-success font-weight-bold mb-0">
                      <i class="fas fa-certificate mr-1"></i> ខ. កម្រិតសញ្ញាបត្រ
                    </h6>
                    <button type="button" class="btn btn-xs btn-outline-success font-khmer rounded-pill px-3" @click="addEducationRow('DEGREE')">
                      <i class="fas fa-plus mr-1"></i> បន្ថែមជួរដេក
                    </button>
                  </div>
                  <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-sm mb-0 bg-white font-khmer">
                      <thead class="bg-light">
                        <tr>
                          <th style="width: 50px" class="text-center">ល.រ</th>
                          <th>កម្រិតសញ្ញាបត្រ (ឧ. បរិញ្ញាបត្រ...)</th>
                          <th>គ្រឹះស្ថានសិក្សា/សាកលវិទ្យាល័យ</th>
                          <th>សញ្ញាបត្រទទួលបាន</th>
                          <th style="width: 140px">ថ្ងៃចូលសិក្សា</th>
                          <th style="width: 140px">ថ្ងៃបញ្ចប់</th>
                          <th style="width: 150px">File សញ្ញាបត្រ</th>
                          <th style="width: 60px" class="text-center">សកម្មភាព</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="!form.degree_educations || form.degree_educations.length === 0">
                          <td colspan="8" class="text-center text-muted py-2">មិនទាន់មានទិន្នន័យឡើយ</td>
                        </tr>
                        <tr v-else v-for="(row, idx) in form.degree_educations" :key="idx">
                          <td class="text-center align-middle font-weight-bold">{{ idx + 1 }}</td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.course_level" placeholder="ឧ. បរិញ្ញាបត្រ..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.institution" placeholder="ឧ. សាកលវិទ្យាល័យ..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.degree" placeholder="ឧ. សញ្ញាបត្របរិញ្ញាបត្រ..." /></td>
                          <td><input type="date" class="form-control form-control-sm" v-model="row.start_date" /></td>
                          <td><input type="date" class="form-control form-control-sm" v-model="row.end_date" /></td>
                          <td class="align-middle">
                            <div class="d-flex align-items-center">
                              <input type="file" :id="'file_input_degree_' + idx" class="d-none" @change="handleEducationFileChange($event, row)" accept=".pdf,.png,.jpg,.jpeg" />
                              <button type="button" class="btn btn-xs btn-outline-secondary font-khmer mr-1" @click="triggerFileInput('degree', idx)">
                                <i class="fas fa-file-upload"></i> ជ្រើសរើស
                              </button>
                              <span v-if="row.file" class="small text-truncate text-success" style="max-width: 80px;" :title="row.file.name">{{ row.file.name }}</span>
                              <span v-else-if="row.certificate_file" class="small text-truncate" style="max-width: 80px;">
                                <a :href="getFullImageUrl(row.certificate_file)" target="_blank" class="text-primary font-weight-bold">មានឯកសារ</a>
                              </span>
                              <span v-else class="small text-muted">គ្មាន File</span>
                            </div>
                          </td>
                          <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="removeEducationRow('DEGREE', idx)">
                              <i class="fas fa-trash-alt"></i>
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- ៥.គ ជំនាញឯកទេស -->
                <div class="card card-outline card-success border-top-0 border-left-0 border-right-0 shadow-none bg-light mb-4">
                  <div class="card-header bg-transparent border-bottom-0 pl-0 d-flex justify-content-between align-items-center">
                    <h6 class="text-success font-weight-bold mb-0">
                      <i class="fas fa-user-cog mr-1"></i> គ. ជំនាញឯកទេស
                    </h6>
                    <button type="button" class="btn btn-xs btn-outline-success font-khmer rounded-pill px-3" @click="addEducationRow('SPECIALIZATION')">
                      <i class="fas fa-plus mr-1"></i> បន្ថែមជួរដេក
                    </button>
                  </div>
                  <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-sm mb-0 bg-white font-khmer">
                      <thead class="bg-light">
                        <tr>
                          <th style="width: 50px" class="text-center">ល.រ</th>
                          <th>ជំនាញឯកទេស/វគ្គសិក្សា</th>
                          <th>គ្រឹះស្ថានសិក្សាបណ្តុះបណ្តាល</th>
                          <th>សញ្ញាបត្រ/វិញ្ញាបនបត្រទទួលបាន</th>
                          <th style="width: 140px">ថ្ងៃចូលសិក្សា</th>
                          <th style="width: 140px">ថ្ងៃបញ្ចប់</th>
                          <th style="width: 150px">File សញ្ញាបត្រ</th>
                          <th style="width: 60px" class="text-center">សកម្មភាព</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="!form.specialization_educations || form.specialization_educations.length === 0">
                          <td colspan="8" class="text-center text-muted py-2">មិនទាន់មានទិន្នន័យឡើយ</td>
                        </tr>
                        <tr v-else v-for="(row, idx) in form.specialization_educations" :key="idx">
                          <td class="text-center align-middle font-weight-bold">{{ idx + 1 }}</td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.course_level" placeholder="ឧ. ជំនាញឯកទេស..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.institution" placeholder="ឧ. មជ្ឈមណ្ឌល..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.degree" placeholder="ឧ. វិញ្ញាបនបត្រ..." /></td>
                          <td><input type="date" class="form-control form-control-sm" v-model="row.start_date" /></td>
                          <td><input type="date" class="form-control form-control-sm" v-model="row.end_date" /></td>
                          <td class="align-middle">
                            <div class="d-flex align-items-center">
                              <input type="file" :id="'file_input_specialization_' + idx" class="d-none" @change="handleEducationFileChange($event, row)" accept=".pdf,.png,.jpg,.jpeg" />
                              <button type="button" class="btn btn-xs btn-outline-secondary font-khmer mr-1" @click="triggerFileInput('specialization', idx)">
                                <i class="fas fa-file-upload"></i> ជ្រើសរើស
                              </button>
                              <span v-if="row.file" class="small text-truncate text-success" style="max-width: 80px;" :title="row.file.name">{{ row.file.name }}</span>
                              <span v-else-if="row.certificate_file" class="small text-truncate" style="max-width: 80px;">
                                <a :href="getFullImageUrl(row.certificate_file)" target="_blank" class="text-primary font-weight-bold">មានឯកសារ</a>
                              </span>
                              <span v-else class="small text-muted">គ្មាន File</span>
                            </div>
                          </td>
                          <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="removeEducationRow('SPECIALIZATION', idx)">
                              <i class="fas fa-trash-alt"></i>
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- ៥.ឃ វគ្គបណ្តុះបណ្តាលក្រោម១២ខែ -->
                <div class="card card-outline card-success border-top-0 border-left-0 border-right-0 shadow-none bg-light mb-4">
                  <div class="card-header bg-transparent border-bottom-0 pl-0 d-flex justify-content-between align-items-center">
                    <h6 class="text-success font-weight-bold mb-0">
                      <i class="fas fa-user-graduate mr-1"></i> ឃ. វគ្គបណ្តុះបណ្តាលក្រោម១២ខែ
                    </h6>
                    <button type="button" class="btn btn-xs btn-outline-success font-khmer rounded-pill px-3" @click="addEducationRow('SHORT_TRAINING')">
                      <i class="fas fa-plus mr-1"></i> បន្ថែមជួរដេក
                    </button>
                  </div>
                  <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-sm mb-0 bg-white font-khmer">
                      <thead class="bg-light">
                        <tr>
                          <th style="width: 50px" class="text-center">ល.រ</th>
                          <th>វគ្គសិក្សា/បណ្តុះបណ្តាល</th>
                          <th>គ្រឹះស្ថាន/អង្គភាពបណ្តុះបណ្តាល</th>
                          <th>វិញ្ញាបនបត្រទទួលបាន</th>
                          <th style="width: 140px">ថ្ងៃចូលសិក្សា</th>
                          <th style="width: 140px">ថ្ងៃបញ្ចប់</th>
                          <th style="width: 150px">File សញ្ញាបត្រ</th>
                          <th style="width: 60px" class="text-center">សកម្មភាព</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="!form.training_educations || form.training_educations.length === 0">
                          <td colspan="8" class="text-center text-muted py-2">មិនទាន់មានទិន្នន័យឡើយ</td>
                        </tr>
                        <tr v-else v-for="(row, idx) in form.training_educations" :key="idx">
                          <td class="text-center align-middle font-weight-bold">{{ idx + 1 }}</td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.course_level" placeholder="ឧ. វគ្គខ្លី..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.institution" placeholder="ឧ. អង្គភាព..." /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.degree" placeholder="ឧ. វិញ្ញាបនបត្រ..." /></td>
                          <td><input type="date" class="form-control form-control-sm" v-model="row.start_date" /></td>
                          <td><input type="date" class="form-control form-control-sm" v-model="row.end_date" /></td>
                          <td class="align-middle">
                            <div class="d-flex align-items-center">
                              <input type="file" :id="'file_input_training_' + idx" class="d-none" @change="handleEducationFileChange($event, row)" accept=".pdf,.png,.jpg,.jpeg" />
                              <button type="button" class="btn btn-xs btn-outline-secondary font-khmer mr-1" @click="triggerFileInput('training', idx)">
                                <i class="fas fa-file-upload"></i> ជ្រើសរើស
                              </button>
                              <span v-if="row.file" class="small text-truncate text-success" style="max-width: 80px;" :title="row.file.name">{{ row.file.name }}</span>
                              <span v-else-if="row.certificate_file" class="small text-truncate" style="max-width: 80px;">
                                <a :href="getFullImageUrl(row.certificate_file)" target="_blank" class="text-primary font-weight-bold">មានឯកសារ</a>
                              </span>
                              <span v-else class="small text-muted">គ្មាន File</span>
                            </div>
                          </td>
                          <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="removeEducationRow('SHORT_TRAINING', idx)">
                              <i class="fas fa-trash-alt"></i>
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

              </div>

              <!-- ផ្នែកទី ៨៖ ៦. សមត្ថភាពភាសាបរទេស (សម្រាប់មន្ត្រីទាំងអស់) -->
              <div class="mt-4">
                <h5 class="text-success font-weight-bold mb-3 border-bottom pb-2 font-khmer">
                  <i class="fas fa-language mr-1"></i> ៦. សមត្ថភាពភាសាបរទេស
                </h5>

                <div class="card card-outline card-success border-top-0 border-left-0 border-right-0 shadow-none bg-light mb-4">
                  <div class="card-header bg-transparent border-bottom-0 pl-0 d-flex justify-content-between align-items-center">
                    <h6 class="text-success font-weight-bold mb-0">
                      <i class="fas fa-globe mr-1"></i> ភាសាបរទេសដែលចេះ
                    </h6>
                    <button type="button" class="btn btn-xs btn-outline-success font-khmer rounded-pill px-3" @click="addLanguageRow">
                      <i class="fas fa-plus mr-1"></i> បន្ថែមជួរដេក
                    </button>
                  </div>
                  <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-sm mb-0 bg-white font-khmer">
                      <thead class="bg-light text-center">
                        <tr>
                          <th style="width: 50px" class="align-middle">ល.រ</th>
                          <th style="width: 250px" class="align-middle">ភាសា</th>
                          <th class="align-middle">អាន</th>
                          <th class="align-middle">សរសេរ</th>
                          <th class="align-middle">និយាយ</th>
                          <th class="align-middle">ស្តាប់</th>
                          <th style="width: 60px" class="align-middle">សកម្មភាព</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="!form.user_languages || form.user_languages.length === 0">
                          <td colspan="7" class="text-center text-muted py-2">មិនទាន់មានទិន្នន័យឡើយ</td>
                        </tr>
                        <tr v-else v-for="(row, idx) in form.user_languages" :key="idx">
                          <td class="text-center align-middle font-weight-bold">{{ idx + 1 }}</td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.language" placeholder="ឧ. អង់គ្លេស, បារាំង..." /></td>
                          <td>
                            <select class="form-control form-control-sm" v-model="row.reading">
                              <option value="">--ជ្រើសរើស--</option>
                              <option value="ល្អណាស់">ល្អណាស់</option>
                              <option value="ល្អ">ល្អ</option>
                              <option value="មធ្យម">មធ្យម</option>
                              <option value="ខ្សោយ">ខ្សោយ</option>
                            </select>
                          </td>
                          <td>
                            <select class="form-control form-control-sm" v-model="row.writing">
                              <option value="">--ជ្រើសរើស--</option>
                              <option value="ល្អណាស់">ល្អណាស់</option>
                              <option value="ល្អ">ល្អ</option>
                              <option value="មធ្យម">មធ្យម</option>
                              <option value="ខ្សោយ">ខ្សោយ</option>
                            </select>
                          </td>
                          <td>
                            <select class="form-control form-control-sm" v-model="row.speaking">
                              <option value="">--ជ្រើសរើស--</option>
                              <option value="ល្អណាស់">ល្អណាស់</option>
                              <option value="ល្អ">ល្អ</option>
                              <option value="មធ្យម">មធ្យម</option>
                              <option value="ខ្សោយ">ខ្សោយ</option>
                            </select>
                          </td>
                          <td>
                            <select class="form-control form-control-sm" v-model="row.listening">
                              <option value="">--ជ្រើសរើស--</option>
                              <option value="ល្អណាស់">ល្អណាស់</option>
                              <option value="ល្អ">ល្អ</option>
                              <option value="មធ្យម">មធ្យម</option>
                              <option value="ខ្សោយ">ខ្សោយ</option>
                            </select>
                          </td>
                          <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="removeLanguageRow(idx)">
                              <i class="fas fa-trash-alt"></i>
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- ផ្នែកទី ៩៖ ៧. ស្ថានភាពគ្រួសារ -->
              <div class="mt-4">
                <h5 class="text-success font-weight-bold mb-3 border-bottom pb-2 font-khmer">
                  <i class="fas fa-users mr-1"></i> ៧. ស្ថានភាពគ្រួសារ
                </h5>

                <!-- ក. ព័ត៌មានឪពុកម្តាយ -->
                <div class="card card-outline card-success shadow-none border mb-4 bg-light">
                  <div class="card-header bg-transparent border-bottom-0 pl-3">
                    <h6 class="text-success font-weight-bold mb-0 font-khmer">
                      <i class="fas fa-user-friends mr-1"></i> ក. ព័ត៌មានឪពុកម្តាយ
                    </h6>
                  </div>
                  <div class="card-body py-2 px-3 bg-white font-khmer">
                    <!-- ឪពុក -->
                    <h6 class="text-success font-weight-bold border-bottom pb-1 mb-3">ព័ត៌មានឪពុក</h6>
                    <div class="row">
                      <div class="form-group col-md-3">
                        <label>ឈ្មោះឪពុក</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.father_name" placeholder="ឈ្មោះខ្មែរ" />
                      </div>
                      <div class="form-group col-md-3">
                        <label>ជាអក្សរឡាតាំង</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.father_latin_name" placeholder="LATIN NAME" />
                      </div>
                      <div class="form-group col-md-2">
                        <label>ស្ថានភាព</label>
                        <select class="form-control form-control-sm" v-model="form.father_status">
                          <option value="">--ជ្រើសរើស--</option>
                          <option value="រស់">រស់</option>
                          <option value="ស្លាប់">ស្លាប់</option>
                        </select>
                      </div>
                      <div class="form-group col-md-2">
                        <label>ថ្ងៃខែឆ្នាំកំណើត</label>
                        <input type="date" class="form-control form-control-sm" v-model="form.father_dob" />
                      </div>
                      <div class="form-group col-md-2">
                        <label>សញ្ជាតិ</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.father_nationality" placeholder="ខ្មែរ" />
                      </div>
                      <div class="form-group col-md-4">
                        <label>ទីលំនៅបច្ចុប្បន្ន</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.father_address" placeholder="ភូមិ ឃុំ/សង្កាត់ ស្រុក/ក្រុង ខេត្ត" />
                      </div>
                      <div class="form-group col-md-4">
                        <label>មុខរបរ</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.father_occupation" placeholder="មុខរបរ" />
                      </div>
                      <div class="form-group col-md-4">
                        <label>ស្ថាប័ន/អង្គភាព</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.father_unit" placeholder="ស្ថាប័ន/អង្គភាព" />
                      </div>
                    </div>

                    <!-- ម្តាយ -->
                    <h6 class="text-success font-weight-bold border-bottom pb-1 mb-3 mt-3">ព័ត៌មានម្តាយ</h6>
                    <div class="row">
                      <div class="form-group col-md-3">
                        <label>ឈ្មោះម្តាយ</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.mother_name" placeholder="ឈ្មោះខ្មែរ" />
                      </div>
                      <div class="form-group col-md-3">
                        <label>ជាអក្សរឡាតាំង</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.mother_latin_name" placeholder="LATIN NAME" />
                      </div>
                      <div class="form-group col-md-2">
                        <label>ស្ថានភាព</label>
                        <select class="form-control form-control-sm" v-model="form.mother_status">
                          <option value="">--ជ្រើសរើស--</option>
                          <option value="រស់">រស់</option>
                          <option value="ស្លាប់">ស្លាប់</option>
                        </select>
                      </div>
                      <div class="form-group col-md-2">
                        <label>ថ្ងៃខែឆ្នាំកំណើត</label>
                        <input type="date" class="form-control form-control-sm" v-model="form.mother_dob" />
                      </div>
                      <div class="form-group col-md-2">
                        <label>សញ្ជាតិ</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.mother_nationality" placeholder="ខ្មែរ" />
                      </div>
                      <div class="form-group col-md-4">
                        <label>ទីលំនៅបច្ចុប្បន្ន</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.mother_address" placeholder="ភូមិ ឃុំ/សង្កាត់ ស្រុក/ក្រុង ខេត្ត" />
                      </div>
                      <div class="form-group col-md-4">
                        <label>មុខរបរ</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.mother_occupation" placeholder="មុខរបរ" />
                      </div>
                      <div class="form-group col-md-4">
                        <label>ស្ថាប័ន/អង្គភាព</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.mother_unit" placeholder="ស្ថាប័ន/អង្គភាព" />
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ខ. ព័ត៌មានបងប្អូន -->
                <div class="card card-outline card-success shadow-none border mb-4 bg-light">
                  <div class="card-header bg-transparent border-bottom-0 pl-3 d-flex justify-content-between align-items-center">
                    <h6 class="text-success font-weight-bold mb-0 font-khmer">
                      <i class="fas fa-users mr-1"></i> ខ. ព័ត៌មានបងប្អូន
                    </h6>
                    <button type="button" class="btn btn-xs btn-outline-success font-khmer rounded-pill px-3" @click="addSiblingRow">
                      <i class="fas fa-plus mr-1"></i> បន្ថែមជួរដេក
                    </button>
                  </div>
                  <div class="card-body p-0 table-responsive bg-white font-khmer">
                    <table class="table table-bordered table-sm mb-0">
                      <thead class="bg-light text-center">
                        <tr>
                          <th style="width: 50px" class="align-middle">ល.រ</th>
                          <th class="align-middle" style="width: 250px">គោត្តនាម និងនាម</th>
                          <th class="align-middle" style="width: 250px">ជាអក្សរឡាតាំង</th>
                          <th class="align-middle" style="width: 110px">ភេទ</th>
                          <th class="align-middle" style="width: 160px">ថ្ងៃខែឆ្នាំកំណើត</th>
                          <th class="align-middle">មុខរបរ (អង្គភាព)</th>
                          <th style="width: 60px" class="align-middle">សកម្មភាព</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="!form.user_siblings || form.user_siblings.length === 0">
                          <td colspan="7" class="text-center text-muted py-2">មិនទាន់មានទិន្នន័យឡើយ</td>
                        </tr>
                        <tr v-else v-for="(row, idx) in form.user_siblings" :key="'sib-' + idx">
                          <td class="text-center align-middle font-weight-bold">{{ idx + 1 }}</td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.name" /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.latin_name" /></td>
                          <td>
                            <select class="form-control form-control-sm" v-model="row.gender">
                              <option value="">--ជ្រើសរើស--</option>
                              <option value="ប្រុស">ប្រុស</option>
                              <option value="ស្រី">ស្រី</option>
                            </select>
                          </td>
                          <td><input type="date" class="form-control form-control-sm" v-model="row.dob" /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.occupation" /></td>
                          <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="removeSiblingRow(idx)">
                              <i class="fas fa-trash-alt"></i>
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- គ. ព័ត៌មានសហព័ទ្ធ (ប្តី ឬប្រពន្ធ) -->
                <div class="card card-outline card-success shadow-none border mb-4 bg-light">
                  <div class="card-header bg-transparent border-bottom-0 pl-3">
                    <h6 class="text-success font-weight-bold mb-0 font-khmer">
                      <i class="fas fa-heart mr-1"></i> គ. ព័ត៌មានសហព័ទ្ធ (ប្តី ឬប្រពន្ធ)
                    </h6>
                  </div>
                  <div class="card-body py-2 px-3 bg-white font-khmer">
                    <div class="row">
                      <div class="form-group col-md-3">
                        <label>ឈ្មោះប្តី ឬប្រពន្ធ</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.spouse_name" placeholder="ឈ្មោះខ្មែរ" />
                      </div>
                      <div class="form-group col-md-3">
                        <label>ជាអក្សរឡាតាំង</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.spouse_latin_name" placeholder="LATIN NAME" />
                      </div>
                      <div class="form-group col-md-2">
                        <label>ស្ថានភាព</label>
                        <select class="form-control form-control-sm" v-model="form.spouse_status">
                          <option value="">--ជ្រើសរើស--</option>
                          <option value="រស់">រស់</option>
                          <option value="ស្លាប់">ស្លាប់</option>
                        </select>
                      </div>
                      <div class="form-group col-md-2">
                        <label>ថ្ងៃខែឆ្នាំកំណើត</label>
                        <input type="date" class="form-control form-control-sm" v-model="form.spouse_dob" />
                      </div>
                      <div class="form-group col-md-2">
                        <label>សញ្ជាតិ</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.spouse_nationality" placeholder="ខ្មែរ" />
                      </div>
                      <div class="form-group col-md-4">
                        <label>ទីកន្លែងកំណើត</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.spouse_birthplace" placeholder="ទីកន្លែងកំណើត" />
                      </div>
                      <div class="form-group col-md-4">
                        <label>មុខរបរ</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.spouse_occupation" placeholder="មុខរបរ" />
                      </div>
                      <div class="form-group col-md-4">
                        <label>ស្ថាប័ន/អង្គភាព</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.spouse_unit" placeholder="ស្ថាប័ន/អង្គភាព" />
                      </div>
                      <div class="form-group col-md-6">
                        <label>ប្រាក់ឧបត្ថម្ភ (វាយជាអត្ថបទ)</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.spouse_allowance" placeholder="ឧ. មានប្រាក់ឧបត្ថម្ភ ឬ គ្មានប្រាក់ឧបត្ថម្ភ" />
                      </div>
                      <div class="form-group col-md-6">
                        <label>លេខទូរស័ព្ទ</label>
                        <input type="text" class="form-control form-control-sm" v-model="form.spouse_phone" placeholder="លេខទូរស័ព្ទ" />
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ឃ. ព័ត៌មានកូន -->
                <div class="card card-outline card-success shadow-none border mb-4 bg-light">
                  <div class="card-header bg-transparent border-bottom-0 pl-3 d-flex justify-content-between align-items-center">
                    <h6 class="text-success font-weight-bold mb-0 font-khmer">
                      <i class="fas fa-child mr-1"></i> ឃ. ព័ត៌មានកូន
                    </h6>
                    <button type="button" class="btn btn-xs btn-outline-success font-khmer rounded-pill px-3" @click="addChildRow">
                      <i class="fas fa-plus mr-1"></i> បន្ថែមជួរដេក
                    </button>
                  </div>
                  <div class="card-body p-0 table-responsive bg-white font-khmer">
                    <table class="table table-bordered table-sm mb-0">
                      <thead class="bg-light text-center">
                        <tr>
                          <th style="width: 50px" class="align-middle">ល.រ</th>
                          <th class="align-middle" style="width: 230px">គោត្តនាម និងនាម</th>
                          <th class="align-middle" style="width: 230px">ជាអក្សរឡាតាំង</th>
                          <th class="align-middle" style="width: 100px">ភេទ</th>
                          <th class="align-middle" style="width: 150px">ថ្ងៃខែឆ្នាំកំណើត</th>
                          <th class="align-middle">មុខរបរ</th>
                          <th class="align-middle" style="width: 220px">ប្រាក់ឧបត្ថម្ភ</th>
                          <th style="width: 60px" class="align-middle">សកម្មភាព</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="!form.user_children || form.user_children.length === 0">
                          <td colspan="8" class="text-center text-muted py-2">មិនទាន់មានទិន្នន័យឡើយ</td>
                        </tr>
                        <tr v-else v-for="(row, idx) in form.user_children" :key="'child-' + idx">
                          <td class="text-center align-middle font-weight-bold">{{ idx + 1 }}</td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.name" /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.latin_name" /></td>
                          <td>
                            <select class="form-control form-control-sm" v-model="row.gender">
                              <option value="">--ជ្រើសរើស--</option>
                              <option value="ប្រុស">ប្រុស</option>
                              <option value="ស្រី">ស្រី</option>
                            </select>
                          </td>
                          <td><input type="date" class="form-control form-control-sm" v-model="row.dob" /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.occupation" /></td>
                          <td><input type="text" class="form-control form-control-sm" v-model="row.allowance" placeholder="ឧ. គ្មានប្រាក់ឧបត្ថម្ភ" /></td>
                          <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="removeChildRow(idx)">
                              <i class="fas fa-trash-alt"></i>
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer bg-light py-2 flex-shrink-0 border-top sticky-modal-footer">
              <button type="button" class="btn btn-secondary font-khmer px-3 mr-2" @click="closeModal">បោះបង់</button>
              <button type="submit" class="btn btn-success font-khmer px-4" :disabled="saving">
                <i class="fas fa-save mr-1"></i> {{ saving ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកទិន្នន័យ' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal កំណត់សិទ្ធិប្រើប្រាស់ម៉ឺនុយ (LeftSidebar Permissions Modal) -->
    <div v-if="showPermissionModal" class="custom-modal-backdrop" @click.self="closePermissionModal">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable my-auto" role="document">
        <div class="modal-content shadow-lg border-0 font-khmer rounded-lg overflow-hidden" style="max-height: calc(100vh - 60px); display: flex; flex-direction: column;">
          <div class="modal-header text-white py-3 flex-shrink-0" style="background: linear-gradient(135deg, #112d26 0%, #1e4d41 100%);">
            <h5 class="modal-title font-weight-bold d-flex align-items-center mb-0">
              <i class="fas fa-user-shield text-warning mr-2"></i>
              កំណត់សិទ្ធិប្រើប្រាស់ម៉ឺនុយ (LeftSidebar Permissions)
            </h5>
            <button type="button" class="close text-white" @click="closePermissionModal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body p-4 bg-light flex-grow-1" style="overflow-y: auto;" v-if="permissionTargetUser">
            <!-- User Summary Header Card -->
            <div class="card border-0 shadow-sm rounded-lg mb-3">
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                  <img :src="getFullImageUrl(permissionTargetUser.profile_thumbnail || permissionTargetUser.profile_image)"
                       class="img-circle elevation-1 mr-3"
                       style="width: 50px; height: 50px; object-fit: cover;"
                       @error="onImageError">
                  <div>
                    <h5 class="font-weight-bold mb-0 text-dark">
                      {{ permissionTargetUser.name_kh || permissionTargetUser.name }}
                      <small v-if="permissionTargetUser.name_en" class="text-muted ml-1">({{ permissionTargetUser.name_en }})</small>
                    </h5>
                    <div class="text-muted small">
                      <i class="fas fa-envelope mr-1 text-secondary"></i> {{ permissionTargetUser.email }}
                      <span v-if="permissionTargetUser.employee_code" class="ml-2">
                        <i class="fas fa-id-badge mr-1 text-secondary"></i> {{ permissionTargetUser.employee_code }}
                      </span>
                    </div>
                  </div>
                </div>
                <div>
                  <span :class="permissionTargetUser.level === 'ADMIN' ? 'badge badge-danger px-3 py-2' : 'badge badge-primary px-3 py-2'">
                    <i :class="permissionTargetUser.level === 'ADMIN' ? 'fas fa-crown mr-1' : 'fas fa-user mr-1'"></i>
                    {{ permissionTargetUser.level }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Notice if target user is Admin -->
            <div v-if="permissionTargetUser.level === 'ADMIN'" class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-3">
              <i class="fas fa-exclamation-triangle fa-2x mr-3 text-warning"></i>
              <div>
                <strong class="d-block text-dark">គណនីប្រភេទ ADMIN (Superadmin):</strong>
                <span class="small text-muted">គណនីនេះទទួលបានសិទ្ធិពេញលេញលើគ្រប់ម៉ឺនុយទាំងអស់ដោយស្វ័យប្រវត្តិ។</span>
              </div>
            </div>

            <!-- Quick Action Toolbar -->
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
              <div class="font-weight-bold text-dark mb-1">
                <i class="fas fa-list-check text-success mr-1"></i>
                ម៉ឺនុយអនុញ្ញាត៖
                <span class="badge badge-success ml-1">{{ selectedPermissions.length }} / {{ allPermissionKeys.length }} ម៉ឺនុយ</span>
              </div>
              <div class="btn-group btn-group-sm mb-1">
                <button type="button" class="btn btn-outline-primary" @click="selectAllPermissions">
                  <i class="fas fa-check-double mr-1"></i> ជ្រើសរើសទាំងអស់
                </button>
                <button type="button" class="btn btn-outline-success" @click="resetToDefaultPermissions">
                  <i class="fas fa-undo mr-1"></i> លំនាំដើមមន្ត្រី
                </button>
                <button type="button" class="btn btn-outline-danger" @click="clearAllPermissions">
                  <i class="fas fa-times mr-1"></i> ដកចេញទាំងអស់
                </button>
              </div>
            </div>

            <!-- Group 1: General Menus -->
            <div class="card border-0 shadow-sm rounded-lg mb-3">
              <div class="card-header bg-white font-weight-bold text-success border-bottom-0 pb-1">
                <i class="fas fa-th-large mr-1"></i> ១. ម៉ឺនុយទូទៅ (General Menus)
              </div>
              <div class="card-body pt-2 pb-2">
                <div class="row">
                  <div class="col-md-6 mb-2" v-for="item in generalMenuItems" :key="item.key">
                    <div class="permission-item-card p-2 rounded border d-flex align-items-center justify-content-between cursor-pointer"
                         :class="{ 'border-success bg-white shadow-sm': selectedPermissions.includes(item.key), 'bg-light text-muted': !selectedPermissions.includes(item.key) }"
                         @click="togglePermission(item.key)">
                      <div class="d-flex align-items-center">
                        <div class="perm-icon mr-2 text-center" :class="item.iconClass">
                          <i :class="item.icon"></i>
                        </div>
                        <div>
                          <div class="font-weight-bold font-size-sm" :class="selectedPermissions.includes(item.key) ? 'text-dark' : 'text-secondary'">
                            {{ item.label }}
                          </div>
                          <small class="font-size-xs text-muted">{{ item.desc }}</small>
                        </div>
                      </div>
                      <div class="custom-control custom-checkbox mr-1">
                        <input type="checkbox" class="custom-control-input" :id="'perm_' + item.key"
                               :checked="selectedPermissions.includes(item.key)"
                               @change.stop="togglePermission(item.key)">
                        <label class="custom-control-label cursor-pointer" :for="'perm_' + item.key"></label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Group 2: Management Menus -->
            <div class="card border-0 shadow-sm rounded-lg mb-0">
              <div class="card-header bg-white font-weight-bold text-primary border-bottom-0 pb-1">
                <i class="fas fa-tools mr-1"></i> ២. ម៉ឺនុយការគ្រប់គ្រង (Management Menus)
              </div>
              <div class="card-body pt-2 pb-2">
                <div class="row">
                  <div class="col-md-6 mb-2" v-for="item in managementMenuItems" :key="item.key">
                    <div class="permission-item-card p-2 rounded border d-flex align-items-center justify-content-between cursor-pointer"
                         :class="{ 'border-primary bg-white shadow-sm': selectedPermissions.includes(item.key), 'bg-light text-muted': !selectedPermissions.includes(item.key) }"
                         @click="togglePermission(item.key)">
                      <div class="d-flex align-items-center">
                        <div class="perm-icon mr-2 text-center" :class="item.iconClass">
                          <i :class="item.icon"></i>
                        </div>
                        <div>
                          <div class="font-weight-bold font-size-sm" :class="selectedPermissions.includes(item.key) ? 'text-dark' : 'text-secondary'">
                            {{ item.label }}
                          </div>
                          <small class="font-size-xs text-muted">{{ item.desc }}</small>
                        </div>
                      </div>
                      <div class="custom-control custom-checkbox mr-1">
                        <input type="checkbox" class="custom-control-input" :id="'perm_' + item.key"
                               :checked="selectedPermissions.includes(item.key)"
                               @change.stop="togglePermission(item.key)">
                        <label class="custom-control-label cursor-pointer" :for="'perm_' + item.key"></label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer bg-white py-2 justify-content-between flex-shrink-0 border-top">
            <span class="text-muted small">
              <i class="fas fa-info-circle mr-1 text-info"></i> សិទ្ធិនឹងមានសុពលភាពភ្លាមៗបន្ទាប់ពីរៀបចំរួច
            </span>
            <div>
              <button type="button" class="btn btn-secondary font-khmer mr-2" @click="closePermissionModal">បោះបង់</button>
              <button type="button" class="btn btn-success font-khmer px-4" :disabled="savingPermissions" @click="savePermissions">
                <i class="fas fa-save mr-1"></i> {{ savingPermissions ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកសិទ្ធិ' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import Swal from 'sweetalert2';
import {
  apiGetUsers,
  apiReadUser,
  apiCreateUser,
  apiUpdateUser,
  apiToggleUserStatus,
  apiDeleteUser,
  apiUpdateUserPermissions,
} from '@/functions/api/user';
import { apiGetDepartments } from '@/functions/api/department';
import { apiGetOfficesByDepartment } from '@/functions/api/office';
import { apiGetPositions } from '@/functions/api/position';

const defaultAvatar = 'https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg';

const users = ref([]);
const departments = ref([]);
const departmentOffices = ref([]);
const positions = ref([]);
const loading = ref(false);
const saving = ref(false);
const showModal = ref(false);
const isEditMode = ref(false);
const currentUserId = ref(null);
const selectedFile = ref(null);
const imagePreviewUrl = ref(null);

// --- Permission Modal State & Logic ---
const showPermissionModal = ref(false);
const permissionTargetUser = ref(null);
const selectedPermissions = ref([]);
const savingPermissions = ref(false);

const generalMenuItems = [
  { key: 'dashboard', label: 'Dashboard', desc: 'ផ្ទាំងគ្រប់គ្រងទិន្នន័យទូទៅ', icon: 'fas fa-tachometer-alt', iconClass: 'text-info' },
  { key: 'profile', label: 'ព័ត៌មានផ្ទាល់ខ្លួន', desc: 'មើល និងព្រីនប្រវត្តិរូបផ្ទាល់ខ្លួន', icon: 'fas fa-id-card', iconClass: 'text-success' },
  { key: 'my-attendances', label: 'វត្តមានរបស់ខ្ញុំ', desc: 'កត់ត្រា និងតាមដានវត្តមានផ្ទាល់ខ្លួន', icon: 'fas fa-calendar-check', iconClass: 'text-primary' },
  { key: 'document-templates', label: 'គំរូឯកសារ', desc: 'ទាញយកទម្រង់គំរូឯកសារផ្សេងៗ', icon: 'fas fa-folder-open', iconClass: 'text-warning' },
  { key: 'work-schedules', label: 'កាលវិភាគការងារ', desc: 'កត់ត្រា និងគ្រប់គ្រងកាលវិភាគ/កិច្ចប្រជុំ', icon: 'fas fa-calendar-alt', iconClass: 'text-warning' },
  { key: 'meeting-rooms', label: 'បន្ទប់ប្រជុំ & ការកក់', desc: 'មើលកាលវិភាគ និងស្នើសុំកក់បន្ទប់ប្រជុំ', icon: 'fas fa-door-open', iconClass: 'text-info' },
  { key: 'weekly-reports', label: 'របាយការណ៍ប្រចាំសប្តាហ៍', desc: 'កត់ត្រា និងតាមដានរបាយការណ៍/កិច្ចការប្រចាំសប្តាហ៍', icon: 'fas fa-clipboard-list', iconClass: 'text-info' },
];

const managementMenuItems = [
  { key: 'manage-document-templates', label: 'គ្រប់គ្រងគំរូឯកសារ', desc: 'បន្ថែម កែប្រែ ឬលុបគំរូឯកសារ', icon: 'fas fa-file-invoice', iconClass: 'text-warning' },
  { key: 'manage-meeting-rooms', label: 'គ្រប់គ្រងបន្ទប់ប្រជុំ', desc: 'ពិនិត្យ អនុម័ត/បដិសេធ និងចាត់ចែងបន្ទប់ប្រជុំ', icon: 'fas fa-tasks', iconClass: 'text-success' },
  { key: 'users', label: 'អ្នកប្រើប្រាស់ / មន្ត្រី', desc: 'គ្រប់គ្រងទិន្នន័យមន្ត្រីទាំងអស់', icon: 'fas fa-users-cog', iconClass: 'text-primary' },
  { key: 'attendances', label: 'គ្រប់គ្រងវត្តមាន', desc: 'គ្រប់គ្រង កត់ត្រា និង Import វត្តមាន', icon: 'fas fa-calendar-alt', iconClass: 'text-success' },
  { key: 'departments', label: 'នាយកដ្ឋាន', desc: 'គ្រប់គ្រងបញ្ជីនាយកដ្ឋាន', icon: 'fas fa-building', iconClass: 'text-secondary' },
  { key: 'divisions', label: 'ការិយាល័យ', desc: 'គ្រប់គ្រងបញ្ជីការិយាល័យ', icon: 'fas fa-door-closed', iconClass: 'text-info' },
  { key: 'positions', label: 'តួនាទី', desc: 'គ្រប់គ្រងបញ្ជីតួនាទីមន្ត្រី', icon: 'fas fa-id-badge', iconClass: 'text-danger' },
  { key: 'backups', label: 'Backups', desc: 'ទាញយក និងគ្រប់គ្រង Backup', icon: 'fas fa-database', iconClass: 'text-dark' },
];

const allPermissionKeys = [
  'dashboard', 'profile', 'my-attendances', 'document-templates', 'work-schedules', 'meeting-rooms', 'weekly-reports',
  'manage-document-templates', 'manage-meeting-rooms', 'users', 'attendances',
  'departments', 'divisions', 'positions', 'backups'
];

const openPermissionModal = (user) => {
  if (!userStore.isAdmin && user.level === 'ADMIN') {
    Swal.fire({
      icon: 'warning',
      title: 'មិនមានសិទ្ធិ',
      text: 'មិនអាចកែប្រែសិទ្ធិរបស់គណនី Admin បានឡើយ!',
      confirmButtonText: 'យល់ព្រម'
    });
    return;
  }
  permissionTargetUser.value = user;
  if (user.level === 'ADMIN') {
    selectedPermissions.value = [...allPermissionKeys];
  } else if (Array.isArray(user.permissions) && user.permissions.length > 0) {
    selectedPermissions.value = [...user.permissions];
  } else {
    selectedPermissions.value = ['profile', 'my-attendances', 'document-templates', 'work-schedules', 'meeting-rooms', 'weekly-reports'];
  }
  showPermissionModal.value = true;
};

const closePermissionModal = () => {
  showPermissionModal.value = false;
  permissionTargetUser.value = null;
  selectedPermissions.value = [];
};

const togglePermission = (key) => {
  const idx = selectedPermissions.value.indexOf(key);
  if (idx > -1) {
    selectedPermissions.value.splice(idx, 1);
  } else {
    selectedPermissions.value.push(key);
  }
};

const selectAllPermissions = () => {
  selectedPermissions.value = [...allPermissionKeys];
};

const resetToDefaultPermissions = () => {
  selectedPermissions.value = ['profile', 'my-attendances', 'document-templates', 'work-schedules', 'meeting-rooms', 'weekly-reports'];
};

const clearAllPermissions = () => {
  selectedPermissions.value = [];
};

const savePermissions = async () => {
  if (!permissionTargetUser.value) return;
  savingPermissions.value = true;
  try {
    await apiUpdateUserPermissions(permissionTargetUser.value.id, selectedPermissions.value);
    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ',
      text: 'បានកំណត់សិទ្ធិប្រើប្រាស់ដោយជោគជ័យ!',
      timer: 1800,
      showConfirmButton: false,
    });
    // Update permissions in local users list
    const idx = users.value.findIndex(u => u.id === permissionTargetUser.value.id);
    if (idx > -1) {
      users.value[idx].permissions = [...selectedPermissions.value];
    }
    closePermissionModal();
  } catch (error) {
    console.error('Error saving permissions:', error);
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ',
      text: error.response?.data?.message || 'មានបញ្ហាក្នុងការរក្សាទុកសិទ្ធិ!',
    });
  } finally {
    savingPermissions.value = false;
  }
};

const filters = reactive({
  keyword: '',
  employee_type: '',
  status: '',
  officer_status: '',
  level: '',
});

const officerStats = reactive({
  ALL: 0,
  ACTIVE: 0,
  RESIGNED: 0,
  RETIRED: 0,
  TRANSFERRED: 0,
  SUSPENDED: 0,
  OTHER: 0,
});

const pagination = reactive({
  currentPage: 1,
  lastPage: 1,
  perPage: 15,
  total: 0,
});

const defaultFormData = {
  name: '',
  name_kh: '',
  name_en: '',
  email: '',
  password: '',
  level: 'USER',
  status: 'ENABLED',
  department_id: '',
  office_id: '',
  position_id: '',
  employee_code: '',
  mef_card_number: '',
  employee_type: 'CIVIL_SERVICE',
  officer_status: 'ACTIVE',
  officer_status_date: '',
  officer_status_reason: '',
  gender: 'MALE',
  marital_status: 'SINGLE',
  dob: '',
  birth_place: '',
  current_address: '',
  phone: '',
  national_id_number: '',
  national_id_expired_date: '',
  national_id_file: null,
  national_id_file_preview: '',
  passport_number: '',
  passport_expired_date: '',
  passport_file: null,
  passport_file_preview: '',
  // ១. ព័ត៌មានបម្រើการងាររដ្ឋដំបូង (បន្ថែមថ្មី)
  first_service_date: '',
  first_appointment_date: '',
  initial_framework: '',
  initial_position: '',
  initial_ministry: '',
  initial_unit: '',
  initial_department: '',
  initial_office: '',

  // ២. ស្ថានភាពមុខងារបច្ចុប្បន្ន (បន្ថែមថ្មី)
  current_framework: '',
  current_appointment_date: '',
  current_position_date: '',
  public_work_histories: [],
  private_work_histories: [],
  user_decorations: [],
  disciplinary_actions: [],
  general_educations: [],
  degree_educations: [],
  specialization_educations: [],
  training_educations: [],
  user_languages: [],

  // ៧. ស្ថានភាពគ្រួសារ
  father_name: '',
  father_latin_name: '',
  father_status: '',
  father_dob: '',
  father_nationality: '',
  father_address: '',
  father_occupation: '',
  father_unit: '',

  mother_name: '',
  mother_latin_name: '',
  mother_status: '',
  mother_dob: '',
  mother_nationality: '',
  mother_address: '',
  mother_occupation: '',
  mother_unit: '',

  spouse_name: '',
  spouse_latin_name: '',
  spouse_status: '',
  spouse_dob: '',
  spouse_nationality: '',
  spouse_birthplace: '',
  spouse_occupation: '',
  spouse_unit: '',
  spouse_allowance: '',
  spouse_phone: '',

  user_siblings: [],
  user_children: [],
};

const form = reactive({ ...defaultFormData });

let searchTimeout = null;

const getFullImageUrl = (path) => {
  if (!path) return defaultAvatar;

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
  event.target.src = defaultAvatar;
};

const onFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedFile.value = file;
    imagePreviewUrl.value = URL.createObjectURL(file);
  }
};

const onNationalIdFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.national_id_file = file;
  } else {
    form.national_id_file = null;
  }
};

const onPassportFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.passport_file = file;
  } else {
    form.passport_file = null;
  }
};

const formatGender = (gender) => {
  if (!gender) return '---';
  const g = String(gender).trim().toUpperCase();
  if (g === 'MALE' || g === 'M') return 'ប្រុស';
  if (g === 'FEMALE' || g === 'F') return 'ស្រី';
  return gender;
};

const getGenderBadge = (gender) => {
  if (!gender) return 'text-muted';
  const g = String(gender).trim().toUpperCase();
  if (g === 'MALE' || g === 'M') return 'text-primary font-weight-500';
  if (g === 'FEMALE' || g === 'F') return 'text-danger font-weight-500';
  return 'text-muted';
};

const formatEmployeeType = (type) => {
  switch (type) {
    case 'CIVIL_SERVICE':
      return 'មន្ត្រីមុខងារសាធារណៈ';
    case 'STATUTORY':
      return 'មន្ត្រីលក្ខន្តិកៈ';
    case 'CONTRACT':
      return 'មន្ត្រីជាប់កិច្ចសន្យា';
    case 'OTHER':
      return 'ផ្សេងៗ';
    default:
      return type || '---';
  }
};

const getEmployeeTypeBadge = (type) => {
  switch (type) {
    case 'CIVIL_SERVICE':
      return 'badge badge-primary';
    case 'STATUTORY':
      return 'badge badge-info';
    case 'CONTRACT':
      return 'badge badge-warning text-dark';
    case 'OTHER':
      return 'badge badge-secondary';
    default:
      return 'badge badge-light border';
  }
};

const fetchDropdowns = async () => {
  try {
    const [deptRes, posRes] = await Promise.allSettled([
      apiGetDepartments(),
      apiGetPositions(),
    ]);
    if (deptRes.status === 'fulfilled') {
      departments.value = deptRes.value.data.data || deptRes.value.data || [];
    }
    if (posRes.status === 'fulfilled') {
      positions.value = posRes.value.data.data || posRes.value.data || [];
    }
  } catch (error) {
    console.error('Error fetching dropdowns:', error);
  }
};

const onDepartmentChange = async () => {
  form.office_id = '';
  departmentOffices.value = [];
  if (form.department_id) {
    try {
      const res = await apiGetOfficesByDepartment(form.department_id);
      departmentOffices.value = res.data.data || res.data || [];
    } catch (error) {
      console.error('Error fetching offices:', error);
    }
  }
};

const fetchUsers = async (page = 1) => {
  loading.value = true;
  try {
    const res = await apiGetUsers({
      page,
      per_page: pagination.perPage,
      keyword: filters.keyword,
      employee_type: filters.employee_type,
      status: filters.status,
      officer_status: filters.officer_status,
      level: filters.level,
    });

    users.value = res.data.users || res.data.data || [];

    if (res.data.meta) {
      pagination.currentPage = res.data.meta.current_page;
      pagination.lastPage = res.data.meta.last_page;
      pagination.perPage = res.data.meta.per_page;
      pagination.total = res.data.meta.total;
    }

    if (res.data.officer_status_counts) {
      Object.assign(officerStats, res.data.officer_status_counts);
    }
  } catch (error) {
    Swal.fire('Error', error.response?.data?.message || 'មិនអាចទាញយកបញ្ជីមន្ត្រីបានឡើយ', 'error');
  } finally {
    loading.value = false;
  }
};

const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchUsers(1);
  }, 400);
};

const resetFilters = () => {
  filters.keyword = '';
  filters.employee_type = '';
  filters.status = '';
  filters.officer_status = '';
  filters.level = '';
  fetchUsers(1);
};

const filterByOfficerStatus = (status) => {
  filters.officer_status = status;
  fetchUsers(1);
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.lastPage) {
    fetchUsers(page);
  }
};

const openCreateModal = () => {
  isEditMode.value = false;
  currentUserId.value = null;
  selectedFile.value = null;
  imagePreviewUrl.value = null;
  departmentOffices.value = [];
  Object.assign(form, defaultFormData);
  showModal.value = true;
};

const openEditModal = async (user) => {
  isEditMode.value = true;
  currentUserId.value = user.id;
  selectedFile.value = null;

  try {
    const res = await apiReadUser(user.id);
    const u = res.data.user || res.data;

    imagePreviewUrl.value = getFullImageUrl(u.profile_thumbnail || u.profile_image);

    if (u.department_id) {
      try {
        const offRes = await apiGetOfficesByDepartment(u.department_id);
        departmentOffices.value = offRes.data.data || offRes.data || [];
      } catch (err) {
        departmentOffices.value = [];
      }
    } else {
      departmentOffices.value = [];
    }

    let mappedGender = 'MALE';
    if (u.gender) {
      const g = String(u.gender).trim().toUpperCase();
      if (g === 'FEMALE' || g === 'F') {
        mappedGender = 'FEMALE';
      } else {
        mappedGender = 'MALE';
      }
    }

    let mappedMarital = 'SINGLE';
    if (u.marital_status) {
      const ms = String(u.marital_status).trim().toUpperCase();
      if (['SINGLE', 'MARRIED', 'DIVORCED'].includes(ms)) {
        mappedMarital = ms;
      }
    }

    Object.assign(form, {
      name: u.name || '',
      name_kh: u.name_kh || u.name || '',
      name_en: u.name_en || '',
      email: u.email || '',
      password: '',
      level: u.level || 'USER',
      status: u.status || 'ENABLED',
      department_id: u.department_id ? Number(u.department_id) : '',
      office_id: u.office_id ? Number(u.office_id) : '',
      position_id: u.position_id ? Number(u.position_id) : '',
      employee_code: u.employee_code || '',
      mef_card_number: u.mef_card_number || '',
      employee_type: u.employee_type || 'CIVIL_SERVICE',
      officer_status: u.officer_status || 'ACTIVE',
      officer_status_date: u.officer_status_date ? String(u.officer_status_date).split('T')[0] : '',
      officer_status_reason: u.officer_status_reason || '',
      gender: mappedGender,
      marital_status: mappedMarital,
      dob: u.dob ? String(u.dob).split('T')[0] : '',
      birth_place: u.birth_place || '',
      current_address: u.current_address || '',
      phone: u.phone || '',
      national_id_number: u.national_id_number || '',
      national_id_expired_date: u.national_id_expired_date ? String(u.national_id_expired_date).split('T')[0] : '',
      national_id_file: null,
      national_id_file_preview: u.national_id_file || '',
      passport_number: u.passport_number || '',
      passport_expired_date: u.passport_expired_date ? String(u.passport_expired_date).split('T')[0] : '',
      passport_file: null,
      passport_file_preview: u.passport_file || '',
    // ✅ បន្ថែម Fields ផ្នែកទី ២ ចូលទីនេះ ដើម្បីឱ្យ Form ចាប់យកទិន្នន័យមកបង្ហាញពេល Edit:
      first_service_date: u.first_service_date ? String(u.first_service_date).split('T')[0] : '',
      first_appointment_date: u.first_appointment_date ? String(u.first_appointment_date).split('T')[0] : '',
      initial_framework: u.initial_framework || '',
      initial_position: u.initial_position || '',
      initial_ministry: u.initial_ministry || '',
      initial_unit: u.initial_unit || '',
      initial_department: u.initial_department || '',
      initial_office: u.initial_office || '',
      
      current_framework: u.current_framework || '',
      current_appointment_date: u.current_appointment_date ? String(u.current_appointment_date).split('T')[0] : '',
      current_position_date: u.current_position_date ? String(u.current_position_date).split('T')[0] : '',
      public_work_histories: u.public_work_histories ? u.public_work_histories.map(wh => ({
        id: wh.id,
        start_date: wh.start_date ? String(wh.start_date).split('T')[0] : '',
        end_date: wh.end_date ? String(wh.end_date).split('T')[0] : '',
        ministry: wh.ministry || '',
        unit: wh.unit || '',
        position: wh.position || '',
        note: wh.note || ''
      })) : [],
      private_work_histories: u.private_work_histories ? u.private_work_histories.map(pwh => ({
        id: pwh.id,
        start_date: pwh.start_date ? String(pwh.start_date).split('T')[0] : '',
        end_date: pwh.end_date ? String(pwh.end_date).split('T')[0] : '',
        company: pwh.company || '',
        position: pwh.position || '',
        skill: pwh.skill || '',
        note: pwh.note || ''
      })) : [],
      user_decorations: u.user_decorations ? u.user_decorations.map(d => ({
        id: d.id,
        document_number: d.document_number || '',
        date: d.date ? String(d.date).split('T')[0] : '',
        institution: d.institution || '',
        content: d.content || '',
        type: d.type || ''
      })) : [],
      disciplinary_actions: u.disciplinary_actions ? u.disciplinary_actions.map(da => ({
        id: da.id,
        document_number: da.document_number || '',
        date: da.date ? String(da.date).split('T')[0] : '',
        institution: da.institution || '',
        content: da.content || '',
        type: da.type || ''
      })) : [],
      general_educations: (u.educations || []).filter(e => e.category === 'GENERAL_EDUCATION').map(e => ({
        id: e.id,
        category: e.category,
        course_level: e.course_level || '',
        institution: e.institution || '',
        degree: e.degree || '',
        start_date: e.start_date ? String(e.start_date).split('T')[0] : '',
        end_date: e.end_date ? String(e.end_date).split('T')[0] : '',
        certificate_file: e.certificate_file || ''
      })),
      degree_educations: (u.educations || []).filter(e => e.category === 'DEGREE').map(e => ({
        id: e.id,
        category: e.category,
        course_level: e.course_level || '',
        institution: e.institution || '',
        degree: e.degree || '',
        start_date: e.start_date ? String(e.start_date).split('T')[0] : '',
        end_date: e.end_date ? String(e.end_date).split('T')[0] : '',
        certificate_file: e.certificate_file || ''
      })),
      specialization_educations: (u.educations || []).filter(e => e.category === 'SPECIALIZATION').map(e => ({
        id: e.id,
        category: e.category,
        course_level: e.course_level || '',
        institution: e.institution || '',
        degree: e.degree || '',
        start_date: e.start_date ? String(e.start_date).split('T')[0] : '',
        end_date: e.end_date ? String(e.end_date).split('T')[0] : '',
        certificate_file: e.certificate_file || ''
      })),
      training_educations: (u.educations || []).filter(e => e.category === 'SHORT_TRAINING').map(e => ({
        id: e.id,
        category: e.category,
        course_level: e.course_level || '',
        institution: e.institution || '',
        degree: e.degree || '',
        start_date: e.start_date ? String(e.start_date).split('T')[0] : '',
        end_date: e.end_date ? String(e.end_date).split('T')[0] : '',
        certificate_file: e.certificate_file || ''
      })),
      user_languages: (u.languages || []).map(l => ({
        id: l.id,
        language: l.language || '',
        reading: l.reading || '',
        writing: l.writing || '',
        speaking: l.speaking || '',
        listening: l.listening || ''
      })),

      // ៧. ស្ថានភាពគ្រួសារ
      father_name: u.father_name || '',
      father_latin_name: u.father_latin_name || '',
      father_status: u.father_status || '',
      father_dob: u.father_dob ? String(u.father_dob).split('T')[0] : '',
      father_nationality: u.father_nationality || '',
      father_address: u.father_address || '',
      father_occupation: u.father_occupation || '',
      father_unit: u.father_unit || '',

      mother_name: u.mother_name || '',
      mother_latin_name: u.mother_latin_name || '',
      mother_status: u.mother_status || '',
      mother_dob: u.mother_dob ? String(u.mother_dob).split('T')[0] : '',
      mother_nationality: u.mother_nationality || '',
      mother_address: u.mother_address || '',
      mother_occupation: u.mother_occupation || '',
      mother_unit: u.mother_unit || '',

      spouse_name: u.spouse_name || '',
      spouse_latin_name: u.spouse_latin_name || '',
      spouse_status: u.spouse_status || '',
      spouse_dob: u.spouse_dob ? String(u.spouse_dob).split('T')[0] : '',
      spouse_nationality: u.spouse_nationality || '',
      spouse_birthplace: u.spouse_birthplace || '',
      spouse_occupation: u.spouse_occupation || '',
      spouse_unit: u.spouse_unit || '',
      spouse_allowance: u.spouse_allowance || '',
      spouse_phone: u.spouse_phone || '',

      user_siblings: (u.siblings || []).map(s => ({
        id: s.id,
        name: s.name || '',
        latin_name: s.latin_name || '',
        gender: s.gender || '',
        dob: s.dob ? String(s.dob).split('T')[0] : '',
        occupation: s.occupation || ''
      })),
      user_children: (u.children || []).map(c => ({
        id: c.id,
        name: c.name || '',
        latin_name: c.latin_name || '',
        gender: c.gender || '',
        dob: c.dob ? String(c.dob).split('T')[0] : '',
        occupation: c.occupation || '',
        allowance: c.allowance || ''
      })),
    });
    showModal.value = true;
  } catch (error) {
    Swal.fire('បរាជ័យ', error.response?.data?.message || 'មិនអាចទាញយកព័ត៌មានលម្អិតបានឡើយ', 'error');
  }
};

const closeModal = () => {
  showModal.value = false;
};

const addPublicWorkRow = () => {
  if (!form.public_work_histories) form.public_work_histories = [];
  form.public_work_histories.push({ start_date: '', end_date: '', ministry: '', unit: '', position: '', note: '' });
};
const removePublicWorkRow = (index) => {
  form.public_work_histories.splice(index, 1);
};

const addPrivateWorkRow = () => {
  if (!form.private_work_histories) form.private_work_histories = [];
  form.private_work_histories.push({ start_date: '', end_date: '', company: '', position: '', skill: '', note: '' });
};
const removePrivateWorkRow = (index) => {
  form.private_work_histories.splice(index, 1);
};

const addDecorationRow = () => {
  if (!form.user_decorations) form.user_decorations = [];
  form.user_decorations.push({ document_number: '', date: '', institution: '', content: '', type: '' });
};
const removeDecorationRow = (index) => {
  form.user_decorations.splice(index, 1);
};

const addDisciplineRow = () => {
  if (!form.disciplinary_actions) form.disciplinary_actions = [];
  form.disciplinary_actions.push({ document_number: '', date: '', institution: '', content: '', type: '' });
};
const removeDisciplineRow = (index) => {
  form.disciplinary_actions.splice(index, 1);
};

const addEducationRow = (category) => {
  if (category === 'GENERAL_EDUCATION') {
    if (!form.general_educations) form.general_educations = [];
    form.general_educations.push({ category: 'GENERAL_EDUCATION', course_level: '', institution: '', degree: '', start_date: '', end_date: '', file: null, certificate_file: '' });
  } else if (category === 'DEGREE') {
    if (!form.degree_educations) form.degree_educations = [];
    form.degree_educations.push({ category: 'DEGREE', course_level: '', institution: '', degree: '', start_date: '', end_date: '', file: null, certificate_file: '' });
  } else if (category === 'SPECIALIZATION') {
    if (!form.specialization_educations) form.specialization_educations = [];
    form.specialization_educations.push({ category: 'SPECIALIZATION', course_level: '', institution: '', degree: '', start_date: '', end_date: '', file: null, certificate_file: '' });
  } else if (category === 'SHORT_TRAINING') {
    if (!form.training_educations) form.training_educations = [];
    form.training_educations.push({ category: 'SHORT_TRAINING', course_level: '', institution: '', degree: '', start_date: '', end_date: '', file: null, certificate_file: '' });
  }
};

const removeEducationRow = (category, index) => {
  if (category === 'GENERAL_EDUCATION') {
    form.general_educations.splice(index, 1);
  } else if (category === 'DEGREE') {
    form.degree_educations.splice(index, 1);
  } else if (category === 'SPECIALIZATION') {
    form.specialization_educations.splice(index, 1);
  } else if (category === 'SHORT_TRAINING') {
    form.training_educations.splice(index, 1);
  }
};

const triggerFileInput = (category, index) => {
  const el = document.getElementById(`file_input_${category}_${index}`);
  if (el) el.click();
};

const handleEducationFileChange = (event, row) => {
  const file = event.target.files[0];
  if (file) {
    row.file = file;
  }
};

const addLanguageRow = () => {
  if (!form.user_languages) form.user_languages = [];
  form.user_languages.push({ language: '', reading: '', writing: '', speaking: '', listening: '' });
};
const removeLanguageRow = (index) => {
  form.user_languages.splice(index, 1);
};

const addSiblingRow = () => {
  if (!form.user_siblings) form.user_siblings = [];
  form.user_siblings.push({ name: '', latin_name: '', gender: '', dob: '', occupation: '' });
};
const removeSiblingRow = (index) => {
  form.user_siblings.splice(index, 1);
};

const addChildRow = () => {
  if (!form.user_children) form.user_children = [];
  form.user_children.push({ name: '', latin_name: '', gender: '', dob: '', occupation: '', allowance: '' });
};
const removeChildRow = (index) => {
  form.user_children.splice(index, 1);
};

const submitForm = async () => {
  saving.value = true;
  form.name = form.name_kh || form.name_en;

  const payload = new FormData();

  Object.keys(form).forEach((key) => {
    if (['general_educations', 'degree_educations', 'specialization_educations', 'training_educations'].includes(key)) {
      return;
    }
    const val = form[key];
    // កែសម្រួលត្រង់នេះ៖ ដក && val !== '' ចេញ
    if (val !== null && val !== undefined) {
      if (['public_work_histories', 'private_work_histories', 'user_decorations', 'disciplinary_actions', 'user_languages', 'user_siblings', 'user_children'].includes(key)) {
        payload.append(key, JSON.stringify(val));
      } else {
        payload.append(key, val);
      }
    }
  });

  // ផ្ដុំ និងបំប្លែងទិន្នន័យផ្នែកសិក្សា/បណ្តុះបណ្តាល
  const combinedEducations = [
    ...(form.general_educations || []),
    ...(form.degree_educations || []),
    ...(form.specialization_educations || []),
    ...(form.training_educations || [])
  ];

  const cleanEducations = combinedEducations.map(e => ({
    id: e.id,
    category: e.category,
    course_level: e.course_level,
    institution: e.institution,
    degree: e.degree,
    start_date: e.start_date,
    end_date: e.end_date,
    certificate_file: e.certificate_file
  }));

  payload.append('user_educations', JSON.stringify(cleanEducations));

  combinedEducations.forEach((e, idx) => {
    if (e.file) {
      payload.append(`education_file_${idx}`, e.file);
    }
  });

  if (selectedFile.value) {
    payload.append('profile_image', selectedFile.value);
  }

  try {
    if (isEditMode.value) {
      payload.append('_method', 'PUT');
      const res = await apiUpdateUser(currentUserId.value, payload);
      Swal.fire('ជោគជ័យ', res.data?.message || 'ព័ត៌មានមន្ត្រីត្រូវបានកែសម្រួលរួចរាល់', 'success');
    } else {
      const res = await apiCreateUser(payload);
      Swal.fire('ជោគជ័យ', res.data?.message || 'បានបញ្ចូលមន្ត្រីថ្មីដោយជោគជ័យ', 'success');
    }
    closeModal();
    fetchUsers(pagination.currentPage);
  } catch (error) {
    const errors = error.response?.data?.errors;
    let errorMsg = error.response?.data?.message || 'មានបញ្ហាក្នុងការរក្សាទុក';
    if (errors) {
      errorMsg = Object.values(errors).flat().join('<br>');
    }
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ',
      html: errorMsg,
    });
  } finally {
    saving.value = false;
  }
};

const toggleStatus = async (user) => {
  const nextStatus = user.status === 'ENABLED' ? 'DISABLED' : 'ENABLED';
  const actionText = nextStatus === 'ENABLED' ? 'បើកដំណើរការ' : 'ផ្អាកដំណើរការ';

  const result = await Swal.fire({
    title: `តើអ្នកចង់${actionText}គណនីនេះមែនទេ?`,
    text: `គណនី ${user.email} នឹងត្រូវប្តូរទៅជា ${nextStatus}`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: nextStatus === 'ENABLED' ? '#28a745' : '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: `យល់ព្រម ${actionText}`,
    cancelButtonText: 'បោះបង់',
  });

  if (result.isConfirmed) {
    try {
      await apiToggleUserStatus(user.id);
      user.status = nextStatus;
      Swal.fire('ជោគជ័យ', `គណនីត្រូវបាន${actionText}រួចរាល់`, 'success');
    } catch (error) {
      Swal.fire('បរាជ័យ', error.response?.data?.message || 'មិនអាចផ្លាស់ប្តូរស្ថានភាពបានឡើយ', 'error');
    }
  }
};

const removeUser = async (id) => {
  const result = await Swal.fire({
    title: 'តើអ្នកប្រាកដថាចង់លុបទិន្នន័យនេះ?',
    text: 'ទិន្នន័យដែលបានលុបមិនអាចទាញយកមកវិញបានទេ!',
    icon: 'error',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'យល់ព្រមលុប',
    cancelButtonText: 'បោះបង់',
  });

  if (result.isConfirmed) {
    try {
      await apiDeleteUser(id);
      Swal.fire('ជោគជ័យ', 'ទិន្នន័យត្រូវបានលុបរួចរាល់', 'success');
      fetchUsers(pagination.currentPage);
    } catch (error) {
      Swal.fire('បរាជ័យ', error.response?.data?.message || 'មិនអាចលុបទិន្នន័យបានឡើយ', 'error');
    }
  }
};


import { useRouter } from 'vue-router';
const router = useRouter();
const printUserProfile = (userId) => {
  router.push({ name: 'user.detail', params: { id: userId } });
};

// មុខងារជំនួយសម្រាប់ស្ថានភាពមន្ត្រី និងរយៈពេលធ្វើការងារ
const formatOfficerStatus = (status) => {
  const map = {
    ACTIVE: 'កំពុងបម្រើការ',
    RESIGNED: 'លាឈប់',
    RETIRED: 'ចូលនិវត្តន៍',
    TRANSFERRED: 'ផ្លាស់ប្តូរ',
    SUSPENDED: 'ព្យួរការងារ',
    OTHER: 'ផ្សេងៗ',
  };
  return map[status] || 'កំពុងបម្រើការ';
};

const getOfficerStatusBadgeClass = (status) => {
  const map = {
    ACTIVE: 'badge badge-success px-2 py-1',
    RESIGNED: 'badge badge-danger px-2 py-1',
    RETIRED: 'badge badge-secondary px-2 py-1',
    TRANSFERRED: 'badge badge-info px-2 py-1',
    SUSPENDED: 'badge badge-warning text-dark px-2 py-1',
    OTHER: 'badge badge-dark px-2 py-1',
  };
  return map[status] || 'badge badge-success px-2 py-1';
};

const getOfficerStatusIcon = (status) => {
  const map = {
    ACTIVE: 'fas fa-check-circle',
    RESIGNED: 'fas fa-times-circle',
    RETIRED: 'fas fa-user-clock',
    TRANSFERRED: 'fas fa-exchange-alt',
    SUSPENDED: 'fas fa-pause-circle',
    OTHER: 'fas fa-info-circle',
  };
  return map[status] || 'fas fa-check-circle';
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return String(dateStr).split('T')[0];
};

const calculateServiceDuration = (startDate, endDate) => {
  if (!startDate) return null;
  try {
    const start = new Date(startDate);
    const end = endDate ? new Date(endDate) : new Date();
    if (isNaN(start.getTime()) || isNaN(end.getTime()) || end < start) return '០ ថ្ងៃ';

    let years = end.getFullYear() - start.getFullYear();
    let months = end.getMonth() - start.getMonth();
    let days = end.getDate() - start.getDate();

    if (days < 0) {
      months--;
      const prevMonth = new Date(end.getFullYear(), end.getMonth(), 0);
      days += prevMonth.getDate();
    }
    if (months < 0) {
      years--;
      months += 12;
    }

    const parts = [];
    if (years > 0) parts.push(`${years} ឆ្នាំ`);
    if (months > 0) parts.push(`${months} ខែ`);
    if (years === 0 && months === 0) {
      parts.push(days > 0 ? `${days} ថ្ងៃ` : 'ទើបចូលបម្រើការងារ');
    }
    return parts.join(' ');
  } catch (e) {
    return null;
  }
};

const modalPreviewDuration = computed(() => {
  const start = form.first_service_date || form.first_appointment_date;
  if (!start) return null;
  const end = (['RESIGNED', 'RETIRED', 'TRANSFERRED', 'SUSPENDED'].includes(form.officer_status) && form.officer_status_date)
    ? form.officer_status_date
    : null;
  return calculateServiceDuration(start, end);
});

const onOfficerStatusChange = () => {
  if (['RESIGNED', 'RETIRED', 'TRANSFERRED', 'SUSPENDED'].includes(form.officer_status)) {
    if (!form.officer_status_date) {
      form.officer_status_date = new Date().toISOString().split('T')[0];
    }
    form.status = 'DISABLED';
  } else if (form.officer_status === 'ACTIVE') {
    form.status = 'ENABLED';
  }
};

onMounted(() => {
  fetchDropdowns();
  fetchUsers();
});

</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Kantumruy+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap');

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

.name-khmer {
  font-family: 'Kantumruy Pro', sans-serif !important;
  font-weight: 600;
  color: #1b3d36;
}

.font-weight-500 {
  font-weight: 500;
}

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

.avatar-img {
  width: 44px;
  height: 44px;
  object-fit: cover;
  border-color: #e5e7eb !important;
}

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

.btn-edit {
  color: #0284c7;
  background-color: #f0f9ff;
  border-color: #bae6fd;
}

.btn-print {
  color: #0c4a6e;
  background-color: #f1f5f9;
  border-color: #cbd5e1;
}

.btn-edit:hover {
  background-color: #0284c7;
  color: #ffffff;
}

.btn-disable {
  color: #d97706;
  background-color: #fffbeb;
  border-color: #fde68a;
}

.btn-disable:hover {
  background-color: #d97706;
  color: #ffffff;
}

.btn-enable {
  color: #16a34a;
  background-color: #f0fdf4;
  border-color: #bbf7d0;
}

.btn-enable:hover {
  background-color: #16a34a;
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

.modal-header {
  border-top-left-radius: calc(0.3rem - 1px);
  border-top-right-radius: calc(0.3rem - 1px);
}

.form-label {
  font-size: 0.9rem;
  margin-bottom: 0.35rem;
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
  border: 2px solid !important;
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
.bg-success-light { background-color: #dcfce7; }
.bg-danger-light { background-color: #fee2e2; }
.bg-secondary-light { background-color: #f1f5f9; }
.bg-info-light { background-color: #e0f2fe; }
.cursor-pointer { cursor: pointer; }
.font-size-xs { font-size: 11px; }

/* Permission Modal Styles */
.btn-permission {
  color: #d97706 !important;
  background-color: #fef3c7 !important;
  border-color: #fde68a !important;
}
.btn-permission:hover {
  background-color: #fde68a !important;
  color: #b45309 !important;
}
.permission-item-card {
  transition: all 0.2s ease;
  border-width: 1.5px !important;
  min-height: 52px;
}
.permission-item-card:hover {
  border-color: #10b981 !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  transform: translateY(-1px);
}
.perm-icon {
  width: 32px;
  height: 32px;
  min-width: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  background-color: rgba(0, 0, 0, 0.05);
  font-size: 14px;
}

.sticky-modal-footer {
  position: sticky !important;
  bottom: 0 !important;
  z-index: 105 !important;
  background-color: #f8f9fa !important;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.06);
}
</style>