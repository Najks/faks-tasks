<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useSubjects, type Task } from '../composables/useSubjects'

const router = useRouter()
const { subjects, tasks, loading, error, fetchSubjects } = useSubjects()

const isTaskCompleted = (task: Task) => task.status?.status === 'completed'

const goToSubject = (subjectId: number) => {
  router.push({ name: 'SubjectDetail', params: { id: subjectId } })
}

const goToTask = (taskId: number) => {
  router.push({ name: 'TaskDetail', params: { id: taskId } })
}

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
                <p class="text-muted mb-0">
                  {{ subject.my_tasks_count }} tasks assigned · {{ subject.my_pending_tasks_count }} pending
                </p>
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
        <div class="row g-4">
          <div v-for="task in tasks" :key="task.id" class="col-12">
            <div class="card shadow-sm border-0 task-card" @click="goToTask(task.id)">
              <div class="card-body d-flex justify-content-between align-items-center gap-3">
                <div>
                  <h3 class="h6 mb-1">{{ task.title }}</h3>
                  <p class="mb-0 small text-muted">
                    {{ task.subjectName }}
                  </p>
                </div>
                <span
                  class="badge"
                  :class="isTaskCompleted(task) ? 'bg-success' : 'bg-warning text-dark'"
                >
                  {{ isTaskCompleted(task) ? 'Completed' : 'Pending' }}
                </span>
               </div>
             </div>
           </div>
         </div>
       </section>
     </div>
   </div>
 </template>

<style scoped>
.subject-card {
  border-radius: 12px;
  cursor: pointer;
  transition: transform 0.2s ease;
}
.subject-card:hover {
  transform: translateY(-2px);
}
.task-card {
  border-radius: 12px;
  cursor: pointer;
  transition: transform 0.2s ease;
}
.task-card:hover {
  transform: translateY(-2px);
}
</style>
