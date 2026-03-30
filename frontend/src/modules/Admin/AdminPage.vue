<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-5">
      <div>
        <h2 class="page-heading">Użytkownicy systemu</h2>
        <p class="page-sub">Zarządzanie kontami użytkowników CRM</p>
      </div>
      <v-btn color="success" prepend-icon="mdi-plus" @click="openCreate">
        Nowy użytkownik
      </v-btn>
    </div>

    <v-card color="#161c2d" border>
      <v-data-table
        :headers="headers"
        :items="users"
        :loading="loading"
        class="crm-table"
        loading-text="Ładowanie…"
        no-data-text="Brak użytkowników"
      >
        <!-- Avatar + name -->
        <template #item.fullName="{ item }">
          <div class="d-flex align-center gap-2">
            <v-avatar color="success" size="28" class="text-caption font-weight-bold">
              {{ initials(item) }}
            </v-avatar>
            <div>
              <div class="text-body-2 font-weight-medium">{{ item.fullName }}</div>
              <div class="text-caption text-medium-emphasis">{{ item.email }}</div>
            </div>
          </div>
        </template>

        <!-- Role chip -->
        <template #item.role="{ item }">
          <v-chip :color="roleColor(item.role.name)" variant="tonal" size="x-small" label>
            {{ item.role.name }}
          </v-chip>
        </template>

        <!-- Active -->
        <template #item.isActive="{ item }">
          <v-icon
            :color="item.isActive ? 'success' : 'error'"
            size="18"
          >
            {{ item.isActive ? 'mdi-check-circle-outline' : 'mdi-close-circle-outline' }}
          </v-icon>
        </template>

        <!-- Date -->
        <template #item.createdAt="{ item }">
          <span class="text-caption text-medium-emphasis">{{ fmtDate(item.createdAt) }}</span>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <v-btn icon size="x-small" variant="text" color="info" @click="openEdit(item)">
            <v-icon size="16">mdi-pencil-outline</v-icon>
          </v-btn>
          <v-btn icon size="x-small" variant="text" color="error" @click="confirmDelete(item)">
            <v-icon size="16">mdi-trash-can-outline</v-icon>
          </v-btn>
        </template>
      </v-data-table>
    </v-card>

    <UserDialog
      v-model="dialog"
      :user="editUser"
      :roles="roles"
      @saved="onSaved"
    />

    <!-- Delete confirm -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card color="#161c2d" border>
        <v-card-title class="dialog-title">
          <v-icon start color="error" size="20">mdi-trash-can-outline</v-icon>Usuń użytkownika
        </v-card-title>
        <v-card-text class="text-body-2">
          Czy na pewno chcesz usunąć <strong>{{ deleteUser?.fullName }}</strong>?
        </v-card-text>
        <v-card-actions class="pa-4">
          <v-spacer />
          <v-btn variant="text" @click="deleteDialog = false">Anuluj</v-btn>
          <v-btn color="error" :loading="deleting" @click="doDelete">Usuń</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { adminApi } from '@/api/admin'
import { accessApi } from '@/api/access'
import { useNotify } from '@/composables/useNotify'
import type { AdminUser, Role } from '@/types'
import UserDialog from './UserDialog.vue'

const { notify } = useNotify()
const loading     = ref(false)
const users       = ref<AdminUser[]>([])
const roles       = ref<Role[]>([])
const dialog      = ref(false)
const editUser    = ref<AdminUser | null>(null)
const deleteDialog = ref(false)
const deleteUser   = ref<AdminUser | null>(null)
const deleting     = ref(false)

const headers = [
  { title: 'Użytkownik', key: 'fullName',  sortable: true  },
  { title: 'Rola',       key: 'role',       sortable: false },
  { title: 'Aktywny',    key: 'isActive',   sortable: true  },
  { title: 'Dodano',     key: 'createdAt',  sortable: true  },
  { title: '',           key: 'actions',    sortable: false, align: 'end' as const },
]

function initials(u: AdminUser) {
  return (u.firstName[0] + u.lastName[0]).toUpperCase()
}
function roleColor(name: string) {
  if (name.includes('Admin'))       return 'error'
  if (name.includes('administrac')) return 'warning'
  return 'success'
}
function fmtDate(d: string) {
  return new Date(d).toLocaleDateString('pl-PL')
}

async function load() {
  loading.value = true
  try {
    const [uRes, rRes] = await Promise.all([adminApi.list(), accessApi.list()])
    users.value = uRes.data.data
    roles.value = rRes.data.data
  } catch { notify('Błąd ładowania.', 'error') }
  finally { loading.value = false }
}

function openCreate() { editUser.value = null; dialog.value = true }
function openEdit(u: AdminUser) { editUser.value = u; dialog.value = true }
function confirmDelete(u: AdminUser) { deleteUser.value = u; deleteDialog.value = true }

async function onSaved(data: Record<string, unknown>) {
  try {
    if (editUser.value) {
      await adminApi.update(editUser.value.id, data as any)
      notify('Użytkownik zaktualizowany.')
    } else {
      await adminApi.create(data as any)
      notify('Użytkownik dodany.')
    }
    load()
  } catch (e: any) {
    notify(e.response?.data?.message ?? 'Błąd zapisu.', 'error')
  }
}

async function doDelete() {
  if (!deleteUser.value) return
  deleting.value = true
  try {
    await adminApi.delete(deleteUser.value.id)
    notify('Użytkownik usunięty.', 'warning')
    deleteDialog.value = false
    load()
  } catch { notify('Błąd usuwania.', 'error') }
  finally { deleting.value = false }
}

onMounted(load)
</script>

<style scoped>
.page-heading { font-size: 1.4rem; font-weight: 700; color: #f1f5f9; }
.page-sub     { font-size: 0.8rem; color: #64748b; margin-top: 2px; }
.dialog-title { font-size: 0.95rem !important; font-weight: 600; padding: 16px 20px !important; color: #f1f5f9; }
:deep(.crm-table .v-data-table__thead th) { background: rgba(255,255,255,0.03) !important; }
</style>
