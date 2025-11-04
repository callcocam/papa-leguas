<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupAndRepublishMigrationsCommand extends Command
{
    public $signature = 'papa-leguas:refresh-migrations
                        {--force : Não pedir confirmação}
                        {--no-backup : Não fazer backup das migrations antigas}';

    public $description = 'Faz backup das migrations publicadas e republica as novas migrations do Papa Leguas';

    /**
     * Lista de migrations do Papa Leguas
     */
    protected array $papaLeguasMigrations = [
        'create_tenants_table',
        'create_users_table',
        'create_roles_table',
        'create_permissions_table',
        'create_role_user_table',
        'create_permission_role_table',
        'create_permission_user_table',
        'create_addresses_table',
        'add_two_factor_columns_to_users_table',
        'create_personal_access_tokens_table',
        'create_cache_table',
        'create_jobs_table',
    ];

    public function handle(): int
    {
        $migrationsPath = database_path('migrations');

        // Verifica se o diretório de migrations existe
        if (!File::isDirectory($migrationsPath)) {
            $this->error("Diretório de migrations não encontrado: {$migrationsPath}");
            return self::FAILURE;
        }

        // Encontra migrations do Papa Leguas
        $foundMigrations = $this->findPapaLeguasMigrations($migrationsPath);

        if (empty($foundMigrations)) {
            $this->info('Nenhuma migration do Papa Leguas encontrada em database/migrations.');
            $this->info('Publicando novas migrations...');
            return $this->publishMigrations();
        }

        // Exibe migrations encontradas
        $this->info('Migrations do Papa Leguas encontradas:');
        foreach ($foundMigrations as $migration) {
            $this->line("  - {$migration}");
        }
        $this->newLine();

        // Pede confirmação se não for --force
        if (!$this->option('force')) {
            if (!$this->confirm('Deseja continuar com backup e republicação?', true)) {
                $this->info('Operação cancelada.');
                return self::SUCCESS;
            }
        }

        // Faz backup se não for --no-backup
        if (!$this->option('no-backup')) {
            $backupPath = $this->backupMigrations($migrationsPath, $foundMigrations);
            $this->info("✓ Backup criado em: {$backupPath}");
        } else {
            $this->warn('⚠ Pulando backup (--no-backup)');
        }

        // Deleta migrations antigas
        $this->deleteMigrations($migrationsPath, $foundMigrations);
        $this->info('✓ Migrations antigas removidas');

        // Republica migrations
        $this->publishMigrations();

        $this->newLine();
        $this->info('✓ Processo concluído com sucesso!');
        $this->newLine();
        $this->info('Próximos passos:');
        $this->line('  1. Revise as novas migrations em database/migrations');
        $this->line('  2. Execute: php artisan migrate');

        return self::SUCCESS;
    }

    /**
     * Encontra migrations do Papa Leguas no diretório
     */
    protected function findPapaLeguasMigrations(string $path): array
    {
        $found = [];
        $files = File::files($path);

        foreach ($files as $file) {
            $filename = $file->getFilename();

            // Verifica se é migration do Papa Leguas (por nome)
            foreach ($this->papaLeguasMigrations as $migrationName) {
                if (str_contains($filename, $migrationName)) {
                    $found[] = $filename;
                    break;
                }
            }

            // Ou verifica pelo conteúdo (bloco de autoria)
            if (empty($found) || !in_array($filename, $found)) {
                $content = File::get($file->getPathname());
                if (str_contains($content, 'Created by Claudio Campos') ||
                    str_contains($content, 'callcocam@gmail.com')) {
                    if (!in_array($filename, $found)) {
                        $found[] = $filename;
                    }
                }
            }
        }

        sort($found);
        return $found;
    }

    /**
     * Cria backup das migrations
     */
    protected function backupMigrations(string $migrationsPath, array $migrations): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $backupPath = database_path("migrations-backup/{$timestamp}");

        // Cria diretório de backup
        File::ensureDirectoryExists($backupPath);

        // Copia migrations para backup
        foreach ($migrations as $migration) {
            $source = "{$migrationsPath}/{$migration}";
            $destination = "{$backupPath}/{$migration}";

            File::copy($source, $destination);
        }

        // Cria arquivo README no backup
        $readmeContent = "# Backup de Migrations - Papa Leguas\n\n";
        $readmeContent .= "Data: " . now()->format('d/m/Y H:i:s') . "\n";
        $readmeContent .= "Total de arquivos: " . count($migrations) . "\n\n";
        $readmeContent .= "## Arquivos:\n";
        foreach ($migrations as $migration) {
            $readmeContent .= "- {$migration}\n";
        }
        $readmeContent .= "\nEste backup foi criado automaticamente pelo comando `php artisan papa-leguas:refresh-migrations`\n";

        File::put("{$backupPath}/README.md", $readmeContent);

        return $backupPath;
    }

    /**
     * Deleta migrations antigas
     */
    protected function deleteMigrations(string $migrationsPath, array $migrations): void
    {
        foreach ($migrations as $migration) {
            $filePath = "{$migrationsPath}/{$migration}";
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }
    }

    /**
     * Publica novas migrations
     */
    protected function publishMigrations(): int
    {
        $this->info('Publicando novas migrations...');

        $result = $this->call('vendor:publish', [
            '--tag' => 'papa-leguas-migrations',
            '--force' => true,
        ]);

        if ($result === 0) {
            $this->info('✓ Novas migrations publicadas com sucesso');
        }

        return $result;
    }
}
