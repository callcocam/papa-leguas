<?php

namespace Callcocam\PapaLeguas\Support\Dashboard;

use Illuminate\Support\Str;

abstract class Widget
{
    protected string $id;

    protected string $type;

    protected string $title;

    protected string $description = '';

    protected int $colSpan = 1;

    protected int $rowSpan = 1;

    protected array $config = [];

    protected bool $loadingEager = false;

    public function __construct(string $title)
    {
        $this->id = Str::uuid()->toString();
        $this->title = $title;
    }

    public static function make(string $title): static
    {
        return new static($title);
    }

    abstract public function getData(): array;

    public function id(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function colSpan(int $span): static
    {
        $this->colSpan = $span;

        return $this;
    }

    public function rowSpan(int $span): static
    {
        $this->rowSpan = $span;

        return $this;
    }

    public function config(array $config): static
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    public function loadingEager(bool $eager = true): static
    {
        $this->loadingEager = $eager;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'colSpan' => $this->colSpan,
            'rowSpan' => $this->rowSpan,
            'config' => $this->config,
            'loadingEager' => $this->loadingEager,
        ];
    }
}
