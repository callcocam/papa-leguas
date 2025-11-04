# Sistema de Table Integrado

Sistema completo de tabelas com integração backend (PHP) e frontend (Vue), incluindo breadcrumbs, paginação, filtros, ações e muito mais.

## 📋 Estrutura

```
packages/callcocam/papa-leguas/
├── src/Support/Table/
│   ├── TableBuilder.php              # Builder principal
│   ├── Sources/ModelSource.php       # Source de dados Eloquent
│   └── Concerns/                     # Traits para funcionalidades
├── resources/js/
│   ├── types/
│   │   └── table.ts                  # Interfaces TypeScript
│   ├── composables/
│   │   ├── useTable.ts               # Gerenciamento de estado
│   │   └── useListLayout.ts          # Layout grid responsivo
│   ├── components/
│   │   └── breadcrumbs/              # Sistema de breadcrumbs
│   └── views/
│       └── crud/
│           └── List.vue              # Componente principal
```

## 🚀 Uso Básico

### Backend (PHP)

```php
<?php

namespace App\Http\Controllers;

use Callcocam\PapaLeguas\Http\Controllers\Landlord\LandlordController;
use Callcocam\PapaLeguas\Support\Concerns\InteractsWithRequests;

class UserController extends LandlordController
{
    use InteractsWithRequests;

    protected function table(\Callcocam\PapaLeguas\Support\Table\TableBuilder $table): \Callcocam\PapaLeguas\Support\Table\TableBuilder
    {
        // Define o modelo
        $table->model(\App\Models\User::class);

        // Define as colunas
        $table->columns([
            \Callcocam\PapaLeguas\Support\Table\Columns\TextColumn::make('name', 'Name'),
            \Callcocam\PapaLeguas\Support\Table\Columns\TextColumn::make('email', 'Email'),
        ]);

        // Define filtros
        $table->filters([
            \Callcocam\PapaLeguas\Support\Table\Filters\TextFilter::make('name', 'Name'),
            \Callcocam\PapaLeguas\Support\Table\Filters\TextFilter::make('email', 'Email'),
        ]);

        // Ações de header
        $table->headerActions([
            \Callcocam\PapaLeguas\Support\Actions\CreateAction::make('users.create'),
            \Callcocam\PapaLeguas\Support\Actions\ImportAction::make('users.import'),
        ]);

        // Ações de linha
        $table->actions([
            \Callcocam\PapaLeguas\Support\Actions\ViewAction::make('users.show'),
            \Callcocam\PapaLeguas\Support\Actions\EditAction::make('users.edit'),
            \Callcocam\PapaLeguas\Support\Actions\DeleteAction::make('users.destroy'),
        ]);

        // Ações em massa
        $table->bulkActions([
            \Callcocam\PapaLeguas\Support\Actions\DeleteAction::make('users.bulk-destroy'),
        ]);

        return $table;
    }
}
```

### Frontend (Vue)

#### Uso Simples

```vue
<template>
  <List resource="users" />
</template>

<script setup lang="ts">
import List from '@papa-leguas/views/crud/List.vue'
</script>
```

#### Uso com Customização

```vue
<template>
  <List 
    resource="users"
    :layout-config="layoutConfig"
    :show-header="true"
    :show-pagination="true"
  >
    <!-- Customizar conteúdo -->
    <template #content="{ table, executeAction }">
      <div v-for="record in table.data" :key="record.id" class="p-4 border rounded">
        <h3>{{ record.name }}</h3>
        <p>{{ record.email }}</p>
        
        <!-- Renderizar ações -->
        <div class="flex gap-2 mt-2">
          <button
            v-for="action in Object.values(record.actions)"
            :key="action.name"
            @click="executeAction(action, record)"
            class="px-3 py-1 rounded"
            :class="`bg-${action.color}-500 text-white`"
          >
            {{ action.label }}
          </button>
        </div>
      </div>
    </template>

    <!-- Customizar header actions -->
    <template #header-actions="{ actions, executeAction }">
      <button
        v-for="action in actions"
        :key="action.name"
        @click="executeAction(action)"
        class="px-4 py-2 rounded"
      >
        {{ action.label }}
      </button>
    </template>
  </List>
</template>

<script setup lang="ts">
import List from '@papa-leguas/views/crud/List.vue'

const layoutConfig = {
  fullWidth: false,
  gridColumns: '1',
  gap: '6',
  responsive: {
    grid: {
      md: '2',
      lg: '3'
    }
  }
}
</script>
```

## 📦 Estrutura de Dados

### Resposta do Backend (JSON)

```json
{
  "data": [
    {
      "id": "01k83ehq74y7wvz056gvnm3v4g",
      "name": "Administrador",
      "email": "admin@example.com",
      "name_formatted": "Administrador",
      "email_formatted": "admin@example.com",
      "actions": {
        "users.show": {
          "name": "users.show",
          "label": "Visualizar",
          "icon": "Eye",
          "method": "GET",
          "url": "http://example.com/api/users/01k83ehq74y7wvz056gvnm3v4g",
          "color": "blue",
          "visible": true,
          "authorized": true
        }
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 2,
    "from": 1,
    "to": 2
  },
  "columns": [
    {
      "name": "name",
      "label": "Name",
      "type": "text",
      "component": "table-column-text",
      "visible": true
    }
  ],
  "bulkActions": [...],
  "filters": [...],
  "headers": [...],
  "breadcrumbs": [
    {
      "title": "Home",
      "href": "/",
      "current": false
    }
  ]
}
```

### Interfaces TypeScript

```typescript
// Importar tipos
import type {
  TableResponse,
  TableRecord,
  TableAction,
  TableColumn,
  TableMeta
} from '@papa-leguas/types/table'

// Usar composable
import { useTable } from '@papa-leguas/composables/useTable'

const {
  state: tableState,
  load,
  reload,
  changePage,
  search,
  executeAction
} = useTable({
  resource: 'users',
  autoLoad: true
})
```

## 🎨 Customização de Layout

### Grid Responsivo

```typescript
const layoutConfig = {
  // Container
  fullWidth: false,              // false = max-w-7xl, true = w-full
  maxWidth: 'max-w-5xl',        // Override da largura máxima
  padding: 'px-4 py-8',         // Override do padding
  
  // Grid
  gridColumns: '1',             // Colunas base
  gap: '6',                     // Espaçamento
  
  // Responsivo
  responsive: {
    grid: {
      sm: '2',                  // 2 colunas em sm
      md: '3',                  // 3 colunas em md
      lg: '4',                  // 4 colunas em lg
      xl: '6'                   // 6 colunas em xl
    },
    span: {
      md: 'full',               // Ocupa todas colunas em md
      lg: '2'                   // Ocupa 2 colunas em lg
    }
  }
}
```

### Breadcrumbs Customizados

```vue
<List
  resource="users"
  :breadcrumb-items="[
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'Usuários', href: '/users' },
    { label: 'Lista', active: true }
  ]"
  :breadcrumb-config="{
    showHome: true,
    homeLabel: 'Início',
    maxItems: 3
  }"
/>
```

## 🔧 Composables

### useTable

Gerencia estado e operações da tabela.

```typescript
const {
  // Estado
  state,                        // Estado completo da tabela
  selectedRecords,              // Registros selecionados
  isAllSelected,                // Todos selecionados?
  hasSelected,                  // Algum selecionado?
  
  // Carregamento
  load,                         // Carregar com novos params
  reload,                       // Recarregar mantendo params
  
  // Interação
  search,                       // Buscar por texto
  changePage,                   // Mudar página
  changeSort,                   // Mudar ordenação
  applyFilters,                 // Aplicar filtros
  clearFilters,                 // Limpar filtros
  
  // Seleção
  toggleRow,                    // Selecionar/desselecionar linha
  selectAll,                    // Selecionar todas
  deselectAll,                  // Desselecionar todas
  isRowSelected,                // Verifica se linha está selecionada
  
  // Ações
  executeAction,                // Executar ação
  executeBulkAction             // Executar ação em massa
} = useTable({
  resource: 'users',
  endpoint: '/custom/endpoint', // Opcional
  initialParams: {              // Opcional
    per_page: 25,
    filters: { status: 'active' }
  },
  autoLoad: true                // Carrega automaticamente
})
```

### useListLayout

Gerencia classes de layout grid.

```typescript
const {
  containerClasses,             // Classes do container
  gridClasses,                  // Classes do grid
  columnSpanClasses,            // Classes de column span
  orderClasses,                 // Classes de order
  itemClasses                   // Classes combinadas do item
} = useListLayout({
  fullWidth: false,
  gridColumns: '3',
  gap: '4'
})
```

## 📡 API do Composable

### Métodos de Carregamento

```typescript
// Carregar com novos parâmetros
await load({ page: 2, search: 'admin' })

// Recarregar mantendo parâmetros atuais
await reload()

// Buscar por texto
await search('admin')

// Mudar página
await changePage(2)

// Mudar ordenação
await changeSort({ column: 'name', direction: 'asc' })

// Aplicar filtros
await applyFilters({ status: 'active', role: 'admin' })

// Limpar filtros
await clearFilters()
```

### Métodos de Seleção

```typescript
// Selecionar/desselecionar linha
toggleRow('01k83ehq74y7wvz056gvnm3v4g')

// Selecionar todas as linhas
selectAll()

// Desselecionar todas as linhas
deselectAll()

// Verificar se linha está selecionada
if (isRowSelected('01k83ehq74y7wvz056gvnm3v4g')) {
  console.log('Linha selecionada')
}
```

### Métodos de Ação

```typescript
// Executar ação individual
await executeAction(action, record)

// Executar ação em massa
await executeBulkAction(bulkAction)
```

## 🎯 Slots Disponíveis

### content

Customiza a renderização dos dados.

```vue
<template #content="{ table, executeAction, toggleRow, isRowSelected }">
  <div v-for="record in table.data" :key="record.id">
    {{ record.name }}
  </div>
</template>
```

### header-actions

Customiza as ações do header.

```vue
<template #header-actions="{ actions, executeAction }">
  <button 
    v-for="action in actions" 
    :key="action.name"
    @click="executeAction(action)"
  >
    {{ action.label }}
  </button>
</template>
```

### pagination

Customiza a paginação.

```vue
<template #pagination="{ meta, changePage }">
  <div>
    Página {{ meta.current_page }} de {{ meta.last_page }}
  </div>
</template>
```

## 🔍 Props do Componente List

```typescript
interface Props {
  resource: string              // OBRIGATÓRIO: nome do resource
  endpoint?: string             // Endpoint customizado
  initialParams?: object        // Parâmetros iniciais
  layoutConfig?: object         // Configuração de layout
  breadcrumbItems?: array       // Override de breadcrumbs
  breadcrumbConfig?: object     // Config de breadcrumbs
  showHeader?: boolean          // Mostrar header (default: true)
  showHeaderActions?: boolean   // Mostrar ações header (default: true)
  showPagination?: boolean      // Mostrar paginação (default: true)
  autoLoad?: boolean            // Auto-carregar (default: true)
}
```

## 📝 Exemplos Avançados

### Com Busca e Filtros

```vue
<template>
  <List resource="users">
    <template #header-actions="{ actions }">
      <!-- Busca -->
      <input
        v-model="searchQuery"
        @input="debounce(() => search(searchQuery), 300)"
        placeholder="Buscar..."
        class="px-3 py-2 border rounded"
      />
      
      <!-- Ações originais -->
      <button v-for="action in actions" :key="action.name">
        {{ action.label }}
      </button>
    </template>
  </List>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useTable } from '@papa-leguas/composables/useTable'

const searchQuery = ref('')
const { search } = useTable({ resource: 'users', autoLoad: false })
</script>
```

### Com Seleção Múltipla

```vue
<template>
  <List resource="users">
    <template #content="{ table, toggleRow, isRowSelected }">
      <div v-for="record in table.data" :key="record.id">
        <input
          type="checkbox"
          :checked="isRowSelected(record.id)"
          @change="toggleRow(record.id)"
        />
        {{ record.name }}
      </div>
    </template>
  </List>
</template>
```

## 🚨 Tratamento de Erros

```typescript
const { state } = useTable({ resource: 'users' })

// Verificar erros
if (state.value.error) {
  console.error('Erro:', state.value.error)
}

// Verificar loading
if (state.value.loading) {
  console.log('Carregando...')
}
```

## 🔗 Integração com Backend PHP

O sistema se integra automaticamente com `InteractsWithRequests` trait:

```php
// O método index() do trait retorna automaticamente o JSON esperado
public function index(Request $request): JsonResponse
{
    if (method_exists($this, 'table')) {
        $table = $this->table(TableBuilder::make($this->getModelClass()))
            ->context($this)
            ->request($request);
        
        return response()->json($table->render($request));
    }
}
```

## 📚 Referências

- Backend: `packages/callcocam/papa-leguas/src/Support/Table/TableBuilder.php`
- Types: `packages/callcocam/papa-leguas/resources/js/types/table.ts`
- Composable: `packages/callcocam/papa-leguas/resources/js/composables/useTable.ts`
- Component: `packages/callcocam/papa-leguas/resources/js/views/crud/List.vue`
- Breadcrumbs: `packages/callcocam/papa-leguas/BREADCRUMB_SYSTEM.md`

