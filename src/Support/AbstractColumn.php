<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support;

use Callcocam\PapaLeguas\Support\Cast\CastRegistry;

abstract class AbstractColumn
{
    use Concerns\BelongsToColor;
    use Concerns\BelongsToIcon;
    use Concerns\BelongsToId;
    use Concerns\BelongsToLabel;
    use Concerns\BelongsToName;
    use Concerns\BelongsToOptions;
    use Concerns\BelongsToTooltip;
    use Concerns\BelongsToType;
    use Concerns\BelongsToVisible;
    use Concerns\EvaluatesClosures;
    use Concerns\FactoryPattern;
    use Concerns\HasCastCallbackFormatter;
    use Concerns\HasGridLayout;

    protected string $component = '';

    /**
     * Método para ser sobrescrito por classes filhas para configuração inicial
     */
    protected function setUp(): void
    {
        //
    }

    /**
     * Define o componente a ser usado
     */
    public function component(string $component): self
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Retorna o componente configurado
     */
    public function getComponent(): string
    {
        return $this->component;
    }

    /**
     * Renderiza o valor da coluna
     */
    public function render($value): array
    {
        $columnName = $this->getName();
        $formatted = '';

        // Usa formatador da coluna se existir
        if ($this->castCallback) {
            $formatted = $this->evaluate($this->castCallback, ['value' => $value, 'column' => $this]);
        }
        // Senão usa auto-formatação
        elseif ($value !== null) {
            $formatted = CastRegistry::autoFormat($value, $columnName);
        }

        return array_merge($this->toArray(), $this->getGridLayoutConfig(), [
            'text' => (string) $formatted,
            'value' => $value,
        ]);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'type' => $this->getType(),
            'icon' => $this->getIcon(),
            'tooltip' => $this->getTooltip(),
            'options' => $this->getOptions(),
            'component' => $this->getComponent(),
            'visible' => $this->isVisible(),
        ];
    }
}
