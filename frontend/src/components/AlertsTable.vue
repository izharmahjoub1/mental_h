<template>
  <div class="alerts-table">
    <div class="header">
      <h3>Alertes</h3>
      <div class="filters">
        <select v-model="filterLevel" @change="applyFilters">
          <option value="">Tous les niveaux</option>
          <option value="RED">Rouge</option>
          <option value="ORANGE">Orange</option>
          <option value="GREEN">Vert</option>
        </select>
        <select v-model="filterAcknowledged" @change="applyFilters">
          <option value="">Tous</option>
          <option value="false">Non acquittées</option>
          <option value="true">Acquittées</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="loading">Chargement...</div>

    <table v-else>
      <thead>
        <tr>
          <th>Patient</th>
          <th>Niveau</th>
          <th>Type</th>
          <th>Raison</th>
          <th>Date</th>
          <th>Statut</th>
          <th v-if="isClinician">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="alert in filteredAlerts"
          :key="alert.id"
          :class="[`alert-${alert.level.toLowerCase()}`, { unacknowledged: !alert.is_acknowledged }]"
        >
          <td>{{ alert.patient?.user?.name }}</td>
          <td>
            <span :class="['badge', `badge-${alert.level.toLowerCase()}`]">
              {{ alert.level }}
            </span>
          </td>
          <td>{{ alert.type }}</td>
          <td>{{ alert.reason }}</td>
          <td>{{ formatDate(alert.created_at) }}</td>
          <td>
            <span v-if="alert.is_acknowledged" class="badge badge-success">
              Acquittée
            </span>
            <span v-else class="badge badge-warning">En attente</span>
          </td>
          <td v-if="isClinician && !alert.is_acknowledged">
            <button
              @click="acknowledgeAlert(alert.id)"
              class="btn-small"
            >
              Acquitter
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAlertStore } from '@/stores/alertStore'
import { useAuthStore } from '@/stores/authStore'

const alertStore = useAlertStore()
const authStore = useAuthStore()

const filterLevel = ref('')
const filterAcknowledged = ref('false')

const { alerts, loading } = alertStore
const isClinician = computed(() => authStore.isClinician)

const filteredAlerts = computed(() => {
  let filtered = [...alerts]

  if (filterLevel.value) {
    filtered = filtered.filter((a) => a.level === filterLevel.value)
  }

  if (filterAcknowledged.value !== '') {
    const isAck = filterAcknowledged.value === 'true'
    filtered = filtered.filter((a) => a.is_acknowledged === isAck)
  }

  return filtered
})

const applyFilters = () => {
  // Les filtres sont appliqués via computed
}

const acknowledgeAlert = async (alertId) => {
  await alertStore.acknowledgeAlert(alertId)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(() => {
  alertStore.fetchAlerts()
})
</script>

<style scoped>
.alerts-table {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.filters {
  display: flex;
  gap: 1rem;
}

select {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead {
  background-color: #f8f9fa;
}

th,
td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #e9ecef;
}

.alert-red {
  background-color: #fee;
}

.alert-orange {
  background-color: #ffe4cc;
}

.alert-green {
  background-color: #d1fae5;
}

.unacknowledged {
  font-weight: 600;
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
}

.badge-red {
  background-color: #fee;
  color: #c00;
}

.badge-orange {
  background-color: #ffe4cc;
  color: #d97706;
}

.badge-green {
  background-color: #d1fae5;
  color: #059669;
}

.badge-success {
  background-color: #d1fae5;
  color: #059669;
}

.badge-warning {
  background-color: #fef3c7;
  color: #d97706;
}

.btn-small {
  background-color: #3b82f6;
  color: white;
  border: none;
  padding: 0.25rem 0.75rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.875rem;
}

.loading {
  text-align: center;
  padding: 2rem;
}
</style>

