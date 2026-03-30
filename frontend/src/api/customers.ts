import { apiClient } from './client'
import type { PaginatedResponse, ApiResponse, Customer } from '@/types'

export const customersApi = {
  list: (page = 1, perPage = 20, search?: string) =>
    apiClient.get<PaginatedResponse<Customer>>('/api/customers', {
      params: { page, perPage, search },
    }),

  get: (id: number) =>
    apiClient.get<ApiResponse<Customer>>(`/api/customers/${id}`),

  create: (data: Partial<Customer> & { assignedToId?: number | null }) =>
    apiClient.post<ApiResponse<Customer>>('/api/customers', data),

  update: (id: number, data: Partial<Customer> & { assignedToId?: number | null }) =>
    apiClient.put<ApiResponse<Customer>>(`/api/customers/${id}`, data),

  delete: (id: number) =>
    apiClient.delete(`/api/customers/${id}`),
}
