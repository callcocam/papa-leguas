<?php

/**
 * Created by Claudio Campos.
 * User: callcocam, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Http\Controllers\Landlord;

use Callcocam\PapaLeguas\Support\Concerns\InteractsWithRequests;
use Callcocam\PapaLeguas\Support\Table\TableBuilder;

class UserController extends LandlordController
{
    use InteractsWithRequests;

    protected ?string $navigationIcon = 'Users';

    protected ?string $navigationGroup = 'Operacional';

    protected function getImportActions(): array
    {
        return [
            \Callcocam\PapaLeguas\Support\Actions\ImportAction::make('users.import')
                ->label('Importar Usuários')
                ->action(function ($options) {
                    // Lógica para processar o arquivo importado de usuários
                }),
        ];
    }

    protected function getExportActions(): array
    {
        return [
            \Callcocam\PapaLeguas\Support\Actions\ExportAction::make('users.export')
                ->action(function ($options) {
                    // Lógica básica de exportação para teste
                    $users = \Callcocam\PapaLeguas\Models\User::query()->limit(100)->get();
                    $data = $users->toJson();
                    $fileName = 'users_export_'.now()->format('Y_m_d_H_i_s').'.json';
                    \Illuminate\Support\Facades\Storage::disk('local')->put($fileName, $data);

                    return [
                        'exported' => $users->count(),
                        'file_url' => \Illuminate\Support\Facades\Storage::url($fileName),
                    ];
                }),
        ];
    }

    protected function table(TableBuilder $table): TableBuilder
    {
        $table->model(\Callcocam\PapaLeguas\Models\User::class);

        $table->columns([
            \Callcocam\PapaLeguas\Support\Table\Columns\TextColumn::make('name', 'Name'),
            \Callcocam\PapaLeguas\Support\Table\Columns\TextColumn::make('email', 'Email'),
            \Callcocam\PapaLeguas\Support\Table\Columns\DateTimeColumn::make('created_at', 'Created At')
                ->dateTime('d/m/Y H:i'),
        ]);

        $table->filters([
            \Callcocam\PapaLeguas\Support\Table\Filters\TextFilter::make('name', 'Nome'),
            \Callcocam\PapaLeguas\Support\Table\Filters\TextFilter::make('email', 'Email'),
            \Callcocam\PapaLeguas\Support\Table\Filters\DateFilter::make('created_at', 'Data de Criação'),
            \Callcocam\PapaLeguas\Support\Table\Filters\TrashedFilter::make('trashed', 'Lixeira'),
        ]);

        $table->headerAction(\Callcocam\PapaLeguas\Support\Actions\CreateAction::make('users.create')
            ->label('Novo Usuário'));

        // Actions de linha (editar, visualizar, excluir) - todas já vêm pré-configuradas!
        $table->actions([
            \Callcocam\PapaLeguas\Support\Actions\ModalAction::make('users.modal')->label('Detalhes')
            ->slideover()
            ->columns([
                \Callcocam\PapaLeguas\Support\Form\Columns\TextField::make('name', 'Name'),
                \Callcocam\PapaLeguas\Support\Form\Columns\TextField::make('email', 'Email'),
            ])
            ->modalTitle('Detalhes do Usuário')->modalContent(function ($record) {
                return "Nome: {$record->name}\nEmail: {$record->email}\nCriado em: {$record->created_at->format('d/m/Y H:i')}";
            }),
            \Callcocam\PapaLeguas\Support\Actions\ViewAction::make('users.show'),
            \Callcocam\PapaLeguas\Support\Actions\EditAction::make('users.edit'),
            \Callcocam\PapaLeguas\Support\Actions\DeleteAction::make('users.destroy')
                ->confirm([
                    'title' => 'Excluir usuário',
                    'message' => 'Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.',
                ]), // Personalizando apenas a mensagem
        ]);

        // Actions em massa (excluir vários, exportar)
        $table->bulkActions([
            \Callcocam\PapaLeguas\Support\Actions\DeleteAction::make('users.bulk-destroy')
                ->label('Excluir selecionados')
                ->confirm([
                    'title' => 'Excluir múltiplos usuários',
                    'message' => 'Tem certeza que deseja excluir os usuários selecionados?',
                ]),
            \Callcocam\PapaLeguas\Support\Actions\ExportAction::make('users.bulk-export')
                ->label('Exportar selecionados')
                ->action(function ($records) {
                    $data = $records->toJson();
                    $fileName = 'users_export_'.now()->format('Y_m_d_H_i_s').'.json';
                    \Illuminate\Support\Facades\Storage::disk('local')->put($fileName, $data);
                }),
        ]);

        return $table;
    }
}
