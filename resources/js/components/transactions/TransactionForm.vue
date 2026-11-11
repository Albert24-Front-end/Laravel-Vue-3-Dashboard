<template>
  <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-2xs h-fit">
    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
      <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
      </div>
      <div>
        <h2 class="text-sm font-bold text-slate-900">Новая операция</h2>
        <p class="text-xs text-slate-500">Добавить запись в базу PostgreSQL</p>
      </div>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-3.5">
      
      <!-- Переключатель Тип операции: Выручка / Расход -->
      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Тип операции</label>
        <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
          <button
            type="button"
            @click="setType('revenue')"
            :class="[
              form.type === 'revenue' 
                ? 'bg-emerald-600 text-white font-bold shadow-xs' 
                : 'text-slate-600 hover:text-slate-900 font-medium',
              'py-1.5 text-xs rounded-lg transition cursor-pointer flex items-center justify-center gap-1.5'
            ]"
          >
            <span>+ Выручка</span>
          </button>
          <button
            type="button"
            @click="setType('expense')"
            :class="[
              form.type === 'expense' 
                ? 'bg-rose-600 text-white font-bold shadow-xs' 
                : 'text-slate-600 hover:text-slate-900 font-medium',
              'py-1.5 text-xs rounded-lg transition cursor-pointer flex items-center justify-center gap-1.5'
            ]"
          >
            <span>− Расход</span>
          </button>
        </div>
      </div>

      <!-- Категория / Статья -->
      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Статья / Категория</label>
        <select
          v-model="form.category"
          required
          class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition cursor-pointer"
        >
          <option value="" disabled>Выберите категорию...</option>
          <template v-if="form.type === 'expense'">
            <option v-for="cat in standardExpenseCategories" :key="cat" :value="cat">
              {{ cat }}
            </option>
          </template>
          <template v-else>
            <option v-for="cat in standardRevenueCategories" :key="cat" :value="cat">
              {{ cat }}
            </option>
          </template>
        </select>
      </div>

      <!-- Сумма и Дата в две колонки -->
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1.5">
            Сумма ({{ currentCurrencySymbol }})
          </label>
          <div class="relative">
            <input
              v-model.number="form.displayAmount"
              type="number"
              step="0.01"
              min="0.01"
              required
              placeholder="0.00"
              class="w-full text-xs font-semibold bg-slate-50 border border-slate-300 rounded-xl pl-3 pr-7 py-2 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
            />
            <span class="absolute right-2.5 top-2 text-xs font-bold text-slate-400">
              {{ currentCurrencySymbol }}
            </span>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1.5">Дата</label>
          <input
            v-model="form.date"
            type="date"
            required
            class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition cursor-pointer"
          />
        </div>
      </div>

      <!-- Короткий комментарий -->
      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Короткий комментарий</label>
        <input
          v-model="form.comment"
          type="text"
          maxlength="250"
          placeholder="Например: Выплата аванса за март"
          class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
        />
      </div>

      <!-- Кнопка отправки формы -->
      <button
        type="submit"
        :disabled="isSubmitting"
        class="w-full mt-2 py-2.5 px-4 text-xs font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition flex items-center justify-center gap-2 cursor-pointer"
      >
        <svg v-if="isSubmitting" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
        <span>{{ isSubmitting ? 'Сохранение...' : 'Добавить запись' }}</span>
      </button>

    </form>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import { useCurrency } from '../../composables/useCurrency';

const props = defineProps({
  standardExpenseCategories: { type: Array, required: true },
  standardRevenueCategories: { type: Array, required: true },
  isSubmitting: { type: Boolean, default: false },
});

const emit = defineEmits(['add-transaction']);

const { currentCurrencySymbol, toRubles } = useCurrency();

const form = reactive({
  type: 'expense',
  category: props.standardExpenseCategories[0] || 'ФОТ',
  displayAmount: null,
  date: new Date().toISOString().substring(0, 10),
  comment: '',
});

const setType = (newType) => {
  form.type = newType;
  if (newType === 'expense') {
    form.category = props.standardExpenseCategories[0];
  } else {
    form.category = props.standardRevenueCategories[0];
  }
};

const handleSubmit = () => {
  if (!form.displayAmount || form.displayAmount <= 0) return;

  const baseAmount = toRubles(form.displayAmount);

  emit('add-transaction', {
    type: form.type,
    category: form.category,
    amount: baseAmount,
    date: form.date,
    comment: form.comment,
  });

  form.displayAmount = null;
  form.comment = '';
};
</script>
