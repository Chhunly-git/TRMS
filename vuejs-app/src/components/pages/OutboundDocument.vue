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
        <!-- Stats & Tabs Bar (All, Doing, In Progress, Complete) -->
        <div class="row mb-3">
          <!-- Tab 0: All (ឯកសារទាំងអស់ដែលខ្លួនមានសិទ្ធិ) -->
          <div class="col-xl-3 col-md-6 mb-2">
            <div
              class="card stat-card shadow-sm cursor-pointer border-0 h-100"
              :class="{ 'active-tab border-primary shadow': activeTab === 'all' }"
              @click="switchTab('all')"
            >
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <div class="text-uppercase small font-weight-bold text-muted">ឯកសារទាំងអស់ (All)</div>
                  <div class="h3 font-weight-bold m-0 text-primary">{{ stats.all || 0 }}</div>
                  <small class="text-muted">ឯកសារទាំងអស់ដែលខ្លួនមានសិទ្ធិ</small>
                </div>
                <div class="stat-icon bg-primary-subtle text-primary rounded-circle p-3">
                  <i class="fas fa-layer-group fa-2x"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Tab 1: Doing (ត្រូវធ្វើ / កំពុងរៀបចំ / ត្រូវពិនិត្យ) -->
          <div class="col-xl-3 col-md-6 mb-2">
            <div
              class="card stat-card shadow-sm cursor-pointer border-0 h-100"
              :class="{ 'active-tab border-warning shadow': activeTab === 'doing' }"
              @click="switchTab('doing')"
            >
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <div class="text-uppercase small font-weight-bold text-muted">១. ត្រូវរៀបចំ (Pending)</div>
                  <div class="h3 font-weight-bold m-0 text-warning">{{ stats.doing || 0 }}</div>
                  <small class="text-muted">លិខិតរង់ចាំលោកអ្នកពិនិត្យ ឬកំពុងព្រាង</small>
                </div>
                <div class="stat-icon bg-warning-subtle text-warning rounded-circle p-3">
                  <i class="fas fa-edit fa-2x"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Tab 2: In Progress (កំពុងដំណើរការ / បានឆ្លងបន្ត) -->
          <div class="col-xl-3 col-md-6 mb-2">
            <div
              class="card stat-card shadow-sm cursor-pointer border-0 h-100"
              :class="{ 'active-tab border-info shadow': activeTab === 'in_progress' }"
              @click="switchTab('in_progress')"
            >
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <div class="text-uppercase small font-weight-bold text-muted">២. កំពុងដំណើរការ (In Progress)</div>
                  <div class="h3 font-weight-bold m-0 text-info">{{ stats.in_progress || 0 }}</div>
                  <small class="text-muted">បានដាក់ឆ្លង និងកំពុងរង់ចាំថ្នាក់ដឹកនាំពិនិត្យ</small>
                </div>
                <div class="stat-icon bg-info-subtle text-info rounded-circle p-3">
                  <i class="fas fa-tasks fa-2x"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Tab 3: Complete (បានអនុម័ត / រួចរាល់) -->
          <div class="col-xl-3 col-md-6 mb-2">
            <div
              class="card stat-card shadow-sm cursor-pointer border-0 h-100"
              :class="{ 'active-tab border-success shadow': activeTab === 'complete' }"
              @click="switchTab('complete')"
            >
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <div class="text-uppercase small font-weight-bold text-muted">៣. បានអនុម័តស្ថាពរ (Complete)</div>
                  <div class="h3 font-weight-bold m-0 text-success">{{ stats.complete || 0 }}</div>
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
                  <th style="width: 150px;">លេខលិខិតចេញ</th>
                  <th style="width: 130px;">ប្រភេទ</th>
                  <th>កម្មវត្ថុ & យោង</th>
                  <th style="width: 200px;" class="text-center">ស្ថានភាព</th>
                  <th style="width: 160px;" class="text-center">សកម្មភាព</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="6" class="text-center py-5 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block"></i>
                    កំពុងទាញយកទិន្នន័យ...
                  </td>
                </tr>
                <tr v-else-if="documents.length === 0">
                  <td colspan="6" class="text-center py-5 text-muted">
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
                    <div class="small text-muted mt-1" v-if="doc.created_at">
                      <i class="far fa-calendar-alt mr-1"></i>{{ formatDateShort(doc.created_at) }}
                    </div>
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
                      <i class="fas fa-quote-left mr-1 text-black-50"></i>{{ formatReferenceText(doc.reference_note) }}
                    </div>
                    <!-- Linked Inbound Document -->
                    <div v-if="doc.inbound_document" class="mt-1 d-flex flex-wrap align-items-center gap-1">
                      <span class="badge badge-light border text-primary small">
                        <i class="fas fa-link mr-1"></i>ឯកសារចូលលេខ៖
                        {{ doc.inbound_document.general_inbound_number }}
                      </span>
                      <span v-if="doc.inbound_document.received_date" class="small text-muted ml-1">
                        ({{ formatDateShort(doc.inbound_document.received_date) }})
                      </span>
                    </div>
                  </td>
                  <td class="text-center align-middle" style="min-width: 180px;">
                    <!-- Status Badge -->
                    <div class="mb-1">
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
                    </div>

                    <!-- Single Avatar: Person who currently holds the document in hand -->
                    <div class="d-flex align-items-center justify-content-center mt-1" v-if="getOutboundCurrentHolder(doc)">
                      <div
                        class="workflow-avatar-wrapper position-relative"
                        :title="getOutboundCurrentHolder(doc).title"
                      >
                        <img
                          v-if="getOutboundCurrentHolder(doc).avatarUrl"
                          :src="getOutboundCurrentHolder(doc).avatarUrl"
                          :alt="getOutboundCurrentHolder(doc).name"
                          class="workflow-avatar"
                          :class="getOutboundCurrentHolder(doc).borderClass"
                          @error="(e) => { e.target.style.display = 'none'; if (e.target.nextElementSibling) e.target.nextElementSibling.style.display = 'flex'; }"
                        />
                        <div
                          class="workflow-avatar-fallback font-weight-bold"
                          :class="getOutboundCurrentHolder(doc).fallbackClass"
                          :style="{ display: getOutboundCurrentHolder(doc).avatarUrl ? 'none' : 'flex' }"
                        >
                          {{ getOutboundCurrentHolder(doc).initial }}
                        </div>
                      </div>
                    </div>
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

                      <!-- Delete Button (If DRAFT or RETURNED) -->
                      <button
                        v-if="canDelete(doc)"
                        type="button"
                        class="btn btn-outline-danger"
                        :title="doc.status === 'RETURNED' ? 'លុបឯកសារដែលថ្នាក់ដឹកនាំបញ្ជូនត្រឡប់' : 'លុបសេចក្តីព្រាង'"
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
                  :officer-signature="userStore.signature_image || ''"
                  :approvals="currentEditingApprovals"
                  :is-approver="isApproverEditing"
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
                      <option v-for="r in availableRecipients" :key="r.id" :value="r.id">
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
              <div class="d-flex align-items-center">
                <button type="button" class="btn btn-secondary px-3" @click="showCreateModal = false">
                  បិទផ្ទាំង
                </button>
                <button
                  v-if="isEditing && currentEditingDoc && canDelete(currentEditingDoc)"
                  type="button"
                  class="btn btn-outline-danger btn-sm ml-2 font-weight-bold"
                  @click="deleteAction(currentEditingDoc)"
                  :title="currentEditingDoc.status === 'RETURNED' ? 'លុបឯកសារដែលថ្នាក់ដឹកនាំបញ្ជូនត្រឡប់' : 'លុបសេចក្តីព្រាង'"
                >
                  <i class="fas fa-trash mr-1"></i> {{ currentEditingDoc.status === 'RETURNED' ? 'លុបឯកសារនេះ' : 'លុបសេចក្តីព្រាង' }}
                </button>
              </div>

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
        <div class="modal-dialog modal-dialog-centered my-auto" :style="reviewStep === 'position' ? 'width: 100%; max-width: 980px;' : 'width: 100%; max-width: 750px;'">
          <div class="modal-content shadow-2xl border-0 rounded-lg overflow-hidden">
            <div class="modal-header bg-primary text-white py-3 px-4 d-flex justify-content-between align-items-center">
              <h5 class="modal-title font-weight-bold mb-0">
                <i class="fas fa-clipboard-check mr-2"></i>
                <span v-if="reviewStep === 'form'">
                  {{ isDg ? 'ពិនិត្យ និងសម្រេចលើលិខិតឆ្លើយតប' : 'ពិនិត្យ និងចារឆ្លងលិខិត' }}
                </span>
                <span v-else>
                  <i class="fas fa-arrows-alt mr-2"></i>រៀបចំទីតាំងមតិយោបល់ & ហត្ថលេខាលើរបាយការណ៍
                </span>
              </h5>
              <div class="d-flex align-items-center">
                <span v-if="reviewStep === 'position'" class="badge badge-light text-primary font-weight-bold mr-3 px-2 py-1">
                  ជំហានទី ២ / ២
                </span>
                <button type="button" class="close text-white" @click="showReviewModal = false">
                  <span>&times;</span>
                </button>
              </div>
            </div>

            <!-- STEP 1: FORM -->
            <div class="modal-body p-4 bg-light" v-if="reviewStep === 'form'">
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
                  <option v-for="r in availableRecipients" :key="'rev_' + r.id" :value="r.id">
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
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="font-weight-bold text-dark mb-0">មតិយោបល់ / ចំណារពិនិត្យ</label>
                  <div class="d-flex flex-wrap gap-1">
                    <button
                      type="button"
                      class="btn btn-xs btn-outline-primary mr-1 mb-1 font-weight-bold"
                      @click="reviewForm.comment = 'សូមគោរពជូនឯកឧត្តមអគ្គនាយក\nមេត្តាពិនិត្យ និងសម្រេច'"
                    >
                      + គោរពជូន ឯ.ឧ អគ្គនាយក
                    </button>
                    <button
                      type="button"
                      class="btn btn-xs btn-outline-info mr-1 mb-1 font-weight-bold"
                      @click="reviewForm.comment = 'សូមគោរពជូនលោកប្រធាននាយកដ្ឋាន\nមេត្តាពិនិត្យ និងសម្រេច'"
                    >
                      + គោរពជូន ប្រធាននាយកដ្ឋាន
                    </button>
                    <button
                      type="button"
                      class="btn btn-xs btn-outline-success mr-1 mb-1 font-weight-bold"
                      @click="reviewForm.comment = 'បានពិនិត្យ និងឯកភាព'"
                    >
                      + បានពិនិត្យ និងឯកភាព
                    </button>
                  </div>
                </div>
                <textarea
                  class="form-control"
                  rows="3"
                  placeholder="កត់ត្រាមតិយោបល់ ឬចំណារណែនាំ..."
                  v-model="reviewForm.comment"
                ></textarea>
              </div>

              <!-- Leader's Review & Signature Placement onto Report -->
              <div class="card border rounded-lg p-3 bg-white mt-3 shadow-xs" v-if="reviewForm.action !== 'RETURN'">
                <div class="custom-control custom-checkbox mb-2">
                  <input
                    type="checkbox"
                    class="custom-control-input"
                    id="embedReviewCheck"
                    v-model="reviewForm.embed_into_document"
                  />
                  <label class="custom-control-label font-weight-bold text-primary cursor-pointer" for="embedReviewCheck">
                    <i class="fas fa-file-signature mr-1"></i> បញ្ចូលមតិយោបល់ និងហត្ថលេខាទៅក្នុងឯកសាររបាយការណ៍
                  </label>
                </div>

                <div v-if="reviewForm.embed_into_document" class="pl-3 border-left ml-2">
                  <div class="row">
                    <div class="col-md-6 mb-2">
                      <label class="small font-weight-bold text-dark mb-1">
                        <i class="fas fa-map-marker-alt text-danger mr-1"></i> ជ្រើសរើសទីតាំងចាប់ផ្តើមលើរបាយការណ៍៖
                      </label>
                      <select class="form-control form-control-sm font-weight-bold" v-model="reviewForm.placement">
                        <option value="SIDE_BY_SIDE_LEFT">ទន្ទឹមហត្ថលេខាអ្នករៀបចំ (ខាងឆ្វេង) - ស្តង់ដារ</option>
                        <option value="ABOVE_DRAFTER">ខាងលើហត្ថលេខាអ្នករៀបចំ</option>
                        <option value="BELOW_DRAFTER">ខាងក្រោមហត្ថលេខាអ្នករៀបចំ</option>
                        <option value="BOTTOM">ខាងក្រោមបង្អស់នៃរបាយការណ៍ (ចុងបញ្ចប់)</option>
                      </select>
                    </div>
                    <div class="col-md-6 mb-2">
                      <label class="small font-weight-bold text-dark mb-1">
                        <i class="fas fa-calendar-alt text-primary mr-1"></i> កាលបរិច្ឆេទចុះហត្ថលេខា៖
                      </label>
                      <input type="text" class="form-control form-control-sm" v-model="reviewForm.sign_date" placeholder="ឧ. ៤/១១/២៦" />
                    </div>
                  </div>

                  <!-- Signature Preview & Drawing -->
                  <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light border mt-1">
                    <div class="d-flex align-items-center">
                      <span class="small font-weight-bold text-muted mr-2">ហត្ថលេខា៖</span>
                      <img v-if="userStore.signature_image" :src="userStore.signature_image" alt="ហត្ថលេខា" style="height: 38px; max-width: 140px; object-fit: contain;" />
                      <span v-else class="small text-primary font-weight-bold" style="font-family: 'Brush Script MT', 'Segoe Script', cursive; font-size: 16pt;">
                        {{ userStore.name_kh || userStore.name }}
                      </span>
                    </div>
                    <button
                      type="button"
                      class="btn btn-xs btn-outline-primary font-weight-bold"
                      @click="showReviewSignatureModal = true"
                    >
                      <i class="fas fa-pen mr-1"></i> {{ userStore.signature_image ? 'កែប្រែហត្ថលេខា' : '+ គូស/បង្ហោះហត្ថលេខា' }}
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- STEP 2: POSITION ON REPORT (DRAGGABLE) -->
            <div class="modal-body p-3 bg-light" v-if="reviewStep === 'position'">
              <!-- Info Alert -->
              <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center justify-content-between shadow-xs">
                <div class="small">
                  <i class="fas fa-arrows-alt text-primary mr-2 fa-lg"></i>
                  <span><strong>ការណែនាំ៖</strong>
                    • <strong>រំកិល៖</strong> ចុចហើយអូសប្រអប់ទៅទីតាំងសមស្រប |
                    • <strong>ពង្រីក-បង្រួម៖</strong> ចុចអូសចំណុច Handle ជុំវិញប្រអប់ |
                    • <strong>កែប្រែមតិ/កាលបរិច្ឆេទ៖</strong> ចុចផ្ទាល់លើអក្សរក្នុងប្រអប់ដើម្បីកែសម្រួល
                  </span>
                </div>
                <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold" @click="resetReviewPosition">
                  <i class="fas fa-undo mr-1"></i> កំណត់ទីតាំងដើម
                </button>
              </div>

              <!-- Document Sheet Scroll Area -->
              <div class="review-paper-scroll" style="max-height: 62vh; overflow-y: auto; background-color: #525659; padding: 20px; border-radius: 6px; display: flex; justify-content: center;">
                <div
                  ref="reviewPositionPaperRef"
                  class="review-position-paper bg-white shadow-lg"
                  style="width: 210mm; min-height: 297mm; padding: 25mm 20mm 20mm 25mm; box-sizing: border-box; position: relative;"
                  v-html="reviewWorkingHtml"
                ></div>
              </div>
            </div>

            <div class="modal-footer bg-white py-2 px-4 justify-content-between">
              <div v-if="reviewStep === 'position'">
                <button type="button" class="btn btn-outline-secondary px-3" @click="reviewStep = 'form'">
                  <i class="fas fa-arrow-left mr-1"></i> ត្រឡប់ក្រោយកែព័ត៌មាន
                </button>
              </div>
              <div v-else>
                <button type="button" class="btn btn-secondary px-3" @click="showReviewModal = false">
                  បោះបង់
                </button>
              </div>

              <div>
                <!-- In Step 1 -->
                <template v-if="reviewStep === 'form'">
                  <button
                    v-if="reviewForm.embed_into_document && reviewForm.action !== 'RETURN' && currentReviewDoc.content"
                    type="button"
                    class="btn btn-primary px-4 font-weight-bold"
                    @click="goToReviewPositionStep"
                  >
                    <span>រៀបចំទីតាំងលើរបាយការណ៍ <i class="fas fa-arrow-right ml-1"></i></span>
                  </button>
                  <button
                    v-else
                    type="button"
                    class="btn px-4 font-weight-bold"
                    :class="reviewForm.action === 'RETURN' ? 'btn-danger' : 'btn-success'"
                    @click="submitReviewAction"
                    :disabled="submittingReview"
                  >
                    <i class="fas fa-check mr-1"></i> {{ reviewForm.action === 'RETURN' ? 'បញ្ជូនត្រឡប់កែសម្រួល' : 'អនុវត្តសកម្មភាព' }}
                  </button>
                </template>

                <!-- In Step 2 -->
                <template v-else>
                  <button
                    type="button"
                    class="btn btn-warning px-3 font-weight-bold mr-2 text-dark shadow-xs"
                    @click="saveAndHoldReview"
                    :disabled="submittingReview"
                    title="រក្សាទុកមតិយោបល់ និងទីតាំងក្នុងដៃសិន ដោយមិនទាន់បញ្ជូនទៅថ្នាក់លើ"
                  >
                    <i class="fas fa-save mr-1"></i> រក្សាទុកក្នុងដៃសិន (ចាំចារឆ្លងតាមក្រោយ)
                  </button>
                  <button
                    type="button"
                    class="btn btn-success px-4 font-weight-bold"
                    @click="submitReviewAction"
                    :disabled="submittingReview"
                  >
                    <i class="fas fa-paper-plane mr-1"></i> {{ reviewForm.action === 'DG_APPROVE' ? 'ឯកភាព & ចុះហត្ថលេខាផ្លូវការ' : 'យល់ព្រម & ចារឆ្លងបន្ត' }}
                  </button>
                </template>
              </div>
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
                <!-- Floating Draggable Save Bar (shown only when boxes have been dragged AND user has document in hand) -->
                <div v-if="hasMovedOpinionBox && canEditPositionInDetail" class="alert alert-warning shadow-sm border d-flex justify-content-between align-items-center py-2 px-3 mx-auto mb-3" style="max-width: 794px;">
                  <div>
                    <i class="fas fa-arrows-alt text-primary mr-2"></i>
                    <strong class="text-dark">លោកអ្នកបានផ្លាស់ប្តូរទីតាំងមតិយោបល់ ឬហត្ថលេខា!</strong>
                    <span class="small text-muted ml-1">សូមចុច «រក្សាទុកទីតាំងថ្មី» ដើម្បីរក្សាទុកការផ្លាស់ប្តូរ</span>
                  </div>
                  <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold mr-2" @click="resetOpinionBoxPosition">
                      <i class="fas fa-undo mr-1"></i> ត្រឡប់ដើម
                    </button>
                    <button type="button" class="btn btn-sm btn-success font-weight-bold shadow-xs" :disabled="savingMovedPosition" @click="saveNewOpinionBoxPosition">
                      <i class="fas fa-save mr-1"></i> {{ savingMovedPosition ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកទីតាំងថ្មី' }}
                    </button>
                  </div>
                </div>

                <div class="d-flex justify-content-center">
                  <div ref="previewPaperRef" class="document-paper-preview bg-white shadow-lg rounded-sm" :style="previewPaperStyle" v-html="currentViewDoc.content || '<em class=\'text-muted\'>ពុំមានខ្លឹមសារ</em>'"></div>
                </div>

                <!-- References & Attachments Card (ឯកសារយោង ឬឧបសម្ព័ន្ធ) -->
                <div class="card border rounded-lg p-3 bg-white shadow-xs mt-4 mx-auto" style="max-width: 794px;">
                  <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <div class="font-weight-bold text-dark font-15">
                      <i class="fas fa-paperclip text-primary mr-2"></i>
                      ឯកសារយោង ឬឧបសម្ព័ន្ធ (References & Attachments)
                    </div>
                    <span
                      class="badge px-2 py-1"
                      :class="(currentViewDoc.inbound_document || currentViewDoc.reference_note || currentViewDoc.file_path) ? 'badge-primary' : 'badge-light text-muted'"
                    >
                      <i class="fas fa-folder-open mr-1"></i>
                      {{ (currentViewDoc.inbound_document || currentViewDoc.reference_note || currentViewDoc.file_path) ? 'មានឯកសារយោង/ឧបសម្ព័ន្ធ' : 'គ្មានឯកសារយោង' }}
                    </span>
                  </div>

                  <!-- 1. Linked Inbound Document (ឯកសារចូលយោង) -->
                  <div v-if="currentViewDoc.inbound_document" class="mb-3 p-3 rounded border bg-light">
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                      <div class="font-weight-bold text-primary font-14">
                        <i class="fas fa-link mr-1"></i> ឯកសារចូលយោង (Linked Inbound Document)
                      </div>
                      <span v-if="currentViewDoc.inbound_document.received_date" class="small text-muted">
                        <i class="far fa-calendar-alt mr-1"></i> កាលបរិច្ឆេទទទួល៖ {{ formatDateShort(currentViewDoc.inbound_document.received_date) }}
                      </span>
                    </div>

                    <div class="row small mb-2">
                      <div class="col-md-6 mb-1">
                        <span class="text-muted">លេខចូលទូទៅ៖</span>
                        <strong class="text-dark ml-1">{{ currentViewDoc.inbound_document.general_inbound_number || '-' }}</strong>
                      </div>
                      <div class="col-md-6 mb-1" v-if="currentViewDoc.inbound_document.dg_inbound_number">
                        <span class="text-muted">លេខចូល ឯ.ឧ អគ្គនាយក៖</span>
                        <strong class="text-danger ml-1">{{ currentViewDoc.inbound_document.dg_inbound_number }}</strong>
                      </div>
                      <div class="col-12 mb-1" v-if="currentViewDoc.inbound_document.sender_organization">
                        <span class="text-muted">មកពីស្ថាប័ន/អង្គភាព៖</span>
                        <strong class="text-dark ml-1">{{ currentViewDoc.inbound_document.sender_organization }}</strong>
                      </div>
                      <div class="col-12 mb-1">
                        <span class="text-muted">កម្មវត្ថុ៖</span>
                        <strong class="text-dark ml-1">{{ currentViewDoc.inbound_document.title }}</strong>
                      </div>
                    </div>

                    <!-- DG Annotation if any -->
                    <div v-if="currentViewDoc.inbound_document.dg_annotation" class="p-2 mb-2 rounded bg-white border border-danger-subtle small">
                      <span class="font-weight-bold text-danger"><i class="fas fa-pen-nib mr-1"></i> ចំណារ ឯ.ឧ អគ្គនាយក៖</span>
                      <span class="text-dark ml-1">{{ currentViewDoc.inbound_document.dg_annotation }}</span>
                    </div>

                    <!-- Inbound File View/Download Buttons -->
                    <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top">
                      <a
                        v-if="currentViewDoc.inbound_document.original_file_path"
                        :href="getViewOriginalUrl(currentViewDoc.inbound_document.id)"
                        target="_blank"
                        class="btn btn-sm btn-outline-primary"
                      >
                        <i class="fas fa-eye mr-1"></i> មើលឯកសារចូលដើម
                      </a>
                      <a
                        v-if="currentViewDoc.inbound_document.original_file_path"
                        :href="getOriginalDownloadUrl(currentViewDoc.inbound_document.id)"
                        target="_blank"
                        class="btn btn-sm btn-outline-secondary"
                      >
                        <i class="fas fa-download mr-1"></i> ទាញយកច្បាប់ដើម
                      </a>
                      <a
                        v-if="currentViewDoc.inbound_document.annotated_file_path"
                        :href="getViewAnnotatedUrl(currentViewDoc.inbound_document.id)"
                        target="_blank"
                        class="btn btn-sm btn-outline-danger"
                      >
                        <i class="fas fa-file-signature mr-1"></i> មើលឯកសារមានចំណារ
                      </a>
                      <a
                        v-if="currentViewDoc.inbound_document.annotated_file_path"
                        :href="getAnnotatedDownloadUrl(currentViewDoc.inbound_document.id)"
                        target="_blank"
                        class="btn btn-sm btn-outline-secondary"
                      >
                        <i class="fas fa-download mr-1"></i> ទាញយកច្បាប់មានចំណារ
                      </a>
                    </div>
                  </div>

                  <!-- 2. Reference Note (កំណត់ត្រាឯកសារយោង) -->
                  <div v-if="currentViewDoc.reference_note" class="mb-3 p-3 rounded border bg-light">
                    <div class="font-weight-bold text-dark font-14 mb-1">
                      <i class="fas fa-quote-left text-info mr-1"></i> កំណត់ត្រាឯកសារយោង (Reference Note)
                    </div>
                    <div class="text-dark small font-weight-bold" style="white-space: pre-wrap;">
                      {{ formatReferenceText(currentViewDoc.reference_note) }}
                    </div>
                  </div>

                  <!-- 3. Scanned Attachment / Annex File (ឯកសារភ្ជាប់ Scan បន្ថែម ឬឧបសម្ព័ន្ធ) -->
                  <div v-if="currentViewDoc.file_path" class="p-3 rounded border bg-light">
                    <div class="font-weight-bold text-dark font-14 mb-2">
                      <i class="fas fa-file-pdf text-danger mr-1"></i> ឯកសារភ្ជាប់ Scan បន្ថែម ឬឧបសម្ព័ន្ធ (Attached Annex)
                    </div>
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                      <div class="text-dark small">
                        <i class="fas fa-paperclip mr-1 text-muted"></i>
                        <strong>{{ currentViewDoc.file_name || 'ឯកសារឧបសម្ព័ន្ធ' }}</strong>
                      </div>
                      <div class="d-flex gap-2">
                        <a
                          :href="getOutboundFileViewUrl(currentViewDoc.id)"
                          target="_blank"
                          class="btn btn-sm btn-outline-primary"
                        >
                          <i class="fas fa-eye mr-1"></i> មើលឯកសារ (Preview)
                        </a>
                        <a
                          :href="getOutboundFileDownloadUrl(currentViewDoc.id)"
                          target="_blank"
                          class="btn btn-sm btn-primary"
                        >
                          <i class="fas fa-download mr-1"></i> ទាញយក (Download)
                        </a>
                      </div>
                    </div>
                  </div>

                  <!-- 4. Empty State Fallback -->
                  <div
                    v-if="!currentViewDoc.inbound_document && !currentViewDoc.reference_note && !currentViewDoc.file_path"
                    class="text-center py-4 text-muted small"
                  >
                    <i class="fas fa-folder-open fa-2x mb-2 text-black-50 d-block"></i>
                    របាយការណ៍/លិខិតនេះពុំមានឯកសារយោង ឬឧបសម្ព័ន្ធភ្ជាប់ជាមួយឡើយ
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

            <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between align-items-center flex-shrink-0">
              <div>
                <button
                  v-if="currentViewDoc && canDelete(currentViewDoc)"
                  type="button"
                  class="btn btn-outline-danger btn-sm font-weight-bold"
                  @click="deleteAction(currentViewDoc)"
                  :title="currentViewDoc.status === 'RETURNED' ? 'លុបឯកសារដែលថ្នាក់ដឹកនាំបញ្ជូនត្រឡប់' : 'លុបសេចក្តីព្រាង'"
                >
                  <i class="fas fa-trash mr-1"></i> {{ currentViewDoc.status === 'RETURNED' ? 'លុបឯកសារនេះ' : 'លុបសេចក្តីព្រាង' }}
                </button>
                <button
                  v-if="currentViewDoc && canEdit(currentViewDoc)"
                  type="button"
                  class="btn btn-outline-primary btn-sm font-weight-bold ml-2"
                  @click="editFromViewModal(currentViewDoc)"
                >
                  <i class="fas fa-edit mr-1"></i> កែសម្រួល
                </button>
              </div>
              <button type="button" class="btn btn-secondary px-4" @click="showViewModal = false">
                បិទ
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Reviewer Signature Modal -->
    <SignatureModal v-model:show="showReviewSignatureModal" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import { useUserStore } from '@/stores/user';
import SignatureModal from '@/components/common/SignatureModal.vue';
import WordEditor from '@/components/common/WordEditor.vue';
import { renderDiffHtml } from '@/utils/diffHelper';
import {
  apiGetOutboundOptions,
  apiGetOutboundDocuments,
  apiGetOutboundDocument,
  apiCreateOutboundDocument,
  apiUpdateOutboundDocument,
  apiUpdateOutboundContent,
  apiReviewOutboundAction,
  apiRecallOutboundDocument,
  apiDeleteOutboundDocument,
  getOutboundFileDownloadUrl,
  getOutboundFileViewUrl,
} from '@/functions/api/outboundDocument';
import {
  getViewOriginalUrl,
  getViewAnnotatedUrl,
  getOriginalDownloadUrl,
  getAnnotatedDownloadUrl,
} from '@/functions/api/inboundDocument';
import {
  getShortKhmerDateString,
  generateDraggableOpinionBoxHtml,
  embedOpinionBoxIntoHtml,
  setupDraggableOpinionBoxes,
} from '@/utils/opinionBoxHelper';

const userStore = useUserStore();
const route = useRoute();

const activeTab = ref('all'); // all, doing, in_progress, complete
const loading = ref(false);
const documents = ref([]);
const stats = reactive({ all: 0, doing: 0, in_progress: 0, complete: 0 });

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
const currentEditingDoc = ref(null);
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
const showReviewSignatureModal = ref(false);
const currentReviewDoc = ref(null);
const submittingReview = ref(false);
const reviewStep = ref('form'); // 'form' | 'position'
const reviewWorkingHtml = ref('');
const reviewPositionPaperRef = ref(null);
let cleanupReviewDraggable = null;
const reviewForm = reactive({
  action: 'FORWARD', // FORWARD, RETURN, DG_APPROVE
  forwarded_to_id: '',
  response_number: '',
  comment: '',
  embed_into_document: true,
  placement: 'SIDE_BY_SIDE_LEFT',
  sign_date: '',
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

// Exclude current user from recipient candidates (ដកឈ្មោះខ្លួនឯងចេញ)
const availableRecipients = computed(() => {
  return (options.recipients || []).filter(r => Number(r.id) !== Number(userStore.id));
});

// Single avatar of the person currently holding the document (អ្នកដែលកំពុងកាន់ឯកសារក្នុងដៃ)
const getOutboundCurrentHolder = (doc) => {
  if (!doc) return null;

  if (doc.status === 'UNDER_REVIEW' && doc.current_approver) {
    const u = doc.current_approver;
    const name = u.name_kh || u.name || 'ថ្នាក់ដឹកនាំ';
    return {
      name,
      title: `កំពុងស្ថិតលើដៃ៖ ${name} (${u.position?.title_kh || 'ថ្នាក់ដឹកនាំកំពុងពិនិត្យ'})`,
      avatarUrl: u.profile_image ? getFullImageUrl(u.profile_image) : '',
      initial: name.charAt(0) || 'ថ',
      borderClass: 'border-warning',
      fallbackClass: 'workflow-avatar-reviewer',
    };
  }

  if (doc.status === 'APPROVED_BY_DG' && (doc.approved_by_user || doc.current_approver)) {
    const u = doc.approved_by_user || doc.current_approver;
    const name = u.name_kh || u.name || 'ឯ.ឧ អគ្គនាយក';
    return {
      name,
      title: `បានឯកភាពដោយ៖ ${name} (${u.position?.title_kh || 'ឯ.ឧ អគ្គនាយក'})`,
      avatarUrl: u.profile_image ? getFullImageUrl(u.profile_image) : '',
      initial: name.charAt(0) || 'អ',
      borderClass: 'border-success',
      fallbackClass: 'workflow-avatar-dg',
    };
  }

  // DRAFT or RETURNED
  const u = doc.drafted_by_user;
  if (u) {
    const name = u.name_kh || u.name || 'មន្ត្រីរៀបចំ';
    const isReturned = doc.status === 'RETURNED';
    return {
      name,
      title: isReturned
        ? `បានបញ្ជូនត្រឡប់មក៖ ${name} (${u.position?.title_kh || 'មន្ត្រីរៀបចំ'})`
        : `កំពុងស្ថិតលើដៃ៖ ${name} (${u.position?.title_kh || 'មន្ត្រីរៀបចំ'})`,
      avatarUrl: u.profile_image ? getFullImageUrl(u.profile_image) : '',
      initial: name.charAt(0) || 'ម',
      borderClass: isReturned ? 'border-danger' : 'border-primary',
      fallbackClass: 'workflow-avatar-drafter',
    };
  }

  return null;
};

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
    const formattedDate = doc.received_date ? formatDateShort(doc.received_date) : '';
    form.reference_note = `យោង៖ ឯកសារចូលលេខ ${doc.general_inbound_number}${formattedDate ? ' ចុះថ្ងៃទី ' + formattedDate : ''}`;
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
      stats.all = res.data.stats.all || 0;
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
  currentEditingDoc.value = doc;
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

const khmerDigits = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];
const toKhmerNum = (num) => String(num).split('').map(d => khmerDigits[d] || d).join('');
const getCurrentKhmerDateString = () => {
  const d = new Date();
  const day = toKhmerNum(d.getDate());
  const monthNamesKh = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'];
  const month = monthNamesKh[d.getMonth()];
  const year = toKhmerNum(d.getFullYear());
  return `រាជធានីភ្នំពេញ ថ្ងៃទី${day} ខែ${month} ឆ្នាំ${year}`;
};

const openReviewActionModal = (doc) => {
  currentReviewDoc.value = doc;
  reviewStep.value = 'form';
  reviewWorkingHtml.value = '';
  if (cleanupReviewDraggable) {
    cleanupReviewDraggable();
    cleanupReviewDraggable = null;
  }
  reviewForm.action = isDg.value ? 'DG_APPROVE' : 'FORWARD';
  reviewForm.forwarded_to_id = '';
  reviewForm.response_number = doc.response_number || '';
  reviewForm.comment = isDg.value
    ? 'បានពិនិត្យ និងសម្រេចឯកភាព'
    : 'សូមគោរពជូនឯកឧត្តមអគ្គនាយក\nមេត្តាពិនិត្យ និងសម្រេច';
  reviewForm.embed_into_document = (doc.response_type !== 'LETTER');
  reviewForm.placement = 'SIDE_BY_SIDE_LEFT';
  reviewForm.sign_date = getShortKhmerDateString(new Date());
  showReviewModal.value = true;
};

const setupReviewPositionDraggable = () => {
  if (cleanupReviewDraggable) {
    cleanupReviewDraggable();
    cleanupReviewDraggable = null;
  }
  if (!reviewPositionPaperRef.value) return;

  cleanupReviewDraggable = setupDraggableOpinionBoxes(
    reviewPositionPaperRef.value,
    () => {},
    { currentUserId: userStore.id, isAdmin: isDg.value, canEdit: true }
  );

  // Auto-select the owner's opinion box so handles are visible immediately
  nextTick(() => {
    if (!reviewPositionPaperRef.value) return;
    const boxes = reviewPositionPaperRef.value.querySelectorAll('.draggable-opinion-box');
    boxes.forEach(b => {
      const bOwnerId = b.getAttribute('data-owner-id');
      if (bOwnerId && Number(bOwnerId) === Number(userStore.id)) {
        b.classList.add('is-selected');
      }
    });
  });
};

const goToReviewPositionStep = () => {
  if (reviewForm.action === 'FORWARD' && !reviewForm.forwarded_to_id) {
    Swal.fire({ icon: 'warning', title: 'សូមជ្រើសរើស', text: 'សូមជ្រើសរើសថ្នាក់ដឹកនាំដើម្បីចារឆ្លងបន្ត!' });
    return;
  }

  const isDgLeader = isDg.value || reviewForm.action === 'DG_APPROVE';
  const reviewerName = userStore.name_kh || userStore.name || '';
  const reviewerSig = userStore.signature_image || '';
  const shortDateKh = reviewForm.sign_date || getShortKhmerDateString(new Date());
  const comment = reviewForm.comment || (isDgLeader ? 'បានពិនិត្យ និងសម្រេចឯកភាព' : 'សូមគោរពជូនឯកឧត្តមអគ្គនាយក\nមេត្តាពិនិត្យ និងសម្រេច');

  const leaderBoxHtml = generateDraggableOpinionBoxHtml({
    comment,
    reviewerName,
    signatureUrl: reviewerSig,
    shortDateKh,
    ownerId: userStore.id,
  });

  const baseContent = currentReviewDoc.value?.content || '';
  reviewWorkingHtml.value = embedOpinionBoxIntoHtml(baseContent, leaderBoxHtml, reviewForm.placement);
  reviewStep.value = 'position';

  nextTick(() => {
    setupReviewPositionDraggable();
  });
};

const resetReviewPosition = () => {
  const isDgLeader = isDg.value || reviewForm.action === 'DG_APPROVE';
  const reviewerName = userStore.name_kh || userStore.name || '';
  const reviewerSig = userStore.signature_image || '';
  const shortDateKh = reviewForm.sign_date || getShortKhmerDateString(new Date());
  const comment = reviewForm.comment || (isDgLeader ? 'បានពិនិត្យ និងសម្រេចឯកភាព' : 'សូមគោរពជូនឯកឧត្តមអគ្គនាយក\nមេត្តាពិនិត្យ និងសម្រេច');

  const leaderBoxHtml = generateDraggableOpinionBoxHtml({
    comment,
    reviewerName,
    signatureUrl: reviewerSig,
    shortDateKh,
    ownerId: userStore.id,
  });

  const baseContent = currentReviewDoc.value?.content || '';
  reviewWorkingHtml.value = embedOpinionBoxIntoHtml(baseContent, leaderBoxHtml, reviewForm.placement);

  nextTick(() => {
    setupReviewPositionDraggable();
  });
};

const submitReviewAction = async () => {
  if (document.activeElement && typeof document.activeElement.blur === 'function') {
    document.activeElement.blur();
  }
  if (reviewForm.action === 'FORWARD' && !reviewForm.forwarded_to_id) {
    Swal.fire({ icon: 'warning', title: 'សូមជ្រើសរើស', text: 'សូមជ្រើសរើសថ្នាក់ដឹកនាំដើម្បីចារឆ្លងបន្ត!' });
    return;
  }

  // If currently in step 1 and embed is selected, transition to position step first
  if (reviewStep.value === 'form' && reviewForm.embed_into_document && currentReviewDoc.value?.content && reviewForm.action !== 'RETURN') {
    goToReviewPositionStep();
    return;
  }

  submittingReview.value = true;
  try {
    let finalContent = undefined;
    if (reviewForm.embed_into_document && currentReviewDoc.value && currentReviewDoc.value.content && reviewForm.action !== 'RETURN') {
      if (reviewPositionPaperRef.value) {
        const clone = reviewPositionPaperRef.value.cloneNode(true);
        clone.querySelectorAll('.draggable-opinion-box').forEach(b => {
          b.classList.remove('is-selected', 'is-dragging', 'is-resizing', 'is-locked-box', 'is-editable-box');
          b.removeAttribute('title');
          b.querySelectorAll('div').forEach(d => {
            if (d.textContent.trim() === '(ហត្ថលេខា)') {
              d.remove();
            }
          });
          b.querySelectorAll('[contenteditable]').forEach(el => {
            el.removeAttribute('contenteditable');
            el.removeAttribute('title');
          });
        });
        finalContent = clone.innerHTML;
      } else {
        const isDgLeader = isDg.value || reviewForm.action === 'DG_APPROVE';
        const reviewerName = userStore.name_kh || userStore.name || '';
        const reviewerSig = userStore.signature_image || '';
        const shortDateKh = reviewForm.sign_date || getShortKhmerDateString(new Date());
        const comment = reviewForm.comment || (isDgLeader ? 'បានពិនិត្យ និងសម្រេចឯកភាព' : 'សូមគោរពជូនឯកឧត្តមអគ្គនាយក\nមេត្តាពិនិត្យ និងសម្រេច');

        const leaderBoxHtml = generateDraggableOpinionBoxHtml({
          comment,
          reviewerName,
          signatureUrl: reviewerSig,
          shortDateKh,
          ownerId: userStore.id,
        });

        finalContent = embedOpinionBoxIntoHtml(currentReviewDoc.value.content, leaderBoxHtml, reviewForm.placement);
      }
    }

    await apiReviewOutboundAction(currentReviewDoc.value.id, {
      action: reviewForm.action,
      forwarded_to_id: reviewForm.forwarded_to_id || undefined,
      response_number: reviewForm.response_number || undefined,
      comment: reviewForm.comment || undefined,
      content: finalContent || undefined,
    });

    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: 'បានអនុវត្តសកម្មភាពពិនិត្យឆ្លងដោយជោគជ័យ!',
      timer: 1800,
      showConfirmButton: false,
    });

    if (cleanupReviewDraggable) {
      cleanupReviewDraggable();
      cleanupReviewDraggable = null;
    }
    showReviewModal.value = false;
    reviewStep.value = 'form';
    loadDocuments();
  } catch (err) {
    const errorMsg = err.response?.data?.message || 'មិនអាចអនុវត្តសកម្មភាពបានឡើយ!';
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: errorMsg });
  } finally {
    submittingReview.value = false;
  }
};

const previewPaperRef = ref(null);
const hasMovedOpinionBox = ref(false);
const savingMovedPosition = ref(false);
const originalContentBackup = ref('');
let cleanupPreviewDraggable = null;

const previewPaperStyle = computed(() => {
  const content = currentViewDoc.value?.content || '';
  const match = content.match(/class=["'][^"']*page-break/g);
  const count = match ? match.length : 0;
  const pages = Math.max(1, count + 1);
  const minH = pages * 1123 + (pages - 1) * 60;
  return {
    width: '100%',
    maxWidth: '794px',
    minHeight: `${minH}px`,
    height: 'auto',
    padding: '45px 55px',
    color: '#111',
    lineHeight: '2.1',
    boxSizing: 'border-box',
    textRendering: 'optimizeLegibility',
    WebkitFontSmoothing: 'antialiased',
    backgroundColor: '#ffffff'
  };
});

const canEditPositionInDetail = computed(() => {
  if (!currentViewDoc.value) return false;
  if (isDg.value) return true;
  const status = currentViewDoc.value.status;
  if (status === 'DRAFT' || status === 'RETURNED') {
    return Number(currentViewDoc.value.drafted_by) === Number(userStore.id);
  }
  if (status === 'UNDER_REVIEW') {
    return Number(currentViewDoc.value.current_approver_id) === Number(userStore.id);
  }
  return false;
});

const saveAndHoldReview = async () => {
  if (document.activeElement && typeof document.activeElement.blur === 'function') {
    document.activeElement.blur();
  }
  submittingReview.value = true;
  try {
    let finalContent = undefined;
    if (reviewPositionPaperRef.value) {
      const clone = reviewPositionPaperRef.value.cloneNode(true);
      clone.querySelectorAll('.draggable-opinion-box').forEach(b => {
        b.classList.remove('is-selected', 'is-dragging', 'is-resizing', 'is-locked-box', 'is-editable-box');
        b.removeAttribute('title');
        b.querySelectorAll('*').forEach(d => {
          if (d.textContent.trim() === '(ហត្ថលេខា)') {
            d.remove();
          }
        });
        b.querySelectorAll('[contenteditable]').forEach(el => {
          el.removeAttribute('contenteditable');
          el.removeAttribute('title');
        });
      });
      finalContent = clone.innerHTML;
    } else if (reviewForm.embed_into_document && currentReviewDoc.value?.content) {
      const isDgLeader = isDg.value || reviewForm.action === 'DG_APPROVE';
      const reviewerName = userStore.name_kh || userStore.name || '';
      const reviewerSig = userStore.signature_image || '';
      const shortDateKh = reviewForm.sign_date || getShortKhmerDateString(new Date());
      const comment = reviewForm.comment || (isDgLeader ? 'បានពិនិត្យ និងសម្រេចឯកភាព' : 'សូមគោរពជូនឯកឧត្តមអគ្គនាយក\nមេត្តាពិនិត្យ និងសម្រេច');

      const leaderBoxHtml = generateDraggableOpinionBoxHtml({
        comment,
        reviewerName,
        signatureUrl: reviewerSig,
        shortDateKh,
        ownerId: userStore.id,
      });

      finalContent = embedOpinionBoxIntoHtml(currentReviewDoc.value.content, leaderBoxHtml, reviewForm.placement);
    }

    await apiReviewOutboundAction(currentReviewDoc.value.id, {
      action: 'HOLD',
      comment: reviewForm.comment || undefined,
      content: finalContent || undefined,
    });

    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: 'បានរក្សាទុកមតិយោបល់ និងខ្លឹមសារក្នុងដៃដោយជោគជ័យ! លោកអ្នកអាចចារឆ្លងទៅថ្នាក់លើនៅពេលក្រោយ។',
      timer: 2000,
      showConfirmButton: false,
    });

    if (cleanupReviewDraggable) {
      cleanupReviewDraggable();
      cleanupReviewDraggable = null;
    }
    showReviewModal.value = false;
    reviewStep.value = 'form';
    loadDocuments();
  } catch (err) {
    const errorMsg = err.response?.data?.message || 'មិនអាចរក្សាទុកក្នុងដៃបានឡើយ!';
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: errorMsg });
  } finally {
    submittingReview.value = false;
  }
};

const initPreviewDraggable = () => {
  if (cleanupPreviewDraggable) {
    cleanupPreviewDraggable();
    cleanupPreviewDraggable = null;
  }
  if (previewPaperRef.value) {
    const canMove = canEditPositionInDetail.value;
    cleanupPreviewDraggable = setupDraggableOpinionBoxes(
      previewPaperRef.value,
      () => {
        if (canMove) {
          hasMovedOpinionBox.value = true;
        }
      },
      {
        currentUserId: userStore.id,
        isAdmin: isDg.value,
        canMove,
        canEdit: canMove,
      }
    );
  }
};

const resetOpinionBoxPosition = () => {
  if (currentViewDoc.value && originalContentBackup.value) {
    currentViewDoc.value.content = originalContentBackup.value;
    hasMovedOpinionBox.value = false;
    nextTick(() => {
      initPreviewDraggable();
    });
  }
};

const saveNewOpinionBoxPosition = async () => {
  if (!currentViewDoc.value || !previewPaperRef.value) return;
  savingMovedPosition.value = true;
  try {
    const clone = previewPaperRef.value.cloneNode(true);
    clone.querySelectorAll('.draggable-opinion-box').forEach(b => {
      b.classList.remove('is-selected', 'is-dragging', 'is-resizing', 'is-locked-box', 'is-editable-box');
      b.removeAttribute('title');
      b.querySelectorAll('div').forEach(d => {
        if (d.textContent.trim() === '(ហត្ថលេខា)') {
          d.remove();
        }
      });
      b.querySelectorAll('[contenteditable]').forEach(el => {
        el.removeAttribute('contenteditable');
        el.removeAttribute('title');
      });
    });
    const updatedHtml = clone.innerHTML;
    await apiUpdateOutboundContent(currentViewDoc.value.id, updatedHtml);
    currentViewDoc.value.content = updatedHtml;
    originalContentBackup.value = updatedHtml;
    hasMovedOpinionBox.value = false;
    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: 'បានរក្សាទុកទីតាំងថ្មីដោយជោគជ័យ!',
      timer: 1600,
      showConfirmButton: false,
    });
    loadDocuments();
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ',
      text: err.response?.data?.message || 'មិនអាចរក្សាទុកទីតាំងបានឡើយ!',
    });
  } finally {
    savingMovedPosition.value = false;
  }
};

const openViewModal = async (doc) => {
  currentViewDoc.value = doc;
  originalContentBackup.value = doc.content || '';
  hasMovedOpinionBox.value = false;
  viewTab.value = 'doc';
  showViewModal.value = true;

  try {
    const res = await apiGetOutboundDocument(doc.id);
    if (res.data?.data) {
      currentViewDoc.value = res.data.data;
      originalContentBackup.value = res.data.data.content || '';
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
  } finally {
    nextTick(() => {
      initPreviewDraggable();
    });
  }
};

const printCurrentViewDoc = () => {
  if (!currentViewDoc.value) return;
  const printWindow = window.open('', '_blank');
  let rawHtml = currentViewDoc.value.content || '';

  // Clean up using DOM parser so NOTHING prints as visual lines or badges on paper
  const tempDoc = new DOMParser().parseFromString(rawHtml, 'text/html');
  tempDoc.querySelectorAll('.page-break').forEach(el => {
    el.className = 'page-break';
    el.removeAttribute('style');
    el.innerHTML = '';
  });
  tempDoc.querySelectorAll('.page-break-badge').forEach(el => el.remove());
  tempDoc.querySelectorAll('.page-number-header').forEach(el => el.remove());
  const content = tempDoc.body.innerHTML;

  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>${currentViewDoc.value.title || 'ឯកសាររដ្ឋបាល'}</title>
        <style>
          @import url('https://fonts.googleapis.com/css2?family=Siemreap&family=Battambang:wght@400;700&display=swap');
          @page {
            size: A4 portrait;
            margin: 20mm 20mm 20mm 25mm;
          }
          body {
            font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif;
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
            border: none !important;
            border-top: none !important;
            border-bottom: none !important;
            outline: none !important;
            box-shadow: none !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            display: block !important;
          }
          .page-break-badge, .page-number-header { display: none !important; }
          .page-number-footer {
            text-align: center !important;
            font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif !important;
            font-size: 10.5pt !important;
            color: #333 !important;
            margin-top: 35px !important;
            margin-bottom: 15px !important;
            page-break-after: avoid !important;
          }
          .closing-and-signature-block,
          .drafter-signature-table,
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
  if (!doc) return;
  const isReturned = (doc.status === 'RETURNED');
  const docTypeLabel = isReturned ? 'ឯកសារដែលថ្នាក់ដឹកនាំបានបញ្ជូនត្រឡប់មកវិញនេះ' : 'សេចក្តីព្រាងនេះ';
  const result = await Swal.fire({
    title: 'តើលោកអ្នកពិតជាចង់លុបមែនទេ?',
    text: `${docTypeLabel} នឹងត្រូវលុបចេញពីប្រព័ន្ធទាំងស្រុង!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    confirmButtonText: 'យល់ព្រមលុប',
    cancelButtonText: 'បោះបង់',
  });
  if (!result.isConfirmed) return;

  try {
    await apiDeleteOutboundDocument(doc.id);
    Swal.fire({ icon: 'success', title: 'បានលុបជោគជ័យ!', timer: 1500, showConfirmButton: false });
    if (showViewModal.value && currentViewDoc.value?.id === doc.id) {
      showViewModal.value = false;
    }
    if (showCreateModal.value && editingDocId.value === doc.id) {
      showCreateModal.value = false;
    }
    loadDocuments();
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: err.response?.data?.message || 'មិនអាចលុបបានឡើយ!' });
  }
};

const editFromViewModal = (doc) => {
  showViewModal.value = false;
  openEditModal(doc);
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
  if (!doc) return false;
  const isDrafter = (doc.drafted_by === userStore.id);
  const isAdmin = (userStore.level && userStore.level.toUpperCase() === 'ADMIN');
  if (!isDrafter && !isAdmin) return false;
  return ['DRAFT', 'RETURNED'].includes(doc.status) || isAdmin;
};

const getFullImageUrl = (path) => {
  if (!path) return '';
  if (
    path.startsWith('http://') ||
    path.startsWith('https://') ||
    path.startsWith('blob:') ||
    path.startsWith('data:')
  ) {
    return path;
  }
  const backendBase = (import.meta.env.VITE_APP_API_URL || import.meta.env.VITE_API_URL || 'http://localhost:8000')
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

const formatDate = (val) => {
  if (!val) return '--';
  try {
    const d = new Date(val);
    if (isNaN(d.getTime())) return val;
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    const time = d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    return `${day}/${month}/${year} ${time}`;
  } catch {
    return val;
  }
};

const formatDateShort = (val) => {
  if (!val) return '';
  try {
    const d = new Date(val);
    if (isNaN(d.getTime())) return val;
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
  } catch {
    return val;
  }
};

const formatReferenceText = (text) => {
  if (!text) return '';
  return text.replace(/(\d{4})-(\d{2})-(\d{2})T\d{2}:\d{2}:\d{2}(?:\.\d+)?Z?/g, (match, y, m, d) => {
    return `${d}/${m}/${y}`;
  });
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

const formatDateTime = (val) => formatDate(val);

const getApprovalActionBadgeClass = (action) => {
  switch (action) {
    case 'SUBMIT':
      return 'badge-primary';
    case 'EDIT':
      return 'badge-warning text-dark';
    case 'FORWARD':
      return 'badge-info';
    case 'RETURN':
      return 'badge-danger';
    case 'DG_APPROVE':
      return 'badge-success';
    case 'RECALL':
      return 'badge-secondary';
    default:
      return 'badge-light border';
  }
};

onMounted(() => {
  loadOptions();
  loadDocuments();
});

onUnmounted(() => {
  if (cleanupPreviewDraggable) {
    cleanupPreviewDraggable();
    cleanupPreviewDraggable = null;
  }
});
</script>

<style scoped>
/* Draggable Leadership Opinion Box & Handles */
:deep(.draggable-opinion-box) {
  position: relative;
  border: 1px dashed transparent !important;
  background: transparent !important;
  vertical-align: top !important;
  transition: box-shadow 0.15s ease;
}

:deep(.draggable-opinion-box:hover),
:deep(.draggable-opinion-box.is-selected) {
  outline: 1.5px dashed #2563eb !important;
  outline-offset: 2px;
}

:deep(.draggable-opinion-box:hover .box-handle),
:deep(.draggable-opinion-box.is-selected .box-handle) {
  display: block !important;
}

:deep(.draggable-opinion-box.is-dragging) {
  opacity: 0.92;
  box-shadow: 0 8px 24px rgba(37, 99, 235, 0.25) !important;
  cursor: grabbing !important;
  z-index: 100 !important;
}

:deep(.draggable-opinion-box.is-resizing) {
  opacity: 0.95;
  outline: 1.5px dashed #2563eb !important;
  user-select: none !important;
  z-index: 100 !important;
}

:deep(.draggable-opinion-box.is-locked-box) {
  cursor: default !important;
  outline: none !important;
  border: 1px dashed transparent !important;
}
:deep(.draggable-opinion-box.is-locked-box:hover) {
  outline: none !important;
}
:deep(.draggable-opinion-box.is-locked-box .box-handle) {
  display: none !important;
}

/* Ensure report signature table always aligns cells to the top row */
:deep(.drafter-signature-table) {
  width: 100% !important;
  border-collapse: collapse !important;
}
:deep(.drafter-signature-table tr) {
  vertical-align: top !important;
}
:deep(.drafter-signature-table td) {
  vertical-align: top !important;
}
:deep(.drafter-signature-table .opinion-left-col) {
  width: 50% !important;
  vertical-align: top !important;
  text-align: center !important;
}

:deep(.box-handle) {
  display: none;
  position: absolute;
  width: 8px;
  height: 8px;
  background-color: #2563eb;
  border: 1px solid #ffffff;
  box-sizing: border-box;
  z-index: 30;
  border-radius: 1px;
}
:deep(.box-handle.handle-tl) { top: -4.5px; left: -4.5px; cursor: nwse-resize; }
:deep(.box-handle.handle-tc) { top: -4.5px; left: calc(50% - 4px); cursor: ns-resize; }
:deep(.box-handle.handle-tr) { top: -4.5px; right: -4.5px; cursor: nesw-resize; }
:deep(.box-handle.handle-ml) { top: calc(50% - 4px); left: -4.5px; cursor: ew-resize; }
:deep(.box-handle.handle-mr) { top: calc(50% - 4px); right: -4.5px; cursor: ew-resize; }
:deep(.box-handle.handle-bl) { bottom: -4.5px; left: -4.5px; cursor: nesw-resize; }
:deep(.box-handle.handle-bc) { bottom: -4.5px; left: calc(50% - 4px); cursor: ns-resize; }
:deep(.box-handle.handle-br) { bottom: -4.5px; right: -4.5px; cursor: nwse-resize; }

:deep(.opinion-box-comment[contenteditable="true"]:hover),
:deep(.opinion-box-date[contenteditable="true"]:hover) {
  background-color: #f1f5f9;
  border-radius: 2px;
  cursor: text;
}

:deep(.opinion-box-comment[contenteditable="true"]:focus),
:deep(.opinion-box-date[contenteditable="true"]:focus) {
  outline: 1.5px dashed #2563eb !important;
  background-color: #ffffff !important;
  border-radius: 2px;
  padding: 1px 3px;
  cursor: text;
}

@media print {
  :deep(.box-handle),
  :deep(.draggable-opinion-box .box-handle) {
    display: none !important;
  }
  :deep(.draggable-opinion-box) {
    outline: none !important;
    box-shadow: none !important;
  }
}

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
  font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif;
  font-size: 12pt;
  line-height: 2.1;
  color: #111;
}

:deep(.document-paper-preview .page-break) {
  page-break-before: always !important;
  break-before: page !important;
  display: block !important;
  margin: 45px -55px 30px -55px !important;
  height: 26px !important;
  padding: 0 !important;
  background-color: #525659 !important;
  border-top: 1px solid #3a3d40 !important;
  border-bottom: 1px solid #3a3d40 !important;
  box-shadow: inset 0 4px 8px rgba(0, 0, 0, 0.35) !important;
  position: relative !important;
  user-select: none !important;
  clear: both !important;
}

:deep(.document-paper-preview .page-break-badge) {
  user-select: none !important;
  position: absolute !important;
  top: 50% !important;
  left: 50% !important;
  transform: translate(-50%, -50%) !important;
  background: #1e40af !important;
  color: #ffffff !important;
  padding: 2px 18px !important;
  border-radius: 12px !important;
  font-size: 9pt !important;
  font-family: sans-serif !important;
  font-weight: bold !important;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3) !important;
  z-index: 10 !important;
}

:deep(.document-paper-preview .page-number-header) {
  display: none !important;
}

:deep(.document-paper-preview .page-number-footer) {
  text-align: center !important;
  font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif !important;
  font-size: 11pt !important;
  color: #444 !important;
  margin-top: 35px !important;
  margin-bottom: 15px !important;
  user-select: none !important;
  clear: both !important;
}

/* Workflow Avatars in Status Column */
.workflow-avatar-wrapper {
  position: relative;
  display: inline-flex;
}
.workflow-avatar {
  width: 34px;
  height: 34px;
  min-width: 34px;
  min-height: 34px;
  object-fit: cover;
  border-radius: 50%;
  border: 2px solid #ffffff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.18);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  cursor: pointer;
}
.workflow-avatar:hover {
  transform: scale(1.15);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
  z-index: 5;
}
.workflow-avatar-fallback {
  width: 34px;
  height: 34px;
  min-width: 34px;
  min-height: 34px;
  border-radius: 50%;
  border: 2px solid #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 13px;
  color: #ffffff;
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.18);
  transition: transform 0.2s ease;
}
.workflow-avatar-fallback:hover {
  transform: scale(1.15);
  z-index: 5;
}
.workflow-avatar-drafter {
  background: linear-gradient(135deg, #1e40af, #3b82f6);
}
.workflow-avatar-reviewer {
  background: linear-gradient(135deg, #b45309, #f59e0b);
}
.workflow-avatar-dg {
  background: linear-gradient(135deg, #15803d, #22c55e);
}
</style>
