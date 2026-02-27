<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { user, logout, isAuthenticated } = useAuth()
const userName = computed(() => user.value?.name ?? 'Student')
const userInitial = computed(() => (user.value?.name ? user.value.name.charAt(0).toUpperCase() : 'S'))
const authLinks = [
  { label: 'Home', to: { name: 'Home' } },
  { label: 'Manage Subjects', to: { name: 'ManageSubjects' } },
  { label: 'Overview', to: { name: 'User' } }
]
const guestLinks = [
  { label: 'Login', to: { name: 'Login' } },
  { label: 'Register', to: { name: 'Register' } }
]

const handleLogout = () => {
  logout()
  router.push({ name: 'Login' })
}
</script>

<template>
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm border-bottom">
    <div class="container">
      <router-link class="navbar-brand fw-semibold" to="/">Faks Tasks</router-link>
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#mainNavbar"
        aria-controls="mainNavbar"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNavbar">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li
            v-for="link in isAuthenticated ? authLinks : guestLinks"
            :key="link.label"
            class="nav-item"
          >
            <router-link class="nav-link" :to="link.to">{{ link.label }}</router-link>
          </li>
        </ul>
        <div class="d-flex align-items-center gap-3 ms-lg-4">
          <div v-if="isAuthenticated" class="user-pill d-flex align-items-center gap-3">
            <div class="avatar">{{ userInitial }}</div>
            <div>
              <div class="fw-semibold mb-0">{{ userName }}</div>
            </div>
          </div>
          <button v-if="isAuthenticated" class="btn btn-outline-danger btn-sm" @click="handleLogout">
            Logout
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.user-pill {
  padding: 0.35rem 0.75rem;
  border-radius: 999px;
  background: #f8f9fa;
  border: 1px solid #e9ecef;
}
.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #0d6efd;
  color: white;
  display: grid;
  place-items: center;
  font-weight: 600;
}
.navbar-nav .nav-link {
  font-weight: 500;
}
</style>
