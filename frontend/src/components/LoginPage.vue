<!-- src/components/LoginPage.vue -->
<script setup lang="ts">
import { reactive, computed, ref, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { login, loading, error: serverError } = useAuth()
const route = useRoute()

const form = reactive({
  email: '',
  password: ''
})

// Safe server error accessor (handles string or ref<string|null>)
const serverErr = computed(() => {
  const s = serverError as unknown as any
  if (s && typeof s === 'object' && 'value' in s) return s.value
  return s ?? null
})

const validationErrors = reactive<Record<string, string>>({})
const generalError = ref<string | null>(null)

const showPassword = ref(false)

const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

// Live validation watchers
watch(() => form.email, (v) => {
  validationErrors.email = ''
  if (!v || !v.trim()) validationErrors.email = 'Email is required.'
  else if (!emailRegex.test(v)) validationErrors.email = 'Please enter a valid email address.'
})

watch(() => form.password, (v) => {
  validationErrors.password = ''
  if (!v) validationErrors.password = 'Password is required.'
})

const validate = (): boolean => {
  validationErrors.email = ''
  validationErrors.password = ''
  generalError.value = null

  if (!form.email || !form.email.trim()) {
    validationErrors.email = 'Email is required.'
  } else if (!emailRegex.test(form.email)) {
    validationErrors.email = 'Please enter a valid email address.'
  }

  if (!form.password) {
    validationErrors.password = 'Password is required.'
  }

  return Object.values(validationErrors).every(v => !v)
}

const isValid = computed(() => {
  return form.email.trim() !== '' && form.password !== '' && emailRegex.test(form.email)
})

const handleSubmit = async () => {
  if (!validate()) return

  // clear server-driven field errors
  Object.keys(validationErrors).forEach(k => (validationErrors[k] = ''))
  generalError.value = null

  try {
    await login({ email: form.email.trim(), password: form.password })
    const redirect = (route.query.redirect as string) || '/'
    await router.replace(redirect)
  } catch (err: any) {
    const srv = serverErr.value ?? (err && (err.response?.data ?? err))

    // Reset field errors
    Object.keys(validationErrors).forEach(k => (validationErrors[k] = ''))

    if (srv && typeof srv === 'object' && srv.errors && typeof srv.errors === 'object') {
      for (const [key, val] of Object.entries(srv.errors)) {
        if (Array.isArray(val)) validationErrors[key] = String(val[0])
        else validationErrors[key] = String(val)
      }
      generalError.value = srv.message ?? 'Login failed. Please check highlighted fields.'
    } else {
      // Don't show raw server error in UI; prefer a friendly message and per-field messages
      generalError.value = 'Login failed. Please check your credentials and try again.'
      // console.warn('server error (login):', srv)
    }
  }
}

const fieldClass = (field: string) => {
  const hasError = !!validationErrors[field]
  const hasValue = !!(form as any)[field]
  return {
    'is-invalid': hasError,
    'is-valid': !hasError && hasValue
  }
}

const trimOnBlur = (field: string) => {
  const v = (form as any)[field]
  if (typeof v === 'string') (form as any)[field] = v.trim()
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
              class="form-control"
              :class="fieldClass('email')"
              v-model="form.email"
              name="email"
              type="email"
              required
              autocomplete="email"
              autofocus
              @blur="trimOnBlur('email')"
              aria-describedby="email-error"
            />
            <div v-if="validationErrors.email" id="email-error" class="text-danger small mt-1" role="alert" aria-live="polite">{{ validationErrors.email }}</div>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <input
                :type="showPassword ? 'text' : 'password'"
                id="password"
                class="form-control"
                :class="fieldClass('password')"
                v-model="form.password"
                name="password"
                required
                autocomplete="current-password"
                @blur="trimOnBlur('password')"
                aria-describedby="password-error"
              />
              <button type="button" class="btn btn-outline-secondary" @click="showPassword = !showPassword" :aria-pressed="showPassword">
                {{ showPassword ? 'Hide' : 'Show' }}
              </button>
            </div>
            <div v-if="validationErrors.password" id="password-error" class="text-danger small mt-1" role="alert" aria-live="polite">{{ validationErrors.password }}</div>
          </div>

          <div v-if="generalError" class="alert alert-danger py-2" role="alert" aria-live="polite">{{ generalError }}</div>

          <button type="submit" :disabled="loading || !isValid" class="btn btn-primary w-100">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
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
