<template>
  <div class="questionnaire-form">
    <h3>{{ questionnaire.title }}</h3>
    <p v-if="questionnaire.description">{{ questionnaire.description }}</p>

    <form @submit.prevent="submitForm">
      <div
        v-for="(question, index) in questionnaire.questions"
        :key="index"
        class="question-item"
      >
        <label>
          <strong>{{ question.text || question.question }}</strong>
          <select
            v-model="answers[index]"
            :required="question.required !== false"
          >
            <option value="">Sélectionner...</option>
            <option
              v-for="option in question.options"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label || option.text }}
            </option>
          </select>
        </label>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary" :disabled="submitting">
          {{ submitting ? 'Envoi...' : 'Soumettre' }}
        </button>
        <button type="button" @click="$emit('cancel')" class="btn-secondary">
          Annuler
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useQuestionnaireStore } from '@/stores/questionnaireStore'

const props = defineProps({
  questionnaire: {
    type: Object,
    required: true,
  },
  assignmentId: {
    type: Number,
    default: null,
  },
})

const emit = defineEmits(['success', 'cancel'])

const questionnaireStore = useQuestionnaireStore()
const answers = ref({})
const submitting = ref(false)

const submitForm = async () => {
  submitting.value = true

  try {
    const result = await questionnaireStore.submitResponse(
      props.questionnaire.id,
      answers.value,
      props.assignmentId
    )

    if (result.success) {
      emit('success', result.data)
    } else {
      alert(result.error || 'Erreur lors de la soumission')
    }
  } catch (error) {
    alert('Erreur lors de la soumission')
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.questionnaire-form {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.question-item {
  margin-bottom: 1.5rem;
}

.question-item label {
  display: block;
  margin-bottom: 0.5rem;
}

.question-item select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  cursor: pointer;
}

.btn-secondary {
  background-color: #6b7280;
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

