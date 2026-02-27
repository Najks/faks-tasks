<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useSubjects, type Task } from '../composables/useSubjects'
import StatisticsPanel from './StatisticsPanel.vue'
import UserPanel from './UserPanel.vue'

const { subjects, tasks, loading, error, fetchSubjects } = useSubjects()

const isTaskCompleted = (task: Task) => task.status?.status === 'completed'

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
    </div>
  </div>
</template>

<style scoped>
.blurb-card {
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(13, 110, 253, 0.08), rgba(13, 67, 172, 0.08));
}
</style>

