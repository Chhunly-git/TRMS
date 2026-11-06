import axios from 'axios';

const APP_API_URL = import.meta.env.VITE_APP_API_URL;

// 1. ជម្រើសសម្រាប់បង្កើតឯកសារចេញ (Inbound docs to link + hierarchy approvers)
export function apiGetOutboundOptions() {
  return axios.get(`${APP_API_URL}/outbound-documents/options`);
}

// 2. ទាញយកបញ្ជីឯកសារចេញ (Doing, In Progress, Complete)
export function apiGetOutboundDocuments(params = {}) {
  return axios.get(`${APP_API_URL}/outbound-documents`, { params });
}

// 3. បង្កើតឯកសារចេញ / របាយការណ៍ / កំណត់បង្ហាញ (Standalone ឬភ្ជាប់ឯកសារចូល)
export function apiCreateOutboundDocument(formData) {
  return axios.post(`${APP_API_URL}/outbound-documents`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  });
}

// 4. បង្ហាញព័ត៌មានលម្អិតឯកសារចេញ
export function apiGetOutboundDocument(id) {
  return axios.get(`${APP_API_URL}/outbound-documents/${id}`);
}

// 5. កែប្រែឯកសារចេញ (ពេលនៅជា Draft ឬ Return for Edit)
export function apiUpdateOutboundDocument(id, formData) {
  return axios.post(`${APP_API_URL}/outbound-documents/${id}`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  });
}

// 5b. រក្សាទុកទីតាំង ឬខ្លឹមសារឯកសារ (Quick content & opinion box repositioning)
export function apiUpdateOutboundContent(id, content) {
  return axios.patch(`${APP_API_URL}/outbound-documents/${id}/content`, { content });
}

// 6. សកម្មភាពពិនិត្យឆ្លង (FORWARD, RETURN, DG_APPROVE)
export function apiReviewOutboundAction(id, data) {
  return axios.post(`${APP_API_URL}/outbound-documents/${id}/review-action`, data);
}

// 7. ទាញយកមកវិញ (Recall by Drafter / Sender)
export function apiRecallOutboundDocument(id) {
  return axios.post(`${APP_API_URL}/outbound-documents/${id}/recall`);
}

// 8. លុបឯកសារចេញ (Draft)
export function apiDeleteOutboundDocument(id) {
  return axios.delete(`${APP_API_URL}/outbound-documents/${id}`);
}

function getAuthTokenQuery() {
  const token = localStorage.getItem('token');
  return token ? `?token=${encodeURIComponent(token)}` : '';
}

// 9. URLs for file download & view
export function getOutboundFileDownloadUrl(id) {
  return `${APP_API_URL}/outbound-documents/${id}/download-file${getAuthTokenQuery()}`;
}

export function getOutboundFileViewUrl(id) {
  return `${APP_API_URL}/outbound-documents/${id}/view-file${getAuthTokenQuery()}`;
}

// 10. Structured Composer v2 API Endpoints
export function apiGetOpinionSlots(id) {
  return axios.get(`${APP_API_URL}/outbound-documents/${id}/opinion-slots`);
}

export function apiUpdateOpinionPlacement(id, data) {
  return axios.patch(`${APP_API_URL}/outbound-documents/${id}/opinion-placement`, data);
}

export function apiGetRenderedHtml(id) {
  return axios.get(`${APP_API_URL}/outbound-documents/${id}/rendered-html`);
}
