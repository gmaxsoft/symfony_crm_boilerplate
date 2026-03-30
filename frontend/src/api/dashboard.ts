import { apiClient } from './client'
import type { ApiResponse, DashboardStats } from '@/types'

export const dashboardApi = {
  stats: () => apiClient.get<ApiResponse<DashboardStats>>('/api/dashboard'),
}
