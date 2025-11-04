<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Actions;

class BulkDeleteAction extends Action
{
    protected string $method = 'DELETE';

    public function __construct(?string $name)
    {
        parent::__construct($name ?? 'bulkDelete');
        $this->name($name)
            ->label('Excluir Selecionados')
            ->icon('Trash2')
            ->color('red')
            ->tooltip('Excluir múltiplos registros')
            ->confirm([
                'title' => 'Confirmar exclusão em massa',
                'message' => 'Tem certeza que deseja excluir os registros selecionados? Esta ação não pode ser desfeita.',
                'confirmText' => 'Sim, excluir todos',
                'cancelText' => 'Cancelar',
            ]);
        $this->setUp();
    }
}
