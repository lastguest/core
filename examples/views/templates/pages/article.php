<?php extend('layouts/main') ?>

<?php section('title') ?><?= $this->title ?> - <?= raw('appName') ?><?php endSection() ?>

<?php push('styles') ?>
    <style>
        .article-body { line-height: 1.8; }
        .article-meta { color: #636e72; font-size: 0.9rem; margin-bottom: 2rem; }
    </style>
<?php endPush() ?>

<?php push('scripts') ?>
    <script>
        // Highlight code blocks, reading time, etc.
        console.log('Article page loaded');
    </script>
<?php endPush() ?>

<?php section('content') ?>
    <article>
        <h1><?= $this->title ?></h1>

        <div class="article-meta">
            By <?= $this->author ?> &middot;
            <?= $this->formatDate(raw('date'), 'F j, Y') ?>
        </div>

        <div class="article-body">
            <?= raw('body') ?>
        </div>
    </article>

    <hr>

    <h3>Related Articles</h3>
    <?= embed('partials/related') ?>
<?php endSection() ?>
