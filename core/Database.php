<?php
/**
 * ============================================================
 * Nadics LectureHub — Database Connection Manager
 * ============================================================
 *
 * Manages PDO database connections using the Singleton pattern.
 * Provides connection pooling awareness, error handling, and
 * query statistics for performance monitoring.
 *
 * Configured for enterprise scale (500K+ concurrent students):
 * - Persistent connections
 * - Prepared statements enforced
 * - UTF-8 MB4 character set
 * - Strict SQL mode
 *
 * @package    NadicsLectureHub
 * @subpackage Core
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace Core;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    /**
     * Singleton PDO connection instance.
     *
     * @var PDO|null
     */
    private static ?PDO $connection = null;

    /**
     * Query execution counter for performance monitoring.
     *
     * @var int
     */
    private static int $queryCount = 0;

    /**
     * Query log for debugging (only in debug mode).
     *
     * @var array
     */
    private static array $queryLog = [];

    /**
     * Whether query logging is enabled.
     *
     * @var bool
     */
    private static bool $logging = false;

    /**
     * Prevent direct instantiation.
     */
    private function __construct() {}

    /**
     * Get the PDO database connection.
     *
     * Creates a new connection on first call, then returns
     * the cached connection on subsequent calls.
     *
     * @return PDO Active database connection
     * @throws PDOException If connection fails
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            self::connect();
        }
        return self::$connection;
    }

    /**
     * Establish a new database connection.
     *
     * Reads configuration from environment variables and creates
     * a PDO connection with enterprise-grade settings.
     *
     * @return void
     * @throws PDOException If connection cannot be established
     */
    private static function connect(): void
    {
        $host      = env('DB_HOST', '127.0.0.1');
        $port      = env('DB_PORT', '3306');
        $database  = env('DB_DATABASE', 'nadics_lecturehub');
        $username  = env('DB_USERNAME', 'root');
        $password  = env('DB_PASSWORD', '');
        $charset   = env('DB_CHARSET', 'utf8mb4');
        $collation = env('DB_COLLATION', 'utf8mb4_unicode_ci');

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

        $options = [
            // Throw exceptions on errors (never fail silently)
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

            // Return associative arrays by default
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            // Use real prepared statements (not emulated)
            PDO::ATTR_EMULATE_PREPARES   => false,

            // Persistent connections for connection pooling
            PDO::ATTR_PERSISTENT         => true,

            // Stringify fetches disabled — preserve data types
            PDO::ATTR_STRINGIFY_FETCHES  => false,

            // Set connection timeout
            PDO::ATTR_TIMEOUT            => 5,
        ];

        try {
            self::$connection = new PDO($dsn, $username, $password, $options);

            // Set collation and SQL mode
            self::$connection->exec("SET NAMES '{$charset}' COLLATE '{$collation}'");
            self::$connection->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

            // Enable query logging in debug mode
            self::$logging = (bool) env('APP_DEBUG', false);

            Logger::getInstance()->info('Database connection established', [
                'host'     => $host,
                'database' => $database,
            ]);

        } catch (PDOException $e) {
            Logger::getInstance()->emergency('Database connection failed', [
                'host'    => $host,
                'database'=> $database,
                'error'   => $e->getMessage(),
            ]);

            throw new PDOException(
                'Database connection failed. Please check your configuration.',
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * Execute a prepared statement with parameter binding.
     *
     * This is the primary method for executing queries safely.
     * All user input MUST be passed through the $params array
     * to prevent SQL injection.
     *
     * @param  string $sql    SQL query with placeholders
     * @param  array  $params Parameters to bind
     * @return PDOStatement    The executed statement
     * @throws PDOException    On query failure
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $startTime = microtime(true);

        try {
            $pdo  = self::getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            self::$queryCount++;

            // Log query in debug mode
            if (self::$logging) {
                $duration = round((microtime(true) - $startTime) * 1000, 2);
                self::$queryLog[] = [
                    'sql'      => $sql,
                    'params'   => $params,
                    'duration' => $duration . 'ms',
                    'time'     => date('H:i:s'),
                ];
            }

            return $stmt;

        } catch (PDOException $e) {
            Logger::getInstance()->error('Query execution failed', [
                'sql'    => $sql,
                'params' => $params,
                'error'  => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Execute a raw SQL statement (non-prepared).
     *
     * WARNING: Only use for DDL statements or trusted SQL.
     * NEVER use with user input.
     *
     * @param  string $sql The SQL statement
     * @return int|false    Number of affected rows
     */
    public static function exec(string $sql): int|false
    {
        return self::getConnection()->exec($sql);
    }

    /**
     * Get the last inserted auto-increment ID.
     *
     * @return string The last insert ID
     */
    public static function lastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }

    /**
     * Begin a database transaction.
     *
     * @return bool
     */
    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Commit the current transaction.
     *
     * @return bool
     */
    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    /**
     * Roll back the current transaction.
     *
     * @return bool
     */
    public static function rollBack(): bool
    {
        return self::getConnection()->rollBack();
    }

    /**
     * Execute a callback within a database transaction.
     *
     * Automatically commits on success, rolls back on exception.
     *
     * @param  callable $callback Function to execute within transaction
     * @return mixed              Return value of the callback
     * @throws \Exception         Re-throws exceptions after rollback
     */
    public static function transaction(callable $callback): mixed
    {
        self::beginTransaction();

        try {
            $result = $callback(self::getConnection());
            self::commit();
            return $result;

        } catch (\Exception $e) {
            self::rollBack();
            Logger::getInstance()->error('Transaction rolled back', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get the total number of queries executed.
     *
     * @return int
     */
    public static function getQueryCount(): int
    {
        return self::$queryCount;
    }

    /**
     * Get the query log (debug mode only).
     *
     * @return array
     */
    public static function getQueryLog(): array
    {
        return self::$queryLog;
    }

    /**
     * Check if the database connection is active.
     *
     * @return bool
     */
    public static function isConnected(): bool
    {
        if (self::$connection === null) {
            return false;
        }

        try {
            self::$connection->query('SELECT 1');
            return true;
        } catch (PDOException) {
            return false;
        }
    }

    /**
     * Close the database connection.
     *
     * @return void
     */
    public static function disconnect(): void
    {
        self::$connection = null;
    }

    /**
     * Prevent cloning of singleton.
     */
    private function __clone() {}
}
