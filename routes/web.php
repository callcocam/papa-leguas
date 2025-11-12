<?php

use Illuminate\Support\Facades\Route;
use Callcocam\PapaLeguas\Http\Controllers\AppController;

/**
 * Rotas para subdomínios (tenants) - Papa Leguas
 * 
 * Estas rotas são ativadas apenas para subdomínios, permitindo que cada tenant
 * tenha sua própria instância da aplicação Papa Leguas.
 * 
 * O domínio base é obtido dinamicamente das configurações da aplicação,
 * suportando diferentes ambientes (local, staging, produção).
 * 
 * Pattern: {subdomain}.domain.com
 * Exemplos: 
 * - tenant1.papa-leguas.com
 * - cliente-x.papa-leguas.com  
 * - demo.localhost (desenvolvimento)
 */

/**
 * Obtém o domínio base dinamicamente das configurações.
 * 
 * Prioridade:
 * 1. landlord.base_domain (configuração específica multi-tenant)
 * 2. app.url (configuração padrão da aplicação)
 * 3. Fallback para localhost em desenvolvimento
 */
if (!function_exists('getBaseWebDomain')) {
    function getBaseWebDomain(): string
    {
        // Primeiro tenta a configuração específica do landlord
        $baseDomain = config('landlord.base_domain');

        // Se não existir, usa a URL da aplicação
        if (!$baseDomain) {
            $baseDomain = config('app.url');
        }

        // Se ainda não existir, fallback para localhost
        if (!$baseDomain) {
            $baseDomain = 'http://localhost';
        }

        // Extrai apenas o host (remove protocolo e porta)
        $host = parse_url($baseDomain, PHP_URL_HOST);

        // Remove 'www.' se existir
        return str_replace('www.', '', $host ?: 'localhost');
    }
}

/**
 * Gera o padrão regex para excluir prefixos específicos.
 * 
 * @param array $excludedPrefixes Lista de prefixos a serem ignorados
 * @return string Padrão regex para usar no where()
 */
if (!function_exists('getExclusionPattern')) {
    function getExclusionPattern(array $excludedPrefixes = ['api']): string
    {
        if (empty($excludedPrefixes)) {
            return '.*'; // Se nenhum prefixo, aceita tudo
        }

        // Escapa caracteres especiais e adiciona barra final
        $escapedPrefixes = array_map(function ($prefix) {
            return preg_quote(trim($prefix, '/'), '/') . '/';
        }, $excludedPrefixes);

        // Cria padrão regex negativo
        return '^(?!' . implode('|', $escapedPrefixes) . ').*';
    }
}

// Obtém o domínio base dinamicamente
$baseDomain = getBaseWebDomain(); 
// Define prefixos a serem ignorados (configurável)
$excludedPrefixes = config('papa-leguas.excluded_prefixes', ['api', 'admin', 'webhook']);

// Rotas para subdomínios (tenants)
Route::domain(sprintf('{subdomain}.%s', $baseDomain))
    ->where(['subdomain' => '[a-zA-Z0-9\-]+']) // Valida formato do subdomínio
    ->middleware('web')
    ->group(function () use ($excludedPrefixes) {
        // Rota catch-all para SPA Vue.js (ignora prefixos configurados)
        Route::get('/{any?}', AppController::class)
            ->where('any', getExclusionPattern($excludedPrefixes))
            ->name('app');
    });
