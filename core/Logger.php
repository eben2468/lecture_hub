<?php
/**
 * ============================================================
 * Nadics LectureHub — PSR-3 Style Logger
 * ============================================================
 *
 * File-based logging system with daily rotation, severity levels,
 * and contextual data support. Follows PSR-3 severity levels.
 *
 * Log files are stored in storage/logs/ with daily rotation:
 *   slms-2026-07-21.log
 *
 * @package    NadicsLectureHub
 * @subpackage Core
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace Core;

class Logger
{
    /**
     * PSR-3 Log levels.
     */
    public const EMERGENCY = 'emergency';
    public const ALERT     = 'alert';
    public const CRITICAL  = 'critical';
    public const ERROR     = 'error';
    public const WARNING   = 'warning';
    public const NOTICE    = 'notice';
    public const INFO      = 'info';
    public const DEBUG     = 'debug';

    /**
     * Level priority map (higher = more severe).
     *
     * @var array<string, int>
     */
    private const LEVEL_PRIORITY = [
        self::DEBUG     => 0,
        self::INFO      => 1,
        self::NOTICE    => 2,
        self::WARNING   => 3,
        self::ERROR     => 4,
        self::CRITICAL  => 5,
        self::ALERT     => 6,
        self::EMERGENCY => 7,
    ];

    /**
     * Path to the log directory.
     *
     * @var string
     */
    private string $logPath;

    /**
     * Minimum log level to record.
     *
     * @var string
     */
    private string $minLevel;

    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Create a new Logger instance.
     *
     * @param string $logPath  Directory for log files
     * @param string $minLevel Minimum severity to log
     */
    public function __construct(string $logPath = '', string $minLevel = self::DEBUG)
    {
        $this->logPath  = $logPath ?: dirname(__DIR__) . '/storage/logs';
        $this->minLevel = $minLevel;

        // Ensure log directory exists
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    /**
     * Get the singleton Logger instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self(
                '',
                env('LOG_LEVEL', self::DEBUG)
            );
        }
        return self::$instance;
    }

    /**
     * Log an emergency message.
     *
     * @param  string $message Log message
     * @param  array  $context Additional context
     * @return void
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Log an alert message.
     *
     * @param  string $message Log message
     * @param  array  $context Additional context
     * @return void
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * Log a critical message.
     *
     * @param  string $message Log message
     * @param  array  $context Additional context
     * @return void
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Log an error message.
     *
     * @param  string $message Log message
     * @param  array  $context Additional context
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * Log a warning message.
     *
     * @param  string $message Log message
     * @param  array  $context Additional context
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Log a notice message.
     *
     * @param  string $message Log message
     * @param  array  $context Additional context
     * @return void
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Log an info message.
     *
     * @param  string $message Log message
     * @param  array  $context Additional context
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Log a debug message.
     *
     * @param  string $message Log message
     * @param  array  $context Additional context
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Write a log entry.
     *
     * @param  string $level   Severity level
     * @param  string $message Log message
     * @param  array  $context Additional context data
     * @return void
     */
    public function log(string $level, string $message, array $context = []): void
    {
        // Check minimum log level
        if (!$this->shouldLog($level)) {
            return;
        }

        // Build log entry
        $timestamp = date('Y-m-d H:i:s');
        $levelUpper = strtoupper($level);
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';

        $entry = "[{$timestamp}] [{$levelUpper}] [{$ip}] {$message}";

        // Append context if present
        if (!empty($context)) {
            $entry .= ' | Context: ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $entry .= PHP_EOL;

        // Write to daily log file
        $filename = $this->logPath . '/slms-' . date('Y-m-d') . '.log';
        file_put_contents($filename, $entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Check if the given level should be logged based on minimum level.
     *
     * @param  string $level The level to check
     * @return bool
     */
    private function shouldLog(string $level): bool
    {
        $currentPriority = self::LEVEL_PRIORITY[$level] ?? 0;
        $minPriority     = self::LEVEL_PRIORITY[$this->minLevel] ?? 0;

        return $currentPriority >= $minPriority;
    }

    /**
     * Clean up old log files beyond the retention period.
     *
     * @param  int  $days Number of days to retain logs
     * @return int        Number of files deleted
     */
    public function cleanup(int $days = 30): int
    {
        $deleted = 0;
        $cutoff  = time() - ($days * 86400);

        $files = glob($this->logPath . '/slms-*.log');
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Prevent cloning of singleton.
     */
    private function __clone() {}
}
