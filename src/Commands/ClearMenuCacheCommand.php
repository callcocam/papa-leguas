<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Commands;

use Callcocam\Papaleguas\Enums\Menu\ContextEnum;
use Callcocam\Papaleguas\Services\Menu\Cache\MenuCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearMenuCacheCommand extends Command
{
    public $signature = 'papa-leguas:clear-menu-cache
                        {context? : Context to clear (landlord, tenant, or all)}';

    public $description = 'Clear menu and routes cache for Papa Leguas';

    public function handle(): int
    {
        $context = $this->argument('context');

        if ($context && !in_array(strtolower($context), ['landlord', 'tenant', 'all'])) {
            $this->error('Context must be: landlord, tenant, or all');
            return self::FAILURE;
        }

        $cacheService = app(MenuCacheService::class);

        if (!$context || strtolower($context) === 'all') {
            // Limpa cache de ambos os contextos
            $this->info('Clearing menu cache for all contexts...');

            $cacheService->flushContext(ContextEnum::LANDLORD);
            Cache::forget('menu_builder.landlord.api_controllers');
            $this->line('  ✓ Landlord cache cleared');

            $cacheService->flushContext(ContextEnum::TENANT);
            Cache::forget('menu_builder.tenant.api_controllers');
            $this->line('  ✓ Tenant cache cleared');

            $this->success('All menu caches cleared successfully!');
        } else {
            // Limpa cache de contexto específico
            $contextEnum = strtolower($context) === 'landlord'
                ? ContextEnum::LANDLORD
                : ContextEnum::TENANT;

            $this->info("Clearing menu cache for {$context}...");

            $cacheService->flushContext($contextEnum);
            Cache::forget("menu_builder.{$contextEnum->value}.api_controllers");

            $this->success("Menu cache for {$context} cleared successfully!");
        }

        $this->newLine();
        $this->info('Next request will regenerate menu and routes from controllers.');

        return self::SUCCESS;
    }
}
