import axios from 'axios';

const APP_API_URL = import.meta.env.VITE_APP_API_URL;

// ទាញយកបញ្ជីពាក្យសុំច្បាប់ (scope: 'my', 'pending_review', 'all')
export function apiGetLeaveRequests(params = {}) {
  return axios.get(`${APP_API_URL}/leave-requests`, { params });
}

// ទាញយកស្ថិតិសង្ខេបពាក្យសុំច្បាប់
export function apiGetLeaveStats(params = {}) {
  return axios.get(`${APP_API_URL}/leave-requests/stats`, { params });
}

// ទាញយកបញ្ជីថ្នាក់ដឹកនាំដែលមានសិទ្ធិទទួលពិនិត្យបន្ត ព្រមទាំងស្ថានភាពឈប់សម្រាករបស់គាត់
export function apiGetNextApproverCandidates(params = {}) {
  return axios.get(`${APP_API_URL}/leave-requests/next-approvers`, { params });
}

// ទាញយកព័ត៌មានលម្អិតនៃពាក្យសុំច្បាប់តែមួយ
export function apiGetLeaveRequest(id) {
  return axios.get(`${APP_API_URL}/leave-requests/${id}`);
}

// បង្កើតពាក្យសុំច្បាប់ថ្មី (គាំទ្រ FormData សម្រាប់ Upload ឯកសារ)
export function apiCreateLeaveRequest(data) {
  const isFormData = data instanceof FormData;
  return axios.post(`${APP_API_URL}/leave-requests`, data, {
    headers: isFormData ? { 'Content-Type': 'multipart/form-data' } : {}
  });
}

// កែប្រែពាក្យសុំច្បាប់
export function apiUpdateLeaveRequest(id, data) {
  const isFormData = data instanceof FormData;
  return axios.post(`${APP_API_URL}/leave-requests/${id}`, data, {
    headers: isFormData ? { 'Content-Type': 'multipart/form-data' } : {}
  });
}

// ដាក់ជូនពាក្យសុំច្បាប់
export function apiSubmitLeaveRequest(id, data = {}) {
  return axios.patch(`${APP_API_URL}/leave-requests/${id}/submit`, data);
}

// ថ្នាក់ដឹកនាំធ្វើចំណារ និងអនុវត្តសកម្មភាព (FORWARD, APPROVE, REJECT, RETURN)
export function apiActionLeaveRequest(id, data) {
  return axios.post(`${APP_API_URL}/leave-requests/${id}/action`, data);
}

// មន្ត្រីបោះបង់ពាក្យសុំច្បាប់ផ្ទាល់ខ្លួន
export function apiCancelLeaveRequest(id) {
  return axios.post(`${APP_API_URL}/leave-requests/${id}/cancel`);
}

// លុបពាក្យសុំច្បាប់ (សម្រាប់តែ DRAFT)
export function apiDeleteLeaveRequest(id) {
  return axios.delete(`${APP_API_URL}/leave-requests/${id}`);
}

// URL សម្រាប់ទាញយកឯកសារភ្ជាប់
export function getLeaveAttachmentDownloadUrl(id) {
  return `${APP_API_URL}/leave-requests/${id}/download-attachment`;
}
