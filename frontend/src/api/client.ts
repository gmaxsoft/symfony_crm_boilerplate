import axios from 'axios'

// W trybie deweloperskim (Vite proxy) VITE_API_URL powinno być puste —
// zapytania /api/* są przekierowane przez proxy do backendu bez problemów CORS.
// W produkcji ustaw VITE_API_URL=https://api.twoja-domena.pl
const baseURL = import.meta.env.VITE_API_URL ?? ''

const apiClient = axios.create({
  baseURL,
  headers: { 'Content-Type': 'application/json' },
  timeout: 10000,
})

apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('venom_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

apiClient.interceptors.response.use(
  (res) => res,
  (error) => {
    // Przekieruj do logowania tylko gdy sesja wygasła (mamy token ale dostaliśmy 401)
    // NIE przekierowuj podczas samego procesu logowania
    const hasToken = !!localStorage.getItem('venom_token')
    const isLoginPage = window.location.pathname === '/login'
    if (error.response?.status === 401 && hasToken && !isLoginPage) {
      localStorage.removeItem('venom_token')
      localStorage.removeItem('venom_user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export { apiClient }
