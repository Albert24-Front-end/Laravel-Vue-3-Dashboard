import { ref, computed } from 'vue';
import api from '../api/client';
import { useToast } from './useToast';

const user = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'));
const token = ref(localStorage.getItem('auth_token') || null);
const isLoading = ref(false);
const authError = ref(null);

export function useAuth() {
  const { showToast } = useToast();
  const isAuthenticated = computed(() => !!token.value && !!user.value);

  const fetchUser = async () => {
    if (!token.value) return null;
    try {
      const res = await api.get('/auth/user');
      user.value = res.data;
      localStorage.setItem('auth_user', JSON.stringify(res.data));
      return res.data;
    } catch (err) {
      logout();
      return null;
    }
  };

  const login = async (email, password) => {
    isLoading.value = true;
    authError.value = null;
    try {
      const res = await api.post('/auth/login', { email, password });
      token.value = res.data.token;
      user.value = res.data.user;
      localStorage.setItem('auth_token', res.data.token);
      localStorage.setItem('auth_user', JSON.stringify(res.data.user));
      showToast(`Добро пожаловать, ${user.value.name}!`);
      return true;
    } catch (err) {
      authError.value = err.response?.data?.message || 'Ошибка авторизации';
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  const register = async (name, email, password, passwordConfirmation) => {
    isLoading.value = true;
    authError.value = null;
    try {
      const res = await api.post('/auth/register', {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });
      token.value = res.data.token;
      user.value = res.data.user;
      localStorage.setItem('auth_token', res.data.token);
      localStorage.setItem('auth_user', JSON.stringify(res.data.user));
      showToast(`Регистрация успешна! Добро пожаловать, ${user.value.name}!`);
      return true;
    } catch (err) {
      authError.value = err.response?.data?.message || 'Ошибка регистрации';
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  const logout = async () => {
    try {
      if (token.value) {
        await api.post('/auth/logout').catch(() => {});
      }
    } finally {
      token.value = null;
      user.value = null;
      localStorage.removeItem('auth_token');
      localStorage.removeItem('auth_user');
      showToast('Вы вышли из системы');
    }
  };

  return {
    user,
    token,
    isLoading,
    authError,
    isAuthenticated,
    login,
    register,
    logout,
    fetchUser,
  };
}
