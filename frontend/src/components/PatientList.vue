<template>
  <div class="patient-list">
    <div class="header">
      <h2>Liste des Patients</h2>
      <button @click="$router.push('/patients/new')" class="btn-primary">
        Nouveau Patient
      </button>
    </div>

    <div v-if="loading" class="loading">Chargement...</div>

    <div v-else class="table-container">
      <table>
        <thead>
          <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Alertes</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="patient in patients" :key="patient.id">
            <td>{{ patient.user?.name }}</td>
            <td>{{ patient.user?.email }}</td>
            <td>{{ patient.phone || '-' }}</td>
            <td>
              <span
                v-for="alert in patient.alerts?.slice(0, 2)"
                :key="alert.id"
                :class="['badge', `badge-${alert.level.toLowerCase()}`]"
              >
                {{ alert.level }}
              </span>
            </td>
            <td>
              <button
                @click="$router.push(`/patients/${patient.id}`)"
                class="btn-link"
              >
                Voir
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="pagination" class="pagination">
        <button
          @click="fetchPatients(pagination.current - 1)"
          :disabled="pagination.current === 1"
        >
          Précédent
        </button>
        <span>Page {{ pagination.current }} / {{ pagination.lastPage }}</span>
        <button
          @click="fetchPatients(pagination.current + 1)"
          :disabled="pagination.current === pagination.lastPage"
        >
          Suivant
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { usePatientStore } from '@/stores/patientStore'

const patientStore = usePatientStore()
const { patients, loading, pagination } = patientStore

const fetchPatients = (page = 1) => {
  patientStore.fetchPatients(page)
}

onMounted(() => {
  fetchPatients()
})
</script>

<style scoped>
.patient-list {
  padding: 2rem;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.table-container {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #e9ecef;
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  margin-right: 0.5rem;
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

.btn-primary {
  background-color: #3b82f6;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}

.btn-link {
  background: none;
  border: none;
  color: #3b82f6;
  cursor: pointer;
  text-decoration: underline;
}

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
}

.loading {
  text-align: center;
  padding: 2rem;
}
</style>

