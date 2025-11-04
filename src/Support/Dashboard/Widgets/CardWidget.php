<?php

namespace Callcocam\PapaLeguas\Support\Dashboard\Widgets;

use Callcocam\PapaLeguas\Support\Dashboard\Concerns\HasDataRoute;
use Callcocam\PapaLeguas\Support\Dashboard\Concerns\HasRefreshInterval;
use Callcocam\PapaLeguas\Support\Dashboard\Widget;
use Closure;

class CardWidget extends Widget
{
    use HasDataRoute;
    use HasRefreshInterval;

    protected string $type = 'card';

    protected ?Closure $contentCallback = null;

    protected array $actions = [];

    public function content(Closure $callback): static
    {
        $this->contentCallback = $callback;

        return $this;
    }

    public function actions(array $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function getData(): array
    {
        return [
            'content' => $this->contentCallback ? call_user_func($this->contentCallback) : null,
            'actions' => $this->actions,
        ];
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['actions'] = $this->actions;

        $data = $this->addDataRouteToArray($data);
        $data = $this->addRefreshIntervalToArray($data);

        return $data;
    }
}
