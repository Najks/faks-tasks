<!-- src/components/LoginPage.vue -->
<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { login, loading, error } = useAuth()
const route = useRoute()

const form = ref({
  email: '',
  password: ''
})

const handleSubmit = async () => {
  try {
    await login(form.value)
    const redirect = (route.query.redirect as string) || '/'
    await router.replace(redirect)
  } catch (err) {
    // Error is handled in composable
  }
}
</script>

<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light py-5">
    <div class="card shadow-lg" style="width: min(420px, 100%);">
      <div class="card-body p-4">
        <h1 class="h3 fw-bold text-center mb-4">Login</h1>

        <form @submit.prevent="handleSubmit" class="needs-validation" novalidate>
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

          <div v-if="error" class="alert alert-danger py-2">
            {{ error }}
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="btn btn-primary w-100"
          >
            {{ loading ? 'Logging in...' : 'Login' }}
          </button>
        </form>

        <p class="text-center text-muted mt-3 mb-0">
          <router-link to="/password/forgot" class="link-secondary">Forgot password?</router-link>
        </p>

        <p class="text-center text-muted mt-4 mb-0">
          Don't have an account?
          <router-link to="/register" class="link-primary">Register</router-link>
        </p>
      </div>
    </div>
  </div>
</template>
