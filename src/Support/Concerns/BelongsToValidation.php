<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Concerns;

trait BelongsToValidation
{
    protected bool $isRequired = false;

    protected ?int $minLength = null;

    protected ?int $maxLength = null;

    /**
     * Define o campo como obrigatório
     */
    public function required(bool $required = true): static
    {
        $this->isRequired = $required;

        return $this;
    }

    /**
     * Define o comprimento mínimo do campo
     */
    public function minLength(int $length): static
    {
        $this->minLength = $length;

        return $this;
    }

    /**
     * Define o comprimento máximo do campo
     */
    public function maxLength(int $length): static
    {
        $this->maxLength = $length;

        return $this;
    }

    /**
     * Verifica se o campo é obrigatório
     */
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * Retorna o comprimento mínimo
     */
    public function getMinLength(): ?int
    {
        return $this->minLength;
    }

    /**
     * Retorna o comprimento máximo
     */
    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }
}
