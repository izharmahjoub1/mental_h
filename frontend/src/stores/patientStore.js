import { defineStore } from 'pinia'
import apiClient from '@/services/apiClient'

export const usePatientStore = defineStore('patient', {
  state: () => ({
    patients: [],
    currentPatient: null,
    loading: false,
    pagination: null,
  }),

  actions: {
    async fetchPatients(page = 1) {
      this.loading = true
      try {
        const response = await apiClient.get('/patients', { params: { page } })
        this.patients = response.data.data
        this.pagination = {
          current: response.data.current_page,
          total: response.data.total,
          perPage: response.data.per_page,
          lastPage: response.data.last_page,
        }
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

    async fetchPatient(id) {
      this.loading = true
      try {
        const response = await apiClient.get(`/patients/${id}`)
        this.currentPatient = response.data
        return { success: true, data: response.data }
      } catch (error) {
        return {
          success: false,
          error: error.response?.data?.message || 'Erreur lors du chargement',
        }
      } finally {
        this.loading = false
      }
    },

    async createPatient(patientData) {
      try {
        const response = await apiClient.post('/patients', patientData)
        this.patients.push(response.data)
        return { success: true, data: response.data }
      } catch (error) {
        return {
          success: false,
          error: error.response?.data?.message || 'Erreur lors de la création',
        }
      }
    },

    async updatePatient(id, patientData) {
      try {
        const response = await apiClient.put(`/patients/${id}`, patientData)
        const index = this.patients.findIndex((p) => p.id === id)
        if (index !== -1) {
          this.patients[index] = response.data
        }
        if (this.currentPatient?.id === id) {
          this.currentPatient = response.data
        }
        return { success: true, data: response.data }
      } catch (error) {
        return {
          success: false,
          error: error.response?.data?.message || 'Erreur lors de la mise à jour',
        }
      }
    },
  },
})

