<?php $favorites=(array)($favorites??[]); ?>
<section class="card favorite-panel">
    <div class="card-header"><div><h3><i data-lucide="star"></i> Meus favoritos</h3><p>Acesso rápido aos alunos, turmas e casos que você acompanha com mais frequência.</p></div><span class="badge"><?= count($favorites) ?></span></div>
    <?php if ($favorites===[]): ?>
        <div class="favorite-empty"><i data-lucide="star"></i><p>Você ainda não adicionou favoritos. Use a estrela disponível nos resultados da busca.</p></div>
    <?php else: ?>
        <div class="favorite-grid">
            <?php foreach ($favorites as $favorite): ?>
                <a class="favorite-card" href="<?= base_url(ltrim((string)$favorite['url'],'/')) ?>">
                    <i data-lucide="star"></i><span><strong><?= e((string)$favorite['title']) ?></strong><small><?= e((string)($favorite['subtitle']??'')) ?></small></span><i data-lucide="arrow-up-right"></i>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
