<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Concerns;

use Callcocam\PapaLeguas\Support\Form\Column;
use Callcocam\PapaLeguas\Support\Form\Columns\CheckboxField;
use Callcocam\PapaLeguas\Support\Form\Columns\DateField;
use Callcocam\PapaLeguas\Support\Form\Columns\EmailField;
use Callcocam\PapaLeguas\Support\Form\Columns\NumberField;
use Callcocam\PapaLeguas\Support\Form\Columns\PasswordField;
use Callcocam\PapaLeguas\Support\Form\Columns\SelectField;
use Callcocam\PapaLeguas\Support\Form\Columns\TextareaField;
use Callcocam\PapaLeguas\Support\Form\Columns\TextField;
use Callcocam\PapaLeguas\Support\Form\Columns\UploadField;

trait HasValidation
{
    /**
     * Cache de regras extraídas
     */
    protected array $extractedRules = [];

    /**
     * Regras personalizadas
     */
    protected array $customRules = [];

    /**
     * Mensagens de erro personalizadas
     */
    protected array $customMessages = [];

    /**
     * Extrai regras de validação dos campos do formulário
     */
    protected function extractValidationRules(array $columns, string $context = 'create'): array
    {
        $rules = [];

        foreach ($columns as $column) {
            if (! $column instanceof Column) {
                continue;
            }

            $fieldRules = $this->getFieldValidationRules($column, $context);

            if (! empty($fieldRules)) {
                $rules[$column->getName()] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * Obtém as regras de validação para um campo específico
     */
    protected function getFieldValidationRules(Column $column, string $context = 'create'): array
    {
        $rules = [];
        $columnArray = $column->toArray();

        // Required
        if ($columnArray['required'] ?? false) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Regras específicas por tipo de campo
        if ($column instanceof UploadField) {
            $rules = array_merge($rules, $this->getUploadFieldRules($columnArray));
        } elseif ($column instanceof EmailField) {
            $rules[] = 'email';
        } elseif ($column instanceof NumberField) {
            $rules[] = 'numeric';
            $rules = array_merge($rules, $this->getNumberFieldRules($columnArray));
        } elseif ($column instanceof SelectField) {
            $rules = array_merge($rules, $this->getSelectFieldRules($columnArray));
        } elseif ($column instanceof DateField) {
            $rules[] = 'date';
            $rules = array_merge($rules, $this->getDateFieldRules($columnArray));
        } elseif ($column instanceof PasswordField) {
            $rules = array_merge($rules, $this->getPasswordFieldRules($columnArray));
        } elseif ($column instanceof TextareaField || $column instanceof TextField) {
            $rules = array_merge($rules, $this->getTextFieldRules($columnArray));
        } elseif ($column instanceof CheckboxField) {
            $rules[] = 'boolean';
        }

        return array_filter($rules);
    }

    /**
     * Regras para campos de upload
     */
    protected function getUploadFieldRules(array $columnArray): array
    {
        $rules = ['file'];

        // Tipos de arquivo aceitos
        if (! empty($columnArray['acceptedFileTypes'])) {
            $mimes = array_map(function ($type) {
                return ltrim($type, '.');
            }, $columnArray['acceptedFileTypes']);

            $rules[] = 'mimes:' . implode(',', $mimes);
        }

        // Tamanho máximo (converter MB para KB)
        if (isset($columnArray['maxSize'])) {
            $rules[] = 'max:' . $columnArray['maxSize'] * 1024;
        }

        return $rules;
    }

    /**
     * Regras para campos numéricos
     */
    protected function getNumberFieldRules(array $columnArray): array
    {
        $rules = [];

        if (isset($columnArray['min'])) {
            $rules[] = 'min:' . $columnArray['min'];
        }

        if (isset($columnArray['max'])) {
            $rules[] = 'max:' . $columnArray['max'];
        }

        return $rules;
    }

    /**
     * Regras para campos de seleção
     */
    protected function getSelectFieldRules(array $columnArray): array
    {
        $rules = [];

        // Se tem opções definidas, valida que o valor está nas opções
        if (! empty($columnArray['options'])) {
            $validValues = array_map(function ($option) {
                return is_array($option) ? ($option['value'] ?? $option['id'] ?? null) : $option;
            }, $columnArray['options']);

            $validValues = array_filter($validValues, fn($value) => $value !== null);

            if (! empty($validValues)) {
                $rules[] = 'in:' . implode(',', $validValues);
            }
        }

        // Se é multiple, deve ser um array
        if ($columnArray['multiple'] ?? false) {
            $rules[] = 'array';
        }

        return $rules;
    }

    /**
     * Regras para campos de data
     */
    protected function getDateFieldRules(array $columnArray): array
    {
        $rules = [];

        if (isset($columnArray['minDate'])) {
            $rules[] = 'after_or_equal:' . $columnArray['minDate'];
        }

        if (isset($columnArray['maxDate'])) {
            $rules[] = 'before_or_equal:' . $columnArray['maxDate'];
        }

        return $rules;
    }

    /**
     * Regras para campos de senha
     */
    protected function getPasswordFieldRules(array $columnArray): array
    {
        $rules = [];

        if (isset($columnArray['minLength'])) {
            $rules[] = 'min:' . $columnArray['minLength'];
        }

        return $rules;
    }

    /**
     * Regras para campos de texto
     */
    protected function getTextFieldRules(array $columnArray): array
    {
        $rules = [];

        if (isset($columnArray['maxLength'])) {
            $rules[] = 'max:' . $columnArray['maxLength'];
        }

        if (isset($columnArray['minLength'])) {
            $rules[] = 'min:' . $columnArray['minLength'];
        }

        return $rules;
    }

    /**
     * Mescla regras extraídas com regras personalizadas
     */
    protected function mergeRules(array $extractedRules, array $customRules): array
    {
        $merged = $extractedRules;

        foreach ($customRules as $field => $rules) {
            if (isset($merged[$field])) {
                // Mescla regras se o campo já existe
                $existingRules = is_array($merged[$field]) ? $merged[$field] : [$merged[$field]];
                $newRules = is_array($rules) ? $rules : [$rules];

                $merged[$field] = array_unique(array_merge($existingRules, $newRules));
            } else {
                // Adiciona nova regra
                $merged[$field] = $rules;
            }
        }

        return $merged;
    }

    /**
     * Obtém regras de validação para criação
     */
    protected function getValidationRules(): array
    {
        return $this->getValidationRulesForContext('create');
    }

    /**
     * Obtém regras de validação para atualização
     */
    protected function getValidationUpdateRules(): array
    {
        return $this->getValidationRulesForContext('update');
    }

    /**
     * Obtém regras de validação para importação
     */
    protected function getImportValidationRules($actions = []): array
    {
        // Regras padrão para importação
        $defaultRules = [];

        // Se tem método importRules() no controller, usa ele
        if (method_exists($this, 'importRules')) {
            $customRules = $this->importRules();

            return $this->mergeRules($defaultRules, $customRules);
        }

        // Se tem action com form (ImportAction), extrai regras dos campos
        if ($actions) {
            foreach ($actions as $action) {
                if (method_exists($action, 'columns')) {
                    $columns = $action->getColumns();
                    foreach ($columns as $column) {
                        if (! $column instanceof Column) {
                            continue;
                        }
                        $extractedRules = $this->getFieldValidationRules($column, 'import');
                    }
                    $defaultRules = $this->mergeRules($defaultRules, $extractedRules);
                }
            }
        }

        return $defaultRules;
    }

    /**
     * Obtém regras de validação para um contexto específico
     */
    protected function getValidationRulesForContext(string $context): array
    {
        // Se tem método form() no controller, extrai regras dos campos
        if (method_exists($this, 'form')) {
            $form = $this->form(\Callcocam\PapaLeguas\Support\Form\Form::make());
            $columns = $form->getColumns();

            $extractedRules = $this->extractValidationRules($columns, $context);
        } else {
            $extractedRules = [];
        }

        // Obtém regras personalizadas do método correspondente
        $customRulesMethod = $context === 'create' ? 'rules' : 'updateRules';

        if (method_exists($this, $customRulesMethod)) {
            $customRules = $this->{$customRulesMethod}();
        } else {
            $customRules = [];
        }

        // Mescla regras extraídas com personalizadas
        return $this->mergeRules($extractedRules, $customRules);
    }

    /**
     * Obtém mensagens de validação personalizadas
     */
    protected function getValidationMessages(): array
    {
        if (method_exists($this, 'messages')) {
            return $this->messages();
        }

        return [];
    }

    /**
     * Obtém atributos personalizados para mensagens de erro
     */
    protected function getValidationAttributes(): array
    {
        if (method_exists($this, 'attributes')) {
            return $this->attributes();
        }

        // Extrai labels dos campos automaticamente
        if (method_exists($this, 'form')) {
            $form = $this->form(\Callcocam\PapaLeguas\Support\Form\Form::make());
            $columns = $form->getColumns();

            $attributes = [];
            foreach ($columns as $column) {
                if ($column instanceof Column) {
                    $attributes[$column->getName()] = $column->getLabel();
                }
            }

            return $attributes;
        }

        return [];
    }
}
