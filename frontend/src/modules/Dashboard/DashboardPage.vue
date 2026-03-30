<template>
  <div>
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h2 class="page-heading">Dashboard</h2>
        <p class="page-sub">
          Witaj z powrotem,
          <span class="text-success">{{ authStore.user?.firstName }}</span>! —
          {{ dateStr }}
        </p>
      </div>
      <v-chip color="success" variant="tonal" prepend-icon="mdi-circle" size="small">
        System aktywny
      </v-chip>
    </div>

    <!-- Stat Cards -->
    <v-row class="mb-6">
      <v-col v-for="s in statCards" :key="s.label" cols="12" sm="6" lg="3">
        <v-card class="stat-card" color="#161c2d" border>
          <v-card-text>
            <div class="d-flex align-center justify-space-between mb-3">
              <div class="stat-icon" :style="{ background: s.iconBg }">
                <v-icon :color="s.iconColor" size="20">{{ s.icon }}</v-icon>
              </div>
              <span :class="['trend', s.trendUp ? 'trend--up' : 'trend--neutral']">
                {{ s.trendLabel }}
              </span>
            </div>
            <div class="stat-value">
              <template v-if="loading">
                <v-skeleton-loader type="text" width="60" />
              </template>
              <template v-else>{{ s.value }}</template>
            </div>
            <div class="stat-label">{{ s.label }}</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Charts Row -->
    <v-row>
      <!-- Main Sparkline -->
      <v-col cols="12" lg="8">
        <v-card color="#161c2d" border class="chart-card">
          <v-card-text>
            <div class="d-flex align-center justify-space-between mb-1">
              <div>
                <div class="chart-title">Aktywność systemu</div>
                <div class="chart-sub">Nowi kontrahenci — ostatnie 12 miesięcy</div>
              </div>
              <v-chip color="success" variant="tonal" size="x-small">Live</v-chip>
            </div>

            <v-sparkline
              :value="sparkData"
              color="success"
              :smooth="12"
              :fill="true"
              auto-draw
              height="140"
              line-width="2"
              padding="16"
            />

            <!-- Month labels -->
            <div class="month-labels">
              <span v-for="m in months" :key="m">{{ m }}</span>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Quick Stats panel -->
      <v-col cols="12" lg="4">
        <v-card color="#161c2d" border class="chart-card" height="100%">
          <v-card-text>
            <div class="chart-title mb-4">Skrócone statystyki</div>

            <div v-if="loading" class="d-flex justify-center py-8">
              <v-progress-circular indeterminate color="success" size="36" />
            </div>

            <template v-else>
              <div v-for="item in quickStats" :key="item.label" class="quick-stat">
                <div class="d-flex align-center gap-3">
                  <div class="quick-icon" :style="{ background: item.bg }">
                    <v-icon :color="item.color" size="16">{{ item.icon }}</v-icon>
                  </div>
                  <div class="flex-1">
                    <div class="quick-label">{{ item.label }}</div>
                    <v-progress-linear
                      :model-value="item.pct"
                      :color="item.color"
                      height="4"
                      bg-color="rgba(255,255,255,0.06)"
                      class="mt-1"
                    />
                  </div>
                  <div class="quick-val">{{ item.val }}</div>
                </div>
              </div>
            </template>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Bottom Row -->
    <v-row class="mt-0">
      <v-col cols="12" sm="4" v-for="mini in miniCards" :key="mini.title">
        <v-card color="#161c2d" border>
          <v-card-text>
            <div class="d-flex align-center justify-space-between mb-3">
              <span class="chart-sub">{{ mini.title }}</span>
              <v-icon :color="mini.color" size="18">{{ mini.icon }}</v-icon>
            </div>
            <div class="stat-value text-h5">{{ loading ? '—' : mini.value }}</div>
            <v-sparkline
              :value="mini.data"
              :color="mini.color"
              :smooth="8"
              auto-draw
              height="48"
              line-width="1.5"
              padding="4"
            />
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { dashboardApi } from '@/api/dashboard'

const authStore = useAuthStore()
const loading   = ref(true)
const customers = ref(0)
const users     = ref(0)

const dateStr = new Intl.DateTimeFormat('pl-PL', {
  weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
}).format(new Date())

const months    = ['Sty','Lut','Mar','Kwi','Maj','Cze','Lip','Sie','Wrz','Paź','Lis','Gru']
const sparkData = ref([3,7,5,12,9,15,11,18,14,22,17,25])

const statCards = computed(() => [
  {
    label: 'Kontrahenci',
    value: customers.value,
    icon: 'mdi-account-multiple-outline',
    iconBg: 'rgba(74,222,128,0.12)',
    iconColor: 'success',
    trendLabel: '+12%',
    trendUp: true,
  },
  {
    label: 'Aktywni użytkownicy',
    value: users.value,
    icon: 'mdi-account-check-outline',
    iconBg: 'rgba(167,139,250,0.12)',
    iconColor: 'secondary',
    trendLabel: '+5%',
    trendUp: true,
  },
  {
    label: 'Moduły systemu',
    value: 5,
    icon: 'mdi-view-grid-outline',
    iconBg: 'rgba(96,165,250,0.12)',
    iconColor: 'info',
    trendLabel: 'Stałe',
    trendUp: false,
  },
  {
    label: 'Status systemu',
    value: '100%',
    icon: 'mdi-shield-check-outline',
    iconBg: 'rgba(251,191,36,0.12)',
    iconColor: 'warning',
    trendLabel: 'Online',
    trendUp: true,
  },
])

const quickStats = computed(() => [
  { label: 'Aktywni kontrahenci', val: customers.value, pct: 80, icon: 'mdi-account-multiple-outline', color: 'success', bg: 'rgba(74,222,128,0.12)' },
  { label: 'Użytkownicy systemu',  val: users.value,     pct: 60, icon: 'mdi-account-cog-outline',      color: 'secondary', bg: 'rgba(167,139,250,0.12)' },
  { label: 'Integralność danych',  val: '100%',          pct: 100, icon: 'mdi-database-check-outline',  color: 'info',      bg: 'rgba(96,165,250,0.12)'  },
])

const miniCards = computed(() => [
  { title: 'Kontrahenci (trend)',   value: customers.value, icon: 'mdi-trending-up',   color: 'success',   data: [2,4,3,8,6,11,9,14] },
  { title: 'Użytkownicy (trend)',   value: users.value,     icon: 'mdi-account-group', color: 'secondary', data: [1,2,2,3,3,4,4,5]   },
  { title: 'Sesje API (ostatnie)',  value: '—',             icon: 'mdi-api',           color: 'info',      data: [5,8,6,12,10,15,13,18] },
])

onMounted(async () => {
  try {
    const res = await dashboardApi.stats()
    customers.value = res.data.data.stats.customers
    users.value     = res.data.data.stats.users
    // fill sparkline with dummy data scaled by customers
    sparkData.value = months.map((_, i) => Math.max(1, Math.round((customers.value || 5) * (0.3 + i * 0.06))))
  } catch { /* use defaults */ }
  finally { loading.value = false }
})
</script>

<style scoped>
.page-heading {
  font-size: 1.4rem;
  font-weight: 700;
  color: #f1f5f9;
}
.page-sub {
  font-size: 0.8rem;
  color: #64748b;
  margin-top: 2px;
}

/* Stat card */
.stat-card { border: 1px solid rgba(255,255,255,0.07) !important; }
.stat-icon {
  width: 38px; height: 38px;
  display: flex; align-items: center; justify-content: center;
}
.stat-value {
  font-size: 1.7rem;
  font-weight: 700;
  color: #f1f5f9;
  line-height: 1;
  margin-bottom: 4px;
}
.stat-label {
  font-size: 0.75rem;
  color: #64748b;
}
.trend {
  font-size: 0.7rem;
  font-weight: 600;
  padding: 2px 7px;
}
.trend--up      { background: rgba(74,222,128,0.12); color: #4ade80; }
.trend--neutral { background: rgba(148,163,184,0.12); color: #94a3b8; }

/* Chart */
.chart-card { border: 1px solid rgba(255,255,255,0.07) !important; }
.chart-title { font-size: 0.9rem; font-weight: 600; color: #e2e8f0; }
.chart-sub   { font-size: 0.72rem; color: #64748b; margin-top: 2px; }

.month-labels {
  display: flex;
  justify-content: space-between;
  padding: 0 16px;
  font-size: 0.65rem;
  color: #475569;
  margin-top: -4px;
}

/* Quick stats */
.quick-stat { margin-bottom: 16px; }
.quick-icon {
  width: 30px; height: 30px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.quick-label { font-size: 0.75rem; color: #94a3b8; }
.quick-val   { font-size: 0.85rem; font-weight: 700; color: #f1f5f9; min-width: 40px; text-align: right; }
</style>
