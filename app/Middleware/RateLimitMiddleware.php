<?php
/**
 * ============================================================
 * Nadics LectureHub — Rate Limit Middleware
 * ============================================================
 *
 * Implements file-based token bucket rate limiting.
 * Protects against brute-force attacks and API abuse.
 *
 * @package    NadicsLectureHub
 * @subpackage App\Middleware
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace App\Middleware;

use Core\Middleware;
use Core\Request;
use Core\Response;
use Core\Logger;

class RateLimitMiddleware extends Middleware
{
    /**
     * Maximum number of requests allowed.
     *
     * @var int
     */
    private int $maxAttempts;

    /**
     * Time window in seconds.
     *
     * @var int
     */
    private int $window;

    /**
     * Path for storing rate limit data.
     *
     * @var string
     */
    private string $storagePath;

    /**
     * Create a new RateLimitMiddleware instance.
     *
     * @param int $maxAttempts Max requests per window
     * @param int $window      Window duration in seconds
     */
    public function __construct(int $maxAttempts = 0, int $window = 0)
    {
        $this->maxAttempts = $maxAttempts ?: (int) env('RATE_LIMIT_MAX', 60);
        $this->window      = $window ?: (int) env('RATE_LIMIT_WINDOW', 60);
        $this->storagePath = dirname(__DIR__, 2) . '/storage/cache/rate_limits';

        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    /**
     * Handle the incoming request.
     *
     * @param  Request  $request The HTTP request
     * @param  callable $next    Next middleware
     * @return void
     */
    public function handle(Request $request, callable $next): void
    {
        $key = $this->resolveKey($request);

        if ($this->tooManyAttempts($key)) {
            $retryAfter = $this->getRetryAfter($key);

            Logger::getInstance()->warning('Rate limit exceeded', [
                'ip'          => $request->ip(),
                'uri'         => $request->uri(),
                'retry_after' => $retryAfter,
            ]);

            if ($request->expectsJson() || $request->isApi()) {
                http_response_code(429);
                header("Retry-After: {$retryAfter}");
                Response::error('Too many requests. Please try again later.', 429);
                return;
            }

            flash('error', 'Too many requests. Please wait a moment and try again.');
            back();
            return;
        }

        $this->hit($key);
        $next();
    }

    /**
     * Generate a unique rate limit key for the request.
     *
     * @param  Request $request
     * @return string
     */
    private function resolveKey(Request $request): string
    {
        return md5($request->ip() . '|' . $request->uri());
    }

    /**
     * Check if the rate limit has been exceeded.
     *
     * @param  string $key Rate limit key
     * @return bool
     */
    private function tooManyAttempts(string $key): bool
    {
        $data = $this->getData($key);

        if (!$data) {
            return false;
        }

        // Clean up expired entries
        if ($data['window_start'] + $this->window < time()) {
            $this->resetKey($key);
            return false;
        }

        return $data['attempts'] >= $this->maxAttempts;
    }

    /**
     * Record a request attempt.
     *
     * @param  string $key Rate limit key
     * @return void
     */
    private function hit(string $key): void
    {
        $data = $this->getData($key);

        if (!$data || ($data['window_start'] + $this->window < time())) {
            $data = [
                'attempts'     => 1,
                'window_start' => time(),
            ];
        } else {
            $data['attempts']++;
        }

        $this->saveData($key, $data);
    }

    /**
     * Get the seconds until the rate limit window resets.
     *
     * @param  string $key Rate limit key
     * @return int
     */
    private function getRetryAfter(string $key): int
    {
        $data = $this->getData($key);

        if (!$data) {
            return 0;
        }

        return max(0, ($data['window_start'] + $this->window) - time());
    }

    /**
     * Get rate limit data for a key.
     *
     * @param  string $key
     * @return array|null
     */
    private function getData(string $key): ?array
    {
        $file = $this->storagePath . '/' . $key . '.json';

        if (!file_exists($file)) {
            return null;
        }

        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : null;
    }

    /**
     * Save rate limit data for a key.
     *
     * @param  string $key
     * @param  array  $data
     * @return void
     */
    private function saveData(string $key, array $data): void
    {
        $file = $this->storagePath . '/' . $key . '.json';
        file_put_contents($file, json_encode($data), LOCK_EX);
    }

    /**
     * Reset rate limit data for a key.
     *
     * @param  string $key
     * @return void
     */
    private function resetKey(string $key): void
    {
        $file = $this->storagePath . '/' . $key . '.json';
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
