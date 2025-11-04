<?php

/**
 * Papa Leguas Package Helpers
 * 
 * Funções utilitárias para facilitar o desenvolvimento e integração
 * com o pacote Papa Leguas. Inclui helpers para contexto, domínio,
 * configurações, assets, rotas e utilitários gerais.
 * 
 * @package Callcocam\PapaLeguas
 * @author Claudio Campos <callcocam@gmail.com>
 */

use Callcocam\PapaLeguas\Services\DomainDetectionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

if (!function_exists('current_context')) {
    /**
     * Obtém o contexto atual da aplicação (landlord, tenant ou base).
     * 
     * @return string
     */
    function current_context(): string
    {
        return app(DomainDetectionService::class)->getContext();
    }
}

if (!function_exists('is_landlord')) {
    /**
     * Verifica se o contexto atual é landlord.
     * 
     * @return bool
     */
    function is_landlord(): bool
    {
        return app(DomainDetectionService::class)->isLandlord();
    }
}

if (!function_exists('is_tenant')) {
    /**
     * Verifica se o contexto atual é tenant.
     * 
     * @return bool
     */
    function is_tenant(): bool
    {
        return app(DomainDetectionService::class)->isTenant();
    }
}

if (!function_exists('is_subdomain')) {
    /**
     * Verifica se o request atual é de um subdomínio.
     * 
     * @return bool
     */
    function is_subdomain(): bool
    {
        return app(DomainDetectionService::class)->isSubdomain();
    }
}

if (!function_exists('current_tenant')) {
    /**
     * Obtém o tenant atual baseado no subdomínio.
     * 
     * @return \Callcocam\PapaLeguas\Models\Tenant|null
     */
    function current_tenant()
    {
        if (!is_tenant()) {
            return null;
        }

        $subdomain = get_subdomain();
        if (!$subdomain) {
            return null;
        }

        return Cache::remember(
            "papa_leguas_tenant_{$subdomain}",
            config('papa-leguas.cache.domain_detection_ttl', 3600),
            fn() => \Callcocam\PapaLeguas\Models\Tenant::where('subdomain', $subdomain)->first()
        );
    }
}

if (!function_exists('get_base_domain')) {
    /**
     * Obtém o domínio base da aplicação.
     * 
     * @return string
     */
    function get_base_domain(): string
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

if (!function_exists('get_subdomain')) {
    /**
     * Extrai o subdomínio do request atual.
     * 
     * @return string|null
     */
    function get_subdomain(): ?string
    {
        $host = request()->getHost();
        $baseDomain = get_base_domain();
        
        // Remove o domínio base para obter apenas o subdomínio
        $hostWithoutBase = str_replace(".{$baseDomain}", '', $host);
        
        // Se não mudou, significa que não há subdomínio
        if ($hostWithoutBase === $host || $hostWithoutBase === $baseDomain) {
            return null;
        }
        
        return $hostWithoutBase;
    }
}

if (!function_exists('build_tenant_url')) {
    /**
     * Constrói URL para um tenant específico.
     * 
     * @param string $subdomain
     * @param string $path
     * @param bool $secure
     * @return string
     */
    function build_tenant_url(string $subdomain, string $path = '/', bool $secure = null): string
    {
        $scheme = $secure ?? request()->isSecure() ? 'https' : 'http';
        $baseDomain = get_base_domain();
        $path = ltrim($path, '/');
        
        return "{$scheme}://{$subdomain}.{$baseDomain}/" . $path;
    }
}

if (!function_exists('is_valid_subdomain')) {
    /**
     * Valida se um subdomínio tem formato válido.
     * 
     * @param string $subdomain
     * @return bool
     */
    function is_valid_subdomain(string $subdomain): bool
    {
        $pattern = config('papa-leguas.domain.subdomain_pattern', '[a-zA-Z0-9\-]+');
        $reserved = config('papa-leguas.domain.reserved_subdomains', []);
        
        // Verifica formato
        if (!preg_match("/^{$pattern}$/", $subdomain)) {
            return false;
        }
        
        // Verifica se não é reservado
        if (in_array(strtolower($subdomain), array_map('strtolower', $reserved))) {
            return false;
        }
        
        return true;
    }
}

if (!function_exists('papa_config')) {
    /**
     * Acesso rápido às configurações do pacote Papa Leguas.
     * 
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function papa_config(?string $key = null, $default = null)
    {
        if ($key === null) {
            return config('papa-leguas');
        }
        
        return config("papa-leguas.{$key}", $default);
    }
}

if (!function_exists('tenant_config')) {
    /**
     * Obtém configurações específicas do tenant atual.
     * 
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function tenant_config(?string $key = null, $default = null)
    {
        $tenant = current_tenant();
        
        if (!$tenant) {
            return $default;
        }
        
        $settings = $tenant->settings ?? [];
        
        if ($key === null) {
            return $settings;
        }
        
        return data_get($settings, $key, $default);
    }
}

if (!function_exists('papa_asset')) {
    /**
     * Gera URLs para assets do pacote Papa Leguas.
     * 
     * @param string $path
     * @param bool $secure
     * @return string
     */
    function papa_asset(string $path, bool $secure = null): string
    {
        $publishDir = papa_config('assets.publish_dir', 'vendor/papa-leguas');
        $versioning = papa_config('assets.versioning', true);
        
        $assetPath = "{$publishDir}/{$path}";
        
        if ($versioning && file_exists(public_path($assetPath))) {
            $timestamp = filemtime(public_path($assetPath));
            $assetPath .= "?v={$timestamp}";
        }
        
        return asset($assetPath, $secure);
    }
}

if (!function_exists('tenant_asset')) {
    /**
     * Gera URLs para assets específicos do tenant.
     * 
     * @param string $path
     * @param bool $secure
     * @return string
     */
    function tenant_asset(string $path, bool $secure = null): string
    {
        $tenant = current_tenant();
        
        if (!$tenant) {
            return papa_asset($path, $secure);
        }
        
        $tenantAssetPath = "tenants/{$tenant->subdomain}/{$path}";
        
        // Verifica se existe asset específico do tenant
        if (file_exists(public_path($tenantAssetPath))) {
            return asset($tenantAssetPath, $secure);
        }
        
        // Fallback para asset padrão
        return papa_asset($path, $secure);
    }
}

if (!function_exists('tenant_route')) {
    /**
     * Gera rotas para o contexto tenant.
     * 
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    function tenant_route(string $name, array $parameters = [], bool $absolute = true): string
    {
        $routeName = "tenant.{$name}";
        
        if (!Route::has($routeName)) {
            throw new InvalidArgumentException("Route [{$routeName}] not defined.");
        }
        
        return route($routeName, $parameters, $absolute);
    }
}

if (!function_exists('landlord_route')) {
    /**
     * Gera rotas para o contexto landlord.
     * 
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    function landlord_route(string $name, array $parameters = [], bool $absolute = true): string
    {
        $routeName = "landlord.{$name}";
        
        if (!Route::has($routeName)) {
            throw new InvalidArgumentException("Route [{$routeName}] not defined.");
        }
        
        return route($routeName, $parameters, $absolute);
    }
}

if (!function_exists('api_route')) {
    /**
     * Gera rotas API com contexto automático.
     * 
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    function api_route(string $name, array $parameters = [], bool $absolute = true): string
    {
        $context = current_context();
        $routeName = "{$context}.api.{$name}";
        
        if (!Route::has($routeName)) {
            // Fallback para rota sem contexto
            $routeName = "api.{$name}";
            
            if (!Route::has($routeName)) {
                throw new InvalidArgumentException("Route [{$routeName}] not defined.");
            }
        }
        
        return route($routeName, $parameters, $absolute);
    }
}

if (!function_exists('papa_cache')) {
    /**
     * Cache com prefixo do pacote Papa Leguas.
     * 
     * @param string $key
     * @param mixed $value
     * @param int|\DateTimeInterface|\DateInterval|null $ttl
     * @return mixed
     */
    function papa_cache(string $key, $value = null, $ttl = null)
    {
        $prefix = papa_config('cache.prefix', 'papa_leguas');
        $cacheKey = "{$prefix}:{$key}";
        
        if ($value === null) {
            return Cache::get($cacheKey);
        }
        
        $ttl = $ttl ?? papa_config('cache.domain_detection_ttl', 3600);
        
        return Cache::put($cacheKey, $value, $ttl);
    }
}

if (!function_exists('sanitize_subdomain')) {
    /**
     * Limpa e valida um subdomínio.
     * 
     * @param string $subdomain
     * @return string
     */
    function sanitize_subdomain(string $subdomain): string
    {
        // Converte para minúsculas
        $subdomain = strtolower($subdomain);
        
        // Remove caracteres especiais, mantendo apenas alfanuméricos e hífens
        $subdomain = preg_replace('/[^a-z0-9\-]/', '', $subdomain);
        
        // Remove hífens consecutivos
        $subdomain = preg_replace('/\-+/', '-', $subdomain);
        
        // Remove hífens do início e fim
        $subdomain = trim($subdomain, '-');
        
        return $subdomain;
    }
}

if (!function_exists('generate_tenant_slug')) {
    /**
     * Gera slug único para tenant baseado no nome.
     * 
     * @param string $name
     * @param int $maxLength
     * @return string
     */
    function generate_tenant_slug(string $name, int $maxLength = 50): string
    {
        $slug = Str::slug($name);
        $slug = sanitize_subdomain($slug);
        $slug = Str::limit($slug, $maxLength, '');
        
        // Garante unicidade
        $originalSlug = $slug;
        $counter = 1;
        
        while (\Callcocam\PapaLeguas\Models\Tenant::where('subdomain', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}

if (!function_exists('mask_sensitive_data')) {
    /**
     * Mascara dados sensíveis para logs e debug.
     * 
     * @param string $data
     * @param int $visibleChars
     * @param string $mask
     * @return string
     */
    function mask_sensitive_data(string $data, int $visibleChars = 2, string $mask = '*'): string
    {
        $length = strlen($data);
        
        if ($length <= $visibleChars * 2) {
            return str_repeat($mask, $length);
        }
        
        $start = substr($data, 0, $visibleChars);
        $end = substr($data, -$visibleChars);
        $middle = str_repeat($mask, $length - ($visibleChars * 2));
        
        return $start . $middle . $end;
    }
}

if (!function_exists('papa_debug')) {
    /**
     * Helper para debug específico do Papa Leguas.
     * 
     * @param mixed $data
     * @param string|null $label
     * @return void
     */
    function papa_debug($data, ?string $label = null): void
    {
        if (!papa_config('debug.enabled', false)) {
            return;
        }
        
        $debugData = [
            'timestamp' => now()->toISOString(),
            'context' => current_context(),
            'subdomain' => get_subdomain(),
            'tenant_id' => current_tenant()?->id,
            'label' => $label,
            'data' => $data,
        ];
        
        $channel = papa_config('debug.log_channel', 'daily');
        
        \Illuminate\Support\Facades\Log::channel($channel)->debug('Papa Leguas Debug', $debugData);
    }
}

if (!function_exists('format_tenant_name')) {
    /**
     * Formata nome de tenant para exibição.
     * 
     * @param string $name
     * @param int $maxLength
     * @return string
     */
    function format_tenant_name(string $name, int $maxLength = 30): string
    {
        return Str::limit(Str::title($name), $maxLength);
    }
}

if (!function_exists('get_tenant_timezone')) {
    /**
     * Obtém o timezone do tenant atual ou padrão.
     * 
     * @return string
     */
    function get_tenant_timezone(): string
    {
        return tenant_config('timezone', config('app.timezone', 'UTC'));
    }
}

if (!function_exists('tenant_now')) {
    /**
     * Obtém data/hora atual no timezone do tenant.
     * 
     * @return \Illuminate\Support\Carbon
     */
    function tenant_now(): \Illuminate\Support\Carbon
    {
        return now(get_tenant_timezone());
    }
}