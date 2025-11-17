<template>
  <div id="app">
    <router-view v-if="mounted" />
    <div v-else>Chargement...</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'

const mounted = ref(false)
const authStore = useAuthStore()

onMounted(() => {
  try {
    authStore.initAuth()
    mounted.value = true
  } catch (error) {
    console.error('Erreur lors de l\'initialisation:', error)
    mounted.value = true // Afficher quand même l'app
  }
})
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  background-color: #f5f5f5;
}

#app {
  min-height: 100vh;
}
</style>
