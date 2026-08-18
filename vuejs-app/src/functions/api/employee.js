// src/functions/api/employee.js
import axios from 'axios';

const APP_API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api';

const apiClient = axios.create({
  baseURL: APP_API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Interceptor ភ្ជាប់ Token ស្វ័យប្រវត្តិ
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('token') || localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

const BASE_PATH = '/manage/employees';

// ទាញយកបញ្ជីបុគ្គលិក (Filter, Search, Pagination)
export function apiGetEmployees(params = {}) {
  return apiClient.get(BASE_PATH, { params });
}

// អានព័ត៌មានលម្អិតបុគ្គលិកម្នាក់
export function apiReadEmployee(id) {
  return apiClient.get(`${BASE_PATH}/read/${id}`);
}

// បង្កើតបុគ្គលិកថ្មី
export function apiCreateEmployee(data) {
  return apiClient.post(`${BASE_PATH}/create`, data);
}

// កែសម្រួលព័ត៌មានបុគ្គលិក
export function apiUpdateEmployee(id, data) {
  return apiClient.put(`${BASE_PATH}/update/${id}`, data);
}

// ប្តូរស្ថានភាព (Active / Disabled)
export function apiToggleEmployeeStatus(id) {
  return apiClient.patch(`${BASE_PATH}/toggle-status/${id}`);
}

// លុបទិន្នន័យបុគ្គលិក
export function apiDeleteEmployee(id) {
  return apiClient.delete(`${BASE_PATH}/delete/${id}`);
}