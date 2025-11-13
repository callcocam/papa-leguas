<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Form\Columns;

use Callcocam\PapaLeguas\Support\Form\Column;

class CheckboxField extends Column
{
    protected ?string $description = null;

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->component('form-column-checkbox');
        $this->default(false);
        $this->setUp();
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'description' => $this->description,
        ]);
    }
}
