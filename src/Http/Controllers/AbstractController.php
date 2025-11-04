<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Http\Controllers;

use App\Http\Controllers\Controller;
use Callcocam\PapaLeguas\Http\Concerns\HasRouteConfiguration;

abstract class AbstractController extends Controller
{
    use HasRouteConfiguration;


    protected function getHeaderActions(): array
    {
        return [
            // Ações de cabeçalho padrão
        ];
    }

    protected function getImportActions(): array
    {
        return [
            // Ações para importação
        ];
    }

    protected function getExportActions(): array
    {
        return [
            // Ações para exportação
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return array_merge(
            $this->getImportActions(),
            $this->getExportActions(),
            $this->getHeaderActions()
        );
    }
}
