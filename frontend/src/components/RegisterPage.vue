<!-- src/components/RegisterPage.vue -->
<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { register, loading, error } = useAuth()

const form = ref({
  username: '',
  name: '',
  email: '',
  password: '',
  passwordConfirmation: ''
})

const handleSubmit = async () => {
  if (form.value.password !== form.value.passwordConfirmation) {
    return
  }

  try {
    await register(form.value)
    await router.push('/')
  } catch (err) {
    // Error is handled in composable
  }
}

const passwordsMatch = () => form.value.password === form.value.passwordConfirmation
</script>

<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light py-5">
    <div class="card shadow-lg" style="width: min(460px, 100%);">
      <div class="card-body p-4">
        <h1 class="h3 fw-bold text-center mb-4">Register</h1>

        <form @submit.prevent="handleSubmit" class="needs-validation" novalidate>
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input
              id="username"
              v-model="form.username"
              type="text"
              required
              class="form-control"
            />
          </div>

          <div class="mb-3">
            <label for="name" class="form-label">Full name</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="form-control"
            />
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="form-control"
            />
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="form-control"
            />
          </div>

          <div class="mb-3">
            <label for="passwordConfirmation" class="form-label">Confirm Password</label>
            <input
              id="passwordConfirmation"
              v-model="form.passwordConfirmation"
              type="password"
              required
              class="form-control"
            />
          </div>

          <div v-if="!passwordsMatch() && form.passwordConfirmation" class="text-danger small mb-3">
            Passwords do not match
          </div>

          <div v-if="error" class="alert alert-danger py-2">
            {{ error }}
          </div>

          <button
            type="submit"
            :disabled="loading || !passwordsMatch()"
            class="btn btn-primary w-100"
          >
            {{ loading ? 'Registering...' : 'Register' }}
          </button>
        </form>

        <p class="text-center text-muted mt-4 mb-0">
          Already have an account?
          <router-link to="/login" class="link-primary">Login</router-link>
        </p>
      </div>
    </div>
  </div>
</template>
