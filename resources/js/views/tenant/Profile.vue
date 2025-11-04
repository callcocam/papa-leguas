<template>
  <div class="profile-view max-w-7xl mx-auto p-6 space-y-6">
    <!-- Header Card com Avatar e Informações Principais -->
    <Card class="bg-gradient-to-r from-primary/5 to-primary/10 border-0 shadow-lg">
      <CardHeader>
        <div class="flex flex-col md:flex-row items-center gap-6">
          <!-- Avatar -->
          <div class="w-24 h-24 bg-gradient-to-br from-primary to-primary/70 rounded-full flex items-center justify-center text-white text-3xl font-bold shadow-lg">
            {{ userInitials }}
          </div>

          <!-- Informações Principais -->
          <div class="flex-1 text-center md:text-left space-y-2">
            <CardTitle class="text-3xl">{{ userName }}</CardTitle>
            <div v-if="userEmail" class="text-lg text-muted-foreground">
              <InfoReander :column="userEmail" />
            </div>
          </div>
        </div>
      </CardHeader>
    </Card>

    <!-- Grid de Cards dinâmicos -->
    <div :class="gridClass">
      <div
        v-for="item in cardItems"
        :key="item.id"
        :class="getColumnSpanClass(item)"
        :style="item.order ? `order: ${item.order}` : undefined"
      >
        <InfoReander :column="item" />
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import useAuth from '../../composables/useAuth'
import InfoReander from '../../components/infolist/InfoReander.vue'
import { Card, CardHeader, CardTitle } from '@/components/ui/card'

const { user } = useAuth()

// Todos os itens do usuário
const items = computed(() => {
  const data = user.value?.data
  // Se data é um objeto, converte para array de objetos com id
  if (data && typeof data === 'object' && !Array.isArray(data)) {
    return Object.entries(data).map(([key, value]: [string, any]) => ({
      id: key,
      label: value.label || key,
      text: value.text || value,
      icon: value.icon || 'Settings',
      tooltip: value.tooltip || '',
      type: value.type || 'text',
      component: value.component,
      columns: value.columns || {},
      title: value.title,
      description: value.description,
      gridCols: value.gridCols,
      collapsible: value.collapsible,
      defaultExpanded: value.defaultExpanded,
      color: value.color,
      value: value.value,
    }))
  }
  // Se já é array, retorna como está
  return Array.isArray(data) ? data : []
})

// Extrai nome e email para o header
const userName = computed(() => {
  const nameItem = items.value.find((item: any) => item.id === 'name')
  return nameItem?.text || 'Usuário'
})

const userEmail = computed(() => {
  return items.value.find((item: any) => item.id === 'email')
})

// Iniciais do usuário
const userInitials = computed(() => {
  return userName.value
    .split(' ')
    .map((name: string) => name.charAt(0))
    .slice(0, 2)
    .join('')
    .toUpperCase()
})

// Cards dinâmicos (exclui name e email que vão no header)
const cardItems = computed(() => {
  return items.value.filter((item: any) =>
    item.type === 'card'
  )
})

// Classes do grid principal (default: 2 colunas em lg)
const gridClass = computed(() => {
  const gap = user.value?.layout?.gap || '6'
  return `grid grid-cols-1 lg:grid-cols-2 gap-${gap}`
})

// Gera classes de column span para um item
const getColumnSpanClass = (item: any) => {
  const span = item.columnSpan || '1'
  const responsive = item.responsive?.span || {}

  const classes = []

  // Column span padrão
  if (span === 'full' || span === '2') {
    classes.push('lg:col-span-2')
  }

  // Responsive spans
  if (responsive.sm) classes.push(`sm:col-span-${responsive.sm}`)
  if (responsive.md) classes.push(`md:col-span-${responsive.md}`)
  if (responsive.lg) classes.push(`lg:col-span-${responsive.lg}`)
  if (responsive.xl) classes.push(`xl:col-span-${responsive.xl}`)

  return classes.join(' ')
}
</script>

<style scoped>
.profile-view {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
