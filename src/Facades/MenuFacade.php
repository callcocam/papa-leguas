<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array forContext(\Callcocam\PapaLeguas\Enums\Menu\ContextEnum $context)
 * @method static \Callcocam\PapaLeguas\Services\Menu\MenuBuilderService make(\Callcocam\PapaLeguas\Enums\Menu\ContextEnum $context = \Callcocam\PapaLeguas\Enums\Menu\ContextEnum::LANDLORD)
 * @method static \Callcocam\PapaLeguas\Services\Menu\MenuBuilderService setContext(\Callcocam\PapaLeguas\Enums\Menu\ContextEnum $context)
 * @method static \Callcocam\PapaLeguas\Services\Menu\MenuBuilderService withCache(bool $useCache = true)
 * @method static \Callcocam\PapaLeguas\Services\Menu\MenuBuilderService withoutGroups(bool $withoutGroups = true)
 * @method static \Callcocam\PapaLeguas\Services\Menu\MenuBuilderService build()
 * @method static array render()
 *
 * @see \Callcocam\PapaLeguas\Services\Menu\MenuBuilderService
 */
class MenuBuilder extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \Callcocam\Papaleguas\Services\Menu\MenuBuilderService::class;
    }
}