<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Table\Filters;

use Callcocam\PapaLeguas\Support\Table\Filter;

class TextFilter extends Filter
{
    protected string $component = 'filter-text';

    protected function setUp(): void
    {
        $this->queryUsing(function ($query, $value) {
            $query->where($this->getName(), 'like', "%{$value}%");
        });
    }
}
