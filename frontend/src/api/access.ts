import { apiClient } from './client'
import type { ApiResponse, Role } from '@/types'

export const accessApi = {
  list: () =>
    apiClient.get<ApiResponse<Role[]>>('/api/access/roles'),

  create: (data: { name: string; description?: string | null }) =>
    apiClient.post<ApiResponse<Role>>('/api/access/roles', data),

  update: (id: number, data: { name: string; description?: string | null }) =>
    apiClient.put<ApiResponse<Role>>(`/api/access/roles/${id}`, data),

  delete: (id: number) =>
    apiClient.delete(`/api/access/roles/${id}`),
}
