<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Papaleguas\Services\Menu\Cache;

use Callcocam\Papaleguas\Enums\Menu\ContextEnum;
use Illuminate\Support\Facades\Cache;

class MenuCacheService
{
    protected string $prefix = 'menu_builder';
    protected int $ttl = 86400; // 24 horas

    /**
     * Gera chave de cache
     */
    protected function getCacheKey(ContextEnum $context, string $type): string
    {
        return sprintf('%s.%s.%s', $this->prefix, $context->value, $type);
    }

    /**
     * Obtém do cache ou executa callback
     */
    public function remember(ContextEnum $context, string $type, callable $callback): mixed
    {
        $key = $this->getCacheKey($context, $type);
        
        return Cache::remember($key, $this->ttl, $callback);
    }

    /**
     * Armazena no cache
     */
    public function put(ContextEnum $context, string $type, mixed $data): bool
    {
        $key = $this->getCacheKey($context, $type);
        
        return Cache::put($key, $data, $this->ttl);
    }

    /**
     * Obtém do cache
     */
    public function get(ContextEnum $context, string $type): mixed
    {
        $key = $this->getCacheKey($context, $type);
        
        return Cache::get($key);
    }

    /**
     * Verifica se existe no cache
     */
    public function has(ContextEnum $context, string $type): bool
    {
        $key = $this->getCacheKey($context, $type);
        
        return Cache::has($key);
    }

    /**
     * Remove do cache
     */
    public function forget(ContextEnum $context, string $type): bool
    {
        $key = $this->getCacheKey($context, $type);
        
        return Cache::forget($key);
    }

    /**
     * Limpa todo o cache de menus
     */
    public function flush(): void
    {
        foreach (ContextEnum::all() as $context) {
            $this->forget($context, 'menu');
            $this->forget($context, 'routes');
        }
    }

    /**
     * Limpa cache de um contexto específico
     */
    public function flushContext(ContextEnum $context): void
    {
        $this->forget($context, 'menu');
        $this->forget($context, 'routes');
    }

    /**
     * Define o TTL do cache
     */
    public function setTtl(int $seconds): self
    {
        $this->ttl = $seconds;
        return $this;
    }

    /**
     * Define o prefixo do cache
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }
}