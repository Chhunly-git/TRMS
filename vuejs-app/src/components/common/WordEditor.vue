<template>
  <div class="word-editor-wrapper" :class="{ 'fullscreen-mode': isFullscreen }">
    <!-- 1. Top Ribbon / Toolbar -->
    <div class="word-ribbon shadow-xs">
      <!-- Row 1: Actions & Templates -->
      <div class="ribbon-row d-flex flex-wrap align-items-center justify-content-between p-2 border-bottom bg-light">
        <!-- Template Selection Buttons -->
        <div class="d-flex align-items-center flex-wrap mr-2" v-if="!readonly">
          <span class="small font-weight-bold text-muted mr-2">គំរូឯកសាររដ្ឋបាល៖</span>
          <button
            type="button"
            class="btn btn-xs btn-outline-primary mr-1 mb-1 font-weight-bold"
            @click="insertTemplate('REPORT')"
            title="បញ្ចូលទម្រង់របាយការណ៍ផ្លូវការ"
          >
            <i class="fas fa-file-alt mr-1"></i> គំរូ របាយការណ៍
          </button>
          <button
            type="button"
            class="btn btn-xs btn-outline-info mr-1 mb-1 font-weight-bold"
            @click="insertTemplate('SUBMISSION_NOTE')"
            title="បញ្ចូលទម្រង់កំណត់បង្ហាញផ្លូវការ"
          >
            <i class="fas fa-file-signature mr-1"></i> គំរូ កំណត់បង្ហាញ
          </button>
          <button
            type="button"
            class="btn btn-xs btn-outline-success mr-2 mb-1 font-weight-bold"
            @click="insertTemplate('LETTER')"
            title="បញ្ចូលទម្រង់លិខិតរដ្ឋបាលផ្លូវការ"
          >
            <i class="fas fa-envelope-open-text mr-1"></i> គំរូ លិខិត
          </button>

          <!-- Reviewer & Drafter Blocks -->
          <div class="border-left pl-2 ml-1 d-inline-flex align-items-center flex-wrap">
            <template v-if="isApprover">
              <button
                type="button"
                class="btn btn-xs btn-outline-warning mr-1 mb-1 font-weight-bold text-dark shadow-xs"
                @click="openSuperiorReviewModal"
                title="បញ្ចូលកំណត់ត្រាមតិយោបល់ និងហត្ថលេខារបស់ថ្នាក់ដឹកនាំ"
              >
                <i class="fas fa-clipboard-check mr-1 text-warning"></i> + យោបល់ថ្នាក់ដឹកនាំ
              </button>
              <button
                type="button"
                class="btn btn-xs btn-outline-info mr-1 mb-1 font-weight-bold shadow-xs"
                @click="syncWorkflowApprovals"
                v-if="approvals && approvals.length > 0"
                title="ធ្វើបច្ចុប្បន្នភាពមតិយោបល់ថ្នាក់ដឹកនាំពី Workflow ចូលក្នុងតារាង"
              >
                <i class="fas fa-sync-alt mr-1"></i> 🔄 Sync យោបល់ Workflow
              </button>
            </template>
            <button
              type="button"
              class="btn btn-xs btn-outline-dark mr-1 mb-1 font-weight-bold shadow-xs"
              @click="insertDrafterSignature"
              title="បញ្ចូលប្លុកហត្ថលេខាអ្នករៀបចំ"
            >
              <i class="fas fa-signature mr-1"></i> + ហត្ថលេខាអ្នករៀបចំ
            </button>
            <button
              type="button"
              class="btn btn-xs mr-1 mb-1 font-weight-bold shadow-xs"
              :class="officerSignature ? 'btn-outline-success' : 'btn-outline-primary'"
              @click="showSignatureModal = true"
              :title="officerSignature ? 'ហត្ថលេខារបស់អ្នកត្រូវបានភ្ជាប់រួចរាល់ (ចុចដើម្បីកែប្រែ)' : 'គូស ឬបង្ហោះហត្ថលេខារបស់អ្នក'"
            >
              <i class="fas fa-file-signature mr-1"></i>
              <span>{{ officerSignature ? 'ហត្ថលេខារបស់ខ្ញុំ ✓' : '+ គូស/ដាក់ហត្ថលេខា' }}</span>
            </button>
          </div>
        </div>

        <!-- Action / View Buttons -->
        <div class="d-flex align-items-center flex-wrap ml-auto">
          <button
            type="button"
            class="btn btn-xs btn-outline-dark mr-1 mb-1"
            @click="printDocument"
            title="បោះពុម្ពឯកសារ ឬរក្សាទុកជា PDF"
          >
            <i class="fas fa-print mr-1"></i> បោះពុម្ព / Preview PDF
          </button>
          <button
            type="button"
            class="btn btn-xs btn-outline-success mr-1 mb-1"
            @click="exportToWord"
            title="ទាញយកជាឯកសារ Word (.doc)"
          >
            <i class="fas fa-file-word mr-1"></i> Export Word
          </button>
          <button
            type="button"
            class="btn btn-xs btn-outline-secondary mb-1"
            @click="toggleFullscreen"
            :title="isFullscreen ? 'បិទ Fullscreen' : 'បើកពេញអេក្រង់'"
          >
            <i class="fas" :class="isFullscreen ? 'fa-compress' : 'fa-expand'"></i>
          </button>
        </div>
      </div>

      <!-- Row 2: Word Formatting Controls -->
      <div class="ribbon-row d-flex flex-wrap align-items-center p-2 bg-white" v-if="!readonly">
        <!-- Undo / Redo -->
        <div class="btn-group btn-group-sm mr-2 mb-1">
          <button type="button" class="btn btn-light border btn-tool" @click="exec('undo')" title="Undo (Ctrl+Z)">
            <i class="fas fa-undo"></i>
          </button>
          <button type="button" class="btn btn-light border btn-tool" @click="exec('redo')" title="Redo (Ctrl+Y)">
            <i class="fas fa-redo"></i>
          </button>
        </div>

        <!-- Font Family Selector -->
        <div class="mr-2 mb-1">
          <select class="form-control form-control-sm font-select" v-model="selectedFont" @change="applyFont">
            <option value="'Siemreap', sans-serif">Siemreap (អក្សរតូចទូទៅ)</option>
            <option value="'Khmer OS Muol Light', serif">Khmer OS Muol Light (អក្សរធំ)</option>
            <option value="'Khmer OS Battambang', sans-serif">Khmer OS Battambang</option>
            <option value="'DaunPenh', serif">DaunPenh</option>
            <option value="'Times New Roman', serif">Times New Roman</option>
            <option value="'Arial', sans-serif">Arial</option>
          </select>
        </div>

        <!-- Font Size Selector -->
        <div class="mr-2 mb-1">
          <select class="form-control form-control-sm size-select" v-model="selectedSize" @change="applySize">
            <option value="11pt">11 pt</option>
            <option value="12pt">12 pt (ធម្មតា)</option>
            <option value="13pt">13 pt</option>
            <option value="14pt">14 pt</option>
            <option value="16pt">16 pt (ចំណងជើង)</option>
            <option value="18pt">18 pt (ធំ)</option>
            <option value="20pt">20 pt</option>
          </select>
        </div>

        <!-- Basic Styles (B, I, U, S) -->
        <div class="btn-group btn-group-sm mr-2 mb-1">
          <button type="button" class="btn btn-light border btn-tool font-weight-bold" @click="exec('bold')" title="ដិត (Bold - Ctrl+B)">
            <strong>B</strong>
          </button>
          <button type="button" class="btn btn-light border btn-tool font-italic" @click="exec('italic')" title="ទ្រេត (Italic - Ctrl+I)">
            <em>I</em>
          </button>
          <button type="button" class="btn btn-light border btn-tool text-decoration-underline" @click="exec('underline')" title="បន្ទាត់ក្រោម (Underline - Ctrl+U)">
            <u>U</u>
          </button>
          <button type="button" class="btn btn-light border btn-tool" @click="exec('strikeThrough')" title="គូសឆូត (Strikethrough)">
            <s>S</s>
          </button>
        </div>

        <!-- Colors -->
        <div class="d-flex align-items-center mr-2 mb-1">
          <label class="color-picker-label mr-1" title="ពណ៌អក្សរ (Text Color)">
            <i class="fas fa-font text-danger"></i>
            <input type="color" v-model="textColor" @input="applyTextColor" class="d-none" />
          </label>
          <label class="color-picker-label" title="ពណ៌រំលេច (Highlight Color)">
            <i class="fas fa-highlighter text-warning"></i>
            <input type="color" v-model="highlightColor" @input="applyHighlightColor" class="d-none" />
          </label>
        </div>

        <!-- Alignment -->
        <div class="btn-group btn-group-sm mr-2 mb-1">
          <button type="button" class="btn btn-light border btn-tool" @click="exec('justifyLeft')" title="តម្រឹមឆ្វេង">
            <i class="fas fa-align-left"></i>
          </button>
          <button type="button" class="btn btn-light border btn-tool" @click="exec('justifyCenter')" title="តម្រឹមកណ្តាល">
            <i class="fas fa-align-center"></i>
          </button>
          <button type="button" class="btn btn-light border btn-tool" @click="exec('justifyRight')" title="តម្រឹមស្តាំ">
            <i class="fas fa-align-right"></i>
          </button>
          <button type="button" class="btn btn-light border btn-tool" @click="exec('justifyFull')" title="តម្រឹមស្មើសងខាង">
            <i class="fas fa-align-justify"></i>
          </button>
        </div>

        <!-- Lists & Indent -->
        <div class="btn-group btn-group-sm mr-2 mb-1">
          <button type="button" class="btn btn-light border btn-tool" @click="exec('insertUnorderedList')" title="ចំណុចពងក្រពើ (Bullets)">
            <i class="fas fa-list-ul"></i>
          </button>
          <button type="button" class="btn btn-light border btn-tool" @click="exec('insertOrderedList')" title="លេខរៀង (Numbering)">
            <i class="fas fa-list-ol"></i>
          </button>
          <button type="button" class="btn btn-light border btn-tool" @click="exec('outdent')" title="បន្ថយគម្លាតចូលបន្ទាត់">
            <i class="fas fa-outdent"></i>
          </button>
          <button type="button" class="btn btn-light border btn-tool" @click="exec('indent')" title="បន្ថែមគម្លាតចូលបន្ទាត់">
            <i class="fas fa-indent"></i>
          </button>
        </div>

        <!-- Insert Table & Elements -->
        <div class="btn-group btn-group-sm mb-1 mr-2">
          <button type="button" class="btn btn-light border btn-tool" @click="promptInsertTable" title="បញ្ចូលតារាង (Insert Table)">
            <i class="fas fa-table mr-1 text-primary"></i> តារាង
          </button>
          <button type="button" class="btn btn-light border btn-tool" @click="exec('insertHorizontalRule')" title="បន្ទាត់ផ្តេក">
            <i class="fas fa-minus"></i>
          </button>
        </div>

        <!-- Page Break Controls -->
        <div class="btn-group btn-group-sm mb-1">
          <button
            type="button"
            class="btn btn-light border btn-tool text-primary font-weight-bold"
            @click="insertManualPageBreak"
            title="ផ្ដាច់ទំព័រ (Insert Page Break)"
          >
            <i class="fas fa-file-invoice mr-1 text-primary"></i> ផ្ដាច់ទំព័រ
          </button>
          <button
            type="button"
            v-if="hasPageBreak"
            class="btn btn-light border btn-tool text-danger font-weight-bold"
            @click="removePageBreaks"
            title="លុបការផ្ដាច់ទំព័រ និងរួមបញ្ចូលមកទំព័រទី១វិញ (Remove Page Break)"
          >
            <i class="fas fa-trash-alt mr-1 text-danger"></i> លុបការផ្ដាច់ទំព័រ
          </button>
        </div>
      </div>
    </div>

    <!-- 2. Document Canvas Area (A4 Paper Feel) -->
    <div class="document-canvas-container" ref="canvasContainer">
      <div
        class="document-paper shadow-lg"
        ref="paperEditor"
        :style="paperStyle"
        :contenteditable="!readonly"
        @input="onEditorInput"
        @blur="onEditorBlur"
        spellcheck="false"
      ></div>
    </div>

    <!-- 3. Bottom Status Bar -->
    <div class="word-statusbar bg-light p-1 px-3 border-top d-flex justify-content-between align-items-center text-muted small">
      <div>
        <span class="mr-3"><i class="fas fa-file-word text-primary mr-1"></i> កម្មវិធីរៀបចំឯកសារ (Word-like Editor)</span>
        <span class="mr-3">ពាក្យសរុប៖ <strong>{{ wordCount }}</strong></span>
        <span>ទំព័រប៉ាន់ប្រមាណ៖ <strong>{{ estimatedPages }}</strong> ទំព័រ</span>
      </div>
      <div>
        <span v-if="readonly" class="badge badge-secondary">មើលតែប៉ុណ្ណោះ (Read-Only)</span>
        <span v-else class="text-success"><i class="fas fa-check-circle mr-1"></i> អាចកែសម្រួលបាន</span>
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

              <!-- 3. Date & Placement Row -->
              <div class="row mb-3">
                <div class="col-md-6 mb-2 mb-md-0">
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
                <div class="col-md-6">
                  <label class="font-weight-bold text-dark small mb-1">
                    <i class="fas fa-map-marker-alt text-danger mr-1"></i> ជ្រើសរើសទីតាំងដាក់លើរបាយការណ៍ <span class="text-danger">*</span>
                  </label>
                  <select class="form-control font-weight-bold" v-model="superiorForm.placement">
                    <option value="SIDE_BY_SIDE_LEFT">ទន្ទឹមហត្ថលេខាអ្នករៀបចំ (ខាងឆ្វេង) - ស្តង់ដារ</option>
                    <option value="ABOVE_DRAFTER">ខាងលើហត្ថលេខាអ្នករៀបចំ</option>
                    <option value="BELOW_DRAFTER">ខាងក្រោមហត្ថលេខាអ្នករៀបចំ</option>
                    <option value="BOTTOM">ខាងក្រោមបង្អស់នៃរបាយការណ៍ (ចុងបញ្ចប់)</option>
                    <option value="CURSOR">ត្រង់ទីតាំង Cursor ក្នុងអត្ថបទ</option>
                  </select>
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
  setupDraggableOpinionBoxes,
} from '@/utils/opinionBoxHelper';

const userStore = useUserStore();

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  docType: {
    type: String,
    default: 'REPORT' // 'REPORT', 'SUBMISSION_NOTE', 'BLANK'
  },
  readonly: {
    type: Boolean,
    default: false
  },
  isApprover: {
    type: Boolean,
    default: false
  },
  organizationName: {
    type: String,
    default: 'និយ័តករបរធនបាលកិច្ច'
  },
  departmentName: {
    type: String,
    default: 'នាយកដ្ឋាន...'
  },
  officeName: {
    type: String,
    default: ''
  },
  officerName: {
    type: String,
    default: ''
  },
  officerPosition: {
    type: String,
    default: 'អ្នកធ្វើរបាយការណ៍'
  },
  officerSignature: {
    type: String,
    default: ''
  },
  approvals: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue', 'save-draft', 'change', 'signature-updated']);

const paperEditor = ref(null);
const canvasContainer = ref(null);
const isFullscreen = ref(false);
const showSignatureModal = ref(false);

const selectedFont = ref("'Siemreap', sans-serif");
const selectedSize = ref("12pt");
const textColor = ref("#000000");
const highlightColor = ref("#ffff00");

// Khmer numbers
const khmerDigits = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];
const toKhmerNum = (num) => String(num).split('').map(d => khmerDigits[d] || d).join('');

const khmerMonths = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'];

const getCurrentKhmerDateString = () => {
  const d = new Date();
  const day = toKhmerNum(d.getDate());
  const month = khmerMonths[d.getMonth()];
  const year = toKhmerNum(d.getFullYear());
  return `រាជធានីភ្នំពេញ ថ្ងៃទី${day} ខែ${month} ឆ្នាំ${year}`;
};

// Dual Calendar: Buddhist Era (ពុទ្ធសករាជ) & Gregorian Era (គ្រឹះសករាជ)
const getOfficialKhmerDateHtml = (input = new Date()) => {
  const d = input instanceof Date ? input : new Date(input);
  const day = toKhmerNum(d.getDate());
  const month = khmerMonths[d.getMonth()] || 'វិច្ឆិកា';
  const year = toKhmerNum(d.getFullYear());

  return `
    <div style="font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 10pt; color: #222; margin-bottom: 2px; outline: none; white-space: nowrap;" contenteditable="true" title="កាលបរិច្ឆេទពុទ្ធសករាជ (ចុចដើម្បីកែ)">ថ្ងៃ................... ខែ............. ឆ្នាំ........ ព.ស.២៥......</div>
    <div style="font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 10.5pt; color: #111; margin-bottom: 4px; outline: none; white-space: nowrap;" contenteditable="true" title="កាលបរិច្ឆេទគ្រឹះសករាជ (ចុចដើម្បីកែ)">រាជធានីភ្នំពេញ ថ្ងៃទី${day} ខែ${month} ឆ្នាំ${year}</div>
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

// Word & Page counter
const wordCount = computed(() => {
  if (!paperEditor.value) return 0;
  const text = paperEditor.value.innerText || '';
  const clean = text.trim();
  return clean ? clean.split(/\s+/).length : 0;
});

const estimatedPages = computed(() => {
  if (!paperEditor.value) return 1;
  const pageBreaks = paperEditor.value.querySelectorAll('.page-break');
  const breaksCount = pageBreaks ? pageBreaks.length : 0;
  const heightPages = Math.ceil((paperEditor.value.scrollHeight || 1080) / 1080);
  return Math.max(breaksCount + 1, heightPages, 1);
});

// Execute format command
const exec = (command, value = null) => {
  if (props.readonly) return;
  paperEditor.value?.focus();
  document.execCommand(command, false, value);
  emitUpdate();
};

const applyFont = () => {
  exec('fontName', selectedFont.value);
};

const applySize = () => {
  const selection = window.getSelection();
  if (selection.rangeCount > 0 && !selection.isCollapsed) {
    const range = selection.getRangeAt(0);
    const span = document.createElement('span');
    span.style.fontSize = selectedSize.value;
    span.appendChild(range.extractContents());
    range.insertNode(span);
    emitUpdate();
  }
};

const applyTextColor = () => {
  exec('foreColor', textColor.value);
};

const applyHighlightColor = () => {
  exec('hiliteColor', highlightColor.value);
};

// Insert Page Break (Multi-page without clipped text)
const insertPageBreak = () => {
  if (props.readonly || !paperEditor.value) return;

  const currentBreaks = paperEditor.value.querySelectorAll('.page-break');
  const nextPageNum = (currentBreaks ? currentBreaks.length : 0) + 2;
  const khmerPageNum = toKhmerNum(nextPageNum);

  const pageBreakHtml = createPageBreakHtml(nextPageNum) + `<p style="font-size: 12pt; line-height: 2.1; text-indent: 95px;"><br></p>`;

  paperEditor.value.focus();
  document.execCommand('insertHTML', false, pageBreakHtml);
  emitUpdate();

  Swal.fire({
    icon: 'success',
    title: `បានបន្ថែមទំព័រទី ${khmerPageNum}!`,
    timer: 1300,
    showConfirmButton: false,
  });
};

// Insert Table helper
const promptInsertTable = async () => {
  const { value: formValues } = await Swal.fire({
    title: 'បញ្ចូលតារាងថ្មី (Insert Table)',
    html:
      '<div class="form-group text-left mb-2">' +
      '<label class="small font-weight-bold">ចំនួនជួរដេក (Rows):</label>' +
      '<input id="swal-table-rows" type="number" class="form-control form-control-sm" value="3" min="1" max="50">' +
      '</div>' +
      '<div class="form-group text-left mb-0">' +
      '<label class="small font-weight-bold">ចំនួនជួរឈរ (Columns):</label>' +
      '<input id="swal-table-cols" type="number" class="form-control form-control-sm" value="3" min="1" max="10">' +
      '</div>',
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'បញ្ចូលតារាង',
    cancelButtonText: 'បោះបង់',
    preConfirm: () => {
      const rows = parseInt(document.getElementById('swal-table-rows').value, 10);
      const cols = parseInt(document.getElementById('swal-table-cols').value, 10);
      if (!rows || rows < 1 || !cols || cols < 1) {
        Swal.showValidationMessage('សូមបញ្ចូលចំនួនជួរដេក និងជួរឈរឱ្យបានត្រឹមត្រូវ!');
        return false;
      }
      return { rows, cols };
    }
  });

  if (formValues) {
    insertTableHtml(formValues.rows, formValues.cols);
  }
};

const insertTableHtml = (rows, cols) => {
  let tableHtml = '<table style="width: 100%; border-collapse: collapse; margin: 15px 0; border: 1px solid #333; line-height: 2.1;">';
  tableHtml += '<thead><tr style="background-color: #f2f2f2;">';
  for (let c = 1; c <= cols; c++) {
    tableHtml += `<th style="border: 1px solid #444; padding: 8px; text-align: center; font-weight: bold;">ចំណងជើង ${c}</th>`;
  }
  tableHtml += '</tr></thead><tbody>';

  for (let r = 1; r < rows; r++) {
    tableHtml += '<tr>';
    for (let c = 1; c <= cols; c++) {
      tableHtml += `<td style="border: 1px solid #444; padding: 8px;">ទិន្នន័យ...</td>`;
    }
    tableHtml += '</tr>';
  }
  tableHtml += '</tbody></table><p><br></p>';

  paperEditor.value?.focus();
  document.execCommand('insertHTML', false, tableHtml);
  emitUpdate();
};

// Official Template & Signature Generators
const getDrafterSignatureHtml = () => {
  const dept = props.departmentName || 'នាយកដ្ឋាន...';
  const office = props.officeName ? props.officeName : '';
  const officer = props.officerName || '.....................................';
  const pos = props.officerPosition || 'អ្នកធ្វើរបាយការណ៍';
  const signatureUrl = props.officerSignature || '';
  const dateStr = getCurrentKhmerDateString();

  const signatureImgHtml = signatureUrl
    ? `<div style="text-align: center; margin: 4px 0 2px 0;">
         <img src="${signatureUrl}" alt="ហត្ថលេខា" style="height: 60px; max-width: 190px; object-fit: contain; display: inline-block;" />
       </div>`
    : `<div class="digital-signature-placeholder" style="text-align: center; margin: 4px 0 2px 0; min-height: 52px; display: flex; align-items: center; justify-content: center;">
         <span style="font-family: 'Brush Script MT', 'Segoe Script', cursive, serif; font-size: 20pt; color: #002060; font-weight: bold; border-bottom: 1.5px dashed #002060; padding: 2px 20px; letter-spacing: 1px; display: inline-block;">
           ${officer}
         </span>
       </div>`;

  return `
    <table class="drafter-signature-table" style="width: 100%; border: none; border-collapse: collapse; margin-top: 30px; margin-bottom: 25px; page-break-inside: avoid; break-inside: avoid; line-height: 2.1;">
      <tr>
        <td class="opinion-left-col" style="width: 42%; border: none; vertical-align: top; text-align: center; padding-right: 10px;"></td>
        <td style="width: 58%; border: none; vertical-align: top; text-align: center; font-size: 11pt; line-height: 2.1; padding-left: 10px;">
          ${getOfficialKhmerDateHtml()}
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt; color: #000; margin-top: 2px; white-space: nowrap;">
            ${dept}
          </div>
          ${office ? `<div style="font-family: 'Khmer OS Muol Light', serif; font-size: 10.5pt; color: #000; white-space: nowrap;">${office}</div>` : ''}
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt; color: #000; margin-top: 4px; white-space: nowrap;">
            ${pos}
          </div>
          ${signatureImgHtml}
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; color: #000; font-weight: bold; white-space: nowrap;">
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
  placement: 'SIDE_BY_SIDE_LEFT', // 'SIDE_BY_SIDE_LEFT', 'ABOVE_DRAFTER', 'BELOW_DRAFTER', 'BOTTOM', 'CURSOR'
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
  superiorForm.placement = 'SIDE_BY_SIDE_LEFT';
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

  // Rule: 1 Person can ONLY have ONE opinion box in the report!
  // If user already has one, update it in-place instead of creating a duplicate
  const existingBox = paperEditor.value.querySelector(`.draggable-opinion-box[data-owner-id="${userStore.id}"]`);
  if (existingBox) {
    existingBox.outerHTML = boxHtml;
    emitUpdate();
    showSuperiorReviewModal.value = false;
    Swal.fire({
      icon: 'success',
      title: 'បានធ្វើបច្ចុប្បន្នភាព!',
      text: 'លោកអ្នកមានប្រអប់មតិយោបល់រួចហើយ ប្រព័ន្ធបានធ្វើបច្ចុប្បន្នភាពលើប្រអប់របស់លោកអ្នកដោយមិនបង្កើតប្រអប់ស្ទួនឡើយ។',
      timer: 2000,
      showConfirmButton: false,
    });
    return;
  }

  const drafterTable = paperEditor.value.querySelector('.drafter-signature-table');

  if (superiorForm.placement === 'SIDE_BY_SIDE_LEFT' && drafterTable) {
    let leftCol = drafterTable.querySelector('.opinion-left-col') || drafterTable.querySelector('td:first-child');
    if (leftCol) {
      if (!leftCol.innerHTML.trim()) {
        leftCol.innerHTML = boxHtml;
      } else {
        leftCol.insertAdjacentHTML('beforeend', boxHtml);
      }
    } else {
      drafterTable.insertAdjacentHTML('beforebegin', boxHtml);
    }
  } else if (superiorForm.placement === 'CURSOR' && savedRange.value) {
    const sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(savedRange.value);
    document.execCommand('insertHTML', false, boxHtml);
  } else if (superiorForm.placement === 'ABOVE_DRAFTER') {
    if (drafterTable) {
      drafterTable.insertAdjacentHTML('beforebegin', boxHtml);
    } else {
      paperEditor.value.insertAdjacentHTML('beforeend', boxHtml);
    }
  } else if (superiorForm.placement === 'BELOW_DRAFTER') {
    if (drafterTable) {
      drafterTable.insertAdjacentHTML('afterend', boxHtml);
    } else {
      paperEditor.value.insertAdjacentHTML('beforeend', boxHtml);
    }
  } else {
    // Default 'BOTTOM'
    paperEditor.value.insertAdjacentHTML('beforeend', boxHtml);
  }

  emitUpdate();
  showSuperiorReviewModal.value = false;

  Swal.fire({
    icon: 'success',
    title: 'បានបញ្ចូលយោបល់ និងហត្ថលេខា!',
    text: 'មតិយោបល់ និងហត្ថលេខារបស់ថ្នាក់ដឹកនាំត្រូវបានបញ្ចូលទៅក្នុងរបាយការណ៍ដោយជោគជ័យ។ លោកអ្នកអាចចុចអូសរំកិលទីតាំងបានតាមតម្រូវការ!',
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
    const dateKh = formatAppDate(app);
    const reviewerName = app.user?.name_kh || app.user?.name || '';
    const reviewerPos = app.user?.position?.title_kh || app.stage_kh || 'ថ្នាក់ដឹកនាំ';
    const isDg = (app.action === 'DG_APPROVE' || app.stage === 'DG');
    const reviewerSig = app.user?.signature_image || '';
    const reviewerSigHtml = reviewerSig
      ? `<div style="text-align: right; margin: 4px 0 2px auto;">
           <img src="${reviewerSig}" alt="ហត្ថលេខា" style="height: 55px; max-width: 160px; object-fit: contain; display: inline-block;" />
         </div>`
      : `<div style="height: 35px;"></div>`;

    const title = isDg
      ? `${numKh}. ចំណារ និងការសម្រេចរបស់ឯកឧត្តមអគ្គនាយក`
      : `${numKh}. យោបល់របស់${reviewerPos}`;

    const badge = isDg
      ? `<span style="display:inline-block; font-size:8.5pt; color:#1e40af; background:#dbeafe; padding:1px 8px; border-radius:4px; font-weight:bold;">✓ បានសម្រេច និងឯកភាព</span>`
      : `<span style="display:inline-block; font-size:8.5pt; color:#16a34a; background:#dcfce7; padding:1px 8px; border-radius:4px; font-weight:bold;">✓ បានពិនិត្យ និងចារឆ្លង</span>`;

    const comment = app.comment
      ? `<span style="color:#0f172a; font-weight:500;">${app.comment}</span>`
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
      drafterTable.outerHTML = getDrafterSignatureHtml();
      emitUpdate();
    }
  }
};

const insertReviewerBlocks = () => {
  if (props.readonly || !paperEditor.value) return;
  const html = getReviewBlockHtml(props.approvals);
  if (!html) {
    Swal.fire({
      icon: 'info',
      title: 'ពុំទាន់មានមតិយោបល់',
      text: 'របាយការណ៍នេះមិនទាន់មានមតិយោបល់របស់ថ្នាក់ដឹកនាំនៅឡើយទេ។ មតិយោបល់ និងហត្ថលេខានឹងបង្ហាញដោយស្វ័យប្រវត្តិតាមការចារឆ្លងជាក់ស្តែង!',
      confirmButtonText: 'យល់ព្រម'
    });
    return;
  }
  paperEditor.value.focus();
  document.execCommand('insertHTML', false, html);
  emitUpdate();
  Swal.fire({
    icon: 'success',
    title: 'បានបញ្ចូលកំណត់ត្រាមតិយោបល់ថ្នាក់ដឹកនាំ!',
    timer: 1500,
    showConfirmButton: false,
  });
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

const insertDrafterSignature = () => {
  if (props.readonly || !paperEditor.value) return;
  paperEditor.value.focus();
  document.execCommand('insertHTML', false, getDrafterSignatureHtml());
  emitUpdate();
  Swal.fire({
    icon: 'success',
    title: 'បានបញ្ចូលប្លុកហត្ថលេខាអ្នករៀបចំ!',
    timer: 1500,
    showConfirmButton: false,
  });
};

// Official Template Generators (No LetterHead images, pristine administrative text format)
const getOfficialTemplates = () => {
  const dept = props.departmentName || 'នាយកដ្ឋាន...';
  const office = props.officeName ? props.officeName : '';
  const officer = props.officerName || '.....................................';
  const pos = props.officerPosition || 'អ្នកធ្វើរបាយការណ៍';
  const deptAbbr = getDepartmentAbbr(dept);

  // Standard official administrative text header
  const textHeaderHtml = `
    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 25px; font-size: 11pt; line-height: 2.1;">
      <tr>
        <td style="width: 50%; border: none; vertical-align: top; text-align: left;">
          <img src="logo.png" alt="Logo" style="width: 120px; height: auto; margin-bottom: 4px; padding-left: 40px">
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt; color: #008000;">${props.organizationName || 'និយ័តករបរធនបាលកិច្ច'}</div>
          
          <div style="font-size: 11pt; color: #000; margin-top: 3px;">លេខ៖ <span class="doc-number-input" contenteditable="true" style="display: inline-block; min-width: 45px; border-bottom: 1px dotted #888; outline: none; padding: 0 4px;" title="ចុចដើម្បីវាយលេខចូល">.......</span> ន.ប.ធ./${deptAbbr}</div>
        </td>
        <td style="width: 50%; border: none; vertical-align: top; text-align: center;">
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; color: #008000; margin-bottom: 4px;">ព្រះរាជាណាចក្រកម្ពុជា</div>
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; color: #008000;">ជាតិ សាសនា ព្រះមហាក្សត្រ</div>
          <div style="font-size: 11pt; letter-spacing: 3px;">* * *</div>
        </td>
      </tr>
    </table>
  `;

  return {
    REPORT: `
      <div class="document-page page-1" style="position: relative; line-height: 2.1; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 12pt; color: #111;">
        <!-- Header Text (No Letterhead Image) -->
        ${textHeaderHtml}

        <!-- Recipient Block -->
        <div style="text-align: center; margin-bottom: 20px; padding-right: 25px;">
          <div style="display: inline-block; text-align: center;">
            <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; color: #000; margin-bottom: 4px;">
              សូមគោរពជូន
            </div>
            <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; color: #000;">
              ឯកឧត្តមអគ្គនាយកនៃនិយ័តករបរធនបាលកិច្ច
            </div>
          </div>
        </div>

        <!-- Subject & Reference Table -->
        <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 18px; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 12pt; line-height: 2.1;">
          <tr>
            <td style="width: 95px; vertical-align: top; border: none; padding: 2px 0; white-space: nowrap; font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; color: #000;">កម្មវត្ថុ ៖</td>
            <td style="vertical-align: top; border: none; padding: 2px 0; text-align: justify; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 12pt;">
              របាយការណ៍ស្តីពី...
            </td>
          </tr>
          <tr>
            <td style="vertical-align: top; border: none; padding: 2px 0; font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; color: #000; white-space: nowrap;">យោង ៖</td>
            <td style="vertical-align: top; border: none; padding: 2px 0; text-align: justify; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 12pt;">
              <div>- លិខិតលេខ...</div>
              <div>- ចំណារ<span style="font-family: 'Khmer OS Muol Light', serif;">ឯកឧត្តមអគ្គនាយក</span>...</div>
            </td>
          </tr>
        </table>

        <!-- Body Paragraph 1 -->
        <div style="font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 12pt; text-align: justify; text-indent: 95px; margin-bottom: 18px; line-height: 2.1;">
          សេចក្ដីដូចមានចែងក្នុងកម្មវត្ថុ និងយោងខាងលើ ខ្ញុំសូមគោរពជម្រាបជូន <span style="font-family: 'Khmer OS Muol Light', serif;">ឯកឧត្តមអគ្គនាយក</span> មេត្តាជ្រាបដ៏ខ្ពង់ខ្ពស់ថា៖ (សូមសរសេររៀបរាប់ខ្លឹមសារលម្អិតនៅទីនេះ...)។
        </div>

        <!-- Closing and Signature Block (kept together on page break) -->
        <div class="closing-and-signature-block" style="page-break-inside: avoid; break-inside: avoid;">
          <!-- Body Paragraph 2 (Closing polite sentence 1) -->
          <div class="closing-phrase-1" style="font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 12pt; text-align: justify; text-indent: 95px; margin-bottom: 18px; line-height: 2.1;">
            សេចក្ដីដូចបានគោរពជម្រាបជូនខាងលើ សូម <span style="font-family: 'Khmer OS Muol Light', serif;">ឯកឧត្តមអគ្គនាយក</span> មេត្តាពិនិត្យ ព្រមទាំងផ្តល់ការណែនាំបន្ថែមដោយមេត្តាអនុគ្រោះដ៏ខ្ពង់ខ្ពស់។
          </div>

          <!-- Body Paragraph 3 (Closing polite sentence 2) -->
          <div class="closing-phrase-2" style="font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 12pt; text-align: justify; text-indent: 95px; margin-bottom: 25px; line-height: 2.1;">
            សូម <span style="font-family: 'Khmer OS Muol Light', serif;">ឯកឧត្តមអគ្គនាយក</span> មេត្តាទទួលនូវការគោរពដ៏ខ្ពង់ខ្ពស់ពីខ្ញុំ។
          </div>

          <!-- 1. Drafter Signature Block -->
          ${getDrafterSignatureHtml()}
        </div>
      </div>
    `,
    SUBMISSION_NOTE: `
      <div class="document-page page-1" style="position: relative; line-height: 2.1; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 12pt; color: #111;">
        <!-- Header Text -->
        ${textHeaderHtml}

        <!-- Document Title -->
        <div style="text-align: center; margin: 20px 0;">
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 15pt; color: #000; margin-bottom: 8px;">
            កំណត់បង្ហាញ
          </div>
        </div>

        <!-- Subject & Reference -->
        <div style="font-size: 12pt; margin-bottom: 20px; line-height: 2.1; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif;">
          <table style="width: 100%; border: none; border-collapse: collapse; line-height: 2.1; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif;">
            <tr>
              <td style="width: 95px; vertical-align: top; font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; border: none; white-space: nowrap;">កម្មវត្ថុ ៖</td>
              <td style="vertical-align: top; border: none; font-weight: bold; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 12pt;">
                ករណីស្នើសុំការពិនិត្យ និងសម្រេចដ៏ខ្ពង់ខ្ពស់លើ...
              </td>
            </tr>
            <tr>
              <td style="vertical-align: top; font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; border: none; white-space: nowrap;">យោង ៖</td>
              <td style="vertical-align: top; border: none; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 12pt;">
                - លិខិត ឬចំណាររបស់<span style="font-family: 'Khmer OS Muol Light', serif;">ឯកឧត្តមអគ្គនាយក</span>...
              </td>
            </tr>
            <tr>
              <td style="vertical-align: top; font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; border: none; white-space: nowrap;">គោរពជូន ៖</td>
              <td style="vertical-align: top; font-weight: bold; border: none; color: #002060; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 12pt;">
                <span style="font-family: 'Khmer OS Muol Light', serif;">ឯកឧត្តមអគ្គនាយក</span> មេត្តាពិនិត្យ និងសម្រេចដ៏ខ្ពង់ខ្ពស់។
              </td>
            </tr>
          </table>
        </div>

        <!-- Content Body -->
        <div style="font-size: 12pt; margin-bottom: 20px; line-height: 2.1; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif;">
          <p><strong style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt;">I. សេចក្តីសង្ខេបអំពីអង្គហេតុ៖</strong></p>
          <p style="text-indent: 95px; text-align: justify;">(រៀបរាប់សង្ខេបអំពីព័ត៌មានលម្អិត និងអង្គហេតុដែលបានកើតឡើង...)</p>

          <p><strong style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt;">II. មតិយោបល់ និងសំណូមពររបស់អង្គភាពជំនាញ៖</strong></p>
          <p style="text-indent: 95px; text-align: justify;">(ផ្តល់ជាមតិយោបល់បច្ចេកទេស ច្បាប់ ឬទស្សនវិស័យច្បាស់លាស់ក្នុងការដោះស្រាយ...)</p>

          <p><strong style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt;">III. សំណើគោរពស្នើសុំការសម្រេច៖</strong></p>
          <p style="text-indent: 95px; text-align: justify;">
            ផ្អែកលើមូលដ្ឋាន និងមតិយោបល់ខាងលើ សូម <span style="font-family: 'Khmer OS Muol Light', serif;">ឯកឧត្តមអគ្គនាយក</span> មេត្តាពិនិត្យ និងសម្រេចដ៏ខ្ពង់ខ្ពស់៖
          </p>
          <ul style="margin-left: 95px;">
            <li>ឯកភាពលើខ្លឹមសារលិខិត ឬសំណើ...</li>
            <li>ចុះហត្ថលេខាលើលិខិតឆ្លើយតបភ្ជាប់មកជាមួយនេះ។</li>
          </ul>
        </div>

        <!-- 1. Drafter Signature Block -->
        ${getDrafterSignatureHtml()}
      </div>
    `,
    LETTER: `
      <div class="document-page page-1" style="position: relative; line-height: 2.1; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 12pt; color: #111;">
        <!-- Header Text -->
        ${textHeaderHtml}

        <!-- Recipient Block -->
        <div style="margin-bottom: 25px;">
          <table style="width: 100%; border: none; border-collapse: collapse; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 12pt;">
            <tr>
              <td style="width: 45%; border: none;"></td>
              <td style="width: 55%; border: none; text-align: left; line-height: 2.1;">
                <div style="font-family: 'Khmer OS Muol Light', serif; margin-bottom: 4px;">សូមគោរពជូន</div>
                <div style="font-weight: bold; margin-bottom: 4px;">ឯកឧត្តម / លោកជំទាវ / លោក / លោកស្រី...</div>
                <div style="color: #444;">(ស្ថាប័ន ឬក្រុមហ៊ុនទទួល)</div>
              </td>
            </tr>
          </table>
        </div>

        <!-- Subject & Reference Table -->
        <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 18px; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 12pt; line-height: 2.1;">
          <tr>
            <td style="width: 95px; vertical-align: top; border: none; padding: 2px 0; white-space: nowrap; font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; color: #000;">កម្មវត្ថុ ៖</td>
            <td style="vertical-align: top; border: none; padding: 2px 0; text-align: justify;">
              ករណី...
            </td>
          </tr>
          <tr>
            <td style="vertical-align: top; border: none; padding: 2px 0; font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; color: #000; white-space: nowrap;">យោង ៖</td>
            <td style="vertical-align: top; border: none; padding: 2px 0; text-align: justify;">
              <div>- លិខិតលេខ...</div>
            </td>
          </tr>
        </table>

        <!-- Body -->
        <div style="font-size: 12pt; text-align: justify; text-indent: 95px; margin-bottom: 18px; line-height: 2.1; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif;">
          សេចក្តីដូចមានចែងក្នុងកម្មវត្ថុ និងយោងខាងលើ ខ្ញុំសូមជម្រាបជូន <strong>ឯកឧត្តម / លោកជំទាវ / លោក / លោកស្រី</strong> មេត្តាជ្រាបថា៖ (សូមសរសេររៀបរាប់ខ្លឹមសារលិខិតផ្លូវការនៅទីនេះ...)។
        </div>

        <div style="font-size: 12pt; text-align: justify; text-indent: 95px; margin-bottom: 25px; line-height: 2.1; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif;">
          អាស្រ័យហេតុនេះ សូម <strong>ឯកឧត្តម / លោកជំទាវ / លោក / លោកស្រី</strong> មេត្តាជ្រាប និងចាត់ចែងតាមការគួរ។
        </div>

        <!-- Signature Block: Right aligned for Leadership / DG -->
        <table style="width: 100%; border: none; border-collapse: collapse; margin-top: 35px; margin-bottom: 25px; line-height: 2.1; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif;">
          <tr>
            <td style="width: 45%; border: none; vertical-align: top; font-size: 10pt; line-height: 1.8;">
              <div style="font-weight: bold; text-decoration: underline; margin-bottom: 4px;">ចម្លងជូន៖</div>
              <div>- <span style="font-family: 'Khmer OS Muol Light', serif;">ឯកឧត្តមអគ្គនាយក</span> "ដើម្បីគោរពជ្រាប"</div>
              <div>- នាយកដ្ឋានរដ្ឋបាល និងហិរញ្ញវត្ថុ</div>
              <div>- ឯកសារ - កាលប្បវត្ត</div>
            </td>
            <td style="width: 55%; border: none; text-align: center; font-size: 11pt; line-height: 2.1; vertical-align: top;">
              ${getOfficialKhmerDateHtml()}
              <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; color: #002060; margin-top: 4px; margin-bottom: 75px;">
                អគ្គនាយក
              </div>
              <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; color: #002060;">
                (ហត្ថលេខា និងត្រា)
              </div>
            </td>
          </tr>
        </table>
      </div>
    `
  };
};

const insertTemplate = async (type) => {
  if (paperEditor.value && paperEditor.value.innerText.trim()) {
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

  const templates = getOfficialTemplates();
  const html = templates[type] || templates.REPORT;
  if (paperEditor.value) {
    paperEditor.value.innerHTML = html;
    emitUpdate();
    checkHasPageBreak();
  }
};

let autoPageBreakTimer = null;

const createPageBreakHtml = (pageNum) => {
  const khmerPageNum = toKhmerNum(pageNum);
  const prevPageNum = toKhmerNum(pageNum - 1);
  return `
    <div class="page-number-footer" contenteditable="false" style="text-align: center; margin-top: 35px; margin-bottom: 15px; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 11pt; color: #444; user-select: none;">
      - ${prevPageNum} -
    </div>
    <div class="page-break" contenteditable="false" style="page-break-before: always; break-before: page; margin: 25px -55px 35px -55px; height: 26px; background: #525659; border-top: 1px solid #3a3d40; border-bottom: 1px solid #3a3d40; box-shadow: inset 0 4px 8px rgba(0,0,0,0.35); position: relative; user-select: none;">
      <div class="page-break-badge" contenteditable="false" style="user-select: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #1e40af; color: #ffffff; padding: 2px 18px; border-radius: 12px; font-size: 9pt; font-family: sans-serif; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.3); z-index: 10;">
        ទំព័រទី ${khmerPageNum} (A4)
      </div>
    </div>
  `;
};

const normalizeDocumentHtml = (html) => {
  if (!html) return html;
  try {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const pageBreaks = doc.querySelectorAll('.page-break');
    let modified = false;

    // Remove obsolete top page number headers
    const headers = doc.querySelectorAll('.page-number-header');
    if (headers.length > 0) {
      modified = true;
      headers.forEach(h => h.remove());
    }

    // Ensure "ឯកឧត្តមអគ្គនាយក" always uses Khmer OS Muol Light
    const textNodesAndSpans = doc.querySelectorAll('strong, b, span');
    textNodesAndSpans.forEach(el => {
      if (el.textContent.trim() === 'ឯកឧត្តមអគ្គនាយក' && (!el.style.fontFamily || !el.style.fontFamily.includes('Muol'))) {
        modified = true;
        el.style.fontFamily = "'Khmer OS Muol Light', serif";
        el.style.fontWeight = 'normal';
      }
    });

    pageBreaks.forEach(pb => {
      const badge = pb.querySelector('.page-break-badge');
      const badgeText = badge ? badge.textContent.trim() : 'ទំព័រទី ២ (A4)';

      const childNodes = Array.from(pb.childNodes);
      const nonBadgeNodes = childNodes.filter(n => {
        if (n.nodeType === Node.TEXT_NODE) return n.textContent.trim().length > 0;
        if (n.nodeType === Node.ELEMENT_NODE) return !n.classList.contains('page-break-badge');
        return false;
      });

      if (nonBadgeNodes.length > 0) {
        modified = true;
        const cleanDivider = doc.createElement('div');
        cleanDivider.className = 'page-break';
        cleanDivider.setAttribute('contenteditable', 'false');
        cleanDivider.style.cssText = 'page-break-before: always; break-before: page; margin: 25px -55px 35px -55px; height: 26px; background: #525659; border-top: 1px solid #3a3d40; border-bottom: 1px solid #3a3d40; box-shadow: inset 0 4px 8px rgba(0,0,0,0.35); position: relative; user-select: none;';
        cleanDivider.innerHTML = `<div class="page-break-badge" contenteditable="false" style="user-select: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #1e40af; color: #ffffff; padding: 2px 18px; border-radius: 12px; font-size: 9pt; font-family: sans-serif; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.3); z-index: 10;">${badgeText}</div>`;

        pb.parentNode.insertBefore(cleanDivider, pb);
        nonBadgeNodes.forEach(node => {
          pb.parentNode.insertBefore(node, pb);
        });
        pb.remove();
      } else {
        pb.setAttribute('contenteditable', 'false');
      }
    });

    // Group closing sentences with the drafter signature block if separated by a page break
    const updatedPageBreaks = doc.querySelectorAll('.page-break');
    updatedPageBreaks.forEach(pb => {
      let nextTarget = pb.nextElementSibling;
      if (nextTarget && nextTarget.classList.contains('page-number-header')) {
        nextTarget = nextTarget.nextElementSibling;
      }

      const isSignatureFollowing = nextTarget && (
        nextTarget.classList.contains('drafter-signature-table') ||
        nextTarget.querySelector?.('.drafter-signature-table')
      );

      if (isSignatureFollowing) {
        const closingNodes = [];
        let prev = pb.previousElementSibling;
        while (prev) {
          const txt = prev.textContent.trim();
          if (
            txt.includes('សេចក្ដីដូចបានគោរព') ||
            txt.includes('សូម ឯកឧត្តមអគ្គនាយក') ||
            txt.includes('មេត្តាទទួលនូវការគោរព')
          ) {
            closingNodes.unshift(prev);
            prev = prev.previousElementSibling;
          } else {
            break;
          }
        }

        if (closingNodes.length > 0) {
          modified = true;
          closingNodes.forEach(node => {
            nextTarget.parentNode.insertBefore(node, nextTarget);
          });
        }
      }
    });

    // Ensure footers for multi-page documents
    if (updatedPageBreaks.length > 0) {
      updatedPageBreaks.forEach((pb, idx) => {
        const pageIdx = idx + 2;
        let prev = pb.previousElementSibling;
        if (!prev || !prev.classList.contains('page-number-footer')) {
          const ftr = doc.createElement('div');
          ftr.className = 'page-number-footer';
          ftr.setAttribute('contenteditable', 'false');
          ftr.style.cssText = "text-align: center; margin-top: 35px; margin-bottom: 15px; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 11pt; color: #444; user-select: none;";
          ftr.textContent = `- ${toKhmerNum(pageIdx - 1)} -`;
          pb.parentNode.insertBefore(ftr, pb);
          modified = true;
        }
      });
      const lastClosing = doc.querySelector('.closing-and-signature-block');
      if (lastClosing && !lastClosing.querySelector('.page-number-footer')) {
        const ftr = doc.createElement('div');
        ftr.className = 'page-number-footer';
        ftr.setAttribute('contenteditable', 'false');
        ftr.style.cssText = "text-align: center; margin-top: 35px; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 11pt; color: #444; user-select: none;";
        ftr.textContent = `- ${toKhmerNum(updatedPageBreaks.length + 1)} -`;
        lastClosing.appendChild(ftr);
        modified = true;
      }
    }

    return modified ? doc.body.innerHTML : html;
  } catch (e) {
    console.error('Error normalizing document HTML:', e);
    return html;
  }
};

const PAGE_H = 1123;
const PAGE_GAP = 60;

const hasPageBreak = ref(false);
const pageBreakCount = ref(0);
const contentHeight = ref(PAGE_H);

let isMeasuring = false;
let measureTimer = null;

const updatePageBreakCount = () => {
  if (!paperEditor.value) {
    pageBreakCount.value = 0;
    hasPageBreak.value = false;
    return;
  }
  const breaks = paperEditor.value.querySelectorAll('.page-break');
  const count = breaks ? breaks.length : 0;
  pageBreakCount.value = count;
  hasPageBreak.value = count > 0;
};

const updateContentMeasurements = () => {
  const el = paperEditor.value;
  if (!el || isMeasuring) return;
  isMeasuring = true;
  try {
    updatePageBreakCount();
    const paperTop = el.getBoundingClientRect().top;
    let bottom = 0;
    for (const child of el.querySelectorAll('*')) {
      const b = child.getBoundingClientRect().bottom - paperTop;
      if (b > bottom) bottom = b;
    }
    const padBottom = parseFloat(getComputedStyle(el).paddingBottom) || 0;
    const next = Math.ceil(bottom + padBottom);
    if (Math.abs(next - contentHeight.value) > 2) contentHeight.value = next; // avoid resize ping-pong
  } finally {
    isMeasuring = false;
  }
};

const debouncedUpdateContentMeasurements = () => {
  if (measureTimer) clearTimeout(measureTimer);
  measureTimer = setTimeout(() => {
    updateContentMeasurements();
  }, 100);
};

const paperStyle = computed(() => {
  const contentPages = Math.ceil((contentHeight.value + PAGE_GAP) / (PAGE_H + PAGE_GAP));
  const pages = Math.max(pageBreakCount.value + 1, contentPages, 1);
  return {
    minHeight: `${pages * PAGE_H + (pages - 1) * PAGE_GAP}px`,
    backgroundColor: '#ffffff'
  };
});

const updatePaperMinHeight = () => {
  updateContentMeasurements();
};

const checkHasPageBreak = () => {
  updateContentMeasurements();
};

const findClosingBlock = (root) => {
  if (!root) return null;
  const explicit = root.querySelector('.closing-and-signature-block');
  if (explicit) return explicit;

  // Search for the closing phrase element (សេចក្ដីដូចបានគោរព...)
  const allElements = Array.from(root.querySelectorAll('div, p'));
  for (const el of allElements) {
    if (el.textContent.includes('សេចក្ដីដូចបានគោរពជម្រាបជូនខាងលើ') || el.textContent.includes('សេចក្ដីដូចបានគោរព')) {
      return el;
    }
  }

  return root.querySelector('.drafter-signature-table');
};

const insertManualPageBreak = () => {
  if (!paperEditor.value || props.readonly) return;
  const existingBreaks = paperEditor.value.querySelectorAll('.page-break');
  const nextNum = existingBreaks.length + 2;
  const breakHtml = createPageBreakHtml(nextNum);

  const target = findClosingBlock(paperEditor.value);
  if (target) {
    target.insertAdjacentHTML('beforebegin', breakHtml);
    if (!target.querySelector('.page-number-footer')) {
      target.insertAdjacentHTML('beforeend', `<div class="page-number-footer" contenteditable="false" style="text-align: center; margin-top: 35px; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 11pt; color: #444; user-select: none;">- ${toKhmerNum(nextNum)} -</div>`);
    }
  } else {
    document.execCommand('insertHTML', false, breakHtml);
  }
  emitUpdate();
  checkHasPageBreak();
  Swal.fire({
    icon: 'success',
    title: 'បានបន្ថែមការផ្ដាច់ទំព័រ!',
    text: `បានផ្ដាច់ទំព័រទី ${toKhmerNum(nextNum)} ទំហំស្តង់ដារ A4 ដោយមានលេខទំព័រនៅខាងក្រោម (Footer)`,
    timer: 1600,
    showConfirmButton: false
  });
};

const removePageBreaks = () => {
  if (!paperEditor.value || props.readonly) return;
  const breaks = paperEditor.value.querySelectorAll('.page-break');
  const headers = paperEditor.value.querySelectorAll('.page-number-header');
  const footers = paperEditor.value.querySelectorAll('.page-number-footer');
  breaks.forEach(el => el.remove());
  headers.forEach(el => el.remove());
  footers.forEach(el => el.remove());
  emitUpdate();
  checkHasPageBreak();
  Swal.fire({
    icon: 'success',
    title: 'បានលុបការផ្ដាច់ទំព័រ',
    text: 'ខ្លឹមសារទាំងអស់ត្រូវបានរួមបញ្ចូលគ្នានៅលើទំព័រទី១វិញដោយជោគជ័យ!',
    timer: 1500,
    showConfirmButton: false
  });
};

const triggerAutoPageBreak = () => {
  if (props.readonly || !paperEditor.value) return;
  if (autoPageBreakTimer) clearTimeout(autoPageBreakTimer);
  autoPageBreakTimer = setTimeout(() => {
    checkAndApplyAutoPageBreak();
  }, 500);
};

const checkAndApplyAutoPageBreak = () => {
  if (!paperEditor.value || props.readonly) return;
  const editorEl = paperEditor.value;
  checkHasPageBreak();
  const existingBreaks = Array.from(editorEl.querySelectorAll('.page-break'));
  const drafterTable = editorEl.querySelector('.drafter-signature-table');

  const container = editorEl.querySelector('.document-page') || editorEl;
  const allBlocks = Array.from(container.children).filter(
    el => !el.classList.contains('page-break') && !el.classList.contains('page-number-header')
  );
  if (allBlocks.length === 0) return;

  // Calculate actual content bottom
  let maxBottom = 0;
  allBlocks.forEach(el => {
    const bottom = el.offsetTop + el.offsetHeight;
    if (bottom > maxBottom) maxBottom = bottom;
  });

  const A4_PAGE_CONTENT_MAX = 1060; // Standard A4 printable height limit

  // Auto clean-up: if document has 1 break but content is small (< 940px) and easily fits on 1 page
  if (existingBreaks.length === 1 && maxBottom < 940) {
    const pb = existingBreaks[0];
    const nextEl = pb.nextElementSibling;
    if (nextEl && nextEl.classList.contains('page-number-header')) {
      const targetAfter = nextEl.nextElementSibling;
      if (targetAfter === drafterTable || targetAfter?.classList.contains('drafter-signature-table') || targetAfter?.classList.contains('closing-and-signature-block') || targetAfter?.textContent.includes('សេចក្ដីដូចបានគោរព')) {
        pb.remove();
        nextEl.remove();
        emitUpdate();
        checkHasPageBreak();
        return;
      }
    }
  }

  // Auto-break only if content TRULY exceeds the full A4 page limit (> 1060px)
  if (existingBreaks.length === 0) {
    if (maxBottom > A4_PAGE_CONTENT_MAX) {
      const target = findClosingBlock(editorEl);
      if (target) {
        target.insertAdjacentHTML('beforebegin', createPageBreakHtml(2));
        if (!target.querySelector('.page-number-footer')) {
          target.insertAdjacentHTML('beforeend', `<div class="page-number-footer" contenteditable="false" style="text-align: center; margin-top: 35px; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 11pt; color: #444; user-select: none;">- ២ -</div>`);
        }
        emitUpdate();
        checkHasPageBreak();
      }
    }
  } else {
    // Multi-page document: check if total overflow requires additional page break
    const numPages = existingBreaks.length + 1;
    const maxThreshold = numPages * A4_PAGE_CONTENT_MAX;
    if (maxBottom > maxThreshold) {
      const nextPage = numPages + 1;
      const target = findClosingBlock(editorEl);
      if (target) {
        target.insertAdjacentHTML('beforebegin', createPageBreakHtml(nextPage));
        if (!target.querySelector('.page-number-footer')) {
          target.insertAdjacentHTML('beforeend', `<div class="page-number-footer" contenteditable="false" style="text-align: center; margin-top: 35px; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 11pt; color: #444; user-select: none;">- ${toKhmerNum(nextPage)} -</div>`);
        }
        emitUpdate();
        checkHasPageBreak();
      }
    }
  }
};

const onEditorInput = () => {
  debouncedUpdateContentMeasurements();
  emitUpdate();
  triggerAutoPageBreak();
};

const onEditorBlur = () => {
  emitUpdate();
};

const emitUpdate = () => {
  if (paperEditor.value) {
    const html = paperEditor.value.innerHTML;
    emit('update:modelValue', html);
    emit('change', html);
  }
};

const toggleFullscreen = () => {
  isFullscreen.value = !isFullscreen.value;
};

// Print Document with Multi-Page styling
const printDocument = () => {
  if (!paperEditor.value) return;
  let rawHtml = normalizeDocumentHtml(paperEditor.value.innerHTML);

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

  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>បោះពុម្ពឯកសារ</title>
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
          .page-break-badge, .page-number-header {
            display: none !important;
          }
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

// Export to Microsoft Word (.doc)
const exportToWord = () => {
  if (!paperEditor.value) return;
  let htmlContent = paperEditor.value.innerHTML;

  // Replace page breaks with Word XML page-breaks
  htmlContent = htmlContent.replace(
    /<div class="page-break"[^>]*>[\s\S]*?<div class="page-number-header"[^>]*>([\s\S]*?)<\/div>/gi,
    `<br clear="all" style="page-break-before:always; mso-break-type:section-break" /><p align="center" style="text-align:center;">$1</p>`
  );

  const header = `
    <html xmlns:o='urn:schemas-microsoft-com:office:office'
          xmlns:w='urn:schemas-microsoft-com:office:word'
          xmlns='http://www.w3.org/TR/REC-html40'>
    <head>
      <meta charset='utf-8'>
      <title>ឯកសាររដ្ឋបាល</title>
      <style>
        @page WordSection1 {
          size: 595.3pt 841.9pt; /* A4 */
          margin: 56.7pt 56.7pt 56.7pt 56.7pt;
        }
        div.WordSection1 { page: WordSection1; }
        body {
          font-family: 'Khmer OS Battambang', 'Battambang', sans-serif;
          font-size: 12pt;
          line-height: 1.6;
        }
        table { border-collapse: collapse; width: 100%; }
      </style>
    </head>
    <body>
      <div class="WordSection1">
  `;
  const footer = `
      </div>
    </body>
    </html>
  `;
  const fullHtml = header + htmlContent + footer;
  const blob = new Blob(['\ufeff' + fullHtml], {
    type: 'application/msword;charset=utf-8'
  });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `Document_${new Date().toISOString().split('T')[0]}.doc`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
};

// Sync with modelValue prop
watch(
  () => props.modelValue,
  (newVal) => {
    if (paperEditor.value) {
      const normalized = normalizeDocumentHtml(newVal || '');
      if (paperEditor.value.innerHTML !== normalized) {
        paperEditor.value.innerHTML = normalized;
      }
      checkHasPageBreak();
    }
  }
);

let cleanupDraggable = null;
let domObserver = null;
let resizeObserver = null;

onMounted(() => {
  nextTick(() => {
    if (paperEditor.value) {
      if (props.modelValue && props.modelValue.trim()) {
        paperEditor.value.innerHTML = normalizeDocumentHtml(props.modelValue);
      } else {
        insertTemplate(props.docType || 'REPORT');
      }
      checkHasPageBreak();
      cleanupDraggable = setupDraggableOpinionBoxes(
        paperEditor.value,
        () => {
          emitUpdate();
        },
        { currentUserId: userStore.id, isAdmin: isDgUser.value }
      );

      // Continuously observe DOM changes to keep pageBreakCount & minHeight in sync
      domObserver = new MutationObserver(() => {
        debouncedUpdateContentMeasurements();
      });
      domObserver.observe(paperEditor.value, {
        childList: true,
        subtree: true,
        characterData: true
      });

      // ResizeObserver on the paper element ensures the white page always covers all content
      if (typeof ResizeObserver !== 'undefined') {
        resizeObserver = new ResizeObserver(() => {
          debouncedUpdateContentMeasurements();
        });
        resizeObserver.observe(paperEditor.value);
      }
    }
  });
});

onUnmounted(() => {
  if (measureTimer) clearTimeout(measureTimer);
  if (cleanupDraggable) cleanupDraggable();
  if (domObserver) domObserver.disconnect();
  if (resizeObserver) resizeObserver.disconnect();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap');

/* Draggable Leadership Opinion Box & Handles */
:deep(.draggable-opinion-box) {
  position: relative;
  transition: box-shadow 0.15s ease;
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
  border-bottom: 2px solid #0056b3;
  z-index: 10;
}

.btn-tool {
  padding: 3px 8px;
  font-size: 13px;
  color: #333;
}
.btn-tool:hover {
  background-color: #e2e6ea;
}

.font-select {
  height: 28px;
  font-size: 12px;
  width: 175px;
  padding: 2px 6px;
}

.size-select {
  height: 28px;
  font-size: 12px;
  width: 105px;
  padding: 2px 6px;
}

.color-picker-label {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  background-color: #fff;
  cursor: pointer;
  margin-bottom: 0;
}
.color-picker-label:hover {
  background-color: #e9ecef;
}

.document-canvas-container {
  flex: 1;
  background-color: #525659;
  overflow-y: auto;
  padding: 25px 15px 60px 15px;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-height: 520px;
  max-height: 720px;
}

.fullscreen-mode .document-canvas-container {
  max-height: calc(100vh - 120px);
}

.document-paper {
  background-color: #ffffff !important;
  width: 100%;
  max-width: 794px; /* Exact standard A4 width at 96 DPI (210mm) */
  min-height: 1123px; /* Exact standard A4 height at 96 DPI (297mm) */
  height: auto !important;
  padding: 45px 55px; /* Clean standard Cambodian margins */
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
  border-radius: 2px;
  color: #111;
  outline: none;
  font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif;
  font-size: 12pt;
  line-height: 2.1;
  cursor: text;
  box-sizing: border-box;
  overflow: visible;
  flex-shrink: 0;
  text-rendering: optimizeLegibility;
  -webkit-font-smoothing: antialiased;
}

.document-paper:focus {
  outline: none;
}

:deep(.page-break) {
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

:deep(.page-break-badge) {
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

:deep(.page-number-header) {
  display: none !important;
}

:deep(.page-number-footer) {
  text-align: center !important;
  font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif !important;
  font-size: 11pt !important;
  color: #444 !important;
  margin-top: 35px !important;
  margin-bottom: 15px !important;
  user-select: none !important;
  clear: both !important;
}

:deep(.doc-number-input) {
  outline: none;
  min-width: 45px;
  display: inline-block;
  border-bottom: 1px dotted #888;
  padding: 0 4px;
}

:deep(.doc-number-input:hover),
:deep(.doc-number-input:focus) {
  background-color: #f1f5f9;
  border-bottom: 1.5px solid #2563eb;
}

@media print {
  :deep(.page-break) {
    margin: 0 !important;
    padding: 0 !important;
    height: 0 !important;
    background: transparent !important;
    border: none !important;
    border-top: none !important;
    border-bottom: none !important;
    outline: none !important;
    box-shadow: none !important;
    page-break-before: always !important;
    break-before: page !important;
    padding-top: 10mm !important;
  }
  :deep(.page-break-badge),
  :deep(.page-number-header) {
    display: none !important;
  }
  :deep(.page-number-footer) {
    text-align: center !important;
    font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif !important;
    font-size: 10.5pt !important;
    color: #333 !important;
    margin-top: 35px !important;
    margin-bottom: 15px !important;
    page-break-after: avoid !important;
  }
  :deep(.closing-and-signature-block),
  :deep(.drafter-signature-table),
  :deep(.review-signatures-wrapper) {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }
}

.word-statusbar {
  user-select: none;
}
</style>
