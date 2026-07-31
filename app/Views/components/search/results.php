<?php
$groups = [
    'students' => ['Alunos','user-round'],
    'classes' => ['Turmas','users-round'],
    'occurrences' => ['Ocorrências','clipboard-alert'],
    'monitoring' => ['Acompanhamentos','heart-handshake'],
    'notices' => ['Avisos','megaphone'],
    'users' => ['Usuários','user-cog'],
];
$total = array_sum(array_map('count', array_intersect_key((array)$results, $groups)));
?>
<div class="search-results-page mt-24">
    <div class="card"><div class="card-header"><div><h3>Resultados encontrados</h3><p><?= $total ?> resultado(s) agrupado(s) por área do sistema.</p></div></div></div>
    <?php foreach ($groups as $key => [$label,$icon]): ?>
        <?php $items=(array)($results[$key]??[]); if ($items===[]) continue; ?>
        <section class="card mt-24 search-result-group">
            <div class="card-header"><h3><i data-lucide="<?= e($icon) ?>"></i><?= e($label) ?></h3><span class="badge"><?= count($items) ?></span></div>
            <div class="search-result-list">
                <?php foreach ($items as $item): ?>
                    <div class="search-result-item-wrap">
                        <a href="<?= base_url(ltrim((string)$item['url'],'/')) ?>" class="search-result-item">
                            <i data-lucide="<?= e($icon) ?>"></i><span><strong><?= e((string)$item['title']) ?></strong><small><?= e((string)($item['subtitle']??'')) ?></small></span><i data-lucide="arrow-right"></i>
                        </a>
                        <?php if (in_array((string)($item['type']??''), ['student','class','monitoring'], true)): ?>
                            <?php $isFavorite = !empty($item['is_favorite']); ?>
                            <button type="button" class="favorite-toggle <?= $isFavorite ? 'is-active' : '' ?>" data-favorite-toggle data-favorite-type="<?= e((string)$item['type']) ?>" data-favorite-id="<?= (int)$item['id'] ?>" data-favorite-endpoint="<?= base_url('favoritos/alternar') ?>" data-favorite-label="Favoritar" aria-pressed="<?= $isFavorite ? 'true' : 'false' ?>" title="<?= $isFavorite ? 'Remover dos favoritos' : 'Adicionar aos favoritos' ?>"><i data-lucide="star"></i><span><?= $isFavorite ? 'Favorito' : 'Favoritar' ?></span></button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
    <?php if ($total===0): ?><div class="card mt-24 activity-empty">Nenhum resultado encontrado para esta pesquisa.</div><?php endif; ?>
</div>
