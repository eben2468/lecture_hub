<?php
/**
 * ============================================================
 * Nadics LectureHub — Template View Engine
 * ============================================================
 *
 * Renders PHP view templates with layout inheritance,
 * section management, component inclusion, and auto-escaping.
 *
 * Supports:
 * - Layout inheritance (extending a parent layout)
 * - Named sections (yield/section/endSection)
 * - Component includes
 * - Data passing to views
 * - Auto-escaping via e() helper
 *
 * Usage:
 *   $html = view('dashboard.index', ['user' => $user]);
 *
 * @package    NadicsLectureHub
 * @subpackage Core
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace Core;

class View
{
    /**
     * Base directory for view templates.
     *
     * @var string
     */
    private string $viewPath;

    /**
     * Current layout to extend.
     *
     * @var string|null
     */
    private ?string $layout = null;

    /**
     * Named sections content.
     *
     * @var array<string, string>
     */
    private array $sections = [];

    /**
     * Stack for tracking open sections.
     *
     * @var array
     */
    private array $sectionStack = [];

    /**
     * Data passed to the view.
     *
     * @var array
     */
    private array $data = [];

    /**
     * Shared data available to all views.
     *
     * @var array
     */
    private static array $sharedData = [];

    /**
     * Create a new View instance.
     */
    public function __construct()
    {
        $this->viewPath = dirname(__DIR__) . '/resources/views';
    }

    /**
     * Share data with all views globally.
     *
     * @param  string $key   Variable name
     * @param  mixed  $value Variable value
     * @return void
     */
    public static function share(string $key, mixed $value): void
    {
        self::$sharedData[$key] = $value;
    }

    /**
     * Render a view template.
     *
     * Converts dot-notation view name to a file path,
     * extracts data variables, captures output, and
     * optionally wraps in a layout.
     *
     * @param  string $name View name (dot notation: 'dashboard.index')
     * @param  array  $data Variables to pass to the view
     * @return string       Rendered HTML
     * @throws \RuntimeException If view file not found
     */
    public function render(string $name, array $data = []): string
    {
        $this->data   = array_merge(self::$sharedData, $data);
        $this->layout = null;
        $this->sections = [];

        $filePath = $this->resolveViewPath($name);

        if (!file_exists($filePath)) {
            throw new \RuntimeException("View [{$name}] not found at: {$filePath}");
        }

        // Render the view content
        $content = $this->renderFile($filePath, $this->data);

        // If a layout was specified, render it with sections
        if ($this->layout !== null) {
            $layoutPath = $this->resolveViewPath($this->layout);

            if (!file_exists($layoutPath)) {
                throw new \RuntimeException("Layout [{$this->layout}] not found at: {$layoutPath}");
            }

            // Set the main content as the 'content' section
            if (!isset($this->sections['content'])) {
                $this->sections['content'] = $content;
            }

            $content = $this->renderFile($layoutPath, array_merge($this->data, [
                '__sections' => $this->sections,
            ]));
        }

        return $content;
    }

    /**
     * Render a file and capture its output.
     *
     * @param  string $filePath Absolute path to the template file
     * @param  array  $data     Variables to extract
     * @return string           Captured output
     */
    private function renderFile(string $filePath, array $data): string
    {
        // Make the view engine available in templates as $__view
        $data['__view'] = $this;

        // Extract variables into the local scope
        extract($data, EXTR_SKIP);

        ob_start();
        require $filePath;
        return ob_get_clean();
    }

    /**
     * Convert a dot-notation view name to a file path.
     *
     * @param  string $name View name
     * @return string       Absolute file path
     */
    private function resolveViewPath(string $name): string
    {
        $relativePath = str_replace('.', DIRECTORY_SEPARATOR, $name);
        return $this->viewPath . DIRECTORY_SEPARATOR . $relativePath . '.php';
    }

    // ========================================================
    // LAYOUT & SECTION DIRECTIVES
    // ========================================================

    /**
     * Extend a layout from the current view.
     *
     * Call this at the top of a view file:
     *   $__view->extends('layouts.app');
     *
     * @param  string $layout Layout view name (dot notation)
     * @return void
     */
    public function extends(string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * Alias for extends().
     *
     * @param  string $layout
     * @return void
     */
    public function layout(string $layout): void
    {
        $this->extends($layout);
    }

    /**
     * Start a named section.
     *
     * @param  string $name Section name
     * @return void
     */
    public function section(string $name): void
    {
        $this->sectionStack[] = $name;
        ob_start();
    }

    /**
     * End the current section.
     *
     * @return void
     */
    public function endSection(): void
    {
        if (empty($this->sectionStack)) {
            throw new \RuntimeException('Cannot end a section without starting one.');
        }

        $name = array_pop($this->sectionStack);
        $this->sections[$name] = ob_get_clean();
    }

    /**
     * Output the content of a named section.
     *
     * Used in layout files:
     *   <?= $__view->yield('content') ?>
     *
     * @param  string $name    Section name
     * @param  string $default Default content if section is not defined
     * @return string          Section content
     */
    public function yield(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    /**
     * Include a partial/component view.
     *
     * @param  string $name Component view name (dot notation)
     * @param  array  $data Additional data to pass
     * @return string       Rendered component HTML
     */
    public function include(string $name, array $data = []): string
    {
        $filePath = $this->resolveViewPath($name);

        if (!file_exists($filePath)) {
            throw new \RuntimeException("Component [{$name}] not found at: {$filePath}");
        }

        $mergedData = array_merge($this->data, $data, ['__view' => $this]);
        return $this->renderFile($filePath, $mergedData);
    }

    /**
     * Render a component inline (echoes directly).
     *
     * @param  string $name Component view name
     * @param  array  $data Additional data
     * @return void
     */
    public function component(string $name, array $data = []): void
    {
        echo $this->include($name, $data);
    }

    /**
     * Check if a section has been defined.
     *
     * @param  string $name Section name
     * @return bool
     */
    public function hasSection(string $name): bool
    {
        return isset($this->sections[$name]);
    }

    /**
     * Get the view path.
     *
     * @return string
     */
    public function getViewPath(): string
    {
        return $this->viewPath;
    }
}
