import axios from 'axios';

const APP_API_URL = import.meta.env.VITE_APP_API_URL;

// ទាញយកបញ្ជីកាលវិភាគការងារ
export function apiGetWorkSchedules(params = {}) {
  return axios.get(`${APP_API_URL}/work-schedules`, { params });
}

// ទាញយកស្ថិតិសង្ខេបកាលវិភាគប្រចាំខែ
export function apiGetWorkScheduleSummary(params = {}) {
  return axios.get(`${APP_API_URL}/work-schedules/summary`, { params });
}

// ទាញយកកាលវិភាគតែមួយតាម ID
export function apiGetWorkSchedule(id) {
  return axios.get(`${APP_API_URL}/work-schedules/${id}`);
}

// បង្កើតកាលវិភាគការងារថ្មី
export function apiCreateWorkSchedule(data) {
  return axios.post(`${APP_API_URL}/work-schedules`, data);
}

// កែសម្រួលកាលវិភាគការងារ
export function apiUpdateWorkSchedule(id, data) {
  return axios.put(`${APP_API_URL}/work-schedules/${id}`, data);
}

// កែប្រែស្ថានភាពកាលវិភាគរហ័ស
export function apiUpdateWorkScheduleStatus(id, status) {
  return axios.patch(`${APP_API_URL}/work-schedules/${id}/status`, { status });
}

// លុបកាលវិភាគការងារ
export function apiDeleteWorkSchedule(id) {
  return axios.delete(`${APP_API_URL}/work-schedules/${id}`);
}
