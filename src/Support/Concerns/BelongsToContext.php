<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Concerns;

trait BelongsToContext
{
    protected mixed $context = null;

    public function context(mixed $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function getContext(): mixed
    {
        return $this->context ?? null;
    }
}
