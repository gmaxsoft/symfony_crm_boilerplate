<template>
  <v-app theme="venomDark">

    <!-- ── SIDEBAR ──────────────────────────────────────── -->
    <v-navigation-drawer
      v-model="drawer"
      :rail="rail"
      :width="256"
      :rail-width="64"
      permanent
      class="sidebar"
    >
      <!-- Brand -->
      <div class="brand" :class="{ 'brand--rail': rail }">
        <div class="brand-icon-wrap">
          <v-icon size="22" color="#4ade80">mdi-snake</v-icon>
        </div>
        <transition name="label-fade">
          <span v-if="!rail" class="brand-name">VENOM CRM</span>
        </transition>
      </div>

      <v-divider class="sidebar-divider" />

      <!-- Navigation -->
      <v-list nav density="compact" class="nav-list">
        <template v-for="item in navItems" :key="item.to">
          <v-tooltip :text="item.title" location="end" :disabled="!rail">
            <template #activator="{ props }">
              <v-list-item
                v-bind="props"
                :to="item.to"
                :prepend-icon="item.icon"
                :title="rail ? undefined : item.title"
                exact
                rounded="lg"
                active-color="success"
                class="nav-item"
              />
            </template>
          </v-tooltip>
        </template>
      </v-list>

      <!-- Collapse toggle -->
      <template #append>
        <v-divider class="sidebar-divider mb-2" />
        <v-list nav density="compact" class="nav-list pb-3">
          <v-list-item
            :prepend-icon="rail ? 'mdi-chevron-right' : 'mdi-chevron-left'"
            :title="rail ? undefined : 'Zwiń menu'"
            rounded="lg"
            class="nav-item nav-item--collapse"
            @click="rail = !rail"
          />
        </v-list>
      </template>
    </v-navigation-drawer>

    <!-- ── TOP BAR ───────────────────────────────────────── -->
    <v-app-bar :height="58" class="topbar" elevation="0">
      <v-app-bar-title>
        <span class="page-title">{{ pageTitle }}</span>
      </v-app-bar-title>

      <template #append>
        <div class="topbar-right">
          <!-- User info -->
          <div class="user-block">
            <v-avatar
              color="success"
              size="32"
              class="user-avatar text-caption font-weight-bold"
            >
              {{ authStore.initials }}
            </v-avatar>
            <div class="user-meta d-none d-sm-flex flex-column align-end">
              <span class="user-name">{{ authStore.user?.fullName }}</span>
              <span class="user-role">{{ authStore.user?.role }}</span>
            </div>
          </div>

          <div class="topbar-divider" />

          <v-btn
            icon
            variant="text"
            color="error"
            size="small"
            density="comfortable"
            title="Wyloguj"
            @click="handleLogout"
          >
            <v-icon size="18">mdi-logout-variant</v-icon>
          </v-btn>

          <div class="mr-2" />
        </div>
      </template>
    </v-app-bar>

    <!-- ── MAIN CONTENT ──────────────────────────────────── -->
    <v-main class="main-content">
      <div class="pa-6">
        <router-view v-slot="{ Component }">
          <transition name="fade-slide" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </div>
    </v-main>

    <!-- ── SNACKBAR (shared) ─────────────────────────────── -->
    <v-snackbar
      v-model="snack.show"
      :color="snack.color"
      location="bottom right"
      :timeout="3000"
      rounded="lg"
    >
      {{ snack.text }}
    </v-snackbar>

  </v-app>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotify } from '@/composables/useNotify'

const router    = useRouter()
const route     = useRoute()
const authStore = useAuthStore()
const { snack } = useNotify()

const drawer = ref(true)
const rail   = ref(false)

const navItems = [
  { title: 'Dashboard',   icon: 'mdi-view-dashboard-outline',  to: '/dashboard' },
  { title: 'Kontrahenci', icon: 'mdi-account-multiple-outline', to: '/customers' },
  { title: 'Uprawnienia', icon: 'mdi-shield-key-outline',       to: '/access'    },
  { title: 'Użytkownicy', icon: 'mdi-account-cog-outline',      to: '/admin'     },
]

const pageTitle = computed(() => (route.meta.title as string) ?? 'Dashboard')

function handleLogout() {
  authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
/* ── Sidebar ── */
.sidebar {
  background: #141d2b !important;
  border-right: 1px solid rgba(255, 255, 255, 0.06) !important;
}

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 18px 14px;
  background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
}

.brand--rail {
  justify-content: center;
  padding: 18px 0;
}

.brand-icon-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  background: rgba(74, 222, 128, 0.15);
  border-radius: 8px;
  flex-shrink: 0;
}

.brand-name {
  font-size: 0.95rem;
  font-weight: 700;
  letter-spacing: 1.5px;
  color: #fff;
  white-space: nowrap;
}

.sidebar-divider {
  border-color: rgba(255, 255, 255, 0.06) !important;
}

.nav-list { padding: 8px !important; }

.nav-item {
  color: rgba(255, 255, 255, 0.5) !important;
  margin-bottom: 2px !important;
  transition: background 0.15s, color 0.15s;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.05) !important;
  color: rgba(255, 255, 255, 0.85) !important;
}

/* Vuetify sets active color via active-color="success" */
:deep(.v-list-item--active) {
  background: rgba(74, 222, 128, 0.1) !important;
  border-left: 3px solid #4ade80 !important;
}
:deep(.v-list-item--active .v-icon) { color: #4ade80 !important; }

.nav-item--collapse {
  color: rgba(255, 255, 255, 0.3) !important;
}

/* ── Top Bar ── */
.topbar {
  background: #141d2b !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
}

.page-title {
  font-size: 1rem;
  font-weight: 600;
  color: #f1f5f9;
  letter-spacing: 0.01em;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-block {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-avatar { flex-shrink: 0; }

.user-meta { line-height: 1; }

.user-name {
  font-size: 0.8rem;
  font-weight: 600;
  color: #e2e8f0;
}

.user-role {
  font-size: 0.68rem;
  color: #64748b;
  margin-top: 2px;
}

.topbar-divider {
  width: 1px;
  height: 24px;
  background: rgba(255, 255, 255, 0.08);
}

/* ── Main ── */
.main-content { background: #0f1117 !important; }

/* ── Transitions ── */
.label-fade-enter-active,
.label-fade-leave-active { transition: opacity 0.15s; }
.label-fade-enter-from,
.label-fade-leave-to     { opacity: 0; }
</style>
