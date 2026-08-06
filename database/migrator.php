<?php
/**
 * ============================================================
 * Nadics LectureHub — Migration & Seeder CLI Tool
 * ============================================================
 *
 * Usage:
 *   php database/migrator.php migrate     — Apply pending migrations
 *   php database/migrator.php rollback    — Rollback last batch
 *   php database/migrator.php refresh     — Rollback all & re-migrate
 *   php database/migrator.php seed        — Run database seeders
 *   php database/migrator.php status      — Show migration status
 *
 * @package    NadicsLectureHub
 * @subpackage Database
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Application.php';

// Bootstrap app (loads autoloader & environment)
$app = new Core\Application(BASE_PATH);
$app->boot();

use Core\Database;

class Migrator
{
    private PDO $pdo;
    private string $migrationsPath;

    public function __construct()
    {
        $this->ensureDatabaseExists();
        $this->pdo = Database::getConnection();
        $this->migrationsPath = BASE_PATH . '/database/migrations';
        $this->ensureMigrationsTable();
    }

    /**
     * Ensure database exists, create if missing.
     */
    private function ensureDatabaseExists(): void
    {
        $host     = env('DB_HOST', '127.0.0.1');
        $port     = env('DB_PORT', '3306');
        $database = env('DB_DATABASE', 'nadics_lecturehub');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');
        $charset  = env('DB_CHARSET', 'utf8mb4');

        try {
            $tempPdo = new PDO("mysql:host={$host};port={$port};charset={$charset}", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            echo "\n [ERROR] Could not create/connect to database [{$database}]: " . $e->getMessage() . "\n\n";
            exit(1);
        }
    }

    /**
     * Ensure `migrations` tracking table exists.
     */
    private function ensureMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL,
            `batch` INT UNSIGNED NOT NULL,
            `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $this->pdo->exec($sql);
    }

    /**
     * Run all pending migrations.
     */
    public function migrate(): void
    {
        echo "\n🚀 Running Pending Migrations...\n";
        echo "==================================================\n";

        $executed = $this->getExecutedMigrations();
        $files    = glob($this->migrationsPath . '/*.php');

        sort($files);

        $pending = [];
        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (!in_array($name, $executed)) {
                $pending[] = ['path' => $file, 'name' => $name];
            }
        }

        if (empty($pending)) {
            echo " [INFO] Nothing to migrate. Database is up to date.\n\n";
            return;
        }

        $nextBatch = $this->getNextBatchNumber();

        foreach ($pending as $m) {
            echo " ⏳ Migrating: {$m['name']}...";
            require_once $m['path'];

            $className = 'Database\\Migrations\\' . $this->studly($m['name']);
            if (!class_exists($className)) {
                // Try fallback without namespace
                $className = $this->studly($m['name']);
            }

            if (class_exists($className)) {
                $instance = new $className();
                $instance->up();

                $stmt = $this->pdo->prepare("INSERT INTO `migrations` (`migration`, `batch`) VALUES (?, ?)");
                $stmt->execute([$m['name'], $nextBatch]);

                echo " [DONE]\n";
            } else {
                echo " [FAILED — Class not found: {$className}]\n";
            }
        }

        echo "==================================================\n";
        echo " ✅ Migration Complete!\n\n";
    }

    /**
     * Rollback the last migration batch.
     */
    public function rollback(): void
    {
        echo "\n⏪ Rolling Back Last Migration Batch...\n";
        echo "==================================================\n";

        $lastBatch = $this->getLastBatchNumber();
        if ($lastBatch === 0) {
            echo " [INFO] Nothing to rollback.\n\n";
            return;
        }

        $stmt = $this->pdo->prepare("SELECT `migration` FROM `migrations` WHERE `batch` = ? ORDER BY `id` DESC");
        $stmt->execute([$lastBatch]);
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($migrations as $name) {
            $file = $this->migrationsPath . '/' . $name . '.php';
            if (file_exists($file)) {
                echo " ⏳ Rolling back: {$name}...";
                require_once $file;

                $className = 'Database\\Migrations\\' . $this->studly($name);
                if (!class_exists($className)) {
                    $className = $this->studly($name);
                }

                if (class_exists($className)) {
                    $instance = new $className();
                    $instance->down();

                    $delStmt = $this->pdo->prepare("DELETE FROM `migrations` WHERE `migration` = ?");
                    $delStmt->execute([$name]);

                    echo " [DONE]\n";
                }
            }
        }

        echo "==================================================\n";
        echo " ✅ Rollback Complete!\n\n";
    }

    /**
     * Refresh database (rollback all and migrate).
     */
    public function refresh(): void
    {
        echo "\n🔄 Refreshing Database...\n";
        while ($this->getLastBatchNumber() > 0) {
            $this->rollback();
        }
        $this->migrate();
    }

    /**
     * Show migration status.
     */
    public function status(): void
    {
        echo "\n📊 Migration Status:\n";
        echo "==================================================\n";

        $executed = $this->getExecutedMigrations();
        $files    = glob($this->migrationsPath . '/*.php');
        sort($files);

        foreach ($files as $file) {
            $name = basename($file, '.php');
            $status = in_array($name, $executed) ? " [RAN]" : " [PENDING]";
            echo "{$status}  {$name}\n";
        }
        echo "==================================================\n\n";
    }

    /**
     * Run seeders.
     */
    public function seed(): void
    {
        echo "\n🌱 Running Database Seeders...\n";
        echo "==================================================\n";

        require_once BASE_PATH . '/database/seeders/DatabaseSeeder.php';
        $seeder = new \Database\Seeders\DatabaseSeeder();
        $seeder->run();

        echo "==================================================\n";
        echo " ✅ Seeding Complete!\n\n";
    }

    // Helpers
    private function getExecutedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT `migration` FROM `migrations` ORDER BY `id` ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getNextBatchNumber(): int
    {
        return $this->getLastBatchNumber() + 1;
    }

    private function getLastBatchNumber(): int
    {
        $stmt = $this->pdo->query("SELECT MAX(`batch`) FROM `migrations`");
        return (int) $stmt->fetchColumn() ?: 0;
    }

    private function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }
}

// CLI Command Dispatcher
$command = $argv[1] ?? 'migrate';
$migrator = new Migrator();

switch ($command) {
    case 'migrate':
        $migrator->migrate();
        break;
    case 'rollback':
        $migrator->rollback();
        break;
    case 'refresh':
        $migrator->refresh();
        break;
    case 'status':
        $migrator->status();
        break;
    case 'seed':
        $migrator->seed();
        break;
    case 'fresh':
        $migrator->refresh();
        $migrator->seed();
        break;
    default:
        echo "Unknown command [{$command}]. Available commands: migrate, rollback, refresh, status, seed, fresh\n";
        break;
}
