<?php
$notices = $notices ?? [];
if (!$notices) return;

$totalActive = (int) ($totalActive ?? count($notices));
$priorityLabels = ['INFO'=>'Comunicado','IMPORTANT'=>'Importante','HIGH'=>'Atenção','URGENT'=>'Urgente'];
$categoryLabels = ['GENERAL'=>'Geral','PEDAGOGICAL'=>'Pedagógico','EVENTS'=>'Eventos','CALENDAR'=>'Calendário','MEETINGS'=>'Reuniões','MANAGEMENT'=>'Gestão'];
$youtubeId = static function (?string $url): ?string {
    $url = trim((string) $url);
    if ($url === '') return null;
    $patterns = [
        '~youtu\.be/([A-Za-z0-9_-]{6,})~',
        '~youtube\.com/(?:watch\?[^#]*v=|embed/|shorts/)([A-Za-z0-9_-]{6,})~',
    ];
    foreach ($patterns as $pattern) if (preg_match($pattern, $url, $m)) return $m[1];
    return null;
};
?>
<section class="notice-wall notice-wall-premium" data-notice-carousel aria-label="Mural de avisos">
    <header class="notice-wall-heading">
        <div class="notice-wall-title">
            <span class="notice-wall-icon"><i data-lucide="megaphone"></i></span>
            <div><h2>Mural de Avisos</h2><p>Fique por dentro das principais informações e comunicados.</p></div>
        </div>
        <a href="<?= base_url('avisos') ?>" class="btn-secondary notice-wall-all">Ver todos os avisos <i data-lucide="arrow-up-right"></i></a>
    </header>

    <div class="notice-wall-carousel">
        <button class="notice-wall-arrow prev" type="button" data-carousel-prev aria-label="Aviso anterior"><i data-lucide="arrow-left"></i></button>
        <div class="notice-wall-viewport" data-carousel-viewport tabindex="0">
            <div class="notice-wall-track">
            <?php foreach ($notices as $i => $notice):
                $priority = strtoupper((string)($notice['priority'] ?? 'INFO'));
                $category = strtoupper((string)($notice['category'] ?? 'GENERAL'));
                $videoId = $youtubeId($notice['youtube_url'] ?? null);
                $banner = trim((string)($notice['banner_path'] ?? ''));
                $visualUrl = $banner !== '' ? base_url($banner) : ($videoId ? 'https://img.youtube.com/vi/'.$videoId.'/hqdefault.jpg' : '');
                $attachments = is_array($notice['attachments'] ?? null) ? $notice['attachments'] : [];
                $summary = trim((string)($notice['summary'] ?? ''));
                if ($summary === '') $summary = mb_strimwidth(strip_tags((string)($notice['content'] ?? '')), 0, 115, '…');
                $published = (string)($notice['published_at'] ?? $notice['created_at'] ?? 'now');
                $hasAction = $videoId || $attachments;
                $actionLabel = $videoId ? 'Assistir vídeo' : ($attachments ? 'Saiba mais' : 'Ver aviso');
                $actionIcon = $videoId ? 'play' : ($attachments ? 'paperclip' : 'arrow-right');
            ?>
                <article id="dashboard-aviso-<?= (int)$notice['id'] ?>" class="notice-wall-card priority-<?= strtolower(e($priority)) ?> <?= $visualUrl ? 'has-banner' : 'without-banner' ?> <?= $videoId ? 'is-video' : '' ?>" data-slide data-slide-index="<?= $i ?>">
                    <?php if ($visualUrl): ?><img class="notice-wall-card-image" src="<?= e($visualUrl) ?>" alt="" loading="lazy"><div class="notice-wall-card-shade"></div><?php else: ?><div class="notice-wall-card-decoration"><i data-lucide="megaphone"></i></div><?php endif; ?>
                    <?php if ($videoId): ?><span class="notice-wall-play" aria-hidden="true"><i data-lucide="play"></i></span><?php endif; ?>
                    <div class="notice-wall-card-content">
                        <div class="notice-wall-card-badges"><span class="notice-wall-priority"><?= e($priorityLabels[$priority] ?? 'Comunicado') ?></span><?php if (!empty($notice['pinned'])): ?><span class="notice-wall-pin"><i data-lucide="pin"></i> Fixado</span><?php endif; ?></div>
                        <div class="notice-wall-card-copy"><span class="notice-wall-category"><?= e($categoryLabels[$category] ?? 'Geral') ?></span><h3><?= e((string)($notice['title'] ?? 'Aviso')) ?></h3><p><?= e($summary) ?></p></div>
                        <div class="notice-wall-card-footer"><span><i data-lucide="calendar-days"></i><?= date('d/m/Y', strtotime($published)) ?></span><button type="button" class="notice-wall-cta" data-notice-open="notice-modal-<?= (int)$notice['id'] ?>"><?= e($actionLabel) ?> <i data-lucide="<?= e($actionIcon) ?>"></i></button></div>
                    </div>
                </article>
            <?php endforeach; ?>
            </div>
        </div>
        <button class="notice-wall-arrow next" type="button" data-carousel-next aria-label="Próximo aviso"><i data-lucide="arrow-right"></i></button>
    </div>

    <?php if (count($notices)>1): ?><div class="notice-wall-dots"><?php foreach ($notices as $i=>$_): ?><button type="button" data-carousel-dot="<?= $i ?>" class="<?= $i===0?'active':'' ?>" aria-label="Ir para o aviso <?= $i+1 ?>"></button><?php endforeach; ?></div><?php endif; ?>
    <footer class="notice-wall-summary"><span class="notice-wall-summary-icon"><i data-lucide="bell"></i></span><div><strong><?= $totalActive===1?'1 aviso disponível':$totalActive.' avisos disponíveis' ?></strong><span>Use as setas ou deslize para visualizar todos os comunicados ativos.</span></div><a href="<?= base_url('avisos') ?>" class="btn-secondary"><i data-lucide="list"></i> Abrir central</a></footer>
</section>

<?php foreach ($notices as $notice):
    $videoId = $youtubeId($notice['youtube_url'] ?? null);
    $attachments = is_array($notice['attachments'] ?? null) ? $notice['attachments'] : [];
?>
<div class="notice-detail-modal" id="notice-modal-<?= (int)$notice['id'] ?>" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="notice-modal-title-<?= (int)$notice['id'] ?>">
    <div class="notice-detail-backdrop" data-notice-close></div>
    <article class="notice-detail-dialog">
        <header><div><span>Comunicado escolar</span><h2 id="notice-modal-title-<?= (int)$notice['id'] ?>"><?= e((string)$notice['title']) ?></h2></div><button type="button" class="icon-button" data-notice-close aria-label="Fechar"><i data-lucide="x"></i></button></header>
        <div class="notice-detail-body">
            <?php if ($videoId): ?><div class="notice-video-frame"><iframe src="https://www.youtube-nocookie.com/embed/<?= e($videoId) ?>" title="<?= e((string)$notice['title']) ?>" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div><?php elseif (!empty($notice['banner_path'])): ?><img class="notice-detail-banner" src="<?= e(base_url((string)$notice['banner_path'])) ?>" alt=""><?php endif; ?>
            <div class="notice-detail-content"><?= nl2br(e((string)($notice['content'] ?? ''))) ?></div>
            <?php if ($attachments): ?><?php component('media/attachment-carousel', ['attachments' => $attachments, 'title' => 'Imagens do aviso']); ?><section class="notice-detail-files"><h3><i data-lucide="paperclip"></i> Arquivos</h3><?php foreach ($attachments as $file): ?><a href="<?= e(base_url((string)$file['relative_path'])) ?>" target="_blank" rel="noopener"><i data-lucide="file-down"></i><span><?= e((string)$file['original_name']) ?></span><strong>Abrir</strong></a><?php endforeach; ?></section><?php endif; ?>
            <?php if ($videoId): ?><a class="btn-primary notice-youtube-link" href="<?= e((string)$notice['youtube_url']) ?>" target="_blank" rel="noopener"><i data-lucide="external-link"></i> Abrir no YouTube</a><?php endif; ?>
        </div>
    </article>
</div>
<?php endforeach; ?>
<script src="<?= base_url('assets/js/pages/notices-carousel.js?v=3032') ?>"></script>
