<template>
  <Teleport to="body">
    <div class="custom-modal-backdrop font-khmer" v-if="show" @click.self="close">
      <div class="modal-dialog modal-xl modal-dialog-centered my-auto" style="width: 100%; max-width: 1100px;">
        <div class="modal-content shadow-2xl border-0 rounded-lg overflow-hidden d-flex flex-column" style="max-height: 90vh;">
          <!-- Header -->
          <div class="modal-header bg-dark-custom text-white py-3 px-4 flex-shrink-0 align-items-center">
            <div class="d-flex align-items-center">
              <i class="fas fa-project-diagram text-warning mr-2 fa-lg"></i>
              <div>
                <h5 class="modal-title font-weight-bold mb-0">តាមដានលំហូរឯកសារ (Document Tracking Flow)</h5>
                <small class="text-white-50">តាមដានដំណើរការឯកសារតាមឋានានុក្រម ចាប់ពីពេលចូល រហូតដល់ការសម្រេចចុងក្រោយ</small>
              </div>
            </div>
            <button type="button" class="close text-white" @click="close" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <!-- Body -->
          <div class="modal-body p-4 flex-grow-1" style="overflow-y: auto; background-color: #f8f9fa;">
            <div v-if="loading" class="text-center py-5">
              <i class="fas fa-circle-notch fa-spin fa-3x text-primary mb-3"></i>
              <div class="font-weight-bold text-muted">កំពុងទាញយកទិន្នន័យលំហូរឯកសារ...</div>
            </div>

            <div v-else-if="!doc" class="alert alert-warning text-center">
              មិនមានទិន្នន័យឯកសារឡើយ!
            </div>

            <div v-else>
              <!-- 1. Document Overview Banner -->
              <div class="card border-0 shadow-sm rounded-lg mb-4 bg-white">
                <div class="card-body p-3">
                  <div class="row align-items-center">
                    <div class="col-lg-8">
                      <div class="d-flex align-items-center flex-wrap mb-1">
                        <span class="badge badge-dark font-13 mr-2 mb-1">
                          <i class="fas fa-hashtag mr-1"></i>លេខឯកសារ៖ {{ doc.general_inbound_number || '---' }}
                        </span>
                        <span class="badge mr-2 mb-1" :class="getStatusBadgeClass(doc.status)">
                          {{ doc.status_kh || doc.status }}
                        </span>
                        <span class="badge badge-danger mr-2 mb-1" v-if="doc.is_response_required">
                          <i class="fas fa-reply mr-1"></i> តម្រូវឱ្យឆ្លើយតប
                        </span>
                      </div>
                      <h5 class="font-weight-bold text-dark mb-1">{{ doc.title }}</h5>
                      <div class="text-muted small">
                        <i class="fas fa-building mr-1"></i> ប្រភព៖ <strong>{{ doc.sender_organization }}</strong>
                        <span class="mx-2">•</span>
                        <i class="fas fa-calendar-alt mr-1"></i> ថ្ងៃចូល៖ {{ doc.received_date }}
                        <span v-if="doc.deadline" class="ml-2 text-danger">
                          <i class="fas fa-stopwatch mr-1"></i> កាលកំណត់៖ {{ doc.deadline }}
                        </span>
                      </div>
                    </div>

                    <!-- Current Custodians Highlight -->
                    <div class="col-lg-4 mt-3 mt-lg-0">
                      <div class="p-3 rounded-lg border bg-light">
                        <span class="small text-muted font-weight-bold d-block mb-1">
                          <i class="fas fa-user-clock text-warning mr-1"></i> អ្នកទទួលបន្ទុកបច្ចុប្បន្ន (Current Custodian)
                        </span>
                        <div v-if="custodians.length === 0" class="font-weight-bold text-dark">
                          ---
                        </div>
                        <div v-else v-for="(c, idx) in custodians" :key="idx" class="mb-1 last-mb-0">
                          <strong class="text-primary font-14">{{ c.name }}</strong>
                          <small class="text-muted d-block font-12">{{ c.detail }}</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- 2. Visual Stepper / Workflow Progress Ladder -->
              <div class="card border-0 shadow-sm rounded-lg mb-4 bg-white">
                <div class="card-header bg-white py-3 border-bottom font-weight-bold text-dark">
                  <i class="fas fa-tasks text-success mr-2"></i> ដំណាក់កាលលំហូរការងារ (Workflow Stages)
                </div>
                <div class="card-body p-4">
                  <div class="stepper-horizontal">
                    <!-- Step 1: Reception -->
                    <div class="step-item" :class="getStepClass(1)">
                      <div class="step-circle"><i class="fas fa-inbox"></i></div>
                      <div class="step-label">១. ទទួលឯកសារ</div>
                      <div class="step-sub">{{ doc.registeredByUser?.name_kh || 'បានចុះបញ្ជី' }}</div>
                    </div>

                    <!-- Step 2: DG Annotation -->
                    <div class="step-item" :class="getStepClass(2)">
                      <div class="step-circle"><i class="fas fa-pen-nib"></i></div>
                      <div class="step-label">២. ចំណារអគ្គនាយក</div>
                      <div class="step-sub">{{ doc.dg_annotated_at ? 'បានធ្វើចំណារ' : 'រង់ចាំ' }}</div>
                    </div>

                    <!-- Step 3: Multi-Target Dispatch -->
                    <div class="step-item" :class="getStepClass(3)">
                      <div class="step-circle"><i class="fas fa-share-alt"></i></div>
                      <div class="step-label">៣. ចាត់ចែង/ចែកចាយ</div>
                      <div class="step-sub">{{ doc.dispatched_at ? 'បានចែកចាយ' : 'រង់ចាំ' }}</div>
                    </div>

                    <!-- Step 4: Cascading Forward -->
                    <div class="step-item" :class="getStepClass(4)">
                      <div class="step-circle"><i class="fas fa-sitemap"></i></div>
                      <div class="step-label">៤. ចាត់បន្តថ្នាក់ក្រោម</div>
                      <div class="step-sub">{{ hasCascading ? 'បានចាត់បន្ត' : (doc.dispatched_at ? 'អនុវត្តផ្ទាល់' : '---') }}</div>
                    </div>

                    <!-- Step 5: Response Drafting -->
                    <div class="step-item" :class="getStepClass(5)" v-if="doc.is_response_required">
                      <div class="step-circle"><i class="fas fa-file-signature"></i></div>
                      <div class="step-label">៥. ព្រាងលិខិតឆ្លើយតប</div>
                      <div class="step-sub">{{ latestResponse ? (latestResponse.response_type_kh || 'បានព្រាង') : 'រង់ចាំ' }}</div>
                    </div>

                    <!-- Step 6: Hierarchy Approvals -->
                    <div class="step-item" :class="getStepClass(6)" v-if="doc.is_response_required">
                      <div class="step-circle"><i class="fas fa-stamp"></i></div>
                      <div class="step-label">៦. ឆ្លងឋានានុក្រម</div>
                      <div class="step-sub">{{ latestResponse?.status === 'APPROVED_BY_DG' ? 'ឯកភាព' : (latestResponse ? latestResponse.stage_kh : 'រង់ចាំ') }}</div>
                    </div>

                    <!-- Step 7: Completed -->
                    <div class="step-item" :class="getStepClass(7)">
                      <div class="step-circle"><i class="fas fa-check-double"></i></div>
                      <div class="step-label">៧. បញ្ចប់ការងារ</div>
                      <div class="step-sub">{{ doc.status === 'COMPLETED' ? 'រួចរាល់' : 'មិនទាន់' }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- 3. Details Tabs: Assignments, Response Details, Audit Trail -->
              <div class="card border-0 shadow-sm rounded-lg bg-white">
                <div class="card-header bg-white border-bottom p-0">
                  <ul class="nav nav-tabs border-0 px-3 pt-2 font-weight-bold font-14">
                    <li class="nav-item">
                      <a class="nav-link py-2" :class="{ active: activeTab === 'assignments' }" href="#" @click.prevent="activeTab = 'assignments'">
                        <i class="fas fa-users-cog mr-1 text-primary"></i> បញ្ជីចាត់ចែង និងចាត់បន្ត ({{ doc.assignments?.length || 0 }})
                      </a>
                    </li>
                    <li class="nav-item" v-if="doc.responses?.length > 0">
                      <a class="nav-link py-2" :class="{ active: activeTab === 'response' }" href="#" @click.prevent="activeTab = 'response'">
                        <i class="fas fa-file-contract mr-1 text-info"></i> លិខិតឆ្លើយតប & ការឆ្លង ({{ doc.responses.length }})
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link py-2" :class="{ active: activeTab === 'movements' }" href="#" @click.prevent="activeTab = 'movements'">
                        <i class="fas fa-history mr-1 text-secondary"></i> ប្រវត្តិលំហូរទាំងស្រុង ({{ doc.movements?.length || 0 }})
                      </a>
                    </li>
                  </ul>
                </div>

                <div class="card-body p-3">
                  <!-- TAB 1: Assignments & Cascading -->
                  <div v-if="activeTab === 'assignments'">
                    <div v-if="!doc.assignments || doc.assignments.length === 0" class="text-center py-4 text-muted">
                      មិនទាន់មានការចាត់ចែងជាក់លាក់នៅឡើយ
                    </div>
                    <div v-else class="table-responsive">
                      <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="bg-light">
                          <tr>
                            <th>ល.រ</th>
                            <th>ប្រភេទ</th>
                            <th>អ្នកទទួលបន្ទុក / អង្គភាព</th>
                            <th>ចាត់ចែងដោយ</th>
                            <th>ចំណារ / សេចក្តីណែនាំ</th>
                            <th>ស្ថានភាព</th>
                            <th>កាលបរិច្ឆេទ</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="(assign, index) in doc.assignments" :key="assign.id">
                            <td class="font-weight-bold">{{ index + 1 }}</td>
                            <td>
                              <span class="badge" :class="assign.target_type === 'USER' ? 'badge-primary' : (assign.target_type === 'OFFICE' ? 'badge-info' : (assign.target_type === 'WORKING_GROUP' ? 'badge-indigo text-white' : 'badge-dark'))">
                                {{ assign.target_type_kh }}
                              </span>
                              <span v-if="assign.parent_assignment_id" class="badge badge-warning ml-1" title="ចាត់បន្តពីថ្នាក់លើ">
                                ចាត់បន្ត
                              </span>
                            </td>
                            <td>
                              <strong class="text-dark">{{ assign.target_label_kh }}</strong>
                            </td>
                            <td>{{ assign.assigned_by_user?.name_kh || '---' }}</td>
                            <td>
                              <span class="text-muted" v-if="assign.instruction">{{ assign.instruction }}</span>
                              <span class="text-muted small" v-else>---</span>
                            </td>
                            <td>
                              <span class="badge" :class="assign.status === 'COMPLETED' ? 'badge-success' : (assign.status === 'IN_PROGRESS' ? 'badge-warning' : 'badge-secondary')">
                                {{ assign.status_kh }}
                              </span>
                            </td>
                            <td class="text-muted small">
                              {{ formatDate(assign.created_at) }}
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <!-- TAB 2: Response & Approvals Ladder -->
                  <div v-if="activeTab === 'response'">
                    <div v-for="resp in doc.responses" :key="resp.id" class="card border rounded-lg mb-3 shadow-xs">
                      <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                        <div>
                          <span class="badge badge-primary mr-2">{{ resp.response_type_kh || 'របាយការណ៍' }}</span>
                          <strong class="text-dark font-15">{{ resp.title }}</strong>
                          <span class="text-muted small ml-2">ដោយ៖ {{ resp.drafted_by_user?.name_kh }}</span>
                        </div>
                        <div>
                          <span class="badge mr-2" :class="resp.status === 'APPROVED_BY_DG' ? 'badge-success' : (resp.status === 'RETURNED' ? 'badge-danger' : 'badge-warning')">
                            {{ resp.status_kh }}
                          </span>
                          <button
                            v-if="resp.content"
                            class="btn btn-xs btn-outline-info mr-1"
                            @click="viewResponseContent(resp)"
                            title="មើលខ្លឹមសារពេញ"
                          >
                            <i class="fas fa-eye mr-1"></i> មើលខ្លឹមសារ
                          </button>
                        </div>
                      </div>

                      <div class="card-body p-3 font-13">
                        <div class="row mb-2">
                          <div class="col-md-6" v-if="resp.header_organization">
                            <span class="text-muted">អង្គភាព៖</span> <strong>{{ resp.header_organization }}</strong>
                          </div>
                          <div class="col-md-6" v-if="resp.reference_note">
                            <span class="text-muted">យោង៖</span> {{ resp.reference_note }}
                          </div>
                        </div>

                        <!-- Active Reviewer Highlight -->
                        <div v-if="resp.status === 'UNDER_REVIEW' && resp.current_approver" class="alert alert-warning py-2 px-3 mb-2 font-12 d-flex justify-content-between align-items-center">
                          <span>
                            <i class="fas fa-user-clock text-warning mr-1"></i>
                            កំពុងស្ថិតនៅដៃ៖ <strong>{{ resp.current_approver.name_kh || resp.current_approver.name }}</strong>
                            <small class="text-secondary ml-1" v-if="resp.current_approver.position?.title_kh">({{ resp.current_approver.position.title_kh }})</small>
                          </span>
                          <span class="badge badge-warning text-dark font-11">
                            <i class="fas fa-stopwatch mr-1"></i>
                            {{ getApprovalDuration(resp.approvals?.[resp.approvals?.length - 1], (resp.approvals?.length || 1) - 1, resp.approvals || [], resp) }}
                          </span>
                        </div>

                        <!-- Approval Steps Ladder -->
                        <div class="border-top pt-2 mt-2" v-if="resp.approvals && resp.approvals.length > 0">
                          <strong class="d-block mb-2 text-dark font-12"><i class="fas fa-layer-group text-primary mr-1"></i> ប្រវត្តិឆ្លង និងអនុម័តតាមឋានានុក្រម (Review & Approval Flow)៖</strong>
                          <div class="timeline-approval">
                            <div v-for="(app, aIdx) in resp.approvals" :key="app.id" class="approval-item p-2 mb-2 rounded bg-light border">
                              <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                  <span class="badge badge-info mr-2">{{ app.stage_kh }}</span>
                                  <span class="badge mr-2" :class="app.action === 'DG_APPROVE' ? 'badge-success' : (app.action === 'RETURN' ? 'badge-danger' : 'badge-primary')">{{ app.action_kh }}</span>
                                  <strong class="text-dark">{{ app.user?.name_kh || 'ថ្នាក់ដឹកនាំ' }}</strong>
                                  <small class="text-muted ml-1" v-if="app.user?.position?.title_kh">({{ app.user.position.title_kh }})</small>
                                  <span v-if="app.forwarded_to" class="text-primary font-weight-bold ml-1">
                                    &rarr; {{ app.forwarded_to.name_kh }}
                                    <small class="text-muted" v-if="app.forwarded_to.position?.title_kh">({{ app.forwarded_to.position.title_kh }})</small>
                                  </span>
                                </div>
                                <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ formatDate(app.created_at) }}</small>
                              </div>
                              <!-- Holding / Review Duration Badge -->
                              <div class="my-1">
                                <span class="badge badge-light border text-dark font-11 py-1 px-2 shadow-2xs">
                                  <i class="fas fa-stopwatch text-warning mr-1"></i>
                                  <strong>រយៈពេលពិនិត្យ៖</strong> {{ getApprovalDuration(app, aIdx, resp.approvals, resp) }}
                                </span>
                              </div>
                              <div class="mt-1 text-secondary font-12 pl-2 border-left" v-if="app.comment">
                                <em>«{{ app.comment }}»</em>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- TAB 3: All Movements Audit Trail -->
                  <div v-if="activeTab === 'movements'">
                    <div class="timeline-audit">
                      <div v-for="(m, mIdx) in doc.movements" :key="m.id" class="timeline-row d-flex mb-3">
                        <div class="timeline-marker mr-3 text-center">
                          <div class="marker-circle bg-primary text-white">
                            <i class="fas fa-check font-11"></i>
                          </div>
                          <div class="marker-line"></div>
                        </div>
                        <div class="timeline-content flex-grow-1 p-2 rounded border bg-light">
                          <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="font-weight-bold text-dark font-14">{{ m.action_kh }}</span>
                            <small class="text-muted">{{ formatDate(m.created_at) }}</small>
                          </div>
                          <div class="text-muted small">
                            <i class="fas fa-user text-secondary mr-1"></i>
                            <strong>{{ m.user?.name_kh || m.user?.name || 'ប្រព័ន្ធ' }}</strong>
                            <span class="text-secondary ml-1" v-if="m.user?.position?.title_kh">({{ m.user.position.title_kh }})</span>
                          </div>

                          <!-- Holding duration badge: រយៈពេលដែលស្ថិតនៅក្នុងដៃ User -->
                          <div class="my-1">
                            <span class="badge font-11 py-1 px-2 shadow-2xs" :class="mIdx === doc.movements.length - 1 && doc.status !== 'COMPLETED' ? 'badge-warning text-dark' : 'badge-light border text-dark'">
                              <i class="fas fa-stopwatch mr-1" :class="mIdx === doc.movements.length - 1 && doc.status !== 'COMPLETED' ? 'text-danger' : 'text-primary'"></i>
                              <strong>រយៈពេលកាន់កាប់៖</strong> {{ getMovementDuration(m, mIdx, doc.movements, doc) }}
                            </span>
                          </div>

                          <div class="mt-1 text-dark" v-if="m.comment">
                            {{ m.comment }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="modal-footer bg-light py-2 px-4 justify-content-between">
            <button type="button" class="btn btn-secondary px-4 rounded-pill" @click="close">
              បិទផ្ទាំង
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>

  <!-- Preview Response Modal -->
  <Teleport to="body">
    <div class="custom-modal-backdrop font-khmer" v-if="showPreviewModal" @click.self="showPreviewModal = false">
      <div class="modal-dialog modal-lg modal-dialog-centered my-auto" style="width: 100%; max-width: 850px;">
        <div class="modal-content shadow-2xl border-0 rounded-lg overflow-hidden">
          <div class="modal-header bg-primary text-white py-2 px-3">
            <h6 class="modal-title font-weight-bold mb-0">ខ្លឹមសារលិខិតឆ្លើយតប</h6>
            <button type="button" class="close text-white" @click="showPreviewModal = false"><span>&times;</span></button>
          </div>
          <div class="modal-body p-4 bg-white font-khmer" style="max-height: 75vh; overflow-y: auto;" v-html="previewHtml"></div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { apiGetInboundTrackingFlow } from '@/functions/api/inboundDocument';
import { getMovementDuration, getApprovalDuration } from '@/utils/durationHelper';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  documentId: {
    type: [Number, String],
    default: null
  }
});

const emit = defineEmits(['update:show', 'close']);

const loading = ref(false);
const doc = ref(null);
const custodians = ref([]);
const activeTab = ref('assignments');
const showPreviewModal = ref(false);
const previewHtml = ref('');

const hasCascading = computed(() => {
  return doc.value?.assignments?.some(a => a.parent_assignment_id != null);
});

const latestResponse = computed(() => {
  return doc.value?.responses?.[0] || null;
});

const fetchTrackingData = async () => {
  if (!props.documentId) return;
  loading.value = true;
  try {
    const res = await apiGetInboundTrackingFlow(props.documentId);
    doc.value = res.data.document;
    custodians.value = res.data.current_custodians || [];
  } catch (err) {
    console.error('Error fetching tracking data:', err);
  } finally {
    loading.value = false;
  }
};

watch(
  () => props.show,
  (newVal) => {
    if (newVal && props.documentId) {
      activeTab.value = 'assignments';
      fetchTrackingData();
    }
  }
);

const close = () => {
  emit('update:show', false);
  emit('close');
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'RECEPTION_DRAFT': return 'badge-secondary';
    case 'SUBMITTED_TO_ASSISTANT': return 'badge-info';
    case 'SUBMITTED_TO_DG': return 'badge-primary';
    case 'DG_ANNOTATED': return 'badge-danger';
    case 'DISPATCHED': return 'badge-warning';
    case 'IN_RESPONSE_PROGRESS': return 'badge-warning';
    case 'COMPLETED': return 'badge-success';
    default: return 'badge-secondary';
  }
};

const getStepClass = (stepIndex) => {
  if (!doc.value) return '';
  const status = doc.value.status;

  // Step 1: Reception
  if (stepIndex === 1) return 'step-completed';

  // Step 2: DG Annotation
  if (stepIndex === 2) {
    if (doc.value.dg_annotated_at) return 'step-completed';
    if (['SUBMITTED_TO_ASSISTANT', 'SUBMITTED_TO_DG'].includes(status)) return 'step-active';
    return '';
  }

  // Step 3: Dispatch
  if (stepIndex === 3) {
    if (doc.value.dispatched_at) return 'step-completed';
    if (status === 'DG_ANNOTATED') return 'step-active';
    return '';
  }

  // Step 4: Cascading
  if (stepIndex === 4) {
    if (hasCascading.value) return 'step-completed';
    if (doc.value.dispatched_at && !doc.is_response_required) return 'step-completed';
    if (doc.value.dispatched_at) return 'step-active';
    return '';
  }

  // Step 5: Response Drafting
  if (stepIndex === 5) {
    if (latestResponse.value) return 'step-completed';
    if (status === 'IN_RESPONSE_PROGRESS') return 'step-active';
    return '';
  }

  // Step 6: Approvals Ladder
  if (stepIndex === 6) {
    if (latestResponse.value?.status === 'APPROVED_BY_DG') return 'step-completed';
    if (latestResponse.value?.status === 'UNDER_REVIEW') return 'step-active';
    return '';
  }

  // Step 7: Completed
  if (stepIndex === 7) {
    if (status === 'COMPLETED') return 'step-completed';
    return '';
  }

  return '';
};

const formatDate = (isoStr) => {
  if (!isoStr) return '---';
  try {
    const d = new Date(isoStr);
    return d.toLocaleString('km-KH', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch (e) {
    return isoStr;
  }
};

const viewResponseContent = (resp) => {
  previewHtml.value = resp.content || '<p class="text-muted">មិនមានខ្លឹមសារអត្ថបទ</p>';
  showPreviewModal.value = true;
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap');
.font-khmer { font-family: 'Battambang', sans-serif !important; }

.custom-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.65);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1060;
  overflow-y: auto;
  padding: 1rem;
}

.bg-dark-custom {
  background-color: #1e293b !important;
}

.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.stepper-horizontal {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  position: relative;
  overflow-x: auto;
  padding-bottom: 10px;
}

.step-item {
  flex: 1;
  text-align: center;
  position: relative;
  min-width: 110px;
}

.step-item:not(:last-child)::after {
  content: '';
  position: absolute;
  top: 18px;
  left: 50%;
  width: 100%;
  height: 3px;
  background-color: #e2e8f0;
  z-index: 1;
}

.step-item.step-completed:not(:last-child)::after {
  background-color: #28a745;
}

.step-circle {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background-color: #e2e8f0;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 8px auto;
  font-size: 14px;
  position: relative;
  z-index: 2;
  transition: all 0.3s ease;
}

.step-completed .step-circle {
  background-color: #28a745;
  color: #fff;
  box-shadow: 0 0 10px rgba(40, 167, 69, 0.4);
}

.step-active .step-circle {
  background-color: #ffc107;
  color: #000;
  box-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
  animation: pulse 1.5s infinite;
}

.step-label {
  font-size: 13px;
  font-weight: bold;
  color: #334155;
  margin-bottom: 2px;
}

.step-sub {
  font-size: 11px;
  color: #64748b;
}

.timeline-row .timeline-marker {
  position: relative;
}

.marker-circle {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.marker-line {
  width: 2px;
  background-color: #cbd5e1;
  position: absolute;
  top: 22px;
  bottom: -15px;
  left: 10px;
}

.timeline-row:last-child .marker-line {
  display: none;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}
</style>
