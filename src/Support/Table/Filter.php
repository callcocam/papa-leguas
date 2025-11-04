<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Table;

use Callcocam\PapaLeguas\Support\Concerns\BelongsToContext;
use Callcocam\PapaLeguas\Support\Concerns\BelongsToIcon;
use Callcocam\PapaLeguas\Support\Concerns\BelongsToId;
use Callcocam\PapaLeguas\Support\Concerns\BelongsToLabel;
use Callcocam\PapaLeguas\Support\Concerns\BelongsToName;
use Callcocam\PapaLeguas\Support\Concerns\EvaluatesClosures;
use Callcocam\PapaLeguas\Support\Concerns\FactoryPattern;
use Closure;

class Filter
{
    use BelongsToContext;
    use BelongsToIcon;
    use BelongsToId;
    use BelongsToLabel;
    use BelongsToName;
    use EvaluatesClosures;
    use FactoryPattern;

    protected string $component = 'filter-text';

    protected ?Closure $applyCallback = null;

    protected $value = null;

    public function __construct(string $name, ?string $label = null)
    {
        $this->label($label ?? ucfirst($name));
        $this->name($name);
        $this->id($name);
        $this->setUp();
    }

    public function queryUsing(Closure $callback)
    {
        $this->applyCallback = $callback;

        return $this;
    }

    public function setValue($value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function applyUserQuery(&$query)
    {
        $applyCallback = $this->getApplyCallback();

        if ($applyCallback) {
            return $this->evaluate($applyCallback, [
                'query' => $query,
                'value' => $this->getValue()
            ]);
        }

        return $query;
    }

    public function apply(&$query, $value): static
    {
        $applyCallback = $this->getApplyCallback();
        if ($applyCallback) {
            $this->evaluate($applyCallback, [
                'query' => $query,
                'value' => $value
            ]);
        }

        return $this;
    }

    public function getApplyCallback(): ?Closure
    {
        return $this->applyCallback;
    }

    protected function setUp(): void
    {
        //
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'icon' => $this->getIcon(),
            'component' => $this->component,
            'context' => $this->getContext(),
        ];
    }
}
