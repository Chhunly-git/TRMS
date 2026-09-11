import axios from 'axios';

const APP_API_URL = import.meta.env.VITE_APP_API_URL;

// ទាញយកបញ្ជីរបាយការណ៍ប្រចាំសប្តាហ៍ (តាម scope: 'my' ឬ 'subordinates')
export function apiGetWeeklyReports(params = {}) {
  return axios.get(`${APP_API_URL}/weekly-reports`, { params });
}

// ទាញយកស្ថិតិសង្ខេបរបាយការណ៍
export function apiGetWeeklyReportStats(params = {}) {
  return axios.get(`${APP_API_URL}/weekly-reports/stats`, { params });
}

// ទាញយកជម្រើសនាយកដ្ឋាន និងការិយាល័យសម្រាប់ Filter
export function apiGetWeeklyReportFilterOptions() {
  return axios.get(`${APP_API_URL}/weekly-reports/filter-options`);
}

// ទាញយករបាយការណ៍តែមួយតាម ID
export function apiGetWeeklyReport(id) {
  return axios.get(`${APP_API_URL}/weekly-reports/${id}`);
}

// បង្កើតរបាយការណ៍ថ្មី (គាំទ្រទាំង FormData សម្រាប់ Upload ឯកសារ)
export function apiCreateWeeklyReport(data) {
  const isFormData = data instanceof FormData;
  return axios.post(`${APP_API_URL}/weekly-reports`, data, {
    headers: isFormData ? { 'Content-Type': 'multipart/form-data' } : {}
  });
}

// កែប្រែរបាយការណ៍
export function apiUpdateWeeklyReport(id, data) {
  const isFormData = data instanceof FormData;
  // ប្រសិនបើជា FormData ប្រើ POST ជាមួយ method spoofing ឬ POST ធម្មតាព្រោះយើងមាន Route::match(['PUT', 'POST'])
  return axios.post(`${APP_API_URL}/weekly-reports/${id}`, data, {
    headers: isFormData ? { 'Content-Type': 'multipart/form-data' } : {}
  });
}

// ដាក់ជូនរបាយការណ៍ (ពី DRAFT ទៅ SUBMITTED)
export function apiSubmitWeeklyReport(id) {
  return axios.patch(`${APP_API_URL}/weekly-reports/${id}/submit`);
}

// ថ្នាក់ដឹកនាំពិនិត្យ និងដាក់ចំណារ/មតិយោបល់
export function apiReviewWeeklyReport(id, data) {
  return axios.patch(`${APP_API_URL}/weekly-reports/${id}/review`, data);
}

// លុបរបាយការណ៍
export function apiDeleteWeeklyReport(id) {
  return axios.delete(`${APP_API_URL}/weekly-reports/${id}`);
}

// URL សម្រាប់ទាញយកឯកសារភ្ជាប់
export function getAttachmentDownloadUrl(id) {
  return `${APP_API_URL}/weekly-reports/${id}/download-attachment`;
}

// កែប្រែស្ថានភាពកិច្ចការងាររហ័ស
export function apiUpdateWeeklyReportTaskStatus(taskId, data) {
  return axios.patch(`${APP_API_URL}/weekly-reports/tasks/${taskId}/status`, data);
}
