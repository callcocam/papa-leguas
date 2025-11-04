<!--
 * ActionButton - Componente de botão de ação padrão
 *
 * Renderiza um botão com ícone e label
 * Suporta diferentes variantes e cores
 -->
<template>
  <Button
    :variant="variant"
    :size="size"
    :class="className"
    @click="handleClick"
  >
    <component v-if="iconComponent" :is="iconComponent" :class="iconClasses" />
    <span>{{ action.label }}</span>
  </Button>
</template>

<script setup lang="ts">
import { computed, h } from 'vue'
import { Button } from '@/components/ui/button'
import * as LucideIcons from 'lucide-vue-next'
import type { TableAction } from '../../../types/table'

interface Props {
  action: TableAction
  size?: 'default' | 'sm' | 'lg' | 'icon'
  className?: string
}

const props = withDefaults(defineProps<Props>(), {
  size: 'default'
})

const emit = defineEmits<{
  (e: 'click'): void
}>()

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

// Classes do ícone
const iconClasses = computed(() => {
  return props.size === 'sm' ? 'h-3 w-3 mr-1.5' : 'h-4 w-4 mr-2'
})

// Componente do ícone dinâmico
const iconComponent = computed(() => {
  if (!props.action.icon) return null

  // Tenta obter o ícone do Lucide
  const IconComponent = (LucideIcons as any)[props.action.icon]

  if (!IconComponent) {
    console.warn(`Icon "${props.action.icon}" not found in lucide-vue-next`)
    return null
  }

  return h(IconComponent)
})

// Handler de clique
const handleClick = () => {
  emit('click')
}
</script>
