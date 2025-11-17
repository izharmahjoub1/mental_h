<template>
  <div class="messages-page">
    <h1>Messages</h1>
    <MessagesThread
      v-if="clinicianId"
      :recipient-id="clinicianId"
      :messages="messages"
      @message-sent="loadMessages"
    />
    <div v-else class="no-clinician">
      Aucun clinicien assigné
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '@/services/apiClient'
import { useAuthStore } from '@/stores/authStore'
import MessagesThread from '@/components/MessagesThread.vue'

const authStore = useAuthStore()
const clinicianId = ref(null)
const messages = ref([])

const loadMessages = async () => {
  if (!clinicianId.value) return

  try {
    const response = await apiClient.get(`/messages/thread/${clinicianId.value}`)
    messages.value = response.data
  } catch (error) {
    console.error('Erreur lors du chargement des messages', error)
  }
}

onMounted(async () => {
  // Trouver le clinicien (logique simplifiée - à adapter selon votre logique métier)
  try {
    const response = await apiClient.get('/messages')
    const allMessages = response.data.data
    if (allMessages.length > 0) {
      const firstMessage = allMessages[0]
      clinicianId.value =
        firstMessage.from_user_id === authStore.user.id
          ? firstMessage.to_user_id
          : firstMessage.from_user_id
    }
    await loadMessages()
  } catch (error) {
    console.error('Erreur', error)
  }
})
</script>

<style scoped>
.messages-page {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.no-clinician {
  text-align: center;
  padding: 2rem;
  background: white;
  border-radius: 8px;
}
</style>

