<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Form\Columns;

use Callcocam\PapaLeguas\Support\Form\Column;

class HiddenField extends Column
{
    protected mixed $defaultValue = null;

    public function __construct(string $name, mixed $value = null)
    {
        parent::__construct($name, '');
        $this->type('hidden');
        $this->component('form-column-hidden');
        $this->defaultValue = $value;
        $this->setUp();
    }

    public function default(mixed $value): self
    {
        $this->defaultValue = $value;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'default' => $this->defaultValue,
        ]);
    }
}
