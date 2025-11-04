<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Http\Controllers\Landlord;

use Callcocam\PapaLeguas\Support\Concerns\InteractsWithRequests; 

class PermissionController extends LandlordController
{
    use InteractsWithRequests;

    protected string|null $navigationIcon = 'KeyRound';

    protected string|null $navigationGroup = 'Operacional';
 
}
