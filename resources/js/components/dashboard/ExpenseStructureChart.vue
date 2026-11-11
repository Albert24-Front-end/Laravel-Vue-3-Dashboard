<template>
  <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-2xs flex flex-col h-full">
    <div class="mb-4">
      <h2 class="text-base font-bold text-slate-900">Структура расходов по статьям</h2>
      <p class="text-xs text-slate-500">Распределение затрат по ключевым направлениям бизнеса</p>
    </div>

    <div class="relative w-full h-[230px] flex items-center justify-center">
      <canvas ref="canvasRef"></canvas>
    </div>

    <!-- Информативная легенда с суммами и долями -->
    <div class="mt-4 pt-3 border-t border-slate-100 space-y-2 max-h-[140px] overflow-y-auto pr-1">
      <div 
        v-for="cat in legendItems" 
        :key="cat.name" 
        class="flex items-center justify-between text-xs py-0.5"
      >
        <div class="flex items-center gap-2 truncate">
          <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: cat.color }"></span>
          <span class="text-slate-700 truncate" :title="cat.name">{{ cat.name }}</span>
        </div>
        <div class="flex items-center gap-2 shrink-0 font-medium">
          <span class="text-slate-900 font-semibold">{{ formatMoney(cat.amount) }}</span>
          <span class="text-slate-600 font-bold bg-slate-100 px-1.5 py-0.5 rounded text-[11px]">{{ cat.percent }}%</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, onBeforeUnmount } from 'vue';
import { Chart, registerables } from 'chart.js';
import { useCurrency } from '../../composables/useCurrency';

Chart.register(...registerables);

const props = defineProps({
  chartData: { type: Object, required: true },
});

const { currentRate, currentCurrencySymbol, formatMoney, formatNumber } = useCurrency();
const canvasRef = ref(null);
let chartInstance = null;

const legendItems = computed(() => {
  const d = props.chartData;
  if (!d || !d.labels) return [];
  return d.labels.map((name, i) => ({
    name,
    amount: d.data[i] || 0,
    percent: d.percentages[i] || 0,
    color: d.colors[i] || '#3B82F6',
  }));
});

const renderChart = () => {
  if (!canvasRef.value) return;
  if (chartInstance) chartInstance.destroy();

  const ctx = canvasRef.value.getContext('2d');
  const rate = currentRate.value || 1.0;
  const d = props.chartData || {};

  chartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: d.labels || [],
      datasets: [
        {
          data: (d.data || []).map((v) => v / rate),
          backgroundColor: d.colors || [],
          borderWidth: 2,
          borderColor: '#ffffff',
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (context) => {
              const label = context.label || '';
              const val = context.raw || 0;
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
              return `${label}: ${formatNumber(val)} ${currentCurrencySymbol.value} (${pct}%)`;
            },
          },
        },
      },
    },
  });
};

watch(
  () => [props.chartData, currentRate.value],
  () => {
    renderChart();
  },
  { deep: true }
);

onMounted(() => {
  renderChart();
});

onBeforeUnmount(() => {
  if (chartInstance) chartInstance.destroy();
});
</script>
