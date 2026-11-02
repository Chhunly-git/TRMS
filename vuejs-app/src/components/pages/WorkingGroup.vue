<template>
  <div class="content-wrapper working-group-page">
    <!-- Header -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 class="m-0 font-weight-bold text-dark khmer-font">
              <i class="fas fa-users-cog text-indigo mr-2"></i>គ្រប់គ្រងក្រុមការងារ (Working Groups)
            </h1>
            <p class="text-muted text-sm mb-0 mt-1">
              កំណត់ក្រុមការងារ ចាត់តាំងប្រធាន អនុប្រធាន និងសមាជិកចេញពីមន្ត្រីក្នុងប្រព័ន្ធ
            </p>
          </div>
          <div class="col-sm-6 text-right">
            <button class="btn btn-primary-theme px-3 py-2 shadow-sm" @click="openCreateModal">
              <i class="fas fa-plus-circle mr-1"></i> បង្កើតក្រុមការងារថ្មី
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Stats Cards -->
        <div class="row">
          <div class="col-lg-4 col-6">
            <div class="small-box bg-white border shadow-sm rounded-lg p-3 mb-3">
              <div class="inner">
                <h3 class="font-weight-bold text-primary mb-1">{{ stats.total }}</h3>
                <p class="text-muted mb-0 font-weight-500">ក្រុមការងារសរុប</p>
              </div>
              <div class="icon text-primary opacity-25">
                <i class="fas fa-users"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-6">
            <div class="small-box bg-white border shadow-sm rounded-lg p-3 mb-3">
              <div class="inner">
                <h3 class="font-weight-bold text-success mb-1">{{ stats.active }}</h3>
                <p class="text-muted mb-0 font-weight-500">កំពុងដំណើរការ (Active)</p>
              </div>
              <div class="icon text-success opacity-25">
                <i class="fas fa-check-circle"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-12">
            <div class="small-box bg-white border shadow-sm rounded-lg p-3 mb-3">
              <div class="inner">
                <h3 class="font-weight-bold text-indigo mb-1">{{ stats.totalMembers }}</h3>
                <p class="text-muted mb-0 font-weight-500">សមាជិកសរុបក្នុងគ្រប់ក្រុម</p>
              </div>
              <div class="icon text-indigo opacity-25">
                <i class="fas fa-user-friends"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter & Table Card -->
        <div class="card shadow-sm border-0 rounded-lg">
          <div class="card-header bg-white py-3 border-bottom">
            <div class="row align-items-center">
              <div class="col-md-5 mb-2 mb-md-0">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                  </div>
                  <input
                    type="text"
                    v-model="search"
                    @input="debounceSearch"
                    class="form-control border-left-0 bg-light"
                    placeholder="ស្វែងរកតាមឈ្មោះក្រុមការងារ, លេខកូដ, សេចក្តីសម្រេច..."
                  />
                  <div class="input-group-append" v-if="search">
                    <button class="btn btn-light border" @click="clearSearch">
                      <i class="fas fa-times text-muted"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div class="col-md-3 mb-2 mb-md-0">
                <select v-model="statusFilter" @change="fetchGroups" class="form-control custom-select">
                  <option value="">ស្ថានភាពទាំងអស់</option>
                  <option value="ACTIVE">កំពុងដំណើរការ (Active)</option>
                  <option value="INACTIVE">អសកម្ម (Inactive)</option>
                </select>
              </div>
              <div class="col-md-4 text-md-right text-muted text-sm">
                បង្ហាញ <strong>{{ groups.length }}</strong> ក្រុមការងារ
              </div>
            </div>
          </div>

          <div class="card-body p-0">
            <!-- Loading -->
            <div v-if="loading" class="text-center py-5">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="text-muted mt-2 mb-0">កំពុងផ្ទុកទិន្នន័យក្រុមការងារ...</p>
            </div>

            <!-- Empty state -->
            <div v-else-if="groups.length === 0" class="text-center py-5">
              <i class="fas fa-folder-open text-muted fa-3x mb-3"></i>
              <h5 class="text-dark font-weight-bold">មិនមានទិន្នន័យក្រុមការងារឡើយ</h5>
              <p class="text-muted text-sm mb-3">លោកអ្នកអាចចុចប៊ូតុងខាងក្រោមដើម្បីបង្កើតក្រុមការងារថ្មី</p>
              <button class="btn btn-outline-primary btn-sm" @click="openCreateModal">
                <i class="fas fa-plus-circle mr-1"></i> បង្កើតក្រុមការងារថ្មី
              </button>
            </div>

            <!-- Table -->
            <div v-else class="table-responsive">
              <table class="table table-hover table-striped align-middle mb-0">
                <thead class="thead-light">
                  <tr>
                    <th style="width: 50px;" class="text-center">#</th>
                    <th style="min-width: 240px;">ឈ្មោះក្រុមការងារ</th>
                    <th style="min-width: 180px;">លិខិតបង្កើត / កាលបរិច្ឆេទ</th>
                    <th style="min-width: 200px;">ប្រធានក្រុមការងារ</th>
                    <th style="width: 140px;" class="text-center">សមាជិក</th>
                    <th style="width: 110px;" class="text-center">ស្ថានភាព</th>
                    <th style="width: 160px;" class="text-center">សកម្មភាព</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(group, idx) in groups" :key="group.id">
                    <td class="text-center text-muted font-weight-bold">{{ idx + 1 }}</td>
                    <td>
                      <div class="font-weight-bold text-dark text-base">{{ group.name_kh }}</div>
                      <div class="text-muted text-xs font-italic" v-if="group.name_en">{{ group.name_en }}</div>
                      <div class="badge badge-light border text-muted mt-1" v-if="group.code">
                        <code>{{ group.code }}</code>
                      </div>
                    </td>
                    <td>
                      <div v-if="group.reference_document" class="text-sm font-weight-500 text-dark">
                        <i class="fas fa-file-contract text-primary mr-1"></i>{{ group.reference_document }}
                      </div>
                      <div v-else class="text-muted text-sm">-</div>
                      <div class="text-xs text-muted mt-1" v-if="group.established_date">
                        <i class="far fa-calendar-alt mr-1"></i>{{ formatDate(group.established_date) }}
                      </div>
                    </td>
                    <td>
                      <div v-if="getGroupLeader(group)" class="d-flex align-items-center">
                        <div class="avatar-sm mr-2">
                          <img
                            v-if="getGroupLeader(group).user?.profile_image"
                            :src="getGroupLeader(group).user.profile_image"
                            class="rounded-circle"
                            style="width: 34px; height: 34px; object-fit: cover;"
                          />
                          <div v-else class="avatar-placeholder rounded-circle bg-light-indigo text-indigo font-weight-bold text-center" style="width: 34px; height: 34px; line-height: 34px; font-size: 13px;">
                            {{ (getGroupLeader(group).user?.name_kh || getGroupLeader(group).user?.name || 'L').charAt(0) }}
                          </div>
                        </div>
                        <div>
                          <div class="font-weight-bold text-dark text-sm">
                            {{ getGroupLeader(group).user?.name_kh || getGroupLeader(group).user?.name }}
                          </div>
                          <div class="text-xs text-muted">
                            {{ getGroupLeader(group).user?.position?.title_kh || 'ប្រធានក្រុម' }}
                          </div>
                        </div>
                      </div>
                      <div v-else class="text-muted text-sm font-italic">
                        <span class="badge badge-warning text-dark font-weight-normal">មិនទាន់ចាត់តាំង</span>
                      </div>
                    </td>
                    <td class="text-center">
                      <span class="badge badge-pill badge-primary px-2 py-1">
                        <i class="fas fa-users mr-1"></i>{{ group.members ? group.members.length : 0 }} នាក់
                      </span>
                      <div class="text-xs text-muted mt-1" v-if="getDeputyCount(group) > 0">
                        អនុប្រធាន {{ getDeputyCount(group) }} នាក់
                      </div>
                    </td>
                    <td class="text-center">
                      <span v-if="group.status === 'ACTIVE'" class="badge badge-success px-2 py-1">សកម្ម</span>
                      <span v-else class="badge badge-secondary px-2 py-1">អសកម្ម</span>
                    </td>
                    <td class="text-center">
                      <div class="btn-group btn-group-sm">
                        <button
                          class="btn btn-outline-info"
                          @click="openMembersModal(group)"
                          title="គ្រប់គ្រងសមាជិកក្រុមការងារ"
                        >
                          <i class="fas fa-users-cog"></i>
                        </button>
                        <button
                          class="btn btn-outline-primary"
                          @click="openEditModal(group)"
                          title="កែសម្រួលព័ត៌មានក្រុមការងារ"
                        >
                          <i class="fas fa-edit"></i>
                        </button>
                        <button
                          class="btn btn-outline-danger"
                          @click="confirmDelete(group)"
                          title="លុបក្រុមការងារ"
                        >
                          <i class="fas fa-trash"></i>
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

    <!-- ==================== MODAL 1: CREATE / EDIT WORKING GROUP ==================== -->
    <div v-if="showGroupModal" class="custom-modal-backdrop" @click.self="closeGroupModal">
      <div class="modal-dialog modal-lg modal-dialog-centered my-auto" role="document">
        <div class="modal-content shadow border-0 rounded-lg overflow-hidden d-flex flex-column" style="max-height: 90vh;">
          <div class="modal-header bg-light py-3 px-4 flex-shrink-0 align-items-center">
            <h5 class="modal-title font-weight-bold text-dark mb-0">
              <i class="fas mr-2 text-indigo" :class="isEditMode ? 'fa-edit' : 'fa-plus-circle'"></i>
              {{ isEditMode ? 'កែសម្រួលព័ត៌មានក្រុមការងារ' : 'បង្កើតក្រុមការងារថ្មី' }}
            </h5>
            <button type="button" class="close" @click="closeGroupModal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form @submit.prevent="saveWorkingGroup" class="d-flex flex-column overflow-hidden" style="max-height: calc(90vh - 60px);">
            <div class="modal-body p-4 flex-grow-1" style="overflow-y: auto;">
              <!-- General Info -->
              <div class="row">
                <div class="col-md-8">
                  <div class="form-group">
                    <label class="font-weight-600 text-sm">ឈ្មោះក្រុមការងារ (ខ្មែរ) <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      v-model="form.name_kh"
                      class="form-control"
                      placeholder="ឧ. ក្រុមការងាររៀបចំផែនការយុទ្ធសាស្ត្រ"
                      required
                    />
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="font-weight-600 text-sm">លេខកូដសម្គាល់ (Code)</label>
                    <input
                      type="text"
                      v-model="form.code"
                      class="form-control"
                      placeholder="ឧ. WG-SP"
                    />
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <label class="font-weight-600 text-sm">ឈ្មោះជាភាសាអង់គ្លេស (English Name)</label>
                    <input
                      type="text"
                      v-model="form.name_en"
                      class="form-control"
                      placeholder="e.g. Strategic Planning Working Group"
                    />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="font-weight-600 text-sm">សេចក្តីសម្រេច / លិខិតបង្កើត</label>
                    <input
                      type="text"
                      v-model="form.reference_document"
                      class="form-control"
                      placeholder="ឧ. សេចក្តីសម្រេចលេខ ១២៣ សសរ.អនធ"
                    />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="font-weight-600 text-sm">កាលបរិច្ឆេទបង្កើត</label>
                    <input
                      type="date"
                      v-model="form.established_date"
                      class="form-control"
                    />
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <label class="font-weight-600 text-sm">ការពិពណ៌នា / ភារកិច្ចចម្បង</label>
                    <textarea
                      v-model="form.description"
                      rows="2"
                      class="form-control"
                      placeholder="សង្ខេបអំពីតួនាទី និងភារកិច្ចរបស់ក្រុមការងារ..."
                    ></textarea>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="font-weight-600 text-sm">ស្ថានភាព</label>
                    <select v-model="form.status" class="form-control custom-select">
                      <option value="ACTIVE">កំពុងដំណើរការ (Active)</option>
                      <option value="INACTIVE">ផ្អាកដំណើរការ (Inactive)</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Initial Members Section (Optional inside group modal) -->
              <div class="border-top pt-3 mt-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h6 class="font-weight-bold text-dark mb-0">
                    <i class="fas fa-user-plus text-primary mr-1"></i> សមាជិកក្នុងក្រុមការងារ ({{ formMembers.length }} នាក់)
                  </h6>
                  <button type="button" class="btn btn-sm btn-outline-indigo" @click="showAddMemberRow = !showAddMemberRow">
                    <i class="fas" :class="showAddMemberRow ? 'fa-minus' : 'fa-plus'"></i> {{ showAddMemberRow ? 'បិទផ្ទាំងជ្រើសរើស' : 'ចាត់តាំងសមាជិកបន្ថែម' }}
                  </button>
                </div>

                <!-- Add Member Row Form -->
                <div v-if="showAddMemberRow" class="card bg-light border p-3 mb-3">
                  <div class="row align-items-end">
                    <div class="col-md-5">
                      <label class="text-xs font-weight-bold mb-1">
                        ជ្រើសរើសមន្ត្រី <span class="text-muted">(Order តាមតួនាទី)</span>
                      </label>
                      <OfficerSelect
                        v-model="newMemberInput.user_id"
                        :officers="availableOfficers"
                        :disabled-user-ids="formMemberUserIds"
                        placeholder="ស្វែងរកតាមឈ្មោះ, តួនាទី, នាយកដ្ឋាន..."
                      />
                    </div>
                    <div class="col-md-4">
                      <label class="text-xs font-weight-bold mb-1">តួនាទីក្នុងក្រុមការងារ</label>
                      <select v-model="newMemberInput.role" class="form-control form-control-sm custom-select">
                        <option value="LEADER">ប្រធានក្រុមការងារ</option>
                        <option value="DEPUTY_LEADER">អនុប្រធានក្រុមការងារ</option>
                        <option value="SECRETARY">លេខាធិការ</option>
                        <option value="PERMANENT_MEMBER">សមាជិកអចិន្ត្រៃយ៍</option>
                        <option value="MEMBER">សមាជិក</option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <button
                        type="button"
                        class="btn btn-sm btn-indigo btn-block"
                        @click="addMemberToForm"
                        :disabled="!newMemberInput.user_id"
                      >
                        <i class="fas fa-check mr-1"></i> បញ្ចូល
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Members List in Modal -->
                <div v-if="formMembers.length === 0" class="text-center py-3 text-muted text-sm bg-light rounded">
                  មិនទាន់មានសមាជិកណាមួយត្រូវបានជ្រើសរើសនៅឡើយ
                </div>
                <div v-else class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                  <table class="table table-sm table-bordered table-hover mb-0">
                    <thead class="thead-light text-xs">
                      <tr>
                        <th>ឈ្មោះមន្ត្រី</th>
                        <th>តួនាទីដើម</th>
                        <th>តួនាទីក្នុងក្រុមការងារ</th>
                        <th style="width: 50px;" class="text-center">សកម្មភាព</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(m, idx) in formMembers" :key="idx">
                        <td class="font-weight-500 text-sm">
                          {{ getOfficerName(m.user_id) }}
                        </td>
                        <td class="text-xs text-muted">
                          {{ getOfficerPosition(m.user_id) }}
                        </td>
                        <td>
                          <span class="badge" :class="getRoleBadgeClass(m.role)">
                            {{ getRoleLabel(m.role) }}
                          </span>
                        </td>
                        <td class="text-center">
                          <button
                            type="button"
                            class="btn btn-link text-danger btn-xs p-0"
                            @click="removeMemberFromForm(idx)"
                          >
                            <i class="fas fa-times"></i>
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="modal-footer bg-light py-2 flex-shrink-0">
              <button type="button" class="btn btn-secondary px-3" @click="closeGroupModal">បោះបង់</button>
              <button type="submit" class="btn btn-primary-theme px-4" :disabled="submitting">
                <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                <span>{{ isEditMode ? 'រក្សាទុកការកែប្រែ' : 'បង្កើតក្រុមការងារ' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- ==================== MODAL 2: MANAGE MEMBERS MODAL ==================== -->
    <div v-if="showMembersModal" class="custom-modal-backdrop" @click.self="closeMembersModal">
      <div class="modal-dialog modal-lg modal-dialog-centered my-auto" role="document">
        <div class="modal-content shadow border-0 rounded-lg overflow-hidden d-flex flex-column" style="max-height: 90vh;">
          <div class="modal-header bg-light border-bottom py-3 px-4 flex-shrink-0 align-items-center">
            <div>
              <h5 class="modal-title font-weight-bold text-dark mb-0">
                <i class="fas fa-users-cog text-indigo mr-2"></i>គ្រប់គ្រងសមាជិក៖ {{ selectedGroup?.name_kh }}
              </h5>
              <p class="text-muted text-xs mb-0 mt-1" v-if="selectedGroup?.reference_document">
                យោង៖ {{ selectedGroup.reference_document }}
              </p>
            </div>
            <button type="button" class="close" @click="closeMembersModal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body p-4 flex-grow-1" style="overflow-y: auto;">
            <!-- Add New Member Form Panel -->
            <div class="card border border-primary-light shadow-sm rounded-lg mb-4">
              <div class="card-header bg-light-indigo py-2">
                <span class="font-weight-bold text-indigo text-sm">
                  <i class="fas fa-user-plus mr-1"></i> ចាត់តាំងសមាជិកថ្មីចូលក្នុងក្រុមការងារ
                </span>
              </div>
              <div class="card-body p-3">
                <div class="row align-items-end">
                  <div class="col-md-5">
                    <label class="text-xs font-weight-bold text-muted mb-1">
                      ជ្រើសរើសមន្ត្រី <span class="text-danger">*</span> <span class="text-muted">(Order តាមតួនាទី)</span>
                    </label>
                    <OfficerSelect
                      v-model="addMemberForm.user_id"
                      :officers="availableOfficers"
                      :disabled-user-ids="selectedGroupMemberUserIds"
                      placeholder="ស្វែងរកតាមឈ្មោះ, តួនាទី, នាយកដ្ឋាន..."
                    />
                  </div>
                  <div class="col-md-4">
                    <label class="text-xs font-weight-bold text-muted mb-1">តួនាទីក្នុងក្រុមការងារ <span class="text-danger">*</span></label>
                    <select v-model="addMemberForm.role" class="form-control form-control-sm custom-select">
                      <option value="LEADER">ប្រធានក្រុមការងារ</option>
                      <option value="DEPUTY_LEADER">អនុប្រធានក្រុមការងារ</option>
                      <option value="SECRETARY">លេខាធិការ</option>
                      <option value="PERMANENT_MEMBER">សមាជិកអចិន្ត្រៃយ៍</option>
                      <option value="MEMBER">សមាជិក</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <button
                      type="button"
                      class="btn btn-sm btn-primary-theme btn-block"
                      @click="submitAddMember"
                      :disabled="!addMemberForm.user_id || addingMember"
                    >
                      <span v-if="addingMember" class="spinner-border spinner-border-sm mr-1"></span>
                      <i v-else class="fas fa-plus mr-1"></i> បញ្ចូលសមាជិក
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Existing Members List -->
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="font-weight-bold text-dark mb-0">
                បញ្ជីសមាជិកបច្ចុប្បន្ន ({{ selectedGroupMembers.length }} នាក់)
              </h6>
            </div>

            <div v-if="selectedGroupMembers.length === 0" class="text-center py-4 text-muted bg-light rounded">
              មិនទាន់មានសមាជិកនៅក្នុងក្រុមការងារនេះឡើយ
            </div>
            <div v-else class="table-responsive border rounded">
              <table class="table table-hover align-middle mb-0">
                <thead class="thead-light text-xs">
                  <tr>
                    <th style="width: 40px;" class="text-center">#</th>
                    <th style="min-width: 200px;">ព័ត៌មានមន្ត្រី</th>
                    <th style="min-width: 150px;">តួនាទីផ្លូវការ</th>
                    <th style="min-width: 160px;">តួនាទីក្នុងក្រុមការងារ</th>
                    <th style="width: 80px;" class="text-center">សកម្មភាព</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(m, i) in selectedGroupMembers" :key="m.id">
                    <td class="text-center text-muted font-weight-bold text-xs">{{ i + 1 }}</td>
                    <td>
                      <div class="d-flex align-items-center">
                        <img
                          v-if="m.user?.profile_image"
                          :src="m.user.profile_image"
                          class="rounded-circle mr-2"
                          style="width: 32px; height: 32px; object-fit: cover;"
                        />
                        <div v-else class="avatar-placeholder rounded-circle bg-light-indigo text-indigo font-weight-bold text-center mr-2" style="width: 32px; height: 32px; line-height: 32px; font-size: 12px;">
                          {{ (m.user?.name_kh || m.user?.name || 'M').charAt(0) }}
                        </div>
                        <div>
                          <div class="font-weight-bold text-dark text-sm">
                            {{ m.user?.name_kh || m.user?.name }}
                          </div>
                          <div class="text-xs text-muted">{{ m.user?.email }}</div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="text-sm font-weight-500 text-dark">
                        {{ m.user?.position?.title_kh || 'មន្ត្រី' }}
                      </div>
                      <div class="text-xs text-muted" v-if="m.user?.department">
                        {{ m.user.department.name_kh }}
                      </div>
                    </td>
                    <td>
                      <span class="badge" :class="getRoleBadgeClass(m.role)">
                        <i class="fas fa-award mr-1" v-if="m.role === 'LEADER' || m.role === 'DEPUTY_LEADER'"></i>
                        {{ m.role_kh || getRoleLabel(m.role) }}
                      </span>
                    </td>
                    <td class="text-center">
                      <button
                        class="btn btn-outline-danger btn-xs py-1 px-2"
                        @click="removeMemberFromGroup(m)"
                        title="ដកចេញពីក្រុមការងារ"
                      >
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer bg-light py-2 flex-shrink-0">
            <button type="button" class="btn btn-secondary btn-sm px-3" @click="closeMembersModal">បិទ</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import OfficerSelect from '@/components/common/OfficerSelect.vue';
import {
  apiGetWorkingGroups,
  apiCreateWorkingGroup,
  apiUpdateWorkingGroup,
  apiDeleteWorkingGroup,
  apiAddWorkingGroupMember,
  apiRemoveWorkingGroupMember,
} from '@/functions/api/workingGroup';
import { apiGetInboundRecipientsOptions } from '@/functions/api/inboundDocument';
import Swal from 'sweetalert2';

// State
const loading = ref(false);
const submitting = ref(false);
const addingMember = ref(false);
const groups = ref([]);
const search = ref('');
const statusFilter = ref('');
const availableOfficers = ref([]);

// Selected Group for Member management
const selectedGroup = ref(null);
const selectedGroupMembers = ref([]);

// Modals
const showGroupModal = ref(false);
const showMembersModal = ref(false);
const isEditMode = ref(false);
const currentGroupId = ref(null);
const showAddMemberRow = ref(false);

// Form State
const form = reactive({
  name_kh: '',
  name_en: '',
  code: '',
  reference_document: '',
  established_date: '',
  description: '',
  status: 'ACTIVE',
  order_index: 0,
});

const formMembers = ref([]);

const newMemberInput = reactive({
  user_id: '',
  role: 'MEMBER',
});

const addMemberForm = reactive({
  user_id: '',
  role: 'MEMBER',
});

// Member User IDs Lists (for disabling already chosen officers)
const formMemberUserIds = computed(() => {
  return formMembers.value.map(m => m.user_id);
});

const selectedGroupMemberUserIds = computed(() => {
  return selectedGroupMembers.value.map(m => m.user_id);
});

// Stats
const stats = computed(() => {
  const total = groups.value.length;
  const active = groups.value.filter(g => g.status === 'ACTIVE').length;
  let totalMembers = 0;
  groups.value.forEach(g => {
    if (g.members) totalMembers += g.members.length;
  });
  return { total, active, totalMembers };
});

// Load Data
const fetchGroups = async () => {
  try {
    loading.value = true;
    const res = await apiGetWorkingGroups({
      search: search.value,
      status: statusFilter.value,
      per_page: 100,
    });
    groups.value = res.data.data || res.data;
  } catch (err) {
    console.error('Failed to load working groups:', err);
  } finally {
    loading.value = false;
  }
};

const fetchOfficers = async () => {
  try {
    const res = await apiGetInboundRecipientsOptions();
    availableOfficers.value = res.data.users || [];
  } catch (err) {
    console.error('Failed to load officers:', err);
  }
};

let searchTimeout = null;
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchGroups();
  }, 350);
};

const clearSearch = () => {
  search.value = '';
  fetchGroups();
};

// Helpers
const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-GB'); // dd/mm/yyyy
};

const getGroupLeader = (group) => {
  if (!group.members || group.members.length === 0) return null;
  return group.members.find(m => m.role === 'LEADER' && m.status === 'ACTIVE');
};

const getDeputyCount = (group) => {
  if (!group.members) return 0;
  return group.members.filter(m => m.role === 'DEPUTY_LEADER' && m.status === 'ACTIVE').length;
};

const getRoleLabel = (role) => {
  switch (role) {
    case 'LEADER': return 'ប្រធានក្រុមការងារ';
    case 'DEPUTY_LEADER': return 'អនុប្រធានក្រុមការងារ';
    case 'SECRETARY': return 'លេខាធិការ';
    case 'PERMANENT_MEMBER': return 'សមាជិកអចិន្ត្រៃយ៍';
    case 'MEMBER': return 'សមាជិក';
    default: return role || 'សមាជិក';
  }
};

const getRoleBadgeClass = (role) => {
  switch (role) {
    case 'LEADER': return 'badge-danger text-white py-1 px-2';
    case 'DEPUTY_LEADER': return 'badge-warning text-dark py-1 px-2';
    case 'SECRETARY': return 'badge-info py-1 px-2';
    case 'PERMANENT_MEMBER': return 'badge-primary py-1 px-2';
    case 'MEMBER': return 'badge-light border py-1 px-2';
    default: return 'badge-secondary py-1 px-2';
  }
};

const getOfficerName = (userId) => {
  const u = availableOfficers.value.find(o => o.id === userId);
  return u ? (u.name_kh || u.name) : 'មន្ត្រី';
};

const getOfficerPosition = (userId) => {
  const u = availableOfficers.value.find(o => o.id === userId);
  return u?.position?.title_kh || 'មន្ត្រី';
};

const isMemberSelected = (userId) => {
  return formMembers.value.some(m => m.user_id === userId);
};

const isAlreadyInSelectedGroup = (userId) => {
  return selectedGroupMembers.value.some(m => m.user_id === userId);
};

// Form Actions
const resetForm = () => {
  form.name_kh = '';
  form.name_en = '';
  form.code = '';
  form.reference_document = '';
  form.established_date = '';
  form.description = '';
  form.status = 'ACTIVE';
  form.order_index = 0;
  formMembers.value = [];
  showAddMemberRow.value = false;
  newMemberInput.user_id = '';
  newMemberInput.role = 'MEMBER';
};

const openCreateModal = () => {
  isEditMode.value = false;
  currentGroupId.value = null;
  resetForm();
  showGroupModal.value = true;
};

const closeGroupModal = () => {
  showGroupModal.value = false;
};

const openEditModal = (group) => {
  isEditMode.value = true;
  currentGroupId.value = group.id;
  form.name_kh = group.name_kh;
  form.name_en = group.name_en || '';
  form.code = group.code || '';
  form.reference_document = group.reference_document || '';
  form.established_date = group.established_date ? group.established_date.split('T')[0] : '';
  form.description = group.description || '';
  form.status = group.status || 'ACTIVE';
  form.order_index = group.order_index || 0;

  formMembers.value = (group.members || []).map(m => ({
    user_id: m.user_id,
    role: m.role,
    custom_role_kh: m.custom_role_kh,
    order_index: m.order_index,
  }));

  showAddMemberRow.value = false;
  showGroupModal.value = true;
};

const addMemberToForm = () => {
  if (!newMemberInput.user_id) return;
  formMembers.value.push({
    user_id: parseInt(newMemberInput.user_id),
    role: newMemberInput.role,
    order_index: formMembers.value.length + 1,
  });
  newMemberInput.user_id = '';
  newMemberInput.role = 'MEMBER';
};

const removeMemberFromForm = (idx) => {
  formMembers.value.splice(idx, 1);
};

const saveWorkingGroup = async () => {
  try {
    submitting.value = true;
    const payload = {
      name_kh: form.name_kh.trim(),
      name_en: form.name_en ? form.name_en.trim() : null,
      code: form.code ? form.code.trim() : null,
      reference_document: form.reference_document ? form.reference_document.trim() : null,
      established_date: form.established_date || null,
      description: form.description ? form.description.trim() : null,
      status: form.status || 'ACTIVE',
      order_index: form.order_index ? parseInt(form.order_index) : 0,
      members: formMembers.value.filter(m => !!m.user_id),
    };

    if (isEditMode.value) {
      await apiUpdateWorkingGroup(currentGroupId.value, payload);
      Swal.fire({
        icon: 'success',
        title: 'ជោគជ័យ!',
        text: 'បានកែសម្រួលព័ត៌មានក្រុមការងារដោយជោគជ័យ',
        timer: 1500,
        showConfirmButton: false,
      });
    } else {
      await apiCreateWorkingGroup(payload);
      Swal.fire({
        icon: 'success',
        title: 'ជោគជ័យ!',
        text: 'បានបង្កើតក្រុមការងារថ្មីដោយជោគជ័យ',
        timer: 1500,
        showConfirmButton: false,
      });
    }

    showGroupModal.value = false;
    fetchGroups();
  } catch (err) {
    const msg = err.response?.data?.message || 
                (err.response?.data?.errors ? Object.values(err.response.data.errors)[0]?.[0] : null) || 
                'មានបញ្ហាក្នុងការរក្សាទុកទិន្នន័យ!';
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: msg });
  } finally {
    submitting.value = false;
  }
};

const confirmDelete = (group) => {
  Swal.fire({
    title: 'តើលោកអ្នកពិតជាចង់លុបក្រុមការងារនេះមែនទេ?',
    text: `«${group.name_kh}» នឹងត្រូវលុបចេញពីប្រព័ន្ធ!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'បាទ/ចាស លុប',
    cancelButtonText: 'បោះបង់',
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await apiDeleteWorkingGroup(group.id);
        Swal.fire({
          icon: 'success',
          title: 'បានលុបរួចរាល់!',
          text: 'ក្រុមការងារត្រូវបានលុបដោយជោគជ័យ',
          timer: 1500,
          showConfirmButton: false,
        });
        fetchGroups();
      } catch (err) {
        Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: 'មិនអាចលុបក្រុមការងារបានឡើយ!' });
      }
    }
  });
};

// Members Modal Actions
const openMembersModal = (group) => {
  selectedGroup.value = group;
  selectedGroupMembers.value = [...(group.members || [])];
  addMemberForm.user_id = '';
  addMemberForm.role = 'MEMBER';
  showMembersModal.value = true;
};

const closeMembersModal = () => {
  showMembersModal.value = false;
};

const submitAddMember = async () => {
  if (!addMemberForm.user_id || !selectedGroup.value) return;
  try {
    addingMember.value = true;
    const res = await apiAddWorkingGroupMember(selectedGroup.value.id, {
      user_id: parseInt(addMemberForm.user_id),
      role: addMemberForm.role,
    });

    // Refresh current members list
    selectedGroupMembers.value.push(res.data.data);
    addMemberForm.user_id = '';
    addMemberForm.role = 'MEMBER';

    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: 'បានចាត់តាំងសមាជិកថ្មីចូលក្នុងក្រុមការងារ',
      timer: 1200,
      showConfirmButton: false,
    });

    fetchGroups(); // refresh parent table counts
  } catch (err) {
    const msg = err.response?.data?.message || 
                (err.response?.data?.errors ? Object.values(err.response.data.errors)[0]?.[0] : null) || 
                'មិនអាចបន្ថែមសមាជិកបានឡើយ!';
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: msg });
  } finally {
    addingMember.value = false;
  }
};

const removeMemberFromGroup = (member) => {
  Swal.fire({
    title: 'តើចង់ដកសមាជិករូបនេះមែនទេ?',
    text: `ដកលោក/លោកស្រី «${member.user?.name_kh || member.user?.name}» ចេញពីក្រុមការងារ?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    confirmButtonText: 'យល់ព្រម',
    cancelButtonText: 'បោះបង់',
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await apiRemoveWorkingGroupMember(selectedGroup.value.id, member.id);
        selectedGroupMembers.value = selectedGroupMembers.value.filter(m => m.id !== member.id);
        Swal.fire({
          icon: 'success',
          title: 'បានដករួចរាល់!',
          timer: 1200,
          showConfirmButton: false,
        });
        fetchGroups();
      } catch (err) {
        Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: 'មិនអាចដកសមាជិកបានឡើយ!' });
      }
    }
  });
};

onMounted(() => {
  fetchGroups();
  fetchOfficers();
});
</script>

<style scoped>
.working-group-page {
  font-family: 'Kantumruy Pro', 'Battambang', system-ui, sans-serif;
}

.khmer-font {
  font-family: 'Kantumruy Pro', 'Battambang', system-ui, sans-serif;
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

.btn-primary-theme {
  background-color: #0c2b29;
  color: #ffffff;
  border: none;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-primary-theme:hover {
  background-color: #144542;
  color: #ffffff;
}

.btn-indigo {
  background-color: #6610f2;
  color: #ffffff;
}

.btn-indigo:hover {
  background-color: #520dc2;
  color: #ffffff;
}

.btn-outline-indigo {
  color: #6610f2;
  border-color: #6610f2;
}

.btn-outline-indigo:hover {
  background-color: #6610f2;
  color: #ffffff;
}

.bg-light-indigo {
  background-color: #eef2ff;
}

.text-indigo {
  color: #6610f2 !important;
}

.border-primary-light {
  border-color: #d1d5db !important;
}
</style>
