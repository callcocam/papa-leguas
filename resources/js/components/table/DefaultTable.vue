<!--
 * DefaultTable - Componente table padrão
 * 
 * Renderiza dados da tabela de forma simples
 * Faz a leitura do backend internamente
 -->
<template>
  <div class="space-y-2">
    <!-- Loading -->
    <div v-if="state.loading" class="flex items-center justify-center py-12">
      <div class="text-center">
        <div
          class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"
        ></div>
        <p class="text-sm text-muted-foreground mt-2">Carregando...</p>
      </div>
    </div>

    <!-- Error -->
    <div
      v-else-if="state.error"
      class="rounded-lg border border-destructive bg-destructive/10 p-4"
    >
      <p class="text-sm text-destructive">{{ state.error }}</p>
    </div>

    <!-- Dados -->
    <div v-else class="rounded-lg border bg-card p-6">
      <!-- Header com título -->
      <div v-if="state.title" class="mb-4">
        <h2 class="text-xl font-semibold">{{ state.title }}</h2>
        <p v-if="state.description" class="text-sm text-muted-foreground mt-1">
          {{ state.description }}
        </p>
      </div>

      <!-- Filtros -->
      <TableFilters
        v-if="state.filters && state.filters.length > 0"
        :filters="state.filters"
        :is-loading="state.loading"
        @apply="handleApplyFilters"
        @clear="handleClearFilters"
      />

      <!-- Estatísticas -->
      <div
        v-if="state.meta"
        class="flex items-center gap-4 text-sm text-muted-foreground mb-4"
      >
        <span>Total: {{ state.meta.total }}</span>
        <span>Página {{ state.meta.current_page }} de {{ state.meta.last_page }}</span>
        <span>Por página: {{ state.meta.per_page }}</span>
      </div>

      <!-- Colunas -->
      <div v-if="state.columns && state.columns.length > 0" class="mb-4">
        <h3 class="text-sm font-medium mb-2">Colunas:</h3>
        <div class="flex gap-2 flex-wrap">
          <span
            v-for="column in state.columns"
            :key="column.name"
            class="px-2 py-1 bg-secondary rounded text-xs"
          >
            {{ column.label }}
          </span>
        </div>
      </div>

      <!-- Dados -->
      <div v-if="state.data && state.data.length > 0" class="space-y-2">
        <div
          v-for="record in state.data"
          :key="record.id"
          class="p-4 rounded border hover:bg-accent"
        >
          <!-- Campos do registro -->
          <div class="grid grid-cols-2 gap-2 text-sm mb-2">
            <div v-for="column in state.columns" :key="column.name">
              <span class="font-medium">{{ column.label }}:</span>
              <span class="ml-2">{{ record[column.name] || "-" }}</span>
            </div>
          </div>

          <!-- Actions -->
          <div v-if="record.actions" class="flex gap-2 mt-2 pt-2 border-t">
            <span class="text-xs text-muted-foreground mr-2">Ações:</span>
            <ActionRenderer
              v-for="action in visibleActions(record.actions)"
              :key="action.name"
              :action="action"
              @click="handleActionClick(action)"
            />
          </div>
        </div>
      </div>

      <!-- Sem dados -->
      <div v-else-if="!state.loading" class="text-center py-8 text-muted-foreground">
        <p>Nenhum registro encontrado</p>
      </div>

      <!-- Paginação -->
      <TablePagination
        v-if="state.meta && state.data.length > 0"
        :meta="state.meta"
        @page-change="handlePageChange"
        @per-page-change="handlePerPageChange"
      />

      <!-- Info -->
      <div class="mt-4 p-3 bg-muted rounded text-sm">
        <p class="text-muted-foreground">
          💡 Este é o componente table padrão. Você pode criar seu próprio componente e
          registrá-lo no TableRegistry para customizar a renderização.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, watch, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useTable } from "../../composables/useTable";
import TablePagination from "./TablePagination.vue";
import TableFilters from "../filters/TableFilters.vue";
import type { TableQueryParams, TableAction } from "../../types/table";
import ActionRenderer from "../actions/ActionRenderer.vue";

const route = useRoute();

const props = defineProps<{
  resource?: string;
  endpoint?: string;
  initialParams?: TableQueryParams;
}>();
const emit = defineEmits<{
  (e: "header-action-click", action: TableAction): void;
  (e: "row-action-click", action: TableAction): void;
  (e: "state-updated", state: any): void;
}>();

// Obtém informações do route.meta (prioridade) ou props (fallback)
const resourceName = computed(() => {
  return props.resource || (route.meta?.resource as string) || "unknown";
});

const endpointUrl = computed(() => {
  // Tenta: props.endpoint -> route.meta.endpoint -> constrói endpoint
  if (props.endpoint) return props.endpoint;
  if (route.meta?.endpoint) return route.meta.endpoint as string;

  // Fallback: constrói endpoint baseado no resource padrão
  return `/api/${resourceName.value}`;
});

// Filtra apenas ações visíveis
const visibleActions = (actions: any) => {
  if (!actions) return [];

  // Se é um objeto, converte para array
  const actionsArray = Array.isArray(actions)
    ? actions
    : Object.values(actions);

  return actionsArray.filter((action: any) => action.visible !== false);
};
// // Debug: mostra de onde vieram os dados
// console.log('DefaultTable - Source:', {
//   fromRouteMeta: {
//     resource: route.meta?.resource,
//     endpoint: route.meta?.endpoint,
//     controller: route.meta?.controller,
//     modelName: route.meta?.modelName,
//     context: route.meta?.context
//   },
//   fromProps: {
//     resource: props.resource,
//     endpoint: props.endpoint
//   },
//   resolved: {
//     resource: resourceName.value,
//     endpoint: endpointUrl.value
//   }
// })

// Faz a leitura do backend (SEM autoLoad para evitar duplicação)
const { state, load } = useTable({
  resource: resourceName.value,
  endpoint: endpointUrl.value,
  initialParams: props.initialParams,
  autoLoad: false, // Desativado - vamos gerenciar manualmente
});

// Carrega inicial
onMounted(() => {
  const page = Number(route.query.page) || 1
  const per_page = Number(route.query.per_page) || 15
  
  // Pega todos os outros parâmetros da query (filtros, busca, etc)
  const { page: _, per_page: __, ...filters } = route.query
  
  load({ page, per_page, ...filters })
})

// Watch na rota para reagir às mudanças de paginação e filtros
watch(
  () => route.query,
  (newQuery) => {
    const page = Number(newQuery.page) || 1
    const per_page = Number(newQuery.per_page) || 15
    
    // Pega todos os outros parâmetros da query (filtros, busca, etc)
    const { page: _, per_page: __, ...filters } = newQuery
    
    load({ page, per_page, ...filters })
  }
)

// Watch no state para emitir quando mudar (para o pai acessar headerActions)
watch(
  () => state.value,
  (newState) => {
    emit('state-updated', newState)
  },
  { deep: true, immediate: true }
)

// Handler de ação de linha
const handleActionClick = (action: TableAction) => {
  emit("row-action-click", action);
};

/**
 * Muda para página específica
 * NÃO precisa chamar load() - o watch na rota vai fazer isso
 */
const handlePageChange = async (page: number) => {
  // Nada - o router.push no TablePagination + watch aqui fazem o trabalho
}

/**
 * Muda quantidade de registros por página
 * NÃO precisa chamar load() - o watch na rota vai fazer isso
 */
const handlePerPageChange = async (perPage: number) => {
  // Nada - o router.push no TablePagination + watch aqui fazem o trabalho
}

/**
 * Aplica filtros
 * Filtros já foram aplicados na URL pelo TableFilters, o watch vai reagir
 */
const handleApplyFilters = (filters: Record<string, any>) => {
  console.log('Filters applied:', filters)
  // O watch na rota vai detectar e carregar automaticamente
}

/**
 * Limpa filtros
 * Filtros já foram removidos da URL pelo TableFilters, o watch vai reagir
 */
const handleClearFilters = () => {
  console.log('Filters cleared')
  // O watch na rota vai detectar e carregar automaticamente
}
</script>
