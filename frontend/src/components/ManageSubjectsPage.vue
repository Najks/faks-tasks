<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useSubjects, type Subject } from '../composables/useSubjects'
import { useApiRequest } from '../composables/useApiRequest'

const { subjects, fetchSubjects } = useSubjects()
const { post, loading: createLoading, error: createError } = useApiRequest()
const { patch, loading: editLoading, error: editError } = useApiRequest()
const { del, loading: deleteLoading, error: deleteError } = useApiRequest()

const name = ref('')
const successMessage = ref('')
const editingId = ref<number | null>(null)
const editingName = ref('')

const clearForm = () => {
  name.value = ''
  successMessage.value = ''
}

const startEditing = (subject: Subject) => {
  editingId.value = subject.id
  editingName.value = subject.name
  successMessage.value = ''
}

const cancelEditing = () => {
  editingId.value = null
  editingName.value = ''
}

const addSubject = async () => {
  if (!name.value.trim()) {
    return
  }

  try {
    await post('/subjects', { name: name.value.trim() }, undefined, 'Failed to add subject')
    successMessage.value = 'Subject added successfully.'
    clearForm()
    await fetchSubjects()
  } catch {
    // errors surface via createError
  }
}

const saveEdit = async () => {
  if (!editingId.value || !editingName.value.trim()) {
    return
  }

  try {
    await patch(
      `/subjects/${editingId.value}`,
      { name: editingName.value.trim() },
      undefined,
      'Failed to update subject'
    )
    successMessage.value = 'Subject updated successfully.'
    cancelEditing()
    await fetchSubjects()
  } catch {
    // errors flow through editError
  }
}

const removeSubject = async (subject: Subject) => {
  const confirmed = window.confirm(`Delete subject "${subject.name}"?`)
  if (!confirmed) return

  try {
    await del(`/subjects/${subject.id}`, undefined, 'Failed to delete subject')
    successMessage.value = 'Subject deleted.'
    await fetchSubjects()
  } catch {
    // deleteError
  }
}

const hasActionError = computed(() => createError.value || editError.value || deleteError.value)
const actionErrorMessage = computed(() => createError.value ?? editError.value ?? deleteError.value)

onMounted(async () => {
  await fetchSubjects()
})
</script>

<template>
  <div class="container py-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between flex-column flex-md-row align-items-start gap-3 mb-4">
          <div>
            <h1 class="h4 mb-1">Manage Subjects</h1>
            <p class="text-muted mb-0">Create, rename, or remove the subjects you track.</p>
          </div>
          <button class="btn btn-outline-secondary" @click="$router.push({ name: 'User' })">
            Back to overview
          </button>
        </div>
        <div class="row g-3 align-items-end mb-4">
          <div class="col-sm-8">
            <label class="form-label" for="newSubject">Subject name</label>
            <input
              id="newSubject"
              v-model="name"
              type="text"
              class="form-control"
              placeholder="e.g. Advanced Algorithms"
            />
          </div>
          <div class="col-sm-4">
            <button class="btn btn-primary w-100" @click="addSubject" :disabled="createLoading">
              <span v-if="createLoading" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
              Add subject
            </button>
          </div>
        </div>
        <div v-if="successMessage" class="alert alert-success">
          {{ successMessage }}
        </div>
        <div v-else-if="hasActionError" class="alert alert-danger">
          {{ actionErrorMessage }}
        </div>
        <div class="list-group">
          <div
            v-for="subject in subjects"
            :key="subject.id"
            class="list-group-item bg-transparent border rounded mb-3"
          >
            <div class="d-flex flex-column flex-md-row gap-3 align-items-start">
              <div class="flex-fill">
                <div v-if="editingId !== subject.id">
                  <h3 class="h6 mb-1">{{ subject.name }}</h3>
                  <p class="text-muted mb-0">{{ subject.my_tasks_count }} tasks · {{ subject.my_pending_tasks_count }} pending</p>
                </div>
                <div v-else class="d-flex gap-3 flex-wrap">
                  <input
                    v-model="editingName"
                    type="text"
                    class="form-control"
                    placeholder="New subject name"
                  />
                </div>
              </div>
              <div class="d-flex flex-wrap gap-2 align-items-center">
                <template v-if="editingId === subject.id">
                  <button class="btn btn-primary btn-sm" @click="saveEdit" :disabled="editLoading">
                    <span v-if="editLoading" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                    Save
                  </button>
                  <button class="btn btn-outline-secondary btn-sm" @click="cancelEditing">
                    Cancel
                  </button>
                </template>
                <template v-else>
                  <button class="btn btn-outline-secondary btn-sm" @click="startEditing(subject)">
                    Rename
                  </button>
                  <button class="btn btn-outline-danger btn-sm" @click="removeSubject(subject)" :disabled="deleteLoading">
                    Delete
                  </button>
                </template>
              </div>
            </div>
          </div>
          <div v-if="!subjects.length" class="text-muted fst-italic">No subjects yet. Add one to get started.</div>
        </div>
      </div>
    </div>
  </div>
</template>

