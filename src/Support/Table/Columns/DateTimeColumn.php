<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Table\Columns;

use Callcocam\PapaLeguas\Support\Table\Column;

class DateTimeColumn extends Column
{
    protected string $format;

    public function __construct(string $field, string $label)
    {
        parent::__construct($field, $label);
        $this->format = 'Y-m-d H:i:s';
    }

    public function dateTime(string $format): self
    {
        $this->format = $format;

        return $this;
    }
}
