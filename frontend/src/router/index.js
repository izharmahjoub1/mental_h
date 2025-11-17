import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

// Clinicien routes
import ClinicianLogin from '@/views/clinician/Login.vue'
import ClinicianDashboard from '@/views/clinician/Dashboard.vue'
import PatientList from '@/views/clinician/PatientList.vue'
import PatientDetails from '@/views/clinician/PatientDetails.vue'
import AlertsView from '@/views/clinician/AlertsView.vue'
import MessagesView from '@/views/clinician/MessagesView.vue'

// Patient routes
import PatientLogin from '@/views/patient/Login.vue'
import PatientHome from '@/views/patient/Home.vue'
import PatientQuestionnaires from '@/views/patient/Questionnaires.vue'
import PatientMessages from '@/views/patient/Messages.vue'
import PatientProgress from '@/views/patient/Progress.vue'

const routes = [
  // Clinicien
  {
    path: '/login',
    name: 'clinician-login',
    component: ClinicianLogin,
    meta: { requiresAuth: false, role: 'CLINICIAN' },
  },
  {
    path: '/dashboard',
    name: 'clinician-dashboard',
    component: ClinicianDashboard,
    meta: { requiresAuth: true, role: 'CLINICIAN' },
  },
  {
    path: '/patients',
    name: 'patient-list',
    component: PatientList,
    meta: { requiresAuth: true, role: 'CLINICIAN' },
  },
  {
    path: '/patients/:id',
    name: 'patient-details',
    component: PatientDetails,
    meta: { requiresAuth: true, role: 'CLINICIAN' },
  },
  {
    path: '/alerts',
    name: 'alerts',
    component: AlertsView,
    meta: { requiresAuth: true, role: 'CLINICIAN' },
  },
  {
    path: '/messages',
    name: 'messages',
    component: MessagesView,
    meta: { requiresAuth: true, role: 'CLINICIAN' },
  },

  // Patient
  {
    path: '/patient/login',
    name: 'patient-login',
    component: PatientLogin,
    meta: { requiresAuth: false, role: 'PATIENT' },
  },
  {
    path: '/patient/home',
    name: 'patient-home',
    component: PatientHome,
    meta: { requiresAuth: true, role: 'PATIENT' },
  },
  {
    path: '/patient/questionnaires',
    name: 'patient-questionnaires',
    component: PatientQuestionnaires,
    meta: { requiresAuth: true, role: 'PATIENT' },
  },
  {
    path: '/patient/messages',
    name: 'patient-messages',
    component: PatientMessages,
    meta: { requiresAuth: true, role: 'PATIENT' },
  },
  {
    path: '/patient/progress',
    name: 'patient-progress',
    component: PatientProgress,
    meta: { requiresAuth: true, role: 'PATIENT' },
  },

  // Redirects
  {
    path: '/',
    redirect: '/login',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guards
router.beforeEach((to, from, next) => {
  try {
    // Import dynamique pour éviter les problèmes d'initialisation
    const authStore = useAuthStore()

    // Initialiser l'auth depuis localStorage
    authStore.initAuth()

    // Vérifier l'authentification
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
      const loginRoute = to.meta.role === 'PATIENT' ? '/patient/login' : '/login'
      return next(loginRoute)
    }

    // Vérifier le rôle seulement si l'utilisateur est authentifié
    if (to.meta.role && authStore.isAuthenticated) {
      const hasRole =
        (to.meta.role === 'CLINICIAN' && authStore.isClinician) ||
        (to.meta.role === 'PATIENT' && authStore.isPatient)

      if (!hasRole) {
        // Rediriger vers la page d'accueil appropriée
        const homeRoute = authStore.isClinician ? '/dashboard' : '/patient/home'
        return next(homeRoute)
      }
    }

    next()
  } catch (error) {
    console.error('Erreur dans le router guard:', error)
    // En cas d'erreur, rediriger vers la page de login
    next('/login')
  }
})

export default router

