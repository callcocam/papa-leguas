<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\PapaLeguas\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Callcocam\PapaLeguas\PapaLeguas
 */
class PapaLeguas extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Callcocam\PapaLeguas\PapaLeguas::class;
    }
}
