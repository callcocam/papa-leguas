<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Papaleguas\Services\Menu;

use Callcocam\Papaleguas\Enums\Menu\ContextEnum;
use Callcocam\Papaleguas\Services\Menu\DTOs\ControllerMetadataDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ReflectionClass;

/**
 * Serviço de Descoberta de Controllers
 * 
 * Responsável por descobrir e extrair metadata de controllers no sistema,
 * tanto da aplicação principal quanto de controllers do pacote.
 * 
 * Este serviço varre os diretórios de controllers, identifica classes válidas,
 * e extrai informações de metadata para construção automática de menus e rotas.
 * 
 * @package Callcocam\Papaleguas\Services\Menu
 */
class ControllerDiscoveryService
{
    /**
     * Métodos CRUD padrão que serão identificados nos controllers
     * 
     * @var array<string>
     */
    protected array $standardMethods = [
        'index',
        'show',
        'create',
        'store',
        'edit',
        'update',
        'destroy'
    ];

    /**
     * @param ContextEnum $context Contexto de execução (LANDLORD ou TENANT)
     */
    public function __construct(
        protected ContextEnum $context = ContextEnum::LANDLORD
    ) {}

    /**
     * Obtém o contexto atual
     */
    public function getContext(): ContextEnum
    {
        return $this->context;
    }

    /**
     * Descobre todos os controllers no contexto atual
     * 
     * Este método varre:
     * 1. Controllers do pacote (em Http/Controllers do pacote)
     * 2. Controllers da aplicação (no caminho definido pelo contexto)
     * 
     * @return Collection<int, ControllerMetadataDTO> Coleção de metadata dos controllers descobertos
     */
    public function discover(): Collection
    {
        $path = $this->context->getPath();


        $controllers =  $this->createMetadataPackageCollection(collect());
        if (!File::exists($path) || !is_dir($path)) {
            Log::warning("Controller path does not exist: {$path}");
            return $controllers;
        }

        $files = File::allFiles($path);

        foreach ($files as $file) {
            $controllers = $this->loadMetadataFromFile($controllers, $file);
        }

        return $controllers;
    }

    /**
     * Cria coleção de metadata dos controllers do pacote
     */
    protected function createMetadataPackageCollection(Collection $controllers): Collection
    {
        $packageControllerPath = sprintf(
            '%s/../../Http/Controllers/%s',
            __DIR__,
            $this->context->label()
        );
        if (!File::exists($packageControllerPath) || !is_dir($packageControllerPath)) {
            return $controllers;
        }

        $packageFiles = File::allFiles($packageControllerPath);

        foreach ($packageFiles as $file) {
            $controllers = $this->loadMetadataFromPackageFile($controllers, $file);
        }

        return $controllers;
    }

    /**
     * Carrega metadata de arquivo do pacote
     */
    protected function loadMetadataFromPackageFile(Collection $controllers, $file): Collection
    {
        if ($file->getExtension() !== 'php') {
            return $controllers;
        }

        $className = $this->getClassNameFromPackageFile($file);

        if (!$className || !$this->isValidController($className)) {
            return $controllers;
        }

        try {
            $metadata = $this->extractMetadata($className);
            if ($metadata && $metadata->showInNavigation) {
                $controllers->push($metadata);
            }
        } catch (\Exception $e) {
            Log::error("Error extracting metadata from package controller: {$className}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $controllers;
    }

    /**
     * Extrai nome completo da classe do arquivo do pacote
     * 
     * Lê o conteúdo do arquivo e extrai o namespace e nome da classe
     * através de regex, evitando problemas com caminhos relativos.
     * 
     * @param \SplFileInfo $file Arquivo do controller do pacote
     * @return string|null Nome completo da classe ou null se não encontrado
     */
    protected function getClassNameFromPackageFile($file): ?string
    {
        $content = File::get($file->getPathname());

        // Extrai o namespace do arquivo
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $namespace = $matches[1];

            // Extrai o nome da classe
            if (preg_match('/class\s+(\w+)/', $content, $classMatches)) {
                return $namespace . '\\' . $classMatches[1];
            }
        }

        return null;
    }

    /**
     * Carrega metadata de um arquivo de controller da aplicação
     * 
     * @param Collection $controllers Coleção atual de controllers
     * @param \SplFileInfo $file Arquivo a ser processado
     * @return Collection Coleção atualizada com novo controller (se válido)
     */
    protected function loadMetadataFromFile(Collection $controllers, $file): Collection
    {
        if ($file->getExtension() !== 'php') {
            return $controllers;
        }

        $className = $this->getClassNameFromFile($file);

        if (!$className || !$this->isValidController($className)) {
            return $controllers;
        }

        try {
            $metadata = $this->extractMetadata($className);
            if ($metadata && $metadata->showInNavigation) {
                $controllers->push($metadata);
            }
        } catch (\Exception $e) {
            Log::error("Error extracting metadata from controller: {$className}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        return $controllers;
    }

    /**
     * Extrai metadata de um controller
     * 
     * Instancia o controller e extrai suas informações de metadata usando
     * os métodos da trait HasMenuMetadata (se disponível).
     * 
     * @param string $className Nome completo da classe do controller
     * @return ControllerMetadataDTO|null DTO com metadata ou null se falhar
     */
    protected function extractMetadata(string $className): ?ControllerMetadataDTO
    {
        try {
            $instance = app()->make($className);
            $availableMethods = $this->getAvailableMethods($className);

            return ControllerMetadataDTO::fromController(
                className: $className,
                instance: $instance,
                availableMethods: $availableMethods
            );
        } catch (\Exception $e) {
            Log::error("Cannot instantiate controller: {$className}", [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Obtém métodos CRUD disponíveis no controller
     * 
     * Verifica quais dos métodos padrão (index, show, create, etc.)
     * estão implementados como métodos públicos no controller.
     * 
     * @param string $className Nome completo da classe do controller
     * @return array<string> Lista de nomes de métodos disponíveis
     */
    protected function getAvailableMethods(string $className): array
    {
        $reflection = new ReflectionClass($className);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        $availableMethods = [];

        foreach ($methods as $method) {
            if (in_array($method->name, $this->standardMethods)) {
                $availableMethods[] = $method->name;
            }
        }

        return $availableMethods;
    }

    /**
     * Extrai nome completo da classe a partir do arquivo da aplicação
     * 
     * Constrói o nome da classe (FQCN) usando o caminho relativo do arquivo
     * em relação ao diretório base do contexto. Suporta controllers em subdiretórios.
     * 
     * @param \SplFileInfo $file Arquivo do controller
     * @return string|null Nome completo da classe ou null se inválido
     * 
     * @example
     * Para arquivo: /app/Http/Controllers/Api/Landlord/Users/UserController.php
     * Retorna: App\Http\Controllers\Api\Landlord\Users\UserController
     */
    protected function getClassNameFromFile($file): ?string
    {
        // Obtém o caminho base do contexto
        $basePath = $this->context->getPath();

        // Obtém o caminho relativo completo do arquivo em relação ao diretório base
        $relativePath = Str::after($file->getPathname(), $basePath . DIRECTORY_SEPARATOR);

        // Remove a extensão .php e converte separadores de diretório em namespace
        $className = str_replace(
            ['/', '.php', '\\\\'],
            ['\\', '', '\\'],
            $relativePath
        );

        return sprintf("%s\\%s", $this->context->getNamespace(), $className);
    }

    /**
     * Verifica se é um controller válido
     * 
     * Para ser válido, o controller deve:
     * - Existir como classe
     * - Ser instanciável (não abstrata/interface)
     * - Ter nome terminando em "Controller"
     * 
     * @param string $className Nome completo da classe
     * @return bool True se é um controller válido
     */
    protected function isValidController(string $className): bool
    {
        try {
            if (!class_exists($className)) {
                return false;
            }

            $reflection = new ReflectionClass($className);

            return $reflection->isInstantiable()
                && Str::endsWith($reflection->getShortName(), 'Controller');
        } catch (\Exception $e) {
            Log::warning("Invalid controller class: {$className}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Define o contexto de descoberta
     * 
     * @param ContextEnum $context Novo contexto (LANDLORD ou TENANT)
     * @return self Para encadeamento de métodos
     */
    public function setContext(ContextEnum $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Define métodos padrão a serem buscados nos controllers
     * 
     * Permite customizar quais métodos devem ser identificados como
     * métodos CRUD ao escanear os controllers.
     * 
     * @param array<string> $methods Lista de nomes de métodos
     * @return self Para encadeamento de métodos
     */
    public function setStandardMethods(array $methods): self
    {
        $this->standardMethods = $methods;
        return $this;
    }
}
