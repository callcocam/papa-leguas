<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Papaleguas\Services\Menu\Contracts;

use Callcocam\Papaleguas\Enums\Menu\ContextEnum;

interface MenuBuilderInterface
{
    /**
     * Define o contexto do menu
     */
    public function setContext(ContextEnum $context): self;

    /**
     * Constrói o menu
     */
    public function build(): self;

    /**
     * Renderiza o menu como array
     */
    public function render(): array;

    /**
     * Usa cache para o menu
     */
    public function withCache(bool $useCache = true): self;

    /**
     * Define se deve incluir grupos
     */
    public function withoutGroups(bool $withoutGroups = true): self;
}