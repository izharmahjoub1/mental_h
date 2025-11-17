<template>
  <div class="messages-view">
    <h1>Messages</h1>
    <div class="messages-container">
      <div class="conversations-list">
        <h3>Conversations</h3>
        <div
          v-for="conversation in conversations"
          :key="conversation.id"
          @click="selectConversation(conversation)"
          class="conversation-item"
        >
          {{ conversation.user?.name }}
        </div>
      </div>
      <div class="thread-container">
        <MessagesThread
          v-if="selectedUserId"
          :recipient-id="selectedUserId"
          :messages="threadMessages"
          @message-sent="loadThread"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '@/services/apiClient'
import MessagesThread from '@/components/MessagesThread.vue'

const conversations = ref([])
const selectedUserId = ref(null)
const threadMessages = ref([])

const loadConversations = async () => {
  try {
    const response = await apiClient.get('/messages')
    // Logique pour extraire les conversations uniques
    conversations.value = response.data.data
  } catch (error) {
    console.error('Erreur lors du chargement des conversations', error)
  }
}

const selectConversation = async (conversation) => {
  selectedUserId.value = conversation.from_user_id || conversation.to_user_id
  await loadThread()
}

const loadThread = async () => {
  if (!selectedUserId.value) return

  try {
    const response = await apiClient.get(`/messages/thread/${selectedUserId.value}`)
    threadMessages.value = response.data
  } catch (error) {
    console.error('Erreur lors du chargement du thread', error)
  }
}

onMounted(() => {
  loadConversations()
})
</script>

<style scoped>
.messages-view {
  padding: 2rem;
}

.messages-container {
  display: grid;
  grid-template-columns: 300px 1fr;
  gap: 2rem;
  margin-top: 2rem;
}

.conversations-list {
  background: white;
  padding: 1rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.conversation-item {
  padding: 1rem;
  cursor: pointer;
  border-bottom: 1px solid #e9ecef;
}

.conversation-item:hover {
  background-color: #f3f4f6;
}

.thread-container {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>

