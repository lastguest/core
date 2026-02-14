<?php

/**
 * View\PHP
 *
 * Core\View PHP template engine.
 *
 * Features:
 *   - Template inheritance (extend/section/yield)
 *   - Auto-escaped variable output
 *   - Stacks for asset injection (push/stack)
 *   - View composers via Event system
 *   - Isolated includes and inheriting embeds
 *   - Custom template helpers
 *
 * @package core
 * @author stefano.azzolini@caffeina.com
 * @copyright Caffeina srl - 2015 - http://caffeina.com
 */

namespace View;

class PHP implements Adapter {

    const EXTENSION = '.php';

    protected static $templatePath  = '',
                     $globals       = [],
                     $composers     = [];

    public function __construct($path = null, $options = []) {
        self::$templatePath = ($path ? rtrim($path, '/') : __DIR__) . '/';
    }

    /**
     * Check if a template file exists.
     *
     * @param  string $path  Template path (without extension)
     * @return bool
     */
    public static function exists($path) {
        return is_file(self::$templatePath . $path . static::EXTENSION);
    }

    /**
     * Register a global variable available in all templates.
     *
     * @param string $key
     * @param mixed  $val
     */
    public static function addGlobal($key, $val) {
        self::$globals[$key] = $val;
    }

    /**
     * Register multiple global variables.
     *
     * @param array $defs  Key-value pairs
     */
    public static function addGlobals(array $defs) {
        foreach ($defs as $key => $val) {
            self::$globals[$key] = $val;
        }
    }

    /**
     * Register a custom template helper.
     *
     * @param string   $name
     * @param callable $fn
     */
    public static function addHelper($name, callable $fn) {
        Scope::addHelper($name, $fn);
    }

    /**
     * Register multiple template helpers.
     *
     * @param array $helpers  Map of name => callable
     */
    public static function addHelpers(array $helpers) {
        Scope::addHelpers($helpers);
    }

    /**
     * Register a view composer callback for a template or pattern.
     *
     * Composers are called before a template renders, allowing automatic
     * injection of data. Supports wildcard patterns (e.g. "admin/*").
     *
     * @param string   $pattern   Template name or glob pattern
     * @param callable $callback  Receives &$data by reference
     */
    public static function composer($pattern, callable $callback) {
        self::$composers[] = [$pattern, $callback];
    }

    /**
     * Render a template with data.
     *
     * Handles template inheritance by executing child templates first,
     * then recursively rendering parent layouts with captured sections.
     *
     * @param  string $template  Template path (without extension)
     * @param  array  $data      View data
     * @param  array  $sections  Sections inherited from child template
     * @param  bool   $topLevel  Whether this is the top-level render call
     * @return string
     */
    public function render($template, $data = [], $sections = [], $topLevel = true) {

        $data = array_merge(self::$globals, $data);

        // Fire view composers (exact match and wildcard)
        foreach (self::$composers as list($pattern, $callback)) {
            if ($pattern === $template || fnmatch($pattern, $template)) {
                $callback($data);
            }
        }

        // Fire event-based composers for exact template match
        if (class_exists('Event', false)) {
            \Event::trigger("core.view.compose:{$template}", $data);
        }

        // Reset stacks at the start of a top-level render
        if ($topLevel) {
            Scope::resetStacks();
        }

        $scope = new Scope($data, $sections);

        $template_path = self::$templatePath . trim($template, '/') . static::EXTENSION;

        // Execute template in sandbox
        $sandbox = function () use ($template_path) {
            ob_start();
            include($template_path);
            return ob_get_clean();
        };

        $output = call_user_func($sandbox->bindTo($scope));

        // If the template declared a parent via extend(), render the parent
        // with all captured sections and stacks flowing upward.
        if ($parent = $scope->getParent()) {
            return $this->render(
                $parent,
                $data,
                array_merge($sections, $scope->getSections()),
                false
            );
        }

        return $output;
    }
}
