# View

The View component is Core's built-in PHP template engine. It provides a safe, powerful template layer using plain PHP files — no custom syntax to learn, full IDE support, and zero compilation overhead.

## Table of Contents

- [Quick Start](#quick-start)
- [Setup](#setup)
- [Rendering Templates](#rendering-templates)
- [Template Syntax](#template-syntax)
- [Variable Access & Auto-Escaping](#variable-access--auto-escaping)
- [Template Inheritance](#template-inheritance)
- [Sections & Yields](#sections--yields)
- [Stacks (CSS/JS Injection)](#stacks-cssjs-injection)
- [Includes & Embeds](#includes--embeds)
- [Custom Helpers](#custom-helpers)
- [View Composers](#view-composers)
- [Global Variables](#global-variables)
- [Template Caching](#template-caching)
- [Template Fallbacks](#template-fallbacks)
- [Escape Strategies](#escape-strategies)
- [Adapter Interface](#adapter-interface)
- [Integration with Routes](#integration-with-routes)
- [Full Example: Blog Application](#full-example-blog-application)

---

## Quick Start

```php
// 1. Point the engine at your templates directory
View::using(new View\PHP(__DIR__ . '/templates'));

// 2. Render a template
echo View::from('welcome', ['name' => 'World']);
```

`templates/welcome.php`:
```php
<h1>Hello, <?= $this->name ?>!</h1>
```

Output:
```html
<h1>Hello, World!</h1>
```

---

## Setup

Initialize the View engine with a template directory path:

```php
View::using(new View\PHP('/path/to/templates'));
```

All template paths are resolved relative to this root. Templates use the `.php` extension automatically.

---

## Rendering Templates

### Factory method (recommended)

```php
$view = View::from('page', ['title' => 'Home']);
echo $view;
```

### Constructor

```php
$view = new View('page');
$view->with(['title' => 'Home']);
echo $view;
```

### Chaining

```php
echo View::from('page')
    ->with(['title' => 'Home'])
    ->with(['user'  => $currentUser])
    ->cache(3600);
```

When `with()` is called multiple times, later calls override earlier values for the same key.

---

## Template Syntax

Inside templates, two equivalent APIs are available. Both styles can be mixed freely:

| Function syntax | OOP syntax (`$this->`) |
|-----------------|------------------------|
| `extend('layouts/main')` | `$this->extend('layouts/main')` |
| `section('content')` | `$this->section('content')` |
| `endSection()` | `$this->endSection()` |
| `yields('content')` | `$this->yield('content')` |
| `push('scripts')` | `$this->push('scripts')` |
| `endPush()` | `$this->endPush()` |
| `stack('scripts')` | `$this->stack('scripts')` |
| `raw('key')` | `$this->raw('key')` |
| `e($val, 'url')` | `$this->e($val, 'url')` |
| `partial('tpl', $data)` | `$this->include('tpl', $data)` |
| `embed('tpl', $data)` | `$this->embed('tpl', $data)` |
| `prepend('scripts')` | `$this->prepend('scripts')` |
| `endPrepend()` | `$this->endPrepend()` |

Two PHP reserved words require different names in the function API:

- `yield` -> `yields()` (PHP reserves `yield` for generators)
- `include` -> `partial()` (PHP reserves `include` as a language construct)

Variable access always uses `$this->key` (auto-escaped) or `raw('key')` / `$this->raw('key')` (unescaped).

---

## Variable Access & Auto-Escaping

The View engine is **safe by default**. All string values accessed via `$this->key` are automatically HTML-escaped to prevent XSS attacks.

### Auto-escaped output (default)

```php
<p>Hello, <?= $this->username ?>!</p>
```

If `username` is `<script>alert('xss')</script>`, the output will be:

```html
<p>Hello, &lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;!</p>
```

### Raw (unescaped) output

When you trust the content (e.g., pre-sanitized HTML), use `raw()`:

```php
<div class="content"><?= raw('htmlContent') ?></div>
```

### Manual escaping with strategies

Use `e()` for non-HTML escaping contexts:

```php
<a href="?q=<?= e(raw('query'), 'url') ?>">Search</a>
<script>var name = <?= e(raw('name'), 'js') ?>;</script>
<style>content: '<?= e(raw('icon'), 'css') ?>';</style>
```

### Non-string values

Integers, floats, arrays, and objects pass through without escaping:

```php
<span>Count: <?= $this->count ?></span>  <!-- Integer, no escaping needed -->
```

### Checking if a variable exists

```php
<?php if (isset($this->subtitle)): ?>
    <h2><?= $this->subtitle ?></h2>
<?php endif ?>
```

---

## Template Inheritance

Build layouts with parent/child relationships, similar to Blade's `@extends` or Twig's `{% extends %}`.

### Layout template

`templates/layouts/main.php`:
```php
<!DOCTYPE html>
<html>
<head>
    <title><?= yields('title', 'My App') ?></title>
    <?= stack('styles') ?>
</head>
<body>
    <header>
        <?= yields('header', '<nav>Default Nav</nav>') ?>
    </header>

    <main>
        <?= yields('content') ?>
    </main>

    <footer>
        <?= yields('footer', '&copy; 2024 My App') ?>
    </footer>

    <?= stack('scripts') ?>
</body>
</html>
```

### Child template

`templates/pages/about.php`:
```php
<?php extend('layouts/main') ?>

<?php section('title') ?>About Us<?php endSection() ?>

<?php section('content') ?>
    <h1>About Us</h1>
    <p>Welcome to our about page.</p>
<?php endSection() ?>
```

### How it works

1. The child template calls `extend('layouts/main')` to declare its parent
2. Content between `section('name')` and `endSection()` is captured
3. The parent layout renders, calling `yields('name')` to place sections
4. Sections not defined by the child use the default value from `yields()`

### Multi-level inheritance

Templates can extend other templates that themselves extend a layout:

`templates/layouts/admin.php`:
```php
<?php extend('layouts/main') ?>

<?php section('header') ?>
    <nav>Admin Navigation</nav>
<?php endSection() ?>

<?php section('content') ?>
    <div class="admin-layout">
        <aside><?= yields('sidebar') ?></aside>
        <div class="admin-content"><?= yields('admin_content') ?></div>
    </div>
<?php endSection() ?>
```

`templates/admin/dashboard.php`:
```php
<?php extend('layouts/admin') ?>

<?php section('admin_content') ?>
    <h1>Dashboard</h1>
    <p>Welcome back, <?= $this->name ?>!</p>
<?php endSection() ?>
```

This produces a three-level hierarchy: `dashboard` -> `admin layout` -> `main layout`.

---

## Sections & Yields

### `section($name)` / `endSection()`

Capture a block of HTML to be placed into a layout:

```php
<?php section('sidebar') ?>
    <ul>
        <li><a href="/">Home</a></li>
        <li><a href="/about">About</a></li>
    </ul>
<?php endSection() ?>
```

### `yields($name, $default = '')`

Output a section in a layout. If the child didn't define this section, the default is used:

```php
<?= yields('sidebar', '<p>No sidebar</p>') ?>
```

---

## Stacks (CSS/JS Injection)

Stacks allow child templates and included sub-templates to inject CSS and JavaScript into the layout's `<head>` or before `</body>`.

### Defining stack slots in the layout

```php
<head>
    <link rel="stylesheet" href="/css/app.css">
    <?= stack('styles') ?>
</head>
<body>
    <?= yields('content') ?>

    <script src="/js/app.js"></script>
    <?= stack('scripts') ?>
</body>
```

### Pushing to stacks from child templates

```php
<?php push('styles') ?>
    <link rel="stylesheet" href="/css/dashboard.css">
<?php endPush() ?>

<?php push('scripts') ?>
    <script src="/js/charts.js"></script>
<?php endPush() ?>
```

### Prepending to stacks

Use `prepend()` to add content to the beginning of a stack:

```php
<?php prepend('scripts') ?>
    <script src="/js/vendor/jquery.js"></script>
<?php endPrepend() ?>
```

Stacks are **global** across the render cycle. A `partial()`'d sub-template can push to a stack and it will surface in the layout.

---

## Includes & Embeds

Two ways to compose templates from smaller parts:

### `partial($template, $data)` — Isolated

The included template receives **only** the explicitly passed data (plus globals). It does NOT inherit the parent template's variables.

```php
<!-- Parent has $this->name = 'Rick' -->
<?= partial('components/card', ['title' => 'My Card']) ?>
<!-- The card template cannot access 'name', only 'title' -->
```

Use `partial()` for reusable components that should not depend on context.

### `embed($template, $data)` — Inheriting

The embedded template receives the parent's data merged with any overrides.

```php
<!-- Parent has $this->name = 'Rick' -->
<?= embed('partials/sidebar') ?>
<!-- sidebar can access 'name' because it inherits parent data -->

<?= embed('partials/sidebar', ['name' => 'Daryl']) ?>
<!-- sidebar sees name='Daryl' (override wins) -->
```

Use `embed()` for layout fragments that need access to the current view's context.

---

## Custom Helpers

Register functions that become available as `$this->helperName()` in all templates.

### Registering helpers

```php
// Single helper
View::helper('formatDate', function ($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
});

// Multiple helpers at once
View::helpers([
    'uppercase' => function ($text) {
        return strtoupper($text);
    },
    'truncate' => function ($text, $length = 100) {
        return strlen($text) > $length
            ? substr($text, 0, $length) . '...'
            : $text;
    },
]);
```

### Using helpers in templates

```php
<time><?= $this->formatDate(raw('created_at'), 'd M Y') ?></time>
<h1><?= $this->uppercase(raw('title')) ?></h1>
<p><?= $this->truncate(raw('body'), 200) ?></p>
```

Helpers are called via `$this->helperName()` since they are dynamically dispatched methods. Pass `raw('key')` to get the unescaped value for processing (e.g., date parsing).

---

## View Composers

Composers automatically inject data into templates before they render. This avoids repeating the same data-passing logic across controllers.

### Exact template match

```php
View::composer('partials/navigation', function (&$data) {
    $data['menuItems'] = Navigation::getItems();
});
```

Every time `partials/navigation` renders, `$this->menuItems` is available automatically.

### Wildcard patterns

```php
View::composer('admin/*', function (&$data) {
    $data['currentUser'] = Auth::user();
    $data['notifications'] = Notification::unread();
});
```

All templates under `admin/` will have `currentUser` and `notifications` injected.

### Event-based composers

You can also use the Event system directly:

```php
Event::on('core.view.compose:dashboard', function (&$data) {
    $data['stats'] = Analytics::summary();
});
```

---

## Global Variables

Variables available in every template:

```php
// Single global
View::addGlobal('appName', 'My Application');

// Multiple globals
View::addGlobals([
    'appName'  => 'My Application',
    'appUrl'   => 'https://example.com',
    'year'     => date('Y'),
]);
```

In templates:

```php
<title><?= raw('appName') ?></title>
<footer>&copy; <?= $this->year ?></footer>
```

---

## Template Caching

Cache the rendered output of expensive templates using Core's built-in Cache component:

```php
// Cache for 1 hour
echo View::from('reports/dashboard', $data)->cache(3600);

// Cache for 24 hours
echo View::from('pages/static-about')->cache(86400);
```

The cache key is automatically derived from the template name and a hash of the view data, so different data produces different cache entries.

Requires the `Cache` component to be initialized:

```php
Cache::using(['files', 'memory']);
```

---

## Template Fallbacks

Pass an array of template paths to try in order. The first one that exists is used:

```php
// Try article-specific template, then category, then generic
$view = View::from([
    "articles/article-{$id}",
    "articles/{$category}",
    'articles/default',
], $data);
```

If none exist, an `Exception` is thrown.

---

## Escape Strategies

Four built-in strategies, extensible via the Filter system:

| Strategy | Usage | Function |
|----------|-------|----------|
| `html` | Default `$this->key` | `htmlspecialchars()` |
| `url` | Query parameters | `rawurlencode()` |
| `js` | JavaScript strings | `json_encode()` with hex flags |
| `css` | CSS values | Hex-escape non-alphanumerics |

### Custom escape strategies

Override or add strategies via the Filter system:

```php
// Override the 'html' strategy
Filter::add('core.view.escape.html', function ($value) {
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
});

// Add a custom strategy
Filter::add('core.view.escape.markdown', function ($value) {
    return Parsedown::instance()->text($value);
});
```

Then use in templates:

```php
<?= e(raw('content'), 'markdown') ?>
```

---

## Adapter Interface

The View engine is pluggable. Any class implementing `View\Adapter` can replace the built-in PHP engine:

```php
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
```

Example: swapping in a custom adapter:

```php
View::using(new MyTwigAdapter('/path/to/templates', ['cache' => '/tmp']));
```

---

## Integration with Routes

Views integrate directly with Core's Route system:

```php
// Return a View directly from a route
Route::get('/about', function () {
    return View::from('pages/about', ['title' => 'About']);
});

// Or pass a View as the callback
Route::get('/home', View::from('pages/home'));
```

Views are also auto-rendered when added to a Response:

```php
Response::add(View::from('emails/welcome', $userData));
```

---

## Full Example: Blog Application

### Directory structure

```
templates/
  layouts/
    app.php
  pages/
    home.php
    post.php
  components/
    post-card.php
    pagination.php
  partials/
    sidebar.php
```

### `templates/layouts/app.php`

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= yields('title', 'My Blog') ?></title>
    <link rel="stylesheet" href="/css/app.css">
    <?= stack('styles') ?>
</head>
<body>
    <nav>
        <a href="/">Home</a>
        <a href="/about">About</a>
        <?php if (isset($this->currentUser)): ?>
            <span>Welcome, <?= $this->currentUser ?></span>
        <?php endif ?>
    </nav>

    <div class="container">
        <?= yields('content') ?>
    </div>

    <footer>&copy; <?= $this->year ?> My Blog</footer>

    <script src="/js/app.js"></script>
    <?= stack('scripts') ?>
</body>
</html>
```

### `templates/pages/home.php`

```php
<?php extend('layouts/app') ?>

<?php section('title') ?>Home - My Blog<?php endSection() ?>

<?php section('content') ?>
    <h1>Latest Posts</h1>

    <?php foreach (raw('posts') as $post): ?>
        <?= partial('components/post-card', ['post' => $post]) ?>
    <?php endforeach ?>

    <?= partial('components/pagination', [
        'currentPage' => raw('page'),
        'totalPages'  => raw('totalPages'),
    ]) ?>
<?php endSection() ?>
```

### `templates/components/post-card.php`

```php
<article class="post-card">
    <h2><a href="/post/<?= raw('post')['slug'] ?>">
        <?= raw('post')['title'] ?>
    </a></h2>
    <time><?= $this->formatDate(raw('post')['created_at'], 'M d, Y') ?></time>
    <p><?= $this->truncate(raw('post')['excerpt'], 150) ?></p>
</article>
```

### `templates/pages/post.php`

```php
<?php extend('layouts/app') ?>

<?php section('title') ?><?= $this->title ?> - My Blog<?php endSection() ?>

<?php push('styles') ?>
    <link rel="stylesheet" href="/css/highlight.css">
<?php endPush() ?>

<?php push('scripts') ?>
    <script src="/js/highlight.js"></script>
<?php endPush() ?>

<?php section('content') ?>
    <article>
        <h1><?= $this->title ?></h1>
        <time><?= $this->formatDate(raw('date'), 'F j, Y') ?></time>
        <div class="post-body">
            <?= raw('body') ?>
        </div>
    </article>

    <?= embed('partials/sidebar') ?>
<?php endSection() ?>
```

### Bootstrap code

```php
// Initialize
View::using(new View\PHP(__DIR__ . '/templates'));

// Register globals
View::addGlobals([
    'year' => date('Y'),
]);

// Register helpers
View::helpers([
    'formatDate' => function ($date, $fmt = 'Y-m-d') {
        return date($fmt, strtotime($date));
    },
    'truncate' => function ($text, $len = 100) {
        return mb_strlen($text) > $len ? mb_substr($text, 0, $len) . '...' : $text;
    },
]);

// Auto-inject currentUser into all templates
View::composer('*', function (&$data) {
    $data['currentUser'] = Session::get('user_name');
});

// Auto-inject sidebar data
View::composer('partials/sidebar', function (&$data) {
    $data['recentPosts'] = Post::recent(5);
    $data['categories']  = Category::all();
});

// Routes
Route::get('/', function () {
    $page  = (int) Request::get('page', 1);
    $posts = Post::paginate($page, 10);

    return View::from('pages/home', [
        'posts'      => $posts->items,
        'page'       => $page,
        'totalPages' => $posts->lastPage,
    ]);
});

Route::get('/post/:slug', function ($slug) {
    $post = Post::findBySlug($slug);

    return View::from([
        "pages/post-{$slug}",  // Try slug-specific template first
        'pages/post',          // Fall back to generic
    ], [
        'title' => $post->title,
        'date'  => $post->created_at,
        'body'  => $post->rendered_body,
    ])->cache(600);  // Cache for 10 minutes
});
```

---

## API Reference

### View (Facade)

| Method | Description |
|--------|-------------|
| `View::using(Adapter $handler)` | Set the template engine adapter |
| `View::from($template, $data = null)` | Create a view instance |
| `View::exists($path)` | Check if a template exists |
| `View::helper($name, $fn)` | Register a template helper |
| `View::helpers(array $helpers)` | Register multiple helpers |
| `View::composer($pattern, $fn)` | Register a view composer |
| `View::addGlobal($key, $val)` | Add a global variable |
| `View::addGlobals(array $defs)` | Add multiple globals |
| `$view->with($data)` | Assign data to the view |
| `$view->cache($ttl)` | Cache the rendered output |

### Global Template Functions

Available as plain functions in any template:

| Function | Description |
|----------|-------------|
| `raw('key')` | Access variable (unescaped) |
| `e($value, $strategy)` | Manual escape (html/url/js/css) |
| `extend($layout)` | Declare parent layout |
| `section($name)` | Begin section capture |
| `endSection()` | End section capture |
| `yields($name, $default)` | Output a section |
| `push($name)` | Begin stack push |
| `endPush()` | End stack push |
| `prepend($name)` | Begin stack prepend |
| `endPrepend()` | End stack prepend |
| `stack($name)` | Output a stack |
| `partial($tpl, $data)` | Include template (isolated data) |
| `embed($tpl, $data)` | Embed template (inherited data) |

### View\Scope (`$this` in templates)

| Property/Method | Description |
|----------|-------------|
| `$this->key` | Access variable (auto-escaped) |
| `$this->raw('key')` | Access variable (unescaped) |
| `$this->e($value, $strategy)` | Manual escape |
| `$this->helperName(...)` | Call a registered helper |
| `$this->yield($name, $default)` | Output a section (method name allowed on objects) |
| `$this->include($tpl, $data)` | Include template (method name allowed on objects) |
