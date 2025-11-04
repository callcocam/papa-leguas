<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Info\Columns;

use Callcocam\PapaLeguas\Support\Info\Column;

class DateColumn extends Column
{
    protected string $component = 'info-column-date';

    public function __construct($name, $label = null)
    {
        parent::__construct($name, $label);

        $this->icon('Calendar');
    }
}
