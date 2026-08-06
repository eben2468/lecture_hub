<?php
/**
 * ============================================================
 * Nadics LectureHub — Global Helper Functions
 * ============================================================
 * 
 * Provides globally accessible utility functions used throughout
 * the application. Inspired by Laravel's helper functions.
 * 
 * @package    NadicsLectureHub
 * @subpackage Core
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

use Core\Application;
use Core\Session;
use Core\CSRF;

// ============================================================
// ENVIRONMENT & CONFIGURATION
// ============================================================

if (!function_exists('env')) {
    /**
     * Retrieve an environment variable value.
     *
     * Reads from the parsed .env file stored in the Application
     * container. Returns the default value if the key is not found.
     *
     * @param  string $key     The environment variable name
     * @param  mixed  $default Default value if key doesn't exist
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed
    {
        static $envCache = null;

        if ($envCache === null) {
            $envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
            $envCache = [];

            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    // Skip comments
                    $line = trim($line);
                    if (str_starts_with($line, '#') || empty($line)) {
                        continue;
                    }

                    // Parse key=value pairs
                    if (str_contains($line, '=')) {
                        [$name, $value] = explode('=', $line, 2);
                        $name  = trim($name);
                        $value = trim($value);

                        // Remove surrounding quotes
                        if (
                            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                            (str_starts_with($value, "'") && str_ends_with($value, "'"))
                        ) {
                            $value = substr($value, 1, -1);
                        }

                        // Type casting for common values
                        $value = match (strtolower($value)) {
                            'true', '(true)'   => true,
                            'false', '(false)'  => false,
                            'null', '(null)'    => null,
                            'empty', '(empty)'  => '',
                            default             => $value,
                        };

                        $envCache[$name] = $value;
                    }
                }
            }
        }

        return $envCache[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    /**
     * Retrieve a configuration value using dot notation.
     *
     * Example: config('app.name') reads config/app.php['name']
     *
     * @param  string $key     Dot-notated config key (file.key.subkey)
     * @param  mixed  $default Default value if not found
     * @return mixed
     */
    function config(string $key, mixed $default = null): mixed
    {
        static $configCache = [];

        $segments = explode('.', $key);
        $file     = array_shift($segments);

        // Load config file if not cached
        if (!isset($configCache[$file])) {
            $configPath = dirname(__DIR__) . '/config/' . $file . '.php';
            if (file_exists($configPath)) {
                $configCache[$file] = require $configPath;
            } else {
                $configCache[$file] = [];
            }
        }

        // Traverse nested keys
        $value = $configCache[$file];
        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }
}

// ============================================================
// APPLICATION PATHS
// ============================================================

if (!function_exists('base_path')) {
    /**
     * Get the application base directory path.
     *
     * @param  string $path Optional path to append
     * @return string
     */
    function base_path(string $path = ''): string
    {
        $base = dirname(__DIR__);
        return $path ? $base . DIRECTORY_SEPARATOR . ltrim($path, '/\\') : $base;
    }
}

if (!function_exists('app_path')) {
    /**
     * Get the app/ directory path.
     *
     * @param  string $path Optional path to append
     * @return string
     */
    function app_path(string $path = ''): string
    {
        return base_path('app' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : ''));
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the storage/ directory path.
     *
     * @param  string $path Optional path to append
     * @return string
     */
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : ''));
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the public/ directory path.
     *
     * @param  string $path Optional path to append
     * @return string
     */
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : ''));
    }
}

if (!function_exists('resource_path')) {
    /**
     * Get the resources/ directory path.
     *
     * @param  string $path Optional path to append
     * @return string
     */
    function resource_path(string $path = ''): string
    {
        return base_path('resources' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : ''));
    }
}

// ============================================================
// URL & ASSET HELPERS
// ============================================================

if (!function_exists('url')) {
    /**
     * Generate a full URL for the application.
     *
     * Auto-detects the real server URL from HTTP request variables so the app
     * works correctly on both localhost and any live/hosted server without
     * needing to change APP_URL in .env.
     *
     * Priority:
     *  1. APP_URL when it is explicitly set to a non-localhost value (production override).
     *  2. Auto-detected scheme + host + script directory from $_SERVER (HTTP requests).
     *  3. APP_URL as a plain fallback (CLI / test runners).
     *
     * @param  string $path Path relative to application root
     * @return string       Full URL
     */
    function url(string $path = ''): string
    {
        $configured = rtrim(env('APP_URL', ''), '/');

        // Determine whether APP_URL has been explicitly pointed at a real server.
        $isLocalhostUrl = empty($configured)
            || str_contains($configured, 'localhost')
            || str_contains($configured, '127.0.0.1');

        if (!$isLocalhostUrl) {
            // ── Production override ──────────────────────────────────────────
            // APP_URL is set to a real domain (e.g. https://learning-hub.nadicssolution.com).
            // Use it directly — no basePath manipulation needed for root domains.
            $baseUrl = $configured;
        } elseif (isset($_SERVER['HTTP_HOST'])) {
            // ── Auto-detect from live HTTP request ───────────────────────────
            // Works for localhost/subfolder AND root-domain hosting without any
            // .env change.
            $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host    = $_SERVER['HTTP_HOST'];

            // SCRIPT_NAME is the path to index.php from the server root.
            // Strip the filename to get the base directory.
            $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/\\');

            $baseUrl = rtrim($scheme . '://' . $host . $scriptDir, '/');
        } else {
            // ── CLI / test fallback ──────────────────────────────────────────
            $baseUrl = $configured ?: 'http://localhost';

            // Apply basePath adjustment only in CLI context where $_SERVER is absent.
            $basePath = \Core\Request::getInstance()->getBasePath();
            if ($basePath) {
                $decodedBaseUrl = rawurldecode($baseUrl);
                if (!str_ends_with($decodedBaseUrl, $basePath)) {
                    $baseUrl .= $basePath;
                }
            }
        }

        return $path ? rtrim($baseUrl, '/') . '/' . ltrim($path, '/') : $baseUrl;
    }
}

if (!function_exists('asset')) {
    /**
     * Generate a URL to a public asset (CSS, JS, images).
     *
     * @param  string $path Asset path relative to public/assets/
     * @return string       Full asset URL
     */
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('route')) {
    /**
     * Generate a URL for a named route.
     *
     * @param  string $name   Route name
     * @param  array  $params Route parameters
     * @return string         Generated URL
     */
    function route(string $name, array $params = []): string
    {
        // Route URL generation is delegated to the Router
        // This is a placeholder that will be connected in Application::boot()
        return url($name);
    }
}

// ============================================================
// VIEW & RESPONSE HELPERS
// ============================================================

if (!function_exists('view')) {
    /**
     * Render a view template with data.
     *
     * Uses dot notation for nested views:
     *   view('dashboard.index') => resources/views/dashboard/index.php
     *
     * @param  string $name View name (dot notation)
     * @param  array  $data Data to pass to the view
     * @return string       Rendered HTML content
     */
    function view(string $name, array $data = []): string
    {
        $viewEngine = new Core\View();
        return $viewEngine->render($name, $data);
    }
}

if (!function_exists('redirect')) {
    /**
     * Create a redirect response.
     *
     * @param  string $url  Target URL
     * @param  int    $code HTTP status code (default 302)
     * @return void
     */
    function redirect(string $url, int $code = 302): void
    {
        http_response_code($code);
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('back')) {
    /**
     * Redirect back to the previous page.
     *
     * @return void
     */
    function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        redirect($referer);
    }
}

if (!function_exists('json_response')) {
    /**
     * Send a JSON response and terminate.
     *
     * @param  mixed $data   Data to encode as JSON
     * @param  int   $status HTTP status code
     * @return void
     */
    function json_response(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

// ============================================================
// SESSION & FLASH MESSAGES
// ============================================================

if (!function_exists('session')) {
    /**
     * Get or set a session value.
     *
     * @param  string|null $key   Session key (null returns Session instance)
     * @param  mixed       $value Value to set (null to get)
     * @return mixed
     */
    function session(?string $key = null, mixed $value = null): mixed
    {
        if ($key === null) {
            return Session::getInstance();
        }

        if ($value !== null) {
            Session::getInstance()->set($key, $value);
            return $value;
        }

        return Session::getInstance()->get($key);
    }
}

if (!function_exists('flash')) {
    /**
     * Set a flash message for the next request.
     *
     * @param  string $key     Flash message key (success, error, warning, info)
     * @param  string $message The message content
     * @return void
     */
    function flash(string $key, string $message): void
    {
        Session::getInstance()->flash($key, $message);
    }
}

if (!function_exists('old')) {
    /**
     * Retrieve old input from the previous request.
     *
     * Useful for repopulating forms after validation failure.
     *
     * @param  string $key     Input field name
     * @param  mixed  $default Default value
     * @return mixed
     */
    function old(string $key, mixed $default = ''): mixed
    {
        return Session::getInstance()->getOld($key, $default);
    }
}

// ============================================================
// SECURITY HELPERS
// ============================================================

if (!function_exists('csrf_token')) {
    /**
     * Get the current CSRF token.
     *
     * @return string The CSRF token string
     */
    function csrf_token(): string
    {
        return CSRF::getToken();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate a hidden CSRF token input field.
     *
     * @return string HTML hidden input element
     */
    function csrf_field(): string
    {
        $token = csrf_token();
        return '<input type="hidden" name="_csrf_token" value="' . e($token) . '">';
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities to prevent XSS attacks.
     *
     * @param  string|null $value  The raw string
     * @param  bool        $double Whether to double-encode
     * @return string              The escaped string
     */
    function e(?string $value, bool $double = true): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $double);
    }
}

if (!function_exists('clean')) {
    /**
     * Sanitize a string by stripping tags and trimming.
     *
     * @param  string|null $value The raw input
     * @return string             Cleaned string
     */
    function clean(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return trim(strip_tags($value));
    }
}

// ============================================================
// STRING UTILITIES
// ============================================================

if (!function_exists('str_slug')) {
    /**
     * Generate a URL-friendly slug from a string.
     *
     * @param  string $text      The input string
     * @param  string $separator Word separator (default: -)
     * @return string            URL-safe slug
     */
    function str_slug(string $text, string $separator = '-'): string
    {
        // Transliterate to ASCII
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        // Replace non-alphanumeric characters
        $text = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
        // Replace whitespace with separator
        $text = preg_replace('/[\s]+/', $separator, $text);
        // Trim separator from ends
        $text = trim($text, $separator);
        return strtolower($text);
    }
}

if (!function_exists('str_random')) {
    /**
     * Generate a random alphanumeric string.
     *
     * @param  int    $length String length
     * @return string         Random string
     * @throws \Exception     If random bytes generation fails
     */
    function str_random(int $length = 32): string
    {
        $bytes = random_bytes((int) ceil($length / 2));
        return substr(bin2hex($bytes), 0, $length);
    }
}

if (!function_exists('str_limit')) {
    /**
     * Truncate a string to a maximum length with ellipsis.
     *
     * @param  string $text  The input string
     * @param  int    $limit Maximum character count
     * @param  string $end   Suffix to append (default: ...)
     * @return string
     */
    function str_limit(string $text, int $limit = 100, string $end = '...'): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }
        return mb_substr($text, 0, $limit) . $end;
    }
}

// ============================================================
// DATE & TIME
// ============================================================

if (!function_exists('now')) {
    /**
     * Get the current date/time as a formatted string.
     *
     * @param  string $format Date format (default: Y-m-d H:i:s)
     * @return string
     */
    function now(string $format = 'Y-m-d H:i:s'): string
    {
        return date($format);
    }
}

if (!function_exists('time_ago')) {
    /**
     * Convert a datetime string to a human-readable "time ago" format.
     *
     * @param  string $datetime The datetime string
     * @return string           Human-readable time difference
     */
    function time_ago(string $datetime): string
    {
        $timestamp = strtotime($datetime);
        $diff      = time() - $timestamp;

        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
        } else {
            $years = floor($diff / 31536000);
            return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
        }
    }
}

// ============================================================
// FILE SIZE & FORMAT UTILITIES
// ============================================================

if (!function_exists('format_bytes')) {
    /**
     * Format bytes into a human-readable string.
     *
     * @param  int    $bytes     Number of bytes
     * @param  int    $precision Decimal precision
     * @return string            Formatted string (e.g., "1.5 MB")
     */
    function format_bytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, $precision) . ' ' . $units[$index];
    }
}

// ============================================================
// DEBUGGING
// ============================================================

if (!function_exists('dd')) {
    /**
     * Dump variables and die (stop execution).
     *
     * Only active when APP_DEBUG is true.
     *
     * @param  mixed ...$vars Variables to dump
     * @return void
     */
    function dd(mixed ...$vars): void
    {
        if (!env('APP_DEBUG', false)) {
            return;
        }

        echo '<pre style="background:#1e1e2e;color:#cdd6f4;padding:20px;margin:10px;border-radius:8px;font-family:monospace;font-size:13px;overflow-x:auto;">';
        foreach ($vars as $var) {
            var_dump($var);
            echo "\n" . str_repeat('─', 60) . "\n";
        }
        echo '</pre>';
        exit(1);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump variables without stopping execution.
     *
     * @param  mixed ...$vars Variables to dump
     * @return void
     */
    function dump(mixed ...$vars): void
    {
        if (!env('APP_DEBUG', false)) {
            return;
        }

        echo '<pre style="background:#1e1e2e;color:#cdd6f4;padding:15px;margin:10px;border-radius:8px;font-family:monospace;font-size:13px;overflow-x:auto;">';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
    }
}

// ============================================================
// MISCELLANEOUS
// ============================================================

if (!function_exists('abort')) {
    /**
     * Abort the request with an HTTP error status.
     *
     * Renders the appropriate error view if available.
     *
     * @param  int    $code    HTTP status code
     * @param  string $message Error message
     * @return void
     */
    function abort(int $code = 404, string $message = ''): void
    {
        http_response_code($code);

        $errorView = resource_path("views/errors/{$code}.php");
        if (file_exists($errorView)) {
            extract(['message' => $message, 'code' => $code]);
            require $errorView;
        } else {
            echo "<h1>Error {$code}</h1>";
            if ($message) {
                echo "<p>" . e($message) . "</p>";
            }
        }
        exit;
    }
}

if (!function_exists('is_ajax')) {
    /**
     * Check if the current request is an AJAX request.
     *
     * @return bool
     */
    function is_ajax(): bool
    {
        return (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );
    }
}

if (!function_exists('method_field')) {
    /**
     * Generate a hidden input for HTTP method spoofing.
     *
     * HTML forms only support GET and POST. Use this helper
     * to send PUT, PATCH, or DELETE requests.
     *
     * @param  string $method The HTTP method (PUT, PATCH, DELETE)
     * @return string         Hidden input HTML
     */
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
    }
}

if (!function_exists('auth')) {
    /**
     * Get the authenticated user or check authentication status.
     *
     * @return \Core\Auth The Auth instance
     */
    function auth(): Core\Auth
    {
        return Core\Auth::getInstance();
    }
}

if (!function_exists('generate_default_audio_wav')) {
    /**
     * Generate valid WAV PCM audio data with modulation.
     *
     * @param  int    $durationSeconds Duration in seconds
     * @param  int    $frequency       Base tone frequency in Hz
     * @param  int    $sampleRate      Audio sample rate in Hz
     * @return string                  Raw binary WAV file content
     */
    function generate_default_audio_wav(int $durationSeconds, int $frequency = 350, int $sampleRate = 11025): string
    {
        $numChannels = 1; // mono
        $bitsPerSample = 8; // 8-bit unsigned PCM
        
        $numSamples = $sampleRate * $durationSeconds;
        $dataSize = $numSamples * $numChannels * ($bitsPerSample / 8);
        
        $headerSize = 44;
        $totalSize = $headerSize + $dataSize - 8;
        
        // RIFF header
        $header = 'RIFF';
        $header .= pack('V', $totalSize);
        $header .= 'WAVE';
        
        // Format subchunk
        $header .= 'fmt ';
        $header .= pack('V', 16); // subchunk size
        $header .= pack('v', 1); // audio format (PCM = 1)
        $header .= pack('v', $numChannels);
        $header .= pack('V', $sampleRate);
        $header .= pack('V', $sampleRate * $numChannels * ($bitsPerSample / 8)); // byte rate
        $header .= pack('v', $numChannels * ($bitsPerSample / 8)); // block align
        $header .= pack('v', $bitsPerSample);
        
        // Data subchunk
        $header .= 'data';
        $header .= pack('V', $dataSize);
        
        // Generate warm harmonic audio tone (soft ambient acoustics, no alarm pitch sweeps)
        $data = '';
        for ($i = 0; $i < $numSamples; $i++) {
            $t = $i / $sampleRate;
            // Soft A-major triad (220Hz, 277.18Hz, 329.63Hz) with a subtle decay envelope
            $env = 0.25 * (0.8 + 0.2 * sin(2 * M_PI * 0.25 * $t));
            $sample = sin(2 * M_PI * 220 * $t) * 0.4 
                    + sin(2 * M_PI * 277.18 * $t) * 0.3 
                    + sin(2 * M_PI * 329.63 * $t) * 0.3;
            $val = 128 + 127 * $env * $sample;
            $data .= chr((int)round(max(0, min(255, $val))));
        }
        
        return $header . $data;
    }
}
