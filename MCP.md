# Guia: MCP Servers para Papa Leguas Package

Este guia mostra como criar e configurar servidores MCP (Model Context Protocol) para o pacote Papa Leguas.

## 🏗️ Arquitetura do Sistema

### Backend (PHP)
- **Controllers**: Estendem `LandlordController` ou `AppController`
- **Actions**: Sistema de Actions fluente (`CreateAction`, `EditAction`, `DeleteAction`, etc.)
- **TableBuilder**: Builder para construção de tabelas com colunas, filtros e ações
- **FormBuilder**: Builder para construção de formulários
- **InfoList**: Builder para exibição de informações
- **Responses**: `JsonResponse` para respostas padronizadas

### Frontend (Vue 3)
- **Composables**: `useTable`, `useAction`, `useAuth`, `useBreadcrumbs`, `useListLayout`, etc.
- **Components**: Baseados em ShadCN-Vue
- **Types**: TypeScript interfaces completas para tipo-segurança
- **Layout**: Sistema de grid responsivo com `useListLayout`

**Importante**: O sistema **NÃO usa Inertia.js**, mas uma arquitetura própria com comunicação via API REST + JSON.

---

## 📚 Documentação Disponível

- **[MCP_BUILD_PATTERNS.md](MCP_BUILD_PATTERNS.md)** - Padrões completos de construção e ferramentas MCP propostas
- **[MCP_IMPLEMENTATION_PLAN.md](MCP_IMPLEMENTATION_PLAN.md)** - Plano de implementação detalhado
- **[TABLE_SYSTEM.md](TABLE_SYSTEM.md)** - Sistema de tabelas integrado
- **[BREADCRUMB_SYSTEM.md](BREADCRUMB_SYSTEM.md)** - Sistema de breadcrumbs dinâmico
- **[ROUTING.md](ROUTING.md)** - Sistema de rotas
- **[HELPERS.md](HELPERS.md)** - Helpers disponíveis

---

## 🚀 Quick Start

### 1. Instalação do Laravel MCP

```bash
# Instalar o pacote Laravel MCP
composer require laravel/mcp

# Publicar configurações (se necessário)
php artisan vendor:publish --tag=mcp-config
```

### 2. Criar um MCP Server

```bash
# Exemplo: Servidor para padrões de construção
php artisan make:mcp-server BuildPatternsServer
```

Isso criará o arquivo `app/Mcp/Servers/BuildPatternsServer.php`

### 3. Configurar o Servidor

Estrutura básica de um servidor MCP:

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
        \App\Mcp\Tools\AnalyzeControllerTool::class,
        \App\Mcp\Tools\ValidateActionPatternTool::class,
        \App\Mcp\Tools\GenerateComponentTemplateTool::class,
    ];
    
    protected array $resources = [
        \App\Mcp\Resources\BuildPatternsResource::class,
    ];
}
```

### 4. Criar Ferramentas (Tools)

As ferramentas MCP executam operações específicas. Exemplo:

```bash
php artisan make:mcp-tool AnalyzeControllerTool
```

Exemplo de Tool:

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
        'Analisa um controller e retorna sua estrutura, actions, table config, etc.';
    
    public function inputSchema(): JsonSchema
    {
        return JsonSchema::new()
            ->type('object')
            ->properties([
                'controller' => JsonSchema::new()
                    ->type('string')
                    ->description('Nome completo do controller'),
            ])
            ->required(['controller']);
    }
    
    public function handle(Request $request): Response
    {
        $controllerClass = $request->string('controller');
        
        if (!class_exists($controllerClass)) {
            return Response::error("Controller {$controllerClass} não encontrado.");
        }
        
        // Análise usando Reflection
        $reflection = new \ReflectionClass($controllerClass);
        
        $analysis = [
            'name' => $reflection->getShortName(),
            'namespace' => $reflection->getNamespaceName(),
            'extends' => $reflection->getParentClass()?->getName(),
            'traits' => array_map(
                fn($trait) => $trait->getName(),
                $reflection->getTraits()
            ),
            // ... mais análises
        ];
        
        return Response::text(
            "# Controller Analysis\n\n" .
            json_encode($analysis, JSON_PRETTY_PRINT)
        );
    }
}
```

### 5. Criar Resources

Resources fornecem documentação e contexto:

```bash
php artisan make:mcp-resource BuildPatternsResource
```

Exemplo:

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
        $docs = file_get_contents(
            base_path('packages/callcocam/papa-leguas/MCP_BUILD_PATTERNS.md')
        );
        
        return Response::text($docs);
    }
}
```

### 6. Registrar Rotas

Em `routes/ai.php`:

```php
<?php

use Laravel\Mcp\Facades\Mcp;
use App\Mcp\Servers\BuildPatternsServer;

// Servidor Web (autenticado)
Mcp::web('/mcp/build-patterns', BuildPatternsServer::class)
    ->middleware(['auth:sanctum']);

// Servidor Local (STDIO para Claude Desktop)
Mcp::local('build-patterns', BuildPatternsServer::class);
```

---

## 🧪 Testando o MCP Server

### Via Inspector (Web)

```bash
# Inicia o inspector web
php artisan mcp:inspector build-patterns
```

Acesse o navegador no endereço indicado para testar as tools interativamente.

### Via CLI

```bash
# Lista todos os servidores MCP
php artisan mcp:list

# Serve o servidor via STDIO
php artisan mcp:serve build-patterns
```

### Via Claude Desktop

Adicione ao arquivo de configuração do Claude Desktop (`claude_desktop_config.json`):

```json
{
  "mcpServers": {
    "papa-leguas-patterns": {
      "command": "php",
      "args": [
        "/caminho/completo/para/seu/projeto/artisan",
        "mcp:serve",
        "build-patterns"
      ]
    }
  }
}
```

**Localizações do arquivo:**
- **Windows**: `%APPDATA%\Claude\claude_desktop_config.json`
- **macOS**: `~/Library/Application Support/Claude/claude_desktop_config.json`
- **Linux**: `~/.config/Claude/claude_desktop_config.json`

Após adicionar, reinicie o Claude Desktop e o servidor MCP estará disponível.

---

## 📝 Testes Unitários

Crie testes para suas tools:

```php
<?php

namespace Tests\Feature\Mcp;

use Tests\TestCase;
use App\Mcp\Servers\BuildPatternsServer;
use App\Mcp\Tools\AnalyzeControllerTool;

class BuildPatternsServerTest extends TestCase
{
    public function test_can_analyze_controller(): void
    {
        $response = BuildPatternsServer::tool(
            AnalyzeControllerTool::class,
            ['controller' => 'App\\Http\\Controllers\\UserController']
        );

        $response->assertOk();
        $this->assertStringContainsString('UserController', $response->content);
    }
}
```

Execute os testes:

```bash
php artisan test --filter BuildPatternsServerTest
```

---

## 🎯 Casos de Uso

### 1. Análise de Padrões

```
User: "Analise o UserController"
IA: usa analyze-controller tool
Result: Estrutura completa do controller
```

### 2. Validação de Código

```
User: "Valide se minha CustomAction está correta"
IA: usa validate-action-pattern tool
Result: Lista de erros, avisos e sugestões
```

### 3. Geração de Templates

```
User: "Crie um componente de tabela para produtos"
IA: usa generate-component-template tool
Result: Template Vue com props, emits e composables corretos
```

### 4. Verificação de Integração

```
User: "Verifique a integração entre ProductController e ProductList.vue"
IA: usa check-integration-consistency tool
Result: Lista de inconsistências e sugestões de correção
```

---

## 🔧 Comandos Úteis

```bash
# Listar todos os servidores MCP
php artisan mcp:list

# Executar o inspector para testar
php artisan mcp:inspector build-patterns

# Servir o MCP (STDIO)
php artisan mcp:serve build-patterns

# Limpar cache de rotas
php artisan route:clear

# Ver todas as rotas MCP
php artisan route:list --name=mcp

# Criar novo servidor
php artisan make:mcp-server NomeDoServidor

# Criar nova tool
php artisan make:mcp-tool NomeDaTool

# Criar novo resource
php artisan make:mcp-resource NomeDoResource
```

---

## 🛡️ Segurança

### Autenticação

Sempre use autenticação nos endpoints MCP em produção:

```php
Mcp::web('/mcp/build-patterns', BuildPatternsServer::class)
    ->middleware(['auth:sanctum']);
```

### Rate Limiting

Adicione rate limiting às rotas da API:

```php
Route::middleware(['throttle:60,1'])->group(function () {
    // Rotas MCP
});
```

### Validação de Entrada

Sempre valide as entradas nas tools:

```php
public function handle(Request $request): Response
{
    $request->validate([
        'controller' => 'required|string',
    ]);
    
    // ... resto do código
}
```

---

## 📖 Próximos Passos

1. ✅ Leia **[MCP_BUILD_PATTERNS.md](MCP_BUILD_PATTERNS.md)** para entender todos os padrões
2. ✅ Veja **[MCP_IMPLEMENTATION_PLAN.md](MCP_IMPLEMENTATION_PLAN.md)** para o plano completo
3. ✅ Implemente as tools propostas
4. ✅ Teste com o Inspector
5. ✅ Configure no Claude Desktop
6. ✅ Escreva testes unitários

---

## 🤝 Contribuindo

Para adicionar novas tools ou melhorar as existentes:

1. Crie a tool seguindo os padrões
2. Adicione testes unitários
3. Documente no `MCP_BUILD_PATTERNS.md`
4. Atualize este guia se necessário

---

## 📞 Suporte

- **Documentação Completa**: [MCP_BUILD_PATTERNS.md](MCP_BUILD_PATTERNS.md)
- **Laravel MCP Docs**: https://laravel.com/docs/mcp
- **Model Context Protocol**: https://modelcontextprotocol.io/

---

**Versão**: 1.0.0  
**Última Atualização**: Novembro 2025
