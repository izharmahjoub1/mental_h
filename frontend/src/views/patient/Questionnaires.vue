<template>
  <div class="questionnaires-page">
    <h1>Mes Questionnaires</h1>
    <div v-if="loading" class="loading">Chargement...</div>
    <div v-else-if="assignedQuestionnaires.length === 0" class="no-questionnaires">
      Aucun questionnaire assigné
    </div>
    <div v-else class="questionnaires-list">
      <div
        v-for="assignment in assignedQuestionnaires"
        :key="assignment.id"
        class="questionnaire-item"
      >
        <div>
          <h3>{{ assignment.questionnaire?.title }}</h3>
          <p>{{ assignment.questionnaire?.description }}</p>
          <p v-if="assignment.due_date" class="due-date">
            À compléter avant le {{ formatDate(assignment.due_date) }}
          </p>
        </div>
        <button
          @click="openQuestionnaire(assignment)"
          class="btn-primary"
        >
          Compléter
        </button>
      </div>
    </div>

    <!-- Modal pour le questionnaire -->
    <div v-if="selectedAssignment" class="modal" @click.self="closeModal">
      <div class="modal-content">
        <QuestionnaireForm
          :questionnaire="selectedAssignment.questionnaire"
          :assignment-id="selectedAssignment.id"
          @success="handleSuccess"
          @cancel="closeModal"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuestionnaireStore } from '@/stores/questionnaireStore'
import QuestionnaireForm from '@/components/QuestionnaireForm.vue'

const questionnaireStore = useQuestionnaireStore()
const selectedAssignment = ref(null)

const { assignedQuestionnaires, loading } = questionnaireStore

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR')
}

const openQuestionnaire = (assignment) => {
  selectedAssignment.value = assignment
}

const closeModal = () => {
  selectedAssignment.value = null
}

const handleSuccess = () => {
  closeModal()
  questionnaireStore.fetchAssigned()
}

onMounted(() => {
  questionnaireStore.fetchAssigned()
})
</script>

<style scoped>
.questionnaires-page {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.questionnaires-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.questionnaire-item {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.due-date {
  color: #6b7280;
  font-size: 0.875rem;
  margin-top: 0.5rem;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  cursor: pointer;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.loading,
.no-questionnaires {
  text-align: center;
  padding: 2rem;
}
</style>

