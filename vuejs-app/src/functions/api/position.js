import axios from 'axios';

const APP_API_URL = import.meta.env.VITE_APP_API_URL;

export function apiGetPositions(params = {}) {
  return axios.get(APP_API_URL + '/manage/positions', { params });
}

export function apiReadPosition(id) {
  return axios.get(APP_API_URL + `/manage/positions/read/${id}`);
}

export function apiCreatePosition(data) {
  return axios.post(APP_API_URL + `/manage/positions/create`, data);
}

export function apiUpdatePosition(id, data) {
  return axios.put(APP_API_URL + `/manage/positions/update/${id}`, data);
}

export function apiDeletePosition(id) {
  return axios.delete(APP_API_URL + `/manage/positions/delete/${id}`);
}