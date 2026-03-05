export const TASK_STATUS = {
  NOT_STARTED: 'not_started',
  STARTED: 'started',
  TURNED_IN: 'turned_in',
  COMPLETED: 'completed',
} as const

export type TaskStatusKey = keyof typeof TASK_STATUS
export type TaskStatusValue = typeof TASK_STATUS[TaskStatusKey]

