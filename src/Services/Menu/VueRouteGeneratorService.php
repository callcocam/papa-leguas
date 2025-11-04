<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Papaleguas\Services\Menu;

use Callcocam\Papaleguas\Enums\Menu\ContextEnum;
use Callcocam\Papaleguas\Services\Menu\Cache\MenuCacheService;
use Callcocam\Papaleguas\Services\Menu\Contracts\RouteGeneratorInterface;
use Callcocam\Papaleguas\Services\Menu\DTOs\RouteDataDTO;
use Illuminate\Support\Collection;

class VueRouteGeneratorService implements RouteGeneratorInterface
{
    protected Collection $registeredRoutes;

    protected bool $useCache = true;

    /**
     * Métodos que não geram rotas no Vue Router (apenas endpoints de API)
     */
    protected array $excludedMethods = ['store', 'update'];

    /**
     * Métodos que são rotas filhas (children) do index
     */
    protected array $childRouteMethods = ['list', 'create', 'show', 'edit'];

    /**
     * Métodos que são rotas filhas (children) da list
     */
    protected array $listChildMethods = ['destroy'];

    public function __construct(
        protected RouteCollectionService $routeCollectionService,
        protected MenuCacheService $cacheService,
        protected ContextEnum $context = ContextEnum::LANDLORD
    ) {
        $this->registeredRoutes = collect();
    }

    /**
     * Define o contexto
     */
    public function setContext(ContextEnum $context): self
    {
        $this->context = $context;
        $this->routeCollectionService->setContext($context);

        return $this;
    }

    /**
     * Gera as rotas Vue
     */
    public function generate(): array
    {
        if ($this->useCache) {
            return $this->cacheService->remember(
                $this->context,
                'routes',
                fn () => $this->generateRoutes()
            );
        }

        return $this->generateRoutes();
    }

    /**
     * Gera a estrutura de rotas
     */
    protected function generateRoutes(): array
    {
        $this->registeredRoutes = $this->routeCollectionService
            ->getRoutesByContext($this->context);

        // Agrupa rotas por recurso (permissions, roles, users, etc)
        $groupedRoutes = $this->routeCollectionService
            ->groupByResource($this->registeredRoutes);

        return $groupedRoutes
            ->reject(fn ($_routes, $resource) => in_array($resource, ['unknown', 'skip']) || empty($resource))
            ->map(fn ($routes, $resource) => $this->generateResourceRoutes($routes, $resource))
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Gera rotas para um recurso específico
     *
     * @param  Collection  $routes  Collection de RegisteredRouteDTO do mesmo recurso
     * @param  string  $resourceName  Nome do recurso
     */
    protected function generateResourceRoutes(Collection $routes, string $resourceName): ?array
    {
        // Se não tiver rotas, pula
        if ($routes->isEmpty()) {
            return null;
        }

        // Pega metadata do primeiro route (todas têm a mesma metadata por recurso)
        $firstRoute = $routes->first();
        $metadata = $firstRoute->metadata;

        if (! $metadata) {
            return null;
        }

        // Se não tiver métodos disponíveis, pula
        if (empty($metadata->availableMethods)) {
            return null;
        }

        // Se não for CRUD, cria uma rota simples (não hierárquica)
        if (! $metadata->isCrud()) {
            return $this->generateSingleRoute($metadata, $resourceName);
        }

        // Se for CRUD, cria estrutura hierárquica
        return $this->generateCrudRoutes($metadata, $resourceName);
    }

    /**
     * Gera uma rota única (não-CRUD)
     */
    protected function generateSingleRoute($metadata, string $resourceName): array
    {
        $component = data_get(
            $metadata->componentPaths,
            'index',
            null
        );

        return [
            'resource' => $resourceName,
            'label' => $metadata->pluralModelName,
            'icon' => $metadata->icon,
            'controller' => $metadata->className,
            'routes' => [
                'name' => $metadata->routeName ?? "{$resourceName}.index",
                'path' => $metadata->routeName
                    ? str_replace('.', '/', $metadata->routeName)
                    : "/{$resourceName}",
                'component' => $component,
                'meta' => [
                    'title' => $metadata->pluralModelName,
                    'icon' => $metadata->icon,
                    'action' => 'custom',
                    'resource' => $resourceName,
                    'requiresAuth' => true,
                    'crud' => [],
                ],
            ],
        ];
    }

    /**
     * Gera estrutura hierárquica de rotas CRUD
     */
    protected function generateCrudRoutes($metadata, string $resourceName): ?array
    {
        // Gera rotas filhas
        $childRoutes = $this->generateChildRoutes($metadata, $resourceName);

        if ($childRoutes->isEmpty()) {
            return null;
        }

        // Estrutura hierárquica: Index (pai) -> List, Create, Show, Edit (filhos)
        $indexRoute = [
            'name' => "{$resourceName}.index",
            'path' => "/{$metadata->sluggedName}",
            'component' => $this->getIndexComponent($metadata),
            'redirect' => ['name' => "{$resourceName}.list"],
            'meta' => [
                'title' => $metadata->pluralModelName,
                'icon' => $metadata->icon,
                'action' => 'index',
                'resource' => $resourceName,
                'requiresAuth' => true,
                'layout' => 'default', // Layout wrapper
                'crud' => $metadata->availableMethods,
            ],
            'children' => $childRoutes->toArray(),
        ];

        return [
            'resource' => $resourceName,
            'label' => $metadata->pluralModelName,
            'icon' => $metadata->icon,
            'controller' => $metadata->className,
            'routes' => $indexRoute,
        ];
    }

    /**
     * Gera rotas filhas (list, create, show, edit)
     */
    protected function generateChildRoutes($metadata, string $resourceName): Collection
    {
        $routes = collect();

        // Adiciona rota "list" primeiro (é o padrão de redirect)
        if (in_array('index', $metadata->availableMethods)) {
            $routes->push($this->generateListRoute($metadata, $resourceName));
        }

        // Adiciona outras rotas filhas baseado na configuração
        foreach ($metadata->availableMethods as $method) {
            // Pula se for excluído, index (já tratado), ou filho da list
            if ($this->shouldSkipMethod($method)) {
                continue;
            }

            $routeData = $this->generateRouteForMethod($metadata, $resourceName, $method);

            if ($routeData) {
                $routes->push($routeData);
            }
        }

        return $routes;
    }

    /**
     * Verifica se um método deve ser ignorado
     */
    protected function shouldSkipMethod(string $method): bool
    {
        return in_array($method, $this->excludedMethods)
            || $method === 'index'
            || in_array($method, $this->listChildMethods);
    }

    /**
     * Gera rota para um método específico
     */
    protected function generateRouteForMethod($metadata, string $resourceName, string $method): ?array
    {
        $component = data_get($metadata->componentPaths, $method, null);

        $routeData = RouteDataDTO::forMethod(
            resource: $resourceName,
            method: $method,
            label: $metadata->pluralModelName,
            icon: $metadata->icon,
            component: $component
        );

        return $routeData?->toArray();
    }

    /**
     * Gera a rota "list" (listagem) com suas rotas filhas
     */
    protected function generateListRoute($metadata, string $resourceName): array
    {
        $listComponent = data_get($metadata->componentPaths, 'list', 'views/crud/List.vue');

        // Gera endpoint base da API
        $context = strtolower($this->context->value);
        $endpoint = "/api/{$resourceName}";

        $route = [
            'name' => "{$resourceName}.list",
            'path' => '', // Path vazio pois é filho de /{resource}
            'component' => $listComponent,
            'meta' => [
                'title' => $metadata->pluralModelName,
                'icon' => $metadata->icon,
                'action' => 'list',
                'resource' => $resourceName,
                'context' => $context,
                'requiresAuth' => true,
                'crud' => $metadata->availableMethods,
                // Informações para o componente Table
                'endpoint' => $endpoint,
                'controller' => $metadata->className,
                'modelName' => $metadata->singleModelName,
            ],
        ];

        // Adiciona rotas filhas da list (como destroy)
        $listChildren = $this->generateListChildRoutes($metadata, $resourceName);
        if ($listChildren->isNotEmpty()) {
            $route['children'] = $listChildren->toArray();
        }

        return $route;
    }

    /**
     * Gera rotas filhas da list (destroy, bulk actions, etc)
     */
    protected function generateListChildRoutes($metadata, string $resourceName): Collection
    {
        $routes = collect();

        foreach ($metadata->availableMethods as $method) {
            // Processa apenas métodos que são filhos da list
            if (! in_array($method, $this->listChildMethods)) {
                continue;
            }

            $component = data_get($metadata->componentPaths, $method, null);
            $routeData = RouteDataDTO::forMethod(
                resource: $resourceName,
                method: $method,
                label: $metadata->pluralModelName,
                icon: $metadata->icon,
                component: $component
            );

            if ($routeData) {
                $routes->push($routeData->toArray());
            }
        }

        return $routes;
    }

    /**
     * Obtém o componente Index (layout wrapper)
     */
    protected function getIndexComponent($metadata): string
    {
        $listComponent = data_get($metadata->componentPaths, 'index', 'views/crud/Index.vue');

        return $listComponent;
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
     * Define métodos que devem ser excluídos
     */
    public function setExcludedMethods(array $methods): self
    {
        $this->excludedMethods = $methods;

        return $this;
    }

    /**
     * Define métodos que são rotas filhas
     */
    public function setChildRouteMethods(array $methods): self
    {
        $this->childRouteMethods = $methods;

        return $this;
    }

    /**
     * Define métodos que são filhos da rota list
     */
    public function setListChildMethods(array $methods): self
    {
        $this->listChildMethods = $methods;

        return $this;
    }

    /**
     * Adiciona um método aos filhos da list
     */
    public function addListChildMethod(string $method): self
    {
        if (! in_array($method, $this->listChildMethods)) {
            $this->listChildMethods[] = $method;
        }

        return $this;
    }

    /**
     * Remove um método dos filhos da list
     */
    public function removeListChildMethod(string $method): self
    {
        $this->listChildMethods = array_diff($this->listChildMethods, [$method]);

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

    /**
     * Método helper para gerar rotas rapidamente
     */
    public static function forContext(ContextEnum $context = ContextEnum::LANDLORD): array
    {
        return static::make($context)->generate();
    }
}
