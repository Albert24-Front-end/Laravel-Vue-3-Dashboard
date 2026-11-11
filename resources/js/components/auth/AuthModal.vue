<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl max-w-md w-full p-8 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-200">
      
      <!-- Шапка модального окна -->
      <div class="text-center mb-6">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white mx-auto shadow-md shadow-blue-500/20 mb-3">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
        </div>
        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Корпоративный доступ</h2>
        <p class="text-xs text-slate-500 mt-1">Внутренняя финансовая аналитика и отчетность компании</p>
      </div>

      <!-- Вкладки: Вход / Регистрация -->
      <div class="grid grid-cols-2 gap-1 p-1 bg-slate-100 rounded-xl mb-5">
        <button
          type="button"
          @click="mode = 'login'; errorMessage = null"
          :class="mode === 'login' ? 'bg-white text-blue-700 font-bold shadow-xs' : 'text-slate-600 font-medium hover:text-slate-900'"
          class="py-2 text-xs rounded-lg transition cursor-pointer"
        >
          Вход в систему
        </button>
        <button
          type="button"
          @click="mode = 'register'; errorMessage = null"
          :class="mode === 'register' ? 'bg-white text-blue-700 font-bold shadow-xs' : 'text-slate-600 font-medium hover:text-slate-900'"
          class="py-2 text-xs rounded-lg transition cursor-pointer"
        >
          Регистрация
        </button>
      </div>

      <!-- Сообщение об ошибке -->
      <div v-if="errorMessage" class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-800 text-xs border border-rose-200">
        {{ errorMessage }}
      </div>

      <!-- Форма входа -->
      <form v-if="mode === 'login'" @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Рабочий Email</label>
          <input
            v-model="loginForm.email"
            type="email"
            required
            placeholder="admin@corporate.com"
            class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Пароль</label>
          <input
            v-model="loginForm.password"
            type="password"
            required
            placeholder="••••••••"
            class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
          />
        </div>

        <!-- Быстрый вход для демо-аккаунта -->
        <div class="p-2.5 bg-blue-50/70 border border-blue-200/80 rounded-xl text-[11px] text-blue-900 flex items-center justify-between">
          <div>
            <span class="font-bold">Демо:</span> admin@corporate.com / password123
          </div>
          <button
            type="button"
            @click="fillDemoCredentials"
            class="text-blue-700 hover:text-blue-900 font-bold underline cursor-pointer"
          >
            Подставить
          </button>
        </div>

        <button
          type="submit"
          :disabled="isLoading"
          class="w-full py-2.5 px-4 text-xs font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed shadow-md transition flex items-center justify-center gap-2 cursor-pointer"
        >
          <svg v-if="isLoading" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
          </svg>
          <span>{{ isLoading ? 'Авторизация...' : 'Войти в дашборд' }}</span>
        </button>
      </form>

      <!-- Форма регистрации -->
      <form v-else @submit.prevent="handleRegister" class="space-y-3.5">
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Имя и должность</label>
          <input
            v-model="registerForm.name"
            type="text"
            required
            placeholder="Иван Петров (Финансовый аналитик)"
            class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Рабочий Email</label>
          <input
            v-model="registerForm.email"
            type="email"
            required
            placeholder="user@corporate.com"
            class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Пароль (мин. 6 символов)</label>
          <input
            v-model="registerForm.password"
            type="password"
            required
            minlength="6"
            placeholder="••••••••"
            class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Подтверждение пароля</label>
          <input
            v-model="registerForm.password_confirmation"
            type="password"
            required
            placeholder="••••••••"
            class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
          />
        </div>

        <button
          type="submit"
          :disabled="isLoading"
          class="w-full py-2.5 px-4 text-xs font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed shadow-md transition flex items-center justify-center gap-2 cursor-pointer mt-2"
        >
          <svg v-if="isLoading" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
          </svg>
          <span>{{ isLoading ? 'Регистрация...' : 'Зарегистрироваться' }}</span>
        </button>
      </form>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useAuth } from '../../composables/useAuth';

const emit = defineEmits(['authenticated']);
const { login, register, isLoading } = useAuth();

const mode = ref('login');
const errorMessage = ref(null);

const loginForm = reactive({
  email: 'admin@corporate.com',
  password: 'password123',
});

const registerForm = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const fillDemoCredentials = () => {
  loginForm.email = 'admin@corporate.com';
  loginForm.password = 'password123';
};

const handleLogin = async () => {
  errorMessage.value = null;
  try {
    await login(loginForm.email, loginForm.password);
    emit('authenticated');
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Неверный логин или пароль';
  }
};

const handleRegister = async () => {
  errorMessage.value = null;
  if (registerForm.password !== registerForm.password_confirmation) {
    errorMessage.value = 'Пароли не совпадают';
    return;
  }
  try {
    await register(
      registerForm.name,
      registerForm.email,
      registerForm.password,
      registerForm.password_confirmation
    );
    emit('authenticated');
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Ошибка при регистрации';
  }
};
</script>
