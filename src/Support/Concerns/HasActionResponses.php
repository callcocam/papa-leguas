<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Concerns;

use Callcocam\PapaLeguas\Support\Response\ActionResponse;
use Illuminate\Http\JsonResponse;

trait HasActionResponses
{
    /**
     * Retorna uma resposta padronizada com informações do controller
     */
    protected function response(ActionResponse $response): JsonResponse
    {
        return $response
            ->withController(static::class)
            ->withMethod(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'unknown')
            ->toResponse();
    }

    /**
     * Resposta de sucesso
     */
    protected function success(string $message, array $data = []): JsonResponse
    {
        return $this->response(ActionResponse::success($message, $data));
    }

    /**
     * Resposta de erro
     */
    protected function error(string $message, array $data = []): JsonResponse
    {
        return $this->response(ActionResponse::error($message, $data));
    }

    /**
     * Resposta de aviso
     */
    protected function warning(string $message, array $data = []): JsonResponse
    {
        return $this->response(ActionResponse::warning($message, $data));
    }

    /**
     * Resposta de informação
     */
    protected function info(string $message, array $data = []): JsonResponse
    {
        return $this->response(ActionResponse::info($message, $data));
    }

    // ========== Respostas de Ações ==========

    /**
     * Resposta para listagem
     */
    protected function indexResponse(array $data = []): JsonResponse
    {
        return $this->response(ActionResponse::index($data));
    }

    /**
     * Resposta para criação
     */
    protected function createdResponse(array $data = []): JsonResponse
    {
        return $this->response(ActionResponse::created($data));
    }

    /**
     * Resposta para atualização
     */
    protected function updatedResponse(array $data = []): JsonResponse
    {
        return $this->response(ActionResponse::updated($data));
    }

    /**
     * Resposta para exclusão
     */
    protected function deletedResponse(): JsonResponse
    {
        return $this->response(ActionResponse::deleted());
    }

    /**
     * Resposta para restauração
     */
    protected function restoredResponse(): JsonResponse
    {
        return $this->response(ActionResponse::restored());
    }

    /**
     * Resposta para duplicação
     */
    protected function duplicatedResponse(array $data = []): JsonResponse
    {
        return $this->response(ActionResponse::duplicated($data));
    }

    /**
     * Resposta para exportação
     */
    protected function exportedResponse(int $count = 0, ?string $fileUrl = null): JsonResponse
    {
        return $this->response(ActionResponse::exported($count, $fileUrl));
    }

    /**
     * Resposta para importação
     */
    protected function importedResponse(int $imported = 0, int $failed = 0, array $errors = []): JsonResponse
    {
        return $this->response(ActionResponse::imported($imported, $failed, $errors));
    }

    /**
     * Resposta para exclusão em massa
     */
    protected function bulkDeletedResponse(int $deleted = 0, int $failed = 0): JsonResponse
    {
        return $this->response(ActionResponse::bulkDeleted($deleted, $failed));
    }

    /**
     * Resposta para restauração em massa
     */
    protected function bulkRestoredResponse(int $restored = 0, int $failed = 0): JsonResponse
    {
        return $this->response(ActionResponse::bulkRestored($restored, $failed));
    }

    /**
     * Resposta para validação falhou
     */
    protected function validationFailedResponse(array $errors): JsonResponse
    {
        return $this->response(ActionResponse::validationFailed($errors));
    }

    /**
     * Resposta para não encontrado
     */
    protected function notFoundResponse(string $resource = 'Recurso'): JsonResponse
    {
        return $this->response(ActionResponse::notFound($resource));
    }

    /**
     * Resposta para não autorizado
     */
    protected function unauthorizedResponse(string $message = 'Você não tem permissão para executar esta ação'): JsonResponse
    {
        return $this->response(ActionResponse::unauthorized($message));
    }

    /**
     * Resposta para operação cancelada
     */
    protected function cancelledResponse(string $message = 'Operação cancelada'): JsonResponse
    {
        return $this->response(ActionResponse::cancelled($message));
    }
}
