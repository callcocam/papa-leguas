<?php

namespace Callcocam\PapaLeguas\Support\Dashboard\Widgets;

use Callcocam\PapaLeguas\Support\Dashboard\Concerns\HasDataRoute;
use Callcocam\PapaLeguas\Support\Dashboard\Concerns\HasRefreshInterval;
use Callcocam\PapaLeguas\Support\Dashboard\Widget;
use Closure;

class TableWidget extends Widget
{
    use HasDataRoute;
    use HasRefreshInterval;

    protected string $type = 'table';

    protected array $columns = [];

    protected ?Closure $dataCallback = null;

    protected int $perPage = 5;

    protected bool $paginated = false;

    public function columns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    public function data(Closure $callback): static
    {
        $this->dataCallback = $callback;

        return $this;
    }

    public function perPage(int $perPage): static
    {
        $this->perPage = $perPage;
        $this->paginated = true;

        return $this;
    }

    public function paginated(bool $paginated = true): static
    {
        $this->paginated = $paginated;

        return $this;
    }

    public function getData(): array
    {
        $rows = $this->dataCallback ? call_user_func($this->dataCallback) : [];

        return [
            'rows' => $rows,
            'columns' => $this->columns,
            'pagination' => $this->paginated ? [
                'perPage' => $this->perPage,
                'total' => count($rows),
            ] : null,
        ];
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['columns'] = $this->columns;
        $data['paginated'] = $this->paginated;
        $data['perPage'] = $this->perPage;

        $data = $this->addDataRouteToArray($data);
        $data = $this->addRefreshIntervalToArray($data);

        return $data;
    }
}
