<template>
  <v-app>
    <v-app-bar color="primary" elevation="2">
      <v-app-bar-title>VENOM CRM</v-app-bar-title>
    </v-app-bar>

    <v-main>
      <v-container>
        <v-row justify="center" class="mt-8">
          <v-col cols="12" md="6" class="text-center">
            <v-icon size="80" color="primary">mdi-snake</v-icon>
            <h1 class="text-h4 font-weight-bold mt-4">VENOM CRM</h1>
            <p class="text-subtitle-1 text-grey-darken-1 mt-2">
              System CRM gotowy do konfiguracji
            </p>
            <v-btn color="primary" size="large" class="mt-6" prepend-icon="mdi-rocket-launch">
              Rozpocznij
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>
