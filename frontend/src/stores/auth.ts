import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api/auth'
import type { AuthUser } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('venom_token'))
  const user = ref<AuthUser | null>(
    JSON.parse(localStorage.getItem('venom_user') || 'null')
  )

  const isAuthenticated = computed(() => !!token.value)
  const initials = computed(() => {
    const fn = user.value?.firstName?.trim()
    const ln = user.value?.lastName?.trim()
    if (!fn?.[0] || !ln?.[0]) return '?'
    return (fn[0] + ln[0]).toUpperCase()
  })

  async function login(email: string, password: string) {
    const res = await authApi.login(email, password)
    token.value = res.data.token
    localStorage.setItem('venom_token', res.data.token)
    await fetchMe()
  }

  async function fetchMe() {
    const res = await authApi.me()
    user.value = res.data.data
    localStorage.setItem('venom_user', JSON.stringify(user.value))
  }

  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('venom_token')
    localStorage.removeItem('venom_user')
  }

  return { token, user, isAuthenticated, initials, login, fetchMe, logout }
})
