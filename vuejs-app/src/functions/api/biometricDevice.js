import axios from 'axios';

const APP_API_URL = import.meta.env.VITE_APP_API_URL;

export function apiGetBiometricDevices() {
  return axios.get(`${APP_API_URL}/manage/biometric-devices`);
}

export function apiCreateBiometricDevice(data) {
  return axios.post(`${APP_API_URL}/manage/biometric-devices`, data);
}

export function apiUpdateBiometricDevice(id, data) {
  return axios.put(`${APP_API_URL}/manage/biometric-devices/${id}`, data);
}

export function apiDeleteBiometricDevice(id) {
  return axios.delete(`${APP_API_URL}/manage/biometric-devices/${id}`);
}

export function apiTestBiometricDeviceConnection(id) {
  return axios.post(`${APP_API_URL}/manage/biometric-devices/${id}/test`);
}

export function apiSyncBiometricDevice(id, date) {
  return axios.post(`${APP_API_URL}/manage/biometric-devices/${id}/sync`, { date });
}

export function apiGetBiometricDeviceLogs(params = {}) {
  return axios.get(`${APP_API_URL}/manage/biometric-devices/logs/list`, { params });
}

export function apiGetBiometricSetupInfo() {
  return axios.get(`${APP_API_URL}/manage/biometric-devices/setup-info`);
}
