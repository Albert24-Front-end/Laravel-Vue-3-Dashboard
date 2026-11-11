import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
  },
});

// Request interceptor: attach Sanctum Bearer token and fallback query param
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
    config.params = config.params || {};
    config.params.token = token;
  }
  return config;
});

// Response interceptor: handle 401 unauthenticated
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('auth_user');
      window.dispatchEvent(new CustomEvent('auth:unauthorized'));
    }
    return Promise.reject(error);
  }
);

export default api;
