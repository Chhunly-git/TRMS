import axios from 'axios';

const APP_API_URL = import.meta.env.VITE_APP_API_URL;

export function apiGetDepartments(params = {}) {
  return axios.get(APP_API_URL + '/manage/departments', { params });
}

export function apiReadDepartment(id) {
  return axios.get(APP_API_URL + `/manage/departments/read/${id}`);
}

export function apiCreateDepartment(data) {
  return axios.post(APP_API_URL + `/manage/departments/create`, data);
}

export function apiUpdateDepartment(id, data) {
  return axios.put(APP_API_URL + `/manage/departments/update/${id}`, data);
}

export function apiDeleteDepartment(id) {
  return axios.delete(APP_API_URL + `/manage/departments/delete/${id}`);
}