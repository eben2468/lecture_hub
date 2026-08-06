<?php
/**
 * ============================================================
 * Nadics LectureHub — Base Controller
 * ============================================================
 *
 * All application controllers extend this base class.
 * Provides view rendering, JSON responses, validation,
 * redirect helpers, and flash message support.
 *
 * @package    NadicsLectureHub
 * @subpackage Core
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace Core;

abstract class Controller
{
    /**
     * Middleware registered on this controller.
     *
     * @var array
     */
    protected array $middleware = [];

    /**
     * The current request instance.
     *
     * @var Request|null
     */
    protected ?Request $request = null;

    /**
     * Render a view and send it as the response.
     *
     * @param  string $viewName View name (dot notation)
     * @param  array  $data     Data to pass to the view
     * @param  int    $status   HTTP status code
     * @return void
     */
    protected function view(string $viewName, array $data = [], int $status = 200): void
    {
        // Add common data available in all views
        $data = array_merge($this->getSharedViewData(), $data);

        $html = view($viewName, $data);
        http_response_code($status);
        echo $html;
        exit;
    }

    /**
     * Send a JSON response.
     *
     * @param  mixed $data   Response data
     * @param  int   $status HTTP status code
     * @return void
     */
    protected function json(mixed $data, int $status = 200): void
    {
        json_response($data, $status);
    }

    /**
     * Send a JSON success response.
     *
     * @param  string $message Success message
     * @param  mixed  $data    Response data
     * @param  int    $status  HTTP status code
     * @return void
     */
    protected function jsonSuccess(string $message, mixed $data = null, int $status = 200): void
    {
        Response::success($message, $data, $status);
    }

    /**
     * Send a JSON error response.
     *
     * @param  string $message Error message
     * @param  int    $status  HTTP status code
     * @param  array  $errors  Detailed errors
     * @return void
     */
    protected function jsonError(string $message, int $status = 400, array $errors = []): void
    {
        Response::error($message, $status, $errors);
    }

    /**
     * Redirect to a URL.
     *
     * @param  string $url  Target URL
     * @param  int    $code HTTP redirect code
     * @return void
     */
    protected function redirect(string $url, int $code = 302): void
    {
        redirect($url, $code);
    }

    /**
     * Redirect back to the previous page.
     *
     * @return void
     */
    protected function back(): void
    {
        back();
    }

    /**
     * Redirect back with errors and old input.
     *
     * Used after validation failure to repopulate the form.
     *
     * @param  array $errors Validation errors
     * @param  array $input  Form input to preserve
     * @return void
     */
    protected function backWithErrors(array $errors, array $input = []): void
    {
        $session = Session::getInstance();
        $session->flash('errors', json_encode($errors));

        if (!empty($input)) {
            $session->flashInput($input);
        }

        back();
    }

    /**
     * Redirect back with a success message.
     *
     * @param  string $message Success message
     * @return void
     */
    protected function backWithSuccess(string $message): void
    {
        flash('success', $message);
        back();
    }

    /**
     * Redirect to a URL with a success message.
     *
     * @param  string $url     Target URL
     * @param  string $message Success message
     * @return void
     */
    protected function redirectWithSuccess(string $url, string $message): void
    {
        flash('success', $message);
        redirect($url);
    }

    /**
     * Redirect to a URL with an error message.
     *
     * @param  string $url     Target URL
     * @param  string $message Error message
     * @return void
     */
    protected function redirectWithError(string $url, string $message): void
    {
        flash('error', $message);
        redirect($url);
    }

    /**
     * Validate request input.
     *
     * Returns validated data on success, or redirects back
     * with errors on failure (for web requests), or sends
     * a JSON error response (for API requests).
     *
     * @param  Request $request The HTTP request
     * @param  array   $rules   Validation rules
     * @param  array   $messages Custom error messages
     * @return array   Validated data
     */
    protected function validate(Request $request, array $rules, array $messages = []): array
    {
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->isApi()) {
                Response::error('Validation failed', 422, $validator->errors());
            }

            $this->backWithErrors($validator->errors(), $request->all());
            exit; // backWithErrors calls back() which calls exit, but just in case
        }

        return $validator->validated();
    }

    /**
     * Get data shared across all views.
     *
     * Provides authenticated user data, flash messages,
     * and other global view variables.
     *
     * @return array
     */
    protected function getSharedViewData(): array
    {
        $session = Session::getInstance();
        $auth    = Auth::getInstance();

        return [
            'auth_user'    => $auth->check() ? $auth->user() : null,
            'auth_check'   => $auth->check(),
            'flash_success' => $session->getFlash('success'),
            'flash_error'   => $session->getFlash('error'),
            'flash_warning' => $session->getFlash('warning'),
            'flash_info'    => $session->getFlash('info'),
            'errors'        => json_decode($session->getFlash('errors', '{}'), true) ?: [],
            'app_name'      => env('APP_NAME', 'Nadics LectureHub'),
            'app_version'   => env('APP_VERSION', '1.0.0'),
        ];
    }

    /**
     * Check if the current user is authorized.
     *
     * @param  string|array $roles Allowed roles
     * @return void
     * @throws \RuntimeException If not authorized
     */
    protected function authorize(string|array $roles): void
    {
        $auth = Auth::getInstance();

        if (!$auth->check()) {
            redirect(url('/login'));
        }

        if (!$auth->hasRole($roles)) {
            abort(403, 'You do not have permission to access this resource.');
        }
    }

    /**
     * Check if the current user has a specific permission.
     *
     * @param  string $permission Permission name
     * @return void
     */
    protected function authorizePermission(string $permission): void
    {
        if (!Auth::getInstance()->hasPermission($permission)) {
            abort(403, 'You do not have the required permission.');
        }
    }
}
