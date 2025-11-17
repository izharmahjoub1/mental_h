import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

try {
  const app = createApp(App)
  const pinia = createPinia()

  app.use(pinia)
  app.use(router)

  // Gestion des erreurs globales
  app.config.errorHandler = (err, instance, info) => {
    console.error('Erreur Vue:', err, info)
  }

  app.mount('#app')
  console.log('Application Vue montée avec succès')
} catch (error) {
  console.error('Erreur fatale lors du montage de l\'application:', error)
  document.body.innerHTML = `
    <div style="padding: 20px; font-family: sans-serif;">
      <h1>Erreur de chargement</h1>
      <p>Une erreur s'est produite lors du chargement de l'application.</p>
      <pre>${error.message}</pre>
      <p>Veuillez ouvrir la console (F12) pour plus de détails.</p>
    </div>
  `
}

