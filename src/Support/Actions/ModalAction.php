<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Actions;

use Callcocam\PapaLeguas\Support\Form\Concerns\InteractWithForm;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ModalAction extends Action
{
    use InteractWithForm;

    protected string $component = 'action-modal';

    protected string|Closure|null $modalTitle = null;

    protected string|Closure|null $modalDescription = null;

    protected string|Closure|null $modalContent = null;

    protected string|Closure|null $modalType = 'normal';

    protected string|Closure|null $slideoverPosition = 'right';

    protected array|Closure|null $fillData = null;

    public function __construct(?string $name)
    {
        parent::__construct($name ?? 'modal-action');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon('Eye') 
            ->method('POST');
    }

    public function modalTitle(string|Closure|null $modalTitle): self
    {
        $this->modalTitle = $modalTitle;

        return $this;
    }

    public function getModalTitle(array $context = []): ?string
    {
        return $this->evaluate($this->modalTitle, $context);
    }

    public function modalDescription(string|Closure|null $modalDescription): static
    {
        $this->modalDescription = $modalDescription;

        return $this;
    }

    public function getModalDescription(array $context = []): ?string
    {
        return $this->evaluate($this->modalDescription, $context);
    }

    public function modalContent(string|Closure|null $modalContent): self
    {
        $this->modalContent = $modalContent;

        return $this;
    }

    public function getModalContent(array $context = []): ?string
    {
        return $this->evaluate($this->modalContent, $context);
    }

    public function modalType(string|Closure|null $modalType): self
    {
        $this->modalType = $modalType;

        if ($modalType === 'slideover') {
            $this->component = 'action-modal-slideover';
        }

        return $this;
    }

    public function getModalType(array $context = []): ?string
    {
        return $this->evaluate($this->modalType, $context);
    }

    public function slideover(): self
    {
        return $this->modalType('slideover');
    }

    public function slideoverPosition(string|Closure|null $position): self
    {
        $this->slideoverPosition = $position;

        return $this;
    }

    public function getSlideoverPosition(array $context = []): ?string
    {
        return $this->evaluate($this->slideoverPosition, $context);
    }

    public function slideoverLeft(): self
    {
        return $this->slideover()->slideoverPosition('left');
    }

    public function slideoverRight(): self
    {
        return $this->slideover()->slideoverPosition('right');
    }

    public function fillUsing(array|Closure $fillData): self
    {
        $this->fillData = $fillData;

        return $this;
    }

    public function getFillData(Model $model): ?array
    {
        if ($this->fillData === null) {
            return null;
        }

        if ($this->fillData instanceof Closure) {
            return $this->evaluate($this->fillData, [
                'record' => $model,
            ]);
        }

        return $this->fillData;
    }

    /**
     * Executa todas as verificações de permissão em sequência
     *
     * @param  mixed  $item  Item específico para verificação (opcional)
     */
    protected function validatePermissions($item = null): bool
    {
        return true;
    }

    /**
     * Renderiza a action para um modelo específico
     */
    public function renderForModel(Model $model, ?Request $request = null): ?array
    {
        $parentRender = parent::renderForModel($model, $request);

        if ($parentRender === null) {
            return null;
        }

        $form = $this->getForm();

        return array_merge($parentRender, [
            'modalTitle' => $this->getModalTitle([
                'record' => $model,
            ]),
            'modalDescription' => $this->getModalDescription([
                'record' => $model,
            ]),
            'modalContent' => $this->getModalContent([
                'record' => $model,
            ]),
            'modalType' => $this->getModalType([
                'record' => $model,
            ]),
            'slideoverPosition' => $this->getSlideoverPosition([
                'record' => $model,
            ]),
            'fillData' => $this->getFillData($model),
            'columns' => $form['columns'] ?? null,
            'form' => $form,
        ]);
    }
}
