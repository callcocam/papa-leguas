# Sistema de Breadcrumbs Dinâmico

Sistema de breadcrumbs configurável e personalizável usando o padrão de Registry.

## 📋 Estrutura

```
packages/callcocam/papa-leguas/resources/js/
├── utils/
│   └── BreadcrumbRegistry.ts          # Registry para componentes breadcrumb
├── components/
│   └── breadcrumbs/
│       ├── BreadcrumbRenderer.vue     # Renderizador dinâmico
│       └── DefaultBreadcrumb.vue      # Componente padrão (shadcn-vue)
├── composables/
│   └── useListLayout.ts               # Composable para grid layout
└── views/
    └── crud/
        └── List.vue                    # Página de lista com breadcrumbs
```

## 🚀 Uso Básico

### No Vue Component

```vue
<template>
  <List 
    :breadcrumb-items="breadcrumbs"
    :breadcrumb-config="{ showHome: true }"
    :layout-config="layoutConfig"
  >
    <template #content>
      <!-- Seu conteúdo aqui -->
    </template>
  </List>
</template>

<script setup lang="ts">
import List from '@/views/crud/List.vue'

// Configurar breadcrumbs
const breadcrumbs = [
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Usuários', href: '/users' },
  { label: 'Editar', active: true }
]

// Configurar layout
const layoutConfig = {
  fullWidth: false,        // true para tela cheia
  gridColumns: '1',        // Número de colunas base
  gap: '6',                // Espaçamento entre items
  responsive: {
    grid: {
      md: '2',             // 2 colunas em md
      lg: '3'              // 3 colunas em lg
    }
  }
}
</script>
```

## 🎨 Personalização

### 1. Criar Componente Breadcrumb Customizado

```vue
<!-- resources/js/components/MyCustomBreadcrumb.vue -->
<template>
  <nav class="flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
      <li v-for="(item, index) in items" :key="index">
        <a v-if="item.href" :href="item.href" class="text-blue-600 hover:text-blue-800">
          {{ item.label }}
        </a>
        <span v-else class="text-gray-500">{{ item.label }}</span>
      </li>
    </ol>
  </nav>
</template>

<script setup lang="ts">
import type { BreadcrumbItemData, BreadcrumbConfigData } from '@papa-leguas/components/breadcrumbs/BreadcrumbRenderer.vue'

defineProps<{
  items: BreadcrumbItemData[]
  config?: BreadcrumbConfigData
}>()
</script>
```

### 2. Registrar no app.ts

```typescript
// resources/js/app.ts
import { BreadcrumbRegistry } from '../packages/callcocam/papa-leguas/resources/js/utils/BreadcrumbRegistry'
import { defineAsyncComponent } from 'vue'

// Sobrescrever componente padrão
BreadcrumbRegistry.register(
  'breadcrumb-default',
  defineAsyncComponent(() => import('./components/MyCustomBreadcrumb.vue'))
)

// Ou criar novo tipo
BreadcrumbRegistry.register(
  'breadcrumb-minimal',
  defineAsyncComponent(() => import('./components/MinimalBreadcrumb.vue'))
)
```

### 3. Usar Componente Customizado

```vue
<List 
  :breadcrumb-items="breadcrumbs"
  :breadcrumb-config="{ 
    component: 'breadcrumb-minimal'  // Usa componente customizado
  }"
/>
```

## 🎛️ Configurações de Breadcrumb

### BreadcrumbItemData

```typescript
interface BreadcrumbItemData {
  label: string         // Texto do breadcrumb
  href?: string         // URL (opcional)
  icon?: Component      // Ícone lucide (opcional)
  active?: boolean      // Item ativo (opcional)
}
```

### BreadcrumbConfigData

```typescript
interface BreadcrumbConfigData {
  component?: string    // Nome do componente registrado (padrão: 'breadcrumb-default')
  separator?: string    // Separador customizado
  maxItems?: number     // Máximo de items visíveis (padrão: 3)
  showHome?: boolean    // Mostrar link Home (padrão: true)
  homeLabel?: string    // Label do Home (padrão: 'Home')
  homeHref?: string     // URL do Home (padrão: '/')
  homeIcon?: Component  // Ícone do Home (padrão: Home do lucide)
}
```

## 📐 Configurações de Layout

### ListLayoutConfig

```typescript
interface ListLayoutConfig {
  // Container
  fullWidth?: boolean        // Tela cheia ou container limitado
  maxWidth?: string          // Largura máxima customizada
  padding?: string           // Padding customizado
  
  // Grid
  gridColumns?: string       // Número de colunas base
  gap?: string               // Espaçamento entre items
  columnSpan?: string        // Quantas colunas ocupar
  order?: number             // Ordem de exibição
  
  // Responsivo
  responsive?: {
    grid?: {
      sm?: string            // Colunas em sm
      md?: string            // Colunas em md
      lg?: string            // Colunas em lg
      xl?: string            // Colunas em xl
    }
    span?: {
      sm?: string            // Span em sm
      md?: string            // Span em md
      lg?: string            // Span em lg
      xl?: string            // Span em xl
    }
  }
}
```

## 📊 Exemplos de Layout

### Layout Tela Cheia

```vue
<List 
  :layout-config="{ 
    fullWidth: true,
    padding: 'p-8'
  }"
/>
```

### Layout Grid Responsivo

```vue
<List 
  :layout-config="{ 
    gridColumns: '1',
    gap: '4',
    responsive: {
      grid: {
        sm: '2',   // 2 colunas em tablets
        lg: '4'    // 4 colunas em desktops
      }
    }
  }"
/>
```

### Layout com Cards

```vue
<List :layout-config="layoutConfig">
  <template #content="{ layout }">
    <Card v-for="item in items" :key="item.id">
      <CardHeader>
        <CardTitle>{{ item.title }}</CardTitle>
      </CardHeader>
      <CardContent>
        {{ item.content }}
      </CardContent>
    </Card>
  </template>
</List>
```

## 🔗 Integração com Backend

O sistema está preparado para receber configurações do backend (PHP):

```php
// No seu Controller
use Callcocam\PapaLeguas\Support\Concerns\HasGridLayout;

class MyResource
{
    use HasGridLayout;
    
    public function configure()
    {
        $this->gridColumns('1')
             ->gap('6')
             ->responsiveGridColumns(
                 sm: '2',
                 md: '3',
                 lg: '4'
             );
    }
}
```

## 📝 Notas

- Componentes breadcrumb são lazy-loaded para otimização
- Sistema usa shadcn-vue por padrão
- Totalmente type-safe com TypeScript
- Suporta ícones do lucide-vue-next
- Classes Tailwind CSS geradas dinamicamente

## 🔍 Debug

Para ver componentes registrados:

```typescript
import BreadcrumbRegistry from '@papa-leguas/utils/BreadcrumbRegistry'

console.log(BreadcrumbRegistry.getStats())
// { total: 1, initialized: true, components: ['breadcrumb-default'] }
```

