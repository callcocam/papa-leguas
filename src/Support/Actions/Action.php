<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Actions;

use Callcocam\PapaLeguas\Support\AbstractColumn;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class Action extends AbstractColumn
{
    protected string $method = 'POST';

    protected string $target = '_self';

    protected string|Closure|bool|null $url = null;

    protected array|Closure $confirm = [];

    protected string|Closure|null $authorization = null;

    protected ?string $position = null;

    protected string $component = 'LinkButton';

    protected ?Closure $callbackAction = null;

    protected Closure|string|null $to = null;

    public function __construct(?string $name)
    {
        $this->name($name);
        $this->url(function ($target) {
            $name = sprintf('api.%s.%s', request()->getContext(), $this->name);
            if (\Illuminate\Support\Facades\Route::has($name)) {
                return $target instanceof Model
                    ? route($name, $target->id, false)
                    : route($name, [], false);
            }

            return '#';
        });
        $this->setUp();
    }

    protected function setUp(): void
    {
        // Método HTTP padrão
        $this->method = strtoupper($this->method);
    }

    public function action(Closure $callback): self
    {
        $this->callbackAction = $callback;

        return $this;
    }

    public function getCallbackAction(): ?Closure
    {
        return $this->callbackAction;
    }
    /**
     * Verificação de autorização (só valida se houver authorization configurado)
     */
    public function authorize(Model|Collection|LengthAwarePaginator|array $target, ?Request $request = null): bool
    {
        // Se for string, verifica via Gate
        return Gate::allows($this->evaluate($this->authorization, [
            'target' => $target,
            'request' => $request,
        ]), $target instanceof Model ? $target : null);
    }

    /**
     * Gera URL da ação
     */
    public function getUrl(Model|Collection|LengthAwarePaginator|array $target): string
    {
        // Se url for Closure, avalia e retorna
        return $this->evaluate($this->url, [
            'target' => $target,
        ]);
    }

    // ========== Fluent Interface ==========

    public function authorizeUsing(string|Closure $authorization): self
    {
        $this->authorization = $authorization;

        return $this;
    }

    public function confirm(array|bool|Closure $confirm): self
    {
        $this->confirm = $confirm;

        return $this;
    }

    public function url(string|Closure $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function method(string $method): self
    {
        $this->method = strtoupper($method);

        return $this;
    }

    public function target(string $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function openInNewTab(): self
    {
        return $this->target('_blank');
    }

    public function openInModal(): self
    {
        return $this->target('modal');
    }

    public function position(string $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Retorna URL como template
     */
    public function getUrlTemplate(): ?string
    {
        return $this->evaluate($this->url, [
            'target' => null,
        ]);
    }

    public function to(string|Closure|null $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function getTo(?Model $model = null, ?string $url = null): mixed
    {

        return $this->evaluate($this->to, [
            'model' => $model,
            'url' => $url,
        ]);
    }

    /**
     * Configuração estruturada de confirmação
     */
    public function getConfirmationConfig(): ?array
    {

        $default = [
            'title' => 'Confirmar ação',
            'message' => 'Tem certeza que deseja executar esta ação?',
            'confirmText' => 'Confirmar',
            'cancelText' => 'Cancelar',
            'confirmColor' => $this->getColor() ?? 'primary',
        ];

        return array_merge($default, $this->evaluate($this->confirm));
    }

    /**
     * Renderiza a action
     */
    public function render($value): array
    {
        return array_merge(
            $this->toArray(),
            $this->getGridLayoutConfig(),
            ['action' => $this->renderForModel($value)]
        );
    }

    /**
     * Renderiza a action para um modelo específico
     */
    public function renderForModel(Model $model, ?Request $request = null): ?array
    {
        try {
            // Verifica visibilidade
            $visible = $this->isVisible($model);
            if (! $visible) {
                return [
                    'visible' => false,
                ];
            }
            
            // Verifica autorização
            $authorized = $this->authorize($model, $request);
            if (! $authorized) {
                return [
                    'authorized' => false,
                ];
            }
            // Obtém a URL
            $url = $this->getUrl($model);
            
            // Se não conseguiu gerar URL, não renderiza
            if (! $url) {
                return null;
            }

            return [
                'name' => $this->getName(),
                'label' => $this->getLabel(),
                'type' => $this->getType(),
                'icon' => $this->getIcon(),
                'tooltip' => $this->getTooltip(),
                'options' => $this->getOptions(),
                'component' => $this->component,
                'method' => $this->method,
                'target' => $this->target,
                'url' => $url,
                'color' => $this->evaluate($this->color, [
                    'model' => $model,
                ]),
                'confirm' => $this->evaluate($this->confirm, [
                    'model' => $model,
                ]),
                'position' => $this->position,
                'action' => $url,
                'visible' => $visible,
                'authorized' => $authorized,
                'to' => $this->getTo($model, $url)
            ];
        } catch (\Exception) {
            return null;
        }
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'method' => $this->method,
            'target' => $this->target,
            'url' => $this->getUrlTemplate(),
            'color' => $this->getColor(),
            'confirm' => $this->getConfirmationConfig(),
            'position' => $this->position,
            'component' => $this->getComponent(),
            'to' => $this->getTo(),
        ]);
    }
}
