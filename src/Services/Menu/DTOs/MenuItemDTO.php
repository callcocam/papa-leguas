<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Papaleguas\Services\Menu\DTOs;

class MenuItemDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly string $icon,
        public readonly ?string $route,
        public readonly ?string $group,
        public readonly int $order,
        public readonly string $singleModelName,
        public readonly string $pluralModelName,
        public readonly string $className,
        public readonly array $methods,
        public readonly ?string $type = null,
        public readonly ?array $children = null,
    ) {}

    /**
     * Cria um item de menu a partir de metadata
     */
    public static function fromMetadata(ControllerMetadataDTO $metadata): self
    {
        return new self(
            id: $metadata->getResourceId(),
            label: $metadata->pluralModelName,
            icon: $metadata->icon,
            route: $metadata->routeName,
            group: $metadata->group,
            order: $metadata->order,
            singleModelName: $metadata->singleModelName,
            pluralModelName: $metadata->pluralModelName,
            className: $metadata->className,
            methods: $metadata->availableMethods,
        );
    }

    /**
     * Cria um item de menu tipo grupo
     */
    public static function createGroup(
        string $id,
        string $name,
        string $label,
        string $icon,
        int $order,
        array $children
    ): self {
        return new self(
            id: $id,
            label: $label,
            icon: $icon,
            route: null,
            group: null,
            order: $order,
            singleModelName: $name,
            pluralModelName: $label,
            className: '',
            methods: [],
            type: 'group',
            children: $children,
        );
    }

    /**
     * Converte para array
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'label' => $this->label,
            'icon' => $this->icon,
            'order' => $this->order,
            'active' => false,
            'singleModelName' => $this->singleModelName,
            'pluralModelName' => $this->pluralModelName,
        ];

        if ($this->type === 'group') {
            $data['type'] = 'group';
            $data['name'] = $this->singleModelName;
            $data['children'] = $this->children;
        } else {
            $data['route'] = $this->route;
            $data['group'] = $this->group;
            $data['class'] = $this->className;
            $data['methods'] = $this->methods;
        }

        return $data;
    }

    /**
     * Verifica se é um grupo
     */
    public function isGroup(): bool
    {
        return $this->type === 'group';
    }
}