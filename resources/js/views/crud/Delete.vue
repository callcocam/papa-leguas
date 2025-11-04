<!--
 * Delete View - Tela de Exclusão com Confirmação
 *
 * Abre automaticamente um modal de confirmação ao montar
 * Executa a exclusão via API e retorna para a lista
 -->
<template>
  <AlertDialog v-model:open="isOpen">
    <AlertDialogContent>
      <div class="flex flex-col items-center gap-4 py-4">
        <component :is="alertIcon" class="h-16 w-16 text-destructive" />

        <AlertDialogHeader class="text-center space-y-2">
          <AlertDialogTitle class="text-center">
            Confirmar Exclusão
          </AlertDialogTitle>
          <AlertDialogDescription class="text-center">
            <span v-if="resourceName">
              Tem certeza que deseja excluir este {{ resourceName }}?
            </span>
            <span v-else>
              Tem certeza que deseja excluir este registro?
            </span>
            <br />
            <strong class="text-destructive">Esta ação não pode ser desfeita.</strong>
          </AlertDialogDescription>
        </AlertDialogHeader>

        <!-- Campo de confirmação por digitação -->
        <div v-if="requiresTypedConfirmation" class="w-full px-6">
          <label class="block text-sm font-medium mb-2 text-center">
            Digite <strong>{{ typedConfirmationWord }}</strong> para confirmar:
          </label>
          <input
            v-model="typedWord"
            type="text"
            :placeholder="typedConfirmationWord"
            class="w-full px-3 py-2 border rounded-md text-center focus:outline-none focus:ring-2 focus:ring-primary"
            @keyup.enter="isTypedWordCorrect && !isDeleting && handleConfirmDelete()"
          />
          <p v-if="showTypedError" class="text-sm text-destructive mt-2 text-center">
            A palavra digitada não corresponde
          </p>
        </div>
      </div>

      <AlertDialogFooter class="flex justify-center gap-2 w-full items-center">
        <div class="flex w-full justify-center space-x-4">
          <AlertDialogCancel @click="handleCancel" :disabled="isDeleting">
            Cancelar
          </AlertDialogCancel>
          <AlertDialogAction
            class="bg-destructive   hover:bg-destructive/90 text-white"
            @click="handleConfirmDelete"
            :disabled="isDeleting || (requiresTypedConfirmation && !isTypedWordCorrect)"
          >
            {{ isDeleting ? 'Excluindo...' : 'Sim, Excluir' }}
          </AlertDialogAction>
        </div>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, h } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useNotifications } from '../../composables/useNotifications'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import * as LucideIcons from 'lucide-vue-next'
import { useAction } from '../../composables/useAction'

const route = useRoute()
const router = useRouter()
const notifications = useNotifications()
const actionComposable = useAction()  
// Estado
const isOpen = ref(false)
const isDeleting = ref(false)

// Estado para confirmação por digitação
const typedWord = ref('')
const showTypedError = ref(false)

// Obtém informações da rota
const resource = computed(() => route.meta.resource as string || '')
const resourceName = computed(() => route.meta.modelName as string || resource.value) 

// Configuração de confirmação por digitação (pode vir da rota ou ser configurado)
const requiresTypedConfirmation = computed(() => {
  // Verifica se a meta da rota especifica confirmação por digitação
  return route.meta.requiresTypedConfirmation === true
})

const typedConfirmationWord = computed(() => {
  // Palavra personalizada da rota ou padrão "EXCLUIR"
  return (route.meta.typedConfirmationWord as string) || 'EXCLUIR'
})

// Verifica se a palavra digitada está correta
const isTypedWordCorrect = computed(() => {
  if (!requiresTypedConfirmation.value) return true
  return typedWord.value.toUpperCase() === typedConfirmationWord.value.toUpperCase()
})

// Ícone de alerta
const alertIcon = computed(() => {
  const AlertTriangleIcon = (LucideIcons as any)['AlertTriangle']
  return h(AlertTriangleIcon)
})

// Gera a URL da API para exclusão
const deleteUrl = computed(() => { 
  return `/api${route.fullPath}`.replace('/delete', '')
})

// Lista de rotas para retornar
const listRouteName = computed(() => `${resource.value}.list`)

/**
 * Abre o modal automaticamente ao montar o componente
 */
onMounted(() => {
  // Pequeno delay para animação suave
  setTimeout(() => {
    isOpen.value = true
  }, 100)
})

/**
 * Cancela a exclusão e volta para a lista
 */
const handleCancel = () => {
  isOpen.value = false
  
  // Aguarda o modal fechar antes de navegar
  setTimeout(() => {
    router.push({ name: listRouteName.value })
  }, 200)
}

/**
 * Confirma e executa a exclusão
 */
const handleConfirmDelete = async () => {
  // Valida confirmação por digitação se necessário
  if (requiresTypedConfirmation.value && !isTypedWordCorrect.value) {
    showTypedError.value = true
    return
  }

  isDeleting.value = true

  try {
    const success = await actionComposable.execute(
      {
        url: deleteUrl.value,
        method: 'DELETE',
        successMessage: `${resourceName.value} excluído com sucesso!`,
      }
    ) 
    if (success) { 
      
      // Aguarda o modal fechar e navega para lista
      setTimeout(() => {
        router.push({ name: listRouteName.value })
      }, 200)
    } else {
      notifications.error('Ocorreu um erro ao tentar excluir o registro.')
    }
  } catch (error: any) {
    console.error('Erro inesperado:', error)
    isOpen.value = true // Garante que o modal permaneça aberto
    notifications.error(
      error?.message || 'Ocorreu um erro ao tentar excluir o registro.'
    )
  } finally {
    isDeleting.value = false
  }
}
</script>
