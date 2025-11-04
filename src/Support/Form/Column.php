<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Form;

use Callcocam\PapaLeguas\Support\AbstractColumn;
use Callcocam\PapaLeguas\Support\Concerns\BelongsToHelpers;

abstract class Column extends AbstractColumn
{
    use BelongsToHelpers;

    protected string $type = 'text';

    protected string $component = 'form-column-text';

    public function __construct($name, $label = null)
    {
        $this->name($name);
        $this->id($name);
        $this->label($label ?? ucfirst($name));
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'default' => $this->getDefault(),
            'helpText' => $this->getHelpText(),
            'hint' => $this->getHint(),
            'prepend' => $this->getPrepend(),
            'append' => $this->getAppend(),
            'prefix' => $this->getPrefix(),
            'suffix' => $this->getSuffix(),
        ]);
    }
}
