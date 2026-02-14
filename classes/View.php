<?php

/**
 * View
 *
 * View template handling class.
 *
 * Provides a facade to the underlying template engine adapter with support for:
 *   - Template inheritance (layouts with extend/section/yield)
 *   - Auto-escaped output (safe by default)
 *   - Stacks for CSS/JS asset injection
 *   - View composers for automatic data injection
 *   - Render caching via the Cache component
 *   - Custom template helpers
 *   - Isolated includes and inheriting embeds
 *
 * @package core
 * @author stefano.azzolini@caffeinalab.com
 * @copyright Caffeina srl - 2015 - http://caffeina.it
 */

class View {
    use Module;

    protected static $handler = null;
    protected $options = [
      'template'  => '',
      'data'      => [],
      'cache_ttl' => 0,
    ];

    /**
     * Construct a new view based on the passed template.
     *
     * @param  mixed $template The template path or an array of fallback paths.
     * @throws Exception If no template in the list exists.
     */
    public function __construct($template) {
      foreach ((array)$template as $templ) {
        if (static::$handler->exists($templ))
          return $this->options['template'] = $templ;
      }
      throw new Exception("[Core.View] Template not found.");
    }

    /**
     * Load a Template Handler.
     *
     * @param  View\Adapter $handler The template handler adapter instance
     */
    public static function using(View\Adapter $handler) {
      static::$handler = $handler;
    }

    /**
     * View factory method, can optionally pass data to pre-init the view.
     *
     * @param  string $template The template path
     * @param  array  $data     The key-value map of data to pass to the view
     * @return View
     */
    public static function from($template, $data = null) {
      $view = new self($template);
      return $data ? $view->with($data) : $view;
    }

    /**
     * Assigns data to the view. Last call wins (later data overrides earlier).
     *
     * @param  array $data  The key-value map of data to pass to the view
     * @return View  Chainable
     */
    public function with($data) {
      if ($data) {
        $this->options['data'] = array_merge($this->options['data'], $data);
      }
      return $this;
    }

    /**
     * Enable render caching for this view instance.
     *
     * Requires the Cache component. The cache key is derived from the
     * template name and a hash of the view data.
     *
     * @param  int  $ttl  Cache time-to-live in seconds
     * @return View Chainable
     */
    public function cache($ttl) {
      $this->options['cache_ttl'] = (int)$ttl;
      return $this;
    }

    /**
     * Render view when casted to a string.
     *
     * If caching is enabled, the rendered output is stored/retrieved via Cache.
     *
     * @return string The rendered view
     */
    public function __toString() {
      $rendered = null;
      $ttl = $this->options['cache_ttl'];

      if ($ttl > 0 && class_exists('Cache', false)) {
        $cacheKey = 'view:' . $this->options['template'] . ':' . md5(serialize($this->options['data']));
        $template = $this->options['template'];
        $data     = $this->options['data'];
        $handler  = static::$handler;
        $rendered = Cache::get($cacheKey, function () use ($handler, $template, $data) {
          return $handler->render($template, $data);
        }, $ttl);
      } else {
        $rendered = static::$handler->render($this->options['template'], $this->options['data']);
      }

      return Filter::with('core.view', $rendered);
    }

    /**
     * Returns the handler instance.
     *
     * @return View\Adapter
     */
    public static function & handler() {
      return static::$handler;
    }

    /**
     * Check if a template exists.
     *
     * @param  string $templatePath
     * @return bool
     */
    public static function exists($templatePath) {
      return static::$handler->exists($templatePath);
    }

    /**
     * Register a custom template helper available as $this->name() in templates.
     *
     * @param string   $name
     * @param callable $fn
     */
    public static function helper($name, callable $fn) {
      static::$handler->addHelper($name, $fn);
    }

    /**
     * Register multiple template helpers at once.
     *
     * @param array $helpers  Map of name => callable
     */
    public static function helpers(array $helpers) {
      static::$handler->addHelpers($helpers);
    }

    /**
     * Register a view composer for automatic data injection.
     *
     * Composers run before a template renders. Supports wildcard patterns.
     *
     * @param string   $pattern   Template name or glob pattern (e.g. "admin/*")
     * @param callable $callback  Receives &$data by reference
     */
    public static function composer($pattern, callable $callback) {
      static::$handler->composer($pattern, $callback);
    }

    /**
     * Propagate the call to the handler.
     */
    public function __call($n, $p) {
      return call_user_func_array([static::$handler, $n], $p);
    }

    /**
     * Propagate the static call to the handler.
     */
    public static function __callStatic($n, $p) {
      return forward_static_call_array([static::$handler, $n], $p);
    }

}
