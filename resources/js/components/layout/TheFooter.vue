<script setup>
</script>
<template>
  <footer class="mt-auto border-t border-slate-200 bg-white py-4 text-center text-xs text-slate-500">
    <div class="max-w-420 mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
      <div>Корпоративный финансовый дашборд компании © {{ new Date().getFullYear() }}</div>
      <div class="flex items-center gap-4 text-slate-400">
        <span>СУБД: PostgreSQL (Docker)</span>
        <span>•</span>
        <span>Курсы: ЦБ РФ</span>
        <span>•</span>
        <span>Laravel Sanctum</span>
      </div>
    </div>
  </footer>
</template>
