<template>
  <div 
    v-if="toast.show" 
    class="fixed bottom-4 right-4 z-50 max-w-sm p-4 rounded-xl shadow-xl border text-xs font-semibold flex items-center gap-2.5 transition-all"
    :class="toast.type === 'error' ? 'bg-rose-600 text-white border-rose-700' : 'bg-slate-900 text-white border-slate-800'"
  >
    <span>{{ toast.message }}</span>
    <button @click="hideToast" class="ml-auto text-white/70 hover:text-white cursor-pointer">✕</button>
  </div>
</template>

<script setup>
import { useToast } from '../../composables/useToast';

const { toast, hideToast } = useToast();
</script>
