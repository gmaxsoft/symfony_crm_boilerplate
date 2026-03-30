<template>
  <div class="login-wrap">
    <v-card class="login-card" color="#161c2d" rounded="0">
      <!-- Header -->
      <div class="login-header">
        <div class="logo-ring">
          <v-icon size="28" color="#4ade80">mdi-snake</v-icon>
        </div>
        <h1 class="login-title">VENOM CRM</h1>
        <p class="login-sub">System zarządzania relacjami z klientami</p>
      </div>

      <v-card-text class="px-8 pt-2 pb-8">
        <!-- Error -->
        <v-alert
          v-if="error"
          type="error"
          variant="tonal"
          density="compact"
          rounded="0"
          class="mb-5"
          closable
          @click:close="error = ''"
        >{{ error }}</v-alert>

        <v-form @submit.prevent="submit">
          <label class="field-label">Adres e-mail</label>
          <v-text-field
            v-model="form.email"
            type="email"
            placeholder="admin@venom.pl"
            prepend-inner-icon="mdi-email-outline"
            color="success"
            class="mb-3"
            rounded="0"
            autocomplete="email"
            :disabled="loading"
          />

          <label class="field-label">Hasło</label>
          <v-text-field
            v-model="form.password"
            :type="showPwd ? 'text' : 'password'"
            placeholder="••••••••"
            prepend-inner-icon="mdi-lock-outline"
            :append-inner-icon="showPwd ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
            color="success"
            class="mb-6"
            rounded="0"
            autocomplete="current-password"
            :disabled="loading"
            @click:append-inner="showPwd = !showPwd"
          />

          <v-btn
            type="submit"
            color="success"
            size="large"
            block
            rounded="0"
            :loading="loading"
          >
            <v-icon start>mdi-login</v-icon>
            Zaloguj się
          </v-btn>
        </v-form>
      </v-card-text>

      <div class="login-footer">
        VENOM CRM &copy; {{ year }}
      </div>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router    = useRouter()
const authStore = useAuthStore()

const year    = new Date().getFullYear()
const loading = ref(false)
const error   = ref('')
const showPwd = ref(false)
const form    = reactive({ email: '', password: '' })

async function submit() {
  if (!form.email || !form.password) {
    error.value = 'Wypełnij wszystkie pola.'
    return
  }
  error.value = ''
  loading.value = true
  try {
    await authStore.login(form.email, form.password)
    router.push('/dashboard')
  } catch (e: any) {
    if (!e.response) {
      error.value = 'Błąd połączenia z serwerem. Upewnij się, że backend działa.'
    } else if (e.response.status === 401) {
      error.value = e.response.data?.message || 'Nieprawidłowy e-mail lub hasło.'
    } else {
      error.value = e.response.data?.message || `Błąd serwera (${e.response.status}). Spróbuj ponownie.`
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-wrap {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.login-card {
  width: 440px;
  max-width: 100%;
  border: 1px solid rgba(255, 255, 255, 0.07);
  overflow: hidden;
}

.login-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px 32px 28px;
  background: linear-gradient(180deg, rgba(6, 79, 60, 0.4) 0%, transparent 100%);
}

.logo-ring {
  width: 56px;
  height: 56px;
  background: rgba(74, 222, 128, 0.12);
  border: 1px solid rgba(74, 222, 128, 0.25);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 14px;
}

.login-title {
  font-size: 1.5rem;
  font-weight: 800;
  letter-spacing: 2px;
  color: #f1f5f9;
  margin-bottom: 4px;
}

.login-sub {
  font-size: 0.78rem;
  color: #64748b;
  text-align: center;
}

.field-label {
  display: block;
  font-size: 0.75rem;
  font-weight: 500;
  color: #94a3b8;
  margin-bottom: 4px;
  margin-left: 2px;
}

.login-footer {
  padding: 14px;
  text-align: center;
  font-size: 0.7rem;
  color: #334155;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}
</style>
