<template>
  <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
    <div class="max-w-420 mx-auto px-4 sm:px-6 lg:px-8 py-3.5">
      <div class="flex flex-col lg:flex-row lg:flex-wrap xl:flex-nowrap lg:items-center lg:justify-between gap-4">

        <!-- Логотип и название дашборда -->
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20 shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h1 class="text-md sm:text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
              Финансовый дашборд
            </h1>
            <p class="hidden sm:block text-xs text-slate-500">Корпоративная финансовая аналитика и учет операций</p>
          </div>
        </div>

        <!-- Фильтры периода, валюта и действия -->
        <div class="flex flex-wrap sm:flex-nowrap items-center gap-2.5">

          <!-- Фильтр по Году -->
          <select
            v-model="filters.year"
            @change="$emit('period-changed')"
            class="text-xs font-medium bg-slate-100 hover:bg-slate-200/70 border border-slate-300/80 rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition cursor-pointer"
          >
            <option :value="2026">2026 год</option>
            <option :value="2025">2025 год</option>
            <option value="all">Все годы</option>
          </select>

          <!-- Фильтр по Месяцам года -->
          <select
            v-model="filters.month"
            @change="$emit('period-changed')"
            class="text-xs font-medium bg-slate-100 hover:bg-slate-200/70 border border-slate-300/80 rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition cursor-pointer"
          >
            <option value="all">Все месяцы</option>
            <option v-for="(name, num) in monthsList" :key="num" :value="num">
              {{ name }}
            </option>
          </select>

          <!-- Переключатель валюты -->
          <div class="inline-flex flex-wrap sm:flex-nowrap rounded-lg bg-slate-100 p-0.5 border border-slate-200" title="Переключение отображения валюты">
            <button
              v-for="(info, code) in currencies"
              :key="code"
              type="button"
              @click="setCurrency(code)"
              :class="[
                currentCurrency === code
                  ? 'bg-white text-blue-700 shadow-xs font-bold'
                  : 'text-slate-600 hover:text-slate-900 font-medium',
                'px-2.5 py-1 text-xs rounded-md transition-all flex items-center gap-1 cursor-pointer'
              ]"
            >
              <span class="font-semibold">{{ info.symbol }}</span>
              <span class="text-[11px] opacity-80">{{ code }}</span>
            </button>
          </div>
        </div>
          <!-- Кнопки действий: Импорт CSV, Скачать CSV, PDF -->
          <div class="flex flex-wrap sm:flex-nowrap items-center gap-1.5 lg:ml-0">
              <!-- Импорт из CSV -->
              <button
                  type="button"
                  @click="$emit('open-import')"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 hover:text-slate-900 shadow-2xs transition cursor-pointer"
                  title="Загрузить операции из CSV"
              >
                  <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                  </svg>
                  <span>Импорт CSV</span>
              </button>

              <!-- Скачать CSV -->
              <button
                  type="button"
                  :disabled="isExportingCsv"
                  @click="downloadCsv"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 hover:text-slate-900 shadow-2xs transition cursor-pointer disabled:opacity-50"
                  title="Скачать реестр операций в формате CSV"
              >
                  <svg v-if="isExportingCsv" class="animate-spin w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                  </svg>
                  <svg v-else class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                  </svg>
                  <span>{{ isExportingCsv ? 'Экспорт...' : 'Экспорт CSV' }}</span>
              </button>

              <!-- Отчет PDF -->
              <button
                  type="button"
                  :disabled="isExportingPdf"
                  @click="downloadPdf"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-xs hover:shadow transition cursor-pointer disabled:opacity-50"
                  title="Сформировать официальный финансовый отчет в формате PDF"
              >
                  <svg v-if="isExportingPdf" class="animate-spin w-3.5 h-3.5 text-white" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                  </svg>
                  <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                  <span>{{ isExportingPdf ? 'Генерация...' : 'Отчет PDF' }}</span>
              </button>

              <!-- Пользователь и Выход -->
              <div v-if="user" class="pl-2 border-l border-slate-200 flex items-center gap-2">
              <span class="text-xs text-slate-600 hidden md:inline truncate max-w-[130px]" :title="user.name">
                {{ user.name }}
              </span>
                  <button
                      @click="logout"
                      class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer"
                      title="Выйти из системы"
                  >
                      <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                      </svg>
                  </button>
              </div>

          </div>

      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue';
import api from '../../api/client';
import { useCurrency } from '../../composables/useCurrency';
import { useAuth } from '../../composables/useAuth';
import { useToast } from '../../composables/useToast';

const props = defineProps({
  filters: { type: Object, required: true },
  monthsList: { type: Object, required: true },
  databaseDriver: { type: String, default: 'pgsql' },
});

defineEmits(['period-changed', 'open-import']);

const { currentCurrency, currencies, setCurrency } = useCurrency();
const { user, logout } = useAuth();
const { showToast } = useToast();

const isExportingCsv = ref(false);
const isExportingPdf = ref(false);

const downloadCsv = async () => {
  isExportingCsv.value = true;
  try {
    const response = await api.get('/transactions/export-csv', {
      params: {
        year: props.filters.year,
        month: props.filters.month,
      },
      responseType: 'blob',
    });

    const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `transactions_${props.filters.year}_${props.filters.month}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    showToast('Реестр CSV успешно скачан');
  } catch (err) {
    showToast('Ошибка при скачивании CSV', 'error');
  } finally {
    isExportingCsv.value = false;
  }
};

const downloadPdf = async () => {
  isExportingPdf.value = true;
  try {
    const response = await api.get('/reports/pdf', {
      params: {
        year: props.filters.year,
        month: props.filters.month,
        currency: currentCurrency.value,
      },
      responseType: 'blob',
    });

    const blob = new Blob([response.data], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `financial_report_${props.filters.year}_${props.filters.month}.pdf`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    showToast('Финансовый PDF-отчет сформирован');
  } catch (err) {
    showToast('Ошибка при генерации PDF', 'error');
  } finally {
    isExportingPdf.value = false;
  }
};
</script>
