<?php

namespace Callcocam\PapaLeguas\Support\Dashboard;

class DashboardLayout
{
    protected array $widgets = [];

    protected int $columns = 3;

    protected string $gap = '1rem';

    public static function make(): static
    {
        return new static;
    }

    public function widgets(array $widgets): static
    {
        $this->widgets = $widgets;

        return $this;
    }

    public function columns(int $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    public function gap(string $gap): static
    {
        $this->gap = $gap;

        return $this;
    }

    public function getWidgets(): array
    {
        return $this->widgets;
    }

    public function getWidget(string $widgetId): ?Widget
    {
        foreach ($this->widgets as $widget) {
            if ($widget->getId() === $widgetId) {
                return $widget;
            }
        }

        return null;
    }

    public function toArray(): array
    {
        return [
            'widgets' => array_map(fn (Widget $widget) => $widget->toArray(), $this->widgets),
            'layout' => [
                'columns' => $this->columns,
                'gap' => $this->gap,
            ],
        ];
    }
}
