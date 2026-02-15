<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= yields('title', 'Core View Demo') ?></title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; color: #333; }
        header { background: #2d3436; color: white; padding: 1rem 2rem; }
        header nav a { color: #dfe6e9; text-decoration: none; margin-right: 1rem; }
        main { max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        footer { text-align: center; padding: 2rem; color: #636e72; border-top: 1px solid #dfe6e9; margin-top: 3rem; }
    </style>
    <?= stack('styles') ?>
</head>
<body>
    <header>
        <nav>
            <strong><?= raw('appName') ?></strong>
            &nbsp;&nbsp;
            <a href="/">Home</a>
            <a href="/about">About</a>
            <a href="/contact">Contact</a>
        </nav>
    </header>

    <main>
        <?= yields('content') ?>
    </main>

    <footer>
        <?= yields('footer', '&copy; ' . raw('year') . ' ' . raw('appName')) ?>
    </footer>

    <?= stack('scripts') ?>
</body>
</html>
