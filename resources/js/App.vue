<template>
  <div class="min-h-screen bg-slate-50 text-slate-800 flex flex-col font-sans">

    <!-- Верхняя панель (Header) -->
    <TheHeader
      :filters="dashboard.filters"
      :months-list="dashboard.monthsList"
      :database-driver="dashboard.databaseDriver.value"
      @period-changed="refreshAll"
      @open-import="isImportModalOpen = true"
    />

    <!-- Основной контент дашборда (доступен авторизованным пользователям) -->
    <main v-if="auth.isAuthenticated.value" class="max-w-420 mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full flex-1 space-y-6">

      <!-- Плашка активного курса валюты (если выбрана не базовая RUB) -->
      <div
        v-if="currency.currentCurrency.value !== 'RUB'"
        class="bg-blue-50/80 border border-blue-200/80 rounded-xl px-4 py-2 flex items-center justify-between text-xs text-blue-900"
      >
        <div class="flex items-center gap-2">
          <span class="inline-block w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
          <span>Отображение в валюте <strong>{{ currency.currencies.value[currency.currentCurrency.value]?.name }} ({{ currency.currentCurrency.value }})</strong>. Все суммы пересчитаны по курсу ЦБ РФ: <strong>1 {{ currency.currentCurrency.value }} = {{ currency.formatNumber(currency.currentRate.value) }} ₽</strong></span>
        </div>
        <button
          @click="currency.setCurrency('RUB')"
          class="underline hover:text-blue-700 cursor-pointer font-medium"
        >
          Вернуть в рубли (₽)
        </button>
      </div>

      <!-- 1) 4 Крупные карточки KPI -->
      <KpiCardsGrid
        :kpi="dashboard.kpi.value"
        :current-period-text="dashboard.currentPeriodText.value"
      />

      <!-- 2) Два интерактивных графика рядом (Chart.js) -->
      <ChartsSection
        :bar-chart="dashboard.barChart.value"
        :doughnut-chart="dashboard.doughnutChart.value"
      />

      <!-- 3) Форма и таблица операций -->
      <OperationsSection
        :transactions="transactionsState.transactions.value"
        :pagination="transactionsState.pagination.value"
        :table-filters="transactionsState.tableFilters"
        :is-loading="transactionsState.isLoading.value"
        :is-submitting="transactionsState.isSubmitting.value"
        :standard-expense-categories="transactionsState.standardExpenseCategories"
        :standard-revenue-categories="transactionsState.standardRevenueCategories"
        @add-transaction="handleAddTransaction"
        @filter-changed="handleFilterChanged"
        @page-changed="handlePageChanged"
        @delete-transaction="handleDeleteTransaction"
      />

    </main>

    <!-- Заглушка, если не авторизован -->
    <div v-else class="flex-1 flex items-center justify-center p-6 text-center">
      <div class="max-w-md p-8 bg-white rounded-3xl border border-slate-200 shadow-md">
        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <h3 class="text-base font-bold text-slate-900 mb-1">Требуется корпоративная авторизация</h3>
        <p class="text-xs text-slate-500 mb-4">Финансовая информация защищена и доступна только авторизованным сотрудникам компании.</p>
        <button
          @click="isAuthModalOpen = true"
          class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition cursor-pointer"
        >
          Войти в аккаунт
        </button>
      </div>
    </div>

    <!-- Подвал (Footer) -->
    <TheFooter />

    <!-- Модальное окно авторизации (если не вошел) -->
    <AuthModal
      v-if="isAuthModalOpen && !auth.isAuthenticated.value"
      @authenticated="onAuthenticated"
    />

    <!-- Модальное окно импорта CSV -->
    <CsvImportModal
      v-if="isImportModalOpen"
      @close="isImportModalOpen = false"
      @import-success="handleCsvImport"
    />

    <!-- Toast уведомления -->
    <AppToast />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import TheHeader from './components/layout/TheHeader.vue';
import TheFooter from './components/layout/TheFooter.vue';
import AuthModal from './components/auth/AuthModal.vue';
import KpiCardsGrid from './components/dashboard/KpiCardsGrid.vue';
import ChartsSection from './components/dashboard/ChartsSection.vue';
import OperationsSection from './components/transactions/OperationsSection.vue';
import CsvImportModal from './components/transactions/CsvImportModal.vue';
import AppToast from './components/ui/AppToast.vue';

import { useAuth } from './composables/useAuth';
import { useCurrency } from './composables/useCurrency';
import { useDashboard } from './composables/useDashboard';
import { useTransactions } from './composables/useTransactions';

const auth = useAuth();
const currency = useCurrency();
const dashboard = useDashboard();
const transactionsState = useTransactions(dashboard.filters);

const isAuthModalOpen = ref(!auth.isAuthenticated.value);
const isImportModalOpen = ref(false);

const refreshAll = async () => {
  if (!auth.isAuthenticated.value) return;
  await Promise.all([
    dashboard.fetchStats(),
    transactionsState.fetchTransactions(),
  ]);
};

const onAuthenticated = async () => {
  isAuthModalOpen.value = false;
  await currency.fetchCurrencies();
  await refreshAll();
};

const handleAddTransaction = async (data) => {
  try {
    await transactionsState.createTransaction(data);
    await refreshAll();
  } catch (err) {
    // Handled in composable
  }
};

const handleFilterChanged = (overrides) => {
  if (overrides) {
    Object.assign(transactionsState.tableFilters, overrides);
  }
  transactionsState.tableFilters.page = 1;
  transactionsState.fetchTransactions();
};

const handlePageChanged = (page) => {
  transactionsState.tableFilters.page = page;
  transactionsState.fetchTransactions();
};

const handleDeleteTransaction = async (id) => {
  const success = await transactionsState.deleteTransaction(id);
  if (success) {
    await refreshAll();
  }
};

const handleCsvImport = async (file, done) => {
  try {
    const result = await transactionsState.importCsv(file);
    done(result);
    await refreshAll();
  } catch (err) {
    done({
      message: err.response?.data?.message || 'Ошибка при загрузке CSV файла',
      errors: err.response?.data?.errors ? Object.values(err.response.data.errors).flat() : [err.message],
    });
  }
};

onMounted(async () => {
  window.addEventListener('auth:unauthorized', () => {
    isAuthModalOpen.value = true;
  });

  if (auth.isAuthenticated.value) {
    await auth.fetchUser();
    await currency.fetchCurrencies();
    await refreshAll();
  } else {
    isAuthModalOpen.value = true;
  }
});
</script>
