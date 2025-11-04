<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Concerns;

use Illuminate\Support\Str;

trait HasModelInference
{
    /**
     * Get the model class from controller name
     */
    public function getModelClass(): string
    {
        if (isset($this->model) && $this->model) {
            return $this->model;
        }

        // Get controller class name without namespace
        $controllerName = class_basename(static::class);

        // Remove 'Controller' suffix to get model name
        $modelName = Str::replaceLast('Controller', '', $controllerName);

        // Try different namespaces for the model
        $possibleNamespaces = [
            'App\\Models\\',
            'Callcocam\\PapaLeguas\\Models\\',
            config('papa_leguas.models_namespace', 'App\\Models\\'),
        ];

        foreach ($possibleNamespaces as $namespace) {
            $modelClass = $namespace.$modelName;
            if (class_exists($modelClass)) {
                return $this->model = $modelClass;
            }
        }

        throw new \Exception('Model class not found for controller: '.static::class);
    }

    /**
     * Get model instance
     */
    protected function getModel()
    {
        $modelClass = $this->getModelClass();

        return new $modelClass;
    }

    /**
     * Get the resource name from controller
     */
    public function getResourceName(): string
    {
        $controllerName = class_basename(static::class);
        $modelName = Str::replaceLast('Controller', '', $controllerName);

        return Str::snake(Str::plural($modelName));
    }

    /**
     * Get the singular resource name
     */
    protected function getSingularResourceName(): string
    {
        $controllerName = class_basename(static::class);
        $modelName = Str::replaceLast('Controller', '', $controllerName);

        return Str::snake($modelName);
    }
}
