<script setup lang="ts">
import NavBar from './components/NavBar.vue'
</script>

<template>
  <div class="bg-light min-vh-100">
    <NavBar />
    <main class="py-5">
      <RouterView />
    </main>
  </div>
</template>
