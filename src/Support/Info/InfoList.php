<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Info;

use Callcocam\PapaLeguas\Support\Concerns;

class InfoList
{
    use Concerns\FactoryPattern;
    use Concerns\HasGridLayout;
    use Concerns\InteractWithActions;
    use Concerns\InteractWithColumns;

    /**
     * Retorna as configurações de layout do InfoList
     */
    public function getLayoutConfig(): array
    {
        return $this->getGridLayoutConfig();
    }

    public function render(array $data): array
    {
        $renderedData = [];
        foreach ($this->columns as $column) {
            $columnName = $column->getName();
            $value = $data[$columnName] ?? null;
            $rendered = $column->render($value);

            // Se a coluna tem sub-colunas para renderizar (CardColumn)
            if (isset($rendered['_columns_to_render'])) {
                $childColumns = $rendered['_columns_to_render'];
                $renderedColumns = [];

                foreach ($childColumns as $childColumn) {
                    $childName = $childColumn->getName();
                    $childValue = $data[$childName] ?? null;

                    // Pula valores vazios
                    if ($childValue === null || $childValue === '') {
                        continue;
                    }

                    $renderedColumns[$childName] = array_merge(
                        $childColumn->render($childValue),
                        [
                            'id' => $childName,
                            'label' => $childColumn->getLabel(),
                        ]
                    );
                }

                // Substitui o array vazio de columns pelo renderizado
                $rendered['columns'] = $renderedColumns;
                // Remove a metadata
                unset($rendered['_columns_to_render']);
            }

            $renderedData[$columnName] = $rendered;
        }

        return array_merge($renderedData, [
            'viewActions' => $this->getArrayActions(),
        ]);
    }
}
