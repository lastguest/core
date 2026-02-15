<?php
/**
 * Core View Demo
 *
 * This script demonstrates all major features of the enhanced View engine.
 * Run it with: php examples/views/demo.php
 *
 * Features demonstrated:
 *   1. Template inheritance (extend/section/yield)
 *   2. Auto-escaping & raw output
 *   3. Stacks (CSS/JS injection)
 *   4. Include (isolated) vs Embed (inheriting)
 *   5. Custom helpers
 *   6. View composers
 *   7. Global variables
 *   8. Template fallbacks
 *   9. Render caching
 */

require_once __DIR__ . '/../../classes/Loader.php';

// ─── Setup ───────────────────────────────────────────────────────
View::using(new View\PHP(__DIR__ . '/templates'));

// ─── Global variables ────────────────────────────────────────────
View::addGlobals([
    'appName' => 'Core Blog',
    'year'    => date('Y'),
]);

// ─── Custom helpers ──────────────────────────────────────────────
View::helpers([
    'formatDate' => function ($date, $fmt = 'Y-m-d') {
        return date($fmt, strtotime($date));
    },
    'truncate' => function ($text, $len = 100) {
        return mb_strlen($text) > $len ? mb_substr($text, 0, $len) . '...' : $text;
    },
]);

// ─── View composers ─────────────────────────────────────────────
// This composer fires for every template matching "pages/*"
View::composer('pages/*', function (&$data) {
    $data['composerNote'] = 'Data injected by wildcard composer';
});

// ─── Sample data ─────────────────────────────────────────────────
$articles = [
    [
        'id'      => 1,
        'title'   => 'Getting Started with Core',
        'excerpt' => 'Learn how to build applications rapidly with the Core framework. This guide covers installation, routing, and your first view template.',
        'date'    => '2024-12-01',
    ],
    [
        'id'      => 2,
        'title'   => 'Template Inheritance Explained',
        'excerpt' => 'Master the extend/section/yield pattern to build DRY layouts. No more copy-pasting headers and footers across pages.',
        'date'    => '2024-12-15',
    ],
    [
        'id'      => 3,
        'title'   => 'XSS Prevention & Auto-Escaping',
        'excerpt' => 'How Core View\'s auto-escaping keeps your app safe by default, and when to use raw() for trusted content.',
        'date'    => '2025-01-05',
    ],
];


echo "=== DEMO 1: Home page with template inheritance ===\n\n";

$html = (string) View::from('pages/home', ['articles' => $articles]);
echo $html;
echo "\n\n";


echo "=== DEMO 2: Article page with stacks and embeds ===\n\n";

$html = (string) View::from('pages/article', [
    'title'  => 'Getting Started with Core',
    'author' => 'Stefano Azzolini',
    'date'   => '2024-12-01',
    'body'   => '<p>This is the <strong>article body</strong> with HTML content.</p>',
]);
echo $html;
echo "\n\n";


echo "=== DEMO 3: Auto-escaping prevents XSS ===\n\n";

// This simulates user-controlled input with a malicious script tag
$malicious = '<script>alert("xss")</script>';
View::using(new View\PHP(__DIR__ . '/templates'));

// Create a simple inline template for this demo
$tmpDir = sys_get_temp_dir() . '/core_view_demo';
@mkdir($tmpDir);
file_put_contents($tmpDir . '/xss_test.php', '<p>User says: <?= $this->message ?></p>');
View::using(new View\PHP($tmpDir));

$safe = (string) View::from('xss_test', ['message' => $malicious]);
echo "Input:  $malicious\n";
echo "Output: $safe\n";
echo "The <script> tag is safely escaped!\n\n";

// Restore original template path
View::using(new View\PHP(__DIR__ . '/templates'));


echo "=== DEMO 4: Template fallbacks ===\n\n";

// Create temp templates for fallback demo
$tmpDir2 = sys_get_temp_dir() . '/core_view_fallback';
@mkdir($tmpDir2);
file_put_contents($tmpDir2 . '/article-specific.php', 'Specific article template');
file_put_contents($tmpDir2 . '/article-generic.php', 'Generic article template');

View::using(new View\PHP($tmpDir2));

// First existing template wins
$view = View::from(['article-missing', 'article-specific', 'article-generic']);
echo "Fallback result: " . $view . "\n\n";

// Clean up
unlink($tmpDir2 . '/article-specific.php');
unlink($tmpDir2 . '/article-generic.php');
rmdir($tmpDir2);
@unlink($tmpDir . '/xss_test.php');
@rmdir($tmpDir);

echo "=== All demos complete ===\n";
