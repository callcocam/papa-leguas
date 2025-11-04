# Papa Leguas - Otimizações de Performance

## 📊 Resumo das Melhorias

O `PapaLeguasServiceProvider` foi completamente reescrito para otimizar performance e melhorar a organização do código. As principais melhorias incluem:

### 🚀 Otimizações Implementadas

#### 1. **DomainDetectionService**
- **Cache interno por request**: Evita reprocessamento da mesma detecção
- **Cache de configurações**: Reduz chamadas `config()` de múltiplas para uma única
- **Processamento otimizado de strings**: Evita manipulações desnecessárias
- **Detecção lazy**: Só processa quando necessário

#### 2. **Request Macros Otimizados**
- **Delegação para DomainDetectionService**: Usa cache interno
- **Separação de responsabilidades**: Código mais limpo e testável
- **Documentação completa**: PHPDoc detalhado para todos os macros

#### 3. **Service Provider Reorganizado**
- **Registro lazy**: MenuServiceProvider só é registrado quando necessário
- **Configuração condicional**: Fortify guards só configurados em contexto web
- **Cache de singletons**: Serviços reutilizados entre requests
- **Evita chamadas CLI**: Não executa lógica web em comandos Artisan

#### 4. **MenuServiceProvider Melhorado**
- **Contexto cachado**: Usa DomainDetectionService em vez de múltiplas calls
- **Métodos organizados**: Separação clara de responsabilidades
- **Documentação completa**: PHPDoc para todos os métodos

## 📈 Comparação de Performance

### Antes (Versão Original)
```php
// ❌ Problemas da versão anterior:
- request() chamado múltiplas vezes durante boot
- Lógica duplicada entre macros e singleton  
- config() chamado repetidamente sem cache
- MenuServiceProvider registrado sempre
- Processamento de strings redundante
```

### Depois (Versão Otimizada)
```php
// ✅ Melhorias da versão atual:
- Cache interno evita reprocessamento
- Serviços registrados como singletons
- Configurações cachadas
- Registro condicional de services
- Código limpo e bem documentado
```

## 🛠️ Como Usar os Novos Serviços

### DomainDetectionService

```php
use Callcocam\PapaLeguas\Services\DomainDetectionService;

// Injeção de dependência
public function __construct(DomainDetectionService $domainService)
{
    $this->domainService = $domainService;
}

// Uso em controllers
public function index(DomainDetectionService $domainService)
{
    if ($domainService->isTenant()) {
        // Lógica para tenant
    }
    
    if ($domainService->isLandlord()) {
        // Lógica para landlord
    }
    
    $context = $domainService->getContext(); // 'tenant', 'landlord' ou 'base'
}

// Via container
$domainService = app(DomainDetectionService::class);
$isSubdomain = $domainService->isSubdomain();
```

### Request Macros (Mantém Compatibilidade)

```php
// Os macros continuam funcionando como antes
if (request()->isTenant()) {
    // Código para tenant
}

if (request()->isLandlord()) {
    // Código para landlord
}

$context = request()->getContext();
$debugInfo = request()->getDomainDebugInfo();
```

### Debug e Monitoramento

```php
// Informações de debug
$debugInfo = app(DomainDetectionService::class)->getDebugInfo();
/*
Array:
[
    'host' => 'tenant.example.com',
    'hostname' => 'tenant', 
    'is_tenant' => true,
    'is_landlord' => false,
    'is_subdomain' => true,
    'context' => 'tenant',
    'config' => [...]
]
*/

// Limpar cache (útil em testes)
DomainDetectionService::clearCache();
```

## 🧪 Testes de Performance

### Benchmark Interno
```php
// Teste de performance do cache interno
$service = app(DomainDetectionService::class);

// Primeira chamada - processamento completo
$start = microtime(true);
$result1 = $service->isTenant();
$time1 = microtime(true) - $start;

// Segunda chamada - usa cache
$start = microtime(true);
$result2 = $service->isTenant();
$time2 = microtime(true) - $start;

// $time2 deve ser significativamente menor que $time1
```

## 🔧 Configurações Recomendadas

### Cache de Aplicação
Para máxima performance em produção, configure cache de aplicação:

```php
// config/cache.php
'stores' => [
    'domain_detection' => [
        'driver' => 'redis', // ou 'memcached'
        'connection' => 'default',
        'prefix' => 'domain_detection',
    ],
],
```

### Configurações do Menu Builder
```php
// config/menu-builder.php
'cache' => [
    'ttl' => 3600, // 1 hora
    'prefix' => 'menu_cache',
],
```

## 📝 Migração da Versão Anterior

### Código que Precisa Atualização

1. **Singleton `isNotSubdomain`**:
```php
// ❌ Antes
$isNotSubdomain = app('isNotSubdomain');

// ✅ Agora (recomendado)
$isNotSubdomain = app(DomainDetectionService::class)->isNotSubdomain();

// ✅ Ou (compatibilidade mantida)
$isNotSubdomain = app('isNotSubdomain'); // Ainda funciona
```

2. **Request Macros**:
```php
// ✅ Continua funcionando igual
request()->isTenant();
request()->isLandlord();
request()->getContext();
```

## 🚨 Troubleshooting

### Problemas Comuns

1. **Cache não limpa entre testes**:
```php
// Adicione no setUp() dos testes
DomainDetectionService::clearCache();
```

2. **Configuração não carrega**:
```php
// Verifique se as configurações estão corretas
config('landlord.base_domain');
config('landlord.landlord_subdomain');
config('landlord.local_domains');
```

3. **MenuServiceProvider não registra**:
```php
// Verifique se não está em contexto CLI
if (!app()->runningInConsole()) {
    // MenuServiceProvider será registrado
}
```

## 📋 Checklist de Performance

- [x] Cache interno implementado
- [x] Singletons registrados corretamente  
- [x] Configurações cachadas
- [x] Registro condicional de services
- [x] Documentação PHPDoc completa
- [x] Compatibilidade com versão anterior
- [x] Testes de performance incluídos
- [x] Guia de migração fornecido

## 🎯 Próximos Passos

1. **Monitoramento**: Implementar métricas de performance
2. **Cache Redis**: Configurar cache distribuído para clusters
3. **Lazy Loading**: Implementar carregamento ainda mais tardio
4. **Profiling**: Adicionar ferramentas de profiling detalhado