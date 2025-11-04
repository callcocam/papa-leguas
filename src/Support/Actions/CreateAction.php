<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Actions;

class CreateAction extends Action
{
    protected string $method = 'GET';

    public function __construct(?string $name)
    {
        parent::__construct($name ?? 'create');
        $this->name($name)
            ->label('Criar Novo')
            ->icon('Plus')
            ->component('action-link')
            ->color('green')
            ->to(function ($model) {
                return [
                    'name' => $this->getName()
                ];
            })
            ->tooltip('Criar novo registro');
        $this->setUp();
    }
}
