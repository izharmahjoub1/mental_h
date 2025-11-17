import { defineStore } from 'pinia'
import apiClient from '@/services/apiClient'

export const useQuestionnaireStore = defineStore('questionnaire', {
  state: () => ({
    questionnaires: [],
    assignedQuestionnaires: [],
    loading: false,
  }),

  actions: {
    async fetchQuestionnaires() {
      this.loading = true
      try {
        const response = await apiClient.get('/questionnaires')
        this.questionnaires = response.data
        return { success: true }
      } catch (error) {
        return {
          success: false,
          error: error.response?.data?.message || 'Erreur lors du chargement',
        }
      } finally {
        this.loading = false
      }
    },

    async fetchAssigned() {
      this.loading = true
      try {
        const response = await apiClient.get('/questionnaires/assigned/me')
        this.assignedQuestionnaires = response.data
        return { success: true }
      } catch (error) {
        return {
          success: false,
          error: error.response?.data?.message || 'Erreur lors du chargement',
        }
      } finally {
        this.loading = false
      }
    },

    async assignQuestionnaire(questionnaireId, patientId, dueDate = null) {
      try {
        const response = await apiClient.post(`/questionnaires/${questionnaireId}/assign`, {
          patient_id: patientId,
          due_date: dueDate,
        })
        return { success: true, data: response.data }
      } catch (error) {
        return {
          success: false,
          error: error.response?.data?.message || 'Erreur lors de l\'assignation',
        }
      }
    },

    async submitResponse(questionnaireId, answers, assignmentId = null) {
      try {
        const response = await apiClient.post(`/questionnaires/${questionnaireId}/responses`, {
          answers,
          assignment_id: assignmentId,
        })
        return { success: true, data: response.data }
      } catch (error) {
        return {
          success: false,
          error: error.response?.data?.message || 'Erreur lors de la soumission',
        }
      }
    },
  },
})

