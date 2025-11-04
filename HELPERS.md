# Papa Leguas - Funções Helper

## 📚 Visão Geral

O pacote Papa Leguas inclui diversas funções helper que facilitam o desenvolvimento e integração com o sistema multi-tenant. Estas funções são carregadas automaticamente pelo Composer.

## 🌐 Helpers de Contexto

### `current_context()`
Obtém o contexto atual da aplicação.

```php
$context = current_context(); // 'landlord', 'tenant' ou 'base'
```

### `is_landlord()` / `is_tenant()` / `is_subdomain()`
Verificações rápidas de contexto.

```php
if (is_landlord()) {
    // Lógica para administradores
}

if (is_tenant()) {
    // Lógica para tenants
}

if (is_subdomain()) {
    // Está em um subdomínio
}
```

### `current_tenant()`
Obtém o tenant atual (com cache automático).

```php
$tenant = current_tenant();
if ($tenant) {
    echo "Tenant: {$tenant->name}";
}
```

## 🏠 Helpers de Domínio

### `get_base_domain()`
Obtém o domínio base da aplicação.

```php
$domain = get_base_domain(); // 'papa-leguas.com'
```

### `get_subdomain()`
Extrai o subdomínio do request atual.

```php
$subdomain = get_subdomain(); // 'cliente1' de 'cliente1.papa-leguas.com'
```

### `build_tenant_url()`
Constrói URLs para tenants específicos.

```php
$url = build_tenant_url('cliente1', '/dashboard');
// https://cliente1.papa-leguas.com/dashboard

$url = build_tenant_url('demo', '/api/users', false);
// http://demo.papa-leguas.com/api/users
```

### `is_valid_subdomain()`
Valida formato de subdomínio.

```php
if (is_valid_subdomain('cliente-1')) {
    // Subdomínio válido
}

if (!is_valid_subdomain('www')) {
    // Subdomínio reservado
}
```

## ⚙️ Helpers de Configuração

### `papa_config()`
Acesso rápido às configurações do pacote.

```php
$debug = papa_config('debug.enabled'); // config('papa-leguas.debug.enabled')
$prefix = papa_config('cache.prefix', 'default_prefix');
$allConfig = papa_config(); // Todas as configurações
```

### `tenant_config()`
Configurações específicas do tenant atual.

```php
$timezone = tenant_config('timezone', 'UTC');
$logo = tenant_config('branding.logo');
$settings = tenant_config(); // Todas as configurações do tenant
```

## 🎨 Helpers de Assets

### `papa_asset()`
URLs para assets do pacote (com versionamento automático).

```php
$cssUrl = papa_asset('app.css');
// /vendor/papa-leguas/app.css?v=1642681234

$jsUrl = papa_asset('app.js');
// /vendor/papa-leguas/app.js?v=1642681234
```

### `tenant_asset()`
Assets específicos do tenant (com fallback).

```php
$logo = tenant_asset('logo.png');
// Tenta: /tenants/cliente1/logo.png
// Fallback: /vendor/papa-leguas/logo.png
```

## 🛣️ Helpers de Rotas

### `tenant_route()` / `landlord_route()`
Gera rotas para contextos específicos.

```php
$dashboardUrl = tenant_route('dashboard');
// route('tenant.dashboard')

$adminUrl = landlord_route('users.index');
// route('landlord.users.index')
```

### `api_route()`
Rotas API com contexto automático.

```php
$apiUrl = api_route('users.index');
// Se tenant: route('tenant.api.users.index')
// Se landlord: route('landlord.api.users.index')
// Fallback: route('api.users.index')
```

## 🔧 Helpers de Utilitários

### `papa_cache()`
Cache com prefixo do pacote.

```php
// Salvar no cache
papa_cache('user_data', $userData, 3600);

// Recuperar do cache
$userData = papa_cache('user_data');
```

### `sanitize_subdomain()`
Limpa e valida subdomínios.

```php
$clean = sanitize_subdomain('Cliente@123!');
// 'cliente123'

$clean = sanitize_subdomain('---test---');
// 'test'
```

### `generate_tenant_slug()`
Gera slug único para tenant.

```php
$slug = generate_tenant_slug('Empresa XYZ Ltda');
// 'empresa-xyz-ltda'

$slug = generate_tenant_slug('Test Company');
// 'test-company' ou 'test-company-1' se já existir
```

### `mask_sensitive_data()`
Mascara dados sensíveis para logs.

```php
$masked = mask_sensitive_data('12345678901', 2);
// '12*******01'

$masked = mask_sensitive_data('password123', 1, '#');
// 'p#########3'
```

## 🐛 Helpers de Debug

### `papa_debug()`
Debug específico do Papa Leguas (respeitando configurações).

```php
papa_debug($userData, 'User login attempt');
papa_debug(['key' => 'value']);
```

### Debug automático inclui:
- Timestamp
- Contexto atual
- Subdomínio
- ID do tenant
- Label personalizada
- Dados fornecidos

## 🕐 Helpers de Timezone

### `get_tenant_timezone()` / `tenant_now()`
Trabalha com timezone do tenant.

```php
$timezone = get_tenant_timezone(); // 'America/Sao_Paulo'
$now = tenant_now(); // Carbon no timezone do tenant
```

## 📝 Helpers de Formatação

### `format_tenant_name()`
Formata nomes de tenant para exibição.

```php
$formatted = format_tenant_name('empresa xyz ltda', 20);
// 'Empresa Xyz Ltda'
```

## 🔒 Segurança

- Todas as funções são protegidas com `function_exists()`
- Cache automático para performance
- Validação de entrada quando aplicável
- Logs seguros com mascaramento de dados sensíveis

## 📖 Exemplos Práticos

### Middleware customizado usando helpers:
```php
public function handle($request, Closure $next)
{
    if (is_tenant()) {
        $tenant = current_tenant();
        if (!$tenant || !$tenant->isActive()) {
            abort(404);
        }
    }
    
    return $next($request);
}
```

### Controller usando helpers:
```php
public function dashboard()
{
    $context = current_context();
    $assets = [
        'css' => papa_asset('dashboard.css'),
        'js' => papa_asset('dashboard.js'),
        'logo' => tenant_asset('logo.png'),
    ];
    
    papa_debug($assets, 'Dashboard assets loaded');
    
    return view('dashboard', compact('context', 'assets'));
}
```

### Blade usando helpers:
```blade
@if(is_tenant())
    <h1>{{ format_tenant_name(current_tenant()->name) }}</h1>
    <img src="{{ tenant_asset('logo.png') }}" alt="Logo">
@endif

<script src="{{ papa_asset('app.js') }}"></script>
```