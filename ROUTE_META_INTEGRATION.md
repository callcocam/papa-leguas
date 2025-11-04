# Integração Route Meta com Table System

Sistema automático que injeta informações no `route.meta` para os componentes de table usarem.

## 🎯 Como Funciona

### 1. **Backend Gera Rotas com Meta**

```php
// VueRouteGeneratorService.php
protected function generateListRoute($metadata, string $resourceName): array
{
    $context = strtolower($this->context->value);
    $endpoint = "/api/{$context}/{$resourceName}";
    
    return [
        'name' => "{$resourceName}.list",
        'path' => '',
        'component' => 'views/crud/List.vue',
        'meta' => [
            // Informações básicas
            'title' => $metadata->pluralModelName,
            'icon' => $metadata->icon,
            'action' => 'list',
            'resource' => $resourceName,
            
            // Informações para Table
            'endpoint' => $endpoint,              // Ex: /api/landlord/users
            'controller' => $metadata->className,  // Ex: App\Http\Controllers\UserController
            'modelName' => $metadata->singleModelName, // Ex: User
        ],
    ];
}
```

### 2. **Frontend Usa route.meta Automaticamente**

```vue
<!-- List.vue -->
<template>
  <!-- Não precisa passar props! -->
  <TableRenderer />
</template>
```

O componente busca do `route.meta`:

```typescript
const route = useRoute()

// Resolve automaticamente
const resourceName = computed(() => {
    return props.resource || route.meta.resource as string
})

const endpointUrl = computed(() => {
    return props.endpoint || route.meta.endpoint as string
})
```

---

## 📊 Estrutura do route.meta

### Meta Padrão (Gerado Automaticamente)

```typescript
interface RouteMeta {
  // Básico
  title: string              // Nome plural do resource
  icon: string               // Ícone
  action: string             // 'list', 'create', 'edit', etc
  resource: string           // Nome do resource (ex: 'users')
  requiresAuth: boolean      // Requer autenticação
  
  // Para Table
  endpoint: string           // URL da API (ex: '/api/landlord/users')
  controller: string         // Classe do controller
  modelName: string          // Nome do model (ex: 'User')
  
  // CRUD disponível
  crud: string[]             // ['index', 'create', 'show', 'edit', 'destroy']
}
```

---

## 🚀 Uso

### Caso 1: Uso Automático (Recomendado)

Quando a rota é acessada normalmente, tudo funciona automaticamente:

```vue
<!-- views/crud/List.vue -->
<template>
  <!-- NADA é necessário! -->
</template>
```

O sistema resolve:
- ✅ `resource` → `route.meta.resource`
- ✅ `endpoint` → `route.meta.endpoint`
- ✅ `controller` → `route.meta.controller`

### Caso 2: Override via Props

Se precisar customizar:

```vue
<template>
  <List 
    resource="custom-users"
    endpoint="/custom/endpoint"
  />
</template>
```

**Prioridade**: Props > route.meta

---

## 🔧 Customização no Controller

### Personalizar Endpoint

Se quiser mudar o endpoint no controller:

```php
class UserController extends LandlordController
{
    // O meta será gerado automaticamente com:
    // endpoint: /api/landlord/users
    // resource: users
    // controller: App\Http\Controllers\UserController
}
```

### Múltiplos Contextos

O endpoint muda automaticamente por contexto:

```php
// Landlord
endpoint: /api/landlord/users

// Tenant  
endpoint: /api/tenant/users

// Admin
endpoint: /api/admin/users
```

---

## 🎨 Componentes Table Customizados

Seu componente de table também pode usar `route.meta`:

```vue
<!-- TableCards.vue -->
<template>
  <div class="grid grid-cols-3 gap-4">
    <Card v-for="record in state.data" :key="record.id">
      <!-- Renderização customizada -->
    </Card>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useTable } from '@papa-leguas/composables/useTable'

const route = useRoute()

const props = defineProps({
  resource: String,
  endpoint: String,
  initialParams: Object
})

// Resolve do route.meta ou props
const resourceName = computed(() => {
  return props.resource || route.meta.resource
})

const endpointUrl = computed(() => {
  return props.endpoint || route.meta.endpoint
})

// Usa o composable com dados resolvidos
const { state } = useTable({
  resource: resourceName.value,
  endpoint: endpointUrl.value,
  initialParams: props.initialParams,
  autoLoad: true
})
</script>
```

---

## 🔍 Debug

Para ver o que está no `route.meta`:

```vue
<script setup>
import { useRoute } from 'vue-router'

const route = useRoute()

console.log('Route Meta:', {
  resource: route.meta.resource,
  endpoint: route.meta.endpoint,
  controller: route.meta.controller,
  modelName: route.meta.modelName
})
</script>
```

---

## 📝 Exemplo Completo

### Backend (Automático)

```php
// UserController.php
class UserController extends LandlordController
{
    use InteractsWithRequests;
    
    protected function table(TableBuilder $table): TableBuilder
    {
        $table->model(User::class);
        $table->columns([
            TextColumn::make('name', 'Name'),
            TextColumn::make('email', 'Email'),
        ]);
        return $table;
    }
}
```

### Rota Gerada

```javascript
{
  name: 'users.list',
  path: '',
  component: 'views/crud/List.vue',
  meta: {
    title: 'Users',
    icon: 'Users',
    action: 'list',
    resource: 'users',
    endpoint: '/api/landlord/users',
    controller: 'App\\Http\\Controllers\\UserController',
    modelName: 'User',
    crud: ['index', 'create', 'show', 'edit', 'destroy']
  }
}
```

### Frontend (Automático)

```vue
<!-- Acessa /users e funciona! -->
<template>
  <!-- List.vue usa route.meta automaticamente -->
</template>
```

---

## 🎯 Vantagens

1. ✅ **Zero Configuration**: Funciona automaticamente
2. ✅ **Type-Safe**: TypeScript nos componentes
3. ✅ **Flexible**: Pode sobrescrever via props
4. ✅ **DRY**: Não repete informações
5. ✅ **Maintainable**: Mudanças no backend refletem automaticamente

---

## 🛠️ Editando Configuração

### No Controller

Você pode influenciar o que vai no meta através dos metadados do controller:

```php
class UserController extends LandlordController
{
    protected ?string $model = User::class;
    protected string|null $navigationIcon = 'Users';
    protected string|null $navigationGroup = 'Operacional';
    
    // Influencia o resource name
    protected function getPluralModelName(): string
    {
        return 'Usuários';
    }
}
```

### No Service Provider

Para customização global:

```php
// AppServiceProvider.php
VueRouteGeneratorService::macro('customEndpoint', function($resource) {
    return "/custom/api/{$resource}";
});
```

---

## 🔗 Arquivos Relacionados

- Backend: `packages/callcocam/papa-leguas/src/Services/Menu/VueRouteGeneratorService.php`
- Frontend: `packages/callcocam/papa-leguas/resources/js/views/crud/List.vue`
- Table: `packages/callcocam/papa-leguas/resources/js/components/table/DefaultTable.vue`
- Composable: `packages/callcocam/papa-leguas/resources/js/composables/useTable.ts`

