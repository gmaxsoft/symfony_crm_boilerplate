import { createApp } from 'vue'
import { createVuetify } from 'vuetify'
import { createPinia } from 'pinia'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'
import router from './router'
import App from './App.vue'
import './style.css'

const vuetify = createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: 'venomDark',
    themes: {
      venomDark: {
        dark: true,
        colors: {
          background:        '#0f1117',
          surface:           '#161c2d',
          'surface-variant': '#1a2436',
          primary:           '#4ade80',
          secondary:         '#a78bfa',
          error:             '#f87171',
          info:              '#60a5fa',
          success:           '#4ade80',
          warning:           '#fbbf24',
        },
      },
    },
  },
  defaults: {
    VBtn:       { rounded: '0', elevation: 0 },
    VCard:      { rounded: '0', elevation: 0 },
    VTextField: { rounded: '0', variant: 'outlined', density: 'comfortable', bgColor: '#1a2236' },
    VSelect:    { rounded: '0', variant: 'outlined', density: 'comfortable', bgColor: '#1a2236' },
    VTextarea:  { rounded: '0', variant: 'outlined', density: 'comfortable', bgColor: '#1a2236' },
    VChip:      { rounded: '0' },
    VAlert:     { rounded: '0' },
    VSnackbar:  { rounded: '0' },
    VListItem:  { rounded: '0' },
    VDialog:    { rounded: '0' },
  },
})

const app = createApp(App)
app.use(createPinia()).use(vuetify).use(router).mount('#app')
