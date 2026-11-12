<template>
  <div class="word-editor-wrapper" :class="{ 'fullscreen-mode': isFullscreen }">
    <!-- 1. Ribbon -->
    <div class="word-ribbon shadow-xs">
      <div class="lc-ribbon-row lc-ribbon-top">
        <div class="lc-ribbon-group" v-if="!readonly">
          <span class="lc-ribbon-label">គំរូ៖</span>
          <div class="btn-group btn-group-sm" role="group" aria-label="Document templates">
            <button
              type="button"
              class="btn lc-btn"
              :class="docType === 'REPORT' ? 'btn-primary' : 'btn-outline-primary'"
              @click="insertTemplate('REPORT')"
              title="បញ្ចូលទម្រង់របាយការណ៍"
            >
              <i class="fas fa-file-alt mr-1"></i>របាយការណ៍
            </button>
            <button
              type="button"
              class="btn lc-btn"
              :class="docType === 'SUBMISSION_NOTE' ? 'btn-info' : 'btn-outline-info'"
              @click="insertTemplate('SUBMISSION_NOTE')"
              title="បញ្ចូលទម្រង់កំណត់បង្ហាញ"
            >
              <i class="fas fa-file-signature mr-1"></i>កំណត់បង្ហាញ
            </button>
            <button
              type="button"
              class="btn lc-btn"
              :class="docType === 'LETTER' ? 'btn-success' : 'btn-outline-success'"
              @click="insertTemplate('LETTER')"
              title="បញ្ចូលទម្រង់លិខិតរដ្ឋបាល"
            >
              <i class="fas fa-envelope-open-text mr-1"></i>លិខិត
            </button>
          </div>

          <span class="lc-divider"></span>

          <template v-if="isApprover">
            <button
              type="button"
              class="btn btn-sm btn-outline-warning text-dark font-weight-bold lc-btn"
              @click="openSuperiorReviewModal"
              title="បញ្ចូលកំណត់ត្រាមតិយោបល់ និងហត្ថលេខារបស់ថ្នាក់ដឹកនាំ"
            >
              <i class="fas fa-clipboard-check mr-1 text-warning"></i>+ យោបល់ថ្នាក់ដឹកនាំ
            </button>
            <button
              v-if="approvals && approvals.length > 0"
              type="button"
              class="btn btn-sm btn-outline-info font-weight-bold lc-btn"
              @click="syncWorkflowApprovals"
              title="ធ្វើបច្ចុប្បន្នភាពមតិយោបល់ថ្នាក់ដឹកនាំពី Workflow"
            >
              <i class="fas fa-sync-alt mr-1"></i>Sync Workflow
            </button>
          </template>

          <button
            type="button"
            class="btn btn-sm font-weight-bold lc-btn"
            :class="officerSignature ? 'btn-outline-success' : 'btn-outline-primary'"
            @click="showSignatureModal = true"
            title="គូស ឬកែប្រែហត្ថលេខារបស់ខ្ញុំ"
          >
            <i class="fas fa-file-signature mr-1"></i>
            <span>{{ officerSignature ? 'ហត្ថលេខារបស់ខ្ញុំ ✓' : '+ គូស/ដាក់ហត្ថលេខា' }}</span>
          </button>
        </div>

        <div class="lc-ribbon-group ml-auto">
          <div class="btn-group btn-group-sm" role="tablist">
            <button type="button" class="btn lc-btn" :class="mode === 'write' ? 'btn-success' : 'btn-outline-secondary'" @click="setMode('write')">
              <i class="fas fa-pen mr-1"></i>សរសេរ
            </button>
            <button type="button" class="btn lc-btn" :class="mode === 'view' ? 'btn-success' : 'btn-outline-secondary'" @click="setMode('view')">
              <i class="fas fa-file mr-1"></i>មើលជាទំព័រ
            </button>
          </div>
          <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-dark lc-btn" @click="printDocument" title="បោះពុម្ព ឬរក្សាទុកជា PDF">
              <i class="fas fa-print mr-1"></i>បោះពុម្ព / PDF
            </button>
            <button type="button" class="btn btn-outline-success lc-btn" @click="exportToWord" title="ទាញយកជាឯកសារ Word">
              <i class="fas fa-file-word mr-1"></i>Word
            </button>
            <button type="button" class="btn btn-outline-secondary lc-btn" @click="toggleFullscreen" :title="isFullscreen ? 'បិទពេញអេក្រង់' : 'បើកពេញអេក្រង់'">
              <i class="fas" :class="isFullscreen ? 'fa-compress' : 'fa-expand'"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- 2. Formatting toolbar: only for «ខ្លឹមសារ» and «ឃ្លាបញ្ចប់» -->
      <div class="lc-ribbon-row lc-ribbon-format" v-if="!readonly && mode === 'write'" @mousedown.prevent>
        <div class="btn-group btn-group-sm">
          <button type="button" class="btn btn-light border btn-tool" @click="fmt('undo')" title="Undo (Ctrl+Z)"><i class="fas fa-undo"></i></button>
          <button type="button" class="btn btn-light border btn-tool" @click="fmt('redo')" title="Redo (Ctrl+Y)"><i class="fas fa-redo"></i></button>
        </div>
        <div class="btn-group btn-group-sm">
          <button type="button" class="btn btn-light border btn-tool" @click="fmt('bold')" title="ដិត"><strong>B</strong></button>
          <button type="button" class="btn btn-light border btn-tool" @click="fmt('italic')" title="ទ្រេត"><em>I</em></button>
          <button type="button" class="btn btn-light border btn-tool" @click="fmt('underline')" title="បន្ទាត់ក្រោម"><u>U</u></button>
          <button type="button" class="btn btn-light border btn-tool lc-moul-btn" @click="fmt('moul')" title="អក្សរមូល (ឧ. ឯកឧត្តមអគ្គនាយក)">មូល</button>
        </div>
        <div class="btn-group btn-group-sm">
          <button type="button" class="btn btn-light border btn-tool" @click="fmt('insertUnorderedList')" title="ចំណុច"><i class="fas fa-list-ul"></i></button>
          <button type="button" class="btn btn-light border btn-tool" @click="fmt('insertOrderedList')" title="លេខរៀង"><i class="fas fa-list-ol"></i></button>
        </div>
        <div class="btn-group btn-group-sm">
          <button type="button" class="btn btn-light border btn-tool" @click="fmt('justifyLeft')" title="តម្រឹមឆ្វេង"><i class="fas fa-align-left"></i></button>
          <button type="button" class="btn btn-light border btn-tool" @click="fmt('justifyCenter')" title="តម្រឹមកណ្តាល"><i class="fas fa-align-center"></i></button>
          <button type="button" class="btn btn-light border btn-tool" @click="fmt('justifyFull')" title="តម្រឹមស្មើសងខាង"><i class="fas fa-align-justify"></i></button>
        </div>
        <div class="btn-group btn-group-sm">
          <button type="button" class="btn btn-light border btn-tool" @click="fmt('table')" title="បញ្ចូលតារាង"><i class="fas fa-table mr-1 text-primary"></i>តារាង</button>
          <button type="button" class="btn btn-light border btn-tool" @click="fmt('removeFormat')" title="លុបទម្រង់"><i class="fas fa-eraser"></i></button>
        </div>
        <span class="lc-format-hint d-none d-lg-inline-flex align-items-center ml-auto">
          <i class="fas fa-info-circle mr-1 text-primary"></i>កែទម្រង់បានក្នុង «ខ្លឹមសារ» «ឃ្លាបញ្ចប់» និងប្រើ «មូល» ក្នុង «កម្មវត្ថុ» · ផ្ដាច់ទំព័រស្វ័យប្រវត្តិ
        </span>
      </div>
    </div>

    <!-- 3. Canvas -->
    <div class="lc-canvas" ref="canvasContainer">
      <div v-show="mode === 'write'" class="lc-sheet-wrap" :style="{ width: (794 * scale) + 'px', height: sheetHeight + 'px' }">
        <div
          class="lc-sheet lc-doc"
          :class="{ 'lc-editing': !readonly }"
          ref="paperEditor"
          :style="{ transform: `scale(${scale})` }"
          spellcheck="false"
          @input="onEditorInput"
          @paste="onEditorPaste"
          @keydown="onEditorKeydown"
          @focusin="onEditorFocusIn"
          @focusout="onEditorFocusOut"
          @click="onEditorClick"
        ></div>
        <div class="lc-guides" :style="{ transform: `scale(${scale})` }">
          <div v-for="g in guides" :key="g.page" class="lc-guide" :style="{ top: g.top + 'px' }">
            <span>ចុងទំព័រ {{ toKhmerNum(g.page - 1) }} · ទំព័រ {{ toKhmerNum(g.page) }} ចាប់ផ្តើមត្រង់នេះ</span>
          </div>
        </div>
      </div>
      <div v-show="mode === 'view'" class="lc-pages" ref="pagesEl"></div>
    </div>

    <!-- 4. Status bar -->
    <div class="word-statusbar bg-light p-1 px-3 border-top d-flex justify-content-between align-items-center text-muted small">
      <div>
        <span class="mr-3"><i class="fas fa-file-word text-primary mr-1"></i> កម្មវិធីរៀបចំឯកសារ</span>
        <span class="mr-3">ពាក្យសរុប៖ <strong>{{ toKhmerNum(wordCount) }}</strong></span>
        <span>ចំនួនទំព័រ៖ <strong>{{ toKhmerNum(pageCount) }}</strong></span>
      </div>
      <div>
        <span v-if="readonly" class="badge badge-secondary">មើលតែប៉ុណ្ណោះ (Read-Only)</span>
        <span v-else class="text-success"><i class="fas fa-check-circle mr-1"></i> ចុចលើក្រដាសដើម្បីសរសេរ</span>
      </div>
    </div>

    <!-- Superior Review & Signature Modal -->
    <Teleport to="body">
      <div class="custom-modal-backdrop" v-if="showSuperiorReviewModal">
        <div class="modal-dialog modal-lg modal-dialog-centered my-auto" style="width: 100%; max-width: 720px;">
          <div class="modal-content shadow-2xl border-0 rounded-lg overflow-hidden">
            <div class="modal-header bg-warning text-dark py-3 px-4 d-flex justify-content-between align-items-center">
              <h5 class="modal-title font-weight-bold mb-0">
                <i class="fas fa-file-signature mr-2 text-dark"></i>
                បញ្ចូលមតិយោបល់ និងហត្ថលេខាថ្នាក់ដឹកនាំ
              </h5>
              <button type="button" class="close text-dark" @click="showSuperiorReviewModal = false" style="font-size: 1.5rem; border: none; background: transparent; cursor: pointer;">
                <span>&times;</span>
              </button>
            </div>

            <div class="modal-body p-4 bg-light">
              <!-- 1. Header Title -->
              <div class="form-group mb-3">
                <label class="font-weight-bold text-dark small mb-1">
                  ចំណងជើងផ្នែកយោបល់ / ចំណារ <span class="text-danger">*</span>
                </label>
                <input
                  type="text"
                  class="form-control font-weight-bold"
                  v-model="superiorForm.title"
                  placeholder="ឧ. ចំណារ និងការសម្រេចរបស់ឯកឧត្តមអគ្គនាយក ឬ យោបល់របស់ប្រធាននាយកដ្ឋាន..."
                />
              </div>

              <!-- 2. Comment / Opinion -->
              <div class="form-group mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="font-weight-bold text-dark small mb-0">
                    ខ្លឹមសារមតិយោបល់ / ចំណារណែនាំ <span class="text-danger">*</span>
                  </label>
                  <!-- Quick comment chips -->
                  <div class="d-flex flex-wrap gap-1">
                    <button
                      type="button"
                      class="btn btn-xs btn-outline-primary mr-1 mb-1 font-weight-bold"
                      @click="superiorForm.comment = 'សូមគោរពជូនឯកឧត្តមអគ្គនាយក\nមេត្តាពិនិត្យ និងសម្រេច'"
                    >
                      + គោរពជូន ឯ.ឧ អគ្គនាយក
                    </button>
                    <button
                      type="button"
                      class="btn btn-xs btn-outline-info mr-1 mb-1 font-weight-bold"
                      @click="superiorForm.comment = 'សូមគោរពជូនលោកប្រធាននាយកដ្ឋាន\nមេត្តាពិនិត្យ និងសម្រេច'"
                    >
                      + គោរពជូន ប្រធាននាយកដ្ឋាន
                    </button>
                    <button
                      type="button"
                      class="btn btn-xs btn-outline-success mr-1 mb-1 font-weight-bold"
                      @click="superiorForm.comment = 'បានពិនិត្យ និងឯកភាព'"
                    >
                      + បានពិនិត្យ និងឯកភាព
                    </button>
                    <button
                      type="button"
                      class="btn btn-xs btn-outline-secondary mb-1"
                      @click="superiorForm.comment = 'សូមឯកភាព និងចាត់ចែងអនុវត្តបន្តតាមនីតិវិធី'"
                    >
                      + សូមឯកភាព និងចាត់ចែងបន្ត
                    </button>
                  </div>
                </div>
                <textarea
                  class="form-control"
                  rows="3"
                  v-model="superiorForm.comment"
                  placeholder="កត់ត្រាខ្លឹមសារយោបល់ ឬការណែនាំនៅទីនេះ..."
                ></textarea>
              </div>

              <!-- 3. Date & Auto Placement Row -->
              <div class="row mb-3 align-items-end">
                <div class="col-md-7 mb-2 mb-md-0">
                  <label class="font-weight-bold text-dark small mb-1">
                    <i class="fas fa-calendar-alt text-primary mr-1"></i> កាលបរិច្ឆេទចុះហត្ថលេខា
                  </label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="superiorForm.dateStr"
                    placeholder="ឧ. ៤/១១/២៦ ឬ រាជធានីភ្នំពេញ ថ្ងៃទី... ខែ... ឆ្នាំ..."
                  />
                </div>
                <div class="col-md-5">
                  <div class="small text-primary bg-light border rounded px-2 py-2">
                    <i class="fas fa-magic mr-1"></i> ទីតាំងប្រអប់៖ <strong>Auto</strong> (រៀបចំទីតាំងទូលាយ និងមិនត្រួតគ្នា)
                  </div>
                </div>
              </div>

              <!-- 4. Signature Preview & Management -->
              <div class="card border rounded p-3 bg-white mb-2 shadow-xs">
                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                  <span class="font-weight-bold text-dark small">
                    <i class="fas fa-signature text-success mr-1"></i> ហត្ថលេខា និងឈ្មោះថ្នាក់ដឹកនាំ
                  </span>
                  <button
                    type="button"
                    class="btn btn-xs btn-outline-primary font-weight-bold"
                    @click="showSignatureModal = true"
                  >
                    <i class="fas fa-pen mr-1"></i> {{ (userStore.signature_image || superiorForm.signatureUrl) ? 'ផ្លាស់ប្តូរ ឬគូសហត្ថលេខា' : '+ គូស/បង្ហោះហត្ថលេខា' }}
                  </button>
                </div>
                <div class="row align-items-center">
                  <div class="col-sm-6 text-center border-right">
                    <div class="small text-muted mb-1">គំរូហត្ថលេខាឌីជីថល៖</div>
                    <div v-if="userStore.signature_image || superiorForm.signatureUrl" class="p-2 border rounded bg-light d-inline-block">
                      <img :src="userStore.signature_image || superiorForm.signatureUrl" alt="ហត្ថលេខា" style="height: 55px; max-width: 180px; object-fit: contain;" />
                    </div>
                    <div v-else class="p-2 border rounded bg-light d-inline-block">
                      <span style="font-family: 'Brush Script MT', 'Segoe Script', cursive, serif; font-size: 18pt; color: #002060; font-weight: bold; border-bottom: 1.5px dashed #002060; padding: 0 15px;">
                        {{ superiorForm.name }}
                      </span>
                    </div>
                  </div>
                  <div class="col-sm-6 pt-2 pt-sm-0">
                    <div class="small text-muted">ឈ្មោះ៖</div>
                    <div class="font-weight-bold text-dark font-14">{{ superiorForm.name }}</div>
                    <div class="small text-muted mt-1">តួនាទី៖</div>
                    <div class="text-dark small">{{ superiorForm.position }}</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer bg-white py-2 px-4 d-flex justify-content-between">
              <button type="button" class="btn btn-secondary px-3" @click="showSuperiorReviewModal = false">
                បោះបង់
              </button>
              <button
                type="button"
                class="btn btn-warning px-4 font-weight-bold text-dark shadow-sm"
                @click="applySuperiorReview"
              >
                <i class="fas fa-check-circle mr-1"></i> បញ្ចូលទៅក្នុងរបាយការណ៍
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Signature Modal -->
    <SignatureModal v-model:show="showSignatureModal" @saved="onSignatureSaved" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, watch, computed, nextTick } from 'vue';
import Swal from 'sweetalert2';
import { useUserStore } from '@/stores/user';
import SignatureModal from '@/components/common/SignatureModal.vue';
import {
  getShortKhmerDateString,
  generateDraggableOpinionBoxHtml,
  embedOpinionBoxIntoDom,
  setupDraggableOpinionBoxes,
  escapeHtml,
  sanitizeUrl,
  extractFilePathFromUrl,
} from '@/utils/opinionBoxHelper';
import {
  DOC_CSS, DOC_VERSION_MARK, PAGE_W, PAGE_H,
  buildTemplate, cleanBodyHtml, collectBlocks, paginate, renderPagesHtml, openPrintPagesWindow, toKhmerNum, extractDocNumberPrefix,
} from '@/utils/letterComposer';

const userStore = useUserStore();

const props = defineProps({
  modelValue: { type: String, default: '' },
  docType: { type: String, default: 'REPORT' }, // 'REPORT', 'SUBMISSION_NOTE', 'LETTER'
  readonly: { type: Boolean, default: false },
  isApprover: { type: Boolean, default: false },
  organizationName: { type: String, default: 'និយ័តករបរធនបាលកិច្ច' },
  departmentName: { type: String, default: 'នាយកដ្ឋាន...' },
  officeName: { type: String, default: '' },
  officerName: { type: String, default: '' },
  officerPosition: { type: String, default: 'អ្នកធ្វើរបាយការណ៍' },
  officerSignature: { type: String, default: '' },
  approvals: { type: Array, default: () => [] },
  // New (optional): rendered by the system, never stored in the HTML
  documentNumber: { type: String, default: '' }, // e.g. "០០១/២៦"
  departmentAbbr: { type: String, default: '' }, // e.g. "ន.ក.ទ." (falls back to name guess)
  logoUrl: { type: String, default: '/logo.png' },
  footerHtml: { type: String, default: '' }, // page-1 footer (address, QR...) — trusted, from settings
});

const emit = defineEmits(['update:modelValue', 'update:documentNumber', 'change', 'signature-updated']);
const currentDocNumber = ref(props.documentNumber || '');

const paperEditor = ref(null);
const canvasContainer = ref(null);
const pagesEl = ref(null);
const isFullscreen = ref(false);
const showSignatureModal = ref(false);
const mode = ref(props.readonly ? 'view' : 'write');
const scale = ref(1);
const sheetHeight = ref(PAGE_H);
const guides = ref([]);
const pageCount = ref(1);
const wordCount = ref(0);

const khmerMonths = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'];

// Dual calendar block inside the drafter signature. Editable islands are marked with
// data-field (contenteditable is added at runtime, so the HTML purifier may strip it safely).
const getOfficialKhmerDateHtml = () => {
  const d = new Date();
  const day = toKhmerNum(d.getDate());
  const month = khmerMonths[d.getMonth()] || 'វិច្ឆិកា';
  const year = toKhmerNum(d.getFullYear());
  return `
    <div data-field="date-lunar" style="font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 12pt; color: #222; margin-bottom: 2px;" title="កាលបរិច្ឆេទពុទ្ធសករាជ (ចុចដើម្បីកែ)">ថ្ងៃ................... ខែ............. ឆ្នាំ........ ព.ស.២៥......</div>
    <div data-field="date-solar" style="font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 12pt; color: #111; margin-bottom: 4px;" title="កាលបរិច្ឆេទគ្រឹះសករាជ (ចុចដើម្បីកែ)">រាជធានីភ្នំពេញ ថ្ងៃទី${day} ខែ${month} ឆ្នាំ${year}</div>
  `;
};

// Default department abbreviations in Trust Regulator (និយ័តករបរធនបាលកិច្ច)
const getDepartmentAbbr = (name = '') => {
  if (!name) return '...';
  if (name.includes('ចុះបញ្ជី')) return 'ច.ប.ច.';
  if (name.includes('កិច្ចការទូទៅ')) return 'ន.ក.ទ.';
  if (name.includes('គតិយុត្ត') || name.includes('អធិការកិច្ច')) return 'គ.អ.';
  if (name.includes('ស្រាវជ្រាវ') || name.includes('បណ្តុះបណ្តាល') || name.includes('សហប្រតិបត្តិការ')) return 'ស.ប.ស.';
  
  const clean = name.replace(/^នាយកដ្ឋាន\s*/, '').trim();
  const words = clean.split(/\s+/);
  if (words.length > 0) {
    const letters = words.map(w => w.charAt(0)).filter(Boolean).join('.');
    return letters + '.';
  }
  return '...';
};


const getDrafterSignatureHtml = () => {
  const dept = escapeHtml(props.departmentName || 'នាយកដ្ឋាន...');
  const office = props.officeName ? escapeHtml(props.officeName) : '';
  const officer = escapeHtml(props.officerName || '.....................................');
  const pos = escapeHtml(props.officerPosition || 'អ្នកធ្វើរបាយការណ៍');
  const signatureUrl = sanitizeUrl(props.officerSignature || '');
  const drafterFilePath = extractFilePathFromUrl(props.officerSignature || '');
  const drafterDataAttr = drafterFilePath ? ` data-file-path="${drafterFilePath}"` : '';

  const signatureImgHtml = signatureUrl
    ? `<div style="text-align: center; margin: 4px 0 2px 0;">
         <img src="${signatureUrl}"${drafterDataAttr} alt="ហត្ថលេខា" style="height: 60px; max-width: 190px; object-fit: contain; display: inline-block;" />
       </div>`
    : `<div class="digital-signature-placeholder" style="text-align: center; margin: 4px 0 2px 0; min-height: 52px; display: flex; align-items: center; justify-content: center;">
         <span style="font-family: 'Brush Script MT', 'Segoe Script', cursive, serif; font-size: 20pt; color: #002060; font-weight: bold; border-bottom: 1.5px dashed #002060; padding: 2px 20px; letter-spacing: 1px; display: inline-block;">
           ${officer}
         </span>
       </div>`;

  return `
    <table class="drafter-signature-table" data-region="signature" style="width: 100%; max-width: 100%; border: none; border-collapse: collapse; margin-top: 30px; margin-bottom: 25px; page-break-inside: avoid; break-inside: avoid; line-height: 2.1; table-layout: fixed; box-sizing: border-box;">
      <tr>
        <td class="opinion-left-col" style="width: 46%; border: none; vertical-align: top; text-align: center; padding-right: 10px; box-sizing: border-box;"></td>
        <td style="width: 54%; border: none; vertical-align: top; text-align: center; font-size: 12pt; line-height: 2.1; padding-left: 10px; box-sizing: border-box; overflow-wrap: break-word; word-break: break-word;">
          ${getOfficialKhmerDateHtml()}
          <div class="lc-moul" style="font-family: 'Khmer OS Muol Light', 'Moul', serif; font-size: 12pt; color: #000; margin-top: 2px;">
            ${dept}
          </div>
          ${office ? `<div class="lc-moul" style="font-family: 'Khmer OS Muol Light', 'Moul', serif; font-size: 12pt; color: #000;">${office}</div>` : ''}
          <div class="lc-moul" style="font-family: 'Khmer OS Muol Light', 'Moul', serif; font-size: 12pt; color: #000; margin-top: 4px;">
            ${pos}
          </div>
          ${signatureImgHtml}
          <div class="lc-moul" style="font-family: 'Khmer OS Muol Light', 'Moul', serif; font-size: 12pt; color: #000;">
            ${officer}
          </div>
        </td>
      </tr>
    </table>
  `;
};


// Superior Review & Opinion Modal State & Logic
const showSuperiorReviewModal = ref(false);
const savedRange = ref(null);

const superiorForm = reactive({
  title: '',
  comment: '',
  dateStr: '',
  placement: 'AUTO',
  name: '',
  position: '',
  signatureUrl: '',
});

const isDgUser = computed(() => {
  const level = userStore.position?.level ? Number(userStore.position.level) : 99;
  return level <= 1 || (userStore.level && userStore.level.toUpperCase() === 'ADMIN');
});

const openSuperiorReviewModal = () => {
  if (props.readonly) return;

  const selection = window.getSelection();
  if (selection && selection.rangeCount > 0 && paperEditor.value && paperEditor.value.contains(selection.getRangeAt(0).commonAncestorContainer)) {
    savedRange.value = selection.getRangeAt(0).cloneRange();
  } else {
    savedRange.value = null;
  }

  const posTitle = userStore.position?.title_kh || 'ថ្នាក់ដឹកនាំ';
  superiorForm.title = isDgUser.value
    ? 'ចំណារ និងការសម្រេចរបស់ឯកឧត្តមអគ្គនាយក'
    : `យោបល់របស់${posTitle}`;
  superiorForm.comment = isDgUser.value
    ? 'បានពិនិត្យ និងសម្រេចឯកភាព'
    : 'សូមគោរពជូនឯកឧត្តមអគ្គនាយក\nមេត្តាពិនិត្យ និងសម្រេច';
  superiorForm.dateStr = getShortKhmerDateString(new Date());
  superiorForm.placement = 'AUTO';
  superiorForm.name = userStore.name_kh || userStore.name || '';
  superiorForm.position = isDgUser.value ? 'អគ្គនាយក' : posTitle;
  superiorForm.signatureUrl = userStore.signature_image || '';

  // If user already has an opinion box in the report, pre-fill its existing values
  if (paperEditor.value) {
    const existingBox = paperEditor.value.querySelector(`.draggable-opinion-box[data-owner-id="${userStore.id}"]`);
    if (existingBox) {
      const existingComment = existingBox.querySelector('.opinion-box-comment')?.textContent?.trim();
      const existingDate = existingBox.querySelector('.opinion-box-date')?.textContent?.trim();
      if (existingComment) superiorForm.comment = existingComment;
      if (existingDate) superiorForm.dateStr = existingDate;
    }
  }

  showSuperiorReviewModal.value = true;
};

const refreshDraggableBoxes = () => {
  if (!paperEditor.value) return;
  if (cleanupDraggable) cleanupDraggable();
  cleanupDraggable = setupDraggableOpinionBoxes(
    paperEditor.value,
    () => { emitUpdate(); scheduleLayout(); },
    { currentUserId: userStore.id }
  );
};

const applySuperiorReview = () => {
  if (!paperEditor.value) return;

  const shortDateKh = superiorForm.dateStr || getShortKhmerDateString(new Date());
  const boxHtml = generateDraggableOpinionBoxHtml({
    comment: superiorForm.comment || (isDgUser.value ? 'បានពិនិត្យ និងសម្រេចឯកភាព' : 'សូមគោរពជូនឯកឧត្តមអគ្គនាយក\nមេត្តាពិនិត្យ និងសម្រេច'),
    signatureUrl: userStore.signature_image || superiorForm.signatureUrl,
    reviewerName: superiorForm.name || userStore.name_kh || userStore.name,
    shortDateKh,
    ownerId: userStore.id,
  });

  const hadExistingBox = Boolean(paperEditor.value.querySelector(`.draggable-opinion-box[data-owner-id="${userStore.id}"]`));
  embedOpinionBoxIntoDom(paperEditor.value, boxHtml, 'AUTO');

  refreshDraggableBoxes();
  emitUpdate();
  scheduleLayout();
  showSuperiorReviewModal.value = false;

  Swal.fire({
    icon: 'success',
    title: hadExistingBox ? 'បានធ្វើបច្ចុប្បន្នភាព!' : 'បានបញ្ចូលយោបល់ និងហត្ថលេខា!',
    text: hadExistingBox
      ? 'លោកអ្នកមានប្រអប់មតិយោបល់រួចហើយ ប្រព័ន្ធបានធ្វើបច្ចុប្បន្នភាពលើប្រអប់របស់លោកអ្នកដោយមិនបង្កើតប្រអប់ស្ទួនឡើយ។'
      : 'មតិយោបល់ និងហត្ថលេខារបស់ថ្នាក់ដឹកនាំត្រូវបានរៀបចំទីតាំងដោយស្វ័យប្រវត្តិ។ លោកអ្នកអាចចុចអូសរំកិលទីតាំងបានតាមតម្រូវការ!',
    timer: 2000,
    showConfirmButton: false
  });
};

// Superiors' Review Block: ONLY renders reviews that have ALREADY been submitted!
const getReviewBlockHtml = (approvalsList = []) => {
  const list = (approvalsList && approvalsList.length > 0) ? approvalsList : (props.approvals || []);
  if (!list || !list.length) return '';

  // Only display completed actions by actual users
  const completedReviews = list.filter(a => ['FORWARD', 'SUBMIT', 'DG_APPROVE', 'RETURN'].includes(a.action) && a.user);
  if (!completedReviews.length) return '';

  const formatAppDate = (app) => {
    if (!app || !app.created_at) return '';
    try {
      const d = new Date(app.created_at);
      const day = toKhmerNum(d.getDate());
      const monthNamesKh = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'];
      const month = monthNamesKh[d.getMonth()];
      const year = toKhmerNum(d.getFullYear());
      return `ថ្ងៃទី${day} ខែ${month} ឆ្នាំ${year}`;
    } catch {
      return '';
    }
  };

  let reviewCardsHtml = '';
  completedReviews.forEach((app, idx) => {
    const numKh = toKhmerNum(idx + 1);
    const dateKh = escapeHtml(formatAppDate(app));
    const reviewerName = escapeHtml(app.user?.name_kh || app.user?.name || '');
    const reviewerPos = escapeHtml(app.user?.position?.title_kh || app.stage_kh || 'ថ្នាក់ដឹកនាំ');
    const isDg = (app.action === 'DG_APPROVE' || app.stage === 'DG');
    const reviewerSig = sanitizeUrl(app.user?.signature_image || '');
    const reviewerFilePath = extractFilePathFromUrl(app.user?.signature_image || '');
    const reviewerDataAttr = reviewerFilePath ? ` data-file-path="${reviewerFilePath}"` : '';
    const reviewerSigHtml = reviewerSig
      ? `<div style="text-align: right; margin: 4px 0 2px auto;">
           <img src="${reviewerSig}"${reviewerDataAttr} alt="ហត្ថលេខា" style="height: 55px; max-width: 160px; object-fit: contain; display: inline-block;" />
         </div>`
      : `<div style="height: 35px;"></div>`;

    const title = isDg
      ? `${numKh}. ចំណារ និងការសម្រេចរបស់ឯកឧត្តមអគ្គនាយក`
      : `${numKh}. យោបល់របស់${reviewerPos}`;

    const badge = isDg
      ? `<span style="display:inline-block; font-size:8.5pt; color:#1e40af; background:#dbeafe; padding:1px 8px; border-radius:4px; font-weight:bold;">✓ បានសម្រេច និងឯកភាព</span>`
      : `<span style="display:inline-block; font-size:8.5pt; color:#16a34a; background:#dcfce7; padding:1px 8px; border-radius:4px; font-weight:bold;">✓ បានពិនិត្យ និងចារឆ្លង</span>`;

    const comment = app.comment
      ? `<span style="color:#0f172a; font-weight:500;">${escapeHtml(app.comment)}</span>`
      : `<span style="color:#64748b; font-style:italic;">(បានឯកភាពតាមឋានានុក្រម)</span>`;

    reviewCardsHtml += `
      <div style="border: 1px solid #94a3b8; border-radius: 4px; padding: 12px 16px; margin-bottom: 12px; background-color: ${isDg ? '#f8fafc' : '#ffffff'}; page-break-inside: avoid; break-inside: avoid;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
          <div>
            <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 10.5pt; color: ${isDg ? '#002060' : '#0f172a'}; margin-bottom: 3px;">
              ${title}
            </div>
            ${badge}
          </div>
          ${dateKh ? `<div style="font-size: 9.5pt; color: #475569;">កាលបរិច្ឆេទ៖ ${dateKh}</div>` : ''}
        </div>
        <div style="font-size: 10.5pt; line-height: 2.1; margin-top: 8px; margin-bottom: 15px; color: #1e293b;">
          <strong>${isDg ? 'ខ្លឹមសារចំណារ៖' : 'ខ្លឹមសារយោបល់៖'}</strong> ${comment}
        </div>
        <div style="text-align: right; padding-right: 10px;">
          <div style="font-style: italic; font-size: 9pt; color: #64748b;">${isDg ? '(ហត្ថលេខា និងត្រា)' : '(ហត្ថលេខា និងឈ្មោះ)'}</div>
          ${reviewerSigHtml}
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt; color: ${isDg ? '#002060' : '#000'}; margin-top: 2px;">
            ${reviewerName}
          </div>
          <div style="font-size: 9.5pt; color: #475569;">
            ${reviewerPos}
          </div>
        </div>
      </div>
    `;
  });

  return `
    <div class="review-signatures-wrapper" style="margin-top: 30px; margin-bottom: 25px; page-break-inside: avoid; break-inside: avoid;">
      <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt; color: #002060; margin-bottom: 10px; border-bottom: 1.5px solid #002060; padding-bottom: 4px;">
        កំណត់ត្រាមតិយោបល់ និងការសម្រេចរបស់ថ្នាក់ដឹកនាំ៖
      </div>
      ${reviewCardsHtml}
    </div>
  `;
};

const onSignatureSaved = (newSigUrl) => {
  emit('signature-updated', newSigUrl);
  superiorForm.signatureUrl = newSigUrl;
  if (paperEditor.value) {
    const drafterTable = paperEditor.value.querySelector('.drafter-signature-table');
    if (drafterTable) {
      replaceDrafterTable(drafterTable);
    }
  }
};

const syncWorkflowApprovals = () => {
  if (props.readonly || !paperEditor.value) return;
  const newHtml = getReviewBlockHtml(props.approvals);
  const existingBlock = paperEditor.value.querySelector('.review-signatures-wrapper');
  if (existingBlock) {
    if (newHtml) {
      existingBlock.outerHTML = newHtml;
    } else {
      existingBlock.remove();
    }
  } else if (newHtml) {
    paperEditor.value.insertAdjacentHTML('beforeend', newHtml);
  }
  emitUpdate();
  Swal.fire({
    icon: 'success',
    title: 'បានធ្វើបច្ចុប្បន្នភាព!',
    text: 'បាន Sync មតិយោបល់ និងការសម្រេចរបស់ថ្នាក់ដឹកនាំពី Workflow ចូលក្នុងតារាងដោយជោគជ័យ។',
    timer: 1600,
    showConfirmButton: false
  });
};

// Replace the drafter block in place, keeping any opinion boxes already placed in its left column.
const replaceDrafterTable = (existing) => {
  const tmp = document.createElement('div');
  tmp.innerHTML = getDrafterSignatureHtml().trim();
  const fresh = tmp.querySelector('.drafter-signature-table');
  if (!fresh || !paperEditor.value) return;
  if (existing) {
    const oldLeft = existing.querySelector('.opinion-left-col');
    const newLeft = fresh.querySelector('.opinion-left-col');
    if (oldLeft && newLeft) newLeft.innerHTML = oldLeft.innerHTML;
    existing.replaceWith(fresh);
  } else {
    const reviews = paperEditor.value.querySelector('.review-signatures-wrapper');
    if (reviews) reviews.before(fresh); else paperEditor.value.appendChild(fresh);
  }
  hydrate();
  emitUpdate();
  scheduleLayout();
};

// ---------------------------------------------------------------------------
// Templates (structured regions)
// ---------------------------------------------------------------------------
const templateContext = () => ({
  organizationName: escapeHtml(props.organizationName || 'និយ័តករបរធនបាលកិច្ច'),
  abbr: escapeHtml(props.departmentAbbr || getDepartmentAbbr(props.departmentName || '')),
  logoUrl: sanitizeUrl(props.logoUrl || '/logo.png'),
  drafterHtml: getDrafterSignatureHtml(),
});

const insertTemplate = async (type, { confirm = true } = {}) => {
  if (!paperEditor.value) return;
  if (confirm && paperEditor.value.innerText.trim()) {
    const result = await Swal.fire({
      title: 'តើអ្នកប្រាកដទេ?',
      text: 'ការបញ្ចូលគំរូថ្មីនឹងជំនួសខ្លឹមសារចាស់ដែលមានស្រាប់។ តើអ្នកចង់បន្តទេ?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'បាទ/ចាស ជំនួស',
      cancelButtonText: 'បោះបង់'
    });
    if (!result.isConfirmed) return;
  }
  paperEditor.value.innerHTML = buildTemplate(type, templateContext());
  hydrate();
  emitUpdate();
  refreshLayout();
};

// ---------------------------------------------------------------------------
// Hydrate / serialize: editing attributes live only in the browser, never in the saved HTML
// ---------------------------------------------------------------------------
const RICH_SELECTOR = '[data-region="body"], [data-field="closing"]';
const EDITABLE_SELECTOR = '[data-region="body"], [data-field]';
const PLACEHOLDERS = {
  subject: '(កម្មវត្ថុ)', refs: '(យោង)', recipient: '(អ្នកទទួល)', closing: '(ឃ្លាបញ្ចប់)',
};

const hydrate = () => {
  const root = paperEditor.value;
  if (!root) return;
  root.querySelectorAll('[contenteditable]').forEach((el) => el.removeAttribute('contenteditable'));
  root.querySelectorAll(EDITABLE_SELECTOR).forEach((el) => {
    if (!props.readonly) el.setAttribute('contenteditable', 'true');
    const ph = PLACEHOLDERS[el.getAttribute('data-field')];
    if (ph) el.setAttribute('data-ph', ph);
  });
  const num = root.querySelector('[data-sys="doc-number"]');
  if (num) {
    if (!props.readonly) {
      num.setAttribute('contenteditable', 'true');
      num.setAttribute('data-ph', '.......................');
      num.setAttribute('title', 'ចុចដើម្បីកែលេខឯកសារ');
    }
    if (document.activeElement !== num) {
      num.textContent = extractDocNumberPrefix(currentDocNumber.value) || '.......................';
    }
  }
  const refs = root.querySelector('[data-field="refs"]');
  if (refs) normalizeRefs(refs);
  updateWordCount();
};

const serialize = () => {
  if (!paperEditor.value) return '';
  const clone = paperEditor.value.cloneNode(true);
  clone.querySelectorAll('[contenteditable]').forEach((el) => el.removeAttribute('contenteditable'));
  clone.querySelectorAll('[data-ph]').forEach((el) => el.removeAttribute('data-ph'));
  clone.querySelectorAll('[data-sys="doc-number"]').forEach((el) => { el.textContent = '.......................'; });
  return clone.innerHTML;
};

let lastEmitted = null;
const emitUpdate = () => {
  if (!paperEditor.value) return;
  const html = serialize();
  lastEmitted = html;
  emit('update:modelValue', html);
  emit('change', html);
};

// ---------------------------------------------------------------------------
// Editing events
// ---------------------------------------------------------------------------
const fieldOf = (node) => {
  const el = node && (node.nodeType === 1 ? node : node.parentElement);
  return el ? el.closest(EDITABLE_SELECTOR) : null;
};
const docNumOf = (node) => {
  const el = node && (node.nodeType === 1 ? node : node.parentElement);
  return el ? el.closest('[data-sys="doc-number"]') : null;
};
const isRich = (el) => !!el && el.matches(RICH_SELECTOR);

const onEditorFocusIn = (e) => {
  const numEl = docNumOf(e.target);
  if (numEl && /^\.+$/.test(numEl.textContent.trim())) {
    numEl.textContent = '';
  }
};

const onEditorInput = (e) => {
  const numEl = docNumOf(e?.target);
  if (numEl) {
    const raw = (numEl.textContent || '').replace(/[\r\n]+/g, ' ').trim();
    const clean = /^\.+$/.test(raw) ? '' : raw;
    currentDocNumber.value = clean;
    emit('update:documentNumber', clean);
    scheduleLayout();
    return;
  }
  emitUpdate();
  updateWordCount();
  scheduleLayout();
};

const onEditorKeydown = (e) => {
  const numEl = docNumOf(e.target);
  if (numEl) {
    if ((e.ctrlKey || e.metaKey) && ['b', 'i', 'u'].includes(e.key.toLowerCase())) {
      e.preventDefault();
      return;
    }
    if (e.key === 'Enter') {
      e.preventDefault();
      numEl.blur();
    }
    return;
  }
  const el = fieldOf(e.target);
  if (!el) return;
  const field = el.getAttribute('data-field');
  // Plain fields: no inline formatting shortcuts
  if (!isRich(el) && (e.ctrlKey || e.metaKey) && ['b', 'i', 'u'].includes(e.key.toLowerCase())) {
    e.preventDefault();
    return;
  }
  if (e.key !== 'Enter') return;
  if (field === 'refs') {
    e.preventDefault();
    const sel = window.getSelection();
    const line = sel.anchorNode && (sel.anchorNode.nodeType === 1 ? sel.anchorNode : sel.anchorNode.parentElement)?.closest('.lc-ref');
    const nd = document.createElement('div');
    nd.className = 'lc-ref';
    nd.innerHTML = '<br>';
    if (line && el.contains(line)) line.after(nd); else el.appendChild(nd);
    const r = document.createRange();
    r.setStart(nd, 0);
    r.collapse(true);
    sel.removeAllRanges();
    sel.addRange(r);
    onEditorInput();
  } else if (field && field !== 'recipient' && !isRich(el) && !e.shiftKey) {
    // single-line fields (subject / dates): Enter finishes the field (Shift+Enter = new line)
    e.preventDefault();
    el.blur();
  }
};

const onEditorPaste = (e) => {
  const cd = e.clipboardData || window.clipboardData;
  const numEl = docNumOf(e.target);
  if (numEl) {
    e.preventDefault();
    const text = (cd.getData('text/plain') || '').replace(/\s*\r?\n\s*/g, ' ').trim();
    document.execCommand('insertText', false, text);
    onEditorInput({ target: numEl });
    return;
  }
  const el = fieldOf(e.target);
  if (!el) return;
  e.preventDefault();
  if (isRich(el)) {
    let html = cd.getData('text/html');
    if (!html) {
      html = (cd.getData('text/plain') || '').split(/\r?\n/).map((x) => `<p>${escapeHtml(x)}</p>`).join('');
    }
    document.execCommand('insertHTML', false, cleanBodyHtml(html));
  } else {
    const field = el.getAttribute('data-field');
    const text = (cd.getData('text/plain') || '').replace(/\s*\r?\n\s*/g, (field === 'refs' || field === 'recipient') ? '\n' : ' ');
    document.execCommand('insertText', false, text);
  }
  onEditorInput();
};

const onEditorFocusOut = (e) => {
  const numEl = docNumOf(e.target);
  if (numEl) {
    const raw = (numEl.textContent || '').replace(/[\r\n]+/g, ' ').trim();
    const clean = /^\.+$/.test(raw) ? '' : raw;
    currentDocNumber.value = clean;
    emit('update:documentNumber', clean);
    if (!clean) numEl.textContent = '.......................';
    return;
  }
  const el = fieldOf(e.target);
  if (!el) return;
  if (el.getAttribute('data-field') === 'refs') normalizeRefs(el);
  emitUpdate();
};

const onEditorClick = (e) => {
  if (props.readonly || fieldOf(e.target) || docNumOf(e.target)) return;
  if (e.target.closest('.draggable-opinion-box')) return;
  if (e.target.closest('[data-region="header"], [data-region="signature"]')) {
    lockedHint();
  }
};

let lockedHintAt = 0;
const lockedHint = () => {
  if (Date.now() - lockedHintAt < 4000) return;
  lockedHintAt = Date.now();
  Swal.fire({
    toast: true, position: 'bottom', icon: 'info', timer: 2200, showConfirmButton: false,
    title: 'ផ្នែកនេះប្រព័ន្ធគ្រប់គ្រង (ក្បាលលិខិត លេខ ហត្ថលេខា)',
  });
};

// Each reference is one <div class="lc-ref"> line; stray text is wrapped.
const normalizeRefs = (el) => {
  Array.from(el.childNodes).forEach((n) => {
    if (n.nodeType === 3 && n.nodeValue.trim()) {
      const d = document.createElement('div');
      d.className = 'lc-ref';
      d.textContent = n.nodeValue.trim();
      n.replaceWith(d);
    } else if (n.nodeType === 1 && !n.classList.contains('lc-ref')) {
      if (n.tagName === 'BR') { n.remove(); return; }
      n.classList.add('lc-ref');
    }
  });
  if (!el.querySelector('.lc-ref')) el.innerHTML = '<div class="lc-ref"><br></div>';
};

// Formatting toolbar (rich regions, plus Moul/undo/redo/removeFormat in subject & refs)
const fmt = (cmd) => {
  if (props.readonly) return;
  const sel = window.getSelection();
  const el = sel && sel.rangeCount ? fieldOf(sel.anchorNode) : null;
  const field = el ? el.getAttribute('data-field') : null;
  const isInlineTextField = field === 'subject' || field === 'refs';
  const allowInSimpleField = isInlineTextField && (cmd === 'moul' || cmd === 'undo' || cmd === 'redo' || cmd === 'removeFormat');
  if (!el || !paperEditor.value.contains(el) || (!isRich(el) && !allowInSimpleField)) {
    Swal.fire({ toast: true, position: 'bottom', icon: 'info', timer: 2000, showConfirmButton: false, title: 'សូមចុចក្នុង «ខ្លឹមសារ» ឬ «កម្មវត្ថុ» ជាមុនសិន' });
    return;
  }
  if (cmd === 'moul') toggleMoul(el);
  else if (cmd === 'table') document.execCommand('insertHTML', false, '<table><tr><td>ចំណងជើង ១</td><td>ចំណងជើង ២</td><td>ចំណងជើង ៣</td></tr><tr><td>…</td><td>…</td><td>…</td></tr></table><p><br></p>');
  else document.execCommand(cmd, false, null);
  onEditorInput();
};

const toggleMoul = (scope) => {
  const sel = window.getSelection();
  const a = sel.anchorNode && (sel.anchorNode.nodeType === 1 ? sel.anchorNode : sel.anchorNode.parentElement);
  const m = a && a.closest('.moul');
  if (m && scope.contains(m)) {
    const f = document.createDocumentFragment();
    while (m.firstChild) f.appendChild(m.firstChild);
    m.replaceWith(f);
    return;
  }
  if (sel.isCollapsed) return;
  document.execCommand('styleWithCSS', false, false);
  document.execCommand('fontName', false, '__moul__');
  scope.querySelectorAll('font[face="__moul__"]').forEach((fn) => {
    const s = document.createElement('span');
    s.className = 'moul';
    while (fn.firstChild) s.appendChild(fn.firstChild);
    fn.replaceWith(s);
  });
};

const updateWordCount = () => {
  const body = paperEditor.value?.querySelector('[data-region="body"]');
  const text = (body?.innerText || '').trim();
  if (!text) { wordCount.value = 0; return; }
  try {
    const seg = new Intl.Segmenter('km', { granularity: 'word' });
    let n = 0;
    for (const s of seg.segment(text)) if (s.isWordLike) n++;
    wordCount.value = n;
  } catch {
    wordCount.value = text.split(/\s+/).length;
  }
};

// ---------------------------------------------------------------------------
// Layout: page-break guides (write mode) and real pages (view / print)
// ---------------------------------------------------------------------------
const outerH = (el) => {
  const cs = getComputedStyle(el);
  return el.offsetHeight + parseFloat(cs.marginTop || 0) + parseFloat(cs.marginBottom || 0);
};

const footerHeight = () => {
  if (!props.footerHtml) return 0;
  const m = document.createElement('div');
  m.className = 'lc-doc lc-measure';
  m.style.width = (PAGE_W - 68) + 'px';
  m.innerHTML = `<div class="lc-page-footer-inner">${props.footerHtml}</div>`;
  document.body.appendChild(m);
  const h = Math.ceil(m.getBoundingClientRect().height);
  m.remove();
  return h;
};

const updateGuides = () => {
  const root = paperEditor.value;
  if (!root || mode.value !== 'write') return;
  sheetHeight.value = root.offsetHeight * scale.value;
  const { header, flow, keep } = collectBlocks(root);
  const headerH = header ? outerH(header) : 0;
  const measureItem = (b) => {
    if (b.kind === 'li') {
      const lis = b.list.children;
      const extra = b.index === lis.length - 1 ? parseFloat(getComputedStyle(b.list).marginBottom || 0) : 0;
      return { el: b.el, h: b.el.offsetHeight + extra };
    }
    return { el: b.el, h: outerH(b.el) };
  };
  const r = paginate(flow.map(measureItem), keep.map(measureItem), headerH, footerHeight());
  const top0 = root.getBoundingClientRect().top;
  const s = scale.value || 1;
  guides.value = r.pages.slice(1).map((items, i) => ({
    page: i + 2,
    top: items[0] ? (items[0].el.getBoundingClientRect().top - top0) / s - 6 : 0,
  }));
  pageCount.value = r.pages.length;
};

const renderPages = (forPrint = false) => {
  const html = renderPagesHtml(serialize(), {
    documentNumber: currentDocNumber.value,
    footerHtml: props.footerHtml,
    scale: forPrint ? 1 : scale.value,
  });
  if (!forPrint && pagesEl.value) {
    pagesEl.value.innerHTML = html.html;
    pageCount.value = html.count;
  }
  return html;
};

let layoutTimer = null;
const scheduleLayout = () => {
  clearTimeout(layoutTimer);
  layoutTimer = setTimeout(refreshLayout, 150);
};
const refreshLayout = () => {
  updateScale();
  if (mode.value === 'write') updateGuides();
  else renderPages();
};
const updateScale = () => {
  const w = canvasContainer.value?.clientWidth || PAGE_W;
  scale.value = Math.min(1, Math.max(0.3, (w - 32) / PAGE_W));
};

const setMode = (m) => {
  if (m === mode.value) return;
  if (mode.value === 'write') emitUpdate();
  mode.value = m;
  nextTick(refreshLayout);
};

// ---------------------------------------------------------------------------
// Print / export (same page renderer as the view mode → what you see is what prints)
// ---------------------------------------------------------------------------
const printDocument = () => {
  if (mode.value === 'write') emitUpdate();
  const { html } = renderPages(true);
  openPrintPagesWindow(html, 'បោះពុម្ពឯកសារ');
};

const exportToWord = () => {
  if (mode.value === 'write') emitUpdate();
  const { html } = renderPages(true);
  const full = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
    <head><meta charset="utf-8"><title>Document</title><style>${DOC_CSS}</style></head><body>${html}</body></html>`;
  const blob = new Blob(['﻿' + full], { type: 'application/msword;charset=utf-8' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `Document_${new Date().toISOString().split('T')[0]}.doc`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
};

const toggleFullscreen = () => {
  isFullscreen.value = !isFullscreen.value;
  nextTick(refreshLayout);
};

// ---------------------------------------------------------------------------
// Sync with v-model: only external changes replace the DOM (no caret jumps while typing)
// ---------------------------------------------------------------------------
watch(
  () => props.modelValue,
  (newVal) => {
    if (!paperEditor.value || newVal === lastEmitted) return;
    if (newVal && newVal.includes(DOC_VERSION_MARK)) {
      paperEditor.value.innerHTML = newVal;
      hydrate();
      lastEmitted = newVal;
      scheduleLayout();
    }
  }
);
watch(() => props.documentNumber, (newNum) => {
  const clean = newNum || '';
  if (clean !== currentDocNumber.value) {
    currentDocNumber.value = clean;
  }
  hydrate();
  scheduleLayout();
});
watch(() => props.readonly, () => { hydrate(); scheduleLayout(); });
watch(() => props.footerHtml, scheduleLayout);

let cleanupDraggable = null;
let resizeObserver = null;
let mutationObserver = null;
const onWindowResize = () => scheduleLayout();

onMounted(() => {
  let st = document.getElementById('lc-doc-css');
  if (!st) {
    st = document.createElement('style');
    st.id = 'lc-doc-css';
    document.head.appendChild(st);
  }
  st.textContent = DOC_CSS;
  nextTick(() => {
    if (!paperEditor.value) return;
    if (props.modelValue && props.modelValue.includes(DOC_VERSION_MARK)) {
      paperEditor.value.innerHTML = props.modelValue;
      lastEmitted = props.modelValue;
      hydrate();
    } else {
      insertTemplate(props.docType || 'REPORT', { confirm: false });
    }
    refreshDraggableBoxes();
    mutationObserver = new MutationObserver(scheduleLayout);
    mutationObserver.observe(paperEditor.value, { childList: true, subtree: true, characterData: true });
    if (typeof ResizeObserver !== 'undefined') {
      resizeObserver = new ResizeObserver(scheduleLayout);
      resizeObserver.observe(paperEditor.value);
      if (canvasContainer.value) resizeObserver.observe(canvasContainer.value);
    }
    window.addEventListener('resize', onWindowResize);
    if (document.fonts) document.fonts.ready.then(scheduleLayout);
    refreshLayout();
  });
});

onUnmounted(() => {
  clearTimeout(layoutTimer);
  if (cleanupDraggable) cleanupDraggable();
  if (resizeObserver) resizeObserver.disconnect();
  if (mutationObserver) mutationObserver.disconnect();
  window.removeEventListener('resize', onWindowResize);
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap');

/* Draggable Leadership Opinion Box & Handles */
:deep(.draggable-opinion-box) {
  position: relative;
  display: inline-block !important;
  vertical-align: top !important;
  box-sizing: border-box !important;
  transition: box-shadow 0.15s ease;
}

:deep(.opinion-boxes-row) {
  display: flex !important;
  flex-wrap: wrap !important;
  justify-content: center !important;
  align-items: flex-start !important;
  gap: 8px 14px !important;
  width: 100% !important;
  margin-top: 8px !important;
  clear: both !important;
  box-sizing: border-box !important;
}

:deep(.draggable-opinion-box.is-locked-box) {
  cursor: default !important;
}

:deep(.draggable-opinion-box.is-locked-box:hover) {
  outline: none !important;
  box-shadow: none !important;
}

:deep(.draggable-opinion-box.is-locked-box .box-handle) {
  display: none !important;
}

:deep(.draggable-opinion-box:hover),
:deep(.draggable-opinion-box.is-selected) {
  outline: 1.5px solid #2563eb !important;
  outline-offset: 1px;
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

:deep(.draggable-opinion-box.is-blocked) {
  outline: 2px dashed #dc2626 !important;
  background-color: rgba(254, 226, 226, 0.35) !important;
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

.word-editor-wrapper {
  display: flex;
  flex-direction: column;
  background-color: #525659;
  border: 1px solid #ced4da;
  border-radius: 6px;
  overflow: hidden;
  font-family: 'Battambang', sans-serif;
}

.fullscreen-mode {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 9999;
  border-radius: 0;
}

.word-ribbon {
  background-color: #f8f9fa;
  border-bottom: 1px solid #d0d7de;
  z-index: 10;
}

.lc-ribbon-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
}

.lc-ribbon-top {
  background: #ffffff;
  border-bottom: 1px solid #e5e7eb;
}

.lc-ribbon-format {
  background: #f8fafc;
  gap: 6px;
  padding: 5px 12px;
}

.lc-ribbon-group {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px;
}

.lc-ribbon-label {
  font-size: 12px;
  font-weight: 700;
  color: #475569;
  white-space: nowrap;
}

.lc-divider {
  width: 1px;
  height: 22px;
  background: #cbd5e1;
  margin: 0 2px;
}

.lc-btn {
  font-size: 12.5px !important;
  padding: 4px 10px !important;
  line-height: 1.35 !important;
  white-space: nowrap;
}

.btn-tool {
  padding: 3px 9px;
  font-size: 12.5px;
  color: #334155;
  line-height: 1.35;
}
.btn-tool:hover {
  background-color: #e2e8f0;
  color: #0f172a;
}

.lc-moul-btn {
  font-family: 'Khmer OS Muol Light', 'Moul', serif !important;
  font-size: 11.5px !important;
}

.lc-format-hint {
  font-size: 11.5px;
  color: #64748b;
  white-space: nowrap;
}

:deep(.drafter-signature-table) {
  table-layout: fixed;
  width: 100%;
  box-sizing: border-box;
}

:deep(.drafter-signature-table td) {
  box-sizing: border-box;
}

.word-statusbar {
  user-select: none;
}
</style>

<style>
/* Composer chrome (not part of the saved document) */
.lc-canvas {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 18px;
  padding: 18px 16px 48px;
  overflow: auto;
  background: #525659;
  min-height: 520px;
  max-height: 720px;
}
.fullscreen-mode .lc-canvas {
  max-height: calc(100vh - 125px);
}
.lc-sheet-wrap { position: relative; flex: none; background: #fff; box-shadow: 0 2px 12px rgba(0, 0, 0, .35); }
.lc-guides { position: absolute; left: 0; top: 0; width: 794px; height: 0; transform-origin: top left; pointer-events: none; }
.lc-guide { position: absolute; left: 0; right: 0; border-top: 2px dashed #2457c5; opacity: .8; }
.lc-guide span { position: absolute; right: 10px; top: -13px; background: #2457c5; color: #fff; font: 12px/1.6 'Battambang', system-ui, sans-serif; padding: 1px 8px; border-radius: 999px; }
.lc-pages { display: flex; flex-direction: column; align-items: center; gap: 18px; }
.lc-pages .box-handle, .lc-pages .opinion-box-actions { display: none !important; }
.lc-pages .draggable-opinion-box { outline: none !important; box-shadow: none !important; cursor: default !important; }
</style>
