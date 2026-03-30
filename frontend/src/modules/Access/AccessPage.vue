<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-5">
      <div>
        <h2 class="page-heading">Uprawnienia</h2>
        <p class="page-sub">Zarządzanie rolami i poziomami dostępu</p>
      </div>
      <v-btn color="success" prepend-icon="mdi-plus" @click="openCreate">
        Nowa rola
      </v-btn>
    </div>

    <!-- Role cards -->
    <v-row v-if="!loading">
      <v-col v-for="role in roles" :key="role.id" cols="12" sm="6" lg="4">
        <v-card color="#161c2d" border class="role-card">
          <v-card-text>
            <div class="d-flex align-center justify-space-between mb-3">
              <div class="role-icon-wrap">
                <v-icon :color="roleColor(role.name)" size="20">{{ roleIcon(role.name) }}</v-icon>
              </div>
              <div class="d-flex gap-1">
                <v-btn icon size="x-small" variant="text" color="info" @click="openEdit(role)">
                  <v-icon size="16">mdi-pencil-outline</v-icon>
                </v-btn>
                <v-btn icon size="x-small" variant="text" color="error" @click="confirmDelete(role)">
                  <v-icon size="16">mdi-trash-can-outline</v-icon>
                </v-btn>
              </div>
            </div>
            <div class="role-name">{{ role.name }}</div>
            <div class="role-desc">{{ role.description ?? 'Brak opisu' }}</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <div v-else class="d-flex justify-center py-12">
      <v-progress-circular indeterminate color="success" size="40" />
    </div>

    <RoleDialog
      v-model="dialog"
      :role="editRole"
      @saved="onSaved"
    />

    <!-- Delete confirm -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card color="#161c2d" border>
        <v-card-title class="dialog-title">
          <v-icon start color="error" size="20">mdi-trash-can-outline</v-icon>Usuń rolę
        </v-card-title>
        <v-card-text class="text-body-2">
          Czy na pewno chcesz usunąć rolę <strong>{{ deleteRole?.name }}</strong>?
          Operacja jest niemożliwa jeśli rola jest przypisana do użytkowników.
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
import { accessApi } from '@/api/access'
import { useNotify } from '@/composables/useNotify'
import type { Role } from '@/types'
import RoleDialog from './RoleDialog.vue'

const { notify } = useNotify()
const loading     = ref(false)
const roles       = ref<Role[]>([])
const dialog      = ref(false)
const editRole    = ref<Role | null>(null)
const deleteDialog = ref(false)
const deleteRole   = ref<Role | null>(null)
const deleting     = ref(false)

function roleColor(name: string) {
  if (name.includes('Admin'))       return 'error'
  if (name.includes('administrac')) return 'warning'
  return 'success'
}
function roleIcon(name: string) {
  if (name.includes('Admin'))       return 'mdi-shield-crown-outline'
  if (name.includes('administrac')) return 'mdi-shield-account-outline'
  return 'mdi-shield-outline'
}

async function load() {
  loading.value = true
  try {
    const res = await accessApi.list()
    roles.value = res.data.data
  } catch { notify('Błąd ładowania ról.', 'error') }
  finally { loading.value = false }
}

function openCreate() { editRole.value = null; dialog.value = true }
function openEdit(r: Role) { editRole.value = r; dialog.value = true }
function confirmDelete(r: Role) { deleteRole.value = r; deleteDialog.value = true }

async function onSaved(data: { name: string; description: string | null }) {
  try {
    if (editRole.value) {
      await accessApi.update(editRole.value.id, data)
      notify('Rola zaktualizowana.')
    } else {
      await accessApi.create(data)
      notify('Rola dodana.')
    }
    load()
  } catch { notify('Błąd zapisu.', 'error') }
}

async function doDelete() {
  if (!deleteRole.value) return
  deleting.value = true
  try {
    await accessApi.delete(deleteRole.value.id)
    notify('Rola usunięta.', 'warning')
    deleteDialog.value = false
    load()
  } catch (e: any) {
    notify(e.response?.data?.message ?? 'Błąd usuwania.', 'error')
  } finally { deleting.value = false }
}

onMounted(load)
</script>

<style scoped>
.page-heading { font-size: 1.4rem; font-weight: 700; color: #f1f5f9; }
.page-sub     { font-size: 0.8rem; color: #64748b; margin-top: 2px; }
.dialog-title { font-size: 0.95rem !important; font-weight: 600; padding: 16px 20px !important; color: #f1f5f9; }

.role-card { border: 1px solid rgba(255,255,255,0.07) !important; transition: border-color 0.2s; }
.role-card:hover { border-color: rgba(74,222,128,0.3) !important; }

.role-icon-wrap {
  width: 40px; height: 40px; border-radius: 10px;
  background: rgba(255,255,255,0.06);
  display: flex; align-items: center; justify-content: center;
}
.role-name { font-size: 0.95rem; font-weight: 600; color: #f1f5f9; margin-bottom: 4px; }
.role-desc { font-size: 0.75rem; color: #64748b; }
</style>
