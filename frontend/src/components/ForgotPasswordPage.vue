<script setup lang="ts">
import { ref } from 'vue'
import { useApiRequest } from '../composables/useApiRequest'
import { useRouter } from 'vue-router'

const router = useRouter()
const email = ref('')
const success = ref('')
const { post, loading, error } = useApiRequest()

const handleSubmit = async () => {
  success.value = ''
  if (!email.value.trim()) return
  try {
    const response = await post<{ message: string }>('/auth/password/request', { email: email.value.trim() }, undefined, 'Unable to create reset token')
    success.value = response.message
  } catch {
    // error handled by composable
  }
}

const goToReset = () => {
  router.push({ name: 'ResetPassword' })
}
</script>

<template>
  <div class="container py-5">
    <div class="card shadow-sm mx-auto" style="max-width: 480px;">
      <div class="card-body">
        <h1 class="h4 mb-3">Forgot your password?</h1>
        <p class="text-muted">Enter your university email to receive a temporary reset token.</p>
        <div class="mb-3">
          <label class="form-label" for="forgotEmail">Email</label>
          <input
            id="forgotEmail"
            v-model="email"
            type="email"
            class="form-control"
            placeholder="you@university.edu"
          />
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-primary" @click="handleSubmit" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
            Send token
          </button>
        </div>
        <div v-if="success" class="alert alert-success mt-3 mb-0">
          {{ success }}
          <button class="btn btn-link px-0" type="button" @click="goToReset">Set a new password</button>
        </div>
        <div v-else-if="error" class="alert alert-danger mt-3 mb-0">
          {{ error }}
        </div>
      </div>
    </div>
  </div>
</template>
