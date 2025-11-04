<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Concerns;

trait HasApplyFormatter
{
    public function applyFormatter(string $formatterClass, $value)
    {
        if (class_exists($formatterClass)) {
            $formatter = new $formatterClass;
            if (method_exists($formatter, 'format')) {
                return $formatter->format($value);
            }
        }

        return $value;
    }
}
