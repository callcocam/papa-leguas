<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Http\Controllers\Tenant;

use Callcocam\PapaLeguas\Http\Controllers\AbstractController;
use Callcocam\Papaleguas\Http\Concerns\HasMenuMetadata;

abstract class TenantController extends AbstractController
{
    use HasMenuMetadata;
    //
}
