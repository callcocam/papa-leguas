<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Form\Columns;

use Callcocam\PapaLeguas\Support\Form\Column;

class UploadField extends Column
{
    protected array $acceptedFileTypes = [];

    protected ?int $maxSize = null;

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->name($name)
            ->label($label ?? 'Upload')
            ->component('form-column-file-upload');
        $this->setUp();
    }

    public function acceptedFileTypes(array $types): self
    {
        $this->acceptedFileTypes = $types;

        return $this;
    }

    public function maxSize(int $sizeInMB): self
    {
        $this->maxSize = $sizeInMB;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'acceptedFileTypes' => $this->acceptedFileTypes,
            'required' => $this->isRequired,
            'maxSize' => $this->maxSize,
            'multiple' => $this->isMultiple(),
        ]);
    }
}
