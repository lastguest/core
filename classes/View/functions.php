<?php

/**
 * View Template Functions
 *
 * Plain global functions available inside any PHP template.
 * Each function delegates to the active View\Scope instance.
 *
 * Usage in templates:
 *   <?php extend('layouts/main') ?>
 *   <?php section('content') ?>...<?php endSection() ?>
 *   <?= yields('content') ?>
 *   <?= e($value, 'url') ?>
 *   <?= partial('components/card', ['title' => 'Hi']) ?>
 *
 * Note: `yield` and `include` are PHP reserved words, so we use
 * `yields()` and `partial()` as the global function names.
 * The $this-> methods ($this->yield(), $this->include()) still work.
 *
 * @package core
 * @author stefano.azzolini@caffeina.com
 * @copyright Caffeina srl - 2015 - http://caffeina.com
 */

/**
 * Escape a value using a named strategy.
 *
 * @param  mixed  $value
 * @param  string $strategy  html|url|js|css
 * @return string
 */
function e($value, $strategy = 'html') {
    return View\Scope::$current->e($value, $strategy);
}

/**
 * Access a view variable without escaping.
 *
 * @param  string $key
 * @return mixed
 */
function raw($key) {
    return View\Scope::$current->raw($key);
}

/**
 * Declare that this template extends a parent layout.
 *
 * @param string $layout
 */
function extend($layout) {
    View\Scope::$current->extend($layout);
}

/**
 * Begin capturing a named section.
 *
 * @param string $name
 */
function section($name) {
    View\Scope::$current->section($name);
}

/**
 * End the current section capture.
 */
function endSection() {
    View\Scope::$current->endSection();
}

/**
 * Output a named section, or a default value.
 * Named `yields` because `yield` is a PHP reserved word.
 *
 * @param  string $name
 * @param  string $default
 * @return string
 */
function yields($name, $default = '') {
    return View\Scope::$current->yield($name, $default);
}

/**
 * Begin pushing content onto a named stack.
 *
 * @param string $name
 */
function push($name) {
    View\Scope::$current->push($name);
}

/**
 * End the current push capture.
 */
function endPush() {
    View\Scope::$current->endPush();
}

/**
 * Begin prepending content onto a named stack.
 *
 * @param string $name
 */
function prepend($name) {
    View\Scope::$current->prepend($name);
}

/**
 * End the current prepend capture.
 */
function endPrepend() {
    View\Scope::$current->endPrepend();
}

/**
 * Output all content pushed onto a named stack.
 *
 * @param  string $name
 * @return string
 */
function stack($name) {
    return View\Scope::$current->stack($name);
}

/**
 * Include a sub-template with isolated data.
 * Named `partial` because `include` is a PHP reserved word.
 *
 * @param  string $template
 * @param  array  $data
 * @return string
 */
function partial($template, $data = []) {
    return View\Scope::$current->include($template, $data);
}

/**
 * Embed a sub-template inheriting the current scope's data.
 *
 * @param  string $template
 * @param  array  $data
 * @return string
 */
function embed($template, $data = []) {
    return View\Scope::$current->embed($template, $data);
}
