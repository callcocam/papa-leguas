<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Table;

use Callcocam\PapaLeguas\Support\Cast\CastRegistry;
use Callcocam\PapaLeguas\Support\Concerns;
use Callcocam\PapaLeguas\Support\Table\Concerns\IteractWithTable;
use Callcocam\PapaLeguas\Support\Table\Sources\AbstractSource;
use Callcocam\PapaLeguas\Support\Table\Sources\ModelSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class TableBuilder
{
    use Concerns\BelongToRequest;
    use Concerns\InteractWithActions;
    use Concerns\InteractWithBulkActions;
    use Concerns\InteractWithColumns;
    use Concerns\InteractWithFilters;
    use Concerns\InteractWithHeaderActions;
    use IteractWithTable;

    protected array $config = [
        'auto_detect_casts' => true,
    ];

    public function __construct($model = null, $type = 'model')
    {

        // === INICIALIZAÇÃO DO SISTEMA AUTOMÁTICO COMPLETO ===
        CastRegistry::initialize(); // Carrega formatadores padrão

        $this->dataSource = match ($type) {
            'model' => ModelSource::makeForModel($model, $this->config)->context($this),
            default => $model,
        };

        // ✅ CORREÇÃO: Inicializar após contexto definido
        if ($this->dataSource && method_exists($this->dataSource, 'initialize')) {
            $this->dataSource->initialize();
        }
    }

    public static function make($model = null, $type = 'model'): static
    {
        return new static($model, $type);
    }

    /**
     * Define configuração
     */
    public function config(string $key, mixed $value): static
    {
        $this->config[$key] = $value;

        return $this;
    }

    /**
     * Set default configuration
     */
    public function defaults(array $config): static
    {
        $this->config = array_merge($this->config, $config);
        if ($this->dataSource) {
            $this->dataSource->setConfig($this->config);
        }

        return $this;
    }

    /**
     * Obtém configuração
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Obtém o data source
     */
    public function setDataSource(AbstractSource $dataSource): static
    {
        $this->dataSource = $dataSource;

        return $this;
    }

    public function getDataSource(): AbstractSource
    {
        return $this->dataSource;
    }

    /**
     * Define o modelo base
     */
    public function model(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Define a query base
     */
    public function baseQuery(Builder $query): static
    {
        $this->dataSource->setBaseQuery($query);

        return $this;
    }

    /**
     * Obtem os scope de relacionamento
     */
    public function getScopes(): array
    {
        return [];
    }

    /**
     * 🔄 CORREÇÃO: Métodos que o AbstractSource precisa para paginação/ordenação
     */
    public function getOrderBy(): array
    {
        $request = $this->getRequest();

        if (! $request) {
            return ['id' => 'desc']; // Default fallback
        }

        // ✅ Processar sort e direction da request
        $sort = $request->input('sort');
        $direction = $request->input('direction', 'asc');

        if ($sort) {
            return [$sort => strtolower($direction)];
        }

        return ['id' => 'desc']; // Default se não tiver sort
    }

    public function getSearch(): ?string
    {
        $request = $this->getRequest();

        return $request?->input('search');
    }

    public function render($params = []): array
    {

        $paginated = $this->toArray();
        Storage::disk('local')->put('table.json', json_encode($paginated));

        return $paginated;
    }

    protected function getResourceName(): string
    {
        return $this->getContext()->getPluralModelName();
    }

    protected function getTitle(): string
    {

        return $this->getContext()->getSingleModelName();
    }

    protected function getDescription(): ?string
    {
        return $this->getContext()->getDescription();
    }

    protected function getEndpoint(): string
    {
        $name = sprintf('api.%s.%s', request()->getContext(), $this->getResourceName());
        if (\Illuminate\Support\Facades\Route::has($name)) {
            return route($name, $this->getModel()->id);
        }

        return '#';
    }

    protected function getBreadcrumbs(): array
    {
        return [
            ['title' => 'Home', 'href' => '/', 'current' => false],
            ['title' => $this->getTitle(), 'href' => '#', 'current' => true],
        ];
    }
}
