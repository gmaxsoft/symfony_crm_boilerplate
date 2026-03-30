import { apiClient } from './client'

export const authApi = {
  login: (email: string, password: string) =>
    apiClient.post<{ token: string }>('/api/auth/login', { email, password }),

  me: () =>
    apiClient.get('/api/auth/me'),
}
