<template>
  <div>
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-5">
      <div>
        <h2 class="page-heading">
          Kontrahenci
        </h2>
        <p class="page-sub">
          Zarządzanie kontrahentami systemu CRM
        </p>
      </div>
      <v-btn
        color="success"
        prepend-icon="mdi-plus"
        @click="openCreate"
      >
        Nowy kontrahent
      </v-btn>
    </div>

    <!-- Filters -->
    <v-card
      color="#161c2d"
      border
      class="mb-5"
    >
      <v-card-text class="py-3">
        <v-row
          dense
          align="center"
        >
          <v-col
            cols="12"
            sm="6"
            md="4"
          >
            <v-text-field
              v-model="search"
              placeholder="Szukaj (nazwa, e-mail, NIP)…"
              prepend-inner-icon="mdi-magnify"
              density="compact"
              hide-details
              color="success"
              clearable
              @update:model-value="onSearch"
            />
          </v-col>
          <v-col
            cols="auto"
            class="ms-auto"
          >
            <v-chip
              :color="total > 0 ? 'success' : 'default'"
              variant="tonal"
              size="small"
            >
              {{ total }} rekordów
            </v-chip>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Table -->
    <v-card
      color="#161c2d"
      border
    >
      <v-data-table
        :headers="headers"
        :items="items"
        :loading="loading"
        :items-per-page="perPage"
        :items-length="total"
        class="crm-table"
        loading-text="Ładowanie danych…"
        no-data-text="Brak kontrahentów"
        @update:options="onOptions"
      >
        <!-- Status chip -->
        <template #item.status="{ item }">
          <v-chip
            :color="statusColor(item.status)"
            variant="tonal"
            size="x-small"
            label
          >
            {{ statusLabel(item.status) }}
          </v-chip>
        </template>

        <!-- Assigned -->
        <template #item.assignedTo="{ item }">
          <span class="text-caption">{{ item.assignedTo?.fullName ?? '—' }}</span>
        </template>

        <!-- Date -->
        <template #item.createdAt="{ item }">
          <span class="text-caption text-medium-emphasis">{{ fmtDate(item.createdAt) }}</span>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <v-btn
            icon
            size="x-small"
            variant="text"
            color="info"
            @click="openEdit(item)"
          >
            <v-icon size="16">
              mdi-pencil-outline
            </v-icon>
          </v-btn>
          <v-btn
            icon
            size="x-small"
            variant="text"
            color="error"
            @click="confirmDelete(item)"
          >
            <v-icon size="16">
              mdi-trash-can-outline
            </v-icon>
          </v-btn>
        </template>
      </v-data-table>
    </v-card>

    <!-- Dialog CRUD -->
    <CustomerDialog
      v-model="dialog"
      :customer="editItem"
      :users="userList"
      @saved="onSaved"
    />

    <!-- Delete confirm -->
    <v-dialog
      v-model="deleteDialog"
      max-width="400"
    >
      <v-card
        color="#161c2d"
        border
      >
        <v-card-title class="dialog-title">
          <v-icon
            start
            color="error"
            size="20"
          >
            mdi-trash-can-outline
          </v-icon>
          Usuń kontrahenta
        </v-card-title>
        <v-card-text class="text-body-2">
          Czy na pewno chcesz usunąć <strong>{{ deleteItem?.name }}</strong>? Tej operacji nie można cofnąć.
        </v-card-text>
        <v-card-actions class="pa-4">
          <v-spacer />
          <v-btn
            variant="text"
            @click="deleteDialog = false"
          >
            Anuluj
          </v-btn>
          <v-btn
            color="error"
            :loading="deleting"
            @click="doDelete"
          >
            Usuń
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { customersApi } from '@/api/customers'
import { adminApi } from '@/api/admin'
import { useNotify } from '@/composables/useNotify'
import type { Customer, AdminUser } from '@/types'
import CustomerDialog from './CustomerDialog.vue'

const { notify }  = useNotify()
const loading     = ref(false)
const items       = ref<Customer[]>([])
const total       = ref(0)
const page        = ref(1)
const perPage     = ref(20)
const search      = ref('')
const userList    = ref<Pick<AdminUser, 'id' | 'fullName'>[]>([])
const dialog      = ref(false)
const editItem    = ref<Customer | null>(null)
const deleteDialog = ref(false)
const deleteItem   = ref<Customer | null>(null)
const deleting     = ref(false)
let searchTimer: ReturnType<typeof setTimeout>

const headers = [
  { title: 'Nazwa',      key: 'name',       sortable: true  },
  { title: 'E-mail',     key: 'email',      sortable: false },
  { title: 'Telefon',    key: 'phone',      sortable: false },
  { title: 'NIP',        key: 'nip',        sortable: false },
  { title: 'Status',     key: 'status',     sortable: true  },
  { title: 'Opiekun',    key: 'assignedTo', sortable: false },
  { title: 'Dodano',     key: 'createdAt',  sortable: true  },
  { title: '',           key: 'actions',    sortable: false, align: 'end' as const },
]

function statusColor(s: string) {
  return s === 'active' ? 'success' : s === 'prospect' ? 'info' : 'default'
}
function statusLabel(s: string) {
  return s === 'active' ? 'Aktywny' : s === 'prospect' ? 'Prospekt' : 'Nieaktywny'
}
function fmtDate(d: string) {
  return new Date(d).toLocaleDateString('pl-PL')
}

async function load() {
  loading.value = true
  try {
    const res = await customersApi.list(page.value, perPage.value, search.value || undefined)
    items.value = res.data.data
    total.value = res.data.meta.total
  } catch { notify('Błąd ładowania kontrahentów.', 'error') }
  finally { loading.value = false }
}

function onOptions(opts: { page: number; itemsPerPage: number }) {
  page.value    = opts.page
  perPage.value = opts.itemsPerPage
  load()
}

function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; load() }, 400)
}

function openCreate() { editItem.value = null; dialog.value = true }
function openEdit(c: Customer) { editItem.value = c; dialog.value = true }
function confirmDelete(c: Customer) { deleteItem.value = c; deleteDialog.value = true }

async function onSaved(data: Record<string, unknown>) {
  try {
    if (editItem.value) {
      await customersApi.update(editItem.value.id, data as Partial<Customer>)
      notify('Kontrahent zaktualizowany.')
    } else {
      await customersApi.create(data as Partial<Customer>)
      notify('Kontrahent dodany.')
    }
    load()
  } catch { notify('Błąd zapisu.', 'error') }
}

async function doDelete() {
  if (!deleteItem.value) return
  deleting.value = true
  try {
    await customersApi.delete(deleteItem.value.id)
    notify('Kontrahent usunięty.', 'warning')
    deleteDialog.value = false
    load()
  } catch { notify('Błąd usuwania.', 'error') }
  finally { deleting.value = false }
}

onMounted(async () => {
  const res = await adminApi.list()
  userList.value = res.data.data.map(u => ({ id: u.id, fullName: u.fullName }))
  load()
})
</script>

<style scoped>
.page-heading { font-size: 1.4rem; font-weight: 700; color: #f1f5f9; }
.page-sub     { font-size: 0.8rem; color: #64748b; margin-top: 2px; }
.dialog-title { font-size: 0.95rem !important; font-weight: 600; padding: 16px 20px !important; color: #f1f5f9; }

:deep(.crm-table .v-data-table__thead th) {
  background: rgba(255,255,255,0.03) !important;
}
</style>
