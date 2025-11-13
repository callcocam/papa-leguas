<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Form\Columns;

use Callcocam\PapaLeguas\Support\Form\Column;

class PasswordField extends Column
{
    protected ?string $placeholder = null;

    protected bool $showToggle = true;

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type('password');
        $this->component('form-column-password');
        $this->setUp();
    }

    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function showToggle(bool $show = true): self
    {
        $this->showToggle = $show;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'placeholder' => $this->placeholder,
            'showToggle' => $this->showToggle,
        ]);
    }
}
