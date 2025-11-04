<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Http\Controllers\Auth\Api;

use App\Http\Controllers\Controller;
use Callcocam\PapaLeguas\Support\Info;
use Callcocam\PapaLeguas\Support\Info\InfoList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MeController extends Controller
{
    /**
     * Get the authenticated user information with tenant data
     */
    public function me(Request $request)
    {
        $guard = config('auth.defaults.guard');
        $user = $request->user($guard);

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Carregar dados do tenant se o usuário tiver um
        $userData = $user->toArray();

        if ($user->tenant_id && $user->tenant) {
            $userData['tenant'] = $user->tenant->toArray();
        }

        // Adicionar informações do guard atual
        $userData['current_guard'] = $guard;

        $userData['data'] = InfoList::make()
            ->columns([
                // Header - Informações principais (fora dos cards)
                Info\Columns\TextColumn::make('name')->label('Nome'),
                Info\Columns\EmailColumn::make('email')->label('E-mail'),

                // Card: Informações Pessoais
                Info\Columns\CardColumn::make('personal_info')
                    ->title('Informações Pessoais')
                    ->icon('User')
                    ->gridColumns('1')
                    ->columns([
                        Info\Columns\PhoneColumn::make('phone')->label('Telefone'),
                        Info\Columns\TextColumn::make('document')->label('CPF/CNPJ'),
                        Info\Columns\TextColumn::make('bio')->label('Biografia'),
                        Info\Columns\TextColumn::make('slug')->label('Slug'),
                    ]),

                // Card: Conta e Status
                Info\Columns\CardColumn::make('account_info')
                    ->title('Conta e Status')
                    ->icon('Settings')
                    ->gridColumns('2')
                    ->columns([
                        Info\Columns\StatusColumn::make('status')->label('Status da Conta'),
                        Info\Columns\TextColumn::make('current_guard')->label('Guard Atual'),
                    ]),

                // Card: Segurança
                Info\Columns\CardColumn::make('security_info')
                    ->title('Segurança')
                    ->description('Informações de segurança e autenticação')
                    ->icon('Shield')
                    ->gridColumns('2')
                    ->collapsible(true, true)
                    ->columns([
                        Info\Columns\BooleanColumn::make('email_verified_at')
                            ->label('E-mail Verificado')
                            ->labels('Verificado', 'Não Verificado'),
                        Info\Columns\BooleanColumn::make('two_factor_confirmed_at')
                            ->label('Autenticação 2FA')
                            ->labels('Ativo', 'Inativo'),
                        Info\Columns\DateColumn::make('last_login_at')->label('Último Acesso'),
                        Info\Columns\TextColumn::make('last_login_ip')->label('IP do Último Acesso'),
                    ]),

                // Card: Datas e Histórico
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
            ->render($userData);

            Storage::disk('local')->put('user_data.json', json_encode($userData, JSON_PRETTY_PRINT));

        return response()->json($userData);
    }
}
