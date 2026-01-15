<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\{Setting, ActivityLog};

class BackupDatabase extends Command
{
    protected $signature = 'backup:run';
    protected $description = 'Create automated database backup';

    public function handle()
    {
        if (!Setting::get('backup_enabled', true)) {
            $this->info('Backup disabled');
            return 0;
        }

        try {
            $backupPath = storage_path('app/backups');
            if (!File::exists($backupPath)) File::makeDirectory($backupPath, 0755, true);

            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filePath = $backupPath . '/' . $filename;

            if (config('database.default') === 'sqlite') {
                File::copy(database_path('database.sqlite'), $filePath);
            } else {
                // Get database credentials
                $dbHost = config('database.connections.mysql.host');
                $dbPort = config('database.connections.mysql.port', 3306);
                $dbName = config('database.connections.mysql.database');
                $dbUser = config('database.connections.mysql.username');
                $dbPass = config('database.connections.mysql.password');

                // Use mysqldump with proper Windows compatibility
                putenv('MYSQL_PWD=' . $dbPass);
                $command = sprintf(
                    'mysqldump -h "%s" -P "%s" -u "%s" "%s" > "%s" 2>&1',
                    $dbHost,
                    $dbPort,
                    $dbUser,
                    $dbName,
                    $filePath
                );

                exec($command, $output, $returnVar);

                if ($returnVar !== 0 || !File::exists($filePath) || File::size($filePath) === 0) {
                    // Fallback: use PHP to generate SQL dump
                    $this->createSqlDump($filePath);
                }
            }

            // Cleanup old backups
            $retentionDays = Setting::get('backup_retention_days', 30);
            foreach (File::files($backupPath) as $file) {
                if (now()->diffInDays(date('Y-m-d', $file->getMTime())) > $retentionDays) {
                    File::delete($file->getPathname());
                }
            }

            ActivityLog::log('backup_created', "Automated backup: {$filename}", 1);
            $this->info("Backup created: {$filename}");
            return 0;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return 1;
        }
    }

    protected function createSqlDump(string $filePath): void
    {
        $db = \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbName = config('database.connections.mysql.database');

        $sql = "-- Database Backup: " . $dbName . "\n";
        $sql .= "-- Generated: " . \Carbon\Carbon::now()->format('Y-m-d H:i:s') . "\n\n";

        // Get all tables
        $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $tableField = 'Tables_in_' . $dbName;

        foreach ($tables as $table) {
            $tableName = $table->$tableField;
            $sql .= "-- Table: $tableName\n\n";

            // Get CREATE TABLE
            $createTable = \Illuminate\Support\Facades\DB::select("SHOW CREATE TABLE `$tableName`");
            $sql .= $createTable[0]->{'Create Table'} . ";\n\n";

            // Get table data
            $rows = \Illuminate\Support\Facades\DB::select("SELECT * FROM `$tableName`");
            foreach ($rows as $row) {
                $values = array_map(function($value) {
                    if ($value === null) return 'NULL';
                    if (is_numeric($value)) return $value;
                    return "'" . addslashes($value) . "'";
                }, (array)$row);
                $sql .= "INSERT INTO `$tableName` VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }

        \Illuminate\Support\Facades\File::put($filePath, $sql);
    }
}
