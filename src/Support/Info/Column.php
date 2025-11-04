<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Info;

use Callcocam\PapaLeguas\Support\AbstractColumn;

abstract class Column extends AbstractColumn
{
    protected string $type = 'text';

    protected string $component = 'info-column-text';

    public function __construct($name, $label = null)
    {
        $this->name($name);
        $this->id($name);
        $this->label($label ?? ucfirst($name));
    }
}
