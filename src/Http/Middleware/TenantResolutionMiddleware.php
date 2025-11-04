<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Http\Middleware;

use Callcocam\PapaLeguas\Models\Tenant;
use Callcocam\PapaLeguas\Support\Landlord\TenantManager;
use Closure;
use Illuminate\Http\Request; 
use Illuminate\Support\Str;

class TenantResolutionMiddleware
{
    protected TenantManager $tenantManager;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }

    public function handle(Request $request, Closure $next)
    {
        // Verifica se deve ignorar tenant resolution
        if ($this->shouldSkipTenantResolution($request)) {
            $this->tenantManager->disable(); 
        }

        $host = $request->getHost();
        $tenant = $this->resolveTenant($host);
        

        if (!$tenant) {
            return $this->handleTenantNotFound($host);
        }

        $this->setupTenant($tenant);

        return $next($request);
    }

    protected function shouldSkipTenantResolution(Request $request): bool
    {

        if ($request->isTenant()) {
            return false;
        }

        return true;
    }

    protected function resolveTenant(string $host)
    {
        $model = config('landlord.models.tenant', Tenant::class);
        $tenant = app($model)->where('domain', $host)->first();

        if (!$tenant && str_contains($host, '.')) {
            $subdomain = explode('.', $host)[0];
            $tenant = app($model)->where('prefix', $subdomain)->first();
        }

        return $tenant;
    }

    protected function setupTenant($tenant): void
    {
        $this->tenantManager->addTenant("tenant_id", $tenant->id);

        config([
            'app.tenant_id' => $tenant->id,
            'app.name' => Str::limit($tenant->name, 20, '...'),
            'app.tenant' => $tenant->toArray(),
        ]);
    }

    protected function handleTenantNotFound(string $host)
    {
        return response()->json(['message' => 'Tenant não encontrado.'], 404);
    } 
}
