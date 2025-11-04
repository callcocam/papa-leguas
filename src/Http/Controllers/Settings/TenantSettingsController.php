<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Callcocam\PapaLeguas\Models\Tenant;
use Callcocam\PapaLeguas\Services\DomainDetectionService;
use Callcocam\PapaLeguas\Support\Info;
use Callcocam\PapaLeguas\Support\Info\InfoList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantSettingsController extends Controller
{
    public function __construct(
        private readonly DomainDetectionService $domainDetectionService
    ) {}

    public function show(Request $request): JsonResponse
    {
        $hostname = $this->extractHostnameFromRequest($request);

        $tenant = Tenant::where('id', config('app.tenant_id'))
            ->first();

        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found',
                'hostname' => $hostname,
            ], 404);
        }

        $tenantData = $tenant->toArray();

        $tenantData['data'] = InfoList::make()
            ->columns([
                Info\Columns\TextColumn::make('name')->label('Nome'),
                Info\Columns\EmailColumn::make('email')->label('E-mail'),

                Info\Columns\CardColumn::make('basic_info')
                    ->title('Informações Básicas')
                    ->icon('Building2')
                    ->gridColumns('1')
                    ->columns([
                        Info\Columns\TextColumn::make('domain')->label('Domínio'),
                        Info\Columns\TextColumn::make('slug')->label('Slug'),
                        Info\Columns\PhoneColumn::make('phone')->label('Telefone'),
                        Info\Columns\TextColumn::make('document')->label('Documento'),
                        Info\Columns\TextColumn::make('description')->label('Descrição'),
                    ]),

                Info\Columns\CardColumn::make('technical_info')
                    ->title('Configurações Técnicas')
                    ->icon('Settings')
                    ->gridColumns('2')
                    ->columns([
                        Info\Columns\TextColumn::make('database')->label('Database'),
                        Info\Columns\TextColumn::make('prefix')->label('Prefix'),
                        Info\Columns\StatusColumn::make('status')->label('Status'),
                        Info\Columns\BooleanColumn::make('is_primary')
                            ->label('Tenant Principal')
                            ->labels('Sim', 'Não'),
                    ]),

                Info\Columns\CardColumn::make('dates_info')
                    ->title('Datas e Histórico')
                    ->icon('Calendar')
                    ->gridColumns('3')
                    ->collapsible(true, false)
                    ->columns([
                        Info\Columns\DateColumn::make('created_at')->label('Criado em'),
                        Info\Columns\DateColumn::make('updated_at')->label('Atualizado em'),
                        Info\Columns\DateColumn::make('deleted_at')->label('Deletado em'),
                    ]),
            ])
            ->render($tenantData);

        return response()->json($tenantData);
    }

    private function extractHostnameFromRequest(Request $request): string
    {
        return str($request->getHost())
            ->replace(['http://', 'https://', 'www.'], '')
            ->explode('.')
            ->first();
    }
}
