<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas;

use Callcocam\PapaLeguas\Services\DomainDetectionService;
use Callcocam\PapaLeguas\Services\Menu\Cache\MenuCacheService;
use Callcocam\PapaLeguas\Services\Menu\Contracts\MenuBuilderInterface;
use Callcocam\PapaLeguas\Services\Menu\Contracts\RouteGeneratorInterface;
use Callcocam\PapaLeguas\Services\Menu\ControllerDiscoveryService;
use Callcocam\PapaLeguas\Services\Menu\MenuBuilderService;
use Callcocam\PapaLeguas\Services\Menu\RouteCollectionService;
use Callcocam\PapaLeguas\Services\Menu\VueRouteGeneratorService;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider para o sistema de menus do Papa Leguas.
 * 
 * Este service provider é responsável por:
 * - Registrar serviços de cache de menu
 * - Configurar descoberta de controllers baseada em contexto
 * - Registrar builders de menu e geradores de rotas Vue
 * - Otimizar performance usando detecção de contexto cachada
 * 
 * Performance: Otimizado para usar o DomainDetectionService e evitar
 * múltiplas chamadas de request() durante o registro de serviços.
 * 
 * @package Callcocam\PapaLeguas
 * @author Claudio Campos <callcocam@gmail.com>
 * @version 1.0.0
 */
class MenuServiceProvider extends ServiceProvider
{
    /**
     * Registra serviços do sistema de menu.
     * 
     * Configura cache, descoberta de controllers, builders e geradores
     * de rotas, todos otimizados para usar detecção de contexto cachada.
     * 
     * @return void
     */
    public function register(): void
    {
        $this->registerMenuCacheService();
        $this->registerControllerDiscoveryService();
        $this->registerRouteCollectionService();
        $this->registerMenuBuilderServices();
        $this->registerRouteGeneratorServices();
    }

    /**
     * Registra o serviço de cache de menu como singleton.
     * 
     * @return void
     */
    private function registerMenuCacheService(): void
    {
        $this->app->singleton(MenuCacheService::class, function () {
            $cacheService = new MenuCacheService();

            if ($ttl = config('menu-builder.cache.ttl')) {
                $cacheService->setTtl($ttl);
            }

            if ($prefix = config('menu-builder.cache.prefix')) {
                $cacheService->setPrefix($prefix);
            }

            return $cacheService;
        });
    }

    /**
     * Registra o serviço de descoberta de controllers.
     *
     * IMPORTANTE: O contexto é resolvido dinamicamente a cada requisição,
     * não no momento do registro do service provider. Isso garante que
     * o contexto correto (landlord/tenant) seja detectado baseado no
     * domínio da requisição HTTP atual.
     *
     * NOTA: Este serviço ainda é usado por api.php para registrar rotas dinamicamente.
     *
     * @return void
     */
    private function registerControllerDiscoveryService(): void
    {
        $this->app->bind(ControllerDiscoveryService::class, function ($app) {
            // IMPORTANTE: getMenuContext agora será chamado durante a requisição,
            // não durante o boot do service provider. Isso permite que o
            // request() esteja disponível e o contexto seja detectado corretamente.
            $context = $this->getMenuContext($app);

            $service = new ControllerDiscoveryService();
            $service->setContext($context);

            if ($methods = config('menu-builder.methods.standard')) {
                $service->setStandardMethods($methods);
            }

            return $service;
        });
    }

    /**
     * Registra o serviço de coleta de rotas registradas.
     *
     * Este serviço lê rotas de Route::getRoutes() e as transforma em DTOs
     * para serem usados por VueRouteGeneratorService e MenuBuilderService.
     *
     * @return void
     */
    private function registerRouteCollectionService(): void
    {
        $this->app->bind(RouteCollectionService::class, function ($app) {
            $context = $this->getMenuContext($app);

            return (new RouteCollectionService(
                $app->make(MenuCacheService::class)
            ))->setContext($context);
        });
    }

    /**
     * Registra os serviços de construção de menu.
     *
     * @return void
     */
    private function registerMenuBuilderServices(): void
    {
        $this->app->bind(MenuBuilderInterface::class, MenuBuilderService::class);

        $this->app->bind(MenuBuilderService::class, function ($app) {
            $context = $this->getMenuContext($app);
            return (new MenuBuilderService(
                $app->make(RouteCollectionService::class),
                $app->make(MenuCacheService::class)
            ))->setContext($context);
        });
    }

    /**
     * Registra os serviços de geração de rotas.
     *
     * @return void
     */
    private function registerRouteGeneratorServices(): void
    {
        $this->app->bind(RouteGeneratorInterface::class, VueRouteGeneratorService::class);

        $this->app->bind(VueRouteGeneratorService::class, function ($app) {
            $context = $this->getMenuContext($app);
            $service = new VueRouteGeneratorService(
                $app->make(RouteCollectionService::class),
                $app->make(MenuCacheService::class)
            );

            if ($methods = config('menu-builder.methods.excluded_from_routes')) {
                $service->setExcludedMethods($methods);
            }

            $service->setContext($context);
            return $service;
        });
    }

    /**
     * Obtém o contexto do menu usando o DomainDetectionService.
     * 
     * @param \Illuminate\Foundation\Application $app Container da aplicação
     * @return mixed Contexto do menu (LANDLORD ou TENANT)
     */
    private function getMenuContext($app)
    {
        $domainService = $app->make(DomainDetectionService::class);
        
        return $domainService->isLandlord() 
            ? \Callcocam\PapaLeguas\Enums\Menu\ContextEnum::LANDLORD 
            : \Callcocam\PapaLeguas\Enums\Menu\ContextEnum::TENANT;
    }

    /**
     * Executa o bootstrap dos serviços.
     * 
     * Publica arquivos de configuração do sistema de menu.
     * 
     * @return void
     */
    public function boot(): void
    {
        $this->publishMenuConfig();
    }

    /**
     * Publica o arquivo de configuração do menu builder.
     * 
     * @return void
     */
    private function publishMenuConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../config/menu-builder.php' => config_path('menu-builder.php'),
        ], 'menu-builder-config');
    }
}
