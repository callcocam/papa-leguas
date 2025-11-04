<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Http\Concerns;

use Illuminate\Support\Str;

/**
 * Trait para configuração completa de rotas e menu no controller
 * 
 * UNIFICA HasMenuMetadata + Configuração de Rotas
 * 
 * O dev pode sobrescrever qualquer método/propriedade para customizar:
 * 
 * ROTAS:
 * - Nome do recurso (resource name)
 * - Prefixo de rotas (route prefix)
 * - Nome das rotas (route names)
 * - Path das rotas (route paths)
 * - Títulos e labels
 * 
 * MENU:
 * - Ícone de navegação
 * - Grupo de navegação
 * - Ordem no menu
 * - Visibilidade
 * - Componentes Vue
 * 
 * @property string|null $navigationIcon Ícone do menu (ex: 'heroicon-o-users')
 * @property string|null $navigationGroup Grupo de navegação
 * @property int $navigationOrder Ordem no menu (menor = mais acima)
 * @property bool $showInNavigation Se deve aparecer no menu
 * @property string|null $singleModelName Nome singular do modelo
 * @property string|null $pluralModelName Nome plural do modelo
 * @property string|null $description Descrição do recurso
 * @property string|null $componentIndexPath Caminho do componente Index
 * @property string|null $componentCreatePath Caminho do componente Create
 * @property string|null $componentEditPath Caminho do componente Edit
 * @property string|null $componentShowPath Caminho do componente Show
 * @property bool $isCrud Se é um controller CRUD completo
 */
trait HasRouteConfiguration
{
    // ==========================================
    // MENU & METADATA
    // ==========================================

    /**
     * Verifica se o controller deve ser exibido na navegação
     */
    public function getShowInNavigation(): bool
    {
        return property_exists($this, 'showInNavigation')
            ? $this->showInNavigation
            : true;
    }

    /**
     * Obtém o ícone do menu
     * @example 'heroicon-o-users'
     */
    public function getNavigationIcon(): ?string
    {
        return property_exists($this, 'navigationIcon')
            ? $this->navigationIcon
            : null;
    }

    /**
     * Obtém o grupo do menu
     * @example 'Administração', 'Cadastros'
     */
    public function getNavigationGroup(): ?string
    {
        return property_exists($this, 'navigationGroup')
            ? $this->navigationGroup
            : null;
    }

    /**
     * Obtém a ordem do item na navegação
     * @return int Número da ordem (menor = mais acima, padrão: 999)
     */
    public function getNavigationOrder(): int
    {
        return property_exists($this, 'navigationOrder')
            ? $this->navigationOrder
            : 999;
    }

    /**
     * Verifica se é um controller CRUD completo
     */
    public function isCrud(): bool
    {
        return property_exists($this, 'isCrud') ? $this->isCrud : true;
    }

    /**
     * Descrição do recurso
     */
    public function getDescription(): ?string
    {
        return property_exists($this, 'description')
            ? $this->description
            : null;
    }

    /**
     * Obtém os caminhos dos componentes Vue para cada ação CRUD
     * 
     * Permite customização via propriedades:
     * - $componentIndexPath
     * - $componentListPath
     * - $componentCreatePath
     * - $componentEditPath
     * - $componentShowPath
     * 
     * Ou via hooks:
     * - beforeComponentPaths()
     * - afterComponentPaths()
     */
    public function getComponentPaths(): array
    {
        $componentPaths = [
            'index' => 'views/crud/Index.vue',
            'list' => 'views/crud/List.vue',
            'create' => 'views/crud/Create.vue',
            'edit' => 'views/crud/Edit.vue',
            'show' => 'views/crud/Show.vue',
        ];

        if (method_exists($this, 'beforeComponentPaths')) {
            $componentPaths = array_merge($componentPaths, $this->beforeComponentPaths());
        }

        if (property_exists($this, 'componentIndexPath') && $this->componentIndexPath) {
            $componentPaths['index'] = $this->componentIndexPath;
        }
        if (property_exists($this, 'componentListPath') && $this->componentListPath) {
            $componentPaths['list'] = $this->componentListPath;
        }
        if (property_exists($this, 'componentCreatePath') && $this->componentCreatePath) {
            $componentPaths['create'] = $this->componentCreatePath;
        }
        if (property_exists($this, 'componentEditPath') && $this->componentEditPath) {
            $componentPaths['edit'] = $this->componentEditPath;
        }
        if (property_exists($this, 'componentShowPath') && $this->componentShowPath) {
            $componentPaths['show'] = $this->componentShowPath;
        }

        if (method_exists($this, 'afterComponentPaths')) {
            $componentPaths = array_merge($componentPaths, $this->afterComponentPaths());
        }

        return $componentPaths;
    }

    // ==========================================
    // RESOURCE NAMES
    // ==========================================
    // ==========================================
    // RESOURCE NAMES
    // ==========================================

    /**
     * Obtém o nome singular do modelo
     * 
     * Se a propriedade $singleModelName estiver definida, retorna seu valor.
     * Caso contrário, extrai automaticamente do nome da classe do controller.
     * 
     * @example UserController -> "User"
     * @example ProductController -> "Product"
     */
    public function getSingleModelName(): ?string
    {
        if (property_exists($this, 'singleModelName') && $this->singleModelName) {
            return $this->singleModelName;
        }

        // Extrai do nome da classe
        $className = class_basename(static::class);
        return Str::replaceLast('Controller', '', $className);
    }

    /**
     * Obtém o nome plural do modelo
     * 
     * @example "User" -> "Users"
     * @example "Category" -> "Categories"
     */
    public function getPluralModelName(): ?string
    {
        if (property_exists($this, 'pluralModelName') && $this->pluralModelName) {
            return $this->pluralModelName;
        }

        $singular = $this->getSingleModelName();
        return str($singular)->plural()->title()->toString();
    }

    /**
     * Nome do recurso em kebab-case (ex: "users", "products")
     * Usado como base para geração de nomes e paths
     * 
     * @example "users" -> routes: users.index, users.show
     * @example "products" -> routes: products.index, products.show
     */
    protected function getResourceName(): string
    {
        if (property_exists($this, 'resourceName') && $this->resourceName) {
            return $this->resourceName;
        } 
        return str($this->getPluralModelName())->plural()->kebab()->toString();
    }

    /**
     * Nome do recurso no singular em kebab-case
     */
    protected function getSingularResourceName(): string
    {
        return Str::singular($this->getResourceName());
    }

    /**
     * Nome em slug (alias para getResourceName)
     */
    public function getSluggedName(): ?string
    {
        if (property_exists($this, 'sluggedName') && $this->sluggedName) {
            return $this->sluggedName;
        }

        return $this->getResourceName();
    }

    // ==========================================
    // ROUTE PREFIXES
    // ==========================================

    // ==========================================
    // ROUTE PREFIXES
    // ==========================================

    /**
     * Prefixo de rotas API (opcional)
     * 
     * @example "api/v1" -> /api/v1/users
     * @example "admin" -> /admin/users
     * @example null -> /users (sem prefixo)
     */
    protected function getApiRoutePrefix(): ?string
    {
        return property_exists($this, 'apiRoutePrefix')
            ? $this->apiRoutePrefix
            : null;
    }

    /**
     * Prefixo de rotas WEB (opcional)
     * 
     * @example "admin" -> /admin/users
     * @example "dashboard" -> /dashboard/users
     * @example null -> /users (sem prefixo)
     */
    protected function getWebRoutePrefix(): ?string
    {
        return property_exists($this, 'webRoutePrefix')
            ? $this->webRoutePrefix
            : null;
    }

    /**
     * Nome base das rotas (padrão Laravel)
     * Detecta automaticamente se é Landlord ou Tenant pelo namespace
     * 
     * @example "users" -> users.index, users.show, users.store...
     * @example "landlord.users" -> landlord.users.index (se namespace tiver Landlord)
     */
    protected function getRouteName(): string
    {
        if (property_exists($this, 'routeName') && $this->routeName) {
            return $this->routeName;
        }

        $resource = $this->getSingularResourceName();

        // Detecta contexto pelo namespace (para retrocompatibilidade com HasMenuMetadata)
        $namespace = (new \ReflectionClass($this))->getNamespaceName();

        if (str_contains($namespace, 'Landlord')) {
            return "landlord.{$resource}";
        }

        return $resource;
    }

    // ==========================================
    // ROUTE PATHS
    // ==========================================

    /**
     * Path completo das rotas API
     * 
     * @param string $action index|show|store|update|destroy
     * @return string
     * 
     * @example index -> /api/users
     * @example show -> /api/users/{id}
     * @example store -> /api/users
     */
    public function getApiRoutePath(string $action): string
    {
        $prefix = $this->getApiRoutePrefix();
        $resource = $this->getResourceName();

        $base = $prefix ? "{$prefix}/{$resource}" : $resource;

        return match ($action) {
            'index' => "/{$base}",
            'store' => "/{$base}",
            'show' => "/{$base}/{id}",
            'update' => "/{$base}/{id}",
            'destroy' => "/{$base}/{id}",
            default => "/{$base}/{$action}",
        };
    }

    /**
     * Path completo das rotas WEB
     * 
     * @param string $action index|create|show|edit|store|update|destroy
     * @return string
     * 
     * @example index -> /users
     * @example create -> /users/create
     * @example show -> /users/{id}
     * @example edit -> /users/{id}/edit
     */
    public function getWebRoutePath(string $action): string
    {
        $prefix = $this->getWebRoutePrefix();
        $resource = $this->getResourceName();

        $base = $prefix ? "{$prefix}/{$resource}" : $resource;

        return match ($action) {
            'index' => "/{$base}",
            'create' => "/{$base}/create",
            'store' => "/{$base}",
            'show' => "/{$base}/{id}",
            'edit' => "/{$base}/{id}/edit",
            'update' => "/{$base}/{id}",
            'destroy' => "/{$base}/{id}",
            default => "/{$base}/{$action}",
        };
    }

    /**
     * Nome completo da rota (padrão Laravel)
     * RETROCOMPATIBILIDADE com HasMenuMetadata::getRouteName($action)
     * 
     * @param string $action index|show|store|update|destroy
     * @return string
     * 
     * @example index -> users.index
     * @example show -> users.show
     * @example store -> users.store
     * @example index (Landlord) -> landlord.users.index
     */
    public function getRouteNameFor(string $action = 'index'): string
    {
        $base = $this->getRouteName();

        return "{$base}.{$action}";
    }

    // ==========================================
    // CRUD PATHS (usado por Actions)
    // ==========================================

    /**
     * Retorna array com paths CRUD customizáveis
     * Usado pelas Actions para gerar URLs
     */
    public function getCrudPaths(): array
    {
        $paths = [];

        if (method_exists($this, 'getListPath')) {
            $paths['list'] = $this->getListPath();
        } else {
            $paths['list'] = $this->getApiRoutePath('index');
        }

        if (method_exists($this, 'getCreatePath')) {
            $paths['create'] = $this->getCreatePath();
        } else {
            $paths['create'] = $this->getWebRoutePath('create');
        }

        if (method_exists($this, 'getEditPath')) {
            $paths['edit'] = $this->getEditPath();
        } else {
            $paths['edit'] = $this->getWebRoutePath('edit');
        }

        if (method_exists($this, 'getShowPath')) {
            $paths['show'] = $this->getShowPath();
        } else {
            $paths['show'] = $this->getApiRoutePath('show');
        }

        return $paths;
    }

    // ==========================================
    // TITLES & LABELS
    // ==========================================

    /**
     * Título do recurso (singular)
     * Usado em formulários e páginas de detalhes
     * 
     * @example "User"
     * @example "Product"
     */
    public function getResourceTitle(): string
    {
        return Str::title(str_replace('-', ' ', $this->getSingularResourceName()));
    }

    /**
     * Título do recurso (plural)
     * Usado em listagens
     * 
     * @example "Users"
     * @example "Products"
     */
    public function getResourceTitlePlural(): string
    {
        return Str::title(str_replace('-', ' ', $this->getResourceName()));
    }

    /**
     * Label para ação CREATE
     */
    public function getCreateLabel(): string
    {
        return "Create {$this->getResourceTitle()}";
    }

    /**
     * Label para ação EDIT
     */
    public function getEditLabel(): string
    {
        return "Edit {$this->getResourceTitle()}";
    }

    /**
     * Label para ação VIEW
     */
    public function getViewLabel(): string
    {
        return "View {$this->getResourceTitle()}";
    }

    /**
     * Label para ação DELETE
     */
    public function getDeleteLabel(): string
    {
        return "Delete {$this->getResourceTitle()}";
    }

    /**
     * Label para ação LIST
     */
    public function getListLabel(): string
    {
        return "List {$this->getResourceTitlePlural()}";
    }

    /**
     * Configuração completa de rotas para o MenuBuilder
     * Retorna array com todas as informações necessárias
     */
    public function getRouteConfiguration(): array
    {
        $resourceName = $this->getResourceName();
        return [
            'resource' => [
                'name' => $resourceName,
                'singular' => $this->getSingularResourceName(),
                'title' => $this->getResourceTitle(),
                'title_plural' => $this->getResourceTitlePlural(),
            ],
            'api' => [
                'prefix' => $this->getApiRoutePrefix(),
                'routes' => [
                    'index' => [
                        'name' => $this->getRouteNameFor('index'),
                        'path' => $this->getApiRoutePath('index'),
                        'method' => 'GET',
                        'label' => $this->getListLabel(),
                    ],
                    'store' => [
                        'name' => $this->getRouteNameFor('store'),
                        'path' => $this->getApiRoutePath('store'),
                        'method' => 'POST',
                        'label' => $this->getCreateLabel(),
                    ],
                    'show' => [
                        'name' => $this->getRouteNameFor('show'),
                        'path' => $this->getApiRoutePath('show'),
                        'method' => 'GET',
                        'label' => $this->getViewLabel(),
                    ],
                    'update' => [
                        'name' => $this->getRouteNameFor('update'),
                        'path' => $this->getApiRoutePath('update'),
                        'method' => 'PUT',
                        'label' => $this->getEditLabel(),
                    ],
                    'destroy' => [
                        'name' => $this->getRouteNameFor('destroy'),
                        'path' => $this->getApiRoutePath('destroy'),
                        'method' => 'DELETE',
                        'label' => $this->getDeleteLabel(),
                    ],
                ],
            ],
            'web' => [
                'prefix' => $this->getWebRoutePrefix(),
                'routes' => [
                    'index' => [
                        'name' => $this->getRouteNameFor('index'),
                        'path' => $this->getWebRoutePath('index'),
                        'method' => 'GET',
                        'label' => $this->getListLabel(),
                    ],
                    'create' => [
                        'name' => $this->getRouteNameFor('create'),
                        'path' => $this->getWebRoutePath('create'),
                        'method' => 'GET',
                        'label' => $this->getCreateLabel(),
                    ],
                    'store' => [
                        'name' => $this->getRouteNameFor('store'),
                        'path' => $this->getWebRoutePath('store'),
                        'method' => 'POST',
                        'label' => $this->getCreateLabel(),
                    ],
                    'show' => [
                        'name' => $this->getRouteNameFor('show'),
                        'path' => $this->getWebRoutePath('show'),
                        'method' => 'GET',
                        'label' => $this->getViewLabel(),
                    ],
                    'edit' => [
                        'name' => $this->getRouteNameFor('edit'),
                        'path' => $this->getWebRoutePath('edit'),
                        'method' => 'GET',
                        'label' => $this->getEditLabel(),
                    ],
                    'update' => [
                        'name' => $this->getRouteNameFor('update'),
                        'path' => $this->getWebRoutePath('update'),
                        'method' => 'PUT',
                        'label' => $this->getEditLabel(),
                    ],
                    'destroy' => [
                        'name' => $this->getRouteNameFor('destroy'),
                        'path' => $this->getWebRoutePath('destroy'),
                        'method' => 'DELETE',
                        'label' => $this->getDeleteLabel(),
                    ],
                ],
            ],
        ];
    }
}
