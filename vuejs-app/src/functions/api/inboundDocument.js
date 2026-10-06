import axios from 'axios';

const APP_API_URL = import.meta.env.VITE_APP_API_URL;

// 1. គណនាលេខចូលបន្ទាប់ដោយស្វ័យប្រវត្តិ
export function apiGenerateInboundNumber(params = {}) {
  return axios.get(`${APP_API_URL}/inbound-documents/generate-number`, { params });
}

// ជម្រើសនាយកដ្ឋាន ការិយាល័យ និងមន្ត្រីសម្រាប់ Dispatch
export function apiGetInboundRecipientsOptions() {
  return axios.get(`${APP_API_URL}/inbound-documents/recipients-options`);
}

// 2. ទាញយកបញ្ជីឯកសារចូល
export function apiGetInboundDocuments(params = {}) {
  return axios.get(`${APP_API_URL}/inbound-documents`, { params });
}

// 3. ទាញយកស្ថិតិឯកសារចូល
export function apiGetInboundDocumentStats() {
  return axios.get(`${APP_API_URL}/inbound-documents/stats`);
}

// 4. ទាញយកព័ត៌មានលម្អិតឯកសារចូល
export function apiGetInboundDocument(id) {
  return axios.get(`${APP_API_URL}/inbound-documents/${id}`);
}

// 5. ចុះបញ្ជីឯកសារចូលថ្មីដោយអ្នកទទួល (Receptionist Store)
export function apiCreateInboundDocument(formData) {
  return axios.post(`${APP_API_URL}/inbound-documents`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  });
}

// 6. កែប្រែព័ត៌មានឯកសារចូល
export function apiUpdateInboundDocument(id, formData) {
  return axios.post(`${APP_API_URL}/inbound-documents/${id}`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  });
}

// 7. អ្នកទទួលឯកសារបញ្ជូនទៅកាន់ជំនួយការអគ្គនាយក
export function apiSendToAssistant(id, comment = '') {
  return axios.patch(`${APP_API_URL}/inbound-documents/${id}/send-to-assistant`, { comment });
}

// 8. ជំនួយការចុះលេខជំនួយការអគ្គនាយក និងដាក់ជូនអគ្គនាយក
export function apiAssistantReceiveAndSubmitToDg(id, data) {
  return axios.post(`${APP_API_URL}/inbound-documents/${id}/assistant-receive`, data);
}

// 9. ឯកឧត្តមអគ្គនាយកធ្វើចំណារលើឯកសារ
export function apiDgAnnotate(id, data) {
  return axios.post(`${APP_API_URL}/inbound-documents/${id}/dg-annotate`, data);
}

// 10. ជំនួយការ Scan និងចែកចាយឯកសារទៅកាន់ភាគីពាក់ព័ន្ធ
export function apiAssistantDispatch(id, formData) {
  return axios.post(`${APP_API_URL}/inbound-documents/${id}/assistant-dispatch`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  });
}

// 10B. ចាត់ចែង និងបញ្ជូនបន្តតាមឋានានុក្រម (Cascading Forward / Re-assign Downward)
export function apiForwardInboundDocument(id, data) {
  return axios.post(`${APP_API_URL}/inbound-documents/${id}/forward`, data);
}

// 11. ទទួលជ្រាប និងបញ្ចប់ដំណើរការ (Path A)
export function apiAcknowledgeInboundDocument(id, comment = '') {
  return axios.post(`${APP_API_URL}/inbound-documents/${id}/acknowledge`, { comment });
}

// 12. ទាញយកបញ្ជីបេក្ខភាពថ្នាក់ដឹកនាំសម្រាប់ឆ្លងលិខិតឆ្លើយតប
export function apiGetNextResponseApprovers(id) {
  return axios.get(`${APP_API_URL}/inbound-documents/${id}/next-response-approvers`);
}

// 13. ដាក់ស្នើព្រាងលិខិតឆ្លើយតប (Path B)
export function apiSubmitResponseDraft(id, formData) {
  return axios.post(`${APP_API_URL}/inbound-documents/${id}/submit-response`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  });
}

// 14. ដំណើរការឆ្លងលិខិតឆ្លើយតប (Forward, Return, DG Approve)
export function apiProcessResponseAction(id, formData) {
  return axios.post(`${APP_API_URL}/inbound-documents/${id}/response-action`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  });
}

// 15. ទាញយកឯកសារដើម
export function getOriginalDownloadUrl(id) {
  return `${APP_API_URL}/inbound-documents/${id}/download-original`;
}

// 16. ទាញយកឯកសារមានចំណារអគ្គនាយក
export function getAnnotatedDownloadUrl(id) {
  return `${APP_API_URL}/inbound-documents/${id}/download-annotated`;
}

// 17. ទាញយកឯកសារឆ្លើយតប
export function getResponseDownloadUrl(responseId) {
  return `${APP_API_URL}/inbound-documents/responses/${responseId}/download`;
}

// 18. លុបឯកសារចូល
export function apiDeleteInboundDocument(id) {
  return axios.delete(`${APP_API_URL}/inbound-documents/${id}`);
}

// 19. ទាញយកទិន្នន័យសម្រាប់បោះពុម្ពសន្លឹកតាមដានឯកសារ (Routing Slip Data)
export function apiGetRoutingSlipData(id) {
  return axios.get(`${APP_API_URL}/inbound-documents/${id}/routing-slip-data`);
}

// 20. កំណត់ ឬកែសម្រួលព័ត៌មាន Telegram Chat ID
export function apiUpdateTelegramSettings(data) {
  return axios.post(`${APP_API_URL}/inbound-documents/telegram-settings`, data);
}

// 21. សាកល្បងផ្ញើសារតាម Telegram Bot (Test Connection)
export function apiTestTelegramConnection(data = {}) {
  return axios.post(`${APP_API_URL}/inbound-documents/test-telegram`, data);
}
