<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Callcocam\PapaLeguas\Commands\BackupAndRepublishMigrationsCommand;
use Callcocam\PapaLeguas\Commands\ClearMenuCacheCommand;
use Callcocam\PapaLeguas\Commands\PapaLeguasCommand;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Callcocam\PapaLeguas\Services\DomainDetectionService;
use Callcocam\PapaLeguas\Support\Landlord\LandlordServiceProvider;
use Callcocam\PapaLeguas\Support\Shinobi\ShinobiServiceProvider;
use Callcocam\PapaLeguas\Traits\RequestMacrosTrait;
use Illuminate\Foundation\Application;
use Illuminate\Routing\ResourceRegistrar;

/**
 * Service Provider principal do pacote Papa Leguas.
 * 
 * Este service provider é responsável por:
 * - Configurar o pacote (views, rotas, assets, comandos)
 * - Registrar serviços no container de dependência
 * - Configurar detecção de contexto tenant/landlord otimizada
 * - Registrar Request macros para facilitar uso em toda aplicação
 * - Configurar guards do Fortify baseado no contexto
 * 
 * Performance: Otimizado para evitar chamadas desnecessárias durante boot
 * e usar cache interno para melhorar performance de detecção de domínio.
 * 
 * @package Callcocam\PapaLeguas
 * @author Claudio Campos <callcocam@gmail.com>
 * @version 1.0.0
 */
class PapaLeguasServiceProvider extends PackageServiceProvider
{
    use RequestMacrosTrait;

    /**
     * Configura o pacote com suas funcionalidades básicas.
     * 
     * Define configurações, views, rotas, comandos e assets do pacote.
     * Este método é chamado automaticamente pelo Spatie Package Tools.
     * 
     * @param Package $package Instância do pacote a ser configurado
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('papa-leguas')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoutes('web', 'api')
            ->hasCommands([
                PapaLeguasCommand::class,
                ClearMenuCacheCommand::class,
                BackupAndRepublishMigrationsCommand::class,
            ])
            ->hasMigrations([
                // Tabelas principais (ordem de dependência)
                'create_tenants_table',
                'create_users_table',
                'create_roles_table',
                'create_permissions_table',

                // Tabelas pivot (relacionamentos muitos-para-muitos)
                'create_role_user_table',
                'create_permission_role_table',
                'create_permission_user_table',

                // Outras tabelas
                'create_addresses_table',

                // Modificações de tabelas
                'add_two_factor_columns_to_users_table',

                // Tabelas de sistema
                'create_personal_access_tokens_table',
                'create_cache_table',
                'create_jobs_table',
            ])
            ->hasInstallCommand(function (InstallCommand $command) {

                $command->startWith(function (InstallCommand $command) {
                    $command->call('papa-leguas:refresh-migrations', [
                        '--force' => true,
                    ]);
                });
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->publishAssets();
                // $command->info('Obrigado por instalar o Papa Leguas! Visite https://www.sigasmart.com.br para mais informações.');
            });
    }

    /**
     * Executado após o boot do pacote.
     * 
     * Configura a publicação de assets compilados para o diretório público.
     * Os assets são copiados do diretório build/ para public/vendor/papa-leguas/.
     * 
     * @return void
     */
    public function packageBooted(): void
    {
        $this->publishAssets();
    }

    public function packageRegistered(): void
    {
        // Usar o registrar estendido que permite configuração flexível
        $this->app->bind(ResourceRegistrar::class, CompositeResourceRegistrar::class);
    }

    /**
     * Executado durante o registro do pacote no container.
     *
     * Registra serviços e configura Request macros.
     * Otimizado para evitar chamadas desnecessárias durante boot da aplicação.
     *
     * NOTA: Não há mais configuração de guards múltiplos (landlord/tenant).
     * O sistema usa um único guard (sanctum) e o contexto é usado apenas
     * para gerenciar rotas e paths.
     *
     * @return void
     */
    public function registeringPackage(): void
    {
        $this->registerCoreServices();
        $this->registerRequestMacros();
        $this->registerMenuServiceProvider();
    }

    /**
     * Registra serviços principais no container.
     * 
     * @return void
     */
    private function registerCoreServices(): void
    {
        // Registra o serviço de detecção de domínio como singleton para performance
        $this->app->singleton(DomainDetectionService::class);

        // Registra helper para verificação de subdomínio (compatibilidade com código legacy)
        $this->app->singleton('isNotSubdomain', function (Application $app): bool {
            return $app->make(DomainDetectionService::class)->isNotSubdomain();
        });
    }


    /**
     * Registra o MenuServiceProvider de forma lazy.
     * 
     * O MenuServiceProvider é registrado apenas quando necessário
     * para evitar impacto na performance durante o boot.
     * 
     * @return void
     */
    private function registerMenuServiceProvider(): void
    {
        if (!$this->app->runningInConsole()) {
            $this->app->register(LandlordServiceProvider::class);
            $this->app->register(ShinobiServiceProvider::class);
            $this->app->register(MenuServiceProvider::class);
        }
    }

    /**
     * Publica os assets compilados.
     * 
     * Assets são copiados do diretório build/ para public/vendor/papa-leguas/
     * com tags para permitir publicação seletiva.
     * 
     * @return void
     */
    private function publishAssets(): void
    {
        // Publicar assets compilados do diretório build para public/vendor/papa-leguas
        $this->publishes([
            __DIR__ . '/../build' => public_path('vendor/papa-leguas'),
        ], 'papa-leguas-assets');

        // Publicar assets também com o tag padrão 'assets'
        $this->publishes([
            __DIR__ . '/../build' => public_path('vendor/papa-leguas'),
        ], 'assets');
    }
}
