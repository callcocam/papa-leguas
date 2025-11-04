<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Http\Controllers\Landlord;

use Callcocam\PapaLeguas\Support\Concerns\InteractsWithRequests;

class ImageController extends LandlordController
{
    use InteractsWithRequests;

    protected string|null $navigationIcon = 'Images';

    protected string|null $navigationGroup = 'Operacional';
    
    // Esconde do menu até estar configurado
    protected bool $showInNavigation = false;

    /**
     * Configura a tabela (exemplo básico)
     */
    protected function table(\Callcocam\PapaLeguas\Support\Table\TableBuilder $table): \Callcocam\PapaLeguas\Support\Table\TableBuilder
    {
        // Descomente quando tiver um modelo definido
        // $table->model(\App\Models\Image::class);
        
        // $table->columns([
        //     \Callcocam\PapaLeguas\Support\Table\Columns\TextColumn::make('name', 'Nome'),
        // ]);

        return $table;
    }
}
