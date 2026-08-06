<?php
/**
 * ============================================================
 * Nadics LectureHub — URL Router
 * ============================================================
 *
 * Full-featured routing engine supporting:
 * - HTTP methods: GET, POST, PUT, PATCH, DELETE
 * - Named routes for URL generation
 * - Route groups with prefix and middleware
 * - Route parameters with regex constraints
 * - Middleware attachment per route
 * - Automatic 404 handling
 *
 * @package    NadicsLectureHub
 * @subpackage Core
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace Core;

class Router
{
    /**
     * Registered routes indexed by HTTP method.
     *
     * @var array<string, array>
     */
    private array $routes = [
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'PATCH'  => [],
        'DELETE' => [],
    ];

    /**
     * Named routes for URL generation.
     *
     * @var array<string, string>
     */
    private array $namedRoutes = [];

    /**
     * Current group prefix stack.
     *
     * @var array
     */
    private array $groupStack = [];

    /**
     * Current group middleware stack.
     *
     * @var array
     */
    private array $groupMiddleware = [];

    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Get the singleton Router instance.
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
     * Register a GET route.
     *
     * @param  string          $uri    URI pattern
     * @param  string|callable $action Controller@method or closure
     * @return self
     */
    public function get(string $uri, string|callable $action): self
    {
        return $this->addRoute('GET', $uri, $action);
    }

    /**
     * Register a POST route.
     *
     * @param  string          $uri    URI pattern
     * @param  string|callable $action Controller@method or closure
     * @return self
     */
    public function post(string $uri, string|callable $action): self
    {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * Register a PUT route.
     *
     * @param  string          $uri    URI pattern
     * @param  string|callable $action Controller@method or closure
     * @return self
     */
    public function put(string $uri, string|callable $action): self
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Register a PATCH route.
     *
     * @param  string          $uri    URI pattern
     * @param  string|callable $action Controller@method or closure
     * @return self
     */
    public function patch(string $uri, string|callable $action): self
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    /**
     * Register a DELETE route.
     *
     * @param  string          $uri    URI pattern
     * @param  string|callable $action Controller@method or closure
     * @return self
     */
    public function delete(string $uri, string|callable $action): self
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Register a route that responds to multiple HTTP methods.
     *
     * @param  array           $methods HTTP methods
     * @param  string          $uri     URI pattern
     * @param  string|callable $action  Controller@method or closure
     * @return self
     */
    public function match(array $methods, string $uri, string|callable $action): self
    {
        foreach ($methods as $method) {
            $this->addRoute(strtoupper($method), $uri, $action);
        }
        return $this;
    }

    /**
     * Register a route that responds to all HTTP methods.
     *
     * @param  string          $uri    URI pattern
     * @param  string|callable $action Controller@method or closure
     * @return self
     */
    public function any(string $uri, string|callable $action): self
    {
        foreach (array_keys($this->routes) as $method) {
            $this->addRoute($method, $uri, $action);
        }
        return $this;
    }

    /**
     * Assign a name to the last registered route.
     *
     * @param  string $name Route name
     * @return self
     */
    public function name(string $name): self
    {
        // Find the last registered route across all methods
        foreach (array_reverse(array_keys($this->routes)) as $method) {
            $routes = &$this->routes[$method];
            if (!empty($routes)) {
                $lastKey = array_key_last($routes);
                $routes[$lastKey]['name'] = $name;
                $this->namedRoutes[$name] = $routes[$lastKey]['uri'];
                break;
            }
        }
        return $this;
    }

    /**
     * Attach middleware to the last registered route.
     *
     * @param  string|array $middleware Middleware class name(s)
     * @return self
     */
    public function middleware(string|array $middleware): self
    {
        $middleware = is_array($middleware) ? $middleware : [$middleware];

        foreach (array_keys($this->routes) as $method) {
            $routes = &$this->routes[$method];
            if (!empty($routes)) {
                $lastKey = array_key_last($routes);
                $routes[$lastKey]['middleware'] = array_merge(
                    $routes[$lastKey]['middleware'] ?? [],
                    $middleware
                );
            }
        }
        return $this;
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param  array    $attributes Group attributes (prefix, middleware)
     * @param  callable $callback   Route definitions
     * @return void
     */
    public function group(array $attributes, callable $callback): void
    {
        // Push group prefix
        if (isset($attributes['prefix'])) {
            $this->groupStack[] = '/' . trim($attributes['prefix'], '/');
        }

        // Push group middleware
        if (isset($attributes['middleware'])) {
            $mw = is_array($attributes['middleware']) ? $attributes['middleware'] : [$attributes['middleware']];
            $this->groupMiddleware = array_merge($this->groupMiddleware, $mw);
        }

        // Execute route definitions
        $callback($this);

        // Pop group prefix
        if (isset($attributes['prefix'])) {
            array_pop($this->groupStack);
        }

        // Pop group middleware
        if (isset($attributes['middleware'])) {
            $mw = is_array($attributes['middleware']) ? $attributes['middleware'] : [$attributes['middleware']];
            $this->groupMiddleware = array_diff($this->groupMiddleware, $mw);
        }
    }

    /**
     * Register a resource controller (CRUD routes).
     *
     * Creates standard RESTful routes:
     * - GET    /resource          → index
     * - GET    /resource/create   → create
     * - POST   /resource          → store
     * - GET    /resource/{id}     → show
     * - GET    /resource/{id}/edit → edit
     * - PUT    /resource/{id}     → update
     * - DELETE /resource/{id}     → destroy
     *
     * @param  string $uri        Resource URI
     * @param  string $controller Controller class name
     * @return void
     */
    public function resource(string $uri, string $controller): void
    {
        $this->get($uri, "{$controller}@index");
        $this->get("{$uri}/create", "{$controller}@create");
        $this->post($uri, "{$controller}@store");
        $this->get("{$uri}/{id}", "{$controller}@show");
        $this->get("{$uri}/{id}/edit", "{$controller}@edit");
        $this->put("{$uri}/{id}", "{$controller}@update");
        $this->delete("{$uri}/{id}", "{$controller}@destroy");
    }

    // ========================================================
    // ROUTE RESOLUTION
    // ========================================================

    /**
     * Dispatch the current request to the matching route.
     *
     * @param  Request $request The current HTTP request
     * @return void
     */
    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri    = $request->uri();

        // Normalize URI
        $uri = '/' . trim($uri, '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        // Search registered routes for the current method
        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route) {
            $params = $this->matchRoute($route['pattern'], $uri);

            if ($params !== false) {
                // Execute middleware pipeline
                $middleware = $route['middleware'] ?? [];
                $this->runMiddleware($middleware, $request, function () use ($route, $params, $request) {
                    $this->executeAction($route['action'], $params, $request);
                });
                return;
            }
        }

        // No route matched — 404
        $this->handleNotFound($request);
    }

    /**
     * Match a route pattern against a URI.
     *
     * @param  string $pattern Route regex pattern
     * @param  string $uri     Request URI
     * @return array|false     Matched parameters or false
     */
    private function matchRoute(string $pattern, string $uri): array|false
    {
        if (preg_match($pattern, $uri, $matches)) {
            // Filter to named parameters only
            return array_filter($matches, fn($key) => !is_numeric($key), ARRAY_FILTER_USE_KEY);
        }
        return false;
    }

    /**
     * Execute a route action (controller method or closure).
     *
     * @param  string|callable $action Controller@method string or closure
     * @param  array           $params Route parameters
     * @param  Request         $request Current request
     * @return void
     */
    private function executeAction(string|callable $action, array $params, Request $request): void
    {
        if (is_callable($action)) {
            // Closure route
            call_user_func_array($action, array_merge([$request], array_values($params)));
            return;
        }

        // Controller@method string
        if (is_string($action) && str_contains($action, '@')) {
            [$controllerClass, $method] = explode('@', $action, 2);

            // Resolve the full controller class name
            if (!str_starts_with($controllerClass, 'App\\')) {
                $controllerClass = 'App\\Controllers\\' . $controllerClass;
            }

            if (!class_exists($controllerClass)) {
                throw new \RuntimeException("Controller [{$controllerClass}] not found.");
            }

            $controller = new $controllerClass();

            if (!method_exists($controller, $method)) {
                throw new \RuntimeException("Method [{$method}] not found in [{$controllerClass}].");
            }

            call_user_func_array([$controller, $method], array_merge([$request], array_values($params)));
            return;
        }

        throw new \RuntimeException("Invalid route action.");
    }

    /**
     * Run the middleware pipeline.
     *
     * @param  array    $middleware Middleware class names
     * @param  Request  $request   Current request
     * @param  callable $target    Final action to execute
     * @return void
     */
    private function runMiddleware(array $middleware, Request $request, callable $target): void
    {
        if (empty($middleware)) {
            $target();
            return;
        }

        $middlewareClass = array_shift($middleware);

        if (!class_exists($middlewareClass)) {
            throw new \RuntimeException("Middleware [{$middlewareClass}] not found.");
        }

        $instance = new $middlewareClass();
        $instance->handle($request, function () use ($middleware, $request, $target) {
            $this->runMiddleware($middleware, $request, $target);
        });
    }

    /**
     * Handle 404 Not Found.
     *
     * @param  Request $request Current request
     * @return void
     */
    private function handleNotFound(Request $request): void
    {
        if ($request->expectsJson() || $request->isApi()) {
            Response::error('Resource not found', 404);
        } else {
            abort(404, 'The page you are looking for could not be found.');
        }
    }

    // ========================================================
    // INTERNAL
    // ========================================================

    /**
     * Add a route to the registry.
     *
     * @param  string          $method HTTP method
     * @param  string          $uri    URI pattern
     * @param  string|callable $action Handler
     * @return self
     */
    private function addRoute(string $method, string $uri, string|callable $action): self
    {
        // Apply group prefix
        $prefix = implode('', $this->groupStack);
        $fullUri = $prefix . '/' . trim($uri, '/');
        $fullUri = '/' . trim($fullUri, '/');

        // Convert URI pattern to regex
        $pattern = $this->compilePattern($fullUri);

        $this->routes[$method][] = [
            'uri'        => $fullUri,
            'pattern'    => $pattern,
            'action'     => $action,
            'middleware'  => $this->groupMiddleware,
            'name'       => null,
        ];

        return $this;
    }

    /**
     * Compile a URI pattern into a regex.
     *
     * Converts {param} segments into named capture groups.
     *
     * @param  string $uri URI pattern
     * @return string      Regex pattern
     */
    private function compilePattern(string $uri): string
    {
        // Escape forward slashes
        $pattern = preg_replace_callback('/\{(\w+)\}/', function ($matches) {
            $paramName = $matches[1];
            return "(?P<{$paramName}>[^/]+)";
        }, $uri);

        return '#^' . $pattern . '$#';
    }

    /**
     * Generate a URL for a named route.
     *
     * @param  string $name   Route name
     * @param  array  $params Route parameters
     * @return string         Generated URL
     */
    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \RuntimeException("Route [{$name}] not found.");
        }

        $uri = $this->namedRoutes[$name];

        // Replace parameters
        foreach ($params as $key => $value) {
            $uri = str_replace("{{$key}}", $value, $uri);
        }

        return url($uri);
    }

    /**
     * Get all registered routes (for debugging).
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Prevent cloning of singleton.
     */
    private function __clone() {}
}
