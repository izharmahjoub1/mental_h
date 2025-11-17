<template>
  <div class="alerts-view">
    <h1>Gestion des Alertes</h1>
    <AlertsTable />
  </div>
</template>

<script setup>
import AlertsTable from '@/components/AlertsTable.vue'
</script>

<style scoped>
.alerts-view {
  padding: 2rem;
}
</style>

