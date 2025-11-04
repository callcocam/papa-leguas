<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Actions;

class ViewAction extends Action
{
    protected string $method = 'GET';

    public function __construct(?string $name)
    {
        parent::__construct($name ?? 'view');
        $this->name($name) // ✅ Sempre define o name
            ->label('Visualizar')
            ->icon('Eye')
            ->color('blue')
            ->component('action-link')->to(function ($model) use ($name) {
                return [
                    'name' => $name,
                    'params' => ['id' => data_get($model, 'id')]
                ];
            })
            ->tooltip('Visualizar detalhes');
        $this->setUp();
    }
}
