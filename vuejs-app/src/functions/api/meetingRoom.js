import axios from 'axios';

const APP_API_URL = import.meta.env.VITE_APP_API_URL;

// ==================== MEETING ROOMS API ====================

// ទាញយកបញ្ជីបន្ទប់ប្រជុំ
export function apiGetMeetingRooms(params = {}) {
  return axios.get(`${APP_API_URL}/meeting-rooms`, { params });
}

// ពិនិត្យភាពទំនេររបស់បន្ទប់ប្រជុំតាមចន្លោះពេល
export function apiCheckRoomAvailability(params = {}) {
  return axios.get(`${APP_API_URL}/meeting-rooms/availability`, { params });
}

// ទាញយកព័ត៌មានបន្ទប់ប្រជុំតែមួយ
export function apiGetMeetingRoom(id) {
  return axios.get(`${APP_API_URL}/meeting-rooms/${id}`);
}

// បង្កើតបន្ទប់ប្រជុំថ្មី (Admin/Manager)
export function apiCreateMeetingRoom(data) {
  return axios.post(`${APP_API_URL}/meeting-rooms`, data);
}

// កែសម្រួលបន្ទប់ប្រជុំ (Admin/Manager)
export function apiUpdateMeetingRoom(id, data) {
  return axios.put(`${APP_API_URL}/meeting-rooms/${id}`, data);
}

// លុបបន្ទប់ប្រជុំ (Admin/Manager)
export function apiDeleteMeetingRoom(id) {
  return axios.delete(`${APP_API_URL}/meeting-rooms/${id}`);
}

// ==================== ROOM BOOKINGS API ====================

// ទាញយកបញ្ជីការស្នើសុំកក់បន្ទប់ទាំងអស់ (Admin/Manager)
export function apiGetRoomBookings(params = {}) {
  return axios.get(`${APP_API_URL}/room-bookings`, { params });
}

// ទាញយកកាលវិភាគបន្ទប់ប្រជុំ (Timetable) សម្រាប់ប្រតិទិន
export function apiGetRoomTimetable(params = {}) {
  return axios.get(`${APP_API_URL}/room-bookings/timetable`, { params });
}

// ទាញយកបញ្ជីការកក់របស់ User បច្ចុប្បន្ន
export function apiGetMyBookings(params = {}) {
  return axios.get(`${APP_API_URL}/room-bookings/my-bookings`, { params });
}

// ទាញយកព័ត៌មានការកក់តែមួយ
export function apiGetRoomBooking(id) {
  return axios.get(`${APP_API_URL}/room-bookings/${id}`);
}

// ស្នើសុំកក់បន្ទប់ប្រជុំថ្មី
export function apiCreateRoomBooking(data) {
  return axios.post(`${APP_API_URL}/room-bookings`, data);
}

// កែសម្រួលការស្នើសុំកក់បន្ទប់
export function apiUpdateRoomBooking(id, data) {
  return axios.put(`${APP_API_URL}/room-bookings/${id}`, data);
}

// ស្នើសុំបោះបង់/លុបចោលការកក់ (Cancel)
export function apiCancelRoomBooking(id, data = {}) {
  return axios.post(`${APP_API_URL}/room-bookings/${id}/cancel`, data);
}

// លុបកំណត់ត្រាកក់បន្ទប់ (Delete)
export function apiDeleteRoomBooking(id) {
  return axios.delete(`${APP_API_URL}/room-bookings/${id}`);
}

// អនុម័ត និងកំណត់បន្ទប់ប្រជុំ (Approve & Assign Room)
export function apiApproveRoomBooking(id, data) {
  return axios.post(`${APP_API_URL}/room-bookings/${id}/approve`, data);
}

// បដិសេធការស្នើសុំកក់បន្ទប់ (Reject)
export function apiRejectRoomBooking(id, data) {
  return axios.post(`${APP_API_URL}/room-bookings/${id}/reject`, data);
}
