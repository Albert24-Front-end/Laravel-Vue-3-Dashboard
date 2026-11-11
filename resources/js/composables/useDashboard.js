import { ref, reactive, computed } from 'vue';
import api from '../api/client';

const filters = reactive({
  year: 2026,
  month: 'all',
});

const monthsList = {
  1: 'Январь', 2: 'Февраль', 3: 'Март', 4: 'Апрель',
  5: 'Май', 6: 'Июнь', 7: 'Июль', 8: 'Август',
  9: 'Сентябрь', 10: 'Октябрь', 11: 'Ноябрь', 12: 'Декабрь',
};

const rawStats = ref({
  database_driver: 'pgsql',
  kpi: {
    revenue: 0,
    expenses: 0,
    net_profit: 0,
    margin: 0,
    is_profitable: true,
  },
  bar_chart: { labels: [], datasets: [] },
  doughnut_chart: { labels: [], data: [], percentages: [], colors: [] },
});

const isLoading = ref(false);

export function useDashboard() {
  const kpi = computed(() => rawStats.value.kpi);
  const barChart = computed(() => rawStats.value.bar_chart);
  const doughnutChart = computed(() => rawStats.value.doughnut_chart);
  const databaseDriver = computed(() => rawStats.value.database_driver);

  const currentPeriodText = computed(() => {
    if (filters.month !== 'all') {
      return `${monthsList[filters.month]} ${filters.year}`;
    }
    return `${filters.year} год`;
  });

  const fetchStats = async () => {
    isLoading.value = true;
    try {
      const res = await api.get('/dashboard/stats', {
        params: {
          year: filters.year,
          month: filters.month,
        },
      });
      rawStats.value = res.data;
    } catch (err) {
      console.error('Error fetching dashboard stats:', err);
    } finally {
      isLoading.value = false;
    }
  };

  return {
    filters,
    monthsList,
    currentPeriodText,
    kpi,
    barChart,
    doughnutChart,
    databaseDriver,
    isLoading,
    fetchStats,
  };
}
