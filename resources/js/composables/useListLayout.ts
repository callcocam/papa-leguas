/**
 * useListLayout - Composable para gerenciar layout de lista
 * 
 * Integra com HasGridLayout.php do backend
 */

import { computed } from 'vue'

export interface GridConfig {
  gridColumns?: string
  columnSpan?: string
  order?: number
  gap?: string
  responsive?: {
    grid?: {
      sm?: string
      md?: string
      lg?: string
      xl?: string
    }
    span?: {
      sm?: string
      md?: string
      lg?: string
      xl?: string
    }
  }
}

export interface ListLayoutConfig extends GridConfig {
  fullWidth?: boolean
  maxWidth?: string
  padding?: string
}

/**
 * Gera classes Tailwind CSS baseadas na configuração de grid
 */
export function useListLayout(config: ListLayoutConfig = {}) {
  /**
   * Classes de container principal
   */
  const containerClasses = computed(() => {
    const classes: string[] = []

    // Full width ou max-width
    if (config.fullWidth) {
      classes.push('w-full')
    } else if (config.maxWidth) {
      classes.push(config.maxWidth)
    } else {
      classes.push('max-w-7xl mx-auto')
    }

    // Padding
    if (config.padding) {
      classes.push(config.padding)
    } else {
      classes.push('px-4 sm:px-6 lg:px-8 py-8')
    }

    return classes.join(' ')
  })

  /**
   * Classes de grid
   */
  const gridClasses = computed(() => {
    const classes: string[] = ['grid']

    // Grid columns base
    if (config.gridColumns) {
      classes.push(`grid-cols-${config.gridColumns}`)
    } else {
      classes.push('grid-cols-1')
    }

    // Gap
    if (config.gap) {
      classes.push(`gap-${config.gap}`)
    } else {
      classes.push('gap-6')
    }

    // Responsive grid columns
    if (config.responsive?.grid) {
      const { sm, md, lg, xl } = config.responsive.grid
      if (sm) classes.push(`sm:grid-cols-${sm}`)
      if (md) classes.push(`md:grid-cols-${md}`)
      if (lg) classes.push(`lg:grid-cols-${lg}`)
      if (xl) classes.push(`xl:grid-cols-${xl}`)
    }

    return classes.join(' ')
  })

  /**
   * Classes de column span
   */
  const columnSpanClasses = computed(() => {
    const classes: string[] = []

    // Column span base
    if (config.columnSpan) {
      if (config.columnSpan === 'full') {
        classes.push('col-span-full')
      } else {
        classes.push(`col-span-${config.columnSpan}`)
      }
    }

    // Responsive column span
    if (config.responsive?.span) {
      const { sm, md, lg, xl } = config.responsive.span
      if (sm) {
        classes.push(sm === 'full' ? 'sm:col-span-full' : `sm:col-span-${sm}`)
      }
      if (md) {
        classes.push(md === 'full' ? 'md:col-span-full' : `md:col-span-${md}`)
      }
      if (lg) {
        classes.push(lg === 'full' ? 'lg:col-span-full' : `lg:col-span-${lg}`)
      }
      if (xl) {
        classes.push(xl === 'full' ? 'xl:col-span-full' : `xl:col-span-${xl}`)
      }
    }

    return classes.join(' ')
  })

  /**
   * Classes de order
   */
  const orderClasses = computed(() => {
    if (config.order !== undefined) {
      return `order-${config.order}`
    }
    return ''
  })

  /**
   * Todas as classes combinadas para um item
   */
  const itemClasses = computed(() => {
    return [
      columnSpanClasses.value,
      orderClasses.value
    ].filter(Boolean).join(' ')
  })

  return {
    containerClasses,
    gridClasses,
    columnSpanClasses,
    orderClasses,
    itemClasses
  }
}

/**
 * Hook para processar configuração de grid do backend
 */
export function useBackendGridConfig(backendConfig: GridConfig): ListLayoutConfig {
  return {
    gridColumns: backendConfig.gridColumns,
    columnSpan: backendConfig.columnSpan,
    order: backendConfig.order,
    gap: backendConfig.gap,
    responsive: backendConfig.responsive,
    fullWidth: backendConfig.columnSpan === 'full'
  }
}

