<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

use Callcocam\Papaleguas\Services\Menu\ControllerDiscoveryService;
use Callcocam\PapaLeguas\Services\DomainDetectionService;
use Callcocam\PapaLeguas\Enums\Menu\ContextEnum;
use Illuminate\Support\Facades\Route;

/**
 * IMPORTANTE: Detecção de contexto otimizada para rotas dinâmicas.
 *
 * Este arquivo registra rotas dinamicamente baseadas no contexto (landlord/tenant).
 *
 * SOLUÇÃO: Usar DomainDetectionService diretamente com o request atual
 * para determinar o contexto correto antes de resolver os controllers.
 *
 * O contexto é detectado pelo domínio da requisição:
 * - landlord.domain.com -> LANDLORD
 * - tenant.domain.com -> TENANT
 */

// Detecta o contexto através do request atual
$domainService = app(DomainDetectionService::class);
$context = $domainService->isLandlord()
    ? ContextEnum::LANDLORD
    : ContextEnum::TENANT;

// Descobre controllers com cache (24h)
// Para limpar o cache: php artisan cache:forget "menu_builder.{context}.api_controllers"
// IMPORTANTE: Desabilita cache durante migrations para evitar erro de tabela não existir
$cacheKey = "menu_builder.{$context->value}.api_controllers";
$controllers = [];

try {
    // Verifica se não está rodando migrations
    if (!app()->runningInConsole() || !in_array('migrate', $_SERVER['argv'] ?? [])) {
        $controllers = \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () {
            $service = app(ControllerDiscoveryService::class);
            return $service->discover();
        });
    } else {
        // Durante migrations, descobre sem cache
        $service = app(ControllerDiscoveryService::class);
        $controllers = $service->discover();
    }
} catch (\Exception $e) {
    // Fallback se houver erro de cache
    $service = app(ControllerDiscoveryService::class);
    $controllers = $service->discover();
}

// Registra as rotas com prefixo e nome baseados no contexto
// O guard é único (sanctum) para todos os contextos
Route::middleware(['api', 'auth:sanctum'])
    ->prefix('api')
    ->name($context->getRouteNames())
    ->group(function () use ($controllers) {

        Route::get('user', [\Callcocam\PapaLeguas\Http\Controllers\Auth\Api\MeController::class, 'me']);

        Route::get('tenant-settings', [\Callcocam\PapaLeguas\Http\Controllers\Settings\TenantSettingsController::class, 'show']);

        Route::get('dashboard', [\Callcocam\PapaLeguas\Http\Controllers\DashboardController::class, 'index']);
        Route::get('dashboard/widget/{widgetId}', [\Callcocam\PapaLeguas\Http\Controllers\DashboardController::class, 'widgetData']);

        foreach ($controllers as $metadata) {
            if ($metadata->isCrud()) {
                $sluggedName = $metadata->sluggedName;
                $controllerClass = $metadata->className;
                Route::resource($sluggedName, $controllerClass)->parameter($sluggedName, 'id');
            } else {
                // Registra rotas individuais conforme os métodos disponíveis
                $sluggedName = $metadata->sluggedName;
                $controllerClass = $metadata->className;
                foreach ($metadata->availableMethods as $method) {
                    match ($method) {
                        'index' => Route::get($sluggedName, [$controllerClass, 'index'])->name(sprintf('%s.index', request()->getContext(), $sluggedName)),
                        'store' => Route::post($sluggedName, [$controllerClass, 'store'])->name(sprintf('%s.store', request()->getContext(), $sluggedName)),
                        'show' => Route::get("{$sluggedName}/{id}", [$controllerClass, 'show'])->name(sprintf('%s.show', request()->getContext(), $sluggedName)),
                        'edit' => Route::get("{$sluggedName}/{id}/edit", [$controllerClass, 'edit'])->name(sprintf('%s.edit', request()->getContext(), $sluggedName)),
                        'update' => Route::match(['put', 'patch'], "{$sluggedName}/{id}", [$controllerClass, 'update'])->name(sprintf('%s.update', request()->getContext(), $sluggedName)),
                        'destroy' => Route::delete("{$sluggedName}/{id}", [$controllerClass, 'destroy'])->name(sprintf('%s.destroy', request()->getContext(), $sluggedName)),
                        default => null,
                    };
                }
            }
        }
    });
