# Plano de Implementação - MCP Build Patterns Server

## 📋 Resumo Executivo

Criei uma proposta completa para um **MCP Server de Padrões de Construção** que irá ajudar IAs (como eu 😊) a entender e seguir os padrões do pacote Papa Leguas.

### Arquivos Criados

1. **MCP_BUILD_PATTERNS.md** - Documentação completa dos padrões e ferramentas MCP
2. **MCP.md** - Atualizado com informações corretas sobre a arquitetura atual

---

## 🎯 O que o MCP Server vai fazer?

O servidor MCP irá:

1. **Analisar** controllers, actions e components existentes
2. **Validar** se o código segue os padrões do pacote
3. **Gerar** templates de código seguindo as convenções
4. **Verificar** consistência entre backend (PHP) e frontend (Vue)
5. **Sugerir** composables e patterns adequados
6. **Documentar** automaticamente os padrões do projeto

---

## 🛠️ Ferramentas MCP Propostas

### 1. **analyze-controller**
Analisa um controller e retorna sua estrutura completa:
- Namespace, extends, traits
- Propriedades (navigationIcon, navigationGroup, etc.)
- Métodos (table, form, infoList)
- Actions configuradas
- Validação de padrões

**Caso de uso**: "Analise o UserController para eu criar um ProductController similar"

### 2. **validate-action-pattern**
Valida se uma Action customizada segue os padrões:
- Estende Action base
- Tem métodos obrigatórios
- Segue convenções de nomenclatura
- Usa fluent interface corretamente

**Caso de uso**: "Valide se minha CustomExportAction está correta"

### 3. **generate-component-template**
Gera templates de componentes Vue seguindo os padrões:
- Table components
- Form components
- Card components
- Modal components
- Action components

**Caso de uso**: "Gere um template de table component para produtos"

### 4. **check-integration-consistency**
Verifica se backend e frontend estão consistentes:
- Actions backend disponíveis no frontend
- Endpoints corretos
- Estrutura de dados compatível
- Types TypeScript corretos

**Caso de uso**: "Verifique se o ProductController está integrado corretamente com ProductList.vue"

### 5. **suggest-composable-usage**
Sugere composables adequados para um componente:
- useTable para tabelas
- useAction para ações
- useBreadcrumbs para navegação
- useListLayout para layout

**Caso de uso**: "Quais composables devo usar no meu ProductForm.vue?"

### 6. **validate-type-safety**
Valida tipos TypeScript:
- Props corretas
- Interfaces definidas
- Types importados
- Emits tipados

**Caso de uso**: "Valide os tipos do meu component"

---

## 📐 Padrões Documentados

### Backend (PHP)

#### Controllers
```php
// ✅ Padrão correto
class UserController extends LandlordController
{
    use InteractsWithRequests;
    
    protected string|null $navigationIcon = 'Users';
    protected string|null $navigationGroup = 'Operacional';
    
    protected function table(TableBuilder $table): TableBuilder
    {
        $table->model(User::class);
        // ... configuração
        return $table;
    }
}
```

#### Actions
```php
// ✅ Action customizada correta
class CustomAction extends Action
{
    protected string $method = 'POST';
    protected string $component = 'LinkButton';
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->label('Custom Label')
            ->icon('Icon')
            ->color('primary')
            ->confirm([...]);
    }
}
```

#### Responses
```php
// ✅ Response padrão
return JsonResponse::success(
    data: $users,
    message: 'Users loaded',
    meta: [...]
);
```

### Frontend (Vue/TypeScript)

#### Components
```vue
<script setup lang="ts">
// ✅ Props tipadas
interface Props {
  resource: string
  endpoint?: string
}

const props = withDefaults(defineProps<Props>(), {
  endpoint: undefined
})

// ✅ Emits tipados
interface Emits {
  (e: 'update', value: any): void
}

const emit = defineEmits<Emits>()

// ✅ Composables
const { state, load } = useTable({ resource: props.resource })
</script>
```

#### Types
```typescript
// ✅ Types bem definidos
export interface TableRecord {
  id: string | number
  [key: string]: any
}

export interface TableAction {
  name: string
  label: string
  icon?: string
  method?: string
  confirm?: {
    title: string
    message: string
  }
}
```

---

## 🚀 Próximos Passos

### 1. Implementar as Tools

Criar as classes em `app/Mcp/Tools/`:

```bash
php artisan make:mcp-tool AnalyzeControllerTool
php artisan make:mcp-tool ValidateActionPatternTool
php artisan make:mcp-tool GenerateComponentTemplateTool
php artisan make:mcp-tool CheckIntegrationConsistencyTool
php artisan make:mcp-tool SuggestComposableUsageTool
php artisan make:mcp-tool ValidateTypeSafetyTool
```

### 2. Implementar os Resources

Criar resources em `app/Mcp/Resources/`:

```bash
php artisan make:mcp-resource BuildPatternsResource
php artisan make:mcp-resource ControllerPatternsResource
php artisan make:mcp-resource ComponentPatternsResource
```

### 3. Criar o Server

```bash
php artisan make:mcp-server BuildPatternsServer
```

### 4. Registrar Rotas

Em `routes/ai.php`:

```php
use App\Mcp\Servers\BuildPatternsServer;

Mcp::web('/mcp/build-patterns', BuildPatternsServer::class)
    ->middleware(['auth:sanctum']);

Mcp::local('build-patterns', BuildPatternsServer::class);
```

### 5. Testar

```bash
# Via Inspector
php artisan mcp:inspector build-patterns

# Via Claude Desktop
# Adicionar ao claude_desktop_config.json
```

---

## 💡 Benefícios

### Para IAs
- ✅ Entender rapidamente os padrões do projeto
- ✅ Gerar código consistente automaticamente
- ✅ Validar código antes de criar
- ✅ Sugerir melhorias baseadas nos padrões

### Para Desenvolvedores
- ✅ Documentação viva e sempre atualizada
- ✅ Validação automática de código
- ✅ Templates prontos para uso
- ✅ Consistência garantida no projeto

### Para o Projeto
- ✅ Código padronizado
- ✅ Menos bugs de integração
- ✅ Onboarding mais rápido
- ✅ Manutenção facilitada

---

## 📚 Arquivos de Referência

1. **MCP_BUILD_PATTERNS.md** - Documentação completa com todos os padrões
2. **TABLE_SYSTEM.md** - Sistema de tabelas
3. **BREADCRUMB_SYSTEM.md** - Sistema de breadcrumbs
4. **ROUTING.md** - Sistema de rotas
5. **HELPERS.md** - Helpers disponíveis

---

## 🎨 Exemplo de Uso Prático

### Cenário: Criar CRUD de Produtos

1. **IA analisa padrão existente**
```
Tool: analyze-controller
Input: { controller: "App\\Http\\Controllers\\UserController" }
Output: Estrutura completa do UserController
```

2. **IA gera novo controller**
```php
class ProductController extends LandlordController
{
    use InteractsWithRequests;
    
    protected string|null $navigationIcon = 'Package';
    protected string|null $navigationGroup = 'Produtos';
    
    protected function table(TableBuilder $table): TableBuilder
    {
        // ... gerado baseado no padrão do UserController
    }
}
```

3. **IA valida o código**
```
Tool: validate-action-pattern
Input: { action_class: "App\\Actions\\CustomExportAction", strict: true }
Output: ✅ Válido com sugestões de melhoria
```

4. **IA gera component Vue**
```
Tool: generate-component-template
Input: { type: "table", name: "ProductTable" }
Output: Template completo do componente
```

5. **IA sugere composables**
```
Tool: suggest-composable-usage
Input: { features: ["table", "filters", "export"] }
Output: useTable, useAction, useNotifications
```

6. **IA verifica integração**
```
Tool: check-integration-consistency
Input: { 
  controller: "App\\Http\\Controllers\\ProductController",
  vueComponent: "resources/js/views/products/List.vue"
}
Output: ✅ Integração consistente
```

---

## ⚙️ Configuração no Claude Desktop

```json
{
  "mcpServers": {
    "papa-leguas-patterns": {
      "command": "php",
      "args": [
        "/caminho/para/seu/projeto/artisan",
        "mcp:serve",
        "build-patterns"
      ]
    }
  }
}
```

---

## 📞 Suporte e Dúvidas

Para dúvidas sobre implementação:
1. Consulte **MCP_BUILD_PATTERNS.md**
2. Use as tools MCP para análise
3. Verifique os exemplos no código existente

---

**Status**: 📝 Pronto para implementação
**Prioridade**: 🔥 Alta (vai melhorar muito a qualidade do código gerado por IAs)
**Complexidade**: ⭐⭐⭐ Média (requer conhecimento de Reflection API e MCP)
