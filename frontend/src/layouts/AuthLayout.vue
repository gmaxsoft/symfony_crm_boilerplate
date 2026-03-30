<template>
  <v-app theme="venomDark" class="auth-bg">
    <v-main class="d-flex align-center justify-center">
      <router-view />
    </v-main>
  </v-app>
</template>

<style scoped>
.auth-bg {
  background: radial-gradient(ellipse at 30% 50%, rgba(74,222,128,0.06) 0%, #0f1117 60%) !important;
}
</style>
