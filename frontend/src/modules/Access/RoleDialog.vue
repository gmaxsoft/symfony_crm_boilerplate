<template>
  <v-dialog
    :model-value="modelValue"
    max-width="480"
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
          mdi-shield-key-outline
        </v-icon>
        {{ role ? 'Edytuj rolę' : 'Nowa rola' }}
      </v-card-title>
      <v-divider color="rgba(255,255,255,0.07)" />

      <v-card-text class="pt-5">
        <v-text-field
          v-model="form.name"
          label="Nazwa roli *"
          color="success"
          class="mb-3"
        />
        <v-textarea
          v-model="form.description"
          label="Opis"
          rows="3"
          color="success"
        />
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
import type { Role } from '@/types'

const props = defineProps<{ modelValue: boolean; role: Role | null }>()
const emit  = defineEmits<{
  (e: 'update:modelValue', v: boolean): void
  (e: 'saved', data: { name: string; description: string | null }): void
}>()

const form = ref({ name: '', description: '' })

watch(() => props.modelValue, (open) => {
  if (open) form.value = { name: props.role?.name ?? '', description: props.role?.description ?? '' }
})

function save() {
  if (!form.value.name.trim()) return
  emit('saved', { name: form.value.name, description: form.value.description || null })
  emit('update:modelValue', false)
}
</script>

<style scoped>
.dialog-title { font-size: 0.95rem !important; font-weight: 600; padding: 16px 20px !important; color: #f1f5f9; }
</style>
