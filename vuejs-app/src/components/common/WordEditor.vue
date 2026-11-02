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
            class="btn btn-xs btn-outline-secondary mr-2 mb-1"
            @click="insertTemplate('BLANK')"
            title="ទំព័រទទេ"
          >
            <i class="fas fa-file mr-1"></i> ទំព័រទទេ
          </button>
        </div>

        <!-- Action / View Buttons -->
        <div class="d-flex align-items-center flex-wrap ml-auto">
          <button
            type="button"
            class="btn btn-xs btn-outline-dark mr-1 mb-1"
            @click="printDocument"
            title="បោះពុម្ពឯកសារ ឬរក្សាទុកជា PDF"
          >
            <i class="fas fa-print mr-1"></i> បោះពុម្ព
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
        <span>ពាក្យសរុប៖ <strong>{{ wordCount }}</strong></span>
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
    default: 'អគ្គនាយកដ្ឋាន'
  },
  departmentName: {
    type: String,
    default: 'នាយកដ្ឋាន'
  },
  officerName: {
    type: String,
    default: ''
  },
  officerPosition: {
    type: String,
    default: 'មន្ត្រី'
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

// Word counter
const wordCount = computed(() => {
  if (!paperEditor.value) return 0;
  const text = paperEditor.value.innerText || '';
  const clean = text.trim();
  return clean ? clean.split(/\s+/).length : 0;
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
  // execCommand fontSize only supports 1-7, so we style selected text via span
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
  let tableHtml = '<table style="width: 100%; border-collapse: collapse; margin: 15px 0; border: 1px solid #333;">';
  // Table Header
  tableHtml += '<thead><tr style="background-color: #f2f2f2;">';
  for (let c = 1; c <= cols; c++) {
    tableHtml += `<th style="border: 1px solid #444; padding: 8px; text-align: center; font-weight: bold;">ចំណងជើង ${c}</th>`;
  }
  tableHtml += '</tr></thead><tbody>';

  // Table Rows
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

// Official Template Generators
const getOfficialTemplates = () => {
  const todayKh = new Date().toLocaleDateString('km-KH', { year: 'numeric', month: 'long', day: 'numeric' });
  const dateStr = `រាជធានីភ្នំពេញ, ថ្ងៃទី... ខែ... ឆ្នាំ២០២...`;

  return {
    REPORT: `
      <div style="font-family: 'Khmer OS Battambang', sans-serif; line-height: 1.8; color: #111;">
        <!-- Header Section -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px; border: none;">
          <tr>
            <td style="width: 50%; vertical-align: top; text-align: left; border: none; font-size: 12pt;">
              <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt; color: #002060;">${props.organizationName || 'អគ្គនាយកដ្ឋាន...'}</div>
              <div style="font-weight: bold; font-size: 11pt;">${props.departmentName || 'នាយកដ្ឋាន...'}</div>
              <div>លេខ៖ .................... របក</div>
            </td>
            <td style="width: 50%; vertical-align: top; text-align: center; border: none;">
              <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 13pt; color: #002060;">ព្រះរាជាណាចក្រកម្ពុជា</div>
              <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; color: #002060;">ជាតិ សាសនា ព្រះមហាក្សត្រ</div>
              <div style="margin-top: 4px; font-size: 11pt; letter-spacing: 2px;">3 3 3</div>
            </td>
          </tr>
        </table>

        <!-- Document Title -->
        <div style="text-align: center; margin: 25px 0 20px 0;">
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 15pt; color: #000; margin-bottom: 8px;">
            របាយការណ៍
          </div>
          <div style="font-size: 13pt; font-weight: bold;">
            ស្តីពី៖ សេចក្តីរាយការណ៍អំពីលទ្ធផលការងារ ឬការអនុវត្តតាមចំណារ...
          </div>
        </div>

        <!-- Respectfully Line -->
        <div style="margin-bottom: 18px; font-size: 12pt;">
          <strong>គោរពជូន៖ ឯកឧត្តមអគ្គនាយក មេត្តាជ្រាប និងពិនិត្យសម្រេចដ៏ខ្ពង់ខ្ពស់។</strong>
        </div>

        <!-- Content Body -->
        <div style="font-size: 12pt; text-align: justify; text-indent: 40px; margin-bottom: 15px;">
          តបតាមកម្មវត្ថុ និងយោងខាងលើ ខ្ញុំបាទ/នាងខ្ញុំ មានកិត្តិយសសូមគោរពរាយការណ៍ជូន ឯកឧត្តមអគ្គនាយក មេត្តាជ្រាបថា៖
        </div>

        <div style="font-size: 12pt; margin-bottom: 15px;">
          <p><strong>១. សេចក្តីផ្តើម និងស្ថានភាពទូទៅ៖</strong></p>
          <p style="text-indent: 40px;">(សូមសរសេរបរិយាយលម្អិតអំពីព័ត៌មានទូទៅ និងដើមចមនៃបញ្ហា ឬឯកសារចូល...)</p>

          <p><strong>២. លទ្ធផលនៃការពិនិត្យ និងការស្រាវជ្រាវ៖</strong></p>
          <p style="text-indent: 40px;">(សរសេរចំណុចលម្អិត និងទិន្នន័យជាក់ស្តែងដែលទទួលបាន...)</p>

          <p><strong>៣. សេចក្តីសន្និដ្ឋាន និងសំណូមពរ៖</strong></p>
          <p style="text-indent: 40px;">អាស្រ័យហេតុនេះ សូម <strong>ឯកឧត្តមអគ្គនាយក</strong> មេត្តាជ្រាប និងផ្តល់ការណែនាំ ឬការសម្រេចដ៏ខ្ពង់ខ្ពស់។</p>
        </div>

        <!-- Signature Section -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 40px; border: none;">
          <tr>
            <td style="width: 50%; vertical-align: top; text-align: center; border: none;">
              <!-- Left side: Office/Dept Chief endorsement if needed -->
            </td>
            <td style="width: 50%; vertical-align: top; text-align: center; border: none; font-size: 12pt;">
              <div style="margin-bottom: 5px;">${dateStr}</div>
              <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt; margin-bottom: 65px;">
                ${props.officerPosition || 'អ្នកធ្វើរបាយការណ៍'}
              </div>
              <div style="font-weight: bold; font-size: 12pt;">
                ${props.officerName || '.....................................'}
              </div>
            </td>
          </tr>
        </table>
      </div>
    `,
    SUBMISSION_NOTE: `
      <div style="font-family: 'Khmer OS Battambang', sans-serif; line-height: 1.8; color: #111;">
        <!-- Header Section -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px; border: none;">
          <tr>
            <td style="width: 50%; vertical-align: top; text-align: left; border: none; font-size: 12pt;">
              <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt; color: #002060;">${props.organizationName || 'អគ្គនាយកដ្ឋាន...'}</div>
              <div style="font-weight: bold; font-size: 11pt;">${props.departmentName || 'នាយកដ្ឋាន...'}</div>
              <div>លេខ៖ .................... ក.ប.</div>
            </td>
            <td style="width: 50%; vertical-align: top; text-align: center; border: none;">
              <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 13pt; color: #002060;">ព្រះរាជាណាចក្រកម្ពុជា</div>
              <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 12pt; color: #002060;">ជាតិ សាសនា ព្រះមហាក្សត្រ</div>
              <div style="margin-top: 4px; font-size: 11pt; letter-spacing: 2px;">3 3 3</div>
            </td>
          </tr>
        </table>

        <!-- Document Title -->
        <div style="text-align: center; margin: 25px 0 20px 0;">
          <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 15pt; color: #000; margin-bottom: 8px;">
            កំណត់បង្ហាញ
          </div>
        </div>

        <!-- Subject & Reference -->
        <div style="font-size: 12pt; margin-bottom: 20px; line-height: 2;">
          <table style="width: 100%; border: none;">
            <tr>
              <td style="width: 110px; vertical-align: top; font-weight: bold; border: none;">កម្មវត្ថុ៖</td>
              <td style="vertical-align: top; border: none;">
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
        <div style="font-size: 12pt; margin-bottom: 20px;">
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

        <!-- Signature Section -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 40px; border: none;">
          <tr>
            <td style="width: 50%; vertical-align: top; text-align: center; border: none;">
              <!-- Hierarchy endorsement space -->
            </td>
            <td style="width: 50%; vertical-align: top; text-align: center; border: none; font-size: 12pt;">
              <div style="margin-bottom: 5px;">${dateStr}</div>
              <div style="font-family: 'Khmer OS Muol Light', serif; font-size: 11pt; margin-bottom: 65px;">
                ${props.officerPosition || 'អ្នករៀបចំកំណត់បង្ហាញ'}
              </div>
              <div style="font-weight: bold; font-size: 12pt;">
                ${props.officerName || '.....................................'}
              </div>
            </td>
          </tr>
        </table>
      </div>
    `,
    BLANK: `
      <div style="font-family: 'Khmer OS Battambang', sans-serif; line-height: 1.8; font-size: 12pt;">
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

// Print
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
          @page { size: A4; margin: 20mm 20mm 20mm 25mm; }
          body { font-family: 'Khmer OS Battambang', 'Battambang', sans-serif; font-size: 12pt; line-height: 1.8; color: #000; }
          table { width: 100%; border-collapse: collapse; }
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
  const htmlContent = paperEditor.value.innerHTML;
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
          margin: 70.85pt 56.7pt 70.85pt 70.85pt; /* margins in pt */
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
  max-height: 680px;
}

.fullscreen-mode .document-canvas-container {
  max-height: calc(100vh - 120px);
}

.document-paper {
  background-color: #ffffff;
  width: 100%;
  max-width: 820px; /* A4 aspect ratio width */
  min-height: 1050px;
  padding: 55px 65px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.35);
  border-radius: 2px;
  color: #111;
  outline: none;
  font-family: 'Khmer OS Battambang', 'Battambang', sans-serif;
  font-size: 12pt;
  line-height: 1.8;
  cursor: text;
}

.document-paper:focus {
  outline: none;
}

.word-statusbar {
  user-select: none;
}
</style>
