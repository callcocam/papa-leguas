<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Form\Columns;

use Callcocam\PapaLeguas\Support\Form\Column;

class TextareaField extends Column
{
    protected int $rows = 3;

    protected ?string $placeholder = null;

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->component('form-column-textarea');
        $this->setUp();
    }

    public function rows(int $rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'rows' => $this->rows,
            'placeholder' => $this->placeholder,
        ]);
    }
}
