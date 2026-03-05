<script setup lang="ts">
import { onMounted, computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useSubjects, type Task } from '../composables/useSubjects'
import { TASK_STATUS } from '../constants/taskStatus'

const router = useRouter()
const { subjects, tasks, loading, error, fetchSubjects, updateTaskStatus } = useSubjects()

const isTaskCompleted = (task: Task) => task.status?.status === TASK_STATUS.COMPLETED

const goToSubject = (subjectId: string | number) => {
  router.push({ name: 'SubjectDetail', params: { id: String(subjectId) } })
}

const goToTask = (taskId: number) => {
  router.push({ name: 'TaskDetail', params: { id: taskId } })
}

const formatDueDate = (d?: string) => {
  if (!d) return ''
  try {
    return new Date(d).toLocaleDateString()
  } catch (e) {
    return d
  }
}

const truncate = (text?: string | null, n = 120) => {
  if (!text) return ''
  return text.length > n ? text.slice(0, n - 1) + '…' : text
}

const isOverdue = (task: Task) => {
  if (!task.due_date) return false
  if (isTaskCompleted(task)) return false
  const due = new Date(task.due_date)
  const now = new Date()
  return due < now
}

// UI state: which group is expanded
const expanded = ref<Record<string | number, boolean>>({})

// tasks that are currently updating (by id)
const updating = ref<Record<number, boolean>>({})

const toggleExpand = (subjectId: string | number) => {
  expanded.value[subjectId] = !expanded.value[subjectId]
}

const toggleTaskCompletion = async (task: Task) => {
  if (!updateTaskStatus) return
  if (updating.value[task.id]) return
  updating.value[task.id] = true
  try {
    const completed = isTaskCompleted(task)
    await updateTaskStatus(task.id, !completed)
    await fetchSubjects()
  } catch (e) {
    // optionally show UI feedback
    console.error('Failed to update task status', e)
  } finally {
    updating.value[task.id] = false
  }
}

// helper to detect numeric subject IDs (we sometimes use 'unassigned' string)
const isNumericId = (id: unknown): boolean => {
  return /^\d+$/.test(String(id))
}

// Group tasks by subject, sort pending before completed, then by due date
const groupedTasks = computed(() => {
  const map = new Map<number | string, { subjectId: number | string; subjectName: string; tasks: Task[] }>()

  tasks.value.forEach((t: Task) => {
    const subjectId = (t as any).subject_id ?? (t.subject && (t.subject as any).id) ?? 'unassigned'
    const subjectName = (t as any).subjectName ?? (t.subject && (t.subject as any).name) ?? 'Unassigned'

    if (!map.has(subjectId)) {
      map.set(subjectId, { subjectId, subjectName, tasks: [] })
    }
    map.get(subjectId)!.tasks.push(t)
  })

  const groups = Array.from(map.values()).map(g => {
    g.tasks.sort((a, b) => {
      const aComp = isTaskCompleted(a) ? 1 : 0
      const bComp = isTaskCompleted(b) ? 1 : 0
      if (aComp !== bComp) return aComp - bComp

      const aDue = a.due_date ? new Date(a.due_date).getTime() : Infinity
      const bDue = b.due_date ? new Date(b.due_date).getTime() : Infinity
      return aDue - bDue
    })
    return g
  })

  // sort groups by number of pending tasks desc
  groups.sort((g1, g2) => {
    const p1 = g1.tasks.filter(t => !isTaskCompleted(t)).length
    const p2 = g2.tasks.filter(t => !isTaskCompleted(t)).length
    return p2 - p1
  })

  return groups
})

onMounted(async () => {
  await fetchSubjects()
})
</script>

<template>
  <div class="container py-5">
    <div class="text-center mb-4">
      <h1 class="display-5 fw-bold">Coursework Overview</h1>
      <p class="text-muted">Review every subject and the assignments currently assigned to you.</p>
    </div>

    <div v-if="loading" class="py-5 text-center text-muted">
      Loading...
    </div>
    <div v-else-if="error" class="alert alert-danger">
      {{ error }}
    </div>
    <div v-else>
      <section id="subjects" class="mb-5">
        <h2 class="h4 mb-3">Subjects</h2>
        <div class="row g-4">
          <div v-for="subject in subjects" :key="subject.id" class="col-12 col-md-6">
            <div class="card h-100 shadow-sm subject-card" @click="goToSubject(subject.id)">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                  <h3 class="h6 mb-2">{{ subject.name }}</h3>
                  <span
                    class="badge rounded-pill fs-6"
                    :class="subject.my_pending_tasks_count ? 'bg-warning text-dark' : 'bg-success'"
                  >
                    {{ subject.my_pending_tasks_count }} remaining
                  </span>
                </div>
                <p class="text-muted mb-2">
                  {{ subject.my_tasks_count }} tasks assigned · {{ subject.my_pending_tasks_count }} pending
                </p>
                <div class="progress" style="height:8px; border-radius:8px; overflow:hidden">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    :style="{ width: (subject.my_tasks_count ? Math.round(((subject.my_tasks_count - subject.my_pending_tasks_count) / subject.my_tasks_count) * 100) : 0) + '%' }"
                    :class="subject.my_pending_tasks_count ? 'bg-info' : 'bg-success'"
                    aria-valuenow="0"
                    aria-valuemin="0"
                    aria-valuemax="100"
                  ></div>
                </div>
              </div>
            </div>
          </div>
          <div v-if="!subjects.length" class="col-12">
            <div class="alert alert-secondary mb-0">
              No subjects yet. Add one from the subjects page once you are set up.
            </div>
          </div>
        </div>
      </section>

      <section id="tasks" v-if="tasks.length">
        <h2 class="h4 mb-3">Assigned Tasks</h2>

        <div v-for="group in groupedTasks" :key="group.subjectId" class="mb-4">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
              <h3 class="h6 mb-0">{{ group.subjectName }}</h3>
              <p class="mb-0 small text-muted">
                Showing {{ expanded[group.subjectId] ? group.tasks.length : Math.min(3, group.tasks.length) }} of {{ group.tasks.length }} · {{ group.tasks.filter(t => !isTaskCompleted(t)).length }} pending
              </p>
            </div>
            <div class="d-flex align-items-center gap-2">
              <button class="btn btn-sm btn-outline-secondary" @click.stop="toggleExpand(group.subjectId)">
                {{ expanded[group.subjectId] ? 'Collapse' : 'Show all' }}
              </button>
              <router-link
                v-if="isNumericId(group.subjectId)"
                :to="{ name: 'SubjectDetail', params: { id: String(group.subjectId) } }"
                class="btn btn-sm btn-outline-primary"
                @click.stop
                :aria-label="`View subject ${group.subjectName}`"
              >
                View subject →
              </router-link>
              <button v-else class="btn btn-sm btn-outline-secondary" disabled aria-hidden="true">Unassigned</button>
            </div>
          </div>

          <ul class="list-group">
            <li
              v-for="task in (expanded[group.subjectId] ? group.tasks : group.tasks.slice(0, 3))"
              :key="task.id"
              :class="['list-group-item d-flex justify-content-between align-items-start gap-3 task-card', isOverdue(task) ? 'overdue' : '']"
              @click="goToTask(task.id)">
              <div class="w-100">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <h4 class="h6 mb-1">{{ task.title }}</h4>
                    <p class="mb-0 small text-muted">{{ task.subjectName ?? task.subject?.name }}</p>
                  </div>
                  <div class="text-end ms-3">
                    <small :class="isOverdue(task) ? 'text-danger' : 'text-muted'">{{ formatDueDate(task.due_date) }}</small>
                  </div>
                </div>
                <p class="mb-0 small text-muted mt-2">{{ truncate(task.description) }}</p>
              </div>

              <div class="ms-3 d-flex flex-column align-items-end">
                <button
                  class="btn btn-sm"
                  :class="isTaskCompleted(task) ? 'btn-success' : 'btn-outline-warning text-dark'"
                  :disabled="updating[task.id]"
                  @click.stop="toggleTaskCompletion(task)">
                  <span v-if="updating[task.id]" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                  <span v-else>{{ isTaskCompleted(task) ? 'Completed' : 'Mark done' }}</span>
                </button>
              </div>
            </li>
          </ul>
        </div>

      </section>
    </div>
  </div>
</template>

<style scoped>
.subject-card {
  border-radius: 12px;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.subject-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 18px rgba(0,0,0,0.06);
}
.task-card {
  border-radius: 10px;
  cursor: pointer;
  transition: transform 0.12s ease, box-shadow 0.12s ease;
  margin-bottom: 8px;
}
.task-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(0,0,0,0.04);
}
.task-card.overdue {
  border-left: 4px solid #dc3545; /* bootstrap danger */
}
.list-group-item .btn {
  min-width: 96px;
}
</style>
