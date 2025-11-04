<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Papaleguas\Http\Concerns;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Trait HasMenuMetadata
 * 
 * Facilita a definição de metadata de menu em controllers, permitindo configurar
 * automaticamente itens de navegação, rotas e componentes Vue.
 * 
 * Esta trait fornece métodos para extrair informações de metadata do controller,
 * como ícones, grupos de navegação, ordem dos itens, nomes de modelos e caminhos
 * de componentes Vue, permitindo uma configuração flexível e customizável.
 * 
 * @example
 * ```php
 * class UserController extends Controller
 * {
 *     use HasMenuMetadata;
 * 
 *     protected string $navigationIcon = 'heroicon-o-users';
 *     protected string $navigationGroup = 'Administração';
 *     protected int $navigationOrder = 10;
 *     protected bool $showInNavigation = true;
 *     protected string $singleModelName = 'User';
 *     protected string $pluralModelName = 'Users';
 * }
 * ```
 * 
 * @property string|null $navigationIcon Ícone do item no menu (ex: 'heroicon-o-users')
 * @property string|null $navigationGroup Grupo de navegação onde o item será exibido
 * @property int $navigationOrder Ordem do item na navegação (menor = mais acima)
 * @property bool $showInNavigation Define se o item deve aparecer na navegação
 * @property string|null $singleModelName Nome singular do modelo
 * @property string|null $pluralModelName Nome plural do modelo
 * @property string|null $description Descrição do recurso ou item de menu
 * @property string|null $componentIndexPath Caminho customizado do componente Index
 * @property string|null $componentCreatePath Caminho customizado do componente Create
 * @property string|null $componentEditPath Caminho customizado do componente Edit
 * @property string|null $componentShowPath Caminho customizado do componente Show
 * @property bool|null $navigationVisible Define se o item é visível na navegação
 * @property bool|null $navigationActive Define se o item está ativo na navegação
 * 
 * @package Callcocam\PapaleguasMenu\Http\Concerns
 */
trait HasMenuMetadata
{
    /**
     * Verifica se o controller deve ser exibido na navegação
     * 
     * Por padrão, retorna true. Você pode sobrescrever definindo a propriedade
     * $showInNavigation no seu controller.
     * 
     * @return bool True se deve mostrar na navegação, false caso contrário
     */
    public function getShowInNavigation(): bool
    {
        return property_exists($this, 'showInNavigation')
            ? $this->showInNavigation
            : true;
    }

    /**
     * Obtém o nome singular do modelo
     * 
     * Se a propriedade $singleModelName estiver definida, retorna seu valor.
     * Caso contrário, extrai automaticamente do nome da classe do controller,
     * removendo o sufixo "Controller".
     * 
     * @example
     * Para UserController, retorna "User"
     * Para ProductController, retorna "Product"
     * 
     * @return string|null Nome singular do modelo
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
     * Se a propriedade $pluralModelName estiver definida, retorna seu valor.
     * Caso contrário, utiliza o nome singular e aplica pluralização automática.
     * 
     * @example
     * Para "User", retorna "Users"
     * Para "Category", retorna "Categories"
     * 
     * @return string|null Nome plural do modelo
     */
    public function getPluralModelName(): ?string
    {
        if (property_exists($this, 'pluralModelName') && $this->pluralModelName) {
            return $this->pluralModelName;
        }

        // Usa o nome singular e pluraliza
        $singular = $this->getSingleModelName();
        return str($singular)->plural()->title()->toString();
    }

    public function getSluggedName(): ?string
    {
        if (property_exists($this, 'sluggedName') && $this->sluggedName) {
            return $this->sluggedName;
        }

        return str($this->getPluralModelName())->plural()->kebab()->toString();
    }

    public function getDescription(): ?string
    {
        return property_exists($this, 'description')
            ? $this->description
            : null;
    }

    /**
     * Obtém o ícone do menu
     * 
     * Retorna o ícone configurado para ser exibido no item de menu.
     * Geralmente usado com ícones do Heroicons ou similar.
     * 
     * @example
     * 'heroicon-o-users'
     * 'heroicon-s-shopping-cart'
     * 
     * @return string|null Identificador do ícone ou null se não definido
     */
    public function getNavigationIcon(): ?string
    {
        return property_exists($this, 'navigationIcon')
            ? $this->navigationIcon
            : null;
    }

    /**
     * Obtém o grupo do menu
     * 
     * Define em qual grupo de navegação o item será agrupado.
     * Itens do mesmo grupo são exibidos juntos no menu.
     * 
     * @example
     * 'Administração'
     * 'Configurações'
     * 'Cadastros'
     * 
     * @return string|null Nome do grupo ou null se não agrupado
     */
    public function getNavigationGroup(): ?string
    {
        return property_exists($this, 'navigationGroup')
            ? $this->navigationGroup
            : null;
    }

    /**
     * Obtém a ordem do item na navegação
     * 
     * Define a posição do item no menu. Valores menores aparecem primeiro.
     * Por padrão, retorna 999 (final da lista).
     * 
     * @example
     * 10 = Aparece no topo
     * 100 = Aparece no meio
     * 999 = Aparece no final (padrão)
     * 
     * @return int Número da ordem (menor = mais acima)
     */
    public function getNavigationOrder(): int
    {
        return property_exists($this, 'navigationOrder')
            ? $this->navigationOrder
            : 999;
    }

    /**
     * Obtém o nome da rota baseado no modelo e ação
     * 
     * Gera automaticamente o nome da rota seguindo o padrão Laravel Resource.
     * Detecta automaticamente se o controller está em um contexto Landlord
     * (multi-tenancy) pelo namespace e ajusta o prefixo da rota.
     * 
     * @param string $action Ação desejada (index, create, store, show, edit, update, destroy)
     * 
     * @example
     * Para UserController com action 'index': retorna "user.index"
     * Para UserController com action 'edit' no contexto Landlord: retorna "landlord.user.edit"
     * Para ProductController com action 'show': retorna "product.show"
     * 
     * @return string Nome completo da rota
     */
    public function getRouteName(string $action = 'index'): string
    {
        $resource = str($this->getSingleModelName())->snake()->toString();

        // Detecta contexto pelo namespace
        $namespace = (new \ReflectionClass($this))->getNamespaceName();

        if (str_contains($namespace, 'Landlord')) {
            Log::info("Printing landlord route name for {$namespace} and resource {$resource} and action {$action} and route ". "landlord.{$resource}.{$action}");
            return "landlord.{$resource}.{$action}";
        }
        Log::info("Printing tenant route name for {$namespace} and resource {$resource} and action {$action} and route ". "{$resource}.{$action}");
        return "{$resource}.{$action}";
    }

    /**
     * Obtém os caminhos dos componentes Vue para cada ação CRUD
     * 
     * Retorna um array associativo com os caminhos dos componentes Vue
     * que serão carregados para cada ação do CRUD. Permite customização
     * através de propriedades específicas ou hooks (beforeComponentPaths e afterComponentPaths).
     * 
     * Propriedades customizáveis:
     * - $componentIndexPath: Caminho customizado para o componente de listagem
     * - $componentCreatePath: Caminho customizado para o componente de criação
     * - $componentEditPath: Caminho customizado para o componente de edição
     * - $componentShowPath: Caminho customizado para o componente de visualização
     * 
     * Hooks disponíveis:
     * - beforeComponentPaths(): Executado antes de aplicar as customizações
     * - afterComponentPaths(): Executado após aplicar as customizações
     * 
     * @example
     * ```php
     * // Valores padrão retornados:
     * [
     *     'index' => 'crud/Index',
     *     'create' => 'crud/Create',
     *     'edit' => 'crud/Edit',
     *     'show' => 'crud/Show',
     * ]
     * 
     * // Customizando no controller:
     * protected string $componentIndexPath = 'users/UserList';
     * protected string $componentEditPath = 'users/UserEdit';
     * 
     * // Usando hooks:
     * protected function beforeComponentPaths(): array
     * {
     *     return ['custom' => 'users/Custom'];
     * }
     * ```
     * 
     * @return array<string, string> Array associativo com action => caminho do componente
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

    /**
     * Verifica se o item de navegação está visível
     * 
     * @return bool True se visível, false caso contrário
     */
    public function isNavigationVisible(): bool
    {
        return property_exists($this, 'navigationVisible')
            ? $this->navigationVisible
            : true;
    }

    /**
     * Verifica se o item de navegação está ativo
     * 
     * @return bool True se ativo, false caso contrário
     */
    public function isNavigationActive(): bool
    {
        return property_exists($this, 'navigationActive')
            ? $this->navigationActive
            : false;
    }

    public function isCrud(): bool
    {
        return property_exists($this, 'isCrud') ? $this->isCrud : true;
    }

    public function getCrudPaths(): array
    {
        $paths = [];
        if(method_exists($this, 'getListPath')){
            $paths['list'] = $this->getListPath();
        }
        if(method_exists($this, 'getCreatePath')){
            $paths['create'] = $this->getCreatePath();
        }
        if(method_exists($this, 'getEditPath')){
            $paths['edit'] = $this->getEditPath();
        }
        if(method_exists($this, 'getShowPath')){
            $paths['show'] = $this->getShowPath();
        }
        return $paths;
    }
}
