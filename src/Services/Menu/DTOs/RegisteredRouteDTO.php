<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Papaleguas\Services\Menu\DTOs;

use Illuminate\Routing\Route;
use Illuminate\Support\Str;

/**
 * DTO que representa uma rota registrada no Laravel
 *
 * Este DTO encapsula informações de uma rota extraída de Route::getRoutes(),
 * incluindo metadata do controller associado.
 */
class RegisteredRouteDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $uri,
        public readonly string $method,
        public readonly string $action,
        public readonly string $controller,
        public readonly array $middleware,
        public readonly ?ControllerMetadataDTO $metadata = null,
    ) {}

    /**
     * Cria um RegisteredRouteDTO a partir de uma Route do Laravel
     */
    public static function fromRoute(Route $route): ?self
    {
        // Obtém o action da rota (Controller@method ou Closure)
        $action = $route->getAction();

        // Pula rotas que não têm controller (Closures, etc)
        if (! isset($action['controller'])) {
            return null;
        }

        // Parse do controller e method
        $controllerAction = $action['controller'];

        // Se for string "Controller@method"
        if (is_string($controllerAction)) {
            [$controllerClass, $methodName] = Str::parseCallback($controllerAction, null);
        } else {
            // Se for array [Controller::class, 'method']
            if (is_array($controllerAction) && count($controllerAction) === 2) {
                [$controllerClass, $methodName] = $controllerAction;
            } else {
                return null;
            }
        }

        if (! $controllerClass || ! $methodName) {
            return null;
        }

        // Tenta instanciar o controller para extrair metadata
        try {
            $instance = app()->make($controllerClass);

            // Verifica se o controller tem o trait HasMenuMetadata (incluindo traits de classes pai)
            if (! method_exists($instance, 'getNavigationIcon')) {
                // Controller não tem o trait HasMenuMetadata, metadata fica null
                $metadata = null;
            } else {
                // Extrai metadata do controller
                $metadata = ControllerMetadataDTO::fromRoute($route, $instance);
            }
        } catch (\Exception $e) {
            // Se não conseguir instanciar, metadata fica null
            $metadata = null;
        }

        return new self(
            name: $route->getName() ?? '',
            uri: $route->uri(),
            method: implode('|', $route->methods()),
            action: $methodName,
            controller: $controllerClass,
            middleware: $route->middleware(),
            metadata: $metadata
        );
    }

    /**
     * Verifica se é uma rota de API
     */
    public function isApiRoute(): bool
    {
        return Str::startsWith($this->uri, 'api/')
            && in_array('api', $this->middleware);
    }

    /**
     * Verifica se requer autenticação
     */
    public function requiresAuth(): bool
    {
        foreach ($this->middleware as $middleware) {
            if (Str::startsWith($middleware, 'auth')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtém o nome do recurso da rota
     * Ex: "api.tenant.users.index" -> "users"
     */
    public function getResourceName(): ?string
    {
        if (! $this->name) {
            return null;
        }

        $parts = explode('.', $this->name);

        // Remove contexto (api, tenant, landlord) e action (index, show, etc)
        $filtered = array_filter($parts, fn ($part) => ! in_array($part, [
            'api', 'tenant', 'landlord', 'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
        ]));

        return ! empty($filtered) ? reset($filtered) : null;
    }

    /**
     * Verifica se a rota pertence a um contexto específico
     */
    public function belongsToContext(string $context): bool
    {
        return Str::contains($this->name, $context);
    }

    /**
     * Converte para array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'uri' => $this->uri,
            'method' => $this->method,
            'action' => $this->action,
            'controller' => $this->controller,
            'middleware' => $this->middleware,
            'metadata' => $this->metadata?->toArray(),
        ];
    }
}
