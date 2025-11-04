<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Actions;

class EditAction extends Action
{
    protected string $method = 'GET';

    public function __construct(?string $name)
    {
        parent::__construct($name ?? 'edit');
        $this->name($name) // ✅ Sempre define o name
            ->label('Editar')
            ->icon('Edit')
            ->color('blue')
            ->component('action-link')
            ->to(function ($model) use ($name) {
                return [
                    'name' => $name,
                    'params' => ['id' => data_get($model, 'id')]
                ];
            })
            ->tooltip('Editar registro');
        $this->setUp();
    }
}
