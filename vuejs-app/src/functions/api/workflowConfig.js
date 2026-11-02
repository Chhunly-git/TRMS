import axios from 'axios';

const BASE_URL = `${import.meta.env.VITE_API_URL ?? 'http://localhost:8000'}/api/manage/workflow-configs`;
const MY_CONFIG_URL = `${import.meta.env.VITE_API_URL ?? 'http://localhost:8000'}/api/workflow-configs/my-config`;

export const apiGetWorkflowConfigs = (params = {}) => {
  return axios.get(BASE_URL, { params });
};

export const apiGetWorkflowOptions = () => {
  return axios.get(`${BASE_URL}/options`);
};

export const apiGetWorkflowConfig = (userId) => {
  return axios.get(`${BASE_URL}/${userId}`);
};

export const apiSaveWorkflowConfig = (data) => {
  return axios.post(`${BASE_URL}/save`, data);
};

export const apiBatchSaveWorkflowConfigs = (data) => {
  return axios.post(`${BASE_URL}/batch-save`, data);
};

export const apiResetWorkflowConfig = (userId) => {
  return axios.delete(`${BASE_URL}/${userId}`);
};

export const apiGetMyWorkflowConfig = () => {
  return axios.get(MY_CONFIG_URL);
};
