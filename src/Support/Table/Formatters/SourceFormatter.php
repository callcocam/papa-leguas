<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Table\Formatters;

use Callcocam\PapaLeguas\Support\Cast\EloquentCastIntegration;
use Callcocam\PapaLeguas\Support\Cast\ModelIntrospection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * SourceFormatter - Responsável APENAS por formatação e transformação de dados
 *
 * Responsabilidades:
 * - Detectar e registrar casts do modelo
 * - Aplicar formatação de colunas
 * - Transformar valores de relacionamentos
 * - Aplicar apresentação de dados
 *
 * NÃO É responsável por:
 * - Leitura de dados (use ModelSource)
 * - Filtragem (use ModelSource)
 * - Construção de queries (use ModelSource)
 */
class SourceFormatter
{
    protected array $config = [];

    protected ?array $modelIntrospection = null;

    protected array $columns = [];

    public function __construct(array $config = [], array $columns = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->columns = $columns;
    }

    /**
     * Configuração padrão
     */
    protected function getDefaultConfig(): array
    {
        return [
            'auto_detect_casts' => true, // Auto-detecta casts do modelo
            'relationship_separator' => '.', // Separador para valores de relacionamento
        ];
    }

    /**
     * Detecta configuração do modelo e registra casts
     */
    public function detectModelConfiguration(Model|string $model): void
    {
        if (! $model) {
            return;
        }

        $modelClass = is_string($model) ? $model : get_class($model);

        try {
            // Introspecção do modelo
            $this->modelIntrospection = ModelIntrospection::analyze($modelClass);

            // Auto-detecção de casts
            if ($this->config['auto_detect_casts']) {
                EloquentCastIntegration::detectAndRegister($modelClass);
            }
        } catch (\Exception $e) {
            // Ignora erros de detecção silenciosamente
        }
    }

    /**
     * Formata uma collection de items
     */
    public function formatCollection(Collection $items): Collection
    {
        return $items->transform(function ($item) {
            return $this->formatItem($item);
        });
    }

    /**
     * Formata um item individual
     */
    public function formatItem($item)
    {
        if (! $item instanceof Model) {
            return $item;
        }

        // Aplica formatação de cada coluna
        foreach ($this->columns as $column) {
            if (! $column->isFormatted()) {
                continue;
            }

            $columnName = $column->getName();
            $value = $this->getColumnValue($item, $columnName);

            // Aplica formatação da coluna
            if (method_exists($column, 'format')) {
                $value = $column->format($value, $item);
            }

            $this->setColumnValue($item, $columnName, $value);
        }

        return $item;
    }

    /**
     * Obtém valor de coluna (suporta relacionamentos)
     */
    protected function getColumnValue($model, string $column)
    {
        if ($this->isRelationshipColumn($column)) {
            return $this->getRelationshipValue($model, $column);
        }

        return data_get($model, $column);
    }

    /**
     * Define valor de coluna (suporta relacionamentos)
     */
    protected function setColumnValue($model, string $column, $value): void
    {
        if ($this->isRelationshipColumn($column)) {
            $this->setRelationshipValue($model, $column, $value);

            return;
        }

        data_set($model, $column, $value);
    }

    /**
     * Obtém valor de relacionamento
     */
    protected function getRelationshipValue($model, string $column)
    {
        $separator = $this->config['relationship_separator'];

        return data_get($model, str_replace($separator, '.', $column));
    }

    /**
     * Define valor de relacionamento
     */
    protected function setRelationshipValue($model, string $column, $value): void
    {
        $separator = $this->config['relationship_separator'];
        data_set($model, str_replace($separator, '.', $column), $value);
    }

    /**
     * Verifica se é coluna de relacionamento
     */
    protected function isRelationshipColumn(string $column): bool
    {
        return str_contains($column, $this->config['relationship_separator']);
    }

    /**
     * Obtém coluna formatada por nome
     */
    public function getFormattedColumn(string $name)
    {
        foreach ($this->columns as $column) {
            if ($column->getName() === $name && $column->isFormatted()) {
                return $column;
            }
        }

        return null;
    }

    /**
     * Define colunas
     */
    public function setColumns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Obtém introspecção do modelo
     */
    public function getModelIntrospection(): ?array
    {
        return $this->modelIntrospection;
    }

    /**
     * Factory method
     */
    public static function make(array $config = [], array $columns = []): static
    {
        return new static($config, $columns);
    }
}
