<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    /**
     * Download a complete SQL dump of the database.
     * Restricted to Admin (role_id === 1 or role_name === 'admin').
     */
    public function download(): StreamedResponse
    {
        $user = auth()->user();
        $isAdmin = $user && ((int)$user->role_id === 1 || strtolower(trim($user->role->role_name ?? '')) === 'admin');
        if (!$isAdmin) {
            abort(403, 'Unauthorized. Only Admin can download database backups.');
        }

        // Increase limits for database dump streaming (Unlimited time, safe memory)
        @ini_set('max_execution_time', '0');
        set_time_limit(0);
        @ini_set('memory_limit', '512M');

        // Set timezone to Pakistan (PKT) for accurate local date & time
        date_default_timezone_set('Asia/Karachi');

        $dbName = config('database.connections.mysql.database', 'cms');
        $timestamp = date('Y-m-d_h-i-A'); // e.g. 2026-10-08_10-32-AM
        $filename = "cms_db_backup_{$timestamp}.sql";

        Log::info('Database backup downloaded by user ID ' . $user->id . ' (' . ($user->username ?? $user->name ?? 'Admin') . ')');

        return response()->streamDownload(function () use ($dbName) {
            $pdo = DB::getPdo();

            // SQL header
            echo "-- ========================================================\n";
            echo "-- Navy CMS Database Backup\n";
            echo "-- Database: {$dbName}\n";
            echo "-- Generation Time: " . date('Y-m-d h:i:s A') . " (PKT)\n";
            echo "-- Server OS: " . PHP_OS . "\n";
            echo "-- ========================================================\n\n";

            echo "SET FOREIGN_KEY_CHECKS = 0;\n";
            echo "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
            echo "SET AUTOCOMMIT = 0;\n";
            echo "START TRANSACTION;\n";
            echo "SET time_zone = '+00:00';\n\n";

            // Get all base tables
            $tables = [];
            $tableRows = $pdo->query('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"')->fetchAll(\PDO::FETCH_NUM);
            foreach ($tableRows as $row) {
                $tables[] = $row[0];
            }

            foreach ($tables as $table) {
                echo "-- --------------------------------------------------------\n";
                echo "-- Table structure for table `{$table}`\n";
                echo "-- --------------------------------------------------------\n";
                echo "DROP TABLE IF EXISTS `{$table}`;\n";

                // Table structure
                $createRow = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_NUM);
                echo $createRow[1] . ";\n\n";

                // Table data
                $count = (int) $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
                if ($count > 0) {
                    echo "-- Dumping data for table `{$table}` ({$count} rows)\n";

                    $offset = 0;
                    $batchSize = 500;

                    while ($offset < $count) {
                        $stmt = $pdo->query("SELECT * FROM `{$table}` LIMIT {$batchSize} OFFSET {$offset}");
                        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                        if (!empty($rows)) {
                            $first = true;
                            foreach ($rows as $row) {
                                if ($first) {
                                    $columns = array_map(function ($col) {
                                        return "`" . str_replace("`", "``", $col) . "`";
                                    }, array_keys($row));
                                    echo "INSERT INTO `{$table}` (" . implode(', ', $columns) . ") VALUES\n";
                                    $first = false;
                                } else {
                                    echo ",\n";
                                }

                                $values = array_map(function ($val) use ($pdo) {
                                    if ($val === null) {
                                        return 'NULL';
                                    }
                                    return $pdo->quote($val);
                                }, array_values($row));

                                echo "(" . implode(', ', $values) . ")";
                            }
                            echo ";\n";
                        }

                        $offset += $batchSize;
                    }

                    echo "\n";
                }
            }

            echo "SET FOREIGN_KEY_CHECKS = 1;\n";
            echo "COMMIT;\n";
            echo "-- Dump completed on " . date('Y-m-d h:i:s A') . " (PKT)\n";
        }, $filename, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
