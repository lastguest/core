<?php $this->extend('layouts/main') ?>

<?php $this->section('title') ?><?= $this->title ?> - <?= $this->raw('appName') ?><?php $this->endSection() ?>

<?php $this->push('styles') ?>
    <style>
        .article-body { line-height: 1.8; }
        .article-meta { color: #636e72; font-size: 0.9rem; margin-bottom: 2rem; }
    </style>
<?php $this->endPush() ?>

<?php $this->push('scripts') ?>
    <script>
        // Highlight code blocks, reading time, etc.
        console.log('Article page loaded');
    </script>
<?php $this->endPush() ?>

<?php $this->section('content') ?>
    <article>
        <h1><?= $this->title ?></h1>

        <div class="article-meta">
            By <?= $this->author ?> &middot;
            <?= $this->formatDate($this->raw('date'), 'F j, Y') ?>
        </div>

        <div class="article-body">
            <?= $this->raw('body') ?>
        </div>
    </article>

    <hr>

    <h3>Related Articles</h3>
    <?= $this->embed('partials/related') ?>
<?php $this->endSection() ?>
