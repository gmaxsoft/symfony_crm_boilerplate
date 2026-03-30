<template>
  <v-app theme="venomDark" class="auth-bg">
    <v-main class="d-flex align-center justify-center">
      <router-view />
    </v-main>
  </v-app>
</template>

<style scoped>
.auth-bg {
  background: #0f1117 !important;
}
</style>
