<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Table;

use Callcocam\PapaLeguas\Support\Concerns\BelongsToContext;
use Callcocam\PapaLeguas\Support\Concerns\EvaluatesClosures;
use Callcocam\PapaLeguas\Support\Concerns\FactoryPattern;

class Sorting
{
    use BelongsToContext;
    use EvaluatesClosures;
    use FactoryPattern;

    public function __construct(
        public string $name,
        public string $direction,
    ) {}
}
