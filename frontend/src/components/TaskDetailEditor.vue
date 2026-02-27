<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApiRequest } from '../composables/useApiRequest'

interface TaskDetail {
  id: number
  title: string
  description?: string | null
  due_date?: string
  grade?: number | null
  subject_id: number
  subject?: {
    id: number
    name: string
  }
  status?: {
    id: number
    status: string
  }
}

interface TaskStatus {
  id: number
  status: string
}

const route = useRoute()
const router = useRouter()
const { get: fetchTaskDetail, loading: taskLoading, error: taskError } = useApiRequest()
const { get: fetchTaskStatuses, loading: statusesLoading, error: statusesError } = useApiRequest()
const { patch: updateTask, del: deleteTask, loading: actionLoading, error: actionError } = useApiRequest()

const task = ref<TaskDetail | null>(null)
const statuses = ref<TaskStatus[]>([])
const editing = ref(false)
const form = ref({
  title: '',
  description: '',
  due_date: '',
  grade: '',
  status_id: ''
})
const actionMessage = ref<string | null>(null)

const taskId = computed(() => {
  const raw = route.params.id
  const candidate = Array.isArray(raw) ? raw[0] : raw
  return typeof candidate === 'string' ? parseInt(candidate, 10) : NaN
})

const statusLabel = computed(() => task.value?.status?.status ?? 'Unknown')
const statusBadgeClass = computed(() => {
  if (task.value?.status?.status === 'completed') {
    return 'bg-success text-white'
  }
  if (!task.value?.status?.status) {
    return 'bg-secondary text-white'
  }
  return 'bg-warning text-dark'
})

const formattedDueDate = computed(() => {
  if (!task.value?.due_date) return '—'
  return new Date(task.value.due_date).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
})

const gradeLabel = computed(() => {
  if (task.value?.grade === null || task.value?.grade === undefined) {
    return 'Not graded yet'
  }
  return task.value.grade.toString()
})

const isLoading = computed(() => taskLoading.value || statusesLoading.value)
const loadError = computed(() => taskError.value ?? statusesError.value)
const statusOptions = computed(() => {
  if (statuses.value.length) {
    return statuses.value
  }
  if (task.value?.status) {
    return [task.value.status]
  }
  return []
})
const isSaveDisabled = computed(() =>
  actionLoading.value ||
  !form.value.title.trim() ||
  !form.value.due_date ||
  !form.value.status_id
)

const populateForm = (source: TaskDetail) => {
  form.value = {
    title: source.title,
    description: source.description ?? '',
    due_date: source.due_date ? source.due_date.slice(0, 10) : '',
    grade: source.grade === null || source.grade === undefined ? '' : source.grade.toString(),
    status_id: source.status?.id?.toString() ?? ''
  }
}

const fetchTask = async () => {
  if (!Number.isFinite(taskId.value)) {
    task.value = null
    return
  }

  try {
    const fetched = await fetchTaskDetail<TaskDetail>(
      `/tasks/${taskId.value}`,
      undefined,
      'Failed to load task details'
    )
    task.value = fetched
    if (!editing.value) {
      populateForm(fetched)
    }
  } catch {
    task.value = null
  }
}

const fetchStatuses = async () => {
  try {
    statuses.value = await fetchTaskStatuses<TaskStatus[]>(
      '/status',
      undefined,
      'Failed to load task statuses'
    )
  } catch {
    statuses.value = []
  }
}

const startEditing = () => {
  if (!task.value) return
  actionMessage.value = null
  populateForm(task.value)
  editing.value = true
}

const cancelEditing = () => {
  editing.value = false
  actionMessage.value = null
  if (task.value) {
    populateForm(task.value)
  }
}

const submitChanges = async () => {
  if (!task.value) return

  const payload = {
    title: form.value.title.trim(),
    description: form.value.description.trim() || null,
    due_date: form.value.due_date || null,
    grade: form.value.grade === '' ? null : Number(form.value.grade),
    status_id: Number(form.value.status_id),
    subject_id: task.value.subject_id
  }

  try {
    const updated = await updateTask<TaskDetail>(
      `/tasks/${taskId.value}`,
      payload,
      undefined,
      'Failed to update task'
    )
    task.value = updated
    populateForm(updated)
    editing.value = false
    actionMessage.value = `Task "${updated.title}" saved successfully.`
  } catch {
    // errors surface through actionError
    actionMessage.value = null
  }
}

const handleDelete = async () => {
  if (!task.value) return

  const confirmed = window.confirm('Delete this task? This cannot be undone.')
  if (!confirmed) return

  actionMessage.value = null

  try {
    await deleteTask(`/tasks/${taskId.value}`, undefined, 'Failed to delete task')
    router.push({ name: 'Home' })
  } catch {
    // swallow, actionError will show
  }
}

const goBack = () => {
  router.back()
}

onMounted(() => {
  void fetchStatuses()
  void fetchTask()
})

watch(() => taskId.value, () => {
  editing.value = false
  void fetchTask()
})

watch(task, (value) => {
  if (value && !editing.value) {
    populateForm(value)
  }
})
</script>

<template>
  <div class="container py-5">
    <button class="btn btn-link px-0 mb-3" @click="goBack">← Back to tasks</button>

    <div v-if="isLoading" class="py-5 text-center text-muted">
      Loading task details...
    </div>
    <div v-else-if="loadError" class="alert alert-danger">
      {{ loadError }}
    </div>
    <div v-else-if="task">
      <div class="card shadow-sm detail-card">
        <div class="card-body">
          <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
            <div>
              <p class="text-muted small mb-1">Subject · <strong>{{ task.subject?.name ?? 'Unassigned' }}</strong></p>
              <h1 class="h4 mb-1">{{ task.title }}</h1>
              <span class="badge" :class="statusBadgeClass">{{ statusLabel }}</span>
            </div>
            <div class="text-md-end">
              <p class="mb-1 text-muted small">Due</p>
              <p class="mb-0 fw-semibold">{{ formattedDueDate }}</p>
              <p class="mb-1 text-muted small mt-3">Grade</p>
              <p class="mb-0 fw-semibold">{{ gradeLabel }}</p>
            </div>
          </div>
          <div class="detail-meta mt-3">
            <span class="meta-pill">Status: {{ statusLabel }}</span>
            <span class="meta-pill">Due: {{ formattedDueDate }}</span>
            <span class="meta-pill">Grade: {{ gradeLabel }}</span>
          </div>
          <div class="description-block mt-4">
            <h2 class="h6 mb-2">Description</h2>
            <p class="mb-0" :class="{ 'fst-italic text-muted': !task.description?.length }">
              {{ task.description ?? 'No description added yet.' }}
            </p>
          </div>
          <div v-if="actionMessage" class="alert alert-success mt-4 mb-0">
            {{ actionMessage }}
          </div>
          <div v-else-if="!editing && actionError" class="alert alert-danger mt-4 mb-0">
            {{ actionError }}
          </div>
        </div>
      </div>

      <div class="card shadow-sm action-card mt-4">
        <div class="card-body">
          <div v-if="editing">
            <h2 class="h6 mb-3">Edit Task</h2>
            <div v-if="actionError" class="alert alert-danger">
              {{ actionError }}
            </div>
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label" for="taskTitle">Title</label>
                <input
                  v-model="form.title"
                  id="taskTitle"
                  type="text"
                  class="form-control"
                  placeholder="Enter task title"
                />
              </div>
              <div class="col-12">
                <label class="form-label" for="taskDescription">Description</label>
                <textarea
                  v-model="form.description"
                  id="taskDescription"
                  class="form-control"
                  rows="3"
                  placeholder="Enter task description"
                ></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="taskDueDate">Due Date</label>
                <input
                  v-model="form.due_date"
                  id="taskDueDate"
                  type="date"
                  class="form-control"
                />
              </div>
              <div class="col-md-6">
                <label class="form-label" for="taskGrade">Grade</label>
                <input
                  v-model="form.grade"
                  id="taskGrade"
                  type="number"
                  class="form-control"
                  min="0"
                  step="any"
                  placeholder="Leave empty if not graded"
                />
              </div>
              <div class="col-12">
                <label class="form-label" for="taskStatus">Status</label>
                <select
                  v-model="form.status_id"
                  id="taskStatus"
                  class="form-select"
                  aria-label="Select task status"
                >
                  <option value="">Select status</option>
                  <option v-for="status in statusOptions" :key="status.id" :value="status.id">
                    {{ status.status }}
                  </option>
                </select>
              </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
              <button
                @click="submitChanges"
                :disabled="isSaveDisabled"
                class="btn btn-primary"
              >
                <span v-if="actionLoading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                Save Changes
              </button>
              <button @click="cancelEditing" class="btn btn-outline-secondary">
                Cancel
              </button>
            </div>
          </div>
          <div v-else class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-center">
            <p class="mb-0 text-muted small">Want to tweak this task or remove it?</p>
            <div class="d-flex flex-wrap gap-2">
              <button @click="startEditing" class="btn btn-primary">Edit Task</button>
              <button @click="handleDelete" class="btn btn-outline-danger" :disabled="actionLoading">Delete Task</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="alert alert-secondary">
      Task not found.
    </div>
  </div>
</template>

<style scoped>
.detail-card {
  border-radius: 16px;
}

.meta-pill {
  background-color: #f1f1f1;
  border-radius: 12px;
  padding: 0.25rem 0.75rem;
  margin-right: 0.5rem;
  font-size: 0.875rem;
}

.description-block {
  border-top: 1px solid #e9ecef;
  padding-top: 1.5rem;
}

.action-card {
  border-radius: 16px;
}
</style>
