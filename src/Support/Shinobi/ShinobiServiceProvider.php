<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Shinobi;

use Exception;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class ShinobiServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return null
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/../../../config/shinobi.php', 'shinobi');

        $this->registerGates();
        $this->registerBladeDirectives();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton('shinobi', function ($app) {
            $auth = $app->make('Illuminate\Contracts\Auth\Guard');

            return new \Callcocam\PapaLeguas\Support\Shinobi\Shinobi($auth);
        });
    }

    /**
     * Register the permission gates.
     *
     * @return void
     */
    protected function registerGates()
    {
        Gate::before(function (Authorizable $user, string $permission): ?bool {
            try {
                if (method_exists($user, 'hasPermissionTo')) {
                    return $user->hasPermissionTo($permission) ?: null;
                }
            } catch (Exception $e) {
                Log::warning('Shinobi permission check failed', [
                    'user_id' => $user->id ?? 'guest',
                    'permission' => $permission,
                    'error' => $e->getMessage(),
                ]);
            }

            return null;
        });
    }

    /**
     * Register the blade directives.
     *
     * @return void
     */
    protected function registerBladeDirectives()
    {
        Blade::if('role', function ($role) {
            return auth()->user()?->hasRole($role) ?? false;
        });

        Blade::if('anyrole', function (...$roles) {
            return auth()->user()?->hasAnyRole(...$roles) ?? false;
        });

        Blade::if('allroles', function (...$roles) {
            return auth()->user()?->hasAllRoles(...$roles) ?? false;
        });
    }
}
