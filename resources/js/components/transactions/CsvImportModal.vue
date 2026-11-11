<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-200">
      
      <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-slate-900">Импорт операций из CSV</h3>
            <p class="text-xs text-slate-500">Загрузка файла с финансовыми операциями</p>
          </div>
        </div>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">✕</button>
      </div>

      <div class="space-y-4">
        <!-- Скачать шаблон -->
        <div class="p-3 bg-blue-50/70 border border-blue-200/80 rounded-xl text-xs flex items-center justify-between">
          <div class="text-blue-900">
            <div class="font-semibold">Нужен образец файла?</div>
            <div class="text-blue-700 text-[11px]">Скачайте готовый шаблон с примерами строк</div>
          </div>
          <button
            type="button"
            @click="downloadTemplate"
            class="px-2.5 py-1 bg-white text-blue-700 font-bold rounded-lg border border-blue-300 hover:bg-blue-50 text-[11px] shadow-2xs transition cursor-pointer"
          >
            Скачать шаблон
          </button>
        </div>

        <!-- Выбор файла -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1.5">Выберите CSV-файл</label>
          <input
            type="file"
            ref="fileInputRef"
            accept=".csv,.txt"
            @change="onFileSelected"
            class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-slate-300 rounded-xl p-1"
          />
        </div>

        <!-- Результат / ошибки импорта -->
        <div v-if="importResult" class="p-3 rounded-xl text-xs" :class="importResult.errors && importResult.errors.length ? 'bg-amber-50 text-amber-900 border border-amber-200' : 'bg-emerald-50 text-emerald-900 border border-emerald-200'">
          <div class="font-bold">{{ importResult.message }}</div>
          <ul v-if="importResult.errors && importResult.errors.length" class="mt-1 list-disc list-inside text-[11px] max-h-24 overflow-y-auto">
            <li v-for="(err, i) in importResult.errors" :key="i">{{ err }}</li>
          </ul>
        </div>
      </div>

      <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
        <button
          type="button"
          @click="$emit('close')"
          class="px-3.5 py-2 text-xs font-medium text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-100 cursor-pointer"
        >
          Закрыть
        </button>
        <button
          type="button"
          :disabled="!selectedFile || isUploading"
          @click="handleUpload"
          class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer"
        >
          <svg v-if="isUploading" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
          </svg>
          <span>{{ isUploading ? 'Импорт...' : 'Загрузить файл' }}</span>
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../../api/client';
import { useToast } from '../../composables/useToast';

const emit = defineEmits(['close', 'import-success']);
const { showToast } = useToast();

const selectedFile = ref(null);
const fileInputRef = ref(null);
const isUploading = ref(false);
const importResult = ref(null);

const downloadTemplate = async () => {
  try {
    const res = await api.get('/transactions/csv-template', { responseType: 'blob' });
    const blob = new Blob([res.data], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'transactions_template.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    showToast('Шаблон CSV скачан');
  } catch (err) {
    showToast('Не удалось скачать шаблон', 'error');
  }
};

const onFileSelected = (e) => {
  selectedFile.value = e.target.files[0] || null;
  importResult.value = null;
};

const handleUpload = async () => {
  if (!selectedFile.value) return;

  isUploading.value = true;
  importResult.value = null;

  try {
    emit('import-success', selectedFile.value, (result) => {
      importResult.value = result;
      isUploading.value = false;
      if (fileInputRef.value) fileInputRef.value.value = '';
      selectedFile.value = null;
    });
  } catch (err) {
    isUploading.value = false;
    importResult.value = {
      message: err.message || 'Ошибка импорта',
      errors: [err.message],
    };
  }
};
</script>
