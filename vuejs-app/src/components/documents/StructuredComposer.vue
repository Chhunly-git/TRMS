<template>
  <div class="structured-composer-container">
    <!-- Editor Mode Toolbar -->
    <div class="composer-toolbar bg-light p-2 border rounded mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div class="d-flex align-items-center gap-1 flex-wrap">
        <span class="badge badge-success mr-2 py-2 px-3 font-khmer">
          <i class="fas fa-layer-group mr-1"></i> Structured Composer v2
        </span>
        <button type="button" class="btn btn-sm btn-outline-secondary font-khmer" @click="execCmd('bold')" title="ដិត (Bold)">
          <i class="fas fa-bold"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary font-khmer" @click="execCmd('italic')" title="ទ្រេត (Italic)">
          <i class="fas fa-italic"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary font-khmer" @click="execCmd('underline')" title="គូសបន្ទាត់ក្រោម (Underline)">
          <i class="fas fa-underline"></i>
        </button>
        <div class="vr mx-1"></div>
        <button type="button" class="btn btn-sm btn-outline-secondary font-khmer" @click="execCmd('justifyLeft')" title="តម្រឹមឆ្វេង">
          <i class="fas fa-align-left"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary font-khmer" @click="execCmd('justifyCenter')" title="តម្រឹមកណ្តាល">
          <i class="fas fa-align-center"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary font-khmer" @click="execCmd('justifyRight')" title="តម្រឹមស្តាំ">
          <i class="fas fa-align-right"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary font-khmer" @click="execCmd('justifyFull')" title="តម្រឹមសងខាង (Justify)">
          <i class="fas fa-align-justify"></i>
        </button>
        <div class="vr mx-1"></div>
        <button type="button" class="btn btn-sm btn-outline-secondary font-khmer" @click="execCmd('insertUnorderedList')" title="បញ្ជីចំណុច (Bullets)">
          <i class="fas fa-list-ul"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary font-khmer" @click="execCmd('insertOrderedList')" title="បញ្ជីលេខរៀង (Numbering)">
          <i class="fas fa-list-ol"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-primary font-khmer" @click="insertTable" title="បញ្ចូលតារាង (Table)">
          <i class="fas fa-table mr-1"></i> តារាង
        </button>
        <button type="button" class="btn btn-sm btn-outline-info font-khmer" @click="insertPageBreak" title="ផ្តាច់ទំព័រថ្មី (Page Break)">
          <i class="fas fa-file-alt mr-1"></i> ផ្តាច់ទំព័រថ្មី
        </button>
      </div>

      <div class="d-flex align-items-center gap-2">
        <span class="small text-muted font-khmer">
          <i class="fas fa-shield-alt text-success mr-1"></i> រចនាសម្ព័ន្ធការពារស្វ័យប្រវត្ត (Header, Signatures & Footer មិនអាចច្រឡំលុបឡើយ)
        </span>
      </div>
    </div>

    <!-- Official A4 Paper Container -->
    <div class="paper-preview-canvas shadow-sm rounded p-3">
      <div class="a4-sheet">
        <!-- ZONE 1: HEADER -->
        <div class="header-row">
          <div class="header-left-col">
            <img src="/logo.png" class="header-logo-img" alt="Logo" @error="onLogoError" />
            <div class="header-org-name">{{ organizationName || 'និយ័តករបរធនបាលកិច្ច' }}</div>
            <div class="header-doc-number">
              លេខ ៖ {{ docNumber || '......... ន.ប.ធ./' + (unitSuffix || '') }}
            </div>
          </div>

          <div class="header-right-col">
            <div class="kingdom-title">ព្រះរាជាណាចក្រកម្ពុជា</div>
            <div class="kingdom-motto">ជាតិ សាសនា ព្រះមហាក្សត្រ</div>
            <div class="kingdom-stars">៚ ៚ ៚</div>
          </div>
        </div>

        <!-- RECIPIENT -->
        <div class="recipient-center-block">
          <div class="recipient-honorific">សូមគោរពជូន</div>
          <div class="recipient-input-wrapper">
            <input
              type="text"
              class="form-control form-control-sm text-center font-moul recipient-input"
              v-model="recipientTextLocal"
              placeholder="ឯកឧត្តមអគ្គនាយក នៃនិយ័តករបរធនបាលកិច្ច"
              @input="emitChange"
            />
          </div>
        </div>

        <!-- ZONE 2: SUBJECT & REFERENCES -->
        <div class="meta-block">
          <div class="meta-row align-items-center mb-2">
            <div class="meta-label-fixed font-weight-bold">កម្មវត្ថុ ៖</div>
            <div class="meta-content-aligned">
              <input
                type="text"
                class="form-control form-control-sm font-weight-bold meta-title-input"
                v-model="titleLocal"
                placeholder="សរសេរកម្មវត្ថុ ឬចំណងជើងរបាយការណ៍..."
                @input="emitChange"
              />
            </div>
          </div>

          <div class="meta-row">
            <div class="meta-label-fixed font-weight-bold pt-1">យោង ៖</div>
            <div class="meta-content-aligned">
              <div v-for="(refItem, rIdx) in referencesLocal" :key="rIdx" class="d-flex align-items-center mb-1 gap-1">
                <span class="text-muted mr-1">-</span>
                <input
                  type="text"
                  class="form-control form-control-sm flex-grow-1"
                  v-model="referencesLocal[rIdx]"
                  placeholder="ឧ. លិខិតលេខ... ចុះថ្ងៃទី..."
                  @input="emitChange"
                />
                <button
                  type="button"
                  class="btn btn-sm btn-outline-danger py-0 px-2"
                  @click="removeReference(rIdx)"
                  title="លុបយោងនេះ"
                >
                  <i class="fas fa-times"></i>
                </button>
              </div>
              <button
                type="button"
                class="btn btn-sm btn-light text-primary font-khmer mt-1 py-0 px-2 border"
                @click="addReference"
              >
                <i class="fas fa-plus-circle mr-1"></i> បន្ថែមយោងថ្មី
              </button>
            </div>
          </div>
        </div>

        <!-- ZONE 3: BODY (RICH TEXT ONLY) -->
        <div class="doc-body-section">
          <label class="small text-muted font-khmer mb-1 d-block">
            <i class="fas fa-pen mr-1"></i> ខ្លឹមសារលិខិត (វាយអត្ថបទកថាខណ្ឌ ឬតារាងនៅទីនេះ) ៖
          </label>
          <div
            ref="bodyEditorRef"
            class="body-editable-area"
            contenteditable="true"
            @input="onBodyInput"
            @blur="onBodyInput"
          ></div>
        </div>

        <!-- ZONE 4: KEEP-TOGETHER CLOSING & SIGNATURES -->
        <div class="keep-together-section mt-3">
          <div class="closing-phrases-block mb-3">
            <div class="mb-2">
              <input
                type="text"
                class="form-control form-control-sm text-justify closing-input"
                v-model="closingPhrase1Local"
                placeholder="សេចក្តីដូចបានគោរពរាយការណ៍ជូនខាងលើ សូម ឯកឧត្តមអគ្គនាយក មេត្តាពិនិត្យ..."
                @input="emitChange"
              />
            </div>
            <div>
              <input
                type="text"
                class="form-control form-control-sm text-justify closing-input"
                v-model="closingPhrase2Local"
                placeholder="សូម ឯកឧត្តមអគ្គនាយក មេត្តាទទួលនូវការគោរពដ៏ខ្ពង់ខ្ពស់អំពីខ្ញុំ។"
                @input="emitChange"
              />
            </div>
          </div>

          <!-- SIGNATURES MAIN ROW: STRICT 50/50 COLUMN SPLIT -->
          <div class="signatures-main-row">
            <!-- Left Side: Leaders' Opinion Boxes Area (50%) -->
            <div class="leaders-opinion-area">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small font-weight-bold text-success font-khmer">
                  <i class="fas fa-stamp mr-1"></i> មតិយោបល់ និងហត្ថលេខាថ្នាក់ដឹកនាំ
                </span>
                <span class="badge badge-light text-muted small border">អូសទម្លាក់ដើម្បីប្តូរ Slot</span>
              </div>

              <div class="opinion-boxes-subgrid">
                <div
                  v-for="box in currentOpinions"
                  :key="box.approval_id"
                  class="current-opinion-box"
                  :class="{ 'can-drag': box.can_move }"
                  :draggable="box.can_move"
                  @dragstart="onDragStart($event, box)"
                >
                  <div class="opinion-text">{{ box.comment }}</div>
                  <div class="opinion-sig-area">
                    <img v-if="box.signature_url" :src="box.signature_url" alt="ហត្ថលេខា" />
                    <span v-else class="text-muted small">ហត្ថលេខា</span>
                  </div>
                  <div class="opinion-short-date">{{ box.short_date_kh }}</div>
                  <div class="opinion-signer-name">{{ box.signer_name_kh }}</div>
                </div>

                <!-- Vacant Drop Slots -->
                <div
                  v-for="vSlot in vacantSlots"
                  :key="vSlot"
                  class="vacant-drop-slot"
                  @dragover.prevent
                  @dragenter.prevent="onDragEnter($event)"
                  @dragleave.prevent="onDragLeave($event)"
                  @drop.prevent="onDrop($event, vSlot)"
                >
                  <i class="fas fa-plus-circle text-muted mb-1"></i>
                  <span class="small text-muted">{{ vSlot }}</span>
                </div>

                <div v-if="currentOpinions.length === 0" class="text-center p-3 text-muted border border-dashed rounded w-100">
                  <small>មិនទាន់មានមតិយោបល់ពីថ្នាក់ដឹកនាំនៅឡើយ</small>
                </div>
              </div>
            </div>

            <!-- Right Side: Drafter Block ONLY (50%) -->
            <div class="drafter-official-block">
              <div class="drafter-lock-badge mb-1">
                <span class="badge badge-light border text-success">
                  <i class="fas fa-lock mr-1"></i> ទីតាំងអ្នកបង្កើតរបាយការណ៍ (អចិន្ត្រៃយ៍)
                </span>
              </div>
              <div class="lunar-date-text">
                <input
                  type="text"
                  class="form-control form-control-sm text-center border-0 bg-transparent text-muted font-italic"
                  v-model="lunarDateLocal"
                  placeholder="ថ្ងៃ................... ខែ............. ឆ្នាំ........ ព.ស.២៥៦៨"
                  @input="emitChange"
                />
              </div>
              <div class="solar-date-text">
                <input
                  type="text"
                  class="form-control form-control-sm text-center border-0 bg-transparent font-weight-bold"
                  v-model="solarDateLocal"
                  placeholder="រាជធានីភ្នំពេញ ថ្ងៃទី... ខែ... ឆ្នាំ..."
                  @input="emitChange"
                />
              </div>
              <div class="drafter-dept-text">{{ departmentName || 'នាយកដ្ឋាន...' }}</div>
              <div v-if="officeName" class="drafter-office-text">{{ officeName }}</div>
              <div class="drafter-role-text">{{ officerPosition || 'មន្ត្រីទទួលបន្ទុក' }}</div>
              <div class="drafter-sig-container">
                <img v-if="officerSignature" :src="officerSignature" class="drafter-sig-img" alt="ហត្ថលេខាអ្នកបង្កើត" />
                <div v-else class="drafter-sig-placeholder">
                  <span class="font-signature text-primary">{{ officerName }}</span>
                </div>
              </div>
              <div class="drafter-name-text">{{ officerName }}</div>
            </div>
          </div>
        </div>

        <!-- ZONE 5: FOOTER (Page 1 Only - Pure HTML/CSS) -->
        <div class="page-1-footer mt-4">
          <div class="footer-divider-line"></div>
          <div class="footer-content-row">
            <div class="footer-address-col">
              <div class="footer-addr-kh">អគារ ១៦៨F (ជាន់ទី៨) ផ្លូវ ៥៩៨ សង្កាត់ច្រាំងចំរេះទី១ ខណ្ឌឫស្សីកែវ រាជធានីភ្នំពេញ ព្រះរាជាណាចក្រកម្ពុជា</div>
              <div class="footer-addr-en">Building 168F (8th Floor), Street 598, Sangkat Chrang Chamres 1, Khan Russey Keo, Phnom Penh, Kingdom of Cambodia</div>
            </div>

            <div class="footer-qr-col">
              <div class="footer-qr-box">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="#1b5e20">
                  <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm14-2h4v4h-4v-4zm-4 4h4v4h-4v-4zm4 4h4v4h-4v-4zm-4-8h4v4h-4v-4z"/>
                </svg>
              </div>
              <div class="footer-qr-box">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="#1b5e20">
                  <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm14-2h4v4h-4v-4zm-4 4h4v4h-4v-4zm4 4h4v4h-4v-4zm-4-8h4v4h-4v-4z"/>
                </svg>
              </div>
            </div>

            <div class="footer-contact-col">
              <div class="footer-contact-row"><strong>ទូរសព្ទ ៖</strong> (+៨៥៥) ២៣-៦៣៧ ៨៨៨</div>
              <div class="footer-contact-row"><strong>សារអេឡិចត្រូនិច ៖</strong> info@trustregulator.gov.kh</div>
              <div class="footer-contact-row"><strong>គេហទំព័រ ៖</strong> www.trustregulator.gov.kh</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { apiGetOpinionSlots, apiUpdateOpinionPlacement } from '@/functions/api/outboundDocument';

const props = defineProps({
  docId: { type: [Number, String], default: null },
  docNumber: { type: String, default: '' },
  unitSuffix: { type: String, default: '' },
  organizationName: { type: String, default: 'និយ័តករបរធនបាលកិច្ច' },
  departmentName: { type: String, default: '' },
  officeName: { type: String, default: '' },
  officerName: { type: String, default: '' },
  officerPosition: { type: String, default: 'មន្ត្រីទទួលបន្ទុក' },
  officerSignature: { type: String, default: '' },
  title: { type: String, default: '' },
  recipientType: { type: String, default: 'INTERNAL' },
  recipientText: { type: String, default: 'ឯកឧត្តមអគ្គនាយក នៃនិយ័តករបរធនបាលកិច្ច' },
  references: { type: Array, default: () => [] },
  closingPhrase1: { type: String, default: 'សេចក្តីដូចបានគោរពរាយការណ៍ជូនខាងលើ សូម ឯកឧត្តមអគ្គនាយក មេត្តាពិនិត្យ និងផ្តល់ការណែនាំដ៏ខ្ពង់ខ្ពស់។' },
  closingPhrase2: { type: String, default: 'សូម ឯកឧត្តមអគ្គនាយក មេត្តាទទួលនូវការគោរពដ៏ខ្ពង់ខ្ពស់អំពីខ្ញុំ។' },
  bodyHtml: { type: String, default: '' },
  lunarDateKh: { type: String, default: '' },
  solarDateKh: { type: String, default: '' },
});

const emit = defineEmits([
  'update:title',
  'update:recipientText',
  'update:recipientType',
  'update:references',
  'update:closingPhrase1',
  'update:closingPhrase2',
  'update:bodyHtml',
  'update:lunarDateKh',
  'update:solarDateKh',
  'change',
]);

const bodyEditorRef = ref(null);
const titleLocal = ref(props.title);
const recipientTextLocal = ref(props.recipientText);
const referencesLocal = ref(Array.isArray(props.references) ? [...props.references] : []);
const closingPhrase1Local = ref(props.closingPhrase1);
const closingPhrase2Local = ref(props.closingPhrase2);
const lunarDateLocal = ref(props.lunarDateKh);
const solarDateLocal = ref(props.solarDateKh);

const currentOpinions = ref([]);
const vacantSlots = ref(['OPINION_LEFT_1', 'OPINION_LEFT_2', 'OPINION_LEFT_3', 'OPINION_LEFT_4']);
const draggedBox = ref(null);

watch(() => props.title, (v) => { titleLocal.value = v; });
watch(() => props.recipientText, (v) => { recipientTextLocal.value = v; });
watch(() => props.references, (v) => { referencesLocal.value = Array.isArray(v) ? [...v] : []; }, { deep: true });
watch(() => props.closingPhrase1, (v) => { closingPhrase1Local.value = v; });
watch(() => props.closingPhrase2, (v) => { closingPhrase2Local.value = v; });
watch(() => props.lunarDateKh, (v) => { lunarDateLocal.value = v; });
watch(() => props.solarDateKh, (v) => { solarDateLocal.value = v; });

watch(() => props.bodyHtml, (v) => {
  if (bodyEditorRef.value && bodyEditorRef.value.innerHTML !== v) {
    bodyEditorRef.value.innerHTML = v || '<p></p>';
  }
});

onMounted(() => {
  if (bodyEditorRef.value) {
    bodyEditorRef.value.innerHTML = props.bodyHtml || '<p>សេចក្តីដូចមានចែងក្នុងកម្មវត្ថុ និងយោងខាងលើ ខ្ញុំសូមគោរពជម្រាបជូន...</p>';
  }
  if (props.docId) {
    fetchOpinionSlots();
  }
});

const emitChange = () => {
  emit('update:title', titleLocal.value);
  emit('update:recipientText', recipientTextLocal.value);
  emit('update:references', referencesLocal.value);
  emit('update:closingPhrase1', closingPhrase1Local.value);
  emit('update:closingPhrase2', closingPhrase2Local.value);
  emit('update:lunarDateKh', lunarDateLocal.value);
  emit('update:solarDateKh', solarDateLocal.value);
  emit('change', {
    title: titleLocal.value,
    recipient_text: recipientTextLocal.value,
    references: referencesLocal.value,
    closing_phrase_1: closingPhrase1Local.value,
    closing_phrase_2: closingPhrase2Local.value,
    body_html: bodyEditorRef.value ? bodyEditorRef.value.innerHTML : '',
    lunar_date_kh: lunarDateLocal.value,
    solar_date_kh: solarDateLocal.value,
  });
};

const onBodyInput = () => {
  if (!bodyEditorRef.value) return;
  const html = bodyEditorRef.value.innerHTML;
  emit('update:bodyHtml', html);
  emitChange();
};

const addReference = () => {
  referencesLocal.value.push('');
  emitChange();
};

const removeReference = (idx) => {
  referencesLocal.value.splice(idx, 1);
  emitChange();
};

const execCmd = (cmd, val = null) => {
  document.execCommand(cmd, false, val);
  onBodyInput();
};

const insertTable = () => {
  const tableHtml = `
    <table class="table table-bordered my-2" style="width: 100%; border-collapse: collapse;">
      <thead>
        <tr style="background: #f7f9fa;">
          <th style="border: 1px solid #ddd; padding: 6px;">ល.រ</th>
          <th style="border: 1px solid #ddd; padding: 6px;">បរិយាយ</th>
          <th style="border: 1px solid #ddd; padding: 6px;">ចំនួន</th>
          <th style="border: 1px solid #ddd; padding: 6px;">ផ្សេងៗ</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">១</td>
          <td style="border: 1px solid #ddd; padding: 6px;">ទិន្នន័យគំរូ</td>
          <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">១</td>
          <td style="border: 1px solid #ddd; padding: 6px;"></td>
        </tr>
      </tbody>
    </table>
  `;
  document.execCommand('insertHTML', false, tableHtml);
  onBodyInput();
};

const insertPageBreak = () => {
  const breakHtml = `<hr class="page-break" style="page-break-after: always; break-after: page; border: 1px dashed #00796b; margin: 20px 0;" />`;
  document.execCommand('insertHTML', false, breakHtml);
  onBodyInput();
};

const onLogoError = (e) => {
  e.target.style.display = 'none';
};

const fetchOpinionSlots = async () => {
  if (!props.docId) return;
  try {
    const res = await apiGetOpinionSlots(props.docId);
    currentOpinions.value = res.data.opinions || [];
    vacantSlots.value = res.data.vacant_slots || ['OPINION_LEFT_1', 'OPINION_LEFT_2', 'OPINION_LEFT_3', 'OPINION_LEFT_4'];
  } catch (err) {
    console.error('Failed to fetch opinion slots:', err);
  }
};

const onDragStart = (e, box) => {
  if (!box.can_move) {
    e.preventDefault();
    return;
  }
  draggedBox.value = box;
  e.dataTransfer.setData('text/plain', box.approval_id);
};

const onDragEnter = (e) => {
  e.target.classList.add('slot-hover');
};

const onDragLeave = (e) => {
  e.target.classList.remove('slot-hover');
};

const onDrop = async (e, targetSlot) => {
  e.target.classList.remove('slot-hover');
  if (!draggedBox.value || !props.docId) return;

  try {
    await apiUpdateOpinionPlacement(props.docId, {
      approval_id: draggedBox.value.approval_id,
      target_slot: targetSlot,
    });
    await fetchOpinionSlots();
  } catch (err) {
    const msg = err.response?.data?.message || 'មិនអាចផ្លាស់ប្តូរទីតាំងបានឡើយ!';
    alert(msg);
  } finally {
    draggedBox.value = null;
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Kantumruy+Pro:ital,wght@0,400;0,600;0,700;1,400&family=Moul&display=swap');

.structured-composer-container {
  font-family: 'Kantumruy Pro', 'Battambang', sans-serif;
}

.paper-preview-canvas {
  background: #eef2f5;
  overflow-x: auto;
  padding: 20px 0;
}

.a4-sheet {
  width: 210mm;
  min-height: 297mm;
  margin: 0 auto;
  background: #ffffff;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
  padding: 16mm 20mm 20mm 25mm; /* Official Cambodian Margins */
  position: relative;
  box-sizing: border-box;
}

/* ZONE 1 */
.header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}
.header-left-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  width: 220px;
}
.header-logo-img {
  width: 68px;
  height: 68px;
  object-fit: contain;
  margin-bottom: 4px;
}
.header-org-name {
  font-family: 'Moul', cursive;
  font-size: 11.5px;
  color: #1b5e20;
  line-height: 1.35;
  white-space: nowrap;
}
.header-doc-number {
  font-size: 12px;
  font-weight: 600;
  margin-top: 3px;
  white-space: nowrap;
}
.header-right-col {
  text-align: center;
  width: 260px;
  padding-top: 5px;
}
.kingdom-title {
  font-family: 'Moul', cursive;
  font-size: 14px;
  color: #1b5e20;
  margin-bottom: 3px;
}
.kingdom-motto {
  font-family: 'Moul', cursive;
  font-size: 12.5px;
  color: #1b5e20;
  letter-spacing: 0.5px;
  margin-bottom: 2px;
}
.kingdom-stars {
  font-size: 11px;
  color: #1b5e20;
  letter-spacing: 3px;
}

/* RECIPIENT */
.recipient-center-block {
  text-align: center;
  margin: 6px 0 14px 0;
}
.recipient-honorific {
  font-family: 'Moul', cursive;
  font-size: 12.5px;
  margin-bottom: 2px;
}
.recipient-input {
  max-width: 450px;
  margin: 0 auto;
  border-color: #c8e6c9;
  font-size: 12.5px;
}

/* ZONE 2 */
.meta-block {
  margin-bottom: 14px;
}
.meta-row {
  display: flex;
  align-items: flex-start;
  font-size: 13.5px;
  line-height: 1.55;
}
.meta-label-fixed {
  width: 1.5cm;
  min-width: 1.5cm;
  white-space: nowrap;
}
.meta-content-aligned {
  flex: 1;
}
.meta-title-input {
  border-color: #b2dfdb;
}

/* ZONE 3: BODY */
.doc-body-section {
  margin-bottom: 15px;
}
.body-editable-area {
  min-height: 250px;
  border: 1px dashed #b2dfdb;
  border-radius: 4px;
  padding: 10px 12px;
  font-size: 13.5px;
  line-height: 1.6;
  text-align: justify;
  background: #fafcfc;
  outline: none;
}
.body-editable-area:focus {
  border-color: #00796b;
  background: #ffffff;
}

/* ZONE 4: SIGNATURES */
.closing-input {
  border-color: #e0e0e0;
  font-size: 13px;
  text-indent: 1.5cm;
}
.signatures-main-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 15px;
}
.leaders-opinion-area {
  width: 50%;
  display: flex;
  flex-direction: column;
}
.opinion-boxes-subgrid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.current-opinion-box {
  flex: 1 1 calc(50% - 8px);
  min-width: 125px;
  text-align: center;
  padding: 4px;
  font-size: 10.5px;
  border: 1px solid #81c784;
  border-radius: 4px;
  background: #ffffff;
}
.current-opinion-box.can-drag {
  cursor: grab;
  box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}
.opinion-text {
  font-size: 10px;
  margin-bottom: 3px;
}
.opinion-sig-area img {
  height: 38px;
  max-width: 130px;
  object-fit: contain;
}
.opinion-short-date {
  font-size: 9.5px;
}
.opinion-signer-name {
  font-family: 'Moul', cursive;
  font-size: 10.5px;
}
.vacant-drop-slot {
  flex: 1 1 calc(50% - 8px);
  min-width: 125px;
  min-height: 75px;
  border: 1.5px dashed #b0bec5;
  border-radius: 4px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #fdfdfd;
  transition: all 0.2s;
}
.vacant-drop-slot.slot-hover {
  background: #e8f5e9;
  border-color: #2e7d32;
}

.drafter-official-block {
  width: 50%;
  text-align: center;
  padding-left: 10px;
}
.drafter-dept-text {
  font-family: 'Moul', cursive;
  font-size: 11px;
  color: #1b5e20;
}
.drafter-office-text {
  font-family: 'Moul', cursive;
  font-size: 10px;
  color: #1b5e20;
  margin-bottom: 2px;
}
.drafter-role-text {
  font-size: 11.5px;
  font-weight: 600;
}
.drafter-sig-img {
  height: 48px;
  object-fit: contain;
  margin: 3px 0;
}
.drafter-name-text {
  font-family: 'Moul', cursive;
  font-size: 12px;
}

/* ZONE 5: FOOTER */
.page-1-footer {
  border-top: 1.5px solid #1b5e20;
  padding-top: 5px;
}
.footer-content-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
  font-size: 7.5px;
  line-height: 1.35;
  color: #1b5e20;
}
.footer-address-col {
  flex: 1.2;
}
.footer-addr-kh {
  font-weight: 600;
  margin-bottom: 2px;
}
.footer-addr-en {
  font-size: 6.8px;
  color: #555;
}
.footer-qr-col {
  display: flex;
  align-items: center;
  gap: 5px;
}
.footer-qr-box {
  width: 32px;
  height: 32px;
  border: 1px dashed #1b5e20;
  border-radius: 2px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ffffff;
}
.footer-contact-col {
  flex: 0.95;
  text-align: right;
  font-size: 7.2px;
}
</style>
