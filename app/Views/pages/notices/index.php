<?php

use App\Auth\Permissions;
use App\Core\Authorization;

$notices = $notices ?? [];
$priorities = $priorities ?? [];
$targets = $targets ?? [];
$categories = $categories ?? [];

$totalActive = (int) ($totalActive ?? 0);
$totalScheduled = (int) ($totalScheduled ?? 0);
$totalExpired = (int) ($totalExpired ?? 0);

$canManageNotices = Authorization::can(
    Permissions::NOTICES_MANAGE
);

component('base/page-header', [
    'title' => 'Avisos da Gestão',
    'subtitle' => $canManageNotices
        ? 'Gerencie os comunicados da comunidade escolar.'
        : 'Consulte os comunicados da gestão escolar.',
]);

$priorityClasses = [
    'INFO' => 'info',
    'IMPORTANT' => 'important',
    'HIGH' => 'high',
    'URGENT' => 'urgent',
];

$priorityIcons = [
    'INFO' => 'info',
    'IMPORTANT' => 'triangle-alert',
    'HIGH' => 'badge-alert',
    'URGENT' => 'siren',
];

$youtubeId = static function (?string $url): ?string {
    $url = trim((string) $url);
    if ($url === '') {
        return null;
    }

    $patterns = [
        '~youtu\.be/([A-Za-z0-9_-]{6,})~',
        '~youtube\.com/(?:watch\?[^#]*v=|embed/|shorts/)([A-Za-z0-9_-]{6,})~',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }

    return null;
};

$fileIcon = static function (array $file): string {
    $mime = strtolower((string) ($file['mime_type'] ?? ''));
    $extension = strtolower((string) ($file['extension'] ?? pathinfo((string) ($file['original_name'] ?? ''), PATHINFO_EXTENSION)));

    if (str_starts_with($mime, 'image/')) return 'image';
    if (str_starts_with($mime, 'audio/')) return 'audio-lines';
    if (str_starts_with($mime, 'video/')) return 'video';
    if ($extension === 'pdf') return 'file-text';
    if (in_array($extension, ['xls', 'xlsx', 'csv'], true)) return 'sheet';
    if (in_array($extension, ['doc', 'docx', 'odt'], true)) return 'file-type-2';
    if (in_array($extension, ['zip', 'rar', '7z'], true)) return 'archive';
    return 'file';
};

?>

<?php if (!empty($noticeSuccess)): ?>

    <div class="alert alert-success">
        <?= e($noticeSuccess) ?>
    </div>

<?php endif; ?>

<?php if (!empty($noticeError)): ?>

    <div class="alert alert-danger">
        <?= e($noticeError) ?>
    </div>

<?php endif; ?>

<div class="notices-page">

    <?php if ($canManageNotices): ?>

        <div class="notices-page-actions">

            <a
                href="<?= base_url('avisos/novo') ?>"
                class="btn-primary"
            >
                <i data-lucide="plus"></i>
                Novo aviso
            </a>

        </div>

    <?php endif; ?>

    <div class="notices-page-kpis">

        <div class="active">

            <span>Ativos</span>

            <strong>
                <?= $totalActive ?>
            </strong>

            <small>
                Visíveis atualmente
            </small>

        </div>

        <div class="scheduled">

            <span>Agendados</span>

            <strong>
                <?= $totalScheduled ?>
            </strong>

            <small>
                Publicações futuras
            </small>

        </div>

        <div class="expired">

            <span>Expirados</span>

            <strong>
                <?= $totalExpired ?>
            </strong>

            <small>
                Fora do período
            </small>

        </div>

        <div>

            <span>Total</span>

            <strong>
                <?= count($notices) ?>
            </strong>

            <small>
                Avisos cadastrados
            </small>

        </div>

    </div>

    <?php if (empty($notices)): ?>

        <div class="card">

            <div class="activity-empty">

                <i data-lucide="message-square-off"></i>

                <strong>
                    Nenhum aviso cadastrado
                </strong>

                <p>
                    Não existem comunicados disponíveis no momento.
                </p>

                <?php if ($canManageNotices): ?>

                    <a
                        href="<?= base_url('avisos/novo') ?>"
                        class="btn-primary"
                    >
                        Criar aviso
                    </a>

                <?php endif; ?>

            </div>

        </div>

    <?php else: ?>

        <div class="notices-grid">

            <?php foreach ($notices as $notice): ?>

                <?php

                $priority = (string) (
                    $notice['priority'] ?? 'INFO'
                );

                $priorityClass = $priorityClasses[$priority]
                    ?? 'info';

                $priorityIcon = $priorityIcons[$priority]
                    ?? 'info';

                $isPinned = (int) (
                    $notice['pinned'] ?? 0
                ) === 1;

                $isActive = (int) (
                    $notice['active'] ?? 0
                ) === 1;

                $publishedAt = !empty(
                    $notice['published_at']
                )
                    ? strtotime(
                        (string) $notice['published_at']
                    )
                    : false;

                $expiresAt = !empty(
                    $notice['expires_at']
                )
                    ? strtotime(
                        (string) $notice['expires_at']
                    )
                    : false;

                $isScheduled = $publishedAt !== false
                    && $publishedAt > time();

                $isExpired = $expiresAt !== false
                    && $expiresAt < time();

                $target = (string) (
                    $notice['target'] ?? 'ALL'
                );

                $attachments = is_array($notice['attachments'] ?? null)
                    ? $notice['attachments']
                    : [];

                $youtubeUrl = trim((string) ($notice['youtube_url'] ?? ''));
                $videoId = $youtubeId($youtubeUrl);
                $hasResources = $attachments !== [] || $videoId !== null;

                $bannerPath = trim((string) ($notice['banner_path'] ?? ''));
                $bannerUrl = $bannerPath !== ''
                    ? base_url($bannerPath)
                    : ($videoId !== null
                        ? 'https://img.youtube.com/vi/' . rawurlencode($videoId) . '/hqdefault.jpg'
                        : '');

                $category = strtoupper((string) ($notice['category'] ?? 'GENERAL'));
                // Avisos antigos classificados como URGENT continuam legíveis,
                // mas a urgência agora pertence exclusivamente à prioridade.
                if ($category === 'URGENT') {
                    $category = 'GENERAL';
                }
                $categoryLabel = $categories[$category] ?? 'Geral';

                $targetLabel = $targets[$target]
                    ?? 'Todos';

                if (
                    $target === 'CLASS'
                    && !empty($notice['target_class_name'])
                ) {
                    $targetLabel = trim(
                        (string) $notice['target_class_name']
                        . (
                            !empty($notice['target_class_year'])
                                ? ' • '
                                    . $notice['target_class_year']
                                    . 'º Ano'
                                : ''
                        )
                        . (
                            !empty($notice['target_class_shift'])
                                ? ' • '
                                    . $notice['target_class_shift']
                                : ''
                        )
                    );
                }

                ?>

                <article
                    class="
                        notice-card
                        notice-card-<?= e($priorityClass) ?>
                        <?= !$isActive ? 'is-inactive' : '' ?>
                    "
                >

                    <?php if ($bannerUrl !== ''): ?>

                        <div class="notice-card-media">
                            <img
                                src="<?= e($bannerUrl) ?>"
                                alt="Imagem do cartão: <?= e((string) ($notice['title'] ?? 'Aviso')) ?>"
                                loading="lazy"
                            >

                            <div class="notice-card-media-badges">
                                <span class="notice-category-badge">
                                    <i data-lucide="tag"></i>
                                    <?= e($categoryLabel) ?>
                                </span>

                                <?php if ($videoId !== null): ?>
                                    <button
                                        type="button"
                                        class="notice-card-media-play"
                                        data-notice-video-open="notice-video-<?= (int) ($notice['id'] ?? 0) ?>"
                                        aria-label="Assistir ao vídeo de <?= e((string) ($notice['title'] ?? 'aviso')) ?>"
                                    >
                                        <i data-lucide="play"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php else: ?>

                        <div class="notice-card-category-row">
                            <span class="notice-category-badge">
                                <i data-lucide="tag"></i>
                                <?= e($categoryLabel) ?>
                            </span>
                        </div>

                    <?php endif; ?>

                    <div class="notice-card-header">

                        <div class="notice-card-icon">

                            <i
                                data-lucide="<?= e($priorityIcon) ?>"
                            ></i>

                        </div>

                        <div class="notice-card-header-content">

                            <span>
                                <?= e(
                                    $priorities[$priority]
                                    ?? 'Informativo'
                                ) ?>
                            </span>

                            <h3>
                                <?= e(
                                    $notice['title']
                                    ?? 'Aviso'
                                ) ?>
                            </h3>

                        </div>

                        <?php if ($isPinned): ?>

                            <span class="notice-card-pinned">

                                <i data-lucide="pin"></i>

                                Fixado

                            </span>

                        <?php endif; ?>

                    </div>

                    <div class="notice-card-content">

                        <?= nl2br(
                            e(
                                $notice['content']
                                ?? ''
                            )
                        ) ?>

                    </div>

                    <div class="notice-card-meta">

                        <div>

                            <i data-lucide="users"></i>

                            <span>
                                <?= e($targetLabel) ?>
                            </span>

                        </div>

                        <div>

                            <i data-lucide="user-round"></i>

                            <span>
                                
                                 <strong>
                                     <?= e(
                                         $notice['created_by_name']
                                         ?? 'Sistema'
                                     ) ?>
                                 </strong>
                        
                                 <?php if (!empty($notice['created_by_role'])): ?>
                                    
                                    <small>
                                        • <?= e($notice['created_by_role']) ?>
                                    </small>

                                <?php endif; ?>
                        
                            </span>

                        </div>

                        <?php if ($publishedAt !== false): ?>

                            <div>

                                <i data-lucide="calendar-clock"></i>

                                <span>
                                    Publicação:
                                    <?= date(
                                        'd/m/Y H:i',
                                        $publishedAt
                                    ) ?>
                                </span>

                            </div>

                        <?php else: ?>

                            <div>

                                <i data-lucide="calendar-check"></i>

                                <span>
                                    Publicação imediata
                                </span>

                            </div>

                        <?php endif; ?>

                        <?php if ($expiresAt !== false): ?>

                            <div>

                                <i data-lucide="calendar-x"></i>

                                <span>
                                    Expira:
                                    <?= date(
                                        'd/m/Y H:i',
                                        $expiresAt
                                    ) ?>
                                </span>

                            </div>

                        <?php endif; ?>

                    </div>

                    <?php if ($attachments !== []): ?>
                        <?php component('media/attachment-carousel', ['attachments' => $attachments, 'title' => 'Imagens do aviso']); ?>
                    <?php endif; ?>

                    <?php if ($hasResources): ?>

                        <section class="notice-card-resources" aria-label="Arquivos e recursos do aviso">

                            <div class="notice-card-resources-title">
                                <i data-lucide="paperclip"></i>
                                <strong>Arquivos e recursos</strong>
                            </div>

                            <div class="notice-card-resource-list">

                                <?php if ($videoId !== null): ?>

                                    <button
                                        type="button"
                                        class="notice-resource-item notice-resource-video"
                                        data-notice-video-open="notice-video-<?= (int) ($notice['id'] ?? 0) ?>"
                                    >
                                        <span class="notice-resource-icon">
                                            <i data-lucide="circle-play"></i>
                                        </span>
                                        <span class="notice-resource-copy">
                                            <strong>Vídeo no YouTube</strong>
                                            <small>Assista sem sair do sistema</small>
                                        </span>
                                        <span class="notice-resource-action">Assistir</span>
                                    </button>

                                <?php endif; ?>

                                <?php foreach ($attachments as $attachment): ?>

                                    <a
                                        class="notice-resource-item"
                                        href="<?= e(base_url((string) ($attachment['relative_path'] ?? ''))) ?>"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        <span class="notice-resource-icon">
                                            <i data-lucide="<?= e($fileIcon($attachment)) ?>"></i>
                                        </span>
                                        <span class="notice-resource-copy">
                                            <strong><?= e((string) ($attachment['original_name'] ?? 'Arquivo')) ?></strong>
                                            <small>Arquivo anexado ao aviso</small>
                                        </span>
                                        <span class="notice-resource-action">Abrir</span>
                                    </a>

                                <?php endforeach; ?>

                            </div>

                        </section>

                    <?php endif; ?>

                    <div class="notice-card-status">

                        <?php if (!$isActive): ?>

                            <span class="badge badge-warning">
                                Arquivado
                            </span>

                        <?php elseif ($isExpired): ?>

                            <span class="badge badge-warning">
                                Expirado
                            </span>

                        <?php elseif ($isScheduled): ?>

                            <span class="badge badge-warning">
                                Agendado
                            </span>

                        <?php else: ?>

                            <span class="badge badge-success">
                                Publicado
                            </span>

                        <?php endif; ?>

                    </div>

                    <?php if ($canManageNotices): ?>

                        <div class="notice-card-actions">

                            <a
                                href="<?= base_url(
                                    'avisos/editar?id='
                                    . (int) ($notice['id'] ?? 0)
                                ) ?>"
                                class="btn-secondary"
                            >
                                Editar
                            </a>

                            <?php if ($isPinned): ?>

                                <form
                                    method="POST"
                                    action="<?= base_url(
                                        'avisos/desafixar'
                                    ) ?>"
                                >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int) (
                                            $notice['id'] ?? 0
                                        ) ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn-secondary"
                                    >
                                        Desafixar
                                    </button>

                                </form>

                            <?php else: ?>

                                <form
                                    method="POST"
                                    action="<?= base_url(
                                        'avisos/fixar'
                                    ) ?>"
                                >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int) (
                                            $notice['id'] ?? 0
                                        ) ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn-secondary"
                                    >
                                        Fixar
                                    </button>

                                </form>

                            <?php endif; ?>

                            <?php if ($isActive): ?>

                                <form
                                    method="POST"
                                    action="<?= base_url(
                                        'avisos/arquivar'
                                    ) ?>"
                                >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int) (
                                            $notice['id'] ?? 0
                                        ) ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn-secondary"
                                    >
                                        Arquivar
                                    </button>

                                </form>

                            <?php else: ?>

                                <form
                                    method="POST"
                                    action="<?= base_url(
                                        'avisos/ativar'
                                    ) ?>"
                                >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int) (
                                            $notice['id'] ?? 0
                                        ) ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn-primary"
                                    >
                                        Ativar
                                    </button>

                                </form>

                            <?php endif; ?>

                            <form
                                method="POST"
                                action="<?= base_url(
                                    'avisos/excluir'
                                ) ?>"
                                onsubmit="
                                    return confirm(
                                        'Deseja realmente excluir este aviso?'
                                    );
                                "
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) (
                                        $notice['id'] ?? 0
                                    ) ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn-danger"
                                >
                                    Excluir
                                </button>

                            </form>

                        </div>

                    <?php endif; ?>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<?php foreach ($notices as $notice): ?>
    <?php
    $youtubeUrl = trim((string) ($notice['youtube_url'] ?? ''));
    $videoId = $youtubeId($youtubeUrl);
    if ($videoId === null) continue;
    ?>

    <div
        class="notice-video-modal"
        id="notice-video-<?= (int) ($notice['id'] ?? 0) ?>"
        aria-hidden="true"
        role="dialog"
        aria-modal="true"
        aria-labelledby="notice-video-title-<?= (int) ($notice['id'] ?? 0) ?>"
    >
        <div class="notice-video-modal-backdrop" data-notice-video-close></div>

        <article class="notice-video-modal-dialog">
            <header>
                <div>
                    <span>Vídeo do aviso</span>
                    <h2 id="notice-video-title-<?= (int) ($notice['id'] ?? 0) ?>">
                        <?= e((string) ($notice['title'] ?? 'Vídeo')) ?>
                    </h2>
                </div>

                <button type="button" class="icon-button" data-notice-video-close aria-label="Fechar vídeo">
                    <i data-lucide="x"></i>
                </button>
            </header>

            <div class="notice-video-modal-body">
                <div class="notice-video-embed">
                    <iframe
                        data-video-src="https://www.youtube-nocookie.com/embed/<?= e($videoId) ?>?rel=0"
                        src=""
                        title="<?= e((string) ($notice['title'] ?? 'Vídeo')) ?>"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                    ></iframe>
                </div>

                <a class="btn-primary" href="<?= e($youtubeUrl) ?>" target="_blank" rel="noopener">
                    <i data-lucide="external-link"></i>
                    Abrir no YouTube
                </a>
            </div>
        </article>
    </div>

<?php endforeach; ?>

<script src="<?= base_url('assets/js/pages/notices-index.js?v=3033') ?>"></script>
