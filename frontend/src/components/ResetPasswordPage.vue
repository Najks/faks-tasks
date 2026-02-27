<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useApiRequest } from '../composables/useApiRequest'

const router = useRouter()
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const successMessage = ref('')
const { post, loading, error } = useApiRequest()

const handleReset = async () => {
  successMessage.value = ''
  if (!email.value || !password.value || password.value !== confirmPassword.value) {
    return
  }

  try {
    await post('/auth/password/reset/simple', {
      email: email.value.trim(),
      password: password.value,
      password_confirmation: confirmPassword.value
    }, undefined, 'Failed to reset password')
    successMessage.value = 'Password reset successful. Redirecting to login...'
    setTimeout(() => router.push({ name: 'Login' }), 1200)
  } catch {
    // handled by composable
  }
}
</script>

<template>
  <div class="container py-5">
    <div class="card shadow-sm mx-auto" style="max-width: 560px;">
      <div class="card-body">
        <h1 class="h4 mb-3">Reset password</h1>
        <p class="text-muted">Provide your email and a new password to reset immediately.</p>
        <div class="mb-3">
          <label class="form-label" for="resetEmail">Email</label>
          <input id="resetEmail" v-model="email" type="email" class="form-control" placeholder="you@university.edu" />
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="resetPassword">New password</label>
            <input id="resetPassword" v-model="password" type="password" class="form-control" placeholder="New password" />
          </div>
          <div class="col-md-6">
            <label class="form-label" for="resetConfirm">Confirm password</label>
            <input id="resetConfirm" v-model="confirmPassword" type="password" class="form-control" placeholder="Confirm it" />
          </div>
        </div>
        <div class="d-flex gap-2 mt-4">
          <button class="btn btn-primary" @click="handleReset" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
            Reset password
          </button>
          <button class="btn btn-outline-secondary" type="button" @click="$router.push({ name: 'Login' })">Back to login</button>
        </div>
        <div v-if="successMessage" class="alert alert-success mt-3 mb-0">
          {{ successMessage }}
        </div>
        <div v-else-if="error" class="alert alert-danger mt-3 mb-0">
          {{ error }}
        </div>
      </div>
    </div>
  </div>
</template>

