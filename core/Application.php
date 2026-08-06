<?php
/**
 * ============================================================
 * Nadics LectureHub — Application Bootstrap
 * ============================================================
 *
 * The central bootstrap class that initializes the entire
 * application: loads environment, starts sessions, registers
 * error handling, loads routes, and dispatches the request.
 *
 * @package    NadicsLectureHub
 * @subpackage Core
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace Core;

class Application
{
    /**
     * The application base path.
     *
     * @var string
     */
    private string $basePath;

    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Create a new Application instance.
     *
     * @param string $basePath The root directory of the application
     */
    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/\\');
        self::$instance = $this;
    }

    /**
     * Get the Application instance.
     *
     * @return self|null
     */
    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    /**
     * Get the application base path.
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Boot the application.
     *
     * Performs all initialization steps in the correct order:
     * 1. Load environment variables
     * 2. Set PHP configuration
     * 3. Register autoloader
     * 4. Set error handling
     * 5. Start session
     * 6. Create required directories
     * 7. Share global view data
     *
     * @return void
     */
    public function boot(): void
    {
        // Step 1: Environment is already loaded by helpers.php env() function
        // (lazy-loaded on first call)

        // Step 2: PHP Configuration
        $this->configurePHP();

        // Step 3: Register PSR-4 autoloader
        $this->registerAutoloader();

        // Step 4: Error handling
        $this->registerErrorHandlers();

        // Step 5: Start session
        Session::start();

        // Step 6: Ensure required directories exist
        $this->ensureDirectories();

        // Step 7: Share global data with views
        $this->shareGlobalViewData();
    }

    /**
     * Run the application — dispatch the HTTP request.
     *
     * @return void
     */
    public function run(): void
    {
        $router  = Router::getInstance();
        $request = Request::getInstance();

        // Load route definitions
        $this->loadRoutes($router);

        // Dispatch the request
        $router->dispatch($request);
    }

    /**
     * Configure PHP settings based on environment.
     *
     * @return void
     */
    private function configurePHP(): void
    {
        // Timezone
        date_default_timezone_set(env('APP_TIMEZONE', 'Africa/Lagos'));

        // Error display (show in dev, hide in production)
        $debug = env('APP_DEBUG', false);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('display_startup_errors', $debug ? '1' : '0');
        error_reporting($debug ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_STRICT);

        // Character encoding
        mb_internal_encoding('UTF-8');

        // Upload limits
        $maxUpload = env('STORAGE_MAX_UPLOAD', 104857600);
        $maxUploadMB = ceil($maxUpload / (1024 * 1024));
        ini_set('upload_max_filesize', $maxUploadMB . 'M');
        ini_set('post_max_size', ($maxUploadMB + 10) . 'M');
        ini_set('max_execution_time', '300');
        ini_set('max_input_time', '300');
    }

    /**
     * Register a PSR-4 compatible autoloader.
     *
     * Maps namespace prefixes to directories:
     *   App\    => app/
     *   Core\   => core/
     *
     * @return void
     */
    private function registerAutoloader(): void
    {
        spl_autoload_register(function (string $class) {
            $prefixes = [
                'App\\'      => $this->basePath . '/app/',
                'Core\\'     => $this->basePath . '/core/',
                'Database\\' => $this->basePath . '/database/',
            ];

            foreach ($prefixes as $prefix => $baseDir) {
                if (str_starts_with($class, $prefix)) {
                    $relativeClass = substr($class, strlen($prefix));
                    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

                    if (file_exists($file)) {
                        require_once $file;
                        return;
                    }
                }
            }
        });
    }

    /**
     * Register global error and exception handlers.
     *
     * @return void
     */
    private function registerErrorHandlers(): void
    {
        // Exception handler
        set_exception_handler(function (\Throwable $e) {
            Logger::getInstance()->critical('Unhandled exception', [
                'type'    => get_class($e),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            if (env('APP_DEBUG', false)) {
                $this->renderDebugError($e);
            } else {
                abort(500, 'An unexpected error occurred. Please try again later.');
            }
        });

        // Error handler — convert errors to exceptions
        set_error_handler(function (int $severity, string $message, string $file, int $line) {
            // Respect error_reporting setting
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        // Shutdown handler — catch fatal errors
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                Logger::getInstance()->emergency('Fatal error', [
                    'message' => $error['message'],
                    'file'    => $error['file'],
                    'line'    => $error['line'],
                ]);

                if (!env('APP_DEBUG', false)) {
                    abort(500, 'A fatal error occurred.');
                }
            }
        });
    }

    /**
     * Render a debug error page with full stack trace.
     *
     * Only displayed when APP_DEBUG is true.
     *
     * @param  \Throwable $e The exception
     * @return void
     */
    private function renderDebugError(\Throwable $e): void
    {
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><title>Error</title>';
        echo '<style>body{font-family:"Segoe UI",system-ui,sans-serif;background:#0F172A;color:#E2E8F0;margin:0;padding:40px}';
        echo '.error-box{max-width:900px;margin:0 auto;background:#1E293B;border-radius:12px;padding:30px;border:1px solid #334155}';
        echo 'h1{color:#EF4444;margin-top:0}h2{color:#F59E0B;font-size:16px}';
        echo '.trace{background:#0F172A;border-radius:8px;padding:20px;font-family:monospace;font-size:13px;overflow-x:auto;white-space:pre-wrap;color:#94A3B8;margin-top:15px;border:1px solid #334155}';
        echo '.file{color:#06B6D4}.line{color:#F59E0B}.msg{color:#EF4444;font-size:18px;font-weight:600}';
        echo '</style></head><body>';
        echo '<div class="error-box">';
        echo '<h1>⚠️ ' . htmlspecialchars(get_class($e)) . '</h1>';
        echo '<p class="msg">' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<h2>📁 <span class="file">' . htmlspecialchars($e->getFile()) . '</span> : <span class="line">Line ' . $e->getLine() . '</span></h2>';
        echo '<div class="trace">' . htmlspecialchars($e->getTraceAsString()) . '</div>';
        echo '</div></body></html>';
        exit(1);
    }

    /**
     * Ensure required storage directories exist.
     *
     * @return void
     */
    private function ensureDirectories(): void
    {
        $directories = [
            $this->basePath . '/storage/logs',
            $this->basePath . '/storage/cache',
            $this->basePath . '/storage/sessions',
            $this->basePath . '/storage/temp',
            $this->basePath . '/public/uploads/profiles',
            $this->basePath . '/public/uploads/lectures',
            $this->basePath . '/public/uploads/materials',
            $this->basePath . '/public/uploads/temp',
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                // Suppress permission errors on shared hosting — directories should
                // already exist with correct permissions set via cPanel/FTP/SSH.
                @mkdir($dir, 0755, true);
            }

            // Protect directory with .htaccess if it doesn't already have one
            $htaccess = $dir . '/.htaccess';
            if (is_dir($dir) && !file_exists($htaccess)) {
                @file_put_contents($htaccess, "Options -Indexes\nDeny from all\n");
            }
        }
    }

    /**
     * Share global data with all views.
     *
     * @return void
     */
    private function shareGlobalViewData(): void
    {
        View::share('app_name', env('APP_NAME', 'Nadics LectureHub'));
        View::share('app_version', env('APP_VERSION', '1.0.0'));
        View::share('app_url', env('APP_URL', ''));
    }

    /**
     * Load route definitions from route files.
     *
     * @param  Router $router The router instance
     * @return void
     */
    private function loadRoutes(Router $router): void
    {
        // Load web routes
        $webRoutes = $this->basePath . '/routes/web.php';
        if (file_exists($webRoutes)) {
            $routerRef = $router;
            require $webRoutes;
        }

        // Load API routes
        $apiRoutes = $this->basePath . '/routes/api.php';
        if (file_exists($apiRoutes)) {
            $router->group(['prefix' => 'api/v1'], function ($router) use ($apiRoutes) {
                require $apiRoutes;
            });
        }
    }
}
