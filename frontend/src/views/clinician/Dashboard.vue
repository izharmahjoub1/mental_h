<template>
  <div class="dashboard">
    <header class="dashboard-header">
      <h1>Tableau de bord</h1>
      <button @click="handleLogout" class="btn-logout">Déconnexion</button>
    </header>

    <div class="stats-grid">
      <div class="stat-card">
        <h3>Patients</h3>
        <p class="stat-value">{{ stats.patients || 0 }}</p>
      </div>
      <div class="stat-card stat-red">
        <h3>Alertes Rouges</h3>
        <p class="stat-value">{{ alertStats?.by_level?.RED || 0 }}</p>
      </div>
      <div class="stat-card stat-orange">
        <h3>Alertes Orange</h3>
        <p class="stat-value">{{ alertStats?.by_level?.ORANGE || 0 }}</p>
      </div>
      <div class="stat-card">
        <h3>Alertes Non Acquittées</h3>
        <p class="stat-value">{{ alertStats?.unacknowledged || 0 }}</p>
      </div>
    </div>

    <div class="dashboard-content">
      <div class="section">
        <h2>Alertes Récentes</h2>
        <AlertsTable />
      </div>

      <div class="section">
        <h2>Actions Rapides</h2>
        <div class="quick-actions">
          <button @click="$router.push('/patients')" class="btn-action">
            Voir tous les patients
          </button>
          <button @click="$router.push('/alerts')" class="btn-action">
            Gérer les alertes
          </button>
          <button @click="$router.push('/messages')" class="btn-action">
            Messages
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useAlertStore } from '@/stores/alertStore'
import AlertsTable from '@/components/AlertsTable.vue'

const router = useRouter()
const authStore = useAuthStore()
const alertStore = useAlertStore()

const stats = ref({ patients: 0 })
const alertStats = ref(null)

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

onMounted(async () => {
  await alertStore.fetchStats()
  alertStats.value = alertStore.stats
})
</script>

<style scoped>
.dashboard {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stat-card.stat-red {
  border-left: 4px solid #dc2626;
}

.stat-card.stat-orange {
  border-left: 4px solid #d97706;
}

.stat-value {
  font-size: 2rem;
  font-weight: bold;
  margin-top: 0.5rem;
}

.dashboard-content {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
}

.section {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.quick-actions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.btn-action {
  background-color: #3b82f6;
  color: white;
  border: none;
  padding: 1rem;
  border-radius: 4px;
  cursor: pointer;
  text-align: left;
}

.btn-logout {
  background-color: #6b7280;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}
</style>

