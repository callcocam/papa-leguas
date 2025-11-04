<?php
/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Http\Controllers\Landlord;

use Callcocam\PapaLeguas\Http\Controllers\AbstractController;
use Callcocam\Papaleguas\Http\Concerns\HasMenuMetadata;

abstract class LandlordController extends AbstractController
{
    use HasMenuMetadata;

    protected string|null $modelClass = null;

    
    //
}