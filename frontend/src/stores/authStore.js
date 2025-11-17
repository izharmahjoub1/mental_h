import { defineStore } from 'pinia'
import apiClient from '@/services/apiClient'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    isAuthenticated: false,
  }),

  getters: {
    isClinician: (state) => state.user?.role === 'CLINICIAN',
    isPatient: (state) => state.user?.role === 'PATIENT',
  },

  actions: {
    async login(email, password) {
      try {
        const response = await apiClient.post('/auth/login', { email, password })
        this.user = response.data.user
        this.token = response.data.token
        this.isAuthenticated = true

        // Sauvegarder dans localStorage
        localStorage.setItem('auth_token', this.token)
        localStorage.setItem('user', JSON.stringify(this.user))

        return { success: true }
      } catch (error) {
        return {
          success: false,
          error: error.response?.data?.message || 'Erreur de connexion',
        }
      }
    },

    async logout() {
      try {
        await apiClient.post('/auth/logout')
      } catch (error) {
        console.error('Erreur lors de la déconnexion', error)
      } finally {
        this.user = null
        this.token = null
        this.isAuthenticated = false
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
      }
    },

    async fetchMe() {
      try {
        const response = await apiClient.get('/auth/me')
        this.user = response.data.user
        this.isAuthenticated = true
        return { success: true }
      } catch (error) {
        this.logout()
        return { success: false }
      }
    },

    initAuth() {
      try {
        const token = localStorage.getItem('auth_token')
        const userStr = localStorage.getItem('user')

        if (token && userStr) {
          try {
            this.token = token
            this.user = JSON.parse(userStr)
            this.isAuthenticated = true
          } catch (parseError) {
            // Données corrompues dans localStorage, nettoyer
            console.warn('Données corrompues dans localStorage, nettoyage...', parseError)
            localStorage.removeItem('auth_token')
            localStorage.removeItem('user')
            this.user = null
            this.token = null
            this.isAuthenticated = false
          }
        }
      } catch (error) {
        console.error('Erreur lors de l\'initialisation de l\'auth', error)
      }
    },
  },
})

