<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Concerns;

use Callcocam\PapaLeguas\Support\AbstractColumn;
use Callcocam\PapaLeguas\Support\Actions\CreateAction;

trait InteractWithHeaderActions
{
    protected array $headerActions = [];

    public function headerActions(array $headerActions): static
    {
        foreach ($headerActions as $action) {
            $this->headerAction($action);
        }

        return $this;
    }

    public function headerAction(AbstractColumn $action): static
    {
        $this->headerActions[] = $action;

        return $this;
    }

    public function createHeaderAction(string $name, string $label): static
    {
        $action = CreateAction::make($name)
            ->label($label);
        $this->headerAction($action);
        return $this;
    }

    /**
     * @return array<AbstractColumn>
     */
    public function getArrayHeaderActions(): array
    {
        return array_map(function (AbstractColumn $action) {
            return $action->toArray();
        }, $this->headerActions);
    }

    public function getHeaderActions(): array
    {
        return $this->headerActions;
    }
}
