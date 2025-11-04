<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Papaleguas\Services\Menu\Contracts;

use Callcocam\Papaleguas\Enums\Menu\ContextEnum;

interface RouteGeneratorInterface
{
    /**
     * Define o contexto das rotas
     */
    public function setContext(ContextEnum $context): self;

    /**
     * Gera as rotas Vue
     */
    public function generate(): array;

    /**
     * Usa cache para as rotas
     */
    public function withCache(bool $useCache = true): self;
}
