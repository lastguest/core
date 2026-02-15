<?php extend('layouts/main') ?>

<?php section('title') ?>Home - <?= raw('appName') ?><?php endSection() ?>

<?php section('content') ?>
    <h1>Welcome to Core View</h1>
    <p>This is the home page rendered with template inheritance.</p>

    <h2>Featured Articles</h2>
    <?php foreach (raw('articles') as $article): ?>
        <?= partial('components/article-card', ['article' => $article]) ?>
    <?php endforeach ?>
<?php endSection() ?>
