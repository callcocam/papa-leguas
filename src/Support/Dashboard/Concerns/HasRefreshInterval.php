<?php

namespace Callcocam\PapaLeguas\Support\Dashboard\Concerns;

trait HasRefreshInterval
{
    protected ?int $refreshInterval = null;

    public function refreshInterval(int $seconds): static
    {
        $this->refreshInterval = $seconds;

        return $this;
    }

    public function getRefreshInterval(): ?int
    {
        return $this->refreshInterval;
    }

    protected function addRefreshIntervalToArray(array $data): array
    {
        if ($this->refreshInterval !== null) {
            $data['refreshInterval'] = $this->refreshInterval;
        }

        return $data;
    }
}
