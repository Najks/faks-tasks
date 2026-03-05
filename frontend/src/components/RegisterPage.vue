<!-- src/components/RegisterPage.vue -->
<script setup lang="ts">
import { reactive, ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { register, loading, error: serverError } = useAuth()

const form = reactive({
  username: '',
  name: '',
  email: '',
  password: '',
  passwordConfirmation: ''
})

// Add a computed helper to safely read the server error whether it's a ref or plain string
const serverErr = computed(() => {
  const s = serverError as unknown as any
  if (s && typeof s === 'object' && 'value' in s) return s.value
  return s ?? null
})

const validationErrors = reactive<Record<string, string>>({})
const generalError = ref<string | null>(null)

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

// Live/field-level validation watchers
watch(() => form.username, (v) => {
  validationErrors.username = ''
  if (!v || !v.trim()) validationErrors.username = 'Username is required.'
})

watch(() => form.name, (v) => {
  validationErrors.name = ''
  if (!v || !v.trim()) validationErrors.name = 'Full name is required.'
})

watch(() => form.email, (v) => {
  validationErrors.email = ''
  if (!v || !v.trim()) validationErrors.email = 'Email is required.'
  else if (!emailRegex.test(v)) validationErrors.email = 'Please enter a valid email address.'
})

watch(() => form.password, (v) => {
  validationErrors.password = ''
  if (!v) validationErrors.password = 'Password is required.'
  else if (v.length < 8) validationErrors.password = 'Password must be at least 8 characters.'
  // also re-check confirmation
  if (form.passwordConfirmation && form.password !== form.passwordConfirmation) {
    validationErrors.passwordConfirmation = "Passwords don't match."
  } else {
    validationErrors.passwordConfirmation = ''
  }
})

watch(() => form.passwordConfirmation, (v) => {
  validationErrors.passwordConfirmation = ''
  if (!v) validationErrors.passwordConfirmation = 'Please confirm your password.'
  else if (form.password !== v) validationErrors.passwordConfirmation = "Passwords don't match."
})

const validate = (): boolean => {
  validationErrors.username = ''
  validationErrors.name = ''
  validationErrors.email = ''
  validationErrors.password = ''
  validationErrors.passwordConfirmation = ''
  generalError.value = null

  if (!form.username || !form.username.trim()) {
    validationErrors.username = 'Username is required.'
  }
  if (!form.name || !form.name.trim()) {
    validationErrors.name = 'Full name is required.'
  }
  if (!form.email || !form.email.trim()) {
    validationErrors.email = 'Email is required.'
  } else if (!emailRegex.test(form.email)) {
    validationErrors.email = 'Please enter a valid email address.'
  }
  if (!form.password) {
    validationErrors.password = 'Password is required.'
  } else if (form.password.length < 8) {
    validationErrors.password = 'Password must be at least 8 characters.'
  }
  if (!form.passwordConfirmation) {
    validationErrors.passwordConfirmation = 'Please confirm your password.'
  } else if (form.password !== form.passwordConfirmation) {
    validationErrors.passwordConfirmation = "Passwords don't match."
  }

  // focus the first invalid field if any
  const hasErrors = Object.values(validationErrors).some(v => !!v)
  if (hasErrors) focusFirstError()

  // return true if there are no validation error messages
  return !hasErrors
}

// focus on first field that has a validation error
const focusFirstError = () => {
  const order = ['username', 'name', 'email', 'password', 'passwordConfirmation']
  for (const key of order) {
    if (validationErrors[key]) {
      const el = document.getElementById(key) as HTMLElement | null
      if (el) el.focus()
      return
    }
  }
}

const isValid = computed(() => {
  // quick passive check to enable/disable button (does not mutate validationErrors)
  return (
    form.username.trim() !== '' &&
    form.name.trim() !== '' &&
    emailRegex.test(form.email) &&
    form.password.length >= 8 &&
    form.password === form.passwordConfirmation
  )
})

const passwordStrength = computed(() => {
  const pw = form.password || ''
  let score = 0
  if (pw.length >= 8) score += 1
  if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score += 1
  if (/[0-9]/.test(pw)) score += 1
  if (/[^A-Za-z0-9]/.test(pw)) score += 1
  const pct = Math.min(100, (score / 4) * 100)
  const label = score <= 1 ? 'Weak' : score === 2 ? 'Fair' : score === 3 ? 'Good' : 'Strong'
  return { score, pct, label }
})

const handleSubmit = async () => {
  // run validation and bail out if invalid
  if (!validate()) return

  // clear server-driven field errors before attempting
  Object.keys(validationErrors).forEach(k => (validationErrors[k] = ''))
  generalError.value = null

  try {
    await register({
      username: form.username.trim(),
      name: form.name.trim(),
      email: form.email.trim(),
      password: form.password,
      passwordConfirmation: form.passwordConfirmation
    })
    await router.push('/')
  } catch (err: any) {
    // Prefer the reactive serverErr computed if available (composable sets it),
    // otherwise try to read typical axios error shape (err.response.data).
    const srv = serverErr.value ?? (err && (err.response?.data ?? err))

    // Reset field errors
    Object.keys(validationErrors).forEach(k => (validationErrors[k] = ''))

    if (srv && typeof srv === 'object') {
      // Laravel-style validation: { message: 'The given data was invalid.', errors: { field: ['msg'] } }
      if (srv.errors && typeof srv.errors === 'object') {
        for (const [key, val] of Object.entries(srv.errors)) {
          // set first message for the field if it exists
          if (Array.isArray(val)) validationErrors[key] = String(val[0])
          else validationErrors[key] = String(val)
        }
        generalError.value = srv.message ?? null
        // focus first server-side error
        focusFirstError()
      } else {
        // Generic object mapping (field: "message")
        for (const [key, val] of Object.entries(srv)) {
          if (key in validationErrors) {
            validationErrors[key] = Array.isArray(val) ? String(val[0]) : String(val)
          }
        }
        // If there's a top-level message, display it; otherwise stringify the object
        generalError.value = (srv.message as string) ?? (srv.error as string) ?? JSON.stringify(srv)
        focusFirstError()
      }
    } else {
      // Only show a friendly message from the client side; keep server error out of UI unless we have nothing else
      generalError.value = generalError.value ?? 'Registration failed. Please check your input.'
      // log server error for debugging
      // console.warn('server error (register):', srv)
    }
  }
}

// trim inputs on blur to avoid accidental whitespace
const trimOnBlur = (field: string) => {
  const v = (form as any)[field]
  if (typeof v === 'string') (form as any)[field] = v.trim()
}

const fieldClass = (field: string) => {
  const hasError = !!validationErrors[field]
  const hasValue = !!(form as any)[field]
  return {
    'is-invalid': hasError,
    'is-valid': !hasError && hasValue
  }
}
</script>

<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light py-5">
    <div class="card shadow-lg" style="width: min(560px, 100%);">
      <div class="card-body p-4">
        <h1 class="h3 fw-bold text-center mb-4">Register</h1>

        <form @submit.prevent="handleSubmit" class="needs-validation" novalidate>
          <div class="row g-3">
            <div class="col-12">
              <label for="username" class="form-label">Username</label>
              <input id="username" class="form-control" :class="fieldClass('username')" v-model="form.username" name="username" type="text" required autocomplete="username" autofocus aria-describedby="username-error" @blur="trimOnBlur('username')" />
              <div v-if="validationErrors.username" id="username-error" class="text-danger small mt-1" role="alert" aria-live="polite">{{ validationErrors.username }}</div>
            </div>

            <div class="col-12">
              <label for="name" class="form-label">Full name</label>
              <input id="name" class="form-control" :class="fieldClass('name')" v-model="form.name" name="name" type="text" required autocomplete="name" aria-describedby="name-error" @blur="trimOnBlur('name')" />
              <div v-if="validationErrors.name" id="name-error" class="text-danger small mt-1" role="alert" aria-live="polite">{{ validationErrors.name }}</div>
            </div>

            <div class="col-12">
              <label for="email" class="form-label">Email</label>
              <input id="email" class="form-control" :class="fieldClass('email')" v-model="form.email" name="email" type="email" required autocomplete="email" aria-describedby="email-error" @blur="trimOnBlur('email')" />
              <div v-if="validationErrors.email" id="email-error" class="text-danger small mt-1" role="alert" aria-live="polite">{{ validationErrors.email }}</div>
            </div>

            <div class="col-md-6">
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
                  autocomplete="new-password"
                  aria-describedby="password-error"
                  @blur="trimOnBlur('password')"
                />
                <button type="button" class="btn btn-outline-secondary" @click="showPassword = !showPassword" :aria-pressed="showPassword">
                  {{ showPassword ? 'Hide' : 'Show' }}
                </button>
              </div>
              <div v-if="validationErrors.password" id="password-error" class="text-danger small mt-1" role="alert" aria-live="polite">{{ validationErrors.password }}</div>
              <div v-else class="form-text">Minimum 8 characters. Use upper, lower, numbers and symbols for a stronger password.</div>
              <div class="mt-2">
                <div class="progress" style="height:8px">
                  <div class="progress-bar" role="progressbar" :style="{ width: passwordStrength.pct + '%' }" :class="{
                    'bg-danger': passwordStrength.score <= 1,
                    'bg-warning': passwordStrength.score === 2,
                    'bg-info': passwordStrength.score === 3,
                    'bg-success': passwordStrength.score === 4
                  }" aria-valuenow="passwordStrength.pct" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted">Strength: {{ passwordStrength.label }}</small>
              </div>
            </div>

            <div class="col-md-6">
              <label for="passwordConfirmation" class="form-label">Confirm Password</label>
              <div class="input-group">
                <input
                  :type="showPasswordConfirmation ? 'text' : 'password'"
                  id="passwordConfirmation"
                  class="form-control"
                  :class="fieldClass('passwordConfirmation')"
                  v-model="form.passwordConfirmation"
                  name="passwordConfirmation"
                  required
                  autocomplete="new-password"
                  aria-describedby="passwordConfirmation-error"
                  @blur="trimOnBlur('passwordConfirmation')"
                />
                <button type="button" class="btn btn-outline-secondary" @click="showPasswordConfirmation = !showPasswordConfirmation" :aria-pressed="showPasswordConfirmation">
                  {{ showPasswordConfirmation ? 'Hide' : 'Show' }}
                </button>
              </div>
              <div v-if="validationErrors.passwordConfirmation" id="passwordConfirmation-error" class="text-danger small mt-1" role="alert" aria-live="polite">{{ validationErrors.passwordConfirmation }}</div>
            </div>

            <div class="col-12">
              <div v-if="generalError" class="alert alert-danger py-2">{{ generalError }}</div>

              <button type="submit" :disabled="loading || !isValid" class="btn btn-primary w-100">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                {{ loading ? 'Registering...' : 'Register' }}
              </button>
            </div>
          </div>
        </form>

        <p class="text-center text-muted mt-4 mb-0">
          Already have an account?
          <router-link to="/login" class="link-primary">Login</router-link>
        </p>
      </div>
    </div>
  </div>
</template>
