<?php

namespace Callcocam\PapaLeguas\Support\Dashboard\Widgets;

use Callcocam\PapaLeguas\Support\Dashboard\Concerns\HasDataRoute;
use Callcocam\PapaLeguas\Support\Dashboard\Concerns\HasRefreshInterval;
use Callcocam\PapaLeguas\Support\Dashboard\Widget;
use Closure;

class ListWidget extends Widget
{
    use HasDataRoute;
    use HasRefreshInterval;

    protected string $type = 'list';

    protected ?Closure $dataCallback = null;

    protected ?Closure $itemLabelCallback = null;

    protected ?Closure $itemDescriptionCallback = null;

    protected ?Closure $itemIconCallback = null;

    protected int $limit = 5;

    public function data(Closure $callback): static
    {
        $this->dataCallback = $callback;

        return $this;
    }

    public function itemLabel(Closure $callback): static
    {
        $this->itemLabelCallback = $callback;

        return $this;
    }

    public function itemDescription(Closure $callback): static
    {
        $this->itemDescriptionCallback = $callback;

        return $this;
    }

    public function itemIcon(Closure $callback): static
    {
        $this->itemIconCallback = $callback;

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function getData(): array
    {
        $rawItems = $this->dataCallback ? call_user_func($this->dataCallback) : [];
        $limitedItems = array_slice($rawItems, 0, $this->limit);

        $items = array_map(function ($item) {
            return [
                'label' => $this->itemLabelCallback ? call_user_func($this->itemLabelCallback, $item) : '',
                'description' => $this->itemDescriptionCallback ? call_user_func($this->itemDescriptionCallback, $item) : '',
                'icon' => $this->itemIconCallback ? call_user_func($this->itemIconCallback, $item) : null,
            ];
        }, $limitedItems);

        return [
            'items' => $items,
            'total' => count($rawItems),
        ];
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['limit'] = $this->limit;

        $data = $this->addDataRouteToArray($data);
        $data = $this->addRefreshIntervalToArray($data);

        return $data;
    }
}
