<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Info\Columns;

use Callcocam\PapaLeguas\Support\Info\Column;

class PhoneColumn extends Column
{
    protected string $type = 'phone';

    protected string $component = 'info-column-phone';

    public function __construct($name, $label = null)
    {
        parent::__construct($name, $label);

        $this->icon('Phone');
    }
}
