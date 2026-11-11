<template>
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    
    <!-- 1. Выручка -->
    <KpiCard
      title="Выручка"
      :value="formatMoney(kpi.revenue)"
      card-class="bg-white border-slate-200"
      icon-container-class="bg-emerald-50 text-emerald-600"
    >
      <template #icon>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
        </svg>
      </template>
      <template #subtext>
        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        <span>Поступления за {{ currentPeriodText }}</span>
      </template>
    </KpiCard>

    <!-- 2. Расходы -->
    <KpiCard
      title="Расходы"
      :value="formatMoney(kpi.expenses)"
      card-class="bg-white border-slate-200"
      icon-container-class="bg-rose-50 text-rose-600"
    >
      <template #icon>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
        </svg>
      </template>
      <template #subtext>
        <span class="inline-block w-1.5 h-1.5 rounded-full bg-rose-500"></span>
        <span>Сумма затрат по всем статьям</span>
      </template>
    </KpiCard>

    <!-- 3. Чистая прибыль (зеленый > 0, красный < 0) -->
    <KpiCard
      title="Чистая прибыль"
      :value="formatMoney(kpi.net_profit)"
      :card-class="kpi.net_profit >= 0 ? 'bg-emerald-50/70 border-emerald-300' : 'bg-rose-50/70 border-rose-300'"
      :label-class="kpi.net_profit >= 0 ? 'text-emerald-800 font-bold' : 'text-rose-800 font-bold'"
      :value-class="kpi.net_profit >= 0 ? 'text-emerald-700' : 'text-rose-700'"
      :icon-container-class="kpi.net_profit >= 0 ? 'bg-emerald-200/80 text-emerald-800' : 'bg-rose-200/80 text-rose-800'"
      :subtext-class="kpi.net_profit >= 0 ? 'text-emerald-700' : 'text-rose-700'"
      :badge-text="kpi.net_profit >= 0 ? '✓' : '!'"
    >
      <template #subtext>
        <span class="font-medium">= Выручка − Расходы</span>
        <span 
          class="font-semibold px-1.5 py-0.2 rounded text-[11px]"
          :class="kpi.net_profit >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
        >
          {{ kpi.net_profit >= 0 ? 'Прибыль' : 'Убыток' }}
        </span>
      </template>
    </KpiCard>

    <!-- 4. Рентабельность / Маржинальность (%) -->
    <KpiCard
      title="Рентабельность"
      :value="`${formatPercent(kpi.margin)}%`"
      card-class="bg-white border-slate-200"
      icon-container-class="bg-blue-50 text-blue-600"
      badge-text="%"
    >
      <template #subtext>
        <div class="w-full">
          <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
            <div 
              class="h-1.5 rounded-full transition-all duration-500" 
              :class="kpi.margin >= 0 ? 'bg-emerald-500' : 'bg-rose-500'" 
              :style="{ width: Math.min(Math.max(kpi.margin, 0), 100) + '%' }"
            ></div>
          </div>
          <div class="mt-1 text-[11px] text-slate-500">= (Чистая прибыль / Выручка) × 100%</div>
        </div>
      </template>
    </KpiCard>

  </section>
</template>

<script setup>
import KpiCard from './KpiCard.vue';
import { useCurrency } from '../../composables/useCurrency';

defineProps({
  kpi: { type: Object, required: true },
  currentPeriodText: { type: String, required: true },
});

const { formatMoney, formatPercent } = useCurrency();
</script>
