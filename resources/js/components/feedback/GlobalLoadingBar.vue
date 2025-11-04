<template>
  <Transition
    enter-active-class="transition-all duration-300"
    leave-active-class="transition-all duration-300"
    enter-from-class="opacity-0"
    leave-to-class="opacity-0"
  >
    <div
      v-if="isLoading"
      class="fixed top-0 left-0 right-0 z-50"
    >
      <!-- Barra de progresso -->
      <div class="h-1 bg-primary/20 w-full overflow-hidden">
        <div
          class="h-full bg-primary transition-all duration-300 ease-out"
          :style="{
            width: progressWidth,
            transition: currentProgress !== undefined ? 'width 0.3s ease-out' : 'none'
          }"
          :class="{
            'animate-loading-bar': currentProgress === undefined
          }"
        />
      </div>

      <!-- Mensagem de loading (opcional) -->
      <Transition
        enter-active-class="transition-all duration-200"
        leave-active-class="transition-all duration-200"
        enter-from-class="opacity-0 -translate-y-2"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div
          v-if="currentMessage"
          class="absolute top-2 right-4 bg-background border rounded-md shadow-lg px-3 py-2 text-sm text-muted-foreground flex items-center gap-2"
        >
          <div class="h-4 w-4 border-2 border-primary border-t-transparent rounded-full animate-spin" />
          {{ currentMessage }}
        </div>
      </Transition>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useGlobalLoading } from '../../composables/useGlobalLoading'

const loading = useGlobalLoading()

const isLoading = computed(() => loading.isLoading.value)
const currentProgress = computed(() => loading.currentProgress.value)
const currentMessage = computed(() => {
  const tasks = loading.loadingTasks.value
  return tasks.length > 0 ? tasks[tasks.length - 1].message : undefined
})

const progressWidth = computed(() => {
  if (currentProgress.value !== undefined) {
    return `${currentProgress.value}%`
  }
  return '100%'
})
</script>

<style scoped>
@keyframes loading-bar {
  0% {
    transform: translateX(-100%);
  }
  50% {
    transform: translateX(0%);
  }
  100% {
    transform: translateX(100%);
  }
}

.animate-loading-bar {
  animation: loading-bar 1.5s ease-in-out infinite;
}
</style>
