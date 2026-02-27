<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { user, logout } = useAuth()

const name = computed(() => user.value?.name ?? 'Student')
const email = computed(() => user.value?.email ?? '')

const handleLogout = () => {
  logout()
  router.push({ name: 'Login' })
}

const goToSubjects = () => {
  router.push({ name: 'Home' })
}
</script>

<template>
  <div class="user-panel card mb-4 px-3 py-3">
    <div class="card-body d-flex flex-column flex-md-row justify-content-between gap-3 align-items-start">
      <div>
        <p class="text-muted mb-1">Currently logged in as</p>
        <h2 class="h5 mb-0">{{ name }}</h2>
        <p class="text-muted small mb-0">{{ email }}</p>
      </div>
      <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
        <button class="btn btn-outline-secondary" @click="goToSubjects">Your subjects</button>
        <button class="btn btn-danger" @click="handleLogout">Logout</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.user-panel {
  border-radius: 16px;
}
</style>
