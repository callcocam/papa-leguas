<template>
  <div>
    <!-- Breadcrumbs com Header Actions -->
    <div
      v-if="showBreadcrumbs && breadcrumbItems.length > 0"
      class="border-b bg-background"
    >
      <div class="w-full flex items-center pb-4">
        <BreadcrumbRenderer :items="breadcrumbItems" :config="breadcrumbConfig">
          <!-- Header Actions renderizadas ao lado dos breadcrumbs -->
          <HeaderActions
            v-if="tableState?.headerActions && tableState.headerActions.length > 0"
            :actions="tableState.headerActions"
            @action-click="handleHeaderAction"
          />
        </BreadcrumbRenderer>
      </div>
    </div>

    <!-- Conteúdo Principal -->
    <main :class="containerClasses">
      <!-- Slot para conteúdo customizado -->
      <slot
        name="table"
        :resource="resourceName"
        :endpoint="endpointUrl"
        :initial-params="initialParams"
      >
        <!-- TableRenderer - A table configurada faz a leitura -->
        <TableRenderer
          :resource="resourceName"
          :endpoint="endpointUrl"
          :component="tableComponent"
          :initial-params="initialParams"
          @state-updated="handleTableStateUpdate"
        />
        <router-view />
      </slot>
    </main>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import BreadcrumbRenderer from '../../components/breadcrumbs/BreadcrumbRenderer.vue'
import TableRenderer from '../../components/table/TableRenderer.vue'
import HeaderActions from '../../components/table/HeaderActions.vue'
import { useListLayout, type ListLayoutConfig } from '../../composables/useListLayout'
import { useBreadcrumbs } from '../../composables/useBreadcrumbs'
import type { BreadcrumbItemData, BreadcrumbConfigData } from '../../components/breadcrumbs/BreadcrumbRenderer.vue'
import type { TableQueryParams } from '../../types/table'

const route = useRoute()

// Estado da tabela (recebido do TableRenderer)
const tableState = ref<any>(null)

interface Props {
    // Resource name (opcional - usa route.meta se não fornecido)
    resource?: string

    // Endpoint customizado (opcional - usa route.meta se não fornecido)
    endpoint?: string

    // Componente de table customizado (opcional)
    tableComponent?: string

    // Parâmetros iniciais de query
    initialParams?: TableQueryParams

    // Configuração de layout
    layoutConfig?: ListLayoutConfig

    // Configuração de breadcrumbs customizados (opcional)
    customBreadcrumbItems?: BreadcrumbItemData[]
    customBreadcrumbConfig?: BreadcrumbConfigData

    // Opções de breadcrumb
    showBreadcrumbs?: boolean
    showHome?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    layoutConfig: () => ({
        fullWidth: false,
        gridColumns: '1',
        gap: '6'
    }),
    showBreadcrumbs: true,
    showHome: true,
    initialParams: () => ({})
})

// Resolve resource e endpoint do route.meta ou props
const resourceName = computed(() => {
    return props.resource || route.meta.resource as string
})

const endpointUrl = computed(() => {
    return props.endpoint || route.meta.endpoint as string
})

// Breadcrumbs automáticos ou customizados
const { breadcrumbItems: autoBreadcrumbs, breadcrumbConfig: autoConfig } = useBreadcrumbs({
    showHome: props.showHome,
    customItems: props.customBreadcrumbItems
})

const breadcrumbItems = computed(() => {
    return props.customBreadcrumbItems || autoBreadcrumbs.value
})

const breadcrumbConfig = computed(() => {
    return props.customBreadcrumbConfig || autoConfig.value
})

// Configuração de layout
const {
    containerClasses
} = useListLayout(props.layoutConfig)

// Watch para debugar tableState
watch(tableState, (newVal) => {
    console.log('tableState changed:', newVal)
    console.log('tableState.headerActions:', newVal?.headerActions)
}, { deep: true })

// Handlers
const handleTableStateUpdate = (state: any) => {
    console.log('Table state updated:', state)
    console.log('Header actions:', state?.headerActions)
    console.log('Header actions type:', typeof state?.headerActions)
    console.log('Header actions length:', state?.headerActions?.length)
    console.log('Is array?:', Array.isArray(state?.headerActions))
    tableState.value = state
}

const handleHeaderAction = (action: any) => {
    // TODO: Implementar lógica de ação
    console.log('Header action clicked:', action)
}
</script>
