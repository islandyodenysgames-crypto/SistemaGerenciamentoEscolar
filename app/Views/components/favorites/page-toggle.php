<?php
$type = (string) ($type ?? '');
$id = (int) ($id ?? 0);
$active = (bool) ($active ?? false);
$label = (string) ($label ?? 'Favoritar');
?>
<div class="page-favorite-action">
    <button
        type="button"
        class="favorite-toggle favorite-toggle-page <?= $active ? 'is-active' : '' ?>"
        data-favorite-toggle
        data-favorite-type="<?= e($type) ?>"
        data-favorite-id="<?= $id ?>"
        data-favorite-endpoint="<?= base_url('favoritos/alternar') ?>"
        data-favorite-label="<?= e($label) ?>"
        aria-pressed="<?= $active ? 'true' : 'false' ?>"
        title="<?= $active ? 'Remover dos favoritos' : 'Adicionar aos favoritos' ?>"
    >
        <i data-lucide="star"></i>
        <span><?= $active ? 'Favorito' : e($label) ?></span>
    </button>
</div>
