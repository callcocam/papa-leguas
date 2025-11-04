<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Response;

use Illuminate\Http\JsonResponse;

class ActionResponse
{
    protected bool $success;

    protected string $message;

    protected string $type;

    protected array $data;

    protected ?string $controller = null;

    protected ?string $method = null;

    protected ?string $timestamp = null;

    public function __construct(
        bool $success = true,
        string $message = '',
        string $type = 'info',
        array $data = []
    ) {
        $this->success = $success;
        $this->message = $message;
        $this->type = $type;
        $this->data = $data;
        $this->timestamp = now()->toDateTimeString();
    }

    /**
     * Cria uma resposta de sucesso
     */
    public static function success(string $message, array $data = [], string $type = 'success'): self
    {
        return new self(true, $message, $type, $data);
    }

    /**
     * Cria uma resposta de erro
     */
    public static function error(string $message, array $data = [], string $type = 'error'): self
    {
        return new self(false, $message, $type, $data);
    }

    /**
     * Cria uma resposta de aviso
     */
    public static function warning(string $message, array $data = [], string $type = 'warning'): self
    {
        return new self(true, $message, $type, $data);
    }

    /**
     * Cria uma resposta de informação
     */
    public static function info(string $message, array $data = [], string $type = 'info'): self
    {
        return new self(true, $message, $type, $data);
    }

    // ========== Respostas Padronizadas de Ações ==========

    /**
     * Resposta para listagem de recursos
     */
    public static function index(array $data = []): self
    {
        return self::success('Listagem de recursos carregada', $data, 'info');
    }

    /**
     * Resposta para criação de recurso
     */
    public static function created(array $data = []): self
    {
        return self::success('Recurso criado com sucesso', $data, 'success');
    }

    /**
     * Resposta para atualização de recurso
     */
    public static function updated(array $data = []): self
    {
        return self::success('Recurso atualizado com sucesso', $data, 'success');
    }

    /**
     * Resposta para exclusão de recurso
     */
    public static function deleted(): self
    {
        return self::success('Recurso removido com sucesso', [], 'success');
    }

    /**
     * Resposta para restauração de recurso
     */
    public static function restored(): self
    {
        return self::success('Recurso restaurado com sucesso', [], 'success');
    }

    /**
     * Resposta para duplicação de recurso
     */
    public static function duplicated(array $data = []): self
    {
        return self::success('Recurso duplicado com sucesso', $data, 'success');
    }

    /**
     * Resposta para exportação
     */
    public static function exported(int $count = 0, ?string $fileUrl = null): self
    {
        $message = $count > 0
            ? "Exportação realizada com sucesso: {$count} registro".($count > 1 ? 's' : '')
            : 'Exportação realizada com sucesso';

        return self::success($message, [
            'exported' => $count,
            'file_url' => $fileUrl,
        ], 'success');
    }

    /**
     * Resposta para importação
     */
    public static function imported(int $imported = 0, int $failed = 0, array $errors = []): self
    {
        $message = "Importação concluída: {$imported} importado".($imported !== 1 ? 's' : '');

        if ($failed > 0) {
            $message .= ", {$failed} com erro".($failed !== 1 ? 's' : '');
        }

        return self::success($message, [
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors,
        ], $failed > 0 ? 'warning' : 'success');
    }

    /**
     * Resposta para exclusão em massa
     */
    public static function bulkDeleted(int $deleted = 0, int $failed = 0): self
    {
        $message = "{$deleted} recurso".($deleted !== 1 ? 's removidos' : ' removido').' com sucesso';

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        return self::success($message, [
            'deleted' => $deleted,
            'failed' => $failed,
        ], $failed > 0 ? 'warning' : 'success');
    }

    /**
     * Resposta para restauração em massa
     */
    public static function bulkRestored(int $restored = 0, int $failed = 0): self
    {
        $message = "{$restored} recurso".($restored !== 1 ? 's restaurados' : ' restaurado').' com sucesso';

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        return self::success($message, [
            'restored' => $restored,
            'failed' => $failed,
        ], $failed > 0 ? 'warning' : 'success');
    }

    /**
     * Resposta para validação falhou
     */
    public static function validationFailed(array $errors): self
    {
        return self::error('Erro de validação', ['errors' => $errors], 'error');
    }

    /**
     * Resposta para recurso não encontrado
     */
    public static function notFound(string $resource = 'Recurso'): self
    {
        return self::error("{$resource} não encontrado", [], 'error');
    }

    /**
     * Resposta para não autorizado
     */
    public static function unauthorized(string $message = 'Você não tem permissão para executar esta ação'): self
    {
        return self::error($message, [], 'error');
    }

    /**
     * Resposta para operação cancelada
     */
    public static function cancelled(string $message = 'Operação cancelada'): self
    {
        return self::warning($message, [], 'warning');
    }

    // ========== Builders ==========

    public function withController(string $controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    public function withMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function withData(array $data): self
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    public function mergeData(array $data): self
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Converte para JsonResponse
     */
    public function toResponse(): JsonResponse
    {
        $response = [
            'success' => $this->success,
            'message' => $this->message,
            'type' => $this->type,
            'timestamp' => $this->timestamp,
            'data' => $this->data,
        ];

        if ($this->controller) {
            $response['controller'] = $this->controller;
        }

        if ($this->method) {
            $response['method'] = $this->method;
        }

        $statusCode = $this->success ? 200 : 400;

        return response()->json($response, $statusCode);
    }

    /**
     * Converte para array
     */
    public function toArray(): array
    {
        $response = [
            'success' => $this->success,
            'message' => $this->message,
            'type' => $this->type,
            'timestamp' => $this->timestamp,
            'data' => $this->data,
        ];

        if ($this->controller) {
            $response['controller'] = $this->controller;
        }

        if ($this->method) {
            $response['method'] = $this->method;
        }

        return $response;
    }
}
