/**
 * useBreadcrumbs - Composable para gerar breadcrumbs automaticamente
 *
 * Gera breadcrumbs baseado na hierarquia de rotas e meta dados
 */

import { computed } from 'vue'
import { useRoute, type RouteLocationNormalizedLoaded } from 'vue-router'
import { Home } from 'lucide-vue-next'
import type { BreadcrumbItemData, BreadcrumbConfigData } from '../components/breadcrumbs/BreadcrumbRenderer.vue'

export interface BreadcrumbOptions {
  showHome?: boolean
  homeLabel?: string
  homeHref?: string
  customItems?: BreadcrumbItemData[]
}

/**
 * Gera breadcrumbs automaticamente baseado na rota atual
 */
export function useBreadcrumbs(options: BreadcrumbOptions = {}) {
  const route = useRoute()

  /**
   * Extrai breadcrumb items da rota e suas rotas pai
   */
  const breadcrumbItems = computed<BreadcrumbItemData[]>(() => {
    // Se tem custom items, usa eles
    if (options.customItems && options.customItems.length > 0) {
      return options.customItems
    }

    const items: BreadcrumbItemData[] = []

    // Gera items baseado na rota
    const routeItems = generateBreadcrumbsFromRoute(route)
    items.push(...routeItems)

    return items
  })

  /**
   * Configuração padrão do breadcrumb
   */
  const breadcrumbConfig = computed<BreadcrumbConfigData>(() => ({
    component: 'breadcrumb-default',
    separator: '/',
    maxItems: 5,
    showHome: options.showHome !== false,
    homeLabel: options.homeLabel || 'Home',
    homeHref: options.homeHref || '/',
    homeIcon: Home
  }))

  return {
    breadcrumbItems,
    breadcrumbConfig
  }
}

/**
 * Gera breadcrumb items a partir de uma rota
 */
function generateBreadcrumbsFromRoute(route: RouteLocationNormalizedLoaded): BreadcrumbItemData[] {
  const items: BreadcrumbItemData[] = []
  const seenLabels = new Set<string>()

  // Pega todos os matches (rota atual + pais)
  const matches = route.matched

  matches.forEach((match, index) => {
    // Pula rotas sem nome ou sem meta.title
    if (!match.name || !match.meta?.title) {
      return
    }

    // Pula rotas que são apenas redirects (não têm component ou têm redirect)
    if (match.redirect || (!match.components && index !== matches.length - 1)) {
      return
    }

    const label = match.meta.title as string

    // Pula labels duplicados (evita "Users -> Users")
    if (seenLabels.has(label)) {
      return
    }
    seenLabels.add(label)

    const isLast = index === matches.length - 1
    const item: BreadcrumbItemData = {
      label,
      icon: match.meta.icon as string | undefined,
      active: isLast
    }

    // Adiciona href se não for o último item
    if (!isLast && match.name) {
      item.href = typeof match.name === 'string' ? `/${match.name.replace(/\./g, '/')}` : match.path
    }

    items.push(item)
  })

  // Se não gerou nenhum item da hierarquia, usa apenas o título da rota atual
  if (items.length === 0 && route.meta?.title) {
    items.push({
      label: route.meta.title as string,
      icon: route.meta.icon as string | undefined,
      active: true
    })
  }

  return items
}

/**
 * Hook para gerar breadcrumbs customizados manualmente
 */
export function createBreadcrumbs(items: BreadcrumbItemData[], config?: BreadcrumbConfigData) {
  return {
    breadcrumbItems: computed(() => items),
    breadcrumbConfig: computed(() => config || {})
  }
}
