<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Papaleguas\Services\Menu;

use Callcocam\Papaleguas\Enums\Menu\ContextEnum;
use Callcocam\Papaleguas\Services\Menu\Cache\MenuCacheService;
use Callcocam\Papaleguas\Services\Menu\DTOs\RegisteredRouteDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

/**
 * Serviço para trabalhar com rotas já registradas no Laravel
 *
 * Este serviço lê rotas de Route::getRoutes() e as transforma em DTOs
 * para serem usados por VueRouteGeneratorService e MenuBuilderService.
 *
 * Benefícios:
 * - Single Source of Truth: rotas registradas são a fonte de verdade
 * - Consistência: menu e vue routes sempre refletem rotas reais
 * - Performance: não precisa varrer filesystem novamente
 */
class RouteCollectionService
{
    protected bool $useCache = true;

    protected ContextEnum $context;

    public function __construct(
        protected MenuCacheService $cacheService,
    ) {
        $this->context = ContextEnum::LANDLORD;
    }

    /**
     * Define o contexto
     */
    public function setContext(ContextEnum $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Obtém todas as rotas registradas no Laravel
     */
    public function getRegisteredRoutes(): Collection
    {
        if ($this->useCache) {
            return $this->cacheService->remember(
                $this->context,
                'registered_routes',
                fn () => $this->fetchRoutesFromLaravel()
            );
        }

        return $this->fetchRoutesFromLaravel();
    }

    /**
     * Obtém rotas filtradas por contexto
     */
    public function getRoutesByContext(ContextEnum $context): Collection
    {
        $this->setContext($context);

        return $this->getRegisteredRoutes()
            ->filter(fn (RegisteredRouteDTO $route) => $this->belongsToContext($route, $context))
            ->filter(fn (RegisteredRouteDTO $route) => $route->metadata !== null)
            ->values();
    }

    /**
     * Busca rotas diretamente do Laravel
     */
    protected function fetchRoutesFromLaravel(): Collection
    {
        $routes = Route::getRoutes();
        $registeredRoutes = collect();

        foreach ($routes as $route) {
            $dto = RegisteredRouteDTO::fromRoute($route);

            if ($dto && $this->isRelevantRoute($dto)) {
                $registeredRoutes->push($dto);
            }
        }

        return $registeredRoutes;
    }

    /**
     * Verifica se a rota é relevante para menu/vue router
     */
    protected function isRelevantRoute(RegisteredRouteDTO $route): bool
    {
        // Deve ser rota de API
        if (! $route->isApiRoute()) {
            return false;
        }

        // Deve ter metadata (controller com HasMenuMetadata trait)
        if ($route->metadata === null) {
            return false;
        }

        // Deve ter nome
        if (empty($route->name)) {
            return false;
        }

        return true;
    }

    /**
     * Verifica se a rota pertence ao contexto
     */
    protected function belongsToContext(RegisteredRouteDTO $route, ContextEnum $context): bool
    {
        // Se o nome da rota contém o contexto (ex: "landlord.users.index")
        if ($route->belongsToContext($context->value)) {
            return true;
        }

        // Se a metadata indica o contexto correto
        // (alguns controllers podem estar em namespaces específicos)
        if ($route->metadata) {
            $controllerNamespace = (new \ReflectionClass($route->metadata->className))->getNamespaceName();

            if ($context === ContextEnum::LANDLORD) {
                return str_contains($controllerNamespace, 'Landlord');
            }

            if ($context === ContextEnum::TENANT) {
                return ! str_contains($controllerNamespace, 'Landlord');
            }
        }

        return false;
    }

    /**
     * Agrupa rotas por recurso
     */
    public function groupByResource(Collection $routes): Collection
    {
        return $routes->groupBy(function (RegisteredRouteDTO $route) {
            // Regra 1: sluggedName vazio = 'Não gere automaticamente'
            // Controllers com getSluggedName() = '' são excluídos (ex: DashboardController)
            if ($route->metadata && $route->metadata->sluggedName === '') {
                return 'skip';
            }

            // Tenta obter o nome do recurso da rota
            $resourceName = $route->getResourceName();

            // Se não conseguiu, usa o sluggedName do metadata
            if (! $resourceName && $route->metadata) {
                $resourceName = $route->metadata->sluggedName;
            }

            // Se ainda está vazio, usa o pluralModelName em kebab-case
            if (! $resourceName && $route->metadata) {
                $resourceName = str($route->metadata->pluralModelName)->kebab()->toString();
            }

            // Último recurso: usa o nome do controller
            if (! $resourceName && $route->metadata) {
                $resourceName = str($route->metadata->singleModelName)->plural()->kebab()->toString();
            }

            return $resourceName ?: 'unknown';
        });
    }

    /**
     * Obtém apenas controllers únicos (sem duplicatas)
     */
    public function getUniqueControllers(Collection $routes): Collection
    {
        return $routes->unique(fn (RegisteredRouteDTO $route) => $route->controller);
    }

    /**
     * Define uso de cache
     */
    public function withCache(bool $useCache = true): self
    {
        $this->useCache = $useCache;

        return $this;
    }

    /**
     * Método estático para facilitar uso
     */
    public static function make(ContextEnum $context = ContextEnum::LANDLORD): self
    {
        $service = app(self::class);
        $service->setContext($context);

        return $service;
    }
}
