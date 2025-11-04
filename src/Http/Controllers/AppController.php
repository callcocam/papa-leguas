<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Http\Controllers;

use App\Http\Controllers\Controller;
use Callcocam\Papaleguas\Services\Menu\MenuBuilderService;
use Callcocam\Papaleguas\Services\Menu\VueRouteGeneratorService;
use Callcocam\PapaLeguas\Support\Info\InfoList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppController extends Controller
{
    public function __invoke(Request $request)
    {
        $builderService = app(MenuBuilderService::class);
        $menus = $builderService
            ->withCache(true)
            ->build()
            ->render();

        $builderRouteService = app(VueRouteGeneratorService::class);
        $routes = $builderRouteService
            ->withCache(true)
            ->generate();
        // Storage::disk('local')->put('routes.json', json_encode($routes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return view("papa-leguas::app", compact('menus', 'routes'));
    }
}
