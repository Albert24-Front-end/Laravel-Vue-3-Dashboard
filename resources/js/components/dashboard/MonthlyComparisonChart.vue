<template>
  <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-2xs flex flex-col h-full">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
      <div>
        <h2 class="text-base font-bold text-slate-900">Динамика выручки и расходов по месяцам</h2>
        <p class="text-xs text-slate-500">Сравнение доходной и расходной частей за выбранный год</p>
      </div>
      <div class="flex items-center gap-3 text-xs">
        <div class="flex items-center gap-1.5">
          <span class="w-3 h-3 rounded-sm bg-emerald-500 inline-block"></span>
          <span class="text-slate-600">Выручка</span>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="w-3 h-3 rounded-sm bg-rose-500 inline-block"></span>
          <span class="text-slate-600">Расходы</span>
        </div>
      </div>
    </div>

    <div class="relative w-full h-[320px] flex items-center justify-center">
      <canvas ref="canvasRef"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from 'vue';
import { Chart, registerables } from 'chart.js';
import { useCurrency } from '../../composables/useCurrency';

Chart.register(...registerables);

const props = defineProps({
  chartData: { type: Object, required: true },
});

const { currentRate, currentCurrencySymbol, formatNumber } = useCurrency();
const canvasRef = ref(null);
let chartInstance = null;

const renderChart = () => {
  if (!canvasRef.value) return;
  if (chartInstance) chartInstance.destroy();

  const ctx = canvasRef.value.getContext('2d');
  const rate = currentRate.value || 1.0;
  const rawDatasets = props.chartData?.datasets || [];

  chartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: props.chartData?.labels || [],
      datasets: [
        {
          label: 'Выручка',
          backgroundColor: '#10B981',
          hoverBackgroundColor: '#059669',
          borderRadius: 6,
          data: (rawDatasets[0]?.data || []).map((v) => v / rate),
        },
        {
          label: 'Расходы',
          backgroundColor: '#EF4444',
          hoverBackgroundColor: '#DC2626',
          borderRadius: 6,
          data: (rawDatasets[1]?.data || []).map((v) => v / rate),
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (context) => {
              const label = context.dataset.label || '';
              const val = context.raw || 0;
              return `${label}: ${formatNumber(val)} ${currentCurrencySymbol.value}`;
            },
          },
        },
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { size: 11 } },
        },
        y: {
          grid: { color: '#f1f5f9' },
          ticks: {
            font: { size: 10 },
            callback: (value) => `${formatNumber(value / 1000)}k`,
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
