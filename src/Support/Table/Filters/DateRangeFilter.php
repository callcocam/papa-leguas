<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Table\Filters;

use Callcocam\PapaLeguas\Support\Table\Filter;

class DateRangeFilter extends Filter
{
    protected string $component = 'filter-date-range';

    protected function setUp(): void
    {
        $this->queryUsing(function ($query, $value) {
            if (is_array($value)) {
                $from = $value['from'] ?? null;
                $to = $value['to'] ?? null;

                if ($from) {
                    $query->whereDate($this->getName(), '>=', $from);
                }

                if ($to) {
                    $query->whereDate($this->getName(), '<=', $to);
                }
            }
        });
    }
}
