<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Papaleguas\Services\Menu\DTOs;

use Illuminate\Support\Str;

class ControllerMetadataDTO
{
    public function __construct(
        public readonly string $className,
        public readonly string $shortName,
        public readonly string $singleModelName,
        public readonly string $pluralModelName,
        public readonly string $sluggedName,
        public readonly string $icon,
        public readonly ?string $group,
        public readonly int $order,
        public readonly bool $showInNavigation,
        public readonly array $availableMethods,
        public readonly ?string $routeName = null,
        public readonly array $componentPaths = [],
        public readonly array $crudPaths = [], // ✅ NOVO: Paths CRUD do controller
        public readonly array $routeConfiguration = [], // ✅ NOVO: Configuração completa de rotas
        public readonly bool $isCrud = true,
        public readonly bool $navigationActive = false,
        public readonly bool $navigationVisible = true,
    ) {}

    /**
     * Cria uma instância a partir de uma Route do Laravel
     */
    public static function fromRoute(\Illuminate\Routing\Route $route, object $instance): self
    {
        $reflection = new \ReflectionClass($instance);
        $className = get_class($instance);

        // Obtém métodos disponíveis do controller
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $crudMethods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
        $availableMethods = [];

        foreach ($methods as $method) {
            if (in_array($method->name, $crudMethods)) {
                $availableMethods[] = $method->name;
            }
        }

        return self::fromController($className, $instance, $availableMethods);
    }

    /**
     * Cria uma instância a partir de um controller
     */
    public static function fromController(string $className, object $instance, array $availableMethods): self
    {
        $reflection = new \ReflectionClass($className);
        $shortName = $reflection->getShortName();

        // Single Model Name
        $singleModelName = method_exists($instance, 'getSingleModelName') && $instance->getSingleModelName()
            ? $instance->getSingleModelName()
            : Str::replaceLast('Controller', '', $shortName);

        // Plural Model Name
        $pluralModelName = method_exists($instance, 'getPluralModelName') && $instance->getPluralModelName()
            ? $instance->getPluralModelName()
            : str($singleModelName)->plural()->title()->toString();

        $sluggedName = method_exists($instance, 'getSluggedName') && $instance->getSluggedName()
            ? $instance->getSluggedName()
            : Str::kebab($pluralModelName);

        // Icon
        $icon = method_exists($instance, 'getNavigationIcon') && $instance->getNavigationIcon()
            ? $instance->getNavigationIcon()
            : 'circle';

        // Group
        $group = method_exists($instance, 'getNavigationGroup') && $instance->getNavigationGroup()
            ? $instance->getNavigationGroup()
            : null;

        // Order
        $order = method_exists($instance, 'getNavigationOrder') && is_numeric($instance->getNavigationOrder())
            ? (int) $instance->getNavigationOrder()
            : 999;

        // Show in Navigation
        $showInNavigation = method_exists($instance, 'getShowInNavigation')
            ? (bool) $instance->getShowInNavigation()
            : true;

        // Route Name
        $routeName = method_exists($instance, 'getRouteName')
            ? $instance->getRouteName('index')
            : null;

        $componentPaths = method_exists($instance, 'getComponentPaths')
            ? $instance->getComponentPaths()
            : [
                'index' => 'views/crud/Index.vue',
                'list' => 'views/crud/List.vue',
                'create' => 'views/crud/Create.vue',
                'edit' => 'views/crud/Edit.vue',
                'show' => 'views/crud/Show.vue',
            ];

        // ✅ NOVO: Busca CRUD Paths do controller
        $crudPaths = method_exists($instance, 'getCrudPaths')
            ? $instance->getCrudPaths()
            : [];

        // ✅ NOVO: Busca configuração completa de rotas
        $routeConfiguration = method_exists($instance, 'getRouteConfiguration')
            ? $instance->getRouteConfiguration()
            : [];

        $isCrud = method_exists($instance, 'isCrud') ? $instance->isCrud() : true;

        $navigationActive = method_exists($instance, 'isNavigationActive') ? $instance->isNavigationActive() : false;

        $navigationVisible = method_exists($instance, 'isNavigationVisible') ? $instance->isNavigationVisible() : true;

        return new self(
            className: $className,
            shortName: $shortName,
            singleModelName: $singleModelName,
            pluralModelName: $pluralModelName,
            sluggedName: $sluggedName,
            icon: $icon,
            group: $group,
            order: $order,
            showInNavigation: $showInNavigation,
            availableMethods: $availableMethods,
            routeName: $routeName,
            componentPaths: $componentPaths,
            crudPaths: $crudPaths, // ✅ NOVO
            routeConfiguration: $routeConfiguration, // ✅ NOVO
            isCrud: $isCrud,
            navigationActive: $navigationActive,
            navigationVisible: $navigationVisible
        );
    }

    /**
     * Converte para array
     */
    public function toArray(): array
    {
        return [
            'className' => $this->className,
            'shortName' => $this->shortName,
            'singleModelName' => $this->singleModelName,
            'pluralModelName' => $this->pluralModelName,
            'sluggedName' => $this->sluggedName,
            'icon' => $this->icon,
            'group' => $this->group,
            'order' => $this->order,
            'showInNavigation' => $this->showInNavigation,
            'availableMethods' => $this->availableMethods,
            'routeName' => $this->routeName,
            'componentPaths' => $this->componentPaths,
            'crudPaths' => $this->crudPaths, // ✅ NOVO
            'routeConfiguration' => $this->routeConfiguration, // ✅ NOVO
            'isCrud' => $this->isCrud,
            'navigationActive' => $this->navigationActive,
            'navigationVisible' => $this->navigationVisible,
        ];
    }

    /**
     * Gera o ID kebab-case do recurso
     */
    public function getResourceId(): string
    {
        return $this->sluggedName;
    }

    /**
     * Gera o nome do recurso em snake_case
     */
    public function getResourceName(): string
    {
        return str($this->singleModelName)->snake()->toString();
    }

    public function isCrud(): bool
    {
        return $this->isCrud;
    }
}
