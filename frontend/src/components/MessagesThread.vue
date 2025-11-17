<template>
  <div class="messages-thread">
    <div class="messages-container" ref="messagesContainer">
      <div
        v-for="message in messages"
        :key="message.id"
        :class="['message', { 'message-sent': message.from_user_id === currentUserId }]"
      >
        <div class="message-header">
          <strong>{{ message.from_user?.name }}</strong>
          <span class="message-time">{{ formatDate(message.created_at) }}</span>
        </div>
        <div class="message-content">{{ message.content }}</div>
      </div>
    </div>

    <form @submit.prevent="sendMessage" class="message-input">
      <textarea
        v-model="newMessage"
        placeholder="Tapez votre message..."
        rows="3"
        required
      ></textarea>
      <button type="submit" class="btn-primary" :disabled="sending">
        {{ sending ? 'Envoi...' : 'Envoyer' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'
import apiClient from '@/services/apiClient'
import { useAuthStore } from '@/stores/authStore'

const props = defineProps({
  messages: {
    type: Array,
    default: () => [],
  },
  recipientId: {
    type: Number,
    required: true,
  },
})

const emit = defineEmits(['message-sent'])

const authStore = useAuthStore()
const currentUserId = authStore.user?.id
const newMessage = ref('')
const sending = ref(false)
const messagesContainer = ref(null)

const sendMessage = async () => {
  if (!newMessage.value.trim()) return

  sending.value = true

  try {
    await apiClient.post('/messages', {
      to_user_id: props.recipientId,
      content: newMessage.value,
    })

    newMessage.value = ''
    emit('message-sent')
  } catch (error) {
    alert('Erreur lors de l\'envoi du message')
  } finally {
    sending.value = false
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

// Scroll vers le bas quand de nouveaux messages arrivent
watch(
  () => props.messages.length,
  () => {
    nextTick(() => {
      if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
      }
    })
  }
)
</script>

<style scoped>
.messages-thread {
  display: flex;
  flex-direction: column;
  height: 600px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.message {
  margin-bottom: 1rem;
  padding: 0.75rem;
  border-radius: 8px;
  background-color: #f3f4f6;
  max-width: 70%;
}

.message-sent {
  margin-left: auto;
  background-color: #3b82f6;
  color: white;
}

.message-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.message-time {
  opacity: 0.7;
}

.message-content {
  word-wrap: break-word;
}

.message-input {
  display: flex;
  gap: 0.5rem;
  padding: 1rem;
  border-top: 1px solid #e9ecef;
}

.message-input textarea {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  resize: none;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  cursor: pointer;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>

