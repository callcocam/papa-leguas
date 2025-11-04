<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

/**
 * Configurações do Papa Leguas Package
 * 
 * Este arquivo contém todas as configurações personalizáveis do pacote
 * Papa Leguas, incluindo exclusões de rotas, domínios e contextos.
 */

return [
    
    /*
    |--------------------------------------------------------------------------
    | Prefixos Excluídos das Rotas SPA
    |--------------------------------------------------------------------------
    |
    | Lista de prefixos de URL que devem ser ignorados pela rota catch-all
    | do SPA Vue.js. Isso permite que APIs, webhooks e áreas administrativas
    | funcionem independentemente do sistema de tenants.
    |
    | Exemplos de uso:
    | - 'api' -> ignora /api/users, /api/v1/products, etc.
    | - 'admin' -> ignora /admin/dashboard, /admin/users, etc.
    | - 'webhook' -> ignora /webhook/stripe, /webhook/paypal, etc.
    |
    */
    
    'excluded_prefixes' => [
        'api',        // APIs REST
        'admin',      // Área administrativa
        'webhook',    // Webhooks externos
        // 'docs',    // Documentação (descomente se necessário)
        // 'health',  // Health checks (descomente se necessário)
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Domínio
    |--------------------------------------------------------------------------
    |
    | Configurações relacionadas à detecção e validação de domínios
    | para o sistema multi-tenant.
    |
    */
    
    'domain' => [
        
        /*
        | Padrão de validação para subdomínios
        | Regex usado para validar se um subdomínio é válido
        */
        'subdomain_pattern' => '[a-zA-Z0-9\-]+',
        
        /*
        | Subdomínios reservados que não podem ser usados por tenants
        | Estes subdomínios são reservados para uso interno do sistema
        */
        'reserved_subdomains' => [
            'www',
            'mail',
            'ftp',
            'admin',
            'api',
            'app',
            'staging',
            'test',
            'dev',
            'demo',
        ],
        
        /*
        | Domínios locais para desenvolvimento
        | Lista de domínios considerados como ambiente local
        */
        'local_domains' => [
            'localhost',
            '127.0.0.1',
            'papa-leguas-02.test',
            'papa-leguas.local',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Cache
    |--------------------------------------------------------------------------
    |
    | Configurações de cache para otimização de performance do sistema
    | de detecção de domínio e contexto.
    |
    */
    
    'cache' => [
        
        /*
        | TTL (Time To Live) para cache de detecção de domínio
        | Em segundos. Use 0 para desabilitar cache.
        */
        'domain_detection_ttl' => env('PAPA_LEGUAS_CACHE_TTL', 3600), // 1 hora
        
        /*
        | Prefixo das chaves de cache
        */
        'prefix' => env('PAPA_LEGUAS_CACHE_PREFIX', 'papa_leguas'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Debug
    |--------------------------------------------------------------------------
    |
    | Configurações para debug e desenvolvimento do sistema.
    |
    */
    
    'debug' => [
        
        /*
        | Habilitar logs de debug
        | Quando habilitado, o sistema registrará informações detalhadas
        | sobre detecção de domínio e contexto nos logs.
        */
        'enabled' => env('PAPA_LEGUAS_DEBUG', false),
        
        /*
        | Canal de log para debug
        */
        'log_channel' => env('PAPA_LEGUAS_LOG_CHANNEL', 'daily'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Assets e Publicação
    |--------------------------------------------------------------------------
    |
    | Configurações relacionadas à publicação e versionamento de assets.
    |
    */
    
    'assets' => [

        /*
        | Diretório de origem dos assets compilados
        */
        'source_dir' => 'build',

        /*
        | Diretório de destino para publicação
        */
        'publish_dir' => 'vendor/papa-leguas',

        /*
        | Versioning dos assets
        | Quando habilitado, adiciona timestamp aos assets publicados
        */
        'versioning' => env('PAPA_LEGUAS_ASSETS_VERSIONING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Nomes de Tabelas
    |--------------------------------------------------------------------------
    |
    | Nomes das tabelas do banco de dados usadas pelo Papa Leguas.
    | Você pode customizar os nomes através de variáveis de ambiente.
    |
    */

    'tables' => [
        'tenants' => env('PAPA_LEGUAS_TABLE_TENANTS', 'tenants'),
        'users' => env('PAPA_LEGUAS_TABLE_USERS', 'users'),
        'roles' => env('PAPA_LEGUAS_TABLE_ROLES', 'roles'),
        'permissions' => env('PAPA_LEGUAS_TABLE_PERMISSIONS', 'permissions'),
        'role_user' => env('PAPA_LEGUAS_TABLE_ROLE_USER', 'role_user'),
        'permission_role' => env('PAPA_LEGUAS_TABLE_PERMISSION_ROLE', 'permission_role'),
        'permission_user' => env('PAPA_LEGUAS_TABLE_PERMISSION_USER', 'permission_user'),
        'addresses' => env('PAPA_LEGUAS_TABLE_ADDRESSES', 'addresses'),
        'personal_access_tokens' => env('PAPA_LEGUAS_TABLE_PERSONAL_ACCESS_TOKENS', 'personal_access_tokens'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Confirmação por Digitação na Exclusão
    |--------------------------------------------------------------------------
    |
    | Define se a exclusão de registros requer confirmação por digitação.
    | Quando ativado GLOBALMENTE, o usuário precisará digitar uma palavra
    | específica antes de confirmar QUALQUER exclusão.
    |
    | Nota: Isso pode ser sobrescrito por controller usando o método
    | requiresTypedConfirmation() no DeleteAction.
    |
    */

    'delete_requires_typed_confirmation' => env('PAPA_LEGUAS_DELETE_REQUIRES_TYPED_CONFIRMATION', false),

    /*
    |--------------------------------------------------------------------------
    | Palavra de Confirmação de Exclusão
    |--------------------------------------------------------------------------
    |
    | A palavra padrão que o usuário deve digitar para confirmar a exclusão.
    | Esta configuração global pode ser sobrescrita por controller.
    |
    */

    'delete_typed_confirmation_word' => env('PAPA_LEGUAS_DELETE_TYPED_CONFIRMATION_WORD', 'EXCLUIR'),
];
