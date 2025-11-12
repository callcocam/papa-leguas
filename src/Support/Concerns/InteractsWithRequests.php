<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmar.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Concerns;

use Callcocam\PapaLeguas\Support\Form\Form;
use Callcocam\PapaLeguas\Support\Info\InfoList;
use Callcocam\PapaLeguas\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait InteractsWithRequests
{
    use EvaluatesClosures;
    use HasActionResponses;
    use HasModelInference;
    use HasValidation;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {

        if (method_exists($this, 'table')) {
            $table = $this->table((TableBuilder::make($this->getModelClass()))
                ->context($this)
                ->request($request));

            if (method_exists($this, 'getTableHeaderActions')) {
                $table->headerActions($this->getTableHeaderActions());
            }

            return response()->json($table->render($request));
        }

        return response()->json([
            'success' => true,
            'message' => 'Listagem de recursos',
            'controller' => static::class,
            'method' => 'index',
            'timestamp' => now()->toDateTimeString(),
            'data' => [],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Formulário de criação',
            'controller' => static::class,
            'method' => 'create',
            'timestamp' => now()->toDateTimeString(),
            'data' => [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(
            $this->getValidationRules(),
            $this->getValidationMessages(),
            $this->getValidationAttributes()
        );

        $modelClass = $this->getModelClass();
        $model = $modelClass::create($validated);

        // Execute action callback if exists
        if ($request->has('action_name') && method_exists($this, 'table')) {
            $table = $this->table((TableBuilder::make($this->getModelClass()))
                ->context($this)
                ->request($request));

            $actionName = $request->input('action_name');
            $actions = array_merge(
                $table->getActions(),
                $table->getHeaderActions()
            );

            foreach ($actions as $action) {
                if ($action->getName() === $actionName) {
                    $callback = $action->getCallbackAction();

                    if ($callback) {
                        $this->evaluate($callback, [
                            'record' => $model,
                            'data' => $validated,
                            'request' => $request,
                        ]);
                    }

                    break;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Recurso criado com sucesso',
            'controller' => static::class,
            'method' => 'store',
            'timestamp' => now()->toDateTimeString(),
            'data' => $model,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        if (method_exists($this, 'infolist')) {
            $infolist = $this->infolist(InfoList::make());

            return $infolist->render($request);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalhes do recurso',
            'controller' => static::class,
            'method' => 'show',
            'timestamp' => now()->toDateTimeString(),
            'id' => $id,
            'data' => [],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id): JsonResponse
    {

        if (method_exists($this, 'form')) {
            $form = $this->form(Form::make());

            return $form->render($request);
        }

        return response()->json([
            'success' => true,
            'message' => 'Formulário de edição',
            'controller' => static::class,
            'method' => 'edit',
            'timestamp' => now()->toDateTimeString(),
            'id' => $id,
            'data' => [],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate(
            $this->getValidationUpdateRules(),
            $this->getValidationMessages(),
            $this->getValidationAttributes()
        );

        $modelClass = $this->getModelClass();
        $model = $modelClass::findOrFail($id);
        $model->update($validated);

        // Execute action callback if exists
        if ($request->has('action_name') && method_exists($this, 'table')) {
            $table = $this->table((TableBuilder::make($this->getModelClass()))
                ->context($this)
                ->request($request));

            $actionName = $request->input('action_name');
            $actions = array_merge(
                $table->getActions(),
                $table->getHeaderActions()
            );

            foreach ($actions as $action) {
                if ($action->getName() === $actionName) {
                    $callback = $action->getCallbackAction();

                    if ($callback) {
                        $this->evaluate($callback, [
                            'record' => $model,
                            'data' => $validated,
                            'request' => $request,
                        ]);
                    }

                    break;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Recurso atualizado com sucesso',
            'controller' => static::class,
            'method' => 'update',
            'timestamp' => now()->toDateTimeString(),
            'id' => $id,
            'data' => $model,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::findOrFail($id);

        // Verifica se o modelo usa soft delete
        $useSoftDelete = method_exists($model, 'trashed');

        // Se usa soft delete, faz soft delete
        if ($useSoftDelete) {
            $model->delete();
        } else {
            // Se não usa soft delete, deleta permanentemente
            $model->forceDelete();
        }

        return $this->deletedResponse();
    }

    /**
     * Import resources from file.
     */
    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate(
            $this->getImportValidationRules(),
            $this->getValidationMessages(),
            $this->getValidationAttributes()
        );

        $actions = $this->getImportActions();

        $user = $request->user();

        $options = [
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'imported' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($actions as $action) {
            $name = str($action->getName())->slug()->toString();
            if ($request->hasFile($name)) {
                $file = $request->file($name);
                // Salvar arquivo temporariamente
                $filePath = $file->storeAs(
                    'imports/users',
                    sprintf('users_.%s', $file->getClientOriginalExtension()),
                    'local'
                );
                $options['file_path'] = $filePath;

                // Executar a ação de importação
                $result = $this->evaluate($action->getCallbackAction(), [
                    'options' => $options,
                    'request' => $request,
                ]);

                // Mescla resultado se for array
                if (is_array($result)) {
                    $options = array_merge($options, $result);
                }
            }
        }

        return $this->importedResponse(
            $options['imported'] ?? 0,
            $options['failed'] ?? 0,
            $options['errors'] ?? []
        );
    }

    /**
     * Export resources to file.
     */
    public function export(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
        ]);

        $actions = $this->getExportActions();
        $name = $validated['name'];

        $user = $request->user();

        $options = [
            'tenant_id' => $user?->tenant_id,
            'user_id' => $user?->id,
            'exported' => 0,
            'file_url' => null,
        ];

        foreach ($actions as $action) {
            $actionName = $action->getName();

            if ($actionName === $name) {
                $callback = $action->getCallbackAction();

                if ($callback) {
                    $result = $this->evaluate($callback, [
                        'options' => $options,
                        'request' => $request,
                    ]);

                    if (is_array($result)) {
                        $options = array_merge($options, $result);
                    }
                }

                break;
            }
        }

        return $this->exportedResponse(
            $options['exported'] ?? 0,
            $options['file_url'] ?? null
        );
    }

    /**
     * Restore a soft deleted resource.
     */
    public function restore(Request $request, string $id): JsonResponse
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::withTrashed()->findOrFail($id);

        $model->restore();

        return $this->restoredResponse();
    }

    /**
     * Force delete a resource permanently.
     */
    public function forceDelete(Request $request, string $id): JsonResponse
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::withTrashed()->findOrFail($id);

        $model->forceDelete();

        return $this->deletedResponse();
    }

    /**
     * Delete multiple resources.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'string'],
        ]);

        $modelClass = $this->getModelClass();
        $ids = $validated['ids'];

        $deleted = 0;
        $failed = 0;

        foreach ($ids as $id) {
            try {
                $model = $modelClass::findOrFail($id);
                $model->delete();
                $deleted++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return $this->bulkDeletedResponse($deleted, $failed);
    }

    /**
     * Restore multiple soft deleted resources.
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'string'],
        ]);

        $modelClass = $this->getModelClass();
        $ids = $validated['ids'];

        $restored = 0;
        $failed = 0;

        foreach ($ids as $id) {
            try {
                $model = $modelClass::withTrashed()->findOrFail($id);
                $model->restore();
                $restored++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return $this->bulkRestoredResponse($restored, $failed);
    }

    /**
     * Duplicate a resource.
     */
    public function duplicate(Request $request, string $id): JsonResponse
    {
        $modelClass = $this->getModelClass();
        $original = $modelClass::findOrFail($id);

        // Cria um array com os atributos do modelo original
        $attributes = $original->toArray();

        // Remove campos que não devem ser duplicados
        unset($attributes['id']);
        unset($attributes['created_at']);
        unset($attributes['updated_at']);
        unset($attributes['deleted_at']);

        // Se tiver slug, adiciona sufixo para evitar duplicação
        if (isset($attributes['slug'])) {
            $attributes['slug'] = $attributes['slug'].'-copy-'.time();
        }

        // Se tiver name/title, adiciona (Cópia)
        if (isset($attributes['name'])) {
            $attributes['name'] = $attributes['name'].' (Cópia)';
        } elseif (isset($attributes['title'])) {
            $attributes['title'] = $attributes['title'].' (Cópia)';
        }

        // Cria o novo registro
        $duplicate = $modelClass::create($attributes);

        return $this->duplicatedResponse();
    }
}
