import { reactive } from 'vue';

const state = reactive({
  show: false,
  message: '',
  type: 'success', // 'success' | 'error' | 'info'
});

let timer = null;

export function useToast() {
  const showToast = (message, type = 'success', duration = 3500) => {
    state.message = message;
    state.type = type;
    state.show = true;

    clearTimeout(timer);
    timer = setTimeout(() => {
      state.show = false;
    }, duration);
  };

  const hideToast = () => {
    state.show = false;
  };

  return {
    toast: state,
    showToast,
    hideToast,
  };
}
