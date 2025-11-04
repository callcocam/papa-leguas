<!--
 * TablePagination - Componente de Paginação
 *
 * Renderiza paginação com suporte a dark mode
 * Atualiza a URL com query params
 * Integra com useTable composable
 -->
<template>
  <div v-if="meta.last_page > 1" class="flex items-center justify-between px-2 py-4 border-t">
    <!-- Informações de registros -->
    <div class="flex-1 text-sm text-muted-foreground">
      Mostrando <span class="font-medium">{{ meta.from }}</span> até
      <span class="font-medium">{{ meta.to }}</span> de
      <span class="font-medium">{{ meta.total }}</span> registros
    </div>

    <!-- Paginação -->
    <Pagination
      :total="meta.total"
      :items-per-page="meta.per_page"
      :page="meta.current_page"
      @update:page="changePage"
    >
      <PaginationContent class="flex items-center gap-1">
        <!-- First Button -->
        <PaginationFirst 
          :disabled="meta.current_page === 1"
        />

        <!-- Previous Button -->
        <PaginationPrevious 
          :disabled="meta.current_page === 1"
        />

        <!-- Page Numbers -->
        <template v-for="pageNum in visiblePages" :key="pageNum">
          <PaginationEllipsis v-if="pageNum === '...'" />
          <PaginationItem 
            v-else
            :value="pageNum as number"
            :is-active="pageNum === meta.current_page"
          >
            {{ pageNum }}
          </PaginationItem>
        </template>

        <!-- Next Button -->
        <PaginationNext 
          :disabled="!meta.has_more_pages"
        />

        <!-- Last Button -->
        <PaginationLast 
          :disabled="!meta.has_more_pages"
        />
      </PaginationContent>
    </Pagination>

    <!-- Per Page Selector -->
    <div class="flex items-center space-x-2">
      <p class="text-sm font-medium text-muted-foreground">Por página</p>
      <Select :model-value="String(meta.per_page)" @update:model-value="changePerPage">
        <SelectTrigger class="h-8 w-[70px]">
          <SelectValue :placeholder="String(meta.per_page)" />
        </SelectTrigger>
        <SelectContent side="top">
          <SelectItem v-for="size in pageSizes" :key="size" :value="String(size)">
            {{ size }}
          </SelectItem>
        </SelectContent>
      </Select>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationFirst,
  PaginationItem,
  PaginationLast,
  PaginationNext,
  PaginationPrevious,
} from '@/components/ui/pagination'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

interface TableMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
  path: string
  has_more_pages: boolean
}

interface Props {
  meta: TableMeta
  onPageChange?: (page: number) => void
  onPerPageChange?: (perPage: number) => void
}

const props = defineProps<Props>()

const router = useRouter()
const route = useRoute()

// Opções de registros por página
const pageSizes = [10, 15, 25, 50, 100]

/**
 * Calcula páginas visíveis com ellipsis
 */
const visiblePages = computed(() => {
  const current = props.meta.current_page
  const last = props.meta.last_page
  const delta = 2 // Páginas antes e depois da atual
  const pages: (number | string)[] = []

  // Se tem poucas páginas, mostra todas
  if (last <= 7) {
    for (let i = 1; i <= last; i++) {
      pages.push(i)
    }
    return pages
  }

  // Sempre mostra primeira página
  pages.push(1)

  // Calcula range ao redor da página atual
  const start = Math.max(2, current - delta)
  const end = Math.min(last - 1, current + delta)

  // Adiciona ellipsis do início se necessário
  if (start > 2) {
    pages.push('...')
  }

  // Adiciona páginas do range
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  // Adiciona ellipsis do fim se necessário
  if (end < last - 1) {
    pages.push('...')
  }

  // Sempre mostra última página
  pages.push(last)

  return pages
})

/**
 * Muda para página específica
 */
const changePage = (page: number) => {
  if (page < 1 || page > props.meta.last_page) return
  if (page === props.meta.current_page) return

  // Atualiza URL primeiro
  router.push({
    query: {
      ...route.query,
      page: page
    }
  })

  // Depois chama callback se existir
  if (props.onPageChange) {
    props.onPageChange(page)
  }
}

/**
 * Muda quantidade de registros por página
 */
const changePerPage = (perPage: string) => {
  const perPageNumber = parseInt(perPage)
  
  // Atualiza URL primeiro (volta para página 1)
  router.push({
    query: {
      ...route.query,
      page: 1,
      per_page: perPageNumber
    }
  })

  // Depois chama callback se existir
  if (props.onPerPageChange) {
    props.onPerPageChange(perPageNumber)
  }
}
</script>
