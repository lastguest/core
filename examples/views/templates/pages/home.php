<?php $this->extend('layouts/main') ?>

<?php $this->section('title') ?>Home - <?= $this->raw('appName') ?><?php $this->endSection() ?>

<?php $this->section('content') ?>
    <h1>Welcome to Core View</h1>
    <p>This is the home page rendered with template inheritance.</p>

    <h2>Featured Articles</h2>
    <?php foreach ($this->raw('articles') as $article): ?>
        <?= $this->include('components/article-card', ['article' => $article]) ?>
    <?php endforeach ?>
<?php $this->endSection() ?>
