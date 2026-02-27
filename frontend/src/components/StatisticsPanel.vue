<script lang="ts">
import { computed, defineComponent } from 'vue'
import type { PropType } from 'vue'

type StatsPayload = {
  subjectCount: number
  totalTasks: number
  completedTasks: number
  pendingTasks: number
  progress: number
  averageGrade: string
}

export default defineComponent({
  name: 'StatisticsPanel',
  props: {
    stats: {
      type: Object as PropType<StatsPayload>,
      required: true
    }
  },
  setup(props) {
    const cards = computed(() => [
      {
        label: 'Subjects',
        value: props.stats.subjectCount,
        description: 'Subjects you are active in'
      },
      {
        label: 'Tasks',
        value: props.stats.totalTasks,
        description: 'All tasks assigned to you'
      },
      {
        label: 'Completed',
        value: props.stats.completedTasks,
        description: 'Finished so far'
      },
      {
        label: 'Pending',
        value: props.stats.pendingTasks,
        description: 'Still on your plate'
      }
    ])

    return {
      cards
    }
  }
})
</script>

<template>
  <div class="card shadow-sm statistics-card">
    <div class="card-body">
      <div class="row g-3">
        <div v-for="card in cards" :key="card.label" class="col-12 col-sm-6 col-lg-3">
          <div class="stat-card p-3">
            <div class="stat-card-value">{{ card.value }}</div>
            <div class="stat-card-label">{{ card.label }}</div>
            <p class="text-muted small mb-0">{{ card.description }}</p>
          </div>
        </div>
      </div>
      <div class="mt-4">
        <div class="d-flex justify-content-between align-items-end">
          <div>
            <p class="text-muted small mb-1">Completion rate</p>
            <h4 class="mb-0">{{ stats.progress }}%</h4>
          </div>
          <div class="text-end">
            <p class="text-muted small mb-1">Average grade</p>
            <h5 class="mb-0">{{ stats.averageGrade }}</h5>
          </div>
        </div>
        <div class="progress mt-2">
          <div
            class="progress-bar"
            role="progressbar"
            :style="{ width: `${stats.progress}%` }"
            :aria-valuenow="stats.progress"
            aria-valuemin="0"
            aria-valuemax="100"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.statistics-card {
  border-radius: 18px;
}
.stat-card {
  border: 1px solid rgba(0, 0, 0, 0.05);
  border-radius: 12px;
  height: 100%;
  background-color: #f8f9fa;
}
.stat-card-value {
  font-size: 1.75rem;
  font-weight: 600;
}
.stat-card-label {
  font-size: 0.95rem;
}
</style>

