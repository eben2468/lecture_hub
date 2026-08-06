<?php
/**
 * ============================================================
 * Nadics LectureHub — HTTP Response Builder
 * ============================================================
 *
 * Provides a fluent interface for building HTTP responses.
 * Supports JSON, HTML, redirects, downloads, and streaming.
 *
 * @package    NadicsLectureHub
 * @subpackage Core
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace Core;

class Response
{
    /** @var int HTTP status code */
    private int $statusCode = 200;

    /** @var array Response headers */
    private array $headers = [];

    /** @var string Response body content */
    private string $body = '';

    /**
     * Set the HTTP status code.
     *
     * @param  int  $code HTTP status code
     * @return self
     */
    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Set a response header.
     *
     * @param  string $name  Header name
     * @param  string $value Header value
     * @return self
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set the response body.
     *
     * @param  string $content Body content
     * @return self
     */
    public function body(string $content): self
    {
        $this->body = $content;
        return $this;
    }

    /**
     * Send an HTML response.
     *
     * @param  string $html   HTML content
     * @param  int    $status HTTP status code
     * @return void
     */
    public function html(string $html, int $status = 200): void
    {
        $this->status($status)
             ->header('Content-Type', 'text/html; charset=utf-8')
             ->body($html)
             ->send();
    }

    /**
     * Send a JSON response.
     *
     * @param  mixed $data   Data to encode
     * @param  int   $status HTTP status code
     * @return void
     */
    public function json(mixed $data, int $status = 200): void
    {
        $this->status($status)
             ->header('Content-Type', 'application/json; charset=utf-8')
             ->body(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
             ->send();
    }

    /**
     * Send a redirect response.
     *
     * @param  string $url  Redirect URL
     * @param  int    $code HTTP status code (302 = temporary, 301 = permanent)
     * @return void
     */
    public function redirect(string $url, int $code = 302): void
    {
        $this->status($code)
             ->header('Location', $url)
             ->send();
    }

    /**
     * Send a redirect back to the previous page.
     *
     * @return void
     */
    public function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        $this->redirect($referer);
    }

    /**
     * Send a file download response.
     *
     * @param  string      $filePath Absolute path to the file
     * @param  string|null $filename Download filename (default: original name)
     * @return void
     */
    public function download(string $filePath, ?string $filename = null): void
    {
        if (!file_exists($filePath)) {
            $this->status(404)->body('File not found')->send();
            return;
        }

        $filename = $filename ?? basename($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        $fileSize = filesize($filePath);

        http_response_code(200);
        header("Content-Type: {$mimeType}");
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header("Content-Length: {$fileSize}");
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        readfile($filePath);
        exit;
    }

    /**
     * Stream a file inline (for viewing in browser).
     *
     * @param  string $filePath Absolute path to the file
     * @return void
     */
    public function stream(string $filePath): void
    {
        if (!file_exists($filePath)) {
            $this->status(404)->body('File not found')->send();
            return;
        }

        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        $fileSize = filesize($filePath);

        http_response_code(200);
        header("Content-Type: {$mimeType}");
        header("Content-Length: {$fileSize}");
        header('Content-Disposition: inline');

        readfile($filePath);
        exit;
    }

    /**
     * Send a no-content response (204).
     *
     * @return void
     */
    public function noContent(): void
    {
        $this->status(204)->send();
    }

    /**
     * Send the response to the client.
     *
     * @return void
     */
    public function send(): void
    {
        // Set status code
        http_response_code($this->statusCode);

        // Send headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // Security headers
        if (!isset($this->headers['X-Content-Type-Options'])) {
            header('X-Content-Type-Options: nosniff');
        }
        if (!isset($this->headers['X-Frame-Options'])) {
            header('X-Frame-Options: SAMEORIGIN');
        }
        if (!isset($this->headers['X-XSS-Protection'])) {
            header('X-XSS-Protection: 1; mode=block');
        }
        if (!isset($this->headers['Referrer-Policy'])) {
            header('Referrer-Policy: strict-origin-when-cross-origin');
        }

        // Output body
        echo $this->body;
        exit;
    }

    /**
     * Create a new Response instance (static factory).
     *
     * @return self
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * Create an error JSON response.
     *
     * @param  string $message Error message
     * @param  int    $status  HTTP status code
     * @param  array  $errors  Detailed error array
     * @return void
     */
    public static function error(string $message, int $status = 400, array $errors = []): void
    {
        $data = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $data['errors'] = $errors;
        }

        (new self())->json($data, $status);
    }

    /**
     * Create a success JSON response.
     *
     * @param  string $message Success message
     * @param  mixed  $data    Response data
     * @param  int    $status  HTTP status code
     * @return void
     */
    public static function success(string $message, mixed $data = null, int $status = 200): void
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        (new self())->json($response, $status);
    }
}
