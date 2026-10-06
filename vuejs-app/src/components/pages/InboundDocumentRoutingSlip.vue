<template>
  <div class="content-wrapper routing-slip-page" style="background-color: #f4f6f9; min-height: 1000px;">
    <!-- Action Header (Hidden on print) -->
    <section class="content-header print-hide">
      <div class="container-fluid">
        <div class="row align-items-center mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark font-weight-bold" style="font-size: 20px;">
              <i class="fas fa-file-invoice text-primary mr-2"></i>
              សន្លឹកតាមដានឯកសារចូល (Routing Slip)
            </h1>
          </div>
          <div class="col-sm-6 text-right">
            <router-link to="/inbound-documents" class="btn btn-secondary mr-2 shadow-sm">
              <i class="fas fa-arrow-left mr-1"></i> ត្រឡប់ក្រោយ
            </router-link>
            <button @click="fetchRoutingSlipData" class="btn btn-outline-info mr-2 shadow-sm" :disabled="loading">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i> ផ្ទុកឡើងវិញ
            </button>
            <button @click="printSlip" class="btn btn-primary px-4 shadow-sm" :disabled="loading">
              <i class="fas fa-print mr-1"></i> បោះពុម្ពសន្លឹកតាមដាន (Print A4)
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Printable A4 Area -->
    <section class="content">
      <div class="container-fluid d-flex justify-content-center pb-5">
        <div v-if="loading" class="text-center py-5">
          <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
          <p class="text-muted font-khmer">កំពុងទាញយកទិន្នន័យសន្លឹកតាមដានឯកសារ...</p>
        </div>

        <div v-else-if="!doc.id" class="text-center py-5">
          <div class="alert alert-danger font-khmer">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            មិនអាចស្វែងរកទិន្នន័យឯកសារចូលនេះឡើយ!
          </div>
        </div>

        <div v-else class="a4-sheet bg-white p-4 shadow-sm font-khmer">
          <!-- 1. Header: Royal Kingdom & Trust Regulator -->
          <div class="row align-items-start mb-3 pb-2 border-bottom">
            <!-- Left: Trust Regulator -->
            <div class="col-4 text-center">
              <img :src="ministryLogo" alt="Trust Regulator Logo" class="tr-logo mb-1" />
              <div class="font-weight-bold text-dark" style="font-size: 13px; line-height: 1.3;">និយ័តករអាណាព្យាបាល</div>
              <div class="text-muted text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">TRUST REGULATOR</div>
              <div class="text-muted mt-1" style="font-size: 11px;">
                លេខទូទៅ៖ <b class="text-dark">{{ doc.general_inbound_number || '---' }}</b>
              </div>
              <div v-if="doc.dg_inbound_number" class="text-primary font-weight-bold" style="font-size: 11.5px;">
                លេខ ឯ.ឧ អគ្គនាយក៖ <code>{{ doc.dg_inbound_number }}</code>
              </div>
            </div>

            <!-- Center: Kingdom of Cambodia -->
            <div class="col-5 text-center pt-2">
              <div class="font-weight-bold" style="font-size: 15px; color: #0f4c81;">ព្រះរាជាណាចក្រកម្ពុជា</div>
              <div class="font-weight-bold" style="font-size: 13px; color: #0f4c81;">ជាតិ សាសនា ព្រះមហាក្សត្រ</div>
              <div class="mt-1">
                <svg width="60" height="12" viewBox="0 0 60 12" fill="none">
                  <path d="M0 6C15 6 15 11 30 11C45 11 45 6 60 6C45 6 45 1 30 1C15 1 15 6 0 6Z" fill="#b8860b"/>
                </svg>
              </div>
            </div>

            <!-- Right: QR Code for Verification & Mobile Tracking -->
            <div class="col-3 text-center">
              <div class="qr-container d-inline-block p-1 border rounded bg-white shadow-2xs">
                <img v-if="qrDataUrl" :src="qrDataUrl" alt="Tracking QR Code" class="img-fluid" style="width: 85px; height: 85px;" />
                <div v-else class="text-muted" style="width: 85px; height: 85px; line-height: 85px; font-size: 10px;">
                  កំពុងបង្កើត QR...
                </div>
              </div>
              <div class="text-muted mt-1" style="font-size: 9.5px; line-height: 1.2;">
                <div>ស្កេនតាមដានឯកសារ</div>
                <div class="text-monospace">SCAN TO TRACK</div>
              </div>
            </div>
          </div>

          <!-- Document Title -->
          <div class="text-center my-3">
            <h4 class="font-weight-bold m-0" style="color: #1a365d; font-size: 18px; letter-spacing: 0.5px;">
              សន្លឹកតាមដានឯកសារចូល
            </h4>
            <div class="text-muted text-uppercase" style="font-size: 11px; letter-spacing: 1px;">
              INBOUND DOCUMENT ROUTING SLIP
            </div>
          </div>

          <!-- 2. Section 1: ព័ត៌មានលម្អិតឯកសារ (Document Info) -->
          <div class="slip-box mb-3">
            <div class="slip-box-header">
              <i class="fas fa-info-circle mr-1"></i> ១. ព័ត៌មានលម្អិតនៃឯកសារចូល (Document Information)
            </div>
            <div class="slip-box-body p-2">
              <div class="row no-gutters mb-1">
                <div class="col-6 pr-2">
                  <span class="info-label">ស្ថាប័ន/ក្រុមហ៊ុនបញ្ជូន៖</span>
                  <span class="info-val font-weight-bold">{{ doc.sender_organization || '---' }}</span>
                </div>
                <div class="col-6 pl-2">
                  <span class="info-label">កាលបរិច្ឆេទទទួល៖</span>
                  <span class="info-val">{{ formatDate(doc.received_date) }} ម៉ោង {{ doc.received_time || '---' }}</span>
                </div>
              </div>

              <div class="row no-gutters mb-1">
                <div class="col-6 pr-2">
                  <span class="info-label">លេខលិខិតដើម៖</span>
                  <span class="info-val text-primary font-weight-bold">{{ doc.external_reference_number || '---' }}</span>
                  <span v-if="doc.external_document_date" class="text-muted ml-1" style="font-size: 11px;">
                    (ចុះថ្ងៃទី {{ formatDate(doc.external_document_date) }})
                  </span>
                </div>
                <div class="col-6 pl-2">
                  <span class="info-label">អ្នកនាំសារ៖</span>
                  <span class="info-val">{{ doc.deliverer_name || '---' }} <span v-if="doc.deliverer_phone" class="text-muted">({{ doc.deliverer_phone }})</span></span>
                </div>
              </div>

              <div class="row no-gutters mb-1">
                <div class="col-4 pr-1">
                  <span class="info-label">ប្រភេទឯកសារ៖</span>
                  <span class="info-val">{{ formatDocType(doc.document_type) }}</span>
                </div>
                <div class="col-4 px-1">
                  <span class="info-label">កម្រិតបន្ទាន់៖</span>
                  <span class="badge" :class="urgencyBadgeClass(doc.urgency)">
                    {{ formatUrgency(doc.urgency) }}
                  </span>
                </div>
                <div class="col-4 pl-1">
                  <span class="info-label">កាលកំណត់ (Deadline)៖</span>
                  <span v-if="doc.deadline" class="badge badge-warning text-dark font-weight-bold px-2 py-1">
                    <i class="far fa-clock mr-1"></i>{{ formatDate(doc.deadline) }}
                  </span>
                  <span v-else class="text-muted">គ្មាន</span>
                </div>
              </div>

              <div class="row no-gutters mt-2 pt-2 border-top">
                <div class="col-12">
                  <span class="info-label">កម្មវត្ថុ (Subject)៖</span>
                  <div class="info-val font-weight-bold text-dark mt-1 p-2 bg-light rounded" style="line-height: 1.5; font-size: 12.5px;">
                    {{ doc.title }}
                  </div>
                </div>
              </div>

              <div v-if="doc.receptionist_notes" class="row no-gutters mt-1">
                <div class="col-12">
                  <span class="info-label">កំណត់ចំណាំអ្នកទទួល៖</span>
                  <span class="info-val text-muted italic">{{ doc.receptionist_notes }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 3. Section 2: ចំណារអគ្គនាយក (DG Annotation) -->
          <div class="slip-box mb-3 dg-box">
            <div class="slip-box-header dg-header">
              <i class="fas fa-stamp mr-1"></i> ២. ចំណារ និងការណែនាំរបស់ ឯកឧត្តមអគ្គនាយក (DG Annotation & Directions)
            </div>
            <div class="slip-box-body p-3 bg-white">
              <div class="row mb-2">
                <div class="col-8">
                  <span class="info-label text-dark">ចំណារឯកឧត្តមអគ្គនាយក៖</span>
                  <div class="dg-annotation-text mt-1 p-2 rounded">
                    « {{ doc.dg_annotation || 'មិនទាន់មានចំណារ' }} »
                  </div>
                </div>
                <div class="col-4 border-left pl-3">
                  <div class="mb-2">
                    <span class="info-label d-block mb-1">ប្រភេទចំណារ៖</span>
                    <span v-if="doc.is_response_required" class="badge badge-danger px-2 py-1" style="font-size: 11px;">
                      <i class="fas fa-reply mr-1"></i> តម្រូវឱ្យឆ្លើយតប (Required)
                    </span>
                    <span v-else class="badge badge-success px-2 py-1" style="font-size: 11px;">
                      <i class="fas fa-check-circle mr-1"></i> សម្រាប់ជ្រាប (For Info)
                    </span>
                  </div>
                  <div>
                    <span class="info-label d-block">កាលបរិច្ឆេទចំណារ៖</span>
                    <span class="info-val font-weight-bold">
                      {{ doc.dg_annotated_at ? formatDateTime(doc.dg_annotated_at) : '---' }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="row pt-2 border-top mt-2">
                <div class="col-8">
                  <span class="info-label">ការចាត់ចែងចែកចាយដំបូង៖</span>
                  <span class="info-val font-weight-bold text-primary">{{ formatTarget(doc) }}</span>
                </div>
                <div class="col-4 border-left pl-3">
                  <span class="info-label">ស្ថានភាពបច្ចុប្បន្ន៖</span>
                  <span class="badge" :class="statusBadgeClass(doc.status)">
                    {{ formatStatus(doc.status) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- 4. Section 3: ប្រវត្តិនៃការចាត់ចែង និងលំហូរឯកសារ (Routing Trail & Hierarchy Movements) -->
          <div class="slip-box mb-3">
            <div class="slip-box-header">
              <i class="fas fa-route mr-1"></i> ៣. កំណត់ត្រានៃការចាត់ចែង និងលំហូរឯកសារ (Routing Trail & Movement History)
            </div>
            <div class="slip-box-body p-0">
              <table class="table table-bordered table-sm m-0 print-table text-center" style="font-size: 11px;">
                <thead class="bg-light">
                  <tr class="font-weight-bold">
                    <th style="width: 35px;">ល.រ</th>
                    <th style="width: 115px;">កាលបរិច្ឆេទ & ម៉ោង</th>
                    <th style="width: 140px;">អ្នកចាត់ចែង / មន្ត្រី</th>
                    <th style="width: 110px;">សកម្មភាព</th>
                    <th class="text-left">ការណែនាំបន្ថែម / ទៅកាន់ភាគីពាក់ព័ន្ធ</th>
                    <th style="width: 70px;">ហត្ថលេខា</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!doc.movements || doc.movements.length === 0">
                    <td colspan="6" class="text-center py-2 text-muted">មិនទាន់មានកំណត់ត្រាលំហូរឯកសារឡើយ</td>
                  </tr>
                  <tr v-else v-for="(m, idx) in doc.movements" :key="m.id">
                    <td>{{ idx + 1 }}</td>
                    <td>{{ formatDateTime(m.created_at) }}</td>
                    <td class="font-weight-bold text-dark">
                      {{ m.user?.name_kh || m.user?.name || '---' }}
                      <div v-if="m.user?.position" class="text-muted" style="font-size: 9.5px;">
                        {{ m.user.position.title_kh }}
                      </div>
                    </td>
                    <td>
                      <span class="badge badge-light border">
                        {{ formatActionKh(m.action) }}
                      </span>
                    </td>
                    <td class="text-left px-2">
                      <div style="line-height: 1.3;">{{ m.comment || '---' }}</div>
                    </td>
                    <td class="text-muted" style="font-size: 9px; vertical-align: bottom;">
                      <i>(ចុះហត្ថលេខា)</i>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- 5. Section 4: ស្ថានភាពលិខិតឆ្លើយតប (Response Status - If any) -->
          <div v-if="doc.latest_response" class="slip-box mb-3">
            <div class="slip-box-header">
              <i class="fas fa-reply-all mr-1"></i> ៤. ស្ថានភាពលិខិតឆ្លើយតប (Response Status)
            </div>
            <div class="slip-box-body p-2">
              <div class="row no-gutters">
                <div class="col-6 pr-2">
                  <span class="info-label">ចំណងជើងព្រាងលិខិត៖</span>
                  <span class="info-val font-weight-bold">{{ doc.latest_response.title }}</span>
                </div>
                <div class="col-3 px-1">
                  <span class="info-label">អ្នករៀបចំ៖</span>
                  <span class="info-val">{{ doc.latest_response.drafted_by_user?.name_kh || '---' }}</span>
                </div>
                <div class="col-3 pl-2">
                  <span class="info-label">ស្ថានភាព៖</span>
                  <span class="badge badge-info">{{ doc.latest_response.status }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 6. Section 5: Signature Blocks -->
          <div class="signature-section mt-4 pt-3">
            <div class="row text-center font-khmer" style="font-size: 11.5px;">
              <!-- Receptionist -->
              <div class="col-4">
                <div class="text-muted">បានទទួល និងចុះបញ្ជី</div>
                <div class="font-weight-bold mt-1">អ្នកទទួលឯកសារ</div>
                <div class="signature-space"></div>
                <div class="font-weight-bold text-dark">
                  {{ doc.registered_by_user?.name_kh || doc.registered_by_user?.name || '.................................' }}
                </div>
                <div class="text-muted" style="font-size: 10px;">
                  ថ្ងៃទី {{ formatDate(doc.received_date) }}
                </div>
              </div>

              <!-- DG Assistant -->
              <div class="col-4">
                <div class="text-muted">បានពិនិត្យ និងដាក់ជូន</div>
                <div class="font-weight-bold mt-1">ជំនួយការអគ្គនាយក</div>
                <div class="signature-space"></div>
                <div class="font-weight-bold text-dark">
                  {{ doc.assistant?.name_kh || doc.assistant?.name || '.................................' }}
                </div>
                <div class="text-muted" style="font-size: 10px;">
                  ថ្ងៃទី {{ doc.dg_received_date ? formatDate(doc.dg_received_date) : '...... / ...... / 20......' }}
                </div>
              </div>

              <!-- Responsible Unit Head -->
              <div class="col-4">
                <div class="text-muted">បានទទួល និងចាត់ចែងអនុវត្ត</div>
                <div class="font-weight-bold mt-1">ប្រធានអង្គភាពទទួលបន្ទុក</div>
                <div class="signature-space"></div>
                <div class="font-weight-bold text-dark">
                  {{ doc.target_user?.name_kh || '.................................' }}
                </div>
                <div class="text-muted" style="font-size: 10px;">
                  ថ្ងៃទី ...... ខែ ...... ឆ្នាំ២០......
                </div>
              </div>
            </div>
          </div>

          <!-- Print Footer -->
          <div class="slip-footer mt-4 pt-2 border-top d-flex justify-content-between align-items-center text-muted" style="font-size: 9.5px;">
            <div>
              <span>ប្រព័ន្ធគ្រប់គ្រងឯកសារ និយ័តករអាណាព្យាបាល (TRMS) | បោះពុម្ពនៅ៖ {{ printGeneratedAt }}</span>
            </div>
            <div>
              <span class="text-monospace">ID: #{{ doc.id }} - {{ doc.general_inbound_number }}</span>
            </div>
          </div>

        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import QRCode from 'qrcode';
import { apiGetRoutingSlipData } from '@/functions/api/inboundDocument';
import ministryLogo from '@/assets/images/logoImage.webp';

const route = useRoute();
const doc = ref({});
const qrDataUrl = ref('');
const loading = ref(false);
const printGeneratedAt = ref('');

async function fetchRoutingSlipData() {
  const docId = route.params.id;
  if (!docId) return;

  loading.value = true;
  try {
    const res = await apiGetRoutingSlipData(docId);
    doc.value = res.data.document || {};
    printGeneratedAt.value = res.data.generated_at || new Date().toLocaleString('en-GB');

    // Generate QR Code offline using the qrcode package
    const trackingUrl = res.data.tracking_url || window.location.href;
    qrDataUrl.value = await QRCode.toDataURL(trackingUrl, {
      width: 140,
      margin: 1,
      color: {
        dark: '#002b49',
        light: '#ffffff',
      },
      errorCorrectionLevel: 'M',
    });
  } catch (err) {
    console.error('Error fetching routing slip data:', err);
  } finally {
    loading.value = false;
  }
}

function printSlip() {
  window.print();
}

function formatDate(dateStr) {
  if (!dateStr) return '---';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;
  return d.toLocaleDateString('en-GB');
}

function formatDateTime(dateTimeStr) {
  if (!dateTimeStr) return '---';
  const d = new Date(dateTimeStr);
  if (isNaN(d.getTime())) return dateTimeStr;
  return `${d.toLocaleDateString('en-GB')} ${d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })}`;
}

function formatDocType(type) {
  const map = {
    LETTER: 'លិខិត',
    REPORT: 'របាយការណ៍',
    INVITATION: 'លិខិតអញ្ជើញ',
    PROPOSAL: 'សំណើសុំ',
    OTHER: 'ផ្សេងៗ',
  };
  return map[type] || type || '---';
}

function formatUrgency(urgency) {
  const map = {
    NORMAL: 'ធម្មតា',
    URGENT: 'បន្ទាន់',
    VERY_URGENT: 'បន្ទាន់បំផុត',
  };
  return map[urgency] || urgency || 'ធម្មតា';
}

function urgencyBadgeClass(urgency) {
  if (urgency === 'VERY_URGENT') return 'badge-danger';
  if (urgency === 'URGENT') return 'badge-warning text-dark';
  return 'badge-secondary';
}

function formatStatus(status) {
  const map = {
    RECEPTION_DRAFT: 'ព្រាងនៅកន្លែងទទួល',
    SUBMITTED_TO_ASSISTANT: 'បានបញ្ជូនទៅជំនួយការ',
    SUBMITTED_TO_DG: 'ដាក់ជូនឯកឧត្តមអគ្គនាយក',
    DG_ANNOTATED: 'មានចំណារអគ្គនាយក',
    DISPATCHED: 'បានចែកចាយទៅអង្គភាព',
    IN_RESPONSE_PROGRESS: 'កំពុងរៀបចំឆ្លើយតប',
    COMPLETED: 'បានបញ្ចប់ពេញលេញ',
    CANCELLED: 'បានបោះបង់',
  };
  return map[status] || status || '---';
}

function statusBadgeClass(status) {
  if (status === 'COMPLETED') return 'badge-success';
  if (status === 'IN_RESPONSE_PROGRESS') return 'badge-info';
  if (status === 'DISPATCHED') return 'badge-primary';
  if (status === 'DG_ANNOTATED') return 'badge-warning text-dark';
  return 'badge-secondary';
}

function formatTarget(document) {
  if (!document) return '---';
  if (document.target_type === 'OFFICER' && document.target_user) {
    return 'មន្ត្រី៖ ' + (document.target_user.name_kh || document.target_user.name);
  }
  if (document.target_type === 'OFFICE' && document.target_office) {
    return 'ការិយាល័យ៖ ' + document.target_office.name_kh;
  }
  if (document.target_type === 'DEPARTMENT' && document.target_department) {
    return 'នាយកដ្ឋាន៖ ' + document.target_department.name_kh;
  }
  return 'មិនទាន់បញ្ជាក់';
}

function formatActionKh(action) {
  const map = {
    REGISTERED: 'ចុះបញ្ជីឯកសារ',
    FORWARDED_TO_ASSISTANT: 'បញ្ជូនទៅជំនួយការ',
    ASSISTANT_RECEIVED: 'ជំនួយការចុះលេខ DG',
    DG_ANNOTATED: 'អគ្គនាយកធ្វើចំណារ',
    DISPATCHED: 'ចែកចាយឯកសារ',
    FORWARDED: 'ចាត់ចែងបន្ត',
    ACKNOWLEDGED: 'ទទួលជ្រាប',
    RESPONSE_DRAFTED: 'ដាក់ស្នើព្រាងឆ្លើយតប',
    RESPONSE_FORWARDED: 'បញ្ជូនលិខិតឆ្លងបន្ត',
    RESPONSE_RETURNED: 'បញ្ជូនត្រឡប់កែសម្រួល',
    RESPONSE_APPROVED: 'អគ្គនាយកឯកភាព',
  };
  return map[action] || action;
}

onMounted(() => {
  fetchRoutingSlipData();
});
</script>

<style scoped>
.font-khmer {
  font-family: 'Kantumruy Pro', 'Khmer OS Battambang', 'Segoe UI', sans-serif !important;
}

.tr-logo {
  width: 58px;
  height: 58px;
  object-fit: contain;
}

.a4-sheet {
  width: 210mm;
  min-height: 297mm;
  border-radius: 4px;
}

.slip-box {
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  overflow: hidden;
}

.slip-box-header {
  background-color: #f1f5f9;
  padding: 5px 10px;
  font-weight: bold;
  font-size: 11.5px;
  color: #1e293b;
  border-bottom: 1px solid #e2e8f0;
}

.info-label {
  color: #64748b;
  font-size: 11px;
  margin-right: 4px;
}

.info-val {
  font-size: 11.5px;
}

.dg-box {
  border: 1.5px solid #2563eb;
  background-color: #f8fafc;
}

.dg-header {
  background-color: #1e40af;
  color: white;
}

.dg-annotation-text {
  background-color: #eff6ff;
  border-left: 3px solid #3b82f6;
  font-style: italic;
  font-weight: 600;
  color: #1e3a8a;
  font-size: 13px;
  line-height: 1.5;
}

.signature-space {
  height: 60px;
}

.print-table th,
.print-table td {
  padding: 4px 6px !important;
  vertical-align: middle !important;
}

/* Print Specific Rules */
@media print {
  .print-hide {
    display: none !important;
  }

  body, html {
    background-color: white !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .routing-slip-page {
    background-color: white !important;
    padding: 0 !important;
    margin: 0 !important;
    min-height: auto !important;
  }

  .a4-sheet {
    width: 100% !important;
    min-height: auto !important;
    box-shadow: none !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  @page {
    size: A4 portrait;
    margin: 12mm 12mm 15mm 12mm;
  }

  .slip-box,
  .signature-section {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  table tr {
    page-break-inside: avoid !important;
  }
}
</style>
