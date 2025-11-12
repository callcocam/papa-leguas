<!--
 * ActionModal - Componente de ação com modal para exibição de conteúdo
 *
 * Exibe um botão que, ao clicar, abre um modal com título, descrição e conteúdo HTML
 * Útil para visualização de informações detalhadas
 *
 * Usa Dialog da shadcn-vue para seguir o padrão do projeto
 -->
<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button
        :variant="variant"
        :size="size"
        @click="handleTriggerClick"
      >
        <component v-if="iconComponent" :is="iconComponent" class="h-4 w-4 mr-2" />
        <span>{{ action.label }}</span>
      </Button>
    </DialogTrigger>

    <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>
          {{ action.modalTitle || action.label }}
        </DialogTitle>
        <DialogDescription v-if="action.modalDescription">
          {{ action.modalDescription }}
        </DialogDescription>
      </DialogHeader>

      <!-- Slot para conteúdo customizado -->
      <slot name="content">
        <div v-if="action.modalContent" v-html="action.modalContent" class="prose dark:prose-invert max-w-none" />

        <!-- Conteúdo padrão se não houver modalContent -->
        <div v-else class="text-center py-12">
          <component
            v-if="iconComponent"
            :is="iconComponent"
            class="h-12 w-12 mx-auto text-muted-foreground mb-4"
          />
          <p class="text-muted-foreground">
            {{ action.label }}
          </p>
        </div>
      </slot>

      <!-- Footer -->
      <DialogFooter v-if="$slots.footer">
        <slot name="footer" />
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, h, watch } from 'vue'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import * as LucideIcons from 'lucide-vue-next'
import type { TableAction } from '../../../types/table'

interface Props {
  action: TableAction & {
    modalTitle?: string
    modalDescription?: string
    modalContent?: string
  }
  size?: 'default' | 'sm' | 'lg' | 'icon'
}

const props = withDefaults(defineProps<Props>(), {
  size: 'default'
})

const emit = defineEmits<{
  (e: 'click'): void
  (e: 'open'): void
  (e: 'close'): void
}>()

// Estado do modal
const isOpen = ref(false)

// Mapeia cor para variant do shadcn
const variant = computed(() => {
  const colorMap: Record<string, any> = {
    'green': 'default',
    'blue': 'default',
    'red': 'destructive',
    'yellow': 'warning',
    'gray': 'secondary',
    'default': 'default'
  }

  return colorMap[props.action.color || 'default'] || 'default'
})

// Componente do ícone dinâmico
const iconComponent = computed(() => {
  if (!props.action.icon) return null

  const IconComponent = (LucideIcons as any)[props.action.icon]

  if (!IconComponent) {
    console.warn(`Icon "${props.action.icon}" not found in lucide-vue-next`)
    return null
  }

  return h(IconComponent)
})

// Handler para click no trigger
const handleTriggerClick = () => {
  emit('click')
}

// Watch para emitir eventos quando o modal abre/fecha
watch(isOpen, (newValue) => {
  if (newValue) {
    emit('open')
  } else {
    emit('close')
  }
})

// Expõe métodos para controle externo
defineExpose({
  open: () => { isOpen.value = true },
  close: () => { isOpen.value = false },
  isOpen,
})
</script>