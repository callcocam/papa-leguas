<?php

namespace Callcocam\PapaLeguas\Support\Dashboard\Widgets;

use Callcocam\PapaLeguas\Support\Dashboard\Concerns\HasDataRoute;
use Callcocam\PapaLeguas\Support\Dashboard\Concerns\HasRefreshInterval;
use Callcocam\PapaLeguas\Support\Dashboard\Widget;
use Closure;

class ChartWidget extends Widget
{
    use HasDataRoute;
    use HasRefreshInterval;

    protected string $type = 'chart';

    protected string $chartType = 'line';

    protected ?Closure $dataCallback = null;

    protected array $options = [];

    public function chartType(string $type): static
    {
        $this->chartType = $type;

        return $this;
    }

    public function data(Closure $callback): static
    {
        $this->dataCallback = $callback;

        return $this;
    }

    public function options(array $options): static
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    public function getData(): array
    {
        return [
            'chartData' => $this->dataCallback ? call_user_func($this->dataCallback) : [],
            'options' => $this->options,
        ];
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['chartType'] = $this->chartType;
        $data['options'] = $this->options;

        $data = $this->addDataRouteToArray($data);
        $data = $this->addRefreshIntervalToArray($data);

        return $data;
    }
}
