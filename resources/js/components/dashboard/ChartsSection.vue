<template>
  <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="lg:col-span-7">
      <MonthlyComparisonChart :chart-data="barChart" />
    </div>
    <div class="lg:col-span-5">
      <ExpenseStructureChart :chart-data="doughnutChart" />
    </div>
  </section>
</template>

<script setup>
import MonthlyComparisonChart from './MonthlyComparisonChart.vue';
import ExpenseStructureChart from './ExpenseStructureChart.vue';

defineProps({
  barChart: { type: Object, required: true },
  doughnutChart: { type: Object, required: true },
});
</script>
