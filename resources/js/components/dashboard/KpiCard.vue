<template>
  <div 
    :class="[
      cardClass,
      'rounded-2xl p-5 border shadow-2xs hover:shadow-md transition relative overflow-hidden'
    ]"
  >
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-semibold uppercase tracking-wider" :class="labelClass">
        {{ title }}
      </span>
      <div 
        class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs"
        :class="iconContainerClass"
      >
        <slot name="icon">
          <span>{{ badgeText || '•' }}</span>
        </slot>
      </div>
    </div>

    <!-- Сумма с пробелами между разрядами -->
    <div class="text-2xl font-extrabold tracking-tight" :class="valueClass">
      {{ value }}
    </div>

    <!-- Нижняя поясняющая строка -->
    <div class="mt-2 text-xs flex items-center gap-1.5" :class="subtextClass">
      <slot name="subtext">
        <span>{{ subtitle }}</span>
      </slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  value: { type: String, required: true },
  subtitle: { type: String, default: '' },
  badgeText: { type: String, default: '' },
  cardClass: { type: String, default: 'bg-white border-slate-200' },
  labelClass: { type: String, default: 'text-slate-500' },
  valueClass: { type: String, default: 'text-slate-900' },
  iconContainerClass: { type: String, default: 'bg-slate-100 text-slate-600' },
  subtextClass: { type: String, default: 'text-slate-500' },
});
</script>
