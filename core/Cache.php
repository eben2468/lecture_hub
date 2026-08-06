<?php
/**
 * ============================================================
 * Nadics LectureHub — File-Based Cache System
 * ============================================================
 *
 * Provides a file-based caching layer with TTL (time-to-live)
 * support. Stores serialized data in the storage/cache directory.
 *
 * @package    NadicsLectureHub
 * @subpackage Core
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace Core;

class Cache
{
    /**
     * Path to the cache directory.
     *
     * @var string
     */
    private string $cachePath;

    /**
     * Default TTL in seconds.
     *
     * @var int
     */
    private int $defaultTtl;

    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Create a new Cache instance.
     *
     * @param string $cachePath  Cache directory path
     * @param int    $defaultTtl Default time-to-live in seconds
     */
    public function __construct(string $cachePath = '', int $defaultTtl = 3600)
    {
        $this->cachePath  = $cachePath ?: dirname(__DIR__) . '/storage/cache';
        $this->defaultTtl = $defaultTtl;

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Get the singleton Cache instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self(
                '',
                (int) env('CACHE_TTL', 3600)
            );
        }
        return self::$instance;
    }

    /**
     * Retrieve an item from the cache.
     *
     * @param  string $key     Cache key
     * @param  mixed  $default Default value if not found or expired
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $filePath = $this->getFilePath($key);

        if (!file_exists($filePath)) {
            return $default;
        }

        $data = unserialize(file_get_contents($filePath));

        // Check expiration
        if ($data['expires_at'] !== 0 && $data['expires_at'] < time()) {
            $this->forget($key);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Store an item in the cache.
     *
     * @param  string $key   Cache key
     * @param  mixed  $value Value to store
     * @param  int    $ttl   Time-to-live in seconds (0 = never expires)
     * @return bool
     */
    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        $ttl = $ttl ?: $this->defaultTtl;

        $data = [
            'value'      => $value,
            'expires_at' => $ttl > 0 ? time() + $ttl : 0,
            'created_at' => time(),
        ];

        $filePath = $this->getFilePath($key);
        return file_put_contents($filePath, serialize($data), LOCK_EX) !== false;
    }

    /**
     * Store an item forever (no expiration).
     *
     * @param  string $key   Cache key
     * @param  mixed  $value Value to store
     * @return bool
     */
    public function forever(string $key, mixed $value): bool
    {
        return $this->set($key, $value, 0);
    }

    /**
     * Check if a cache key exists and is not expired.
     *
     * @param  string $key Cache key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string $key Cache key
     * @return bool
     */
    public function forget(string $key): bool
    {
        $filePath = $this->getFilePath($key);

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    /**
     * Get an item from the cache, or execute the given closure
     * and store the result.
     *
     * @param  string   $key      Cache key
     * @param  int      $ttl      Time-to-live in seconds
     * @param  callable $callback Function to generate the value
     * @return mixed
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Flush the entire cache.
     *
     * @return int Number of files deleted
     */
    public function flush(): int
    {
        $deleted = 0;
        $files   = glob($this->cachePath . '/*.cache');

        foreach ($files as $file) {
            if (unlink($file)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Increment a cached numeric value.
     *
     * @param  string $key    Cache key
     * @param  int    $amount Amount to increment
     * @return int             New value
     */
    public function increment(string $key, int $amount = 1): int
    {
        $value = (int) $this->get($key, 0);
        $value += $amount;
        $this->set($key, $value);
        return $value;
    }

    /**
     * Decrement a cached numeric value.
     *
     * @param  string $key    Cache key
     * @param  int    $amount Amount to decrement
     * @return int             New value
     */
    public function decrement(string $key, int $amount = 1): int
    {
        return $this->increment($key, -$amount);
    }

    /**
     * Clean up expired cache files.
     *
     * @return int Number of expired files removed
     */
    public function cleanup(): int
    {
        $cleaned = 0;
        $files   = glob($this->cachePath . '/*.cache');

        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));

            if ($data['expires_at'] !== 0 && $data['expires_at'] < time()) {
                unlink($file);
                $cleaned++;
            }
        }

        return $cleaned;
    }

    /**
     * Generate the file path for a cache key.
     *
     * @param  string $key Cache key
     * @return string      Absolute file path
     */
    private function getFilePath(string $key): string
    {
        $hash = md5($key);
        return $this->cachePath . '/' . $hash . '.cache';
    }

    /**
     * Prevent cloning of singleton.
     */
    private function __clone() {}
}
