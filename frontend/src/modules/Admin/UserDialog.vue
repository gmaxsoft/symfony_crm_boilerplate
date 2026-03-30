<template>
  <v-dialog
    :model-value="modelValue"
    max-width="520"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <v-card
      color="#161c2d"
      border
    >
      <v-card-title class="dialog-title">
        <v-icon
          start
          color="success"
          size="20"
        >
          mdi-account-cog-outline
        </v-icon>
        {{ user ? 'Edytuj użytkownika' : 'Nowy użytkownik' }}
      </v-card-title>
      <v-divider color="rgba(255,255,255,0.07)" />

      <v-card-text class="pt-5">
        <v-row dense>
          <v-col
            cols="12"
            sm="6"
          >
            <v-text-field
              v-model="form.firstName"
              label="Imię *"
              color="success"
            />
          </v-col>
          <v-col
            cols="12"
            sm="6"
          >
            <v-text-field
              v-model="form.lastName"
              label="Nazwisko *"
              color="success"
            />
          </v-col>
          <v-col cols="12">
            <v-text-field
              v-model="form.email"
              label="E-mail *"
              type="email"
              color="success"
              :disabled="!!user"
            />
          </v-col>
          <v-col cols="12">
            <v-text-field
              v-model="form.password"
              :label="user ? 'Nowe hasło (puste = bez zmian)' : 'Hasło *'"
              :type="showPwd ? 'text' : 'password'"
              :append-inner-icon="showPwd ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
              color="success"
              @click:append-inner="showPwd = !showPwd"
            />
          </v-col>
          <v-col
            cols="12"
            sm="8"
          >
            <v-select
              v-model="form.roleId"
              label="Rola *"
              :items="roles"
              item-title="name"
              item-value="id"
              color="success"
            />
          </v-col>
          <v-col
            cols="12"
            sm="4"
            class="d-flex align-center"
          >
            <v-switch
              v-model="form.isActive"
              label="Aktywny"
              color="success"
              hide-details
              inset
            />
          </v-col>
        </v-row>
      </v-card-text>

      <v-divider color="rgba(255,255,255,0.07)" />
      <v-card-actions class="pa-4">
        <v-spacer />
        <v-btn
          variant="text"
          @click="$emit('update:modelValue', false)"
        >
          Anuluj
        </v-btn>
        <v-btn
          color="success"
          @click="save"
        >
          <v-icon start>
            mdi-content-save-outline
          </v-icon>Zapisz
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import type { AdminUser, Role } from '@/types'

const props = defineProps<{
  modelValue: boolean
  user: AdminUser | null
  roles: Role[]
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', v: boolean): void
  (e: 'saved', data: Record<string, unknown>): void
}>()

const showPwd = ref(false)
const form = ref({
  firstName: '', lastName: '', email: '',
  password: '', roleId: null as number | null, isActive: true,
})

watch(() => props.modelValue, (open) => {
  if (open) {
    form.value = {
      firstName: props.user?.firstName ?? '',
      lastName:  props.user?.lastName  ?? '',
      email:     props.user?.email     ?? '',
      password:  '',
      roleId:    props.user?.role.id   ?? null,
      isActive:  props.user?.isActive  ?? true,
    }
    showPwd.value = false
  }
})

function save() {
  if (!form.value.firstName || !form.value.lastName || !form.value.email || !form.value.roleId) return
  const data: Record<string, unknown> = {
    firstName: form.value.firstName,
    lastName:  form.value.lastName,
    email:     form.value.email,
    roleId:    form.value.roleId,
    isActive:  form.value.isActive,
  }
  if (form.value.password) data.password = form.value.password
  emit('saved', data)
  emit('update:modelValue', false)
}
</script>

<style scoped>
.dialog-title { font-size: 0.95rem !important; font-weight: 600; padding: 16px 20px !important; color: #f1f5f9; }
</style>
