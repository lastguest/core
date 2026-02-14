<?php

/**
 * View\Scope
 *
 * Template execution scope providing variable access, template inheritance,
 * output escaping, stacks, and custom helper support.
 *
 * Replaces the old PHPContext with a full-featured template context.
 *
 * @package core
 * @author stefano.azzolini@caffeina.com
 * @copyright Caffeina srl - 2015 - http://caffeina.com
 */

namespace View;

class Scope {

    protected $data       = [];
    protected $parent     = null;
    protected $sections   = [];
    protected $openBuffer = null;

    protected static $stacks  = [];
    protected static $helpers = [];

    /**
     * Create a new template scope.
     *
     * @param array  $data     The view data (merged globals + local)
     * @param array  $sections Sections inherited from a child template
     * @param array  $stacks   Stacks inherited from a child template
     */
    public function __construct(array $data = [], array $sections = []) {
        $this->data     = $data;
        $this->sections = $sections;
    }

    // ─── Variable Access ──────────────────────────────────────────────

    /**
     * Access a view variable with automatic HTML escaping.
     * Returns an empty string for undefined keys.
     *
     * @param  string $key
     * @return mixed  Escaped string for string values, raw value otherwise
     */
    public function __get($key) {
        $val = $this->data[$key] ?? '';
        return is_string($val) ? htmlspecialchars($val, ENT_QUOTES, 'UTF-8') : $val;
    }

    /**
     * Access a view variable without escaping.
     *
     * @param  string $key
     * @return mixed
     */
    public function raw($key) {
        return $this->data[$key] ?? '';
    }

    /**
     * Manually escape a value with a named strategy.
     * Strategies are extensible via Filter::add('core.view.escape.{strategy}', ...).
     *
     * @param  mixed  $value
     * @param  string $strategy  One of: html, url, js, css
     * @return string
     */
    public function e($value, $strategy = 'html') {
        // Compute built-in default, then let Filter override if registered
        switch ($strategy) {
            case 'url':
                $default = rawurlencode($value);
                break;
            case 'js':
                $default = json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                break;
            case 'css':
                $default = preg_replace_callback('/[^a-zA-Z0-9]/', function ($m) {
                    return '\\' . dechex(ord($m[0])) . ' ';
                }, $value);
                break;
            case 'html':
            default:
                $default = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                break;
        }
        return \Filter::with("core.view.escape.{$strategy}", $default);
    }

    /**
     * Check if a view variable is defined and non-empty.
     *
     * @param  string $key
     * @return bool
     */
    public function __isset($key) {
        return isset($this->data[$key]);
    }

    /**
     * No-op for unset.
     */
    public function __unset($key) {}

    // ─── Template Inheritance ─────────────────────────────────────────

    /**
     * Declare that this template extends a parent layout.
     * The parent template will be rendered after the child finishes,
     * with all captured sections available via yield().
     *
     * @param string $layout  The parent template path
     */
    public function extend($layout) {
        $this->parent = $layout;
    }

    /**
     * Begin capturing a named section.
     * Must be paired with endSection().
     *
     * @param string $name
     */
    public function section($name) {
        $this->openBuffer = $name;
        ob_start();
    }

    /**
     * End the current section capture.
     */
    public function endSection() {
        if ($this->openBuffer !== null) {
            $this->sections[$this->openBuffer] = ob_get_clean();
            $this->openBuffer = null;
        }
    }

    /**
     * Output a named section's content, or a default if the section was not defined.
     *
     * @param  string $name
     * @param  string $default  Fallback content if section is undefined
     * @return string
     */
    public function yield($name, $default = '') {
        return $this->sections[$name] ?? $default;
    }

    /**
     * Returns the parent layout path, or null if none was set.
     *
     * @return string|null
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * Returns all captured sections.
     *
     * @return array
     */
    public function getSections() {
        return $this->sections;
    }

    // ─── Stacks ───────────────────────────────────────────────────────

    /**
     * Begin pushing content onto a named stack.
     * Stacks are global across all templates in a render cycle.
     *
     * @param string $name
     */
    public function push($name) {
        $this->openBuffer = '_stack:' . $name;
        ob_start();
    }

    /**
     * End the current push capture and append to the stack.
     */
    public function endPush() {
        if ($this->openBuffer !== null && strpos($this->openBuffer, '_stack:') === 0) {
            $name = substr($this->openBuffer, 7);
            static::$stacks[$name][] = ob_get_clean();
            $this->openBuffer = null;
        }
    }

    /**
     * Begin prepending content onto a named stack.
     *
     * @param string $name
     */
    public function prepend($name) {
        $this->openBuffer = '_prepend:' . $name;
        ob_start();
    }

    /**
     * End the current prepend capture and prepend to the stack.
     */
    public function endPrepend() {
        if ($this->openBuffer !== null && strpos($this->openBuffer, '_prepend:') === 0) {
            $name = substr($this->openBuffer, 9);
            array_unshift(static::$stacks[$name], ob_get_clean());
            $this->openBuffer = null;
        }
    }

    /**
     * Output all content pushed onto a named stack.
     *
     * @param  string $name
     * @return string
     */
    public function stack($name) {
        return implode("\n", static::$stacks[$name] ?? []);
    }

    /**
     * Reset all stacks. Called at the start of a top-level render.
     */
    public static function resetStacks() {
        static::$stacks = [];
    }

    // ─── Includes & Embeds ────────────────────────────────────────────

    /**
     * Include a sub-template with isolated data (only passed data + globals).
     * The parent scope's data is NOT inherited.
     *
     * @param  string $template
     * @param  array  $data
     * @return string
     */
    public function include($template, $data = []) {
        return (string) \View::from($template, $data);
    }

    /**
     * Embed a sub-template inheriting the current scope's data, merged with overrides.
     *
     * @param  string $template
     * @param  array  $data      Overrides merged on top of parent data
     * @return string
     */
    public function embed($template, $data = []) {
        return (string) \View::from($template, array_merge($this->data, $data));
    }

    // ─── Custom Helpers ───────────────────────────────────────────────

    /**
     * Register a custom helper function available in all templates.
     *
     * @param string   $name
     * @param callable $fn
     */
    public static function addHelper($name, callable $fn) {
        static::$helpers[$name] = $fn;
    }

    /**
     * Register multiple helpers at once.
     *
     * @param array $helpers  Map of name => callable
     */
    public static function addHelpers(array $helpers) {
        foreach ($helpers as $name => $fn) {
            static::addHelper($name, $fn);
        }
    }

    /**
     * Dispatch calls to registered helpers.
     *
     * @param  string $name
     * @param  array  $args
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($name, $args) {
        if (isset(static::$helpers[$name])) {
            return call_user_func_array(
                \Closure::bind(static::$helpers[$name], $this, static::class),
                $args
            );
        }
        throw new \BadMethodCallException("Unknown template helper: {$name}");
    }

    /**
     * Returns all registered helpers.
     *
     * @return array
     */
    public static function getHelpers() {
        return static::$helpers;
    }

    /**
     * Clear all registered helpers (useful for testing).
     */
    public static function clearHelpers() {
        static::$helpers = [];
    }
}
