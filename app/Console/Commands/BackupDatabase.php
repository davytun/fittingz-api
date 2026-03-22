<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as ConsoleCommand;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'Backup the database';

    public function handle()
    {
        $filename = 'backup-' . date('Y-m-d-H-i-s') . '.sql';
        $path = storage_path('app/backups/' . $filename);

        // Create backups directory if it doesn't exist
        if (!file_exists(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $command = sprintf(
            'mysqldump -u%s -p%s %s > %s',
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database'),
            $path
        );

        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            $this->info("Database backup created: {$filename}");
            
            // Delete backups older than 30 days
            $this->cleanOldBackups();
            
            return ConsoleCommand::SUCCESS;
        }

        $this->error('Database backup failed');
        return ConsoleCommand::FAILURE;
    }

    private function cleanOldBackups()
    {
        $backupPath = storage_path('app/backups');
        $files = glob($backupPath . '/backup-*.sql');

        foreach ($files as $file) {
            if (filemtime($file) < strtotime('-30 days')) {
                unlink($file);
                $this->info('Deleted old backup: ' . basename($file));
            }
        }
    }
}