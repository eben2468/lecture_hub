<?php
/**
 * ============================================================
 * Nadics LectureHub — HTTP Request Abstraction
 * ============================================================
 *
 * Wraps PHP's superglobals into a clean, object-oriented
 * interface for handling HTTP requests. Provides methods for
 * accessing query parameters, body data, files, headers,
 * and request metadata.
 *
 * @package    NadicsLectureHub
 * @subpackage Core
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace Core;

class Request
{
    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Parsed request body (for JSON, PUT, PATCH requests).
     *
     * @var array|null
     */
    private ?array $parsedBody = null;

    /**
     * Get the singleton Request instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the HTTP method (with method spoofing support).
     *
     * HTML forms only support GET and POST. This method checks
     * for a _method field to support PUT, PATCH, DELETE.
     *
     * @return string Uppercase HTTP method
     */
    public function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Support method spoofing via _method field
        if ($method === 'POST' && isset($_POST['_method'])) {
            $spoofed = strtoupper($_POST['_method']);
            if (in_array($spoofed, ['PUT', 'PATCH', 'DELETE'])) {
                return $spoofed;
            }
        }

        return $method;
    }

    /**
     * Get the request URI (without query string).
     *
     * @return string
     */
    public function uri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Decode URL encoded characters (e.g., %20 -> space)
        $uri = rawurldecode($uri);

        // Remove query string
        if (str_contains($uri, '?')) {
            $uri = strstr($uri, '?', true);
        }

        // Remove base path (for subfolder installations like XAMPP)
        $basePath = $this->getBasePath();
        if ($basePath && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = '/' . ltrim($uri, '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        return $uri;
    }

    /**
     * Get the base path for subfolder installations.
     *
     * Calculates the subfolder path from SCRIPT_NAME for XAMPP setups
     * where the app is in a subdirectory like /lecture_hub/public.
     *
     * @return string
     */
    public function getBasePath(): string
    {
        $scriptName = rawurldecode($_SERVER['SCRIPT_NAME'] ?? '');
        $basePath   = str_replace('/index.php', '', $scriptName);
        return rtrim($basePath, '/');
    }

    /**
     * Get the full request URL.
     *
     * @return string
     */
    public function fullUrl(): string
    {
        $scheme = $this->isSecure() ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri    = $_SERVER['REQUEST_URI'] ?? '/';

        return "{$scheme}://{$host}{$uri}";
    }

    /**
     * Check if the request method matches.
     *
     * @param  string $method Method to check
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    /**
     * Check if the request is a GET request.
     *
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    /**
     * Check if the request is a POST request.
     *
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    // ========================================================
    // INPUT DATA
    // ========================================================

    /**
     * Get a value from the request input (GET, POST, or JSON body).
     *
     * @param  string $key     Input key
     * @param  mixed  $default Default value
     * @return mixed
     */
    public function input(string $key, mixed $default = null): mixed
    {
        // Check POST data first
        if (isset($_POST[$key])) {
            return $this->sanitize($_POST[$key]);
        }

        // Check GET data
        if (isset($_GET[$key])) {
            return $this->sanitize($_GET[$key]);
        }

        // Check JSON body
        $body = $this->getJsonBody();
        if (isset($body[$key])) {
            return $body[$key];
        }

        return $default;
    }

    /**
     * Get all input data.
     *
     * @return array
     */
    public function all(): array
    {
        $data = array_merge($_GET, $_POST, $this->getJsonBody());

        return array_map(function ($value) {
            return is_string($value) ? $this->sanitize($value) : $value;
        }, $data);
    }

    /**
     * Get only specific keys from input.
     *
     * @param  array $keys Keys to retrieve
     * @return array
     */
    public function only(array $keys): array
    {
        $all  = $this->all();
        $data = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $all)) {
                $data[$key] = $all[$key];
            }
        }

        return $data;
    }

    /**
     * Get all input except specific keys.
     *
     * @param  array $keys Keys to exclude
     * @return array
     */
    public function except(array $keys): array
    {
        $all = $this->all();
        foreach ($keys as $key) {
            unset($all[$key]);
        }
        return $all;
    }

    /**
     * Check if an input key exists.
     *
     * @param  string $key Input key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->input($key) !== null;
    }

    /**
     * Check if an input key exists and is not empty.
     *
     * @param  string $key Input key
     * @return bool
     */
    public function filled(string $key): bool
    {
        $value = $this->input($key);
        return $value !== null && $value !== '';
    }

    /**
     * Get a query parameter (GET).
     *
     * @param  string $key     Parameter name
     * @param  mixed  $default Default value
     * @return mixed
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return isset($_GET[$key]) ? $this->sanitize($_GET[$key]) : $default;
    }

    /**
     * Get a POST parameter.
     *
     * @param  string $key     Parameter name
     * @param  mixed  $default Default value
     * @return mixed
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return isset($_POST[$key]) ? $this->sanitize($_POST[$key]) : $default;
    }

    // ========================================================
    // FILE UPLOADS
    // ========================================================

    /**
     * Get an uploaded file.
     *
     * @param  string $key File input name
     * @return array|null  File data or null
     */
    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Check if a file was uploaded.
     *
     * @param  string $key File input name
     * @return bool
     */
    public function hasFile(string $key): bool
    {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK;
    }

    // ========================================================
    // HEADERS & METADATA
    // ========================================================

    /**
     * Get a request header.
     *
     * @param  string      $name    Header name
     * @param  string|null $default Default value
     * @return string|null
     */
    public function header(string $name, ?string $default = null): ?string
    {
        $headers = $this->headers();
        // Headers are case-insensitive
        $name = strtolower($name);
        foreach ($headers as $key => $value) {
            if (strtolower($key) === $name) {
                return $value;
            }
        }
        return $default;
    }

    /**
     * Get all request headers.
     *
     * @return array
     */
    public function headers(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        // Fallback for servers without getallheaders()
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace('_', '-', substr($key, 5));
                $header = ucwords(strtolower($header), '-');
                $headers[$header] = $value;
            }
        }
        return $headers;
    }

    /**
     * Get the client's IP address.
     *
     * Handles proxy headers for load-balanced environments.
     *
     * @return string
     */
    public function ip(): string
    {
        $keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($keys as $key) {
            if (isset($_SERVER[$key])) {
                $ip = explode(',', $_SERVER[$key])[0];
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Get the user agent string.
     *
     * @return string
     */
    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Check if the request is over HTTPS.
     *
     * @return bool
     */
    public function isSecure(): bool
    {
        return (
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        );
    }

    /**
     * Check if the request is AJAX (XMLHttpRequest).
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );
    }

    /**
     * Check if the request expects a JSON response.
     *
     * @return bool
     */
    public function expectsJson(): bool
    {
        $accept = $this->header('Accept', '');
        return str_contains($accept, 'application/json') || $this->isAjax();
    }

    /**
     * Check if the request has a JSON content type.
     *
     * @return bool
     */
    public function isJson(): bool
    {
        $contentType = $this->header('Content-Type', '');
        return str_contains($contentType, 'application/json');
    }

    /**
     * Check if this is an API request (URI starts with /api/).
     *
     * @return bool
     */
    public function isApi(): bool
    {
        return str_starts_with($this->uri(), '/api/');
    }

    // ========================================================
    // INTERNAL HELPERS
    // ========================================================

    /**
     * Parse the JSON request body.
     *
     * @return array
     */
    private function getJsonBody(): array
    {
        if ($this->parsedBody === null) {
            $this->parsedBody = [];
            if ($this->isJson()) {
                $raw = file_get_contents('php://input');
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $this->parsedBody = $decoded;
                }
            }
        }
        return $this->parsedBody;
    }

    /**
     * Sanitize a string input value.
     *
     * Trims whitespace and strips null bytes.
     *
     * @param  mixed $value Input value
     * @return mixed
     */
    private function sanitize(mixed $value): mixed
    {
        if (is_string($value)) {
            // Remove null bytes (security)
            $value = str_replace(chr(0), '', $value);
            return trim($value);
        }

        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }

        return $value;
    }

    /**
     * Prevent cloning of singleton.
     */
    private function __clone() {}
}
