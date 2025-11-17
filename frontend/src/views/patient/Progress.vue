<template>
  <div class="progress-page">
    <h1>Mon Évolution</h1>
    <div class="charts-container">
      <div class="chart-section">
        <h3>Réponses aux Questionnaires</h3>
        <canvas ref="questionnaireChart"></canvas>
      </div>
      <div class="chart-section">
        <h3>Données de Capteurs</h3>
        <canvas ref="sensorChart"></canvas>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '@/services/apiClient'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const questionnaireChart = ref(null)
const sensorChart = ref(null)
let questionnaireChartInstance = null
let sensorChartInstance = null

const loadData = async () => {
  try {
    // Charger les réponses de questionnaires
    const userStr = localStorage.getItem('user')
    if (userStr) {
      try {
        const user = JSON.parse(userStr)
        const patient = user?.patient
        if (patient) {
          // Logique pour charger et afficher les données
          renderCharts()
        }
      } catch (parseError) {
        console.warn('Erreur de parsing localStorage dans Progress', parseError)
      }
    }
  } catch (error) {
    console.error('Erreur lors du chargement des données', error)
  }
}

const renderCharts = () => {
  // Exemple de graphique simplifié
  if (questionnaireChart.value) {
    questionnaireChartInstance = new Chart(questionnaireChart.value, {
      type: 'line',
      data: {
        labels: ['Semaine 1', 'Semaine 2', 'Semaine 3', 'Semaine 4'],
        datasets: [
          {
            label: 'Score PHQ-9',
            data: [10, 12, 8, 6],
            borderColor: '#3b82f6',
            tension: 0.1,
          },
        ],
      },
      options: {
        responsive: true,
      },
    })
  }

  if (sensorChart.value) {
    sensorChartInstance = new Chart(sensorChart.value, {
      type: 'line',
      data: {
        labels: ['Jour 1', 'Jour 2', 'Jour 3', 'Jour 4', 'Jour 5'],
        datasets: [
          {
            label: 'Fréquence cardiaque',
            data: [72, 75, 70, 73, 71],
            borderColor: '#10b981',
            tension: 0.1,
          },
        ],
      },
      options: {
        responsive: true,
      },
    })
  }
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.progress-page {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.charts-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-top: 2rem;
}

.chart-section {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.chart-section canvas {
  max-height: 300px;
}
</style>

