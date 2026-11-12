<template>
  <div v-if="show" class="signature-modal-backdrop" @click.self="closeModal">
    <div class="signature-modal-dialog">
      <div class="signature-modal-content card shadow-lg border-0 rounded-lg">
        <!-- Header -->
        <div class="card-header bg-gradient-primary text-white d-flex align-items-center justify-content-between py-3">
          <h5 class="mb-0 font-weight-bold font-khmer">
            <i class="fas fa-file-signature mr-2"></i> ហត្ថលេខាឌីជីថលផ្ទាល់ខ្លួន (Digital Signature)
          </h5>
          <button type="button" class="close text-white" @click="closeModal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <!-- Body -->
        <div class="card-body p-4 font-khmer">
          <!-- Current Signature Display (if exists) -->
          <div v-if="userStore.signature_image" class="current-signature-box mb-3 p-3 rounded border bg-light text-center position-relative">
            <div class="small text-muted font-weight-bold mb-2">
              <i class="fas fa-check-circle text-success mr-1"></i> ហត្ថលេខាបច្ចុប្បន្នរបស់អ្នក៖
            </div>
            <div class="sig-preview-wrapper d-inline-block p-2 bg-white rounded border">
              <img :src="userStore.signature_image" alt="ហត្ថលេខា" class="sig-preview-img" />
            </div>
            <div class="mt-2">
              <button
                type="button"
                class="btn btn-xs btn-outline-danger"
                :disabled="isDeleting"
                @click="deleteSignature"
              >
                <i class="fas fa-trash-alt mr-1"></i> {{ isDeleting ? 'កំពុងលុប...' : 'លុបហត្ថលេខានេះ' }}
              </button>
            </div>
          </div>

          <!-- Tabs: Upload Image vs Draw Signature -->
          <ul class="nav nav-pills nav-justified mb-3 signature-pills">
            <li class="nav-item">
              <a
                class="nav-link font-weight-bold"
                :class="{ active: activeTab === 'upload' }"
                href="javascript:void(0)"
                @click="activeTab = 'upload'"
              >
                <i class="fas fa-cloud-upload-alt mr-1"></i> បង្ហោះរូបភាពហត្ថលេខា (Upload)
              </a>
            </li>
            <li class="nav-item">
              <a
                class="nav-link font-weight-bold"
                :class="{ active: activeTab === 'draw' }"
                href="javascript:void(0)"
                @click="switchToDrawTab"
              >
                <i class="fas fa-pen-nib mr-1"></i> គូសហត្ថលេខាផ្ទាល់ (Draw)
              </a>
            </li>
          </ul>

          <!-- Tab 1: Upload File -->
          <div v-show="activeTab === 'upload'" class="tab-content-upload">
            <div
              class="upload-dropzone p-4 text-center border-dashed rounded cursor-pointer"
              @click="$refs.fileInput.click()"
              @dragover.prevent
              @drop.prevent="onFileDrop"
            >
              <input
                type="file"
                ref="fileInput"
                class="d-none"
                accept="image/png, image/jpeg, image/jpg, image/webp"
                @change="onFileChange"
              />
              <div v-if="!uploadedPreview">
                <i class="fas fa-file-image fa-3x text-primary mb-2"></i>
                <div class="font-weight-bold text-dark font-15 mb-1">ចុចទីនេះ ឬទម្លាក់រូបភាពហត្ថលេខា</div>
                <div class="small text-muted">គាំទ្រប្រភេទ file: PNG (ណែនាំផ្ទៃថ្លា Transparent), JPG, WEBP (ទំហំអតិបរមា 2MB)</div>
              </div>
              <div v-else class="uploaded-preview-container">
                <img :src="uploadedPreview" class="uploaded-preview-img rounded" alt="Preview" />
                <div class="mt-2 text-success font-weight-bold small">
                  <i class="fas fa-check mr-1"></i> បានជ្រើសរើសរូបភាព៖ {{ uploadedFile?.name }}
                </div>
                <button type="button" class="btn btn-xs btn-outline-secondary mt-1" @click.stop="resetUpload">
                  ជ្រើសរើសរូបភាពផ្សេង
                </button>
              </div>
            </div>
          </div>

          <!-- Tab 2: Draw on Canvas -->
          <div v-show="activeTab === 'draw'" class="tab-content-draw">
            <!-- Canvas Tools -->
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="d-flex align-items-center">
                <span class="small font-weight-bold mr-2 text-muted">ពណ៌ទឹកប៊ិច៖</span>
                <button
                  type="button"
                  class="btn btn-xs mr-1 color-btn"
                  :class="{ active: penColor === '#002060' }"
                  style="background-color: #002060; color: white;"
                  @click="penColor = '#002060'"
                  title="ពណ៌ខៀវខ្មៅ (Blue)"
                >
                  ខៀវ
                </button>
                <button
                  type="button"
                  class="btn btn-xs color-btn"
                  :class="{ active: penColor === '#000000' }"
                  style="background-color: #000000; color: white;"
                  @click="penColor = '#000000'"
                  title="ពណ៌ខ្មៅ (Black)"
                >
                  ខ្មៅ
                </button>
              </div>
              <div>
                <button
                  type="button"
                  class="btn btn-xs btn-outline-danger font-weight-bold"
                  @click="clearCanvas"
                >
                  <i class="fas fa-redo-alt mr-1"></i> លុបគូសឡើងវិញ
                </button>
              </div>
            </div>

            <!-- Canvas Area -->
            <div class="canvas-container border rounded bg-white position-relative">
              <canvas
                ref="sigCanvas"
                width="500"
                height="180"
                class="w-100 sig-canvas"
                @mousedown="startDrawing"
                @mousemove="draw"
                @mouseup="stopDrawing"
                @mouseleave="stopDrawing"
                @touchstart="handleTouchStart"
                @touchmove="handleTouchMove"
                @touchend="stopDrawing"
              ></canvas>
              <div v-if="!hasDrawn" class="canvas-placeholder-text">
                <i class="fas fa-pencil-alt mr-1"></i> សូមប្រើ Mouse ឬ Touch ដើម្បីគូសហត្ថលេខានៅទីនេះ...
              </div>
            </div>
            <small class="text-muted d-block mt-1">
              * គូសហត្ថលេខារបស់អ្នកឱ្យច្បាស់លាស់ ហត្ថលេខានេះនឹងត្រូវយកទៅប្រើលើរបាយការណ៍ និងលិខិតរដ្ឋបាលដោយស្វ័យប្រវត្តិ។
            </small>
          </div>
        </div>

        <!-- Footer -->
        <div class="card-footer bg-white border-top d-flex justify-content-between py-3">
          <button type="button" class="btn btn-outline-secondary font-khmer" @click="closeModal">
            បោះបង់
          </button>
          <button
            type="button"
            class="btn btn-primary font-khmer font-weight-bold shadow-xs px-4"
            :disabled="isSaving || (!uploadedFile && !hasDrawn)"
            @click="saveSignature"
          >
            <i class="fas fa-spinner fa-spin mr-1" v-if="isSaving"></i>
            <i class="fas fa-save mr-1" v-else></i>
            {{ isSaving ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកហត្ថលេខា' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import { useUserStore } from '@/stores/user';
import { apiUpdateSignatureImage, apiDeleteSignatureImage } from '@/functions/api/auth';
import Swal from 'sweetalert2';

defineProps({
  show: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:show', 'saved']);

const userStore = useUserStore();
const activeTab = ref('upload');
const isSaving = ref(false);
const isDeleting = ref(false);

// Upload state
const fileInput = ref(null);
const uploadedFile = ref(null);
const uploadedPreview = ref(null);

// Draw canvas state
const sigCanvas = ref(null);
const isDrawing = ref(false);
const hasDrawn = ref(false);
const penColor = ref('#002060');
let ctx = null;

const closeModal = () => {
  emit('update:show', false);
};

const onFileChange = (e) => {
  const file = e.target.files[0];
  handleSelectedFile(file);
};

const onFileDrop = (e) => {
  const file = e.dataTransfer.files[0];
  handleSelectedFile(file);
};

const handleSelectedFile = (file) => {
  if (!file) return;
  if (!file.type.startsWith('image/')) {
    Swal.fire({ icon: 'warning', title: 'ឯកសារមិនត្រឹមត្រូវ', text: 'សូមជ្រើសរើសរូបភាព (PNG, JPG, WEBP)!' });
    return;
  }
  if (file.size > 2 * 1024 * 1024) {
    Swal.fire({ icon: 'warning', title: 'ទំហំធំពេក', text: 'ទំហំរូបភាពមិនត្រូវលើសពី 2MB ឡើយ!' });
    return;
  }
  uploadedFile.value = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    uploadedPreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const resetUpload = () => {
  uploadedFile.value = null;
  uploadedPreview.value = null;
  if (fileInput.value) fileInput.value.value = '';
};

// Canvas drawing functions
const initCanvas = () => {
  if (!sigCanvas.value) return;
  ctx = sigCanvas.value.getContext('2d');
  ctx.strokeStyle = penColor.value;
  ctx.lineWidth = 2.5;
  ctx.lineCap = 'round';
  ctx.lineJoin = 'round';
};

const switchToDrawTab = () => {
  activeTab.value = 'draw';
  nextTick(() => {
    initCanvas();
  });
};

const startDrawing = (e) => {
  if (!ctx) initCanvas();
  isDrawing.value = true;
  hasDrawn.value = true;
  ctx.strokeStyle = penColor.value;
  ctx.beginPath();
  const rect = sigCanvas.value.getBoundingClientRect();
  const scaleX = sigCanvas.value.width / rect.width;
  const scaleY = sigCanvas.value.height / rect.height;
  ctx.moveTo((e.clientX - rect.left) * scaleX, (e.clientY - rect.top) * scaleY);
};

const draw = (e) => {
  if (!isDrawing.value || !ctx) return;
  const rect = sigCanvas.value.getBoundingClientRect();
  const scaleX = sigCanvas.value.width / rect.width;
  const scaleY = sigCanvas.value.height / rect.height;
  ctx.lineTo((e.clientX - rect.left) * scaleX, (e.clientY - rect.top) * scaleY);
  ctx.stroke();
};

const stopDrawing = () => {
  if (isDrawing.value && ctx) {
    ctx.closePath();
    isDrawing.value = false;
  }
};

const handleTouchStart = (e) => {
  e.preventDefault();
  const touch = e.touches[0];
  startDrawing(touch);
};

const handleTouchMove = (e) => {
  e.preventDefault();
  const touch = e.touches[0];
  draw(touch);
};

const clearCanvas = () => {
  if (!sigCanvas.value || !ctx) return;
  ctx.clearRect(0, 0, sigCanvas.value.width, sigCanvas.value.height);
  hasDrawn.value = false;
};

// Save Signature API Call
const saveSignature = async () => {
  isSaving.value = true;
  try {
    let res;
    if (activeTab.value === 'upload' && uploadedFile.value) {
      const formData = new FormData();
      formData.append('signature_image', uploadedFile.value);
      res = await apiUpdateSignatureImage(formData);
    } else if (activeTab.value === 'draw' && hasDrawn.value && sigCanvas.value) {
      const base64Data = sigCanvas.value.toDataURL('image/png');
      res = await apiUpdateSignatureImage({
        signature_base64: base64Data
      });
    } else {
      Swal.fire({ icon: 'warning', title: 'សូមជ្រើសរើស', text: 'សូមបង្ហោះរូបភាព ឬគូសហត្ថលេខារបស់អ្នកជាមុនសិន!' });
      isSaving.value = false;
      return;
    }

    if (res.data?.success && res.data?.signature_image) {
      userStore.setSignatureImage(res.data.signature_image);
      emit('saved', res.data.signature_image);
      Swal.fire({
        icon: 'success',
        title: 'ជោគជ័យ!',
        text: 'ហត្ថលេខារបស់លោកអ្នកត្រូវបានរក្សាទុកដោយជោគជ័យ និងភ្ជាប់លើរបាយការណ៍ស្វ័យប្រវត្តិ!',
        timer: 1800,
        showConfirmButton: false
      });
      closeModal();
    }
  } catch (err) {
    console.error('Save signature error:', err);
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ!',
      text: err.response?.data?.message || 'មិនអាចរក្សាទុកហត្ថលេខាបានទេ សូមព្យាយាមម្តងទៀត!'
    });
  } finally {
    isSaving.value = false;
  }
};

// Delete Signature API Call
const deleteSignature = async () => {
  const result = await Swal.fire({
    title: 'តើអ្នកប្រាកដទេ?',
    text: 'តើអ្នកចង់លុបហត្ថលេខានេះចេញមែនទេ?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'បាទ/ចាស លុប',
    cancelButtonText: 'បោះបង់',
    confirmButtonColor: '#d33'
  });
  if (!result.isConfirmed) return;

  isDeleting.value = true;
  try {
    const res = await apiDeleteSignatureImage();
    if (res.data?.success) {
      userStore.setSignatureImage(null);
      emit('saved', null);
      Swal.fire({ icon: 'success', title: 'ជោគជ័យ!', text: 'បានលុបហត្ថលេខាដោយជោគជ័យ!', timer: 1500, showConfirmButton: false });
    }
  } catch (err) {
    console.error('Delete signature error:', err);
    Swal.fire({ icon: 'error', title: 'បរាជ័យ!', text: 'មិនអាចលុបហត្ថលេខាបានឡើយ!' });
  } finally {
    isDeleting.value = false;
  }
};
</script>

<style scoped>
.signature-modal-backdrop {
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
  backdrop-filter: blur(2px);
}

.signature-modal-dialog {
  width: 100%;
  max-width: 540px;
  margin: 20px;
}

.signature-modal-content {
  border: none;
  overflow: hidden;
}

.signature-pills .nav-link {
  border: 1px solid #dee2e6;
  border-radius: 6px;
  color: #495057;
  padding: 8px 12px;
  font-size: 13px;
  transition: all 0.2s;
}

.signature-pills .nav-link.active {
  background-color: #0056b3;
  border-color: #0056b3;
  color: #ffffff;
}

.upload-dropzone {
  border: 2px dashed #0056b3;
  background-color: #f8fafc;
  transition: all 0.2s;
}

.upload-dropzone:hover {
  background-color: #f1f5f9;
  border-color: #003d82;
}

.uploaded-preview-img {
  max-height: 110px;
  max-width: 100%;
  object-fit: contain;
  background-color: #ffffff;
  padding: 6px;
  border: 1px solid #e2e8f0;
}

.sig-preview-img {
  max-height: 70px;
  max-width: 200px;
  object-fit: contain;
}

.canvas-container {
  height: 180px;
  border: 1.5px solid #cbd5e1 !important;
  cursor: crosshair;
}

.sig-canvas {
  touch-action: none;
}

.canvas-placeholder-text {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: #94a3b8;
  font-size: 13px;
  pointer-events: none;
  user-select: none;
}

.color-btn {
  width: 48px;
  font-size: 11px;
  border-radius: 4px;
  font-weight: bold;
}
.color-btn.active {
  box-shadow: 0 0 0 2px #fff, 0 0 0 4px #007bff;
}
</style>
