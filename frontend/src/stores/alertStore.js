import { defineStore } from 'pinia'
import apiClient from '@/services/apiClient'

export const useAlertStore = defineStore('alert', {
  state: () => ({
    alerts: [],
    stats: null,
    loading: false,
    pagination: null,
  }),

  getters: {
    redAlerts: (state) => state.alerts.filter((a) => a.level === 'RED'),
    orangeAlerts: (state) => state.alerts.filter((a) => a.level === 'ORANGE'),
    greenAlerts: (state) => state.alerts.filter((a) => a.level === 'GREEN'),
    unacknowledgedAlerts: (state) => state.alerts.filter((a) => !a.is_acknowledged),
  },

  actions: {
    async fetchAlerts(page = 1) {
      this.loading = true
      try {
        const response = await apiClient.get('/alerts', { params: { page } })
        this.alerts = response.data.data
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

    async fetchStats() {
      try {
        const response = await apiClient.get('/alerts/stats')
        this.stats = response.data
        return { success: true }
      } catch (error) {
        return {
          success: false,
          error: error.response?.data?.message || 'Erreur lors du chargement',
        }
      }
    },

    async acknowledgeAlert(alertId) {
      try {
        const response = await apiClient.post(`/alerts/${alertId}/acknowledge`)
        const index = this.alerts.findIndex((a) => a.id === alertId)
        if (index !== -1) {
          this.alerts[index] = response.data
        }
        return { success: true }
      } catch (error) {
        return {
          success: false,
          error: error.response?.data?.message || 'Erreur lors de l\'acquittement',
        }
      }
    },

    async fetchPatientAlerts(patientId) {
      try {
        const response = await apiClient.get(`/patients/${patientId}/alerts`)
        return { success: true, data: response.data }
      } catch (error) {
        return {
          success: false,
          error: error.response?.data?.message || 'Erreur lors du chargement',
        }
      }
    },
  },
})

