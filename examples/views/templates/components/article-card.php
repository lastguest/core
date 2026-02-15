<div style="border: 1px solid #dfe6e9; border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem;">
    <h3 style="margin-top: 0;">
        <a href="/article/<?= $this->raw('article')['id'] ?>"><?= $this->raw('article')['title'] ?></a>
    </h3>
    <p style="color: #636e72;"><?= $this->truncate($this->raw('article')['excerpt'], 120) ?></p>
    <small>
        <?= $this->formatDate($this->raw('article')['date'], 'M d, Y') ?>
    </small>
</div>
