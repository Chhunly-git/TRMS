import axios from 'axios';

const BASE_URL = `${import.meta.env.VITE_API_URL ?? 'http://localhost:8000'}/api/working-groups`;

export const apiGetWorkingGroups = (params = {}) => {
  return axios.get(BASE_URL, { params });
};

export const apiGetAllWorkingGroups = () => {
  return axios.get(`${BASE_URL}/all`);
};

export const apiGetWorkingGroup = (id) => {
  return axios.get(`${BASE_URL}/${id}`);
};

export const apiCreateWorkingGroup = (data) => {
  return axios.post(BASE_URL, data);
};

export const apiUpdateWorkingGroup = (id, data) => {
  return axios.put(`${BASE_URL}/${id}`, data);
};

export const apiDeleteWorkingGroup = (id) => {
  return axios.delete(`${BASE_URL}/${id}`);
};

export const apiAddWorkingGroupMember = (id, data) => {
  return axios.post(`${BASE_URL}/${id}/members`, data);
};

export const apiRemoveWorkingGroupMember = (id, memberId) => {
  return axios.delete(`${BASE_URL}/${id}/members/${memberId}`);
};
