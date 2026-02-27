// src/composables/useSubjects.ts
import { ref, computed } from 'vue'
import { useApiRequest } from './useApiRequest'

export interface Task {
    id: number
    title: string
    subject_id: number
    status?: {
        id: number
        status: string
    }
    description?: string | null
    due_date?: string
    grade?: number | null
    subjectName?: string
    subject?: {
        id: number
        name: string
    }
}

export interface Subject {
    id: number
    name: string
    tasks: Task[]
    my_tasks_count: number
    my_pending_tasks_count: number
}

export interface TaskStatus {
    id: number
    status: string
}

export const useSubjects = () => {
    const subjects = ref<Subject[]>([])
    const { loading, error, get, patch } = useApiRequest()
    const statuses = ref<TaskStatus[]>([])

    const completedStatusId = computed(() =>
        statuses.value.find(status => status.status === 'completed')?.id
    )

    const pendingStatusId = computed(() => {
        if (completedStatusId.value) {
            const fallback = statuses.value.find(status => status.status !== 'completed')
            return fallback?.id ?? completedStatusId.value
        }
        return statuses.value[0]?.id
    })

    const fetchSubjects = async () => {
        statuses.value = await get<TaskStatus[]>('/status', undefined, 'Failed to load task statuses')
        subjects.value = await get<Subject[]>('/subjects/mine', undefined, 'Failed to load subjects')
    }

    const updateTaskStatus = async (taskId: number, completed: boolean) => {
        const statusId = completed ? completedStatusId.value : pendingStatusId.value

        if (!statusId) {
            throw new Error('Unable to determine task status IDs')
        }

        return patch<Task>(
            `/tasks/${taskId}/status`,
            { status_id: statusId },
            undefined,
            'Failed to update task'
        )
    }

    const tasks = computed(() =>
        subjects.value.flatMap(subject =>
            subject.tasks.map(task => ({
                ...task,
                subjectName: subject.name
            }))
        )
    )

    return {
        subjects,
        loading,
        error,
        fetchSubjects,
        updateTaskStatus,
        tasks
    }
}
