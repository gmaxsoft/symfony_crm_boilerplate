import { ref } from 'vue'

const snack = ref({ show: false, text: '', color: 'success' })

export function useNotify() {
  function notify(text: string, color: 'success' | 'error' | 'warning' | 'info' = 'success') {
    snack.value = { show: true, text, color }
  }
  return { snack, notify }
}
