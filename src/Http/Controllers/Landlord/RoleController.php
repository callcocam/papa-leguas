<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Http\Controllers\Landlord;
 
use Callcocam\PapaLeguas\Support\Concerns\InteractsWithRequests;

class RoleController extends LandlordController
{
    use InteractsWithRequests;

    protected ?string $model = \Callcocam\PapaLeguas\Support\Shinobi\Models\Role::class;

    protected string|null $navigationIcon = 'ShieldCheck';

    protected string|null $navigationGroup = 'Operacional';

    protected function table(\Callcocam\PapaLeguas\Support\Table\TableBuilder $table): \Callcocam\PapaLeguas\Support\Table\TableBuilder
    {
        $table->model($this->model);

        $table->columns([
            \Callcocam\PapaLeguas\Support\Table\Columns\TextColumn::make('name', 'Name'),
            \Callcocam\PapaLeguas\Support\Table\Columns\TextColumn::make('slug', 'Slug'),
        ]);

        $table->filters([
            //
        ]);

        // $table->bulkActions([
        //     //
        // ]);

        $table->actions([
            //
        ]);
        return $table;
    }
}
