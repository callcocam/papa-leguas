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
 * @method static \Callcocam\PapaLeguas\Enums\Menu\ContextEnum make(\Callcocam\PapaLeguas\Enums\Menu\ContextEnum $context = \Callcocam\PapaLeguas\Enums\Menu\ContextEnum::LANDLORD)
 * @method static \Callcocam\PapaLeguas\Enums\Menu\ContextEnum setContext(\Callcocam\PapaLeguas\Enums\Menu\ContextEnum $context)
 * @method static \Callcocam\PapaLeguas\Enums\Menu\ContextEnum withCache(bool $useCache = true)
 * @method static array generate()
 *
 * @see \Callcocam\PapaLeguas\Services\Menu\VueRouteGeneratorService
 */
class VueRoutes extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \Callcocam\PapaLeguas\Services\Menu\VueRouteGeneratorService::class;
    }
}