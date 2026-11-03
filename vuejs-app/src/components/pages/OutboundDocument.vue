<template>
  <div class="content-wrapper font-khmer">
    <!-- Content Header (Page header) -->
    <section class="content-header py-3 bg-white border-bottom shadow-xs">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-sm-6">
            <h1 class="m-0 font-weight-bold text-dark font-20 d-flex align-items-center">
              <i class="fas fa-file-export text-primary mr-2"></i>
              ឯកសារចេញ / របាយការណ៍ / កំណត់បង្ហាញ
            </h1>
            <small class="text-muted">ការរៀបចំ និងឆ្លងលិខិតឆ្លើយតប របាយការណ៍ និងកំណត់បង្ហាញតាមឋានានុក្រម</small>
          </div>
          <div class="col-sm-6 text-right mt-2 mt-sm-0">
            <button
              type="button"
              class="btn btn-primary shadow-sm font-weight-bold"
              @click="openCreateModal"
            >
              <i class="fas fa-plus-circle mr-1"></i> បង្កើតរបាយការណ៍/កំណត់បង្ហាញថ្មី
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Main content -->
    <section class="content py-3">
      <div class="container-fluid">
        <!-- Stats & 3 Tabs Bar -->
        <div class="row mb-3">
          <!-- Tab 1: Doing (ត្រូវធ្វើ / កំពុងរៀបចំ / ត្រូវពិនិត្យ) -->
          <div class="col-md-4 mb-2">
            <div
              class="card stat-card shadow-sm cursor-pointer border-0"
              :class="{ 'active-tab border-warning shadow': activeTab === 'doing' }"
              @click="switchTab('doing')"
            >
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <div class="text-uppercase small font-weight-bold text-muted">១. ត្រូវរៀបចំ (Pending)</div>
                  <div class="h3 font-weight-bold m-0 text-warning">{{ stats.doing }}</div>
                  <small class="text-muted">លិខិតរង់ចាំលោកអ្នកពិនិត្យ ឬកំពុងព្រាង</small>
                </div>
                <div class="stat-icon bg-warning-subtle text-warning rounded-circle p-3">
                  <i class="fas fa-edit fa-2x"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Tab 2: In Progress (កំពុងដំណើរការ / បានឆ្លងបន្ត) -->
          <div class="col-md-4 mb-2">
            <div
              class="card stat-card shadow-sm cursor-pointer border-0"
              :class="{ 'active-tab border-info shadow': activeTab === 'in_progress' }"
              @click="switchTab('in_progress')"
            >
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <div class="text-uppercase small font-weight-bold text-muted">២. កំពុងដំណើរការ (In Progress)</div>
                  <div class="h3 font-weight-bold m-0 text-info">{{ stats.in_progress }}</div>
                  <small class="text-muted">បានដាក់ឆ្លង និងកំពុងរង់ចាំថ្នាក់ដឹកនាំពិនិត្យ</small>
                </div>
                <div class="stat-icon bg-info-subtle text-info rounded-circle p-3">
                  <i class="fas fa-spinner fa-pulse fa-2x"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Tab 3: Complete (បានអនុម័ត / រួចរាល់) -->
          <div class="col-md-4 mb-2">
            <div
              class="card stat-card shadow-sm cursor-pointer border-0"
              :class="{ 'active-tab border-success shadow': activeTab === 'complete' }"
              @click="switchTab('complete')"
            >
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <div class="text-uppercase small font-weight-bold text-muted">៣. បានអនុម័តស្ថាពរ (Complete)</div>
                  <div class="h3 font-weight-bold m-0 text-success">{{ stats.complete }}</div>
                  <small class="text-muted">បានឯកភាពដោយឯកឧត្តមអគ្គនាយក</small>
                </div>
                <div class="stat-icon bg-success-subtle text-success rounded-circle p-3">
                  <i class="fas fa-check-double fa-2x"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter Bar -->
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-body p-3">
            <div class="row align-items-center">
              <div class="col-md-4 mb-2 mb-md-0">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                  </div>
                  <input
                    type="text"
                    class="form-control border-left-0"
                    placeholder="ស្វែងរកតាមកម្មវត្ថុ លេខចេញ ឬយោង..."
                    v-model="filters.search"
                    @keyup.enter="loadDocuments"
                  />
                </div>
              </div>

              <div class="col-md-3 mb-2 mb-md-0">
                <select class="form-control" v-model="filters.response_type" @change="loadDocuments">
                  <option value="">-- ប្រភេទលិខិតទាំងអស់ --</option>
                  <option value="REPORT">របាយការណ៍ (REPORT)</option>
                  <option value="SUBMISSION_NOTE">កំណត់បង្ហាញ (SUBMISSION NOTE)</option>
                  <option value="LETTER">លិខិតទូទៅ (LETTER)</option>
                </select>
              </div>

              <div class="col-md-3 mb-2 mb-md-0">
                <input
                  type="date"
                  class="form-control"
                  v-model="filters.from_date"
                  @change="loadDocuments"
                  title="កាលបរិច្ឆេទចាប់ពី"
                />
              </div>

              <div class="col-md-2 text-right">
                <button type="button" class="btn btn-outline-secondary btn-block" @click="resetFilters">
                  <i class="fas fa-sync-alt mr-1"></i> សម្អាត
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Table Card -->
        <div class="card border-0 shadow-sm">
          <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped align-middle m-0">
              <thead class="bg-light text-dark">
                <tr>
                  <th style="width: 60px;" class="text-center">ល.រ</th>
                  <th style="width: 140px;">លេខលិខិតចេញ</th>
                  <th style="width: 130px;">ប្រភេទ</th>
                  <th>កម្មវត្ថុ & យោង</th>
                  <th style="width: 180px;">អ្នករៀបចំ & អង្គភាព</th>
                  <th style="width: 180px;">ថ្នាក់ដឹកនាំកំពុងពិនិត្យ</th>
                  <th style="width: 140px;" class="text-center">ស្ថានភាព</th>
                  <th style="width: 160px;" class="text-center">សកម្មភាព</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="8" class="text-center py-5 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block"></i>
                    កំពុងទាញយកទិន្នន័យ...
                  </td>
                </tr>
                <tr v-else-if="documents.length === 0">
                  <td colspan="8" class="text-center py-5 text-muted">
                    <i class="fas fa-folder-open fa-3x text-black-50 mb-2 d-block"></i>
                    ពុំមានឯកសារចេញក្នុងផ្នែកនេះឡើយ
                  </td>
                </tr>
                <tr v-for="(doc, idx) in documents" :key="doc.id">
                  <td class="text-center font-weight-bold text-muted">
                    {{ (pagination.current_page - 1) * pagination.per_page + idx + 1 }}
                  </td>
                  <td>
                    <span v-if="doc.response_number" class="badge badge-dark px-2 py-1 font-13 font-weight-bold">
                      {{ doc.response_number }}
                    </span>
                    <span v-else class="text-muted font-italic small">មិនទាន់ចុះលេខ</span>
                  </td>
                  <td>
                    <span
                      class="badge font-12 px-2 py-1"
                      :class="{
                        'badge-primary': doc.response_type === 'REPORT',
                        'badge-info': doc.response_type === 'SUBMISSION_NOTE',
                        'badge-secondary': doc.response_type === 'LETTER',
                      }"
                    >
                      <i
                        class="fas mr-1"
                        :class="{
                          'fa-file-alt': doc.response_type === 'REPORT',
                          'fa-file-signature': doc.response_type === 'SUBMISSION_NOTE',
                          'fa-envelope': doc.response_type === 'LETTER',
                        }"
                      ></i>
                      {{ doc.response_type_kh || (doc.response_type === 'SUBMISSION_NOTE' ? 'កំណត់បង្ហាញ' : (doc.response_type === 'LETTER' ? 'លិខិត' : 'របាយការណ៍')) }}
                    </span>
                  </td>
                  <td>
                    <div class="font-weight-bold text-dark font-14">{{ doc.title }}</div>
                    <div v-if="doc.reference_note" class="text-muted small">
                      <i class="fas fa-quote-left mr-1 text-black-50"></i>{{ doc.reference_note }}
                    </div>
                    <!-- Linked Inbound Document -->
                    <div v-if="doc.inbound_document" class="mt-1">
                      <span class="badge badge-light border text-primary small">
                        <i class="fas fa-link mr-1"></i>ឯកសារចូលលេខ៖
                        {{ doc.inbound_document.general_inbound_number }}
                      </span>
                    </div>
                  </td>
                  <td>
                    <div class="font-weight-bold text-dark">{{ doc.drafted_by_user?.name_kh || doc.drafted_by_user?.name }}</div>
                    <small class="text-muted d-block">
                      {{ doc.department?.name_kh || doc.drafted_by_user?.department?.name_kh || 'និយ័តករបរធនបាលកិច្ច' }}
                    </small>
                  </td>
                  <td>
                    <div v-if="doc.current_approver">
                      <div class="font-weight-bold text-primary">
                        <i class="fas fa-user-clock mr-1"></i>{{ doc.current_approver.name_kh || doc.current_approver.name }}
                      </div>
                      <small class="text-muted">{{ doc.current_approver.position?.title_kh || doc.current_stage }}</small>
                    </div>
                    <div v-else-if="doc.status === 'APPROVED_BY_DG'">
                      <span class="badge badge-success px-2 py-1">
                        <i class="fas fa-check mr-1"></i>ឯកភាពដោយអគ្គនាយក
                      </span>
                    </div>
                    <div v-else class="text-muted small">--</div>
                  </td>
                  <td class="text-center">
                    <span
                      class="badge px-2 py-1 font-12"
                      :class="{
                        'badge-secondary': doc.status === 'DRAFT',
                        'badge-warning text-dark': doc.status === 'UNDER_REVIEW',
                        'badge-danger': doc.status === 'RETURNED',
                        'badge-success': doc.status === 'APPROVED_BY_DG',
                      }"
                    >
                      <i
                        class="fas mr-1"
                        :class="{
                          'fa-pencil-alt': doc.status === 'DRAFT',
                          'fa-clock': doc.status === 'UNDER_REVIEW',
                          'fa-undo': doc.status === 'RETURNED',
                          'fa-check-circle': doc.status === 'APPROVED_BY_DG',
                        }"
                      ></i>
                      {{ doc.status_kh || (doc.status === 'APPROVED_BY_DG' ? 'ឯកភាពដោយអគ្គនាយក' : (doc.status === 'UNDER_REVIEW' ? 'កំពុងឆ្លង' : (doc.status === 'RETURNED' ? 'បញ្ជូនត្រឡប់' : 'ព្រាង'))) }}
                    </span>
                  </td>
                  <td class="text-center">
                    <div class="btn-group btn-group-sm">
                      <!-- View Button -->
                      <button
                        type="button"
                        class="btn btn-outline-info"
                        title="មើលព័ត៌មានលម្អិត, ប្រវត្តិកែប្រែ & ប្រវត្តិឆ្លង"
                        @click="openViewModal(doc)"
                      >
                        <i class="fas fa-eye"></i>
                      </button>

                      <!-- Review Action Button (If I am the current approver) -->
                      <button
                        v-if="canReview(doc)"
                        type="button"
                        class="btn btn-outline-primary"
                        :title="isDg ? 'ពិនិត្យ និងចុះហត្ថលេខាឯកភាព' : 'ពិនិត្យ និងចារឆ្លងបន្ត'"
                        @click="openReviewActionModal(doc)"
                      >
                        <i class="fas fa-clipboard-check"></i>
                      </button>

                      <!-- Edit Button (If Drafter OR if Superior Approver during review) -->
                      <button
                        v-if="canEdit(doc)"
                        type="button"
                        class="btn btn-outline-warning"
                        :title="doc.status === 'UNDER_REVIEW' ? 'កែសម្រួលរបាយការណ៍ (ក្នុងនាមជាថ្នាក់ដឹកនាំ)' : 'កែសម្រួលលិខិត'"
                        @click="openEditModal(doc)"
                      >
                        <i class="fas fa-edit"></i>
                      </button>

                      <!-- Recall Button (If I drafted and currently UNDER_REVIEW) -->
                      <button
                        v-if="canRecall(doc)"
                        type="button"
                        class="btn btn-outline-danger"
                        title="ទាញយកមកវិញដើម្បីកែសម្រួល"
                        @click="recallAction(doc)"
                      >
                        <i class="fas fa-undo"></i>
                      </button>

                      <!-- Delete Button (If DRAFT) -->
                      <button
                        v-if="canDelete(doc)"
                        type="button"
                        class="btn btn-outline-danger"
                        title="លុបសេចក្តីព្រាង"
                        @click="deleteAction(doc)"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-2" v-if="pagination.total > 0">
            <small class="text-muted">
              បង្ហាញ {{ documents.length }} នៃ {{ pagination.total }} ឯកសារ
            </small>
            <ul class="pagination pagination-sm m-0">
              <li class="page-item" :class="{ disabled: pagination.current_page <= 1 }">
                <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page - 1)">« មុន</a>
              </li>
              <li class="page-item disabled">
                <span class="page-link font-weight-bold">ទំព័រ {{ pagination.current_page }} / {{ pagination.last_page }}</span>
              </li>
              <li class="page-item" :class="{ disabled: pagination.current_page >= pagination.last_page }">
                <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page + 1)">បន្ទាប់ »</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- ==========================================
         MODAL 1: CREATE / EDIT OUTBOUND DOCUMENT
         ========================================== -->
    <Teleport to="body">
      <div class="custom-modal-backdrop" v-if="showCreateModal">
        <div class="modal-dialog modal-xl modal-dialog-centered my-auto" style="width: 100%; max-width: 1180px;">
          <div class="modal-content shadow-2xl border-0 rounded-lg overflow-hidden d-flex flex-column" style="max-height: 94vh;">
            <div class="modal-header bg-dark text-white py-3 px-4 flex-shrink-0 align-items-center">
              <div class="d-flex align-items-center">
                <i class="fas fa-file-signature text-warning mr-2 fa-lg"></i>
                <div>
                  <h5 class="modal-title font-weight-bold mb-0">
                    <span v-if="isApproverEditing">កែសម្រួលរបាយការណ៍ (ថ្នាក់ដឹកនាំពិនិត្យ)</span>
                    <span v-else-if="isEditing">កែសម្រួលលិខិតឆ្លើយតប / របាយការណ៍</span>
                    <span v-else>រៀបចំលិខិតចេញ / របាយការណ៍ / កំណត់បង្ហាញ</span>
                  </h5>
                  <small class="text-white-50">សរសេរលើប្រព័ន្ធដូច File Word និងឆ្លងតាមឋានានុក្រមរហូតដល់ឯកឧត្តមអគ្គនាយក</small>
                </div>
              </div>
              <button type="button" class="close text-white" @click="showCreateModal = false">
                <span>&times;</span>
              </button>
            </div>

            <div class="modal-body p-4 flex-grow-1" style="overflow-y: auto; background-color: #f8f9fa;">
              <!-- Superior Editing Notice Banner -->
              <div class="alert alert-warning border-warning shadow-xs py-2 px-3 mb-3 d-flex align-items-center rounded-lg" v-if="isApproverEditing">
                <i class="fas fa-user-edit fa-2x text-warning mr-3"></i>
                <div>
                  <div class="font-weight-bold text-dark font-15">លោកអ្នកកំពុងកែសម្រួលរបាយការណ៍ក្នុងនាមជាថ្នាក់ដឹកនាំពិនិត្យ</div>
                  <div class="small text-muted">
                    រាល់ការកែប្រែទាំងអស់នឹងត្រូវបានកត់ត្រាទុកក្នុង <strong>«ប្រវត្តិកែប្រែ (Revisions & Diff)»</strong> ដោយស្វ័យប្រវត្តិដើម្បីឱ្យអ្នករៀបចំ និងថ្នាក់លើមើលឃើញច្បាស់នូវចំណុចដែលបានកែប្រែ។
                  </div>
                </div>
              </div>

              <!-- 1. Optional Link to Inbound Document -->
              <div class="card border rounded-lg p-3 bg-white mb-3 shadow-xs">
                <label class="form-label font-weight-bold text-dark d-flex align-items-center">
                  <i class="fas fa-link text-primary mr-2"></i>
                  ភ្ជាប់ជាមួយឯកសារចូលឧបសម្ព័ន្ធ (បើមាន)
                </label>
                <select class="form-control" v-model="form.inbound_document_id" @change="onInboundDocSelected">
                  <option :value="null">-- ពុំមានឯកសារចូលភ្ជាប់ --</option>
                  <option v-for="inDoc in options.inbound_documents" :key="inDoc.id" :value="inDoc.id">
                    [លេខ៖ {{ inDoc.general_inbound_number }}] {{ inDoc.title }} (ពី៖ {{ inDoc.sender_organization || 'ទូទៅ' }})
                  </option>
                </select>
                <small class="text-muted mt-1" v-if="selectedInboundDoc">
                  ចំណារឯកឧត្តមអគ្គនាយក៖ <span class="text-danger font-weight-bold">{{ selectedInboundDoc.dg_annotation || 'ពុំមាន' }}</span>
                </small>
              </div>

              <!-- 2. Document Type & Title -->
              <div class="card border rounded-lg p-3 bg-white mb-3 shadow-xs">
                <div class="row">
                  <div class="col-md-4 mb-2">
                    <label class="form-label font-weight-bold">ទម្រង់លិខិតចេញ <span class="text-danger">*</span></label>
                    <div class="btn-group btn-group-toggle w-100">
                      <label class="btn btn-outline-primary btn-sm font-khmer cursor-pointer" :class="{ active: form.response_type === 'REPORT' }">
                        <input type="radio" value="REPORT" v-model="form.response_type" />
                        <i class="fas fa-file-alt mr-1"></i> របាយការណ៍
                      </label>
                      <label class="btn btn-outline-info btn-sm font-khmer cursor-pointer" :class="{ active: form.response_type === 'SUBMISSION_NOTE' }">
                        <input type="radio" value="SUBMISSION_NOTE" v-model="form.response_type" />
                        <i class="fas fa-file-signature mr-1"></i> កំណត់បង្ហាញ
                      </label>
                      <label class="btn btn-outline-secondary btn-sm font-khmer cursor-pointer" :class="{ active: form.response_type === 'LETTER' }">
                        <input type="radio" value="LETTER" v-model="form.response_type" />
                        <i class="fas fa-envelope mr-1"></i> លិខិតទូទៅ
                      </label>
                    </div>
                  </div>

                  <div class="col-md-8 mb-2">
                    <label class="form-label font-weight-bold">កម្មវត្ថុលិខិត <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="ឧ. របាយការណ៍ស្តីពី..."
                      v-model="form.title"
                      required
                    />
                  </div>

                  <div class="col-12 mt-2">
                    <label class="form-label font-weight-bold small text-muted">យោង (Reference Note)</label>
                    <input
                      type="text"
                      class="form-control form-control-sm"
                      placeholder="យោងតាម..."
                      v-model="form.reference_note"
                    />
                  </div>

                  <!-- Change summary input when approver is editing -->
                  <div class="col-12 mt-2" v-if="isApproverEditing">
                    <label class="form-label font-weight-bold small text-primary">
                      <i class="fas fa-comment-dots mr-1"></i> សង្ខេបចំណុចកែប្រែ (Change Summary - បើមាន)
                    </label>
                    <input
                      type="text"
                      class="form-control form-control-sm border-primary"
                      placeholder="ឧ. បានកែសម្រួលកម្មវត្ថុ និងបន្ថែមព័ត៌មានក្នុងកថាខណ្ឌទី២..."
                      v-model="form.change_summary"
                    />
                  </div>
                </div>
              </div>

              <!-- 3. Word-like Editor Component -->
              <div class="mb-3">
                <WordEditor
                  v-model="form.content"
                  :doc-type="form.response_type"
                  :organization-name="form.header_organization || 'និយ័តករបរធនបាលកិច្ច'"
                  :department-name="userStore.department?.name_kh || 'នាយកដ្ឋាន...'"
                  :office-name="userStore.office?.name_kh || ''"
                  :officer-name="form.sign_name || userStore.name_kh || userStore.name"
                  :officer-position="form.sign_title || userStore.position?.title_kh || 'អ្នកធ្វើរបាយការណ៍'"
                  :approvals="currentEditingApprovals"
                />
              </div>

              <!-- 4. Next Approver & Scanned Attachment -->
              <div class="card border rounded-lg p-3 bg-white mb-2 shadow-xs">
                <div class="row">
                  <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label font-weight-bold">
                      <i class="fas fa-user-check text-success mr-1"></i> ជ្រើសរើសថ្នាក់ដឹកនាំដើម្បីឆ្លងបន្ត
                    </label>
                    <select class="form-control font-weight-bold text-dark" v-model="form.forwarded_to_id">
                      <option value="">-- ជ្រើសរើសថ្នាក់ដឹកនាំបន្ទាប់ (ឬទុកទទេបើគ្រាន់តែរក្សាទុកកែប្រែ) --</option>
                      <option v-for="r in options.recipients" :key="r.id" :value="r.id">
                        {{ r.name_kh || r.name }} - {{ r.position?.title_kh || '' }} ({{ r.office?.name_kh || r.department?.name_kh || '' }})
                      </option>
                    </select>
                    <small class="text-muted">ជ្រើសរើសដើម្បីចារឆ្លងឡើងលើបន្ត ឬទុកទទេបើចង់រក្សាទុកក្នុងដៃសិន</small>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label font-weight-bold">
                      <i class="fas fa-paperclip text-primary mr-1"></i> ភ្ជាប់ឯកសារ Scan បន្ថែម (បើមាន)
                    </label>
                    <div class="custom-file">
                      <input
                        type="file"
                        class="custom-file-input"
                        id="outboundFileInput"
                        @change="handleFileChange"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                      />
                      <label class="custom-file-label" for="outboundFileInput">
                        {{ selectedFileName || (form.file_name ? form.file_name : 'ជ្រើសរើសឯកសារភ្ជាប់...') }}
                      </label>
                    </div>
                  </div>
                </div>

                <div class="mt-2" v-if="form.forwarded_to_id">
                  <label class="form-label font-weight-bold small text-muted">កំណត់សម្គាល់ឆ្លង / មតិគោរពជូន</label>
                  <input
                    type="text"
                    class="form-control form-control-sm"
                    placeholder="សូមគោរពជូនឯកឧត្តម/លោក/លោកស្រី..."
                    v-model="form.comment"
                  />
                </div>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light py-2 px-4 justify-content-between flex-shrink-0">
              <button type="button" class="btn btn-secondary px-3" @click="showCreateModal = false">
                បិទផ្ទាំង
              </button>

              <!-- Footer Buttons: Distinct actions for Approver editing vs Drafter -->
              <div v-if="isApproverEditing">
                <button
                  type="button"
                  class="btn btn-warning px-3 mr-2 font-weight-bold text-dark"
                  @click="submitForm(false, false)"
                  :disabled="submitting"
                  title="រក្សាទុកកំណែថ្មីដែលបានកែសម្រួល ដោយមិនទាន់បញ្ជូនបន្ត"
                >
                  <i class="fas fa-save mr-1"></i> រក្សាទុកការកែប្រែ (Save Changes)
                </button>
                <button
                  type="button"
                  class="btn btn-primary px-4 font-weight-bold"
                  @click="submitForm(false, true)"
                  :disabled="submitting || !form.forwarded_to_id"
                  title="រក្សាទុកកំណែថ្មី និងចារឆ្លងបន្តទៅថ្នាក់ដឹកនាំបន្ទាប់"
                >
                  <i class="fas fa-paper-plane mr-1"></i> រក្សាទុក & ចារឆ្លងបន្ត
                </button>
              </div>

              <div v-else>
                <button
                  type="button"
                  class="btn btn-outline-primary px-3 mr-2"
                  @click="submitForm(true, false)"
                  :disabled="submitting"
                >
                  <i class="fas fa-save mr-1"></i> រក្សាទុកជាព្រាង
                </button>
                <button
                  type="button"
                  class="btn btn-primary px-4 font-weight-bold"
                  @click="submitForm(false, true)"
                  :disabled="submitting || !form.forwarded_to_id"
                >
                  <i class="fas fa-paper-plane mr-1"></i> ដាក់ស្នើឆ្លង
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ==========================================
         MODAL 2: REVIEW ACTION (FORWARD / RETURN / APPROVE)
         ========================================== -->
    <Teleport to="body">
      <div class="custom-modal-backdrop" v-if="showReviewModal && currentReviewDoc">
        <div class="modal-dialog modal-lg modal-dialog-centered my-auto" style="width: 100%; max-width: 750px;">
          <div class="modal-content shadow-2xl border-0 rounded-lg overflow-hidden">
            <div class="modal-header bg-primary text-white py-3 px-4">
              <h5 class="modal-title font-weight-bold">
                <i class="fas fa-clipboard-check mr-2"></i>
                {{ isDg ? 'ពិនិត្យ និងសម្រេចលើលិខិតឆ្លើយតប' : 'ពិនិត្យ និងចារឆ្លងលិខិត' }}
              </h5>
              <button type="button" class="close text-white" @click="showReviewModal = false">
                <span>&times;</span>
              </button>
            </div>

            <div class="modal-body p-4 bg-light">
              <!-- Summary Card -->
              <div class="card border rounded-lg p-3 bg-white mb-3 shadow-xs">
                <div class="font-weight-bold font-15 text-dark mb-1">{{ currentReviewDoc.title }}</div>
                <div class="small text-muted mb-2">
                  ប្រភេទ៖ <strong class="text-primary">{{ currentReviewDoc.response_type_kh || currentReviewDoc.response_type }}</strong>
                  <span class="mx-2">•</span>
                  អ្នករៀបចំ៖ <strong>{{ currentReviewDoc.drafted_by_user?.name_kh || currentReviewDoc.drafted_by_user?.name }}</strong>
                </div>
                <div v-if="currentReviewDoc.reference_note" class="small text-muted">
                  យោង៖ {{ currentReviewDoc.reference_note }}
                </div>
              </div>

              <!-- Action Choices -->
              <div class="form-group mb-3">
                <label class="font-weight-bold text-dark">ជ្រើសរើសសកម្មភាព <span class="text-danger">*</span></label>
                <div class="d-flex flex-wrap gap-3">
                  <!-- Option 1: Forward (for Subordinates) -->
                  <div class="custom-control custom-radio mr-4" v-if="!isDg">
                    <input
                      type="radio"
                      id="revActionForward"
                      class="custom-control-input"
                      value="FORWARD"
                      v-model="reviewForm.action"
                    />
                    <label class="custom-control-label font-weight-bold text-primary cursor-pointer" for="revActionForward">
                      <i class="fas fa-share mr-1"></i> ចារបញ្ជូនបន្តទៅថ្នាក់លើ (Forward Upward)
                    </label>
                  </div>

                  <!-- Option 2: DG Approve (for DG / Admin) -->
                  <div class="custom-control custom-radio mr-4" v-if="isDg">
                    <input
                      type="radio"
                      id="revActionApprove"
                      class="custom-control-input"
                      value="DG_APPROVE"
                      v-model="reviewForm.action"
                    />
                    <label class="custom-control-label font-weight-bold text-success cursor-pointer" for="revActionApprove">
                      <i class="fas fa-check-double mr-1"></i> ឯកភាពលើលិខិត (DG Approve)
                    </label>
                  </div>

                  <!-- Option 3: Return for Edit (Available for all approvers) -->
                  <div class="custom-control custom-radio">
                    <input
                      type="radio"
                      id="revActionReturn"
                      class="custom-control-input"
                      value="RETURN"
                      v-model="reviewForm.action"
                    />
                    <label class="custom-control-label font-weight-bold text-danger cursor-pointer" for="revActionReturn">
                      <i class="fas fa-undo mr-1"></i> បញ្ជូនត្រឡប់ទៅអ្នករៀបចំឱ្យកែសម្រួល (Return for Edit)
                    </label>
                  </div>
                </div>
              </div>

              <!-- Next Approver (if FORWARD) -->
              <div class="form-group mb-3" v-if="reviewForm.action === 'FORWARD'">
                <label class="font-weight-bold text-dark">ជ្រើសរើសថ្នាក់ដឹកនាំដើម្បីឆ្លងបន្ត <span class="text-danger">*</span></label>
                <select class="form-control" v-model="reviewForm.forwarded_to_id" required>
                  <option value="" disabled>-- សូមជ្រើសរើសថ្នាក់ដឹកនាំ --</option>
                  <option v-for="r in options.recipients" :key="'rev_' + r.id" :value="r.id">
                    {{ r.name_kh || r.name }} - {{ r.position?.title_kh || '' }} ({{ r.office?.name_kh || r.department?.name_kh || '' }})
                  </option>
                </select>
              </div>

              <!-- Response Number (if DG_APPROVE) -->
              <div class="form-group mb-3" v-if="reviewForm.action === 'DG_APPROVE'">
                <label class="font-weight-bold text-dark">លេខលិខិតចេញផ្លូវការ (Response Number)</label>
                <input
                  type="text"
                  class="form-control"
                  placeholder="ឧ. ១២៣ ន.ប.ធ./..."
                  v-model="reviewForm.response_number"
                />
                <small class="text-muted">អាចកត់ត្រាលេខចេញផ្លូវការដែលបានចុះបញ្ជី ឬទុកឱ្យប្រព័ន្ធបង្កើត</small>
              </div>

              <!-- Comment -->
              <div class="form-group mb-0">
                <label class="font-weight-bold text-dark">មតិយោបល់ / ចំណារពិនិត្យ</label>
                <textarea
                  class="form-control"
                  rows="3"
                  placeholder="កត់ត្រាមតិយោបល់ ឬចំណារណែនាំ..."
                  v-model="reviewForm.comment"
                ></textarea>
              </div>
            </div>

            <div class="modal-footer bg-white py-2 px-4 justify-content-between">
              <button type="button" class="btn btn-secondary px-3" @click="showReviewModal = false">
                បោះបង់
              </button>
              <button
                type="button"
                class="btn px-4 font-weight-bold"
                :class="reviewForm.action === 'RETURN' ? 'btn-danger' : 'btn-success'"
                @click="submitReviewAction"
                :disabled="submittingReview"
              >
                <i class="fas fa-check mr-1"></i> អនុវត្តសកម្មភាព
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ==========================================
         MODAL 3: VIEW DETAIL, REVISIONS & DIFF, APPROVAL TIMELINE
         ========================================== -->
    <Teleport to="body">
      <div class="custom-modal-backdrop" v-if="showViewModal && currentViewDoc">
        <div class="modal-dialog modal-xl modal-dialog-centered my-auto" style="width: 100%; max-width: 1200px;">
          <div class="modal-content shadow-2xl border-0 rounded-lg overflow-hidden d-flex flex-column" style="max-height: 94vh;">
            <div class="modal-header bg-dark text-white py-3 px-4 flex-shrink-0 align-items-center">
              <div class="d-flex align-items-center">
                <i class="fas fa-file-alt text-info mr-2 fa-lg"></i>
                <div>
                  <h5 class="modal-title font-weight-bold mb-0">
                    {{ currentViewDoc.title }}
                  </h5>
                  <small class="text-white-50">លេខ៖ {{ currentViewDoc.response_number || 'មិនទាន់ចុះលេខ' }} • ប្រភេទ៖ {{ currentViewDoc.response_type_kh || currentViewDoc.response_type }}</small>
                </div>
              </div>
              <button type="button" class="close text-white" @click="showViewModal = false">
                <span>&times;</span>
              </button>
            </div>

            <div class="modal-body p-4 flex-grow-1" style="overflow-y: auto; background-color: #525659;">
              <!-- 3 Tabs Navigation Bar -->
              <div class="bg-white p-2 rounded-lg mb-3 shadow-xs d-flex flex-wrap justify-content-between align-items-center">
                <ul class="nav nav-pills m-0">
                  <li class="nav-item mr-2">
                    <a
                      class="nav-link font-weight-bold cursor-pointer"
                      :class="{ active: viewTab === 'doc' }"
                      @click="viewTab = 'doc'"
                    >
                      <i class="fas fa-file-alt mr-1"></i> ខ្លឹមសារលិខិតផ្លូវការ
                    </a>
                  </li>
                  <li class="nav-item mr-2">
                    <a
                      class="nav-link font-weight-bold cursor-pointer"
                      :class="{ active: viewTab === 'revisions' }"
                      @click="viewTab = 'revisions'"
                    >
                      <i class="fas fa-code-branch mr-1 text-warning"></i>
                      ប្រវត្តិកែប្រែ & ផ្ទៀងផ្ទាត់ (Revisions & Diff)
                      <span class="badge badge-warning ml-1" v-if="docRevisions.length > 1">
                        {{ docRevisions.length }} កំណែ
                      </span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a
                      class="nav-link font-weight-bold cursor-pointer"
                      :class="{ active: viewTab === 'timeline' }"
                      @click="viewTab = 'timeline'"
                    >
                      <i class="fas fa-history mr-1"></i> ប្រវត្តិឆ្លង និងចំណារ (Timeline)
                    </a>
                  </li>
                </ul>

                <!-- Action button on tab 1: Print -->
                <div v-if="viewTab === 'doc'">
                  <button type="button" class="btn btn-sm btn-outline-dark font-weight-bold" @click="printCurrentViewDoc">
                    <i class="fas fa-print mr-1"></i> បោះពុម្ព / ទាញយក PDF
                  </button>
                </div>
              </div>

              <!-- TAB 1: FORMATTED WORD DOCUMENT PREVIEW -->
              <div v-show="viewTab === 'doc'">
                <div class="d-flex justify-content-center">
                  <div class="document-paper-preview bg-white shadow-lg rounded-sm" style="width: 100%; max-width: 794px; min-height: 1123px; padding: 45px 55px; color: #111; line-height: 2.1; box-sizing: border-box; text-rendering: optimizeLegibility; -webkit-font-smoothing: antialiased;">
                    <div v-html="currentViewDoc.content || '<em class=\'text-muted\'>ពុំមានខ្លឹមសារ</em>'"></div>

                    <!-- Attachment Download if attached -->
                    <div v-if="currentViewDoc.file_path" class="mt-5 pt-3 border-top">
                      <div class="small font-weight-bold text-muted mb-1">ឯកសារភ្ជាប់ Scan បន្ថែម (Attachment)៖</div>
                      <a
                        :href="getOutboundFileDownloadUrl(currentViewDoc.id)"
                        target="_blank"
                        class="btn btn-sm btn-outline-primary"
                      >
                        <i class="fas fa-paperclip mr-1"></i> ទាញយកឯកសារភ្ជាប់ ({{ currentViewDoc.file_name || 'ឯកសារ' }})
                      </a>
                    </div>
                  </div>
                </div>

                <!-- Workflow Approvals Record Card below Document Preview -->
                <div class="card border rounded-lg p-3 bg-white shadow-xs mt-4 mx-auto" style="max-width: 794px;" v-if="currentViewDoc.approvals && currentViewDoc.approvals.length > 0">
                  <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <div class="font-weight-bold text-dark font-15">
                      <i class="fas fa-clipboard-check text-primary mr-1"></i>
                      កំណត់ត្រាមតិយោបល់ និងការសម្រេចរបស់ថ្នាក់ដឹកនាំក្នុងប្រព័ន្ធ (Workflow Approvals Record)
                    </div>
                    <span class="badge badge-primary px-2 py-1">
                      {{ currentViewDoc.approvals.length }} ថ្នាក់ដឹកនាំបានពិនិត្យ
                    </span>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                      <thead class="bg-light">
                        <tr>
                          <th style="width: 25%;">ថ្នាក់ដឹកនាំ / តួនាទី</th>
                          <th style="width: 20%;">ដំណាក់កាល / សកម្មភាព</th>
                          <th style="width: 18%;">កាលបរិច្ឆេទ</th>
                          <th>ខ្លឹមសារមតិយោបល់ / ចំណារ</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="app in currentViewDoc.approvals" :key="app.id">
                          <td class="font-weight-bold text-dark">
                            {{ app.user?.name_kh || app.user?.name }}
                            <div class="small font-weight-normal text-muted">{{ app.user?.position?.title_kh || app.stage_kh }}</div>
                          </td>
                          <td>
                            <span class="badge" :class="getApprovalActionBadgeClass(app.action)">
                              {{ app.action_kh }}
                            </span>
                            <div class="small text-muted">{{ app.stage_kh }}</div>
                          </td>
                          <td class="small">{{ formatDateTime(app.created_at) }}</td>
                          <td>
                            <div v-if="app.comment" class="font-weight-bold text-dark">{{ app.comment }}</div>
                            <div v-else class="text-muted small font-italic">(ពុំមានកត់ត្រាមតិយោបល់)</div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- TAB 2: APPROVAL TIMELINE -->
              <div v-show="viewTab === 'timeline'">
                <div class="card border rounded-lg p-4 bg-white shadow-xs mx-auto" style="max-width: 900px;">
                  <div class="timeline m-0" v-if="currentViewDoc.approvals && currentViewDoc.approvals.length > 0">
                    <div v-for="app in currentViewDoc.approvals" :key="app.id" class="mb-3">
                      <i
                        class="fas"
                        :class="{
                          'fa-paper-plane bg-primary text-white': app.action === 'SUBMIT',
                          'fa-edit bg-warning text-dark': app.action === 'EDIT',
                          'fa-share bg-info text-white': app.action === 'FORWARD',
                          'fa-undo bg-danger text-white': app.action === 'RETURN',
                          'fa-check-circle bg-success text-white': app.action === 'DG_APPROVE',
                        }"
                      ></i>
                      <div class="timeline-item shadow-none border rounded">
                        <span class="time text-muted"><i class="fas fa-clock mr-1"></i>{{ formatDate(app.created_at) }}</span>
                        <h3 class="timeline-header font-14 font-weight-bold">
                          {{ app.user?.name_kh || app.user?.name }}
                          <span class="badge badge-light border ml-1">{{ app.user?.position?.title_kh || app.stage }}</span>
                          <span class="text-muted font-normal ml-1">បានអនុវត្តសកម្មភាព៖</span>
                          <span
                            class="badge ml-1 px-2 py-1"
                            :class="{
                              'badge-primary': app.action === 'SUBMIT',
                              'badge-warning text-dark': app.action === 'EDIT',
                              'badge-info': app.action === 'FORWARD',
                              'badge-danger': app.action === 'RETURN',
                              'badge-success': app.action === 'DG_APPROVE',
                            }"
                          >
                            {{ formatActionName(app.action) }}
                          </span>
                        </h3>
                        <div class="timeline-body font-13 text-dark">
                          {{ app.comment || 'ពុំមានចំណារបន្ថែម' }}
                          <div v-if="app.forwarded_to" class="mt-1 small text-muted">
                            <i class="fas fa-arrow-right text-primary mr-1"></i> ចារជូនបន្តទៅកាន់៖
                            <strong>{{ app.forwarded_to.name_kh || app.forwarded_to.name }}</strong>
                            ({{ app.forwarded_to.position?.title_kh || '' }})
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-center py-5 text-muted">
                    <i class="fas fa-history fa-3x mb-2 d-block text-black-50"></i>
                    ពុំទាន់មានប្រវត្តិឆ្លងនៅឡើយ
                  </div>
                </div>
              </div>

              <!-- TAB 3: REVISIONS & DIFF VIEWER (ផ្ទៀងផ្ទាត់ចំណុចកែប្រែ) -->
              <div v-show="viewTab === 'revisions'">
                <div class="card border rounded-lg p-3 bg-white shadow-xs mx-auto mb-3" style="max-width: 1050px;">
                  <!-- No Revisions Alert -->
                  <div v-if="docRevisions.length <= 1" class="text-center py-4">
                    <i class="fas fa-info-circle fa-3x text-info mb-2 d-block"></i>
                    <h5 class="font-weight-bold text-dark">ឯកសារនេះមានត្រឹមតែ ១ កំណែដើម (Original Draft) ប៉ុណ្ណោះ</h5>
                    <p class="text-muted small m-0">ពុំទាន់មានការកែសម្រួលខ្លឹមសារពីថ្នាក់ដឹកនាំនៅឡើយទេ។ នៅពេលមានថ្នាក់លើចូលកែសម្រួល ប្រព័ន្ធនឹងរំលេចពណ៌បង្ហាញការកែប្រែនៅទីនេះ។</p>
                  </div>

                  <!-- Has Multiple Revisions: Show Diff Tools -->
                  <div v-else>
                    <!-- Controls Bar -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-3 mb-3">
                      <div class="d-flex flex-wrap align-items-center">
                        <!-- From Revision Selector -->
                        <div class="mr-2 mb-1">
                          <label class="small font-weight-bold text-muted d-block mb-1">កំណែដើម / កំណែចាស់ (From)៖</label>
                          <select class="form-control form-control-sm font-weight-bold" v-model="selectedFromRevId" style="min-width: 220px;">
                            <option v-for="rev in docRevisions" :key="'from_' + rev.id" :value="rev.id">
                              កំណែទី {{ rev.revision_number }} - {{ rev.user?.name_kh || rev.user?.name }} ({{ formatDateShort(rev.created_at) }})
                            </option>
                          </select>
                        </div>

                        <div class="mr-2 mb-1 text-center align-self-end pb-1">
                          <i class="fas fa-arrow-right text-muted fa-lg"></i>
                        </div>

                        <!-- To Revision Selector -->
                        <div class="mr-3 mb-1">
                          <label class="small font-weight-bold text-muted d-block mb-1">កំណែថ្មី / កំណែប្រៀបធៀប (To)៖</label>
                          <select class="form-control form-control-sm font-weight-bold" v-model="selectedToRevId" style="min-width: 220px;">
                            <option v-for="rev in docRevisions" :key="'to_' + rev.id" :value="rev.id">
                              កំណែទី {{ rev.revision_number }} - {{ rev.user?.name_kh || rev.user?.name }} ({{ formatDateShort(rev.created_at) }})
                            </option>
                          </select>
                        </div>
                      </div>

                      <!-- Diff View Mode Toggle & Legend -->
                      <div class="d-flex align-items-center flex-wrap mb-1">
                        <div class="btn-group btn-group-sm mr-3">
                          <button
                            type="button"
                            class="btn font-weight-bold"
                            :class="diffMode === 'inline' ? 'btn-primary' : 'btn-outline-secondary'"
                            @click="diffMode = 'inline'"
                            title="បង្ហាញពណ៌រំលេចបន្ថែម និងលុបផ្ទាល់ក្នុងឯកសារ"
                          >
                            <i class="fas fa-stream mr-1"></i> រំលេចផ្ទាល់ (Inline Diff)
                          </button>
                          <button
                            type="button"
                            class="btn font-weight-bold"
                            :class="diffMode === 'split' ? 'btn-primary' : 'btn-outline-secondary'"
                            @click="diffMode = 'split'"
                            title="មើលផ្ទឹមសងខាង"
                          >
                            <i class="fas fa-columns mr-1"></i> ផ្ទឹមសងខាង (Side-by-Side)
                          </button>
                        </div>

                        <!-- Color Legend -->
                        <div class="d-inline-flex align-items-center small font-weight-bold">
                          <span class="badge badge-success px-2 py-1 mr-2" style="background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc;">
                            <i class="fas fa-plus mr-1"></i> បន្ថែមថ្មី
                          </span>
                          <span class="badge badge-danger px-2 py-1" style="background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7;">
                            <i class="fas fa-minus mr-1"></i> លុបចេញ
                          </span>
                        </div>
                      </div>
                    </div>

                    <!-- Change Details Banner -->
                    <div class="alert alert-light border rounded-lg p-3 mb-3 d-flex justify-content-between align-items-center" v-if="toRevision">
                      <div>
                        <div class="font-weight-bold text-dark font-14">
                          <i class="fas fa-user-edit text-primary mr-1"></i>
                          កែសម្រួលដោយ៖ <strong>{{ toRevision.user?.name_kh || toRevision.user?.name }}</strong>
                          <span class="badge badge-info ml-1">{{ toRevision.user?.position?.title_kh || 'ថ្នាក់ដឹកនាំ' }}</span>
                          <span class="text-muted ml-2 font-normal">({{ formatDate(toRevision.created_at) }})</span>
                        </div>
                        <div class="small text-muted mt-1" v-if="toRevision.change_summary">
                          <i class="fas fa-quote-left mr-1 text-black-50"></i>
                          សេចក្តីសង្ខេប៖ <strong>{{ toRevision.change_summary }}</strong>
                        </div>
                      </div>
                      <div>
                        <span class="badge badge-warning px-3 py-2 font-13 font-weight-bold">
                          កំណែទី {{ toRevision.revision_number }}
                        </span>
                      </div>
                    </div>

                    <!-- Diff 1: Title Diff (if changed) -->
                    <div class="card border rounded p-3 mb-3 bg-light" v-if="diffTitle && fromRevision?.title !== toRevision?.title">
                      <div class="small font-weight-bold text-muted mb-1">
                        <i class="fas fa-heading mr-1 text-primary"></i> ការកែប្រែកម្មវត្ថុ (Subject / Title Change)៖
                      </div>
                      <div class="font-15 font-weight-bold p-2 bg-white border rounded" v-html="diffTitle"></div>
                    </div>

                    <!-- Diff 2: Reference Diff (if changed) -->
                    <div class="card border rounded p-3 mb-3 bg-light" v-if="diffReference && fromRevision?.reference_note !== toRevision?.reference_note">
                      <div class="small font-weight-bold text-muted mb-1">
                        <i class="fas fa-quote-right mr-1 text-info"></i> ការកែប្រែយោង (Reference Note Change)៖
                      </div>
                      <div class="font-14 p-2 bg-white border rounded" v-html="diffReference"></div>
                    </div>

                    <!-- Diff 3: Content Body Diff -->
                    <div class="mb-3">
                      <div class="small font-weight-bold text-muted mb-2">
                        <i class="fas fa-file-alt mr-1 text-primary"></i> ខ្លឹមសារលម្អិតដែលបានកែសម្រួល (Content Diff)៖
                      </div>

                      <!-- Mode A: Inline Diff -->
                      <div v-if="diffMode === 'inline'" class="document-paper-preview bg-white shadow-sm p-4 rounded border" style="min-height: 450px; line-height: 1.8;">
                        <div v-html="diffContent || '<em class=\'text-muted\'>ពុំមានការកែប្រែខ្លឹមសារ</em>'"></div>
                      </div>

                      <!-- Mode B: Side-by-Side Split Diff -->
                      <div v-else class="row">
                        <!-- Left: Old Revision -->
                        <div class="col-md-6 mb-2">
                          <div class="card border h-100 shadow-none">
                            <div class="card-header bg-light py-2 px-3 font-weight-bold text-muted small d-flex justify-content-between">
                              <span>កំណែទី {{ fromRevision?.revision_number }} (ចាស់)</span>
                              <span>{{ fromRevision?.user?.name_kh || fromRevision?.user?.name }}</span>
                            </div>
                            <div class="card-body p-3 font-14" style="max-height: 500px; overflow-y: auto;" v-html="fromRevision?.content || ''"></div>
                          </div>
                        </div>

                        <!-- Right: New Revision -->
                        <div class="col-md-6 mb-2">
                          <div class="card border border-primary h-100 shadow-none">
                            <div class="card-header bg-primary text-white py-2 px-3 font-weight-bold small d-flex justify-content-between">
                              <span>កំណែទី {{ toRevision?.revision_number }} (ថ្មី)</span>
                              <span>{{ toRevision?.user?.name_kh || toRevision?.user?.name }}</span>
                            </div>
                            <div class="card-body p-3 font-14" style="max-height: 500px; overflow-y: auto;" v-html="toRevision?.content || ''"></div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- All Revisions History Table -->
                    <div class="mt-4 pt-3 border-top">
                      <div class="font-weight-bold text-dark font-14 mb-2">
                        <i class="fas fa-history text-secondary mr-1"></i> បញ្ជីប្រវត្តិកំណែទាំងអស់ (All Revision History)
                      </div>
                      <div class="table-responsive">
                        <table class="table table-sm table-hover border">
                          <thead class="bg-light text-muted small">
                            <tr>
                              <th style="width: 80px;" class="text-center">កំណែ</th>
                              <th style="width: 180px;">អ្នកកែសម្រួល</th>
                              <th style="width: 160px;">កាលបរិច្ឆេទ</th>
                              <th>សេចក្តីសង្ខេបនៃការកែប្រែ</th>
                              <th style="width: 140px;" class="text-center">សកម្មភាព</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="rev in docRevisions" :key="'tbl_rev_' + rev.id" :class="{ 'table-warning': rev.id === toRevision?.id }">
                              <td class="text-center font-weight-bold">
                                <span class="badge badge-dark">#{{ rev.revision_number }}</span>
                              </td>
                              <td class="font-weight-bold text-dark">
                                {{ rev.user?.name_kh || rev.user?.name }}
                                <small class="d-block text-muted">{{ rev.user?.position?.title_kh || 'មន្ត្រី' }}</small>
                              </td>
                              <td class="small text-muted">{{ formatDate(rev.created_at) }}</td>
                              <td class="small text-dark">{{ rev.change_summary || '--' }}</td>
                              <td class="text-center">
                                <button
                                  type="button"
                                  class="btn btn-xs btn-outline-primary font-weight-bold"
                                  @click="selectRevisionForCompare(rev)"
                                  title="ជ្រើសរើសកំណែនេះដើម្បីប្រៀបធៀប"
                                >
                                  <i class="fas fa-check mr-1"></i> ផ្ទៀងផ្ទាត់
                                </button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer bg-light py-2 px-4 justify-content-end flex-shrink-0">
              <button type="button" class="btn btn-secondary px-4" @click="showViewModal = false">
                បិទ
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import { useUserStore } from '@/stores/user';
import WordEditor from '@/components/common/WordEditor.vue';
import { renderDiffHtml } from '@/utils/diffHelper';
import {
  apiGetOutboundOptions,
  apiGetOutboundDocuments,
  apiGetOutboundDocument,
  apiCreateOutboundDocument,
  apiUpdateOutboundDocument,
  apiReviewOutboundAction,
  apiRecallOutboundDocument,
  apiDeleteOutboundDocument,
  getOutboundFileDownloadUrl,
} from '@/functions/api/outboundDocument';

const userStore = useUserStore();
const route = useRoute();

const activeTab = ref('doing'); // doing, in_progress, complete
const loading = ref(false);
const documents = ref([]);
const stats = reactive({ doing: 0, in_progress: 0, complete: 0 });

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
});

const filters = reactive({
  search: '',
  response_type: '',
  from_date: '',
});

const options = reactive({
  inbound_documents: [],
  recipients: [],
});

// Modals
const showCreateModal = ref(false);
const isEditing = ref(false);
const editingDocId = ref(null);
const currentEditingApprovals = ref([]);
const isApproverEditing = ref(false);
const submitting = ref(false);
const selectedFile = ref(null);
const selectedFileName = ref('');

const form = reactive({
  inbound_document_id: null,
  response_type: 'REPORT',
  title: '',
  header_organization: '',
  reference_note: '',
  content: '',
  sign_title: '',
  sign_name: '',
  forwarded_to_id: '',
  comment: '',
  change_summary: '',
  file_name: '',
});

// Review Modal
const showReviewModal = ref(false);
const currentReviewDoc = ref(null);
const submittingReview = ref(false);
const reviewForm = reactive({
  action: 'FORWARD', // FORWARD, RETURN, DG_APPROVE
  forwarded_to_id: '',
  response_number: '',
  comment: '',
});

// View Modal
const showViewModal = ref(false);
const currentViewDoc = ref(null);
const viewTab = ref('doc'); // 'doc', 'timeline', 'revisions'
const diffMode = ref('inline'); // 'inline', 'split'
const selectedFromRevId = ref(null);
const selectedToRevId = ref(null);

const isDg = computed(() => {
  const level = userStore.position ? Number(userStore.position.level) : 99;
  return level <= 1 || (userStore.level && userStore.level.toUpperCase() === 'ADMIN');
});

const selectedInboundDoc = computed(() => {
  if (!form.inbound_document_id) return null;
  return options.inbound_documents.find(d => d.id === form.inbound_document_id) || null;
});

// Revisions Computations
const docRevisions = computed(() => {
  if (!currentViewDoc.value || !currentViewDoc.value.revisions) return [];
  return [...currentViewDoc.value.revisions].sort((a, b) => a.revision_number - b.revision_number);
});

const fromRevision = computed(() => {
  if (!docRevisions.value.length) return null;
  if (selectedFromRevId.value) {
    const found = docRevisions.value.find(r => r.id === selectedFromRevId.value);
    if (found) return found;
  }
  if (docRevisions.value.length >= 2) {
    return docRevisions.value[docRevisions.value.length - 2];
  }
  return docRevisions.value[0];
});

const toRevision = computed(() => {
  if (!docRevisions.value.length) return null;
  if (selectedToRevId.value) {
    const found = docRevisions.value.find(r => r.id === selectedToRevId.value);
    if (found) return found;
  }
  return docRevisions.value[docRevisions.value.length - 1];
});

const diffTitle = computed(() => {
  if (!fromRevision.value || !toRevision.value) return '';
  return renderDiffHtml(fromRevision.value.title || '', toRevision.value.title || '');
});

const diffReference = computed(() => {
  if (!fromRevision.value || !toRevision.value) return '';
  return renderDiffHtml(fromRevision.value.reference_note || '', toRevision.value.reference_note || '');
});

const diffContent = computed(() => {
  if (!fromRevision.value || !toRevision.value) return '';
  return renderDiffHtml(fromRevision.value.content || '', toRevision.value.content || '');
});

const selectRevisionForCompare = (rev) => {
  selectedToRevId.value = rev.id;
  const idx = docRevisions.value.findIndex(r => r.id === rev.id);
  if (idx > 0) {
    selectedFromRevId.value = docRevisions.value[idx - 1].id;
  } else {
    selectedFromRevId.value = rev.id;
  }
};

const onInboundDocSelected = () => {
  if (selectedInboundDoc.value) {
    const doc = selectedInboundDoc.value;
    if (!form.title) {
      form.title = `របាយការណ៍ឆ្លើយតបលើ៖ ${doc.title}`;
    }
    form.reference_note = `យោង៖ ឯកសារចូលលេខ ${doc.general_inbound_number} ចុះថ្ងៃទី ${doc.received_date || ''}`;
  }
};

const switchTab = (tab) => {
  activeTab.value = tab;
  pagination.current_page = 1;
  loadDocuments();
};

const loadDocuments = async () => {
  loading.value = true;
  try {
    const params = {
      tab: activeTab.value,
      page: pagination.current_page,
      per_page: pagination.per_page,
      search: filters.search || undefined,
      response_type: filters.response_type || undefined,
      from_date: filters.from_date || undefined,
    };
    const res = await apiGetOutboundDocuments(params);
    documents.value = res.data.data || [];
    pagination.current_page = res.data.current_page;
    pagination.last_page = res.data.last_page;
    pagination.total = res.data.total;
    if (res.data.stats) {
      stats.doing = res.data.stats.doing || 0;
      stats.in_progress = res.data.stats.in_progress || 0;
      stats.complete = res.data.stats.complete || 0;
    }
  } catch (err) {
    console.error('Failed to load documents:', err);
  } finally {
    loading.value = false;
  }
};

const resetFilters = () => {
  filters.search = '';
  filters.response_type = '';
  filters.from_date = '';
  pagination.current_page = 1;
  loadDocuments();
};

const changePage = (page) => {
  if (page < 1 || page > pagination.last_page) return;
  pagination.current_page = page;
  loadDocuments();
};

const loadOptions = async () => {
  try {
    const res = await apiGetOutboundOptions();
    options.inbound_documents = res.data.inbound_documents || [];
    options.recipients = res.data.recipients || [];
  } catch (err) {
    console.error('Failed to load options:', err);
  }
};

const openCreateModal = () => {
  isEditing.value = false;
  editingDocId.value = null;
  currentEditingApprovals.value = [];
  isApproverEditing.value = false;
  form.inbound_document_id = route.query.link_inbound_id ? Number(route.query.link_inbound_id) : null;
  form.response_type = 'REPORT';
  form.title = '';
  form.header_organization = 'និយ័តករបរធនបាលកិច្ច';
  form.reference_note = '';
  form.content = '';
  form.sign_title = userStore.position?.title_kh || 'អ្នកធ្វើរបាយការណ៍';
  form.sign_name = userStore.name_kh || userStore.name || '';
  form.forwarded_to_id = '';
  form.comment = '';
  form.change_summary = '';
  form.file_name = '';
  selectedFile.value = null;
  selectedFileName.value = '';

  if (form.inbound_document_id) {
    onInboundDocSelected();
  }

  showCreateModal.value = true;
};

const openEditModal = (doc) => {
  isEditing.value = true;
  editingDocId.value = doc.id;
  currentEditingApprovals.value = doc.approvals || [];
  isApproverEditing.value = (doc.status === 'UNDER_REVIEW' && (doc.current_approver_id === userStore.id || (userStore.level && userStore.level.toUpperCase() === 'ADMIN')));

  form.inbound_document_id = doc.inbound_document_id;
  form.response_type = doc.response_type || 'REPORT';
  form.title = doc.title || '';
  form.header_organization = doc.header_organization || 'និយ័តករបរធនបាលកិច្ច';
  form.reference_note = doc.reference_note || '';
  form.content = doc.content || '';
  form.sign_title = doc.sign_title || userStore.position?.title_kh || '';
  form.sign_name = doc.sign_name || userStore.name_kh || userStore.name || '';
  form.forwarded_to_id = isApproverEditing.value ? '' : (doc.current_approver_id || '');
  form.comment = '';
  form.change_summary = '';
  form.file_name = doc.file_name || '';
  selectedFile.value = null;
  selectedFileName.value = '';

  showCreateModal.value = true;
};

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedFile.value = file;
    selectedFileName.value = file.name;
  }
};

const submitForm = async (isDraft, willForward = true) => {
  if (document.activeElement && typeof document.activeElement.blur === 'function') {
    document.activeElement.blur();
  }
  if (!form.title) {
    Swal.fire({ icon: 'warning', title: 'សូមបំពេញ', text: 'សូមបញ្ចូលកម្មវត្ថុលិខិត!' });
    return;
  }
  if (!isDraft && willForward && !form.forwarded_to_id) {
    Swal.fire({ icon: 'warning', title: 'សូមជ្រើសរើស', text: 'សូមជ្រើសរើសថ្នាក់ដឹកនាំដើម្បីឆ្លងបន្ត!' });
    return;
  }

  submitting.value = true;
  try {
    const fd = new FormData();
    if (form.inbound_document_id) fd.append('inbound_document_id', form.inbound_document_id);
    fd.append('response_type', form.response_type);
    fd.append('title', form.title);
    if (form.header_organization) fd.append('header_organization', form.header_organization);
    if (form.reference_note) fd.append('reference_note', form.reference_note);
    if (form.content) fd.append('content', form.content);
    if (form.sign_title) fd.append('sign_title', form.sign_title);
    if (form.sign_name) fd.append('sign_name', form.sign_name);
    if (willForward && form.forwarded_to_id) fd.append('forwarded_to_id', form.forwarded_to_id);
    if (form.comment) fd.append('comment', form.comment);
    if (form.change_summary) fd.append('change_summary', form.change_summary);
    fd.append('is_draft', isDraft ? '1' : '0');
    if (selectedFile.value) {
      fd.append('response_file', selectedFile.value);
    }

    if (isEditing.value && editingDocId.value) {
      await apiUpdateOutboundDocument(editingDocId.value, fd);
    } else {
      await apiCreateOutboundDocument(fd);
    }

    let successMsg = 'បានដាក់ស្នើឆ្លងលិខិតទៅកាន់ថ្នាក់ដឹកនាំដោយជោគជ័យ!';
    if (isApproverEditing.value) {
      successMsg = willForward ? 'បានរក្សាទុកការកែសម្រួល និងចារឆ្លងបន្តដោយជោគជ័យ!' : 'បានរក្សាទុកការកែសម្រួលជាកំណែថ្មីដោយជោគជ័យ!';
    } else if (isDraft) {
      successMsg = 'បានរក្សាទុកសេចក្តីព្រាងដោយជោគជ័យ!';
    }

    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: successMsg,
      timer: 1800,
      showConfirmButton: false,
    });

    showCreateModal.value = false;
    loadDocuments();
  } catch (err) {
    const errorMsg = err.response?.data?.message || 'មិនអាចរក្សាទុកបានឡើយ!';
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: errorMsg });
  } finally {
    submitting.value = false;
  }
};

const openReviewActionModal = (doc) => {
  currentReviewDoc.value = doc;
  reviewForm.action = isDg.value ? 'DG_APPROVE' : 'FORWARD';
  reviewForm.forwarded_to_id = '';
  reviewForm.response_number = doc.response_number || '';
  reviewForm.comment = '';
  showReviewModal.value = true;
};

const submitReviewAction = async () => {
  if (document.activeElement && typeof document.activeElement.blur === 'function') {
    document.activeElement.blur();
  }
  if (reviewForm.action === 'FORWARD' && !reviewForm.forwarded_to_id) {
    Swal.fire({ icon: 'warning', title: 'សូមជ្រើសរើស', text: 'សូមជ្រើសរើសថ្នាក់ដឹកនាំដើម្បីចារឆ្លងបន្ត!' });
    return;
  }

  submittingReview.value = true;
  try {
    await apiReviewOutboundAction(currentReviewDoc.value.id, {
      action: reviewForm.action,
      forwarded_to_id: reviewForm.forwarded_to_id || undefined,
      response_number: reviewForm.response_number || undefined,
      comment: reviewForm.comment || undefined,
    });

    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: 'បានអនុវត្តសកម្មភាពពិនិត្យឆ្លងដោយជោគជ័យ!',
      timer: 1800,
      showConfirmButton: false,
    });

    showReviewModal.value = false;
    loadDocuments();
  } catch (err) {
    const errorMsg = err.response?.data?.message || 'មិនអាចអនុវត្តសកម្មភាពបានឡើយ!';
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: errorMsg });
  } finally {
    submittingReview.value = false;
  }
};

const openViewModal = async (doc) => {
  currentViewDoc.value = doc;
  viewTab.value = 'doc';
  showViewModal.value = true;

  try {
    const res = await apiGetOutboundDocument(doc.id);
    if (res.data?.data) {
      currentViewDoc.value = res.data.data;
      if (res.data.data.revisions?.length >= 2) {
        const sorted = [...res.data.data.revisions].sort((a, b) => a.revision_number - b.revision_number);
        selectedFromRevId.value = sorted[sorted.length - 2].id;
        selectedToRevId.value = sorted[sorted.length - 1].id;
      } else if (res.data.data.revisions?.length === 1) {
        selectedFromRevId.value = res.data.data.revisions[0].id;
        selectedToRevId.value = res.data.data.revisions[0].id;
      }
    }
  } catch (err) {
    console.error('Failed to load document details:', err);
  }
};

const printCurrentViewDoc = () => {
  if (!currentViewDoc.value) return;
  const printWindow = window.open('', '_blank');
  let content = currentViewDoc.value.content || '';

  // If approvals exist and document content does NOT already have the review table, append clean official review table
  if (currentViewDoc.value.approvals && currentViewDoc.value.approvals.length > 0 && !content.includes('review-signatures-wrapper')) {
    let reviewRows = '';
    currentViewDoc.value.approvals.forEach(app => {
      const dt = app.created_at ? new Date(app.created_at).toLocaleDateString('km-KH') : '';
      reviewRows += `
        <tr>
          <td style="border: 1px solid #333; padding: 8px;"><strong>${app.user?.name_kh || app.user?.name}</strong><br><small>${app.user?.position?.title_kh || app.stage_kh}</small></td>
          <td style="border: 1px solid #333; padding: 8px; text-align: center;">${app.action_kh}</td>
          <td style="border: 1px solid #333; padding: 8px; text-align: center;">${dt}</td>
          <td style="border: 1px solid #333; padding: 8px;">${app.comment || '-'}</td>
        </tr>
      `;
    });
    content += `
      <div style="margin-top: 35px; page-break-inside: avoid; break-inside: avoid;">
        <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt; color: #002060; margin-bottom: 8px; border-bottom: 1.5px solid #002060; padding-bottom: 3px;">
          កំណត់ត្រាមតិយោបល់ និងការសម្រេចរបស់ថ្នាក់ដឹកនាំក្នុងប្រព័ន្ធ (Workflow Approvals)៖
        </div>
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #333; font-size: 10.5pt; line-height: 2.1;">
          <thead>
            <tr style="background-color: #f2f2f2;">
              <th style="border: 1px solid #333; padding: 6px; width: 28%;">ថ្នាក់ដឹកនាំ / តួនាទី</th>
              <th style="border: 1px solid #333; padding: 6px; width: 18%;">សកម្មភាព</th>
              <th style="border: 1px solid #333; padding: 6px; width: 18%;">កាលបរិច្ឆេទ</th>
              <th style="border: 1px solid #333; padding: 6px;">ខ្លឹមសារមតិយោបល់ / ចំណារ</th>
            </tr>
          </thead>
          <tbody>
            ${reviewRows}
          </tbody>
        </table>
      </div>
    `;
  }

  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>${currentViewDoc.value.title || 'ឯកសាររដ្ឋបាល'}</title>
        <style>
          @import url('https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap');
          @page {
            size: A4 portrait;
            margin: 20mm 20mm 20mm 25mm;
          }
          body {
            font-family: 'Khmer OS Battambang', 'Battambang', sans-serif;
            font-size: 12pt;
            line-height: 2.1 !important;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
            text-rendering: optimizeLegibility;
          }
          p, div { line-height: 2.1 !important; }
          table { width: 100%; border-collapse: collapse; line-height: 2.1 !important; }
          tr { page-break-inside: avoid !important; break-inside: avoid !important; }
          .page-break {
            page-break-before: always !important;
            break-before: page !important;
            border-top: none !important;
            margin-top: 0 !important;
            padding-top: 10mm !important;
          }
          .page-break-badge { display: none !important; }
          .page-number-header {
            text-align: center !important;
            margin-bottom: 25px !important;
            font-weight: bold !important;
            font-size: 12pt !important;
            color: #000 !important;
          }
          .review-signatures-wrapper {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
          }
        </style>
      </head>
      <body>
        ${content}
        <script>
          window.onload = function() {
            window.print();
            window.onafterprint = function() { window.close(); };
          };
        <\/script>
      </body>
    </html>
  `);
  printWindow.document.close();
};

const recallAction = async (doc) => {
  const result = await Swal.fire({
    title: 'តើលោកអ្នកចង់ទាញយកមកវិញមែនទេ?',
    text: 'លិខិតនេះនឹងត្រឡប់ទៅជា «សេចក្តីព្រាង» វិញដើម្បីឱ្យលោកអ្នកអាចកែសម្រួលបាន!',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'ទាញយកមកវិញ',
    cancelButtonText: 'បោះបង់',
  });
  if (!result.isConfirmed) return;

  try {
    await apiRecallOutboundDocument(doc.id);
    Swal.fire({ icon: 'success', title: 'បានទាញយកមកវិញ!', timer: 1500, showConfirmButton: false });
    loadDocuments();
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: err.response?.data?.message || 'មិនអាចទាញយកមកវិញបានឡើយ!' });
  }
};

const deleteAction = async (doc) => {
  const result = await Swal.fire({
    title: 'តើលោកអ្នកពិតជាចង់លុបមែនទេ?',
    text: 'សេចក្តីព្រាងនេះនឹងត្រូវលុបចេញពីប្រព័ន្ធ!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    confirmButtonText: 'លុបចេញ',
    cancelButtonText: 'បោះបង់',
  });
  if (!result.isConfirmed) return;

  try {
    await apiDeleteOutboundDocument(doc.id);
    Swal.fire({ icon: 'success', title: 'បានលុប!', timer: 1500, showConfirmButton: false });
    loadDocuments();
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: err.response?.data?.message || 'មិនអាចលុបបានឡើយ!' });
  }
};

// Permission Helpers
const canReview = (doc) => {
  if (doc.status !== 'UNDER_REVIEW') return false;
  return doc.current_approver_id === userStore.id || (userStore.level && userStore.level.toUpperCase() === 'ADMIN');
};

const canEdit = (doc) => {
  if (['DRAFT', 'RETURNED'].includes(doc.status)) {
    return doc.drafted_by === userStore.id || (userStore.level && userStore.level.toUpperCase() === 'ADMIN');
  }
  // Superior / Approver can edit while UNDER_REVIEW
  if (doc.status === 'UNDER_REVIEW') {
    return doc.current_approver_id === userStore.id || (userStore.level && userStore.level.toUpperCase() === 'ADMIN');
  }
  return false;
};

const canRecall = (doc) => {
  if (doc.status !== 'UNDER_REVIEW') return false;
  return doc.drafted_by === userStore.id;
};

const canDelete = (doc) => {
  if (doc.status !== 'DRAFT') return false;
  return doc.drafted_by === userStore.id || (userStore.level && userStore.level.toUpperCase() === 'ADMIN');
};

const formatDate = (val) => {
  if (!val) return '--';
  const d = new Date(val);
  return d.toLocaleDateString('km-KH') + ' ' + d.toLocaleTimeString('km-KH', { hour: '2-digit', minute: '2-digit' });
};

const formatDateShort = (val) => {
  if (!val) return '--';
  const d = new Date(val);
  return d.toLocaleDateString('km-KH');
};

const formatActionName = (action) => {
  switch (action) {
    case 'SUBMIT': return 'ដាក់ស្នើឆ្លង';
    case 'EDIT': return 'កែសម្រួលខ្លឹមសារ';
    case 'FORWARD': return 'ចារបញ្ជូនបន្ត';
    case 'RETURN': return 'បញ្ជូនត្រឡប់';
    case 'DG_APPROVE': return 'ឯកភាពស្ថាពរ';
    case 'RECALL': return 'ទាញយកមកវិញ';
    default: return action;
  }
};

onMounted(() => {
  loadOptions();
  loadDocuments();
});
</script>

<style scoped>
.stat-card {
  transition: all 0.25s ease-in-out;
  cursor: pointer;
}
.stat-card:hover {
  transform: translateY(-2px);
}
.active-tab {
  border-width: 2px !important;
  background-color: #ffffff !important;
}
.custom-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.65);
  z-index: 1055;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}
.document-paper-preview {
  font-family: 'Khmer OS Battambang', 'Battambang', sans-serif;
  font-size: 12pt;
}
</style>
