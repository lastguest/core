<?php

/**
 * View\Adapter
 *
 * Core\View\Adapter Interface.
 *
 * All template engine adapters must implement this contract.
 *
 * @package core
 * @author stefano.azzolini@caffeina.com
 * @copyright Caffeina srl - 2015 - http://caffeina.com
 */

namespace View;

interface Adapter {
    public function __construct($path = null, $options = []);
    public function render($template, $data = []);
    public static function exists($path);
    public static function addGlobal($key, $val);
    public static function addGlobals(array $defs);
    public static function addHelper($name, callable $fn);
    public static function addHelpers(array $helpers);
}
