<?php

namespace App\Http\Controllers;

use App\Models\{Setting, ActivityLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{File, DB};

class BackupController extends Controller
{
    protected $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    public function index()
    {
        if (auth()->user()->role !== 'manager') abort(403);

        $backups = $this->getBackupFiles();
        $backupEnabled = Setting::get('backup_enabled', true);
        $backupTime = Setting::get('backup_time', '02:00');
        $retentionDays = Setting::get('backup_retention_days', 30);
        $latestBackup = count($backups) > 0 ? $backups[0] : null;

        return view('settings.backup', compact('backups', 'backupEnabled', 'backupTime', 'retentionDays', 'latestBackup'));
    }

    public function create()
    {
        if (auth()->user()->role !== 'manager') abort(403);

        try {
            $filename = $this->createBackup();
            ActivityLog::log('backup_created', "Manual backup: {$filename}");
            return redirect()->route('settings.backup')->with('success', "تم إنشاء النسخة الاحتياطية: {$filename}");
        } catch (\Exception $e) {
            return redirect()->route('settings.backup')->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function download(Request $request)
    {
        if (auth()->user()->role !== 'manager') abort(403);

        $filePath = $this->backupPath . '/' . $request->filename;
        if (!File::exists($filePath)) {
            return redirect()->route('settings.backup')->with('error', 'الملف غير موجود');
        }

        ActivityLog::log('backup_downloaded', "Downloaded: {$request->filename}");
        return response()->download($filePath);
    }

    public function destroy(Request $request)
    {
        if (auth()->user()->role !== 'manager') abort(403);

        $filePath = $this->backupPath . '/' . $request->filename;
        if (File::exists($filePath)) {
            File::delete($filePath);
            ActivityLog::log('backup_deleted', "Deleted: {$request->filename}");
        }

        return redirect()->route('settings.backup')->with('success', 'تم حذف النسخة الاحتياطية');
    }

    public function restore(Request $request)
    {
        if (auth()->user()->role !== 'manager') abort(403);

        $request->validate([
            'filename' => 'required|string',
            'confirm' => 'required|accepted',
        ]);

        $filePath = $this->backupPath . '/' . $request->filename;
        if (!File::exists($filePath)) {
            return redirect()->route('settings.backup')->with('error', 'الملف غير موجود');
        }

        try {
            $backupBeforeRestore = $this->createBackup();
            $this->restoreBackup($filePath);
            ActivityLog::log('backup_restored', "Restored: {$request->filename}. Safety backup: {$backupBeforeRestore}");
            return redirect()->route('login')->with('success', 'تم استعادة النسخة الاحتياطية');
        } catch (\Exception $e) {
            return redirect()->route('settings.backup')->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function uploadAndRestore(Request $request)
    {
        if (auth()->user()->role !== 'manager') abort(403);

        $request->validate([
            'backup_file' => 'required|file|mimes:sql,txt|max:102400', // Max 100MB
        ]);

        $file = $request->file('backup_file');

        try {
            // Save uploaded file temporarily
            $tempPath = $this->backupPath . '/temp_' . time() . '.sql';
            $file->move($this->backupPath, 'temp_' . time() . '.sql');

            // Create safety backup
            $backupBeforeRestore = $this->createBackup();

            // Restore from uploaded file
            $this->restoreBackup($tempPath);

            // Clean up temp file
            File::delete($tempPath);

            ActivityLog::log('backup_uploaded_restored', "Restored from uploaded file. Safety backup: {$backupBeforeRestore}");
            return redirect()->route('login')->with('success', 'تم استعادة النسخة الاحتياطية من الملف المرفوع');
        } catch (\Exception $e) {
            // Clean up temp file on error
            if (File::exists($tempPath ?? '')) {
                File::delete($tempPath);
            }
            return redirect()->route('settings.backup')->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    protected function createBackup(): string
    {
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filePath = $this->backupPath . '/' . $filename;

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
            $command = sprintf(
                'mysqldump -h "%s" -P "%s" -u "%s" -p"%s" "%s" > "%s" 2>&1',
                $dbHost,
                $dbPort,
                $dbUser,
                $dbPass,
                $dbName,
                $filePath
            );

            // Try alternative method using putenv
            putenv('MYSQL_PWD=' . $dbPass);
            $commandAlt = sprintf(
                'mysqldump -h "%s" -P "%s" -u "%s" "%s" > "%s" 2>&1',
                $dbHost,
                $dbPort,
                $dbUser,
                $dbName,
                $filePath
            );

            exec($commandAlt, $output, $returnVar);

            if ($returnVar !== 0 || !File::exists($filePath) || File::size($filePath) === 0) {
                // Fallback: use PHP to generate SQL dump
                $this->createSqlDump($filePath);
            }
        }

        $this->cleanupOldBackups();
        return $filename;
    }

    protected function createSqlDump(string $filePath): void
    {
        $db = DB::connection()->getPdo();
        $dbName = config('database.connections.mysql.database');

        $sql = "-- Database Backup: " . $dbName . "\n";
        $sql .= "-- Generated: " . now()->format('Y-m-d H:i:s') . "\n\n";

        // Get all tables
        $tables = DB::select('SHOW TABLES');
        $tableField = 'Tables_in_' . $dbName;

        foreach ($tables as $table) {
            $tableName = $table->$tableField;
            $sql .= "-- Table: $tableName\n\n";

            // Get CREATE TABLE
            $createTable = DB::select("SHOW CREATE TABLE `$tableName`");
            $sql .= $createTable[0]->{'Create Table'} . ";\n\n";

            // Get table data
            $rows = DB::select("SELECT * FROM `$tableName`");
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

        File::put($filePath, $sql);
    }

    protected function restoreBackup(string $filePath): void
    {
        if (config('database.default') === 'sqlite') {
            File::copy($filePath, database_path('database.sqlite'));
        } else {
            // Try mysql command first
            $dbHost = config('database.connections.mysql.host');
            $dbPort = config('database.connections.mysql.port', 3306);
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbName = config('database.connections.mysql.database');

            // Try with putenv (more reliable)
            putenv('MYSQL_PWD=' . $dbPass);
            $command = sprintf(
                'mysql -h "%s" -P "%s" -u "%s" "%s" < "%s" 2>&1',
                $dbHost,
                $dbPort,
                $dbUser,
                $dbName,
                $filePath
            );

            exec($command, $output, $returnVar);

            // If mysql command fails, use PHP-based restore
            if ($returnVar !== 0) {
                $this->restoreFromSqlDump($filePath);
            }
        }
    }

    protected function restoreFromSqlDump(string $filePath): void
    {
        $sql = File::get($filePath);

        // Get all table names from the SQL file
        preg_match_all('/CREATE TABLE `([^`]+)`/', $sql, $matches);
        $tables = $matches[1] ?? [];

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        try {
            // Drop existing tables
            foreach ($tables as $table) {
                try {
                    DB::statement("DROP TABLE IF EXISTS `$table`");
                } catch (\Exception $e) {
                    // Table might not exist, continue
                }
            }

            // Split SQL into individual statements and execute
            $statements = $this->splitSqlStatements($sql);

            foreach ($statements as $statement) {
                $statement = trim($statement);

                // Skip empty statements and comments
                if (empty($statement) || strpos($statement, '--') === 0) {
                    continue;
                }

                // Skip DROP TABLE statements as we already handled them
                if (preg_match('/^DROP TABLE/i', $statement)) {
                    continue;
                }

                // Execute CREATE TABLE and other statements
                try {
                    DB::statement($statement);
                } catch (\Exception $e) {
                    throw new \Exception("Failed to execute statement: " . substr($statement, 0, 100) . "... Error: " . $e->getMessage());
                }
            }
        } finally {
            // Always re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    protected function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $currentStatement = '';

        // Split by semicolon, handling multi-line statements
        $lines = explode("\n", $sql);

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            // Skip comment lines
            if (strpos($trimmedLine, '--') === 0) {
                continue;
            }

            $currentStatement .= $line . "\n";

            // If line ends with semicolon, it's a complete statement
            if (substr($trimmedLine, -1) === ';') {
                $statement = trim($currentStatement);
                if (!empty($statement)) {
                    $statements[] = $statement;
                }
                $currentStatement = '';
            }
        }

        // Add any remaining statement
        if (!empty(trim($currentStatement))) {
            $statements[] = trim($currentStatement);
        }

        return $statements;
    }

    protected function getBackupFiles(): array
    {
        $files = File::files($this->backupPath);
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename' => $file->getFilename(),
                'size' => $file->getSize(),
                'size_formatted' => $this->formatBytes($file->getSize()),
                'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
            ];
        }

        usort($backups, fn($a, $b) => $b['created_at'] <=> $a['created_at']);
        return $backups;
    }

    protected function cleanupOldBackups(): void
    {
        $retentionDays = Setting::get('backup_retention_days', 30);
        $files = File::files($this->backupPath);

        foreach ($files as $file) {
            if (now()->diffInDays(date('Y-m-d', $file->getMTime())) > $retentionDays) {
                File::delete($file->getPathname());
            }
        }
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' bytes';
    }
}
