import axios from 'axios';
const APP_API_URL = import.meta.env.VITE_APP_API_URL;

export function apiGetAttendances(params = {}) {
  return axios.get(APP_API_URL + '/manage/attendances', { params });
}

export function apiSaveAttendance(data) {
  return axios.post(APP_API_URL + '/manage/attendances/save', data);
}

export function apiImportAttendances(data) {
  return axios.post(APP_API_URL + '/manage/attendances/import', data);
}

export function apiGetMyAttendances(params = {}) {
  return axios.get(APP_API_URL + '/my-attendances', { params });
}