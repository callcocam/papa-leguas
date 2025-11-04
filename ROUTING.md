# Papa Leguas - Configuração de Rotas Dinâmicas

## 🌐 Detecção Dinâmica de Domínio

O sistema agora detecta automaticamente o domínio base das configurações da aplicação, suportando diferentes ambientes sem necessidade de hardcode.

### 📋 Prioridade de Configuração

```php
1. config('landlord.base_domain')    // Configuração específica multi-tenant
2. config('app.url')                 // Configuração padrão Laravel
3. 'localhost'                       // Fallback para desenvolvimento
```

## ⚙️ Configurações por Ambiente

### Desenvolvimento Local
```env
# .env.local
APP_URL=http://papa-leguas-02.test
LANDLORD_BASE_DOMAIN=papa-leguas-02.test
```

**Rotas geradas:**
- `{subdomain}.papa-leguas-02.test` → Controller do tenant
- Exemplo: `cliente1.papa-leguas-02.test`

### Staging
```env
# .env.staging  
APP_URL=https://staging.papa-leguas.com
LANDLORD_BASE_DOMAIN=staging.papa-leguas.com
```

**Rotas geradas:**
- `{subdomain}.staging.papa-leguas.com` → Controller do tenant
- Exemplo: `demo.staging.papa-leguas.com`

### Produção
```env
# .env.production
APP_URL=https://papa-leguas.com
LANDLORD_BASE_DOMAIN=papa-leguas.com
```

**Rotas geradas:**
- `{subdomain}.papa-leguas.com` → Controller do tenant
- Exemplo: `empresa-x.papa-leguas.com`

## 🔧 Configuração Manual

Se precisar configurar manualmente, edite o arquivo de configuração:

```php
// config/landlord.php
return [
    'base_domain' => env('LANDLORD_BASE_DOMAIN', 'papa-leguas.com'),
    'landlord_subdomain' => env('LANDLORD_SUBDOMAIN', 'admin'),
    'local_domains' => [
        'localhost',
        'papa-leguas-02.test',
        '127.0.0.1',
    ],
];
```

## 🧪 Testando as Rotas

### 1. Verificar domínio detectado:
```php
// Em um controller ou tinker
$baseDomain = app(\Callcocam\PapaLeguas\Services\DomainDetectionService::class)
    ->getDebugInfo();
    
dd($baseDomain);
```

### 2. Listar rotas registradas:
```bash
php artisan route:list --name=tenant
```

### 3. Teste manual no browser:
```
# Para desenvolvimento local
http://teste.papa-leguas-02.test

# Para produção  
https://cliente1.papa-leguas.com
```

## 🎯 Validação de Subdomínio

O sistema valida o formato do subdomínio usando regex:

```php
'subdomain' => '[a-zA-Z0-9\-]+'
```

**Permitido:**
- `cliente1`
- `empresa-x`
- `demo123`

**Não permitido:**
- `cliente_1` (underscore)
- `empresa.x` (ponto)
- `123-` (termina com hífen)

## 🔀 Rotas para Landlord (Opcional)

Se quiser que o domínio principal também sirva a aplicação, descomente no arquivo de rotas:

```php
Route::domain($baseDomain)
    ->name('landlord.')
    ->group(function () {
        Route::get('/{any?}', AppController::class)
            ->where('any', '.*')
            ->name('app');
    });
```

Isso permitirá acessar:
- `papa-leguas.com` → Área administrativa
- `cliente1.papa-leguas.com` → Aplicação do tenant

## 🚀 Cache de Rotas

Para melhor performance em produção:

```bash
# Gerar cache de rotas
php artisan route:cache

# Limpar cache se necessário
php artisan route:clear
```

## 🐛 Troubleshooting

### Problema: Rota não encontrada
```bash
# Verificar se o domínio está correto
php artisan tinker
>>> config('landlord.base_domain')
>>> config('app.url')
```

### Problema: Subdomínio não válido
Verificar se o subdomínio atende ao regex `[a-zA-Z0-9\-]+`

### Problema: Cache de rotas
```bash
php artisan route:clear
php artisan config:clear
php artisan route:cache
```

## 🚫 Ignorando Rotas da API

O sistema ignora automaticamente rotas que começam com `api/` para permitir que APIs funcionem independentemente do sistema de tenants.

### Implementação Atual
```php
Route::get('/{any?}', AppController::class)
    ->where('any', '^(?!api/).*') // Regex negativa: NÃO começar com 'api/'
    ->name('app');
```

### Como Funciona
- ✅ `cliente1.domain.com/dashboard` → AppController (SPA)
- ✅ `cliente1.domain.com/produtos/123` → AppController (SPA)
- ❌ `cliente1.domain.com/api/users` → Ignorado (para rotas API)
- ❌ `cliente1.domain.com/api/v1/produtos` → Ignorado (para rotas API)

### Outras Opções de Implementação

#### Opção 1: Múltiplos Prefixos (Atual + Ampliado)
```php
// Ignora api/, admin/, webhook/
Route::get('/{any?}', AppController::class)
    ->where('any', '^(?!api/|admin/|webhook/).*')
    ->name('app');
```

#### Opção 2: Lista de Exclusões
```php
$excludedPrefixes = ['api', 'admin', 'webhook', 'docs'];
$pattern = '^(?!' . implode('/|', $excludedPrefixes) . '/).*';

Route::get('/{any?}', AppController::class)
    ->where('any', $pattern)
    ->name('app');
```

#### Opção 3: Configurável via Config
```php
// config/papa-leguas.php
'excluded_prefixes' => ['api', 'admin', 'webhook'],

// routes/web.php
$excludedPrefixes = config('papa-leguas.excluded_prefixes', ['api']);
$pattern = '^(?!' . implode('/|', $excludedPrefixes) . '/).*';

Route::get('/{any?}', AppController::class)
    ->where('any', $pattern)
    ->name('app');
```

### Rotas API Dedicadas

Para rotas API específicas por tenant, você pode criar um grupo separado:

```php
// Rotas API específicas por tenant (opcional)
Route::domain(sprintf('{subdomain}.%s', $baseDomain))
    ->prefix('api')
    ->name('tenant.api.')
    ->group(function () {
        // Suas rotas API aqui
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/products', [ProductController::class, 'index']);
    });
```

## 📊 Monitoramento

Para debug, você pode adicionar um middleware que loga as informações:

```php
// Em um middleware
Log::info('Domain Detection', [
    'host' => request()->getHost(),
    'base_domain' => config('landlord.base_domain'),
    'is_tenant' => request()->isTenant(),
    'context' => request()->getContext(),
    'route_name' => request()->route()?->getName(),
]);
```

## 🧪 Testando Exclusões

```bash
# Estas URLs devem funcionar (SPA)
curl -H "Host: cliente1.domain.com" http://localhost/dashboard
curl -H "Host: cliente1.domain.com" http://localhost/produtos/123

# Estas URLs devem retornar 404 (ignoradas)
curl -H "Host: cliente1.domain.com" http://localhost/api/users
curl -H "Host: cliente1.domain.com" http://localhost/api/v1/data
```