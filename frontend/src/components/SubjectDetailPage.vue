<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useSubjects, type Task } from '../composables/useSubjects'

const router = useRouter()
const route = useRoute()
const { subjects, loading, error, fetchSubjects, updateTaskStatus } = useSubjects()

const subjectId = parseInt(route.params.id as string)
const subject = computed(() => subjects.value.find(s => s.id === subjectId))

onMounted(async () => {
  await fetchSubjects()
})

const isTaskCompleted = (task: Task) => task.status?.status === 'completed'

type FilterMode = 'all' | 'pending' | 'completed'
const filterModes: FilterMode[] = ['all', 'pending', 'completed']
const filter = ref<FilterMode>('all')

const filteredTasks = computed(() => {
  const currentTasks = subject.value?.tasks ?? []
  return currentTasks.filter(task => {
    if (filter.value === 'all') return true
    if (filter.value === 'completed') return isTaskCompleted(task)
    return !isTaskCompleted(task)
  })
})

const stats = computed(() => {
  const total = subject.value?.tasks.length ?? 0
  const completed = subject.value?.tasks.filter(isTaskCompleted).length ?? 0
  return {
    total,
    completed,
    pending: total - completed
  }
})

const progress = computed(() => {
  if (!subject.value?.tasks.length) return 0
  return Math.round((stats.value.completed / stats.value.total) * 100)
})

const statsForMode = (mode: FilterMode) => {
  if (mode === 'all') return stats.value.total
  if (mode === 'completed') return stats.value.completed
  return stats.value.pending
}

const toggleLoadingId = ref<number | null>(null)
const actionError = ref<string | null>(null)
const actionMessage = ref<string | null>(null)
let feedbackTimer: ReturnType<typeof setTimeout> | null = null

const dismissFeedback = () => {
  actionMessage.value = null
  actionError.value = null
  if (feedbackTimer) {
    clearTimeout(feedbackTimer)
    feedbackTimer = null
  }
}

const showMessage = (message: string) => {
  actionError.value = null
  actionMessage.value = message
  if (feedbackTimer) {
    clearTimeout(feedbackTimer)
  }
  feedbackTimer = setTimeout(() => {
    actionMessage.value = null
    feedbackTimer = null
  }, 2700)
}

const setError = (message: string) => {
  actionMessage.value = null
  actionError.value = message
}

const handleTaskToggle = async (taskId: number) => {
  const currentSubject = subject.value
  if (!currentSubject?.tasks) return

  const task = currentSubject.tasks.find(t => t.id === taskId)
  if (!task) return

  const completed = !isTaskCompleted(task)
  toggleLoadingId.value = taskId
  dismissFeedback()

  try {
    const updated = await updateTaskStatus(taskId, completed)
    task.status = updated.status
    const label = completed ? 'completed' : 'marked as pending'
    showMessage(`Task '${task.title}' is now ${label}.`)
  } catch (err) {
    const message = err instanceof Error ? err.message : 'Unable to update status.'
    setError(message)
  } finally {
    toggleLoadingId.value = null
  }
}

const viewTaskDetails = (taskId: number) => {
  router.push({ name: 'TaskDetail', params: { id: taskId } })
}

const setFilter = (mode: 'all' | 'pending' | 'completed') => {
  filter.value = mode
}

const goBack = () => {
  router.push({ name: 'Home' })
}

const formatDueDate = (value?: string) => {
  if (!value) return 'No due date'
  return new Date(value).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}

const formatGrade = (grade?: number | null) => {
  if (grade === null || grade === undefined) return 'Not graded yet'
  return grade.toString()
}

const toggleButtonLabel = (task: Task) => isTaskCompleted(task) ? 'Mark as pending' : 'Mark as completed'
</script>

<template>
  <div class="container py-5">
    <button class="btn btn-link text-decoration-none px-0 mb-3" @click="goBack">
      ← Back to Subjects
    </button>

    <div v-if="loading" class="py-5 text-center text-muted">
      Loading...
    </div>
    <div v-else-if="error" class="alert alert-danger">
      {{ error }}
    </div>
    <div v-else-if="subject" class="card shadow-sm">
      <div class="card-body">
        <h1 class="display-6 fw-semibold mb-4">{{ subject.name }}</h1>

        <div class="mb-4">
          <span
            class="badge rounded-pill me-2"
            v-for="mode in filterModes"
            :key="mode"
            :class="filter === mode ? 'bg-primary text-white' : 'bg-light text-body'"
            @click="setFilter(mode)"
            style="cursor: pointer"
          >
            {{ mode.charAt(0).toUpperCase() + mode.slice(1) }} ({{ statsForMode(mode) }})
          </span>
        </div>

        <div class="progress mb-4" v-if="subject.tasks?.length">
          <div class="progress-bar"
            role="progressbar"
            :style="{ width: `${progress}%` }"
            :aria-valuenow="progress"
            aria-valuemin="0"
            aria-valuemax="100"
          >
            {{ progress }}% Complete
          </div>
        </div>
        <div v-if="actionMessage" class="alert alert-success alert-dismissible fade show" role="alert">
          {{ actionMessage }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" @click="dismissFeedback"></button>
        </div>
        <div v-else-if="actionError" class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ actionError }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" @click="dismissFeedback"></button>
        </div>

        <ul v-if="filteredTasks.length" class="list-group list-group-flush">
          <li
            v-for="task in filteredTasks"
            :key="task.id"
            class="list-group-item border-0 bg-transparent px-0"
          >
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-start task-row">
              <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                  <h3 class="h6 mb-0">{{ task.title }}</h3>
                  <span
                    class="badge"
                    :class="isTaskCompleted(task) ? 'bg-success text-white' : 'bg-warning text-dark'"
                  >
                    {{ isTaskCompleted(task) ? 'Completed' : 'Pending' }}
                  </span>
                </div>
                <p class="mb-0 small text-muted">
                  Due {{ formatDueDate(task.due_date) }} · Grade {{ formatGrade(task.grade) }}
                </p>
              </div>
              <div class="d-flex flex-wrap gap-2 justify-content-end">
                <button
                  type="button"
                  class="btn btn-outline-secondary btn-sm"
                  @click.stop="handleTaskToggle(task.id)"
                  :disabled="toggleLoadingId === task.id"
                >
                  <span v-if="toggleLoadingId === task.id" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                  {{ toggleButtonLabel(task) }}
                </button>
                <button
                  type="button"
                  class="btn btn-link btn-sm text-decoration-underline"
                  @click.stop="viewTaskDetails(task.id)"
                >
                  View details
                </button>
              </div>
            </div>
           </li>
         </ul>
         <p v-else class="text-muted fst-italic">No tasks yet</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.task-row {
  padding: 0.75rem 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}
.task-row:last-child {
  border-bottom: none;
}
</style>
