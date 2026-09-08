import axios from 'axios';

const APP_API_URL = import.meta.env.VITE_APP_API_URL;

export function apiGetDocumentTemplates(params = {}) {
  return axios.get(APP_API_URL + '/document-templates', { params });
}

export function apiGetManageDocumentTemplates(params = {}) {
  return axios.get(APP_API_URL + '/manage/document-templates', { params });
}

export function apiCreateDocumentTemplate(formData) {
  return axios.post(APP_API_URL + '/manage/document-templates/create', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
}

export function apiUpdateDocumentTemplate(id, formData) {
  return axios.post(APP_API_URL + `/manage/document-templates/update/${id}`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
}

export function apiDeleteDocumentTemplate(id) {
  return axios.delete(APP_API_URL + `/manage/document-templates/delete/${id}`);
}
