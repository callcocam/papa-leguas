<?php

namespace Callcocam\PapaLeguas\Support\Dashboard\Concerns;

trait HasDataRoute
{
    protected ?string $dataRoute = null;

    public function dataRoute(string $route): static
    {
        $this->dataRoute = $route;

        return $this;
    }

    public function getDataRoute(): ?string
    {
        return $this->dataRoute;
    }

    protected function addDataRouteToArray(array $data): array
    {
        if ($this->dataRoute !== null) {
            $data['dataRoute'] = $this->dataRoute;
        }

        return $data;
    }
}
