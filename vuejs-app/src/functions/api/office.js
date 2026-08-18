import axios from 'axios';

const APP_API_URL = import.meta.env.VITE_APP_API_URL;

export function apiGetOffices(params = {}) {
  return axios.get(APP_API_URL + '/manage/offices', { params });
}

export function apiGetOfficesByDepartment(departmentId) {
  return axios.get(APP_API_URL + `/manage/offices/by-department/${departmentId}`);
}

export function apiCreateOffice(data) {
  return axios.post(APP_API_URL + `/manage/offices/create`, data);
}

export function apiUpdateOffice(id, data) {
  return axios.put(APP_API_URL + `/manage/offices/update/${id}`, data);
}

export function apiDeleteOffice(id) {
  return axios.delete(APP_API_URL + `/manage/offices/delete/${id}`);
}