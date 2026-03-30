import { apiClient } from './client'
import type { ApiResponse, AdminUser } from '@/types'

export const adminApi = {
  list: () =>
    apiClient.get<ApiResponse<AdminUser[]>>('/api/admin/users'),

  create: (data: {
    email: string
    password: string
    firstName: string
    lastName: string
    roleId: number
    isActive: boolean
  }) => apiClient.post<ApiResponse<AdminUser>>('/api/admin/users', data),

  update: (id: number, data: {
    firstName: string
    lastName: string
    roleId: number
    isActive: boolean
    password?: string
  }) => apiClient.put<ApiResponse<AdminUser>>(`/api/admin/users/${id}`, data),

  delete: (id: number) =>
    apiClient.delete(`/api/admin/users/${id}`),
}
