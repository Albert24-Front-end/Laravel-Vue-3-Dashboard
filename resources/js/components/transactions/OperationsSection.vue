<template>
  <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Форма добавления (4 колонки) -->
    <div class="lg:col-span-4">
      <TransactionForm
        :standard-expense-categories="standardExpenseCategories"
        :standard-revenue-categories="standardRevenueCategories"
        :is-submitting="isSubmitting"
        @add-transaction="$emit('add-transaction', $event)"
      />
    </div>

    <!-- Таблица операций (8 колонок) -->
    <div class="lg:col-span-8">
      <TransactionTable
        :transactions="transactions"
        :pagination="pagination"
        :table-filters="tableFilters"
        :is-loading="isLoading"
        :standard-expense-categories="standardExpenseCategories"
        :standard-revenue-categories="standardRevenueCategories"
        @filter-changed="$emit('filter-changed', $event)"
        @page-changed="$emit('page-changed', $event)"
        @delete-transaction="$emit('delete-transaction', $event)"
      />
    </div>
  </section>
</template>

<script setup>
import TransactionForm from './TransactionForm.vue';
import TransactionTable from './TransactionTable.vue';

defineProps({
  transactions: { type: Array, required: true },
  pagination: { type: Object, required: true },
  tableFilters: { type: Object, required: true },
  isLoading: { type: Boolean, default: false },
  isSubmitting: { type: Boolean, default: false },
  standardExpenseCategories: { type: Array, required: true },
  standardRevenueCategories: { type: Array, required: true },
});

defineEmits(['add-transaction', 'filter-changed', 'page-changed', 'delete-transaction']);
</script>
