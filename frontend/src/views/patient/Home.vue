<template>
  <div class="patient-home">
    <header>
      <h1>Mon Tableau de Bord</h1>
      <button @click="handleLogout" class="btn-logout">Déconnexion</button>
    </header>

    <div class="alerts-summary">
      <h2>Mes Alertes</h2>
      <div v-if="alerts.length === 0" class="no-alerts">
        Aucune alerte pour le moment
      </div>
      <div v-else>
        <div
          v-for="alert in alerts.slice(0, 5)"
          :key="alert.id"
          :class="['alert-card', `alert-${alert.level.toLowerCase()}`]"
        >
          <strong>{{ alert.level }}</strong>
          <p>{{ alert.reason }}</p>
          <span class="alert-date">{{ formatDate(alert.created_at) }}</span>
        </div>
      </div>
    </div>

    <div class="questionnaires-summary">
      <h2>Questionnaires du Jour</h2>
      <div v-if="assignedQuestionnaires.length === 0" class="no-questionnaires">
        Aucun questionnaire assigné
      </div>
      <div v-else>
        <div
          v-for="assignment in assignedQuestionnaires"
          :key="assignment.id"
          class="questionnaire-card"
        >
          <strong>{{ assignment.questionnaire?.title }}</strong>
          <button
            @click="$router.push(`/patient/questionnaires/${assignment.id}`)"
            class="btn-primary"
          >
            Compléter
          </button>
        </div>
      </div>
    </div>

    <div class="quick-links">
      <button @click="$router.push('/patient/questionnaires')" class="btn-link">
        Tous les questionnaires
      </button>
      <button @click="$router.push('/patient/messages')" class="btn-link">
        Messages
      </button>
      <button @click="$router.push('/patient/progress')" class="btn-link">
        Mon évolution
      </button>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useAlertStore } from '@/stores/alertStore'
import { useQuestionnaireStore } from '@/stores/questionnaireStore'

const router = useRouter()
const authStore = useAuthStore()
const alertStore = useAlertStore()
const questionnaireStore = useQuestionnaireStore()

const alerts = ref([])
const assignedQuestionnaires = ref([])

const handleLogout = async () => {
  await authStore.logout()
  router.push('/patient/login')
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}

onMounted(async () => {
  await alertStore.fetchAlerts()
  alerts.value = alertStore.alerts

  await questionnaireStore.fetchAssigned()
  assignedQuestionnaires.value = questionnaireStore.assignedQuestionnaires
})
</script>

<style scoped>
.patient-home {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.alerts-summary,
.questionnaires-summary {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.alert-card {
  padding: 1rem;
  border-radius: 4px;
  margin-bottom: 1rem;
  border-left: 4px solid;
}

.alert-red {
  background-color: #fee;
  border-color: #dc2626;
}

.alert-orange {
  background-color: #ffe4cc;
  border-color: #d97706;
}

.alert-green {
  background-color: #d1fae5;
  border-color: #059669;
}

.alert-date {
  font-size: 0.875rem;
  color: #6b7280;
}

.questionnaire-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #e9ecef;
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
  border: 1px solid #3b82f6;
  color: #3b82f6;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 1rem;
}

.btn-logout {
  background-color: #6b7280;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}

.quick-links {
  display: flex;
  gap: 1rem;
}

.no-alerts,
.no-questionnaires {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
}
</style>

