import { ref, computed } from 'vue';
import api from '../api/client';

const currentCurrency = ref('RUB');
const currencies = ref({
  RUB: { name: 'Российский рубль', symbol: '₽', rate: 1.0 },
  USD: { name: 'Доллар США', symbol: '$', rate: 91.50 },
  EUR: { name: 'Евро', symbol: '€', rate: 98.80 },
  CNY: { name: 'Китайский юань', symbol: '¥', rate: 12.60 },
  KZT: { name: 'Казахстанский тенге', symbol: '₸', rate: 0.185 },
});

export function useCurrency() {
  const currentRate = computed(() => {
    return currencies.value[currentCurrency.value]?.rate || 1.0;
  });

  const currentCurrencySymbol = computed(() => {
    return currencies.value[currentCurrency.value]?.symbol || '₽';
  });

  const setCurrency = (code) => {
    if (currencies.value[code]) {
      currentCurrency.value = code;
    }
  };

  const convertAmount = (amountInRub) => {
    const rate = currentRate.value || 1.0;
    return (Number(amountInRub) || 0) / rate;
  };

  const toRubles = (amountInCurrentCurrency) => {
    const rate = currentRate.value || 1.0;
    return (Number(amountInCurrentCurrency) || 0) * rate;
  };

  /**
   * Format numbers with thousands spaces (e.g. "1 250 000.00 ₽")
   */
  const formatMoney = (amountInRub) => {
    const converted = convertAmount(amountInRub);
    const formatted = new Intl.NumberFormat('ru-RU', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(converted);
    return `${formatted} ${currentCurrencySymbol.value}`;
  };

  const formatNumber = (num) => {
    return new Intl.NumberFormat('ru-RU', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(num);
  };

  const formatPercent = (num) => {
    return new Intl.NumberFormat('ru-RU', {
      minimumFractionDigits: 1,
      maximumFractionDigits: 1,
    }).format(num);
  };

  const fetchCurrencies = async () => {
    try {
      const res = await api.get('/currencies');
      currencies.value = res.data;
    } catch (err) {
      console.warn('Could not fetch external CBR currency rates, using defaults.');
    }
  };

  return {
    currentCurrency,
    currencies,
    currentRate,
    currentCurrencySymbol,
    setCurrency,
    convertAmount,
    toRubles,
    formatMoney,
    formatNumber,
    formatPercent,
    fetchCurrencies,
  };
}
