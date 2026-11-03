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
            title="បញ្ចូលទម្រង់របាយការណ៍ផ្លូវការ (១ ទំព័រ)"
          >
            <i class="fas fa-file-alt mr-1"></i> គំរូ របាយការណ៍ (១ ទំព័រ)
          </button>
          <button
            type="button"
            class="btn btn-xs btn-outline-primary mr-1 mb-1 font-weight-bold"
            @click="insertTemplate('REPORT_2_PAGES')"
            title="បញ្ចូលទម្រង់របាយការណ៍ផ្លូវការ (២ ទំព័រ ដោយមានលេខទំព័រ)"
          >
            <i class="fas fa-copy mr-1"></i> គំរូ របាយការណ៍ (២ ទំព័រ)
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
            class="btn btn-xs btn-outline-success mr-1 mb-1 font-weight-bold"
            @click="insertTemplate('LETTER')"
            title="បញ្ចូលទម្រង់លិខិតរដ្ឋបាលផ្លូវការ"
          >
            <i class="fas fa-envelope-open-text mr-1"></i> គំរូ លិខិត
          </button>
          <button
            type="button"
            class="btn btn-xs btn-outline-secondary mr-2 mb-1"
            @click="insertTemplate('BLANK')"
            title="ទំព័រទទេ"
          >
            <i class="fas fa-file mr-1"></i> ទំព័រទទេ
          </button>

          <!-- Insert Page Break & Reviewer Blocks -->
          <div class="border-left pl-2 ml-1 d-inline-flex align-items-center flex-wrap">
            <button
              type="button"
              class="btn btn-xs btn-primary font-weight-bold mr-1 mb-1 shadow-xs"
              @click="insertPageBreak"
              title="បន្ថែមទំព័រថ្មី (ទំព័រទី ២, ៣... ដោយមានលេខទំព័រត្រឹមត្រូវ)"
            >
              <i class="fas fa-plus-circle mr-1"></i> + ទំព័រថ្មី (Page Break)
            </button>
            <button
              type="button"
              class="btn btn-xs btn-outline-warning mr-1 mb-1 font-weight-bold text-dark shadow-xs"
              @click="insertReviewerBlocks"
              title="បញ្ចូលកំណត់ត្រាមតិយោបល់ និងហត្ថលេខារបស់ថ្នាក់ដឹកនាំដែលបានដាក់រួច"
            >
              <i class="fas fa-clipboard-check mr-1 text-warning"></i> + យោបល់ថ្នាក់ដឹកនាំ
            </button>
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
              class="btn btn-xs btn-outline-info mr-1 mb-1 font-weight-bold shadow-xs"
              @click="syncWorkflowApprovals"
              v-if="approvals && approvals.length > 0"
              title="ធ្វើបច្ចុប្បន្នភាពមតិយោបល់ថ្នាក់ដឹកនាំពី Workflow ចូលក្នុងតារាង"
            >
              <i class="fas fa-sync-alt mr-1"></i> 🔄 Sync យោបល់ Workflow
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
            <option value="'Khmer OS Battambang', sans-serif">Khmer OS Battambang</option>
            <option value="'Khmer OS Muol Light', serif">Khmer OS Muol Light</option>
            <option value="'Siemreap', sans-serif">Siemreap</option>
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
        <div class="btn-group btn-group-sm mb-1">
          <button type="button" class="btn btn-light border btn-tool" @click="promptInsertTable" title="បញ្ចូលតារាង (Insert Table)">
            <i class="fas fa-table mr-1 text-primary"></i> តារាង
          </button>
          <button type="button" class="btn btn-light border btn-tool" @click="exec('insertHorizontalRule')" title="បន្ទាត់ផ្តេក">
            <i class="fas fa-minus"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- 2. Document Canvas Area (A4 Paper Feel) -->
    <div class="document-canvas-container" ref="canvasContainer">
      <div
        class="document-paper shadow-lg"
        ref="paperEditor"
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
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed, nextTick } from 'vue';
import Swal from 'sweetalert2';


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
  approvals: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue', 'save-draft', 'change']);

const paperEditor = ref(null);
const canvasContainer = ref(null);
const isFullscreen = ref(false);

const selectedFont = ref("'Khmer OS Battambang', sans-serif");
const selectedSize = ref("12pt");
const textColor = ref("#000000");
const highlightColor = ref("#ffff00");

// Khmer numbers
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
  return (pageBreaks ? pageBreaks.length : 0) + 1;
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

  const pageBreakHtml = `
    <div class="page-break" style="page-break-before: always; break-before: page; margin-top: 40px; margin-bottom: 25px; padding-top: 25px; border-top: 1.5px dashed #007bff; position: relative;">
      <div class="page-break-badge" contenteditable="false" style="user-select: none; position: absolute; top: -14px; left: 50%; transform: translateX(-50%); background: #007bff; color: #ffffff; padding: 2px 16px; border-radius: 12px; font-size: 9.5pt; font-family: sans-serif; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
        ទំព័រទី ${khmerPageNum}
      </div>
      <div class="page-number-header" style="text-align: center; margin-bottom: 25px; font-weight: bold; font-size: 12pt; color: #222;" contenteditable="true">
        - ${khmerPageNum} -
      </div>
    </div>
    <p style="font-size: 12pt; line-height: 2.1; text-indent: 45px;"><br></p>
  `;

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
  const dateStr = getCurrentKhmerDateString();

  return `
    <table style="width: 100%; border: none; border-collapse: collapse; margin-top: 30px; margin-bottom: 25px; page-break-inside: avoid; break-inside: avoid; line-height: 2.1;">
      <tr>
        <td style="width: 45%; border: none;"></td>
        <td style="width: 55%; border: none; text-align: center; font-size: 11pt; line-height: 2.1;">
          <div style="font-size: 11pt; color: #333;">${dateStr}</div>
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt; color: #000; margin-top: 2px;">
            ${dept}
          </div>
          ${office ? `<div style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt; color: #000;">${office}</div>` : ''}
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt; color: #000; margin-top: 4px; margin-bottom: 60px;">
            ${pos}
          </div>
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; color: #000; font-weight: bold;">
            ${officer}
          </div>
        </td>
      </tr>
    </table>
  `;
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
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt; color: ${isDg ? '#002060' : '#000'}; margin-top: 25px;">
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

  // Standard official administrative text header
  const textHeaderHtml = `
    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 25px; font-size: 11pt; line-height: 2.1;">
      <tr>
        <td style="width: 50%; border: none; vertical-align: top; text-align: left;">
           <img src="logo.png" alt="Logo" style="width: 120px; height: auto; margin-bottom: 4px; padding-left: 40px">
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt; color: #008000;">${props.organizationName || 'និយ័តករបរធនបាលកិច្ច'}</div>
          
          <div style="font-size: 11pt; color: #000;">លេខ៖.................... ន.ប.ធ.</div>
        </td>
        <td style="width: 50%; border: none; vertical-align: top; text-align: center;">
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; color:  #008000; margin-bottom: 4px;">ព្រះរាជាណាចក្រកម្ពុជា</div>
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; color:  #008000;">ជាតិ សាសនា ព្រះមហាក្សត្រ</div>
          <div style="font-size: 11pt; letter-spacing: 3px;">* * *</div>
        </td>
      </tr>
    </table>
  `;

  return {
    REPORT: `
      <div class="document-page page-1" style="position: relative; line-height: 2.1; color: #111;">
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
        <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 20px; font-size: 12pt; line-height: 2.1;">
          <tr>
            <td style="width: 85px; vertical-align: top; border: none; padding: 2px 0; white-space: nowrap; font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; color: #000;">កម្មវត្ថុ៖</td>
            <td style="vertical-align: top; border: none; padding: 2px 0;">
              របាយការណ៍ស្តីពី...
            </td>
          </tr>
          <tr>
            <td style="vertical-align: top; font-weight: bold; border: none; padding: 2px 0; white-space: nowrap;">យោង៖</td>
            <td style="vertical-align: top; border: none; padding: 2px 0;">
              <div>- លិខិតលេខ...</div>
              <div>- ចំណារឯកឧត្តមអគ្គនាយក...</div>
            </td>
          </tr>
        </table>

        <!-- Body Paragraph 1 -->
        <div style="font-size: 12pt; text-align: justify; text-indent: 45px; margin-bottom: 18px; line-height: 2.1;">
          សេចក្ដីដូចមានចែងក្នុងកម្មវត្ថុ និងយោងខាងលើ ខ្ញុំសូមគោរពជម្រាបជូន <strong>ឯកឧត្តមអគ្គនាយក</strong> មេត្តាជ្រាបដ៏ខ្ពង់ខ្ពស់ថា៖ (សូមសរសេររៀបរាប់ខ្លឹមសារលម្អិតនៅទីនេះ...)។
        </div>

        <!-- Body Paragraph 2 -->
        <div style="font-size: 12pt; text-align: justify; text-indent: 45px; margin-bottom: 18px; line-height: 2.1;">
          សេចក្ដីដូចបានគោរពជម្រាបជូនខាងលើ សូម <strong>ឯកឧត្តមអគ្គនាយក</strong> មេត្តាពិនិត្យ ព្រមទាំងផ្តល់ការណែនាំបន្ថែមដោយមេត្តាអនុគ្រោះដ៏ខ្ពង់ខ្ពស់។
        </div>

        <!-- Body Paragraph 3 -->
        <div style="font-size: 12pt; text-align: justify; text-indent: 45px; margin-bottom: 25px; line-height: 2.1;">
          សូម <strong>ឯកឧត្តមអគ្គនាយក</strong> មេត្តាទទួលនូវការគោរពដ៏ខ្ពង់ខ្ពស់ពីខ្ញុំ។
        </div>

        <!-- 1. Drafter Signature Block -->
        ${getDrafterSignatureHtml()}

        <!-- 2. Superiors' Review & Opinion Block (Only displays reviews that have already been placed!) -->
        ${getReviewBlockHtml()}
      </div>
    `,
    REPORT_2_PAGES: `
      <div class="document-page page-1" style="position: relative; line-height: 2.1; color: #111;">
        <!-- Header Text -->
        ${textHeaderHtml}

        <!-- Recipient Block -->
        <div style="text-align: right; margin-bottom: 20px; padding-right: 25px;">
          <div style="display: inline-block; text-align: center;">
            <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 13pt; color: #000; margin-bottom: 4px;">
              សូមគោរពជូន
            </div>
            <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 13pt; color: #000;">
              ឯកឧត្តមអគ្គនាយកនៃនិយ័តករបរធនបាលកិច្ច
            </div>
          </div>
        </div>

        <!-- Subject & Reference Table -->
        <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 20px; font-size: 12pt; line-height: 2.1;">
          <tr>
            <td style="width: 85px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0; white-space: nowrap;">កម្មវត្ថុ៖</td>
            <td style="vertical-align: top; border: none; font-weight: bold; padding: 2px 0;">
              របាយការណ៍ស្តីពី...
            </td>
          </tr>
          <tr>
            <td style="vertical-align: top; font-weight: bold; border: none; padding: 2px 0; white-space: nowrap;">យោង៖</td>
            <td style="vertical-align: top; border: none; padding: 2px 0;">
              <div>- លិខិតលេខ...</div>
              <div>- ចំណារឯកឧត្តមអគ្គនាយក...</div>
            </td>
          </tr>
        </table>

        <!-- Page 1 Content -->
        <div style="font-size: 12pt; text-align: justify; text-indent: 45px; margin-bottom: 18px; line-height: 2.1;">
          សេចក្ដីដូចមានចែងក្នុងកម្មវត្ថុ និងយោងខាងលើ ខ្ញុំសូមគោរពជម្រាបជូន <strong>ឯកឧត្តមអគ្គនាយក</strong> មេត្តាជ្រាបដ៏ខ្ពង់ខ្ពស់ថា៖ (សូមសរសេររៀបរាប់ខ្លឹមសារលម្អិតនៃទំព័រទី១ នៅទីនេះ...)។
        </div>
        <div style="font-size: 12pt; text-align: justify; text-indent: 45px; margin-bottom: 25px; line-height: 2.1;">
          (ខ្លឹមសារលម្អិតចំណុចទី១... ចំណុចទី២...)
        </div>
      </div>

      <!-- PAGE 2 (Centered Page Number) -->
      <div class="page-break" style="page-break-before: always; break-before: page; margin-top: 40px; margin-bottom: 25px; padding-top: 25px; border-top: 1.5px dashed #007bff; position: relative;">
        <div class="page-break-badge" contenteditable="false" style="user-select: none; position: absolute; top: -14px; left: 50%; transform: translateX(-50%); background: #007bff; color: #ffffff; padding: 2px 16px; border-radius: 12px; font-size: 9.5pt; font-family: sans-serif; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
          ទំព័រទី ២
        </div>
        <div class="page-number-header" style="text-align: center; margin-bottom: 25px; font-weight: bold; font-size: 12pt; color: #222;" contenteditable="true">
          - ២ -
        </div>

        <!-- Page 2 Body Continuation -->
        <div style="font-size: 12pt; text-align: justify; text-indent: 45px; margin-bottom: 18px; line-height: 2.1;">
          (ខ្លឹមសារបន្តសម្រាប់ទំព័រទី២...)
        </div>
        <div style="font-size: 12pt; text-align: justify; text-indent: 45px; margin-bottom: 18px; line-height: 2.1;">
          សេចក្ដីដូចបានគោរពជម្រាបជូនខាងលើ សូម <strong>ឯកឧត្តមអគ្គនាយក</strong> មេត្តាពិនិត្យ ព្រមទាំងផ្តល់ការណែនាំបន្ថែមដោយមេត្តាអនុគ្រោះដ៏ខ្ពង់ខ្ពស់។
        </div>
        <div style="font-size: 12pt; text-align: justify; text-indent: 45px; margin-bottom: 25px; line-height: 2.1;">
          សូម <strong>ឯកឧត្តមអគ្គនាយក</strong> មេត្តាទទួលនូវការគោរពដ៏ខ្ពង់ខ្ពស់ពីខ្ញុំ។
        </div>

        <!-- 1. Drafter Signature Block at end of content -->
        ${getDrafterSignatureHtml()}

        <!-- 2. Superiors' Review & Opinion Block (Only displays reviews that have already been placed!) -->
        ${getReviewBlockHtml()}
      </div>
    `,
    SUBMISSION_NOTE: `
      <div class="document-page page-1" style="position: relative; line-height: 2.1; color: #111;">
        <!-- Header Text -->
        ${textHeaderHtml}

        <!-- Document Title -->
        <div style="text-align: center; margin: 20px 0;">
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 15pt; color: #000; margin-bottom: 8px;">
            កំណត់បង្ហាញ
          </div>
        </div>

        <!-- Subject & Reference -->
        <div style="font-size: 12pt; margin-bottom: 20px; line-height: 2.1;">
          <table style="width: 100%; border: none; border-collapse: collapse; line-height: 2.1;">
            <tr>
              <td style="width: 110px; vertical-align: top; font-weight: bold; border: none;">កម្មវត្ថុ៖</td>
              <td style="vertical-align: top; border: none; font-weight: bold;">
                ករណីស្នើសុំការពិនិត្យ និងសម្រេចដ៏ខ្ពង់ខ្ពស់លើ...
              </td>
            </tr>
            <tr>
              <td style="vertical-align: top; font-weight: bold; border: none;">យោង៖</td>
              <td style="vertical-align: top; border: none;">
                - លិខិត ឬចំណាររបស់ឯកឧត្តមអគ្គនាយក...
              </td>
            </tr>
            <tr>
              <td style="vertical-align: top; font-weight: bold; border: none;">គោរពជូន៖</td>
              <td style="vertical-align: top; font-weight: bold; border: none; color: #002060;">
                ឯកឧត្តមអគ្គនាយក មេត្តាពិនិត្យ និងសម្រេចដ៏ខ្ពង់ខ្ពស់។
              </td>
            </tr>
          </table>
        </div>

        <!-- Content Body -->
        <div style="font-size: 12pt; margin-bottom: 20px; line-height: 2.1;">
          <p><strong>I. សេចក្តីសង្ខេបអំពីអង្គហេតុ៖</strong></p>
          <p style="text-indent: 40px; text-align: justify;">(រៀបរាប់សង្ខេបអំពីព័ត៌មានលម្អិត និងអង្គហេតុដែលបានកើតឡើង...)</p>

          <p><strong>II. មតិយោបល់ និងសំណូមពររបស់អង្គភាពជំនាញ៖</strong></p>
          <p style="text-indent: 40px; text-align: justify;">(ផ្តល់ជាមតិយោបល់បច្ចេកទេស ច្បាប់ ឬទស្សនវិស័យច្បាស់លាស់ក្នុងការដោះស្រាយ...)</p>

          <p><strong>III. សំណើគោរពស្នើសុំការសម្រេច៖</strong></p>
          <p style="text-indent: 40px; text-align: justify;">
            ផ្អែកលើមូលដ្ឋាន និងមតិយោបល់ខាងលើ សូម <strong>ឯកឧត្តមអគ្គនាយក</strong> មេត្តាពិនិត្យ និងសម្រេចដ៏ខ្ពង់ខ្ពស់៖
          </p>
          <ul style="margin-left: 50px;">
            <li>ឯកភាពលើខ្លឹមសារលិខិត ឬសំណើ...</li>
            <li>ចុះហត្ថលេខាលើលិខិតឆ្លើយតបភ្ជាប់មកជាមួយនេះ។</li>
          </ul>
        </div>

        <!-- 1. Drafter Signature Block -->
        ${getDrafterSignatureHtml()}

        <!-- 2. Superiors' Review & Opinion Block -->
        ${getReviewBlockHtml()}
      </div>
    `,
    LETTER: `
      <div class="document-page page-1" style="position: relative; line-height: 2.1; color: #111;">
        <!-- Header Text -->
        ${textHeaderHtml}

        <!-- Recipient Block -->
        <div style="margin-bottom: 25px;">
          <table style="width: 100%; border: none; border-collapse: collapse;">
            <tr>
              <td style="width: 45%; border: none;"></td>
              <td style="width: 55%; border: none; text-align: left; font-size: 12pt; line-height: 2.1;">
                <div style="font-family: 'Khmer OS Muol Light', serif; margin-bottom: 4px;">សូមគោរពជូន</div>
                <div style="font-weight: bold; margin-bottom: 4px;">ឯកឧត្តម / លោកជំទាវ / លោក / លោកស្រី...</div>
                <div style="color: #444;">(ស្ថាប័ន ឬក្រុមហ៊ុនទទួល)</div>
              </td>
            </tr>
          </table>
        </div>

        <!-- Subject & Reference Table -->
        <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 20px; font-size: 12pt; line-height: 2.1;">
          <tr>
            <td style="width: 85px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0; white-space: nowrap;">កម្មវត្ថុ៖</td>
            <td style="vertical-align: top; border: none; font-weight: bold; padding: 2px 0;">
              ករណី...
            </td>
          </tr>
          <tr>
            <td style="vertical-align: top; font-weight: bold; border: none; padding: 2px 0; white-space: nowrap;">យោង៖</td>
            <td style="vertical-align: top; border: none; padding: 2px 0;">
              <div>- លិខិតលេខ...</div>
            </td>
          </tr>
        </table>

        <!-- Body -->
        <div style="font-size: 12pt; text-align: justify; text-indent: 45px; margin-bottom: 18px; line-height: 2.1;">
          សេចក្តីដូចមានចែងក្នុងកម្មវត្ថុ និងយោងខាងលើ ខ្ញុំសូមជម្រាបជូន <strong>ឯកឧត្តម / លោកជំទាវ / លោក / លោកស្រី</strong> មេត្តាជ្រាបថា៖ (សូមសរសេររៀបរាប់ខ្លឹមសារលិខិតផ្លូវការនៅទីនេះ...)។
        </div>

        <div style="font-size: 12pt; text-align: justify; text-indent: 45px; margin-bottom: 25px; line-height: 2.1;">
          អាស្រ័យហេតុនេះ សូម <strong>ឯកឧត្តម / លោកជំទាវ / លោក / លោកស្រី</strong> មេត្តាជ្រាប និងចាត់ចែងតាមការគួរ។
        </div>

        <!-- Signature Block: Right aligned for Leadership / DG -->
        <table style="width: 100%; border: none; border-collapse: collapse; margin-top: 35px; margin-bottom: 25px; line-height: 2.1;">
          <tr>
            <td style="width: 45%; border: none; vertical-align: bottom; font-size: 10pt; line-height: 1.8;">
              <div style="font-weight: bold; text-decoration: underline; margin-bottom: 4px;">ចម្លងជូន៖</div>
              <div>- ឯកឧត្តមអគ្គនាយក "ដើម្បីគោរពជ្រាប"</div>
              <div>- នាយកដ្ឋានរដ្ឋបាល និងហិរញ្ញវត្ថុ</div>
              <div>- ឯកសារ - កាលប្បវត្ត</div>
            </td>
            <td style="width: 55%; border: none; text-align: center; font-size: 11pt; line-height: 2.1;">
              <div style="font-size: 11pt; color: #333;">${getCurrentKhmerDateString()}</div>
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
    `,
    BLANK: `
      <div style="font-family: 'Khmer OS Battambang', 'Battambang', sans-serif; line-height: 2.1; font-size: 12pt;">
        <p><br></p>
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
  const html = templates[type] || templates.BLANK;
  if (paperEditor.value) {
    paperEditor.value.innerHTML = html;
    emitUpdate();
  }
};

const onEditorInput = () => {
  emitUpdate();
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
  const content = paperEditor.value.innerHTML;
  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>បោះពុម្ពឯកសារ</title>
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
          .page-break-badge {
            display: none !important;
          }
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
    if (paperEditor.value && paperEditor.value.innerHTML !== newVal) {
      paperEditor.value.innerHTML = newVal || '';
    }
  }
);

onMounted(() => {
  nextTick(() => {
    if (paperEditor.value) {
      if (props.modelValue && props.modelValue.trim()) {
        paperEditor.value.innerHTML = props.modelValue;
      } else {
        insertTemplate(props.docType || 'REPORT');
      }
    }
  });
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap');

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
  padding: 25px 15px;
  display: flex;
  justify-content: center;
  min-height: 520px;
  max-height: 720px;
}

.fullscreen-mode .document-canvas-container {
  max-height: calc(100vh - 120px);
}

.document-paper {
  background-color: #ffffff;
  width: 100%;
  max-width: 794px; /* Exact standard A4 width at 96 DPI (210mm) */
  min-height: 1123px; /* Exact standard A4 height at 96 DPI (297mm) */
  padding: 45px 55px; /* Clean standard Cambodian margins */
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
  border-radius: 2px;
  color: #111;
  outline: none;
  font-family: 'Khmer OS Battambang', 'Siemreap', 'Battambang', sans-serif;
  font-size: 12pt;
  line-height: 2.1;
  cursor: text;
  box-sizing: border-box;
  overflow: visible;
  text-rendering: optimizeLegibility;
  -webkit-font-smoothing: antialiased;
}

.document-paper:focus {
  outline: none;
}

.word-statusbar {
  user-select: none;
}
</style>
