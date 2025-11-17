<template>
  <div class="patient-details">
    <div v-if="loading" class="loading">Chargement...</div>

    <div v-else-if="patient">
      <div class="header">
        <h2>{{ patient.user?.name }}</h2>
        <button @click="$router.push('/patients')" class="btn-secondary">
          Retour
        </button>
      </div>

      <div class="patient-info">
        <div class="info-section">
          <h3>Informations</h3>
          <p><strong>Email:</strong> {{ patient.user?.email }}</p>
          <p><strong>Téléphone:</strong> {{ patient.phone || '-' }}</p>
          <p><strong>Date de naissance:</strong> {{ formatDate(patient.date_of_birth) }}</p>
        </div>

        <div class="info-section">
          <h3>Alertes Récentes</h3>
          <div v-for="alert in patient.alerts?.slice(0, 5)" :key="alert.id" class="alert-item">
            <span :class="['badge', `badge-${alert.level.toLowerCase()}`]">
              {{ alert.level }}
            </span>
            <span>{{ alert.reason }}</span>
            <span class="alert-date">{{ formatDate(alert.created_at) }}</span>
          </div>
        </div>
      </div>

      <div class="charts-section">
        <h3>Données de Capteurs</h3>
        <div class="chart-container">
          <canvas ref="chartCanvas"></canvas>
        </div>
      </div>

      <div class="questionnaires-section">
        <h3>Questionnaires Assignés</h3>
        <div
          v-for="assignment in patient.questionnaire_assignments"
          :key="assignment.id"
          class="assignment-item"
        >
          <strong>{{ assignment.questionnaire?.title }}</strong>
          <span :class="['status', `status-${assignment.status.toLowerCase()}`]">
            {{ assignment.status }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { usePatientStore } from '@/stores/patientStore'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const route = useRoute()
const patientStore = usePatientStore()
const chartCanvas = ref(null)
let chartInstance = null

const { currentPatient: patient, loading } = patientStore

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('fr-FR')
}

const loadPatient = async () => {
  await patientStore.fetchPatient(route.params.id)
}

const renderChart = () => {
  if (!chartCanvas.value || !patient.value?.sensor_readings) return

  const readings = patient.value.sensor_readings
  const bloodPressure = readings.filter((r) => r.sensor_type === 'blood_pressure')

  if (bloodPressure.length === 0) return

  const labels = bloodPressure.map((r) => formatDate(r.recorded_at))
  const values = bloodPressure.map((r) => {
    const parts = r.value.split('/')
    return parseFloat(parts[0]) // Systolic
  })

  if (chartInstance) {
    chartInstance.destroy()
  }

  chartInstance = new Chart(chartCanvas.value, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Tension Systolique',
          data: values,
          borderColor: '#3b82f6',
          tension: 0.1,
        },
      ],
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: false,
        },
      },
    },
  })
}

watch(
  () => patient.value,
  () => {
    if (patient.value) {
      renderChart()
    }
  },
  { deep: true }
)

onMounted(() => {
  loadPatient()
})
</script>

<style scoped>
.patient-details {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.patient-info {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
}

.info-section {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.alert-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.5rem 0;
  border-bottom: 1px solid #e9ecef;
}

.alert-date {
  margin-left: auto;
  font-size: 0.875rem;
  color: #6b7280;
}

.charts-section,
.questionnaires-section {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.chart-container {
  height: 300px;
  margin-top: 1rem;
}

.assignment-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  border-bottom: 1px solid #e9ecef;
}

.status {
  padding: 0.25rem 0.75rem;
  border-radius: 4px;
  font-size: 0.875rem;
}

.status-pending {
  background-color: #fef3c7;
  color: #d97706;
}

.status-completed {
  background-color: #d1fae5;
  color: #059669;
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

.btn-secondary {
  background-color: #6b7280;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}

.loading {
  text-align: center;
  padding: 2rem;
}
</style>

