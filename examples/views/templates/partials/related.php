<?php
// This partial uses embed(), so it inherits the parent template's data.
// It can access $this->title, $this->author, etc. from the article page.
?>
<ul>
    <li><a href="#">More by <?= $this->author ?></a></li>
    <li><a href="/">Back to all articles</a></li>
</ul>
