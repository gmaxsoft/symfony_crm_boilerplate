<template>
  <v-dialog
    :model-value="modelValue"
    max-width="640"
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
          mdi-account-multiple-outline
        </v-icon>
        {{ customer ? 'Edytuj kontrahenta' : 'Nowy kontrahent' }}
      </v-card-title>
      <v-divider color="rgba(255,255,255,0.07)" />

      <v-card-text class="pt-5">
        <v-row dense>
          <v-col cols="12">
            <v-text-field
              v-model="form.name"
              label="Nazwa *"
              color="success"
            />
          </v-col>
          <v-col
            cols="12"
            sm="6"
          >
            <v-text-field
              v-model="form.email"
              label="E-mail"
              type="email"
              color="success"
            />
          </v-col>
          <v-col
            cols="12"
            sm="6"
          >
            <v-text-field
              v-model="form.phone"
              label="Telefon"
              color="success"
            />
          </v-col>
          <v-col
            cols="12"
            sm="6"
          >
            <v-text-field
              v-model="form.nip"
              label="NIP"
              color="success"
            />
          </v-col>
          <v-col
            cols="12"
            sm="6"
          >
            <v-select
              v-model="form.status"
              label="Status"
              :items="statuses"
              item-title="label"
              item-value="value"
              color="success"
            />
          </v-col>
          <v-col cols="12">
            <v-text-field
              v-model="form.address"
              label="Adres"
              color="success"
            />
          </v-col>
          <v-col
            cols="12"
            sm="5"
          >
            <v-text-field
              v-model="form.city"
              label="Miasto"
              color="success"
            />
          </v-col>
          <v-col
            cols="12"
            sm="3"
          >
            <v-text-field
              v-model="form.zipCode"
              label="Kod pocztowy"
              color="success"
            />
          </v-col>
          <v-col
            cols="12"
            sm="4"
          >
            <v-text-field
              v-model="form.country"
              label="Kraj"
              color="success"
            />
          </v-col>
          <v-col cols="12">
            <v-select
              v-model="form.assignedToId"
              label="Opiekun"
              :items="users"
              item-title="fullName"
              item-value="id"
              color="success"
              clearable
            />
          </v-col>
          <v-col cols="12">
            <v-textarea
              v-model="form.notes"
              label="Notatki"
              rows="3"
              color="success"
            />
          </v-col>
        </v-row>
      </v-card-text>

      <v-divider color="rgba(255,255,255,0.07)" />
      <v-card-actions class="pa-4">
        <v-spacer />
        <v-btn
          variant="text"
          color="default"
          @click="$emit('update:modelValue', false)"
        >
          Anuluj
        </v-btn>
        <v-btn
          color="success"
          :loading="saving"
          @click="save"
        >
          <v-icon start>
            mdi-content-save-outline
          </v-icon>
          Zapisz
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import type { Customer, AdminUser } from '@/types'

const props = defineProps<{
  modelValue: boolean
  customer: Customer | null
  users: Pick<AdminUser, 'id' | 'fullName'>[]
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', v: boolean): void
  (e: 'saved', data: Record<string, unknown>): void
}>()

const saving = ref(false)

const blank = () => ({
  name: '', email: '', phone: '', nip: '',
  address: '', city: '', zipCode: '', country: '',
  notes: '', status: 'active', assignedToId: null as number | null,
})

const form = ref(blank())

const statuses = [
  { label: 'Aktywny',     value: 'active'   },
  { label: 'Nieaktywny',  value: 'inactive' },
  { label: 'Prospekt',    value: 'prospect' },
]

watch(() => props.modelValue, (open) => {
  if (open) {
    if (props.customer) {
      form.value = {
        name:         props.customer.name,
        email:        props.customer.email ?? '',
        phone:        props.customer.phone ?? '',
        nip:          props.customer.nip ?? '',
        address:      props.customer.address ?? '',
        city:         props.customer.city ?? '',
        zipCode:      props.customer.zipCode ?? '',
        country:      props.customer.country ?? '',
        notes:        props.customer.notes ?? '',
        status:       props.customer.status,
        assignedToId: props.customer.assignedTo?.id ?? null,
      }
    } else {
      form.value = blank()
    }
  }
})

function save() {
  if (!form.value.name.trim()) return
  emit('saved', { ...form.value })
  emit('update:modelValue', false)
}
</script>

<style scoped>
.dialog-title {
  font-size: 0.95rem !important;
  font-weight: 600;
  padding: 16px 20px !important;
  color: #f1f5f9;
}
</style>
