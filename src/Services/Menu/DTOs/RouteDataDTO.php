<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Papaleguas\Services\Menu\DTOs;

class RouteDataDTO
{
    public function __construct(
        public readonly string $path,
        public readonly string $name,
        public readonly string $component,
        public readonly array $meta,
        public readonly array $children = [],
    ) {}

    /**
     * Cria dados de rota para um método específico
     */
    public static function forMethod(
        string $resource,
        string $method,
        string $label,
        string $icon,
        ?string $component = null,
        array $children = [],
    ): ?self {
        // Base path para componentes

        return match ($method) {
            // List é rota filha sem path (usa o path pai)
            'list' => new self(
                path: '',
                name: "{$resource}.list",
                component: $component,
                meta: [
                    'title' => $label,
                    'icon' => $icon,
                    'action' => 'list',
                    'resource' => $resource,
                    'requiresAuth' => true,
                ],
                children: $children
            ),

            // Create é rota filha com path relativo
            'create' => new self(
                path: 'create',
                name: "{$resource}.create",
                component: $component,
                meta: [
                    'title' => 'Criar '.str($label)->singular(),
                    'icon' => 'Plus',
                    'action' => 'create',
                    'resource' => $resource,
                    'requiresAuth' => true,
                ]
            ),

            // Show é rota filha com parâmetro
            'show' => new self(
                path: ':id',
                name: "{$resource}.show",
                component: $component,
                meta: [
                    'title' => 'Visualizar '.str($label)->singular(),
                    'icon' => 'Eye',
                    'action' => 'show',
                    'resource' => $resource,
                    'requiresAuth' => true,
                ]
            ),

            // Edit é rota filha com parâmetro
            'edit' => new self(
                path: ':id/edit',
                name: "{$resource}.edit",
                component: $component,
                meta: [
                    'title' => 'Editar '.str($label)->singular(),
                    'icon' => 'Edit',
                    'action' => 'edit',
                    'resource' => $resource,
                    'requiresAuth' => true,
                ]
            ),

            // Destroy é rota filha da list para exclusão
            'destroy' => new self(
                path: ':id/delete',
                name: "{$resource}.destroy",
                component: $component ?? 'views/crud/Delete.vue',
                meta: [
                    'title' => 'Excluir '.str($label)->singular(),
                    'icon' => 'Trash',
                    'action' => 'destroy',
                    'resource' => $resource,
                    'requiresAuth' => true,
                    'hidden' => true, // Não aparece no menu, apenas action na tabela
                    // Configuração de confirmação por digitação (pode ser sobrescrito)
                    'requiresTypedConfirmation' => config('papa-leguas.delete_requires_typed_confirmation', false),
                    'typedConfirmationWord' => config('papa-leguas.delete_typed_confirmation_word', 'EXCLUIR'),
                ]
            ),

            default => null,
        };
    }

    /**
     * Converte para array
     */
    public function toArray(): array
    {
        $data = [
            'path' => $this->path,
            'name' => $this->name,
            'component' => $this->component,
            'meta' => $this->meta,
        ];

        // Adiciona children se houver
        if (! empty($this->children)) {
            $data['children'] = $this->children;
        }

        return $data;
    }

    /**
     * Gera o nome componente com base no recurso e ação
     */
    protected static function generateComponentName(string $resource, string $action, array $crudFilesPath): string
    {
        if (isset($crudFilesPath[$action])) {
            return $crudFilesPath[$action];
        }

        return sprintf('%s/%s.vue', $resource, ucfirst($action));
    }
}
