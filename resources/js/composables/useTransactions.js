import { ref, reactive } from 'vue';
import api from '../api/client';
import { useToast } from './useToast';

const standardExpenseCategories = [
  'ФОТ',
  'Аренда офиса или его обслуживание',
  'Маркетинг и реклама',
  'Закупки и услуги подрядчиков',
  'Уплата налогов',
];

const standardRevenueCategories = [
  'Основная деятельность (продажи)',
  'Оказание консалтинговых услуг',
  'Лицензионные платежи и подписки',
  'Прочие доходы',
];

export function useTransactions(dashboardFilters) {
  const { showToast } = useToast();
  const transactions = ref([]);
  const isLoading = ref(false);
  const isSubmitting = ref(false);

  const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
  });

  const tableFilters = reactive({
    search: '',
    type: 'all',
    category: 'all',
    page: 1,
  });

  const fetchTransactions = async () => {
    isLoading.value = true;
    try {
      const res = await api.get('/transactions', {
        params: {
          search: tableFilters.search || undefined,
          type: tableFilters.type !== 'all' ? tableFilters.type : undefined,
          category: tableFilters.category !== 'all' ? tableFilters.category : undefined,
          year: dashboardFilters?.year !== 'all' ? dashboardFilters?.year : undefined,
          month: dashboardFilters?.month !== 'all' ? dashboardFilters?.month : undefined,
          page: tableFilters.page,
        },
      });

      transactions.value = res.data.data || [];
      pagination.value = {
        current_page: res.data.current_page,
        last_page: res.data.last_page,
        total: res.data.total,
      };
    } catch (err) {
      console.error('Error fetching transactions:', err);
    } finally {
      isLoading.value = false;
    }
  };

  const createTransaction = async (data) => {
    isSubmitting.value = true;
    try {
      const res = await api.post('/transactions', data);
      showToast('Операция успешно добавлена!');
      return res.data.transaction;
    } catch (err) {
      const msg = err.response?.data?.message || 'Ошибка сохранения операции';
      showToast(msg, 'error');
      throw err;
    } finally {
      isSubmitting.value = false;
    }
  };

  const deleteTransaction = async (id) => {
    if (!confirm('Вы уверены, что хотите удалить эту операцию?')) return false;

    try {
      await api.delete(`/transactions/${id}`);
      showToast('Операция удалена');
      return true;
    } catch (err) {
      showToast('Не удалось удалить операцию', 'error');
      return false;
    }
  };

  const importCsv = async (file) => {
    const formData = new FormData();
    formData.append('file', file);

    const res = await api.post('/transactions/import-csv', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return res.data;
  };

  return {
    transactions,
    pagination,
    tableFilters,
    isLoading,
    isSubmitting,
    standardExpenseCategories,
    standardRevenueCategories,
    fetchTransactions,
    createTransaction,
    deleteTransaction,
    importCsv,
  };
}
