import { ref, computed } from 'vue'

/**
 * Gera um UUID v4 compatível com todos os navegadores
 */
const generateUUID = (): string => {
  if (typeof crypto !== 'undefined' && crypto.randomUUID) {
    return crypto.randomUUID()
  }

  // Fallback para navegadores antigos
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
    const r = Math.random() * 16 | 0
    const v = c === 'x' ? r : (r & 0x3 | 0x8)
    return v.toString(16)
  })
}

interface LoadingTask {
  id: string
  message?: string
  progress?: number
}

// Estado global de loading
const loadingTasks = ref<LoadingTask[]>([])
const isLoading = computed(() => loadingTasks.value.length > 0)
const currentProgress = computed(() => {
  if (loadingTasks.value.length === 0) return 0

  const tasksWithProgress = loadingTasks.value.filter(t => t.progress !== undefined)
  if (tasksWithProgress.length === 0) return undefined

  const totalProgress = tasksWithProgress.reduce((sum, task) => sum + (task.progress || 0), 0)
  return totalProgress / tasksWithProgress.length
})

export function useGlobalLoading() {

  /**
   * Inicia uma tarefa de loading
   */
  const start = (message?: string, id?: string): string => {
    const taskId = id || generateUUID()

    loadingTasks.value.push({
      id: taskId,
      message,
      progress: undefined
    })

    return taskId
  }

  /**
   * Atualiza o progresso de uma tarefa
   */
  const updateProgress = (id: string, progress: number, message?: string) => {
    const task = loadingTasks.value.find(t => t.id === id)
    if (task) {
      task.progress = Math.min(100, Math.max(0, progress))
      if (message) {
        task.message = message
      }
    }
  }

  /**
   * Finaliza uma tarefa de loading
   */
  const finish = (id: string) => {
    const index = loadingTasks.value.findIndex(t => t.id === id)
    if (index !== -1) {
      loadingTasks.value.splice(index, 1)
    }
  }

  /**
   * Finaliza todas as tarefas de loading
   */
  const finishAll = () => {
    loadingTasks.value = []
  }

  /**
   * Wrapper para executar uma ação com loading automático
   */
  const withLoading = async <T>(
    fn: (updateProgress: (progress: number, message?: string) => void) => Promise<T>,
    message?: string
  ): Promise<T> => {
    const taskId = start(message)

    try {
      const result = await fn((progress: number, msg?: string) => {
        updateProgress(taskId, progress, msg)
      })
      return result
    } finally {
      finish(taskId)
    }
  }

  return {
    // Estado
    isLoading,
    loadingTasks: computed(() => loadingTasks.value),
    currentProgress,

    // Métodos
    start,
    updateProgress,
    finish,
    finishAll,
    withLoading
  }
}
