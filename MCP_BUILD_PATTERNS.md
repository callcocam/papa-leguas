# MCP Server - Padrões de Construção do Papa Leguas

Este documento define os padrões e convenções para construção de componentes, controllers, actions e toda a arquitetura do pacote Papa Leguas.

## 📋 Índice

1. [Visão Geral da Arquitetura](#visão-geral)
2. [MCP Tools Sugeridas](#mcp-tools-sugeridas)
3. [Padrões Backend (PHP)](#padrões-backend)
4. [Padrões Frontend (Vue/TypeScript)](#padrões-frontend)
5. [Integração Backend-Frontend](#integração-backend-frontend)
6. [Implementação do MCP Server](#implementação-do-mcp-server)

---

## 🎯 Visão Geral da Arquitetura

O Papa Leguas é um pacote Laravel que **NÃO usa Inertia.js**. Ele usa:

- **Backend**: Laravel 12 com Controllers especializados
- **Frontend**: Vue 3 + TypeScript + ShadCN-Vue
- **Comunicação**: API REST com JSON responses
- **Actions**: Sistema fluente de Actions para CRUD e operações customizadas
- **Builders**: TableBuilder, FormBuilder, InfoList para construção declarativa

---

## 🛠️ MCP Tools Sugeridas

### 1. **analyze-controller**
Analisa um controller e retorna sua estrutura, actions, table config, etc.

```typescript
Tool: analyze-controller
Input: { controller: string }
Output: {
  namespace: string,
  extends: string,
  traits: string[],
  properties: object,
  methods: {
    table: { columns, filters, actions, bulkActions },
    form: { fields },
    actions: []
  }
}
```

### 2. **validate-action-pattern**
Valida se uma Action segue os padrões do pacote

```typescript
Tool: validate-action-pattern
Input: { actionClass: string }
Output: {
  valid: boolean,
  errors: string[],
  suggestions: string[],
  extendsCorrectBase: boolean,
  hasRequiredMethods: boolean,
  followsNamingConvention: boolean
}
```

### 3. **generate-component-template**
Gera template de componente Vue seguindo os padrões

```typescript
Tool: generate-component-template
Input: { 
  type: 'table' | 'form' | 'card' | 'modal' | 'action',
  name: string,
  props?: object
}
Output: {
  template: string,
  script: string,
  types: string,
  composables: string[]
}
```

### 4. **check-integration-consistency**
Verifica consistência entre backend e frontend

```typescript
Tool: check-integration-consistency
Input: { 
  controller: string,
  vueComponent?: string 
}
Output: {
  consistent: boolean,
  mismatches: Array<{
    type: 'action' | 'column' | 'filter' | 'endpoint',
    backend: any,
    frontend: any,
    suggestion: string
  }>
}
```

### 5. **suggest-composable-usage**
Sugere composables adequados para um componente

```typescript
Tool: suggest-composable-usage
Input: { 
  componentPath: string,
  features: string[] 
}
Output: {
  required: string[],
  optional: string[],
  examples: Array<{ composable: string, usage: string }>
}
```

### 6. **validate-type-safety**
Valida tipos TypeScript e interfaces

```typescript
Tool: validate-type-safety
Input: { 
  componentPath: string 
}
Output: {
  valid: boolean,
  missingTypes: string[],
  incorrectTypes: Array<{ property: string, expected: string, found: string }>,
  suggestions: string[]
}
```

---

## 🔧 Padrões Backend (PHP)

### Controllers

#### 1. Estrutura Base
```php
namespace App\Http\Controllers\Landlord;

use Callcocam\PapaLeguas\Http\Controllers\Landlord\LandlordController;
use Callcocam\PapaLeguas\Support\Concerns\InteractsWithRequests;
use Callcocam\PapaLeguas\Support\Table\TableBuilder;

class ExampleController extends LandlordController
{
    use InteractsWithRequests;
    
    // Propriedades obrigatórias
    protected string|null $navigationIcon = 'Icon-Name';
    protected string|null $navigationGroup = 'Group-Name';
    
    // Métodos principais
    protected function table(TableBuilder $table): TableBuilder { }
    protected function form(FormBuilder $form): FormBuilder { }
    protected function infoList(InfoList $infoList): InfoList { }
}
```

#### 2. Convenções de Nomenclatura
- **Controllers**: `{Entity}Controller` (Ex: `UserController`, `TenantController`)
- **Namespace**: `App\Http\Controllers\{Context}` (Ex: `Landlord`, `Tenant`)
- **Métodos protegidos**: Sempre `protected` para table, form, infoList
- **Actions customizadas**: Métodos `getImportActions()`, `getExportActions()`

#### 3. Propriedades Importantes
```php
protected string|null $navigationIcon = 'Users';      // Ícone no menu
protected string|null $navigationGroup = 'Operacional'; // Grupo no menu
protected string $model = User::class;                 // Model associado
```

### Actions

#### 1. Actions Disponíveis
```php
// CRUD básico
CreateAction::make('route.name')
EditAction::make('route.name')
ViewAction::make('route.name')
DeleteAction::make('route.name')

// Operações especiais
ImportAction::make('route.name')
ExportAction::make('route.name')
```

#### 2. Padrão de Configuração de Actions
```php
// Action simples
CreateAction::make('users.create')
    ->label('Novo Usuário')

// Action com confirmação
DeleteAction::make('users.destroy')
    ->confirm([
        'title' => 'Excluir usuário',
        'message' => 'Tem certeza? Esta ação não pode ser desfeita.',
    ])

// Action com callback customizado
ExportAction::make('users.export')
    ->action(function ($records) {
        // Lógica customizada
        return ['file_url' => 'path/to/file'];
    })

// Action com autorização
EditAction::make('users.edit')
    ->authorizeUsing('update')
    ->visible(fn($record) => $record->canEdit())
```

#### 3. Estrutura de uma Action Customizada
```php
namespace App\Actions;

use Callcocam\PapaLeguas\Support\Actions\Action;

class CustomAction extends Action
{
    protected string $method = 'POST';
    protected string $component = 'LinkButton';
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->label('Custom Label')
            ->icon('CustomIcon')
            ->color('primary');
    }
    
    public function execute($model, $data = []): mixed
    {
        // Lógica da action
    }
}
```

### TableBuilder

#### 1. Estrutura Completa
```php
protected function table(TableBuilder $table): TableBuilder
{
    // 1. Model
    $table->model(User::class);
    
    // 2. Colunas
    $table->columns([
        TextColumn::make('name', 'Name')
            ->searchable()
            ->sortable(),
        TextColumn::make('email', 'Email')
            ->searchable(),
        DateTimeColumn::make('created_at', 'Created At')
            ->dateTime('d/m/Y H:i')
            ->sortable(),
        BooleanColumn::make('active', 'Active')
            ->trueIcon('CheckCircle')
            ->falseIcon('XCircle'),
    ]);
    
    // 3. Filtros
    $table->filters([
        TextFilter::make('name', 'Name'),
        SelectFilter::make('status', 'Status')
            ->options([
                'active' => 'Ativo',
                'inactive' => 'Inativo',
            ]),
        DateFilter::make('created_at', 'Created At'),
        TrashedFilter::make('trashed', 'Trashed'),
    ]);
    
    // 4. Header Actions
    $table->headerActions([
        CreateAction::make('users.create')
            ->label('Novo Usuário'),
        ImportAction::make('users.import'),
        ExportAction::make('users.export'),
    ]);
    
    // 5. Row Actions
    $table->actions([
        ViewAction::make('users.show'),
        EditAction::make('users.edit'),
        DeleteAction::make('users.destroy')
            ->confirm([...]),
    ]);
    
    // 6. Bulk Actions
    $table->bulkActions([
        DeleteAction::make('users.bulk-destroy')
            ->label('Excluir selecionados'),
        ExportAction::make('users.bulk-export'),
    ]);
    
    return $table;
}
```

#### 2. Tipos de Colunas
```php
// Texto
TextColumn::make('name', 'Name')
    ->searchable()
    ->sortable()
    ->limit(50)

// Data/Hora
DateTimeColumn::make('created_at', 'Created At')
    ->dateTime('d/m/Y H:i')
    ->sortable()

// Booleano
BooleanColumn::make('active', 'Active')
    ->trueIcon('CheckCircle')
    ->falseIcon('XCircle')
    ->trueColor('success')
    ->falseColor('danger')

// Badge
BadgeColumn::make('status', 'Status')
    ->colors([
        'success' => 'active',
        'danger' => 'inactive',
    ])

// Imagem
ImageColumn::make('avatar', 'Avatar')
    ->circular()
    ->width(50)

// Relacionamento
TextColumn::make('tenant.name', 'Tenant')
    ->searchable()
```

### FormBuilder

#### 1. Estrutura Completa
```php
protected function form(FormBuilder $form): FormBuilder
{
    $form->schema([
        // Section/Group
        Section::make('Informações Básicas')
            ->schema([
                TextInput::make('name', 'Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Digite o nome'),
                    
                TextInput::make('email', 'Email')
                    ->email()
                    ->required()
                    ->unique('users', 'email'),
                    
                Select::make('role', 'Role')
                    ->options([
                        'admin' => 'Admin',
                        'user' => 'User',
                    ])
                    ->default('user'),
            ]),
            
        Section::make('Detalhes')
            ->schema([
                Textarea::make('bio', 'Bio')
                    ->rows(4)
                    ->maxLength(1000),
                    
                Toggle::make('active', 'Active')
                    ->default(true),
                    
                FileUpload::make('avatar', 'Avatar')
                    ->image()
                    ->maxSize(1024),
            ]),
    ]);
    
    return $form;
}
```

### Response Pattern

O pacote usa `Callcocam\PapaLeguas\Support\Response\JsonResponse` para respostas consistentes:

```php
use Callcocam\PapaLeguas\Support\Response\JsonResponse;

// Sucesso
return JsonResponse::success(
    data: $user,
    message: 'User created successfully'
);

// Erro
return JsonResponse::error(
    message: 'Validation failed',
    errors: $validator->errors(),
    code: 422
);

// Com metadata
return JsonResponse::success(
    data: $users,
    meta: [
        'total' => $total,
        'per_page' => $perPage,
    ]
);
```

---

## 🎨 Padrões Frontend (Vue/TypeScript)

### Composables

#### 1. useTable
```typescript
import { useTable } from '@papa-leguas/composables/useTable'

const {
  state,           // Estado reativo da tabela
  load,            // Carregar dados
  reload,          // Recarregar
  changePage,      // Mudar página
  changePerPage,   // Mudar itens por página
  applyFilters,    // Aplicar filtros
  sort,            // Ordenar
  selectRow,       // Selecionar linha
  selectAll,       // Selecionar todas
  clearSelection,  // Limpar seleção
  executeAction,   // Executar action
} = useTable({
  resource: 'users',
  endpoint: '/api/users',
  initialParams: { per_page: 15 },
  autoLoad: true
})
```

#### 2. useAction
```typescript
import { useAction } from '@papa-leguas/composables/useAction'

const { execute, loading, error } = useAction()

await execute({
  action: 'users.create',
  method: 'POST',
  data: formData,
  onSuccess: (response) => {
    // Callback de sucesso
  },
  onError: (error) => {
    // Callback de erro
  }
})
```

#### 3. useBreadcrumbs
```typescript
import { useBreadcrumbs } from '@papa-leguas/composables/useBreadcrumbs'

const { items, addItem, setItems, clear } = useBreadcrumbs()

setItems([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Users', href: '/users' },
  { label: 'Edit', active: true }
])
```

#### 4. useListLayout
```typescript
import { useListLayout } from '@papa-leguas/composables/useListLayout'

const { 
  containerClasses,
  gridClasses,
  updateLayout 
} = useListLayout({
  fullWidth: false,
  gridColumns: '1',
  gap: '6',
  responsive: {
    grid: { md: '2', lg: '3' }
  }
})
```

### Components

#### 1. Estrutura de um Componente Table
```vue
<template>
  <div class="table-wrapper">
    <TableHeader 
      :actions="headerActions"
      :filters="filters"
      @action="handleAction"
      @filter="handleFilter"
    />
    
    <TableBody
      :data="state.data"
      :columns="state.columns"
      :actions="rowActions"
      :loading="state.loading"
      @action="handleRowAction"
    />
    
    <TablePagination
      :meta="state.meta"
      @page-change="changePage"
      @per-page-change="changePerPage"
    />
  </div>
</template>

<script setup lang="ts">
import { useTable } from '@papa-leguas/composables/useTable'
import TableHeader from '@papa-leguas/components/table/TableHeader.vue'
import TableBody from '@papa-leguas/components/table/TableBody.vue'
import TablePagination from '@papa-leguas/components/table/TablePagination.vue'

interface Props {
  resource: string
}

const props = defineProps<Props>()

const {
  state,
  load,
  changePage,
  changePerPage,
  executeAction
} = useTable({
  resource: props.resource,
  autoLoad: true
})

const handleAction = async (action: string, data?: any) => {
  await executeAction(action, data)
}
</script>
```

#### 2. Props TypeScript
```typescript
// Sempre definir props com interface
interface Props {
  resource: string
  endpoint?: string
  initialFilters?: Record<string, any>
  showBreadcrumbs?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  showBreadcrumbs: true
})
```

#### 3. Emits TypeScript
```typescript
// Definir emits com tipos
interface Emits {
  (e: 'update:modelValue', value: any): void
  (e: 'action', action: string, data?: any): void
  (e: 'change', value: any): void
}

const emit = defineEmits<Emits>()
```

### Types

#### 1. Table Types
```typescript
// types/table.ts
export interface TableRecord {
  id: string | number
  [key: string]: any
}

export interface TableColumn {
  name: string
  label: string
  sortable?: boolean
  searchable?: boolean
  component?: string
  componentProps?: Record<string, any>
}

export interface TableAction {
  name: string
  label: string
  icon?: string
  color?: string
  url?: string
  method?: string
  confirm?: {
    title: string
    message: string
  }
  visible?: boolean
  disabled?: boolean
}

export interface TableMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
  path: string
  has_more_pages: boolean
}

export interface TableState {
  data: TableRecord[]
  meta: TableMeta
  columns: TableColumn[]
  filters: TableFilter[]
  headerActions: TableAction[]
  bulkActions: TableAction[]
  loading: boolean
  error: string | null
  selectedRows: (string | number)[]
}
```

### ShadCN-Vue Components

O pacote usa ShadCN-Vue como base. Sempre usar os componentes do ShadCN:

```vue
<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger } from '@/components/ui/select'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
</script>
```

---

## 🔗 Integração Backend-Frontend

### 1. Fluxo de Dados

```
Controller (PHP)
    └─> table() retorna TableBuilder
        └─> TableBuilder::toArray()
            └─> JSON Response
                └─> useTable (Vue)
                    └─> state.data, state.columns, state.actions
```

### 2. Estrutura de Resposta JSON

```json
{
  "data": {
    "data": [...],
    "meta": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 15,
      "total": 75
    },
    "columns": [...],
    "filters": [...],
    "headerActions": [...],
    "actions": [...],
    "bulkActions": [...],
    "breadcrumbs": [...]
  }
}
```

### 3. Actions e Endpoints

```php
// Backend
CreateAction::make('users.create')
    ->url(fn() => route('api.landlord.users.store'))
    ->method('POST')

// Frontend
await executeAction({
  action: 'users.create',
  method: 'POST',
  url: '/api/landlord/users',
  data: { name: 'John', email: 'john@example.com' }
})
```

---

## 🚀 Implementação do MCP Server

### Server Configuration

```php
<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;

class BuildPatternsServer extends Server
{
    protected string $name = 'Papa Leguas Build Patterns Server';
    protected string $version = '1.0.0';
    
    protected string $instructions = 
        'Este servidor fornece ferramentas para validar, analisar e gerar código ' .
        'seguindo os padrões de construção do pacote Papa Leguas. ' .
        'Use estas ferramentas ao criar controllers, actions, components e composables.';
    
    protected array $tools = [
        AnalyzeControllerTool::class,
        ValidateActionPatternTool::class,
        GenerateComponentTemplateTool::class,
        CheckIntegrationConsistencyTool::class,
        SuggestComposableUsageTool::class,
        ValidateTypeSafetyTool::class,
    ];
    
    protected array $resources = [
        BuildPatternsResource::class,
        ControllerPatternsResource::class,
        ComponentPatternsResource::class,
    ];
}
```

### Tool Example: AnalyzeControllerTool

```php
<?php

namespace App\Mcp\Tools;

use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class AnalyzeControllerTool extends Tool
{
    protected string $name = 'analyze_controller';
    
    protected string $description = 
        'Analisa um controller e retorna sua estrutura, actions configuradas, ' .
        'table config, form config, etc. Útil para entender como um controller ' .
        'está estruturado e se segue os padrões do pacote.';
    
    public function inputSchema(): JsonSchema
    {
        return JsonSchema::new()
            ->type('object')
            ->properties([
                'controller' => JsonSchema::new()
                    ->type('string')
                    ->description('Nome completo do controller (Ex: App\\Http\\Controllers\\UserController)'),
            ])
            ->required(['controller']);
    }
    
    public function handle(Request $request): Response
    {
        $controllerClass = $request->string('controller');
        
        if (!class_exists($controllerClass)) {
            return Response::error("Controller {$controllerClass} não encontrado.");
        }
        
        $reflection = new \ReflectionClass($controllerClass);
        
        $analysis = [
            'namespace' => $reflection->getNamespaceName(),
            'name' => $reflection->getShortName(),
            'extends' => $reflection->getParentClass()?->getName(),
            'traits' => array_map(
                fn($trait) => $trait->getName(),
                $reflection->getTraits()
            ),
            'properties' => $this->analyzeProperties($reflection),
            'methods' => $this->analyzeMethods($reflection),
            'follows_patterns' => $this->validatePatterns($reflection),
        ];
        
        return Response::text(
            "# Controller Analysis: {$analysis['name']}\n\n" .
            "**Namespace**: `{$analysis['namespace']}`\n" .
            "**Extends**: `{$analysis['extends']}`\n\n" .
            "## Properties\n" .
            $this->formatProperties($analysis['properties']) . "\n\n" .
            "## Methods\n" .
            $this->formatMethods($analysis['methods']) . "\n\n" .
            "## Pattern Validation\n" .
            $this->formatValidation($analysis['follows_patterns'])
        );
    }
    
    private function analyzeProperties(\ReflectionClass $class): array
    {
        $properties = [];
        
        foreach ($class->getProperties() as $property) {
            if ($property->class === $class->getName()) {
                $properties[$property->getName()] = [
                    'visibility' => $this->getVisibility($property),
                    'type' => $property->getType()?->getName(),
                    'default' => $property->getDefaultValue(),
                ];
            }
        }
        
        return $properties;
    }
    
    // ... outros métodos de análise
}
```

### Tool Example: ValidateActionPatternTool

```php
<?php

namespace App\Mcp\Tools;

use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ValidateActionPatternTool extends Tool
{
    protected string $name = 'validate_action_pattern';
    
    protected string $description = 
        'Valida se uma Action segue os padrões do pacote Papa Leguas. ' .
        'Verifica herança, métodos obrigatórios, convenções de nomenclatura, etc.';
    
    public function inputSchema(): JsonSchema
    {
        return JsonSchema::new()
            ->type('object')
            ->properties([
                'action_class' => JsonSchema::new()
                    ->type('string')
                    ->description('Nome completo da Action class'),
                'strict' => JsonSchema::new()
                    ->type('boolean')
                    ->description('Modo strict (valida também boas práticas)')
                    ->default(false),
            ])
            ->required(['action_class']);
    }
    
    public function handle(Request $request): Response
    {
        $actionClass = $request->string('action_class');
        $strict = $request->boolean('strict', false);
        
        if (!class_exists($actionClass)) {
            return Response::error("Action {$actionClass} não encontrada.");
        }
        
        $reflection = new \ReflectionClass($actionClass);
        $errors = [];
        $warnings = [];
        $suggestions = [];
        
        // 1. Verifica se estende Action
        if (!$reflection->isSubclassOf(\Callcocam\PapaLeguas\Support\Actions\Action::class)) {
            $errors[] = 'Action deve estender Callcocam\PapaLeguas\Support\Actions\Action';
        }
        
        // 2. Verifica método setUp
        if (!$reflection->hasMethod('setUp')) {
            $warnings[] = 'Recomendado implementar método setUp() para configuração inicial';
        }
        
        // 3. Verifica propriedades importantes
        $requiredProps = ['method', 'component'];
        foreach ($requiredProps as $prop) {
            if (!$reflection->hasProperty($prop)) {
                $errors[] = "Propriedade '{$prop}' não encontrada";
            }
        }
        
        // 4. Convenção de nomenclatura
        if (!str_ends_with($reflection->getShortName(), 'Action')) {
            $warnings[] = 'Nome da classe deve terminar com "Action"';
        }
        
        // 5. Modo strict
        if ($strict) {
            $this->validateStrictMode($reflection, $warnings, $suggestions);
        }
        
        $valid = empty($errors);
        
        return Response::text(
            "# Action Validation: {$reflection->getShortName()}\n\n" .
            "**Status**: " . ($valid ? '✅ Válido' : '❌ Inválido') . "\n\n" .
            ($errors ? "## ❌ Erros\n" . implode("\n", array_map(fn($e) => "- {$e}", $errors)) . "\n\n" : '') .
            ($warnings ? "## ⚠️ Avisos\n" . implode("\n", array_map(fn($w) => "- {$w}", $warnings)) . "\n\n" : '') .
            ($suggestions ? "## 💡 Sugestões\n" . implode("\n", array_map(fn($s) => "- {$s}", $suggestions)) : '')
        );
    }
}
```

---

## 📚 Resources

### BuildPatternsResource

```php
<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class BuildPatternsResource extends Resource
{
    protected string $uri = 'papa-leguas://build-patterns';
    protected string $name = 'Papa Leguas Build Patterns';
    protected string $description = 'Documentação completa dos padrões de construção';
    
    public function handle(Request $request): Response
    {
        $markdown = $this->generatePatternsDocs();
        return Response::text($markdown);
    }
    
    private function generatePatternsDocs(): string
    {
        return <<<'MD'
# Papa Leguas Build Patterns

## Controllers
- Estender `LandlordController` ou `AppController`
- Usar trait `InteractsWithRequests`
- Definir `$navigationIcon` e `$navigationGroup`
- Implementar métodos `table()`, `form()`, `infoList()` quando necessário

## Actions
- Estender `Action`
- Definir propriedades `$method`, `$component`
- Implementar `setUp()` para configuração
- Usar métodos fluentes: `label()`, `icon()`, `color()`, `confirm()`

## Components Vue
- Definir Props com TypeScript interface
- Usar composables do pacote
- Seguir ShadCN-Vue para UI
- Emitir eventos tipados

## Composables
- Retornar objeto com funções e estado reativo
- Documentar parâmetros e retorno
- Usar TypeScript para tipo-segurança

MD;
    }
}
```

---

## 🎯 Casos de Uso

### 1. Criando novo Controller

```bash
# IA usa analyze-controller para entender padrão existente
MCP Tool: analyze-controller
Input: { controller: "App\\Http\\Controllers\\UserController" }

# IA gera novo controller seguindo o padrão
# IA valida com validate-action-pattern
```

### 2. Criando novo Component Vue

```bash
# IA usa generate-component-template
MCP Tool: generate-component-template
Input: { type: "table", name: "ProductTable" }

# IA sugere composables
MCP Tool: suggest-composable-usage
Input: { features: ["table", "pagination", "filters"] }

# IA valida tipos
MCP Tool: validate-type-safety
```

### 3. Verificando Integração

```bash
# IA verifica consistência backend-frontend
MCP Tool: check-integration-consistency
Input: { 
  controller: "App\\Http\\Controllers\\UserController",
  vueComponent: "resources/js/views/users/List.vue"
}
```

---

## 📝 Checklist de Validação

### Backend (PHP)
- [ ] Controller estende classe base correta
- [ ] Usa trait `InteractsWithRequests`
- [ ] Propriedades `$navigationIcon` e `$navigationGroup` definidas
- [ ] Métodos `table()`, `form()` retornam builders corretos
- [ ] Actions seguem padrão fluente
- [ ] Responses usam `JsonResponse`

### Frontend (Vue)
- [ ] Props definidas com interface TypeScript
- [ ] Usa composables adequados
- [ ] Componentes ShadCN-Vue para UI
- [ ] Emits tipados
- [ ] Types importados de `types/`

### Integração
- [ ] Endpoints backend correspondem aos do frontend
- [ ] Actions backend disponíveis no frontend
- [ ] Estrutura JSON response seguida
- [ ] Types TypeScript correspondem aos dados do backend
