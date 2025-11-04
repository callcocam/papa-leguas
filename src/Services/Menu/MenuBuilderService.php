<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Papaleguas\Services\Menu;

use Callcocam\Papaleguas\Enums\Menu\ContextEnum;
use Callcocam\Papaleguas\Services\Menu\Cache\MenuCacheService;
use Callcocam\Papaleguas\Services\Menu\Contracts\MenuBuilderInterface;
use Callcocam\Papaleguas\Services\Menu\DTOs\MenuItemDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MenuBuilderService implements MenuBuilderInterface
{
    protected Collection $registeredRoutes;

    protected bool $useCache = true;

    protected bool $withoutGroups = false;

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
     * Constrói o menu usando rotas registradas
     */
    public function build(): self
    {
        if ($this->useCache && $this->cacheService->has($this->context, 'menu')) {
            return $this;
        }

        $this->registeredRoutes = $this->routeCollectionService
            ->getRoutesByContext($this->context);

        return $this;
    }

    /**
     * Renderiza o menu como array
     */
    public function render(): array
    {
        if ($this->useCache) {
            return $this->cacheService->remember(
                $this->context,
                'menu',
                fn () => $this->generateMenu()
            );
        }

        return $this->generateMenu();
    }

    /**
     * Gera a estrutura do menu
     */
    protected function generateMenu(): array
    {
        if ($this->withoutGroups) {
            return $this->generateFlatMenu();
        }

        return $this->generateGroupedMenu();
    }

    /**
     * Gera menu sem grupos
     */
    protected function generateFlatMenu(): array
    {
        // Agrupa rotas por recurso e pega apenas a primeira de cada grupo
        $uniqueMetadata = $this->routeCollectionService
            ->groupByResource($this->registeredRoutes)
            ->reject(fn ($routes, $resource) => in_array($resource, ['unknown', 'skip']) || empty($resource))
            ->map(fn ($routes) => $routes->first()->metadata)
            ->filter()
            ->reject(fn ($metadata) => ! $metadata->showInNavigation);

        return $uniqueMetadata
            ->map(fn ($metadata) => MenuItemDTO::fromMetadata($metadata))
            ->sortBy('order')
            ->map(fn ($item) => $item->toArray())
            ->values()
            ->toArray();
    }

    /**
     * Gera menu com grupos
     */
    protected function generateGroupedMenu(): array
    {
        // Agrupa rotas por recurso e pega apenas a primeira de cada grupo
        $uniqueMetadata = $this->routeCollectionService
            ->groupByResource($this->registeredRoutes)
            ->reject(fn ($routes, $resource) => in_array($resource, ['unknown', 'skip']) || empty($resource))
            ->map(fn ($routes) => $routes->first()->metadata)
            ->filter()
            ->reject(fn ($metadata) => ! $metadata->showInNavigation);

        $items = collect();
        $groups = collect();

        // Separa itens com e sem grupo
        foreach ($uniqueMetadata as $metadata) {
            $menuItem = MenuItemDTO::fromMetadata($metadata);

            if ($metadata->group) {
                $groupKey = Str::kebab($metadata->group);

                if (! $groups->has($groupKey)) {
                    $groups->put($groupKey, [
                        'id' => $groupKey,
                        'name' => $metadata->group,
                        'label' => $metadata->group,
                        'active' => false,
                        'icon' => 'Folder',
                        'order' => 999,
                        'items' => collect(),
                    ]);
                }

                $groups->get($groupKey)['items']->push($menuItem);
            } else {
                $items->push($menuItem);
            }
        }

        // Ordena itens dentro dos grupos e define ordem do grupo
        $groups = $groups->map(function ($group) {
            $sortedItems = $group['items']->sortBy('order')->values();
            $group['items'] = $sortedItems;

            if ($sortedItems->isNotEmpty()) {
                $group['order'] = $sortedItems->min('order');
            }

            return $group;
        });

        // Monta resultado final
        $result = collect();

        // Adiciona itens sem grupo ordenados
        $items->sortBy('order')->each(function ($item) use ($result) {
            $result->push($item->toArray());
        });

        // Adiciona grupos ordenados
        $groups->sortBy('order')->each(function ($group) use ($result) {
            $result->push([
                'type' => 'group',
                'id' => $group['id'],
                'name' => $group['name'],
                'label' => $group['label'],
                'icon' => $group['icon'],
                'order' => $group['order'],
                'active' => false,
                'children' => $group['items']->map(fn ($item) => $item->toArray())->toArray(),
            ]);
        });

        return $result->sortBy('order')->values()->toArray();
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
     * Define se deve ignorar grupos
     */
    public function withoutGroups(bool $withoutGroups = true): self
    {
        $this->withoutGroups = $withoutGroups;

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
     * Método helper para gerar menu rapidamente
     */
    public static function forContext(ContextEnum $context = ContextEnum::LANDLORD): array
    {
        return static::make($context)->build()->render();
    }
}
