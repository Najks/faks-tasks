<script setup lang="ts">
import { ref } from 'vue'

defineProps<{ msg: string }>()

const count = ref(0)
</script>

<template>
  <div class="container py-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h1 class="card-title display-6">{{ msg }}</h1>
        <p class="card-text">
          <code>components/HelloWorld.vue</code> is your playground for seeing Hot Module Replacement in action.
        </p>
        <button type="button" class="btn btn-outline-primary" @click="count++">
          count is <span class="fw-semibold">{{ count }}</span>
        </button>
      </div>
    </div>

    <div class="mt-4 text-secondary">
      <p>
        Visit the
        <a href="https://vuejs.org/guide/quick-start.html#local" target="_blank" class="text-decoration-none">
          create-vue guide
        </a>
        to learn how the starter works.
      </p>
      <p>
        Get more tooling hints in the
        <a href="https://vuejs.org/guide/scaling-up/tooling.html#ide-support" target="_blank" class="text-decoration-none">
          Vue Scaling Up Guide
        </a>.
      </p>
      <p class="small text-muted">
        Click on the Vite and Vue logos in the app to explore more resources.
      </p>
    </div>
  </div>
</template>
