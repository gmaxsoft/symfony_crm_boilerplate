export interface AuthUser {
  id: number
  email: string
  firstName: string
  lastName: string
  fullName: string
  role: string
  isActive: boolean
}

export interface Role {
  id: number
  name: string
  description: string | null
}

export interface AdminUser {
  id: number
  email: string
  firstName: string
  lastName: string
  fullName: string
  role: { id: number; name: string }
  isActive: boolean
  createdAt: string
  updatedAt: string | null
}

export interface Customer {
  id: number
  name: string
  email: string | null
  phone: string | null
  nip: string | null
  address: string | null
  city: string | null
  zipCode: string | null
  country: string | null
  notes: string | null
  status: string
  assignedTo: { id: number; fullName: string } | null
  createdAt: string
  updatedAt: string | null
}

export interface PaginatedResponse<T> {
  status: string
  data: T[]
  meta: { total: number; page: number; per_page: number; pages: number }
}

export interface ApiResponse<T> {
  status: string
  data: T
}

export interface DashboardStats {
  stats: { customers: number; users: number }
  notice: string
}
