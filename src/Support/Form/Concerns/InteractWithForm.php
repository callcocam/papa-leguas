<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Form\Concerns;

use Callcocam\PapaLeguas\Support\Concerns;

trait InteractWithForm
{
    use Concerns\HasGridLayout;
    use Concerns\InteractWithColumns;

    /**
     * Retorna o formulário como estrutura de dados
     */
    public function getForm(): ?array
    {
        if (empty($this->getColumns())) {
            return null;
        }

        return [
            'columns' => $this->getArrayColumns(),
        ];
    }
}
