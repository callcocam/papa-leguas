<?php

namespace Callcocam\PapaLeguas\Support\Dashboard\Widgets;

use Callcocam\PapaLeguas\Support\Dashboard\Concerns\HasDataRoute;
use Callcocam\PapaLeguas\Support\Dashboard\Concerns\HasRefreshInterval;
use Callcocam\PapaLeguas\Support\Dashboard\Widget;
use Closure;

class StatWidget extends Widget
{
    use HasDataRoute;
    use HasRefreshInterval;

    protected string $type = 'stat';

    protected string $icon = '';

    protected string $color = 'primary';

    protected ?Closure $valueCallback = null;

    protected ?Closure $descriptionCallback = null;

    protected ?string $trend = null;

    protected ?string $trendDirection = null;

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function color(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function value(Closure $callback): static
    {
        $this->valueCallback = $callback;

        return $this;
    }

    public function descriptionValue(Closure $callback): static
    {
        $this->descriptionCallback = $callback;

        return $this;
    }

    public function trend(string $value, string $direction = 'up'): static
    {
        $this->trend = $value;
        $this->trendDirection = $direction;

        return $this;
    }

    public function getData(): array
    {
        $data = [
            'value' => $this->valueCallback ? call_user_func($this->valueCallback) : null,
            'description' => $this->descriptionCallback ? call_user_func($this->descriptionCallback) : $this->description,
        ];

        if ($this->trend !== null) {
            $data['trend'] = [
                'value' => $this->trend,
                'direction' => $this->trendDirection,
            ];
        }

        return $data;
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['icon'] = $this->icon;
        $data['color'] = $this->color;

        $data = $this->addDataRouteToArray($data);
        $data = $this->addRefreshIntervalToArray($data);

        return $data;
    }
}
