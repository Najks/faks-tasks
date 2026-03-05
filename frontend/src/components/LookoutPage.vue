<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useSubjects, type Task } from '../composables/useSubjects'
import { useAuth } from '../composables/useAuth'
import { useApiRequest } from '../composables/useApiRequest'
import StatisticsPanel from './StatisticsPanel.vue'
import UserPanel from './UserPanel.vue'
import { TASK_STATUS } from '../constants/taskStatus'

const { subjects, tasks, loading, error, fetchSubjects } = useSubjects()
const { user } = useAuth()
const { patch, loading: passwordLoading, error: passwordError } = useApiRequest()

const password = ref('')
const confirmPassword = ref('')
const passwordSuccess = ref('')

const passwordMatches = computed(() => password.value === confirmPassword.value)

const isTaskCompleted = (task: Task) => task.status?.status === TASK_STATUS.COMPLETED

const dashboardStats = computed(() => {
  const totalTasks = tasks.value.length
  const completed = tasks.value.filter(isTaskCompleted).length
  const pending = Math.max(totalTasks - completed, 0)
  const gradeValues = tasks.value
    .map(task => (task.grade === null || task.grade === undefined ? null : Number(task.grade)))
    .filter((value): value is number => value !== null && Number.isFinite(value))
  const averageGrade = gradeValues.length
    ? (gradeValues.reduce((acc, value) => acc + value, 0) / gradeValues.length).toFixed(1)
    : '—'
  return {
    subjectCount: subjects.value.length,
    totalTasks,
    completedTasks: completed,
    pendingTasks: pending,
    progress: totalTasks ? Math.round((completed / totalTasks) * 100) : 0,
    averageGrade
  }
})

onMounted(async () => {
  await fetchSubjects()
})

const handlePasswordUpdate = async () => {
  if (!password.value || !passwordMatches.value) {
    return
  }

  passwordSuccess.value = ''
  try {
    await patch('/auth/me', { password: password.value }, undefined, 'Failed to update password')
    passwordSuccess.value = 'Password updated successfully.'
    password.value = ''
    confirmPassword.value = ''
  } catch {
    // error surfaced through passwordError
  }
}
</script>

<template>
  <div class="container py-5">
    <div class="text-center mb-4">
      <h1 class="display-5 fw-bold">Your Overview</h1>
      <p class="text-muted">Everything you need to know about your workload at a glance.</p>
    </div>

    <UserPanel />
    <StatisticsPanel :stats="dashboardStats" />

    <div v-if="loading" class="py-5 text-center text-muted">Loading data...</div>
    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
    <div v-else class="mt-4">
      <div class="card shadow-sm border-0 blurb-card">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
          <div>
            <h2 class="h5 mb-1">Subjects</h2>
            <p class="mb-0 text-muted">You are enrolled in {{ subjects.length }} subject(s).</p>
          </div>
          <div>
            <h2 class="h5 mb-1">Tasks</h2>
            <p class="mb-0 text-muted">{{ tasks.length }} assigned to you, {{ dashboardStats.pendingTasks }} pending.</p>
          </div>
        </div>
      </div>
      <div class="card shadow-sm border-0 blurb-card mt-4">
        <div class="card-body">
          <h2 class="h5 mb-3">Account</h2>
          <p class="text-muted">Signed in as <strong>{{ user?.name }}</strong> ({{ user?.email }})</p>
          <div class="row g-3 align-items-end">
            <div class="col-md-5">
              <label class="form-label" for="newPassword">New password</label>
              <input id="newPassword" v-model="password" type="password" class="form-control" placeholder="At least 6 characters" />
            </div>
            <div class="col-md-5">
              <label class="form-label" for="confirmPassword">Confirm password</label>
              <input id="confirmPassword" v-model="confirmPassword" type="password" class="form-control" placeholder="Repeat it" />
            </div>
            <div class="col-md-2">
              <button class="btn btn-primary w-100" :disabled="passwordLoading || !passwordMatches" @click="handlePasswordUpdate">
                <span v-if="passwordLoading" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                Update
              </button>
            </div>
          </div>
          <div v-if="passwordSuccess" class="alert alert-success mt-3 mb-0">
            {{ passwordSuccess }}
          </div>
          <div v-else-if="passwordError" class="alert alert-danger mt-3 mb-0">
            {{ passwordError }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.blurb-card {
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(13, 110, 253, 0.08), rgba(13, 67, 172, 0.08));
}
</style>
