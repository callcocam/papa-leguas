<?php

/**
 *  Created by Claudio Campos.
 *  User: callcocam@gmail.com, contato@sigasmart.com.br
 *  https://www.sigasmart.com.br
 */
return [
    'default_tenant_columns' => ['tenant_id'],

    /**
     * Prefixos de rotas que devem ser ignorados pelo controle de tenants
     * Quando uma rota começar com qualquer um desses prefixos,
     * o sistema não tentará detectar/aplicar tenant scoping
     */
    'excluded_prefixes' => [
        'landlord',
        'tenant-not-found',
        'horizon',
        'telescope',
        'sanctum',
        '_ignition',
    ],
    'base_domain' => env('APP_BASE_DOMAIN', 'example.com'), // Domínio base
    'landlord_subdomain' => env('APP_LANDLORD_SUBDOMAIN', 'landlord'), // Subdomínio para landlord
    'local_domains' => explode(',', env('APP_LOCAL_DOMAINS', 'localhost,127.0.0.1')),
];
