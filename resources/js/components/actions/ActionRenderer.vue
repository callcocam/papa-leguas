<!--
 * ActionRenderer - Renderiza ações dinamicamente
 *
 * Usa o ActionRegistry para obter o componente correto
 * Similar ao InfoRenderer.vue
 -->
<template>
  <component :is="component" :action="action" @click="handleClick" />
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import ActionRegistry from '../../utils/ActionRegistry'
import type { TableAction } from '../../types/table'

const props = defineProps<{
  action: TableAction
}>()

const emit = defineEmits<{
  (e: 'click', action: TableAction): void
}>()

/**
 * Obtém o componente a ser renderizado do ActionRegistry
 *
 * Lógica de seleção automática:
 * 1. Se tem 'confirm' e 'to' (rota) → 'action-link-confirm'
 * 2. Se tem 'confirm' → 'action-confirm'
 * 3. Se tem 'to' (rota) → 'action-link'
 * 4. Se tem 'target=modal' → 'action-modal'
 * 5. Caso contrário, usa 'component' especificado
 * 6. Fallback para 'action-button'
 */
const component = computed(() => {
  let componentName = props.action.component

  // Auto-detecção baseada nas propriedades
  if (!componentName) {
    if (props.action.confirm && props.action.to) {
      // Se tem confirmação E rota, usa action-link-confirm
      componentName = 'action-link-confirm'
    } else if (props.action.confirm) {
      // Se tem confirmação (API), usa action-confirm
      componentName = 'action-confirm'
    } else if (props.action.to) {
      // Se tem rota (sem confirmação), usa action-link
      componentName = 'action-link'
    } else if (props.action.target === 'modal') {
      // Se target é modal, usa action-modal
      componentName = 'action-modal'
    } else {
      // Default
      componentName = 'action-button'
    }
  }

  // Tenta obter do registry
  const registeredComponent = ActionRegistry.get(componentName)

  if (registeredComponent) {
    return registeredComponent
  }

  // Fallback para componente padrão
  const fallback = ActionRegistry.get('action-button')

  if (!fallback) {
    console.warn(`Component '${componentName}' not found in registry and no fallback available`)
  }

  return fallback
})

/**
 * Emite evento de click
 */
const handleClick = () => {
  emit('click', props.action)
}
</script>
