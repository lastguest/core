<?php

class ViewTest extends \PHPUnit\Framework\TestCase {

	protected static $TEMPLATE_DIR;

	public static function setUpBeforeClass(): void {
		self::$TEMPLATE_DIR = sys_get_temp_dir() . '/core_view_test_' . getmypid();
		@mkdir(self::$TEMPLATE_DIR);
		@mkdir(self::$TEMPLATE_DIR . '/layouts');
		@mkdir(self::$TEMPLATE_DIR . '/pages');
		@mkdir(self::$TEMPLATE_DIR . '/components');
		@mkdir(self::$TEMPLATE_DIR . '/special');
		@mkdir(self::$TEMPLATE_DIR . '/admin');

		// ── Basic templates ──────────────────────────────────────

		file_put_contents(self::$TEMPLATE_DIR . '/test.php', 'TESTIFICATE');

		file_put_contents(self::$TEMPLATE_DIR . '/test_var.php', '<?=$this->var?>');

		file_put_contents(self::$TEMPLATE_DIR . '/global.php', '<?=$this->raw("THE_DARKNESS")?>');

		file_put_contents(self::$TEMPLATE_DIR . '/special/hello.php',
			'Hello, <?= $this->name ?>!'
		);

		// ── Fallback templates ───────────────────────────────────

		file_put_contents(self::$TEMPLATE_DIR . '/article-123.php',
			'ARTICLE-123:<?=$this->var?>'
		);
		file_put_contents(self::$TEMPLATE_DIR . '/article.php',
			'ARTICLE:<?=$this->var?>'
		);

		// ── Include / Embed templates ────────────────────────────

		file_put_contents(self::$TEMPLATE_DIR . '/components/card.php',
			'<div class="card"><?=$this->title?></div>'
		);

		file_put_contents(self::$TEMPLATE_DIR . '/index_include.php',
			'[<?= $this->include("components/card", ["title" => "Isolated"]) ?>]'
		);

		file_put_contents(self::$TEMPLATE_DIR . '/index_embed.php',
			'[<?= $this->embed("special/hello") ?>]'
		);

		file_put_contents(self::$TEMPLATE_DIR . '/index_embed_override.php',
			'[<?= $this->embed("special/hello", ["name" => "Daryl"]) ?>]'
		);

		// ── Escaping templates ───────────────────────────────────

		file_put_contents(self::$TEMPLATE_DIR . '/escape_auto.php',
			'<?=$this->content?>'
		);

		file_put_contents(self::$TEMPLATE_DIR . '/escape_raw.php',
			'<?=$this->raw("content")?>'
		);

		file_put_contents(self::$TEMPLATE_DIR . '/escape_manual.php',
			'<?=$this->e($this->raw("url"), "url")?>'
		);

		// ── Layout / Inheritance templates ───────────────────────

		file_put_contents(self::$TEMPLATE_DIR . '/layouts/main.php', implode('', [
			'<html>',
			'<head><?=$this->stack("styles")?></head>',
			'<body>',
			'<header><?=$this->yield("header", "Default Header")?></header>',
			'<main><?=$this->yield("content")?></main>',
			'<footer><?=$this->yield("footer", "Default Footer")?></footer>',
			'<?=$this->stack("scripts")?>',
			'</body>',
			'</html>',
		]));

		file_put_contents(self::$TEMPLATE_DIR . '/pages/home.php', implode("\n", [
			'<?php $this->extend("layouts/main") ?>',
			'<?php $this->section("header") ?>Welcome<?php $this->endSection() ?>',
			'<?php $this->section("content") ?>Home Body<?php $this->endSection() ?>',
		]));

		file_put_contents(self::$TEMPLATE_DIR . '/pages/minimal.php', implode("\n", [
			'<?php $this->extend("layouts/main") ?>',
			'<?php $this->section("content") ?>Minimal<?php $this->endSection() ?>',
		]));

		// ── Stack templates ──────────────────────────────────────

		file_put_contents(self::$TEMPLATE_DIR . '/pages/with_stacks.php', implode("\n", [
			'<?php $this->extend("layouts/main") ?>',
			'<?php $this->push("styles") ?><link rel="stylesheet" href="app.css"><?php $this->endPush() ?>',
			'<?php $this->push("scripts") ?><script src="app.js"></script><?php $this->endPush() ?>',
			'<?php $this->section("content") ?>Stacked<?php $this->endSection() ?>',
		]));

		// ── Nested layout (grandchild) ───────────────────────────

		file_put_contents(self::$TEMPLATE_DIR . '/layouts/admin.php', implode("\n", [
			'<?php $this->extend("layouts/main") ?>',
			'<?php $this->section("header") ?>Admin Panel<?php $this->endSection() ?>',
			'<?php $this->section("content") ?><nav>Sidebar</nav><div><?=$this->yield("admin_content")?></div><?php $this->endSection() ?>',
		]));

		file_put_contents(self::$TEMPLATE_DIR . '/admin/dashboard.php', implode("\n", [
			'<?php $this->extend("layouts/admin") ?>',
			'<?php $this->section("admin_content") ?>Dashboard<?php $this->endSection() ?>',
		]));

		// ── Helper template ──────────────────────────────────────

		file_put_contents(self::$TEMPLATE_DIR . '/helper_test.php',
			'<?=$this->formatDate("2024-01-15", "d/m/Y")?>'
		);

		// ── isset check template ─────────────────────────────────

		file_put_contents(self::$TEMPLATE_DIR . '/isset_test.php',
			'<?=isset($this->defined) ? "yes" : "no"?>:<?=isset($this->undefined) ? "yes" : "no"?>'
		);

		// ── Composer test template ───────────────────────────────

		file_put_contents(self::$TEMPLATE_DIR . '/admin/users.php',
			'<?=$this->raw("injected")?>'
		);

		// ── Function-syntax templates ────────────────────────────

		file_put_contents(self::$TEMPLATE_DIR . '/layouts/fn_main.php', implode('', [
			'<html>',
			'<head><?=stack("styles")?></head>',
			'<body>',
			'<header><?=yields("header", "Default Header")?></header>',
			'<main><?=yields("content")?></main>',
			'</body>',
			'</html>',
		]));

		file_put_contents(self::$TEMPLATE_DIR . '/pages/fn_home.php', implode("\n", [
			'<?php extend("layouts/fn_main") ?>',
			'<?php section("header") ?>FN Welcome<?php endSection() ?>',
			'<?php section("content") ?>FN Body<?php endSection() ?>',
		]));

		file_put_contents(self::$TEMPLATE_DIR . '/pages/fn_stacks.php', implode("\n", [
			'<?php extend("layouts/fn_main") ?>',
			'<?php push("styles") ?><link href="fn.css"><?php endPush() ?>',
			'<?php section("content") ?>FN Stacked<?php endSection() ?>',
		]));

		file_put_contents(self::$TEMPLATE_DIR . '/fn_escape.php',
			'<?=e(raw("content"), "url")?>'
		);

		file_put_contents(self::$TEMPLATE_DIR . '/fn_partial.php',
			'[<?=partial("components/card", ["title" => "FN Card"])?>]'
		);

		file_put_contents(self::$TEMPLATE_DIR . '/fn_embed.php',
			'[<?=embed("special/hello")?>]'
		);

		// Init View handler
		View::using(new View\PHP(self::$TEMPLATE_DIR));
	}

	public static function tearDownAfterClass(): void {
		// Clean up temp files
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(self::$TEMPLATE_DIR, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($it as $file) {
			$file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
		}
		rmdir(self::$TEMPLATE_DIR);
	}


	// ═══════════════════════════════════════════════════════════════
	// Basic Rendering
	// ═══════════════════════════════════════════════════════════════

	public function testRender() {
		$this->assertEquals('TESTIFICATE', (string) View::from('test'));
	}

	public function testRenderWithParameters() {
		$this->assertEquals('1', (string) View::from('test_var', ['var' => 1]));
	}

	public function testWithMergeOrder() {
		// Last with() call should win
		$view = View::from('test_var', ['var' => 'first']);
		$view->with(['var' => 'second']);
		$this->assertEquals('second', (string) $view);
	}

	public function testGlobalParameters() {
		View::addGlobal('THE_DARKNESS', 'Jakie');
		$this->assertEquals('Jakie', (string) View::from('global'));
	}

	public function testTemplateExists() {
		$this->assertTrue(View::exists('special/hello'));
		$this->assertFalse(View::exists('im/fake/template'));
	}


	// ═══════════════════════════════════════════════════════════════
	// Fallback Templates
	// ═══════════════════════════════════════════════════════════════

	public function testFallbackException() {
		try {
			View::from(['foo', 'bar', 'baz']);
		} catch (Exception $e) {
			return $this->assertTrue(true);
		}
		$this->assertTrue(false, 'Exception should have been thrown');
	}

	public function testFallback() {
		$this->assertEquals('ARTICLE-123:626', (string) View::from([
			'undefined', 'article-123', 'article',
		], ['var' => 626]));

		$this->assertEquals('ARTICLE:626', (string) View::from([
			'undefined', 'article-9999', 'article',
		], ['var' => 626]));
	}


	// ═══════════════════════════════════════════════════════════════
	// Auto-Escaping
	// ═══════════════════════════════════════════════════════════════

	public function testAutoEscapeHtml() {
		$html = '<script>alert("xss")</script>';
		$result = (string) View::from('escape_auto', ['content' => $html]);
		$this->assertEquals(htmlspecialchars($html, ENT_QUOTES, 'UTF-8'), $result);
		$this->assertStringNotContainsString('<script>', $result);
	}

	public function testRawAccess() {
		$html = '<b>bold</b>';
		$result = (string) View::from('escape_raw', ['content' => $html]);
		$this->assertEquals($html, $result);
	}

	public function testManualEscapeUrl() {
		$url = 'hello world&foo=bar';
		$result = (string) View::from('escape_manual', ['url' => $url]);
		$this->assertEquals(rawurlencode($url), $result);
	}

	public function testNonStringNotEscaped() {
		// Integers and other non-strings should pass through without escaping
		$result = (string) View::from('test_var', ['var' => 42]);
		$this->assertEquals('42', $result);
	}


	// ═══════════════════════════════════════════════════════════════
	// __isset behavior
	// ═══════════════════════════════════════════════════════════════

	public function testIssetBehavior() {
		$result = (string) View::from('isset_test', ['defined' => 'value']);
		$this->assertEquals('yes:no', $result);
	}


	// ═══════════════════════════════════════════════════════════════
	// Include (isolated) and Embed (inheriting)
	// ═══════════════════════════════════════════════════════════════

	public function testIncludeIsolated() {
		$result = (string) View::from('index_include', ['name' => 'Rick']);
		// The included component should only see 'title', not 'name'
		$this->assertEquals('[<div class="card">Isolated</div>]', $result);
	}

	public function testEmbedInheritsData() {
		$result = (string) View::from('index_embed', ['name' => 'Rick']);
		// embed() inherits parent data, so 'name' should be available
		$this->assertEquals('[Hello, Rick!]', $result);
	}

	public function testEmbedOverridesData() {
		$result = (string) View::from('index_embed_override', ['name' => 'Rick']);
		// embed() with override: 'Daryl' should win over 'Rick'
		$this->assertEquals('[Hello, Daryl!]', $result);
	}


	// ═══════════════════════════════════════════════════════════════
	// Template Inheritance (extend / section / yield)
	// ═══════════════════════════════════════════════════════════════

	public function testBasicInheritance() {
		$result = (string) View::from('pages/home');
		$this->assertStringContainsString('<header>Welcome</header>', $result);
		$this->assertStringContainsString('<main>Home Body</main>', $result);
		$this->assertStringContainsString('<html>', $result);
		$this->assertStringContainsString('</html>', $result);
	}

	public function testYieldDefaults() {
		// pages/minimal only defines 'content', so 'header' and 'footer' should use defaults
		$result = (string) View::from('pages/minimal');
		$this->assertStringContainsString('<header>Default Header</header>', $result);
		$this->assertStringContainsString('<main>Minimal</main>', $result);
		$this->assertStringContainsString('<footer>Default Footer</footer>', $result);
	}

	public function testMultiLevelInheritance() {
		// admin/dashboard extends layouts/admin which extends layouts/main
		$result = (string) View::from('admin/dashboard');
		$this->assertStringContainsString('<header>Admin Panel</header>', $result);
		$this->assertStringContainsString('Dashboard', $result);
		$this->assertStringContainsString('<nav>Sidebar</nav>', $result);
		$this->assertStringContainsString('<html>', $result);
	}


	// ═══════════════════════════════════════════════════════════════
	// Stacks (push / stack)
	// ═══════════════════════════════════════════════════════════════

	public function testStacks() {
		$result = (string) View::from('pages/with_stacks');
		$this->assertStringContainsString('<link rel="stylesheet" href="app.css">', $result);
		$this->assertStringContainsString('<script src="app.js"></script>', $result);
		$this->assertStringContainsString('<main>Stacked</main>', $result);
	}


	// ═══════════════════════════════════════════════════════════════
	// Custom Helpers
	// ═══════════════════════════════════════════════════════════════

	public function testCustomHelper() {
		View::helper('formatDate', function ($date, $fmt = 'Y-m-d') {
			return date($fmt, strtotime($date));
		});

		$result = (string) View::from('helper_test');
		$this->assertEquals('15/01/2024', $result);
	}

	public function testUnknownHelperThrows() {
		file_put_contents(self::$TEMPLATE_DIR . '/bad_helper.php', '<?=$this->nonExistent()?>');
		$thrown = false;
		try {
			(string) View::from('bad_helper');
		} catch (\BadMethodCallException $e) {
			$thrown = true;
			// Clean any leftover output buffers from the sandboxed include
			while (ob_get_level() > 1) ob_end_clean();
		}
		$this->assertTrue($thrown, 'BadMethodCallException should have been thrown');
	}


	// ═══════════════════════════════════════════════════════════════
	// View Composers
	// ═══════════════════════════════════════════════════════════════

	public function testComposerExactMatch() {
		View::composer('admin/users', function (&$data) {
			$data['injected'] = 'composer_value';
		});

		$result = (string) View::from('admin/users');
		$this->assertEquals('composer_value', $result);
	}

	public function testComposerWildcard() {
		View::composer('admin/*', function (&$data) {
			$data['injected'] = 'wildcard_value';
		});

		$result = (string) View::from('admin/users');
		$this->assertStringContainsString('wildcard_value', $result);
	}


	// ═══════════════════════════════════════════════════════════════
	// Caching
	// ═══════════════════════════════════════════════════════════════

	public function testCacheChainable() {
		$view = View::from('test')->cache(3600);
		$this->assertEquals('TESTIFICATE', (string) $view);
	}


	// ═══════════════════════════════════════════════════════════════
	// Function Syntax (global template functions)
	// ═══════════════════════════════════════════════════════════════

	public function testFnInheritance() {
		$result = (string) View::from('pages/fn_home');
		$this->assertStringContainsString('<header>FN Welcome</header>', $result);
		$this->assertStringContainsString('<main>FN Body</main>', $result);
		$this->assertStringContainsString('<html>', $result);
	}

	public function testFnStacks() {
		$result = (string) View::from('pages/fn_stacks');
		$this->assertStringContainsString('<link href="fn.css">', $result);
		$this->assertStringContainsString('<main>FN Stacked</main>', $result);
	}

	public function testFnEscape() {
		$result = (string) View::from('fn_escape', ['content' => 'hello world']);
		$this->assertEquals(rawurlencode('hello world'), $result);
	}

	public function testFnPartialIsolated() {
		$result = (string) View::from('fn_partial');
		$this->assertEquals('[<div class="card">FN Card</div>]', $result);
	}

	public function testFnEmbed() {
		$result = (string) View::from('fn_embed', ['name' => 'Morty']);
		$this->assertEquals('[Hello, Morty!]', $result);
	}

	public function testFnYieldDefaults() {
		// fn_home doesn't define header's parent default, but does override it.
		// Test a page that doesn't override header to check defaults.
		file_put_contents(self::$TEMPLATE_DIR . '/pages/fn_defaults.php', implode("\n", [
			'<?php extend("layouts/fn_main") ?>',
			'<?php section("content") ?>Only Content<?php endSection() ?>',
		]));
		$result = (string) View::from('pages/fn_defaults');
		$this->assertStringContainsString('<header>Default Header</header>', $result);
		$this->assertStringContainsString('<main>Only Content</main>', $result);
	}

}
