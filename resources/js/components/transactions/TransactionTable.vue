<template>
  <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-2xs flex flex-col h-full">

    <!-- Заголовок и поиск -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
      <div>
        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
          Реестр операций
          <span class="text-xs text-center font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
            {{ pagination.total ?? transactions.length }} записей
          </span>
        </h2>
        <p class="text-xs text-slate-500">Поиск, фильтрация по статьям и удаление записей</p>
      </div>

      <!-- Поиск по комментарию -->
      <div class="relative w-full sm:w-64">
        <input
          v-model="tableFilters.search"
          @input="onSearchInput"
          type="text"
          placeholder="Поиск по комментарию..."
          class="w-full text-xs pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
        />
        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <button
          v-if="tableFilters.search"
          @click="clearSearch"
          class="absolute right-2.5 top-1.5 text-slate-400 hover:text-slate-600 text-xs cursor-pointer"
        >×</button>
      </div>
    </div>

    <!-- Быстрые фильтры: Тип и Категория -->
    <div class="flex flex-wrap items-center gap-2 mb-3 pb-3 border-b border-slate-100 text-xs">
      <span class="text-slate-500 font-medium">Фильтр:</span>

      <button
        @click="setType('all')"
        :class="tableFilters.type === 'all' ? 'bg-blue-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
        class="px-2.5 py-1 rounded-lg transition cursor-pointer"
      >
        Все типы
      </button>
      <button
        @click="setType('revenue')"
        :class="tableFilters.type === 'revenue' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
        class="px-2.5 py-1 rounded-lg transition cursor-pointer"
      >
        Выручка
      </button>
      <button
        @click="setType('expense')"
        :class="tableFilters.type === 'expense' ? 'bg-rose-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
        class="px-2.5 py-1 rounded-lg transition cursor-pointer"
      >
        Расходы
      </button>

      <!-- Фильтр по категории -->
      <div class="ml-auto">
        <select
          v-model="tableFilters.category"
          @change="$emit('filter-changed')"
          class="w-full text-xs bg-slate-100 border border-slate-300 rounded-lg px-2 py-1 text-slate-700 focus:outline-none cursor-pointer"
        >
          <option value="all">Все категории</option>
          <optgroup label="Статьи расходов">
            <option v-for="cat in standardExpenseCategories" :key="cat" :value="cat">{{ cat }}</option>
          </optgroup>
          <optgroup label="Статьи выручки">
            <option v-for="cat in standardRevenueCategories" :key="cat" :value="cat">{{ cat }}</option>
          </optgroup>
        </select>
      </div>
    </div>

    <!-- Таблица операций -->
    <div class="overflow-x-auto -mx-5 sm:mx-0 flex-1">
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold bg-slate-50/50">
            <th class="py-2.5 px-3">Дата</th>
            <th class="py-2.5 px-3">Тип</th>
            <th class="py-2.5 px-3">Категория</th>
            <th class="py-2.5 px-3">Комментарий</th>
            <th class="py-2.5 px-3 text-right">Сумма ({{ currentCurrencySymbol }})</th>
            <th class="py-2.5 px-3 text-center">Действие</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="isLoading">
            <td colspan="6" class="py-8 text-center text-slate-400">
              <div class="inline-flex items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-blue-600" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                <span>Загрузка данных...</span>
              </div>
            </td>
          </tr>
          <tr v-else-if="transactions.length === 0">
            <td colspan="6" class="py-8 text-center text-slate-400">
              Операции не найдены. Измените фильтры или добавьте новую запись.
            </td>
          </tr>
          <tr
            v-for="tx in transactions"
            :key="tx.id"
            class="hover:bg-slate-50/80 transition group"
          >
            <!-- Дата -->
            <td class="py-2 px-3 whitespace-nowrap text-slate-600 font-medium">
              {{ formatDate(tx.date) }}
            </td>

            <!-- Тип -->
            <td class="py-2 px-3 whitespace-nowrap">
              <span
                v-if="tx.type === 'revenue'"
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                Выручка
              </span>
              <span
                v-else
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                Расход
              </span>
            </td>

            <!-- Категория -->
            <td class="py-2 px-3 font-semibold text-slate-800 whitespace-nowrap">
              {{ tx.category }}
            </td>

            <!-- Комментарий -->
            <td class="py-2 px-3 text-slate-600 max-w-[220px] truncate" :title="tx.comment">
              {{ tx.comment || '—' }}
            </td>

            <!-- Сумма с форматированием разрядов -->
            <td class="py-2 px-3 text-right whitespace-nowrap font-bold" :class="tx.type === 'revenue' ? 'text-emerald-600' : 'text-slate-900'">
              <span v-if="tx.type === 'revenue'">+</span>
              <span v-else>−</span>
              {{ formatMoney(tx.amount) }}
            </td>

            <!-- Кнопка удаления -->
            <td class="py-2 px-3 text-center whitespace-nowrap">
              <button
                type="button"
                @click="$emit('delete-transaction', tx.id)"
                class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition cursor-pointer"
                title="Удалить операцию"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Пагинация -->
    <div v-if="pagination.last_page > 1" class="mt-3 pt-3 border-t border-slate-100 flex gap-2 items-center justify-between text-xs text-slate-500">
      <div>
        Страница {{ pagination.current_page }} из {{ pagination.last_page }}
      </div>
      <div class="flex flex-wrap sm:flex-nowrap items-center gap-1">
        <button
          :disabled="pagination.current_page <= 1"
          @click="$emit('page-changed', pagination.current_page - 1)"
          class="px-2.5 py-1 rounded border border-slate-200 disabled:opacity-40 hover:bg-slate-50 cursor-pointer"
        >
          Назад
        </button>
        <button
          :disabled="pagination.current_page >= pagination.last_page"
          @click="$emit('page-changed', pagination.current_page + 1)"
          class="px-2.5 py-1 rounded border border-slate-200 disabled:opacity-40 hover:bg-slate-50 cursor-pointer"
        >
          Вперед
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { useCurrency } from '../../composables/useCurrency';

defineProps({
  transactions: { type: Array, required: true },
  pagination: { type: Object, required: true },
  tableFilters: { type: Object, required: true },
  isLoading: { type: Boolean, default: false },
  standardExpenseCategories: { type: Array, required: true },
  standardRevenueCategories: { type: Array, required: true },
});

const emit = defineEmits(['filter-changed', 'page-changed', 'delete-transaction']);

const { currentCurrencySymbol, formatMoney } = useCurrency();

let searchTimer = null;
const onSearchInput = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    emit('filter-changed');
  }, 350);
};

const clearSearch = () => {
  emit('filter-changed', { search: '' });
};

const setType = (type) => {
  emit('filter-changed', { type });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '—';
  const d = new Date(dateStr);
  return d.toLocaleDateString('ru-RU');
};
</script>
