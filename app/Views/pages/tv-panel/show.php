<?php
use App\Helpers\DateHelper;

$categoryLabels = [
    'GENERAL' => 'Geral', 'PEDAGOGICAL' => 'Pedagógico', 'EVENTS' => 'Evento',
    'CALENDAR' => 'Calendário', 'MEETINGS' => 'Reunião', 'MANAGEMENT' => 'Gestão',
];
$eventTypeLabels = [
    'EVENT' => 'Evento', 'HOLIDAY' => 'Feriado', 'MEETING' => 'Reunião',
    'EXAM' => 'Avaliação', 'PROJECT' => 'Projeto', 'ACTIVITY' => 'Atividade',
    'SCHOOL' => 'Escolar', 'OTHER' => 'Outro',
];
$slides = (array)($tvConfig['slides'] ?? []);
$ranking = array_slice((array)($ranking ?? []), 0, 8);
$freq = (array)($schoolFrequencyToday ?? []);
$present = (int)($freq['presentes'] ?? $freq['present'] ?? 0);
$absent = (int)($freq['faltas'] ?? $freq['absent'] ?? 0);
$justified = (int)($freq['justificadas'] ?? $freq['justified'] ?? 0);
$medical = (int)($freq['atestados'] ?? $freq['medical'] ?? 0);
$bus = (int)($freq['onibus'] ?? $freq['bus'] ?? 0);
$total = max(1, $present + $absent + $justified + $medical + $bus);
$percentage = (float)($freq['percentage'] ?? $freq['attendance_percentage'] ?? (($present / $total) * 100));
$notices = array_slice(array_values(array_filter((array)($activeNotices ?? []), static fn(array $notice): bool => trim((string)($notice['banner_path'] ?? '')) !== '')), 0, 4);
$upcoming = array_slice((array)($schoolCalendar['upcoming'] ?? []), 0, 24);
$eventPages = $upcoming === [] ? [[]] : array_chunk($upcoming, 4);
$schoolName = 'Sistema de Gerenciamento Escolar';
$theme = in_array((string)($tvConfig['theme'] ?? 'light'), ['light', 'dark', 'auto'], true)
    ? (string)$tvConfig['theme'] : 'light';
$smart = (array)($intelligentContent ?? []);
$smartEnabled = !empty($tvConfig['intelligentContentEnabled']);
$tipTitle = (string)(($smartEnabled && !empty($tvConfig['autoDailyTip'])) ? ($smart['dailyTipTitle'] ?? 'Dica do dia') : ($tvConfig['dailyTipTitle'] ?? 'Dica do dia'));
$tipText = (string)(($smartEnabled && !empty($tvConfig['autoDailyTip'])) ? ($smart['dailyTipText'] ?? '') : ($tvConfig['dailyTipText'] ?? 'Pequenas atitudes constroem grandes resultados.'));
$tipFooter = (string)($tvConfig['dailyTipFooter'] ?? 'Contamos com você! Sua presença transforma o hoje e constrói o amanhã.');
$supportTitle = (string)(($smartEnabled && !empty($tvConfig['autoSupportText'])) ? ($smart['supportTitle'] ?? 'Contamos com você!') : ($tvConfig['supportTitle'] ?? 'Contamos com você!'));
$supportText = (string)(($smartEnabled && !empty($tvConfig['autoSupportText'])) ? ($smart['supportText'] ?? '') : ($tvConfig['supportText'] ?? $tipFooter));
$motivationText = (string)(($smartEnabled && !empty($tvConfig['autoMotivation'])) ? ($smart['motivationText'] ?? '') : ($tvConfig['motivationText'] ?? 'Cada presença representa uma nova oportunidade de aprender.'));
$motivationSubtitle = (string)(($smartEnabled && !empty($tvConfig['autoMotivation'])) ? ($smart['motivationSubtitle'] ?? '') : ($tvConfig['motivationSubtitle'] ?? 'Educação se faz com presença, respeito e compromisso.'));
$institutionalSlogan = (string)(($smartEnabled && !empty($tvConfig['autoInstitutionalSlogan'])) ? ($smart['institutionalSlogan'] ?? '') : ($tvConfig['institutionalSlogan'] ?? 'Educação, presença e futuro.'));
$colorTheme = in_array((string)($tvConfig['colorTheme'] ?? 'green'), ['green','blue','purple','red','automatic'], true) ? (string)$tvConfig['colorTheme'] : 'green';
$animationsEnabled = !array_key_exists('animationsEnabled', $tvConfig) || !empty($tvConfig['animationsEnabled']);
$animationDuration = max(300, min(2000, (int)($tvConfig['animationDuration'] ?? 800)));
$footerSlogan = (string)(($smartEnabled && !empty($tvConfig['autoFooterSlogan'])) ? ($smart['footerSlogan'] ?? '') : ($tvConfig['footerSlogan'] ?? 'Cada presença conta. Cada aluno importa.'));
$tipBannerPath = trim((string)($tvConfig['dailyTipBanner'] ?? ''));
$tipBannerUrl = $tipBannerPath !== '' ? base_url($tipBannerPath) : '';
$svgIcon = static function (string $name): string {
    $paths = [
        'trophy' => '<path d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0V4Z"/><path d="M7 6H4v1a4 4 0 0 0 4 4M17 6h3v1a4 4 0 0 1-4 4"/>',
        'activity' => '<path d="M3 12h4l2-7 4 14 2-7h6"/>',
        'megaphone' => '<path d="m3 11 18-5v12L3 13v-2Z"/><path d="M11.6 16.8 13 21H7l-1.5-6.5"/>',
        'star' => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.2L5.8 21 7 14.2 2 9.3l6.9-1L12 2Z"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/>',
        'chart' => '<path d="M4 19V9M10 19V5M16 19v-8M22 19H2"/>',
        'sparkles' => '<path d="m12 3 1.2 3.8L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.2L12 3Z"/><path d="m19 14 .7 2.3L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14Z"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'user-x' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m17 8 5 5m0-5-5 5"/>',
        'shield' => '<path d="M20 13c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V5l8-3 8 3v8Z"/><path d="m9 12 2 2 4-4"/>',
        'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h6"/>',
        'bus' => '<rect x="4" y="3" width="16" height="16" rx="2"/><path d="M4 11h16M8 19v2M16 19v2M8 7h.01M16 7h.01"/>',
    ];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['sparkles']) . '</svg>';
};
?>
<div class="tv-app <?= $animationsEnabled ? 'tv-animations-enabled' : 'tv-animations-disabled' ?>" data-tv-app data-theme="<?= e($theme) ?>" data-color-theme="<?= e($colorTheme) ?>" data-animation-duration="<?= $animationDuration ?>" data-duration="<?= (int)($tvConfig['duration'] ?? 15) ?>" data-transition="<?= e((string)($tvConfig['transition'] ?? 'fade')) ?>" data-refresh="<?= (int)($tvConfig['autoRefresh'] ?? 60) ?>">
<header class="tv-topbar">
    <div class="tv-brand"><img src="<?= asset('uploads/schools/school-logo.png') ?>" alt="Logotipo da escola"><div><strong><?= e($schoolName) ?></strong><span><?= e($institutionalSlogan) ?></span></div></div>
    <div class="tv-live"><span class="tv-live-dot"></span> PAINEL INSTITUCIONAL</div>
    <?php if (!empty($tvConfig['showClock'])): ?><div class="tv-clock"><strong data-tv-clock>--:--</strong><span data-tv-date>--</span></div><?php endif; ?>
</header>
<main class="tv-stage">
<?php if (in_array('ranking', $slides, true)): ?>
<section class="tv-slide is-active tv-slide-ranking" data-tv-slide>
    <div class="slide-heading"><div class="slide-icon"><?= $svgIcon('trophy') ?></div><div><span>DESEMPENHO DO DIA</span><h1>Ranking diário de frequência por turma</h1><p>Qual é a turma de hoje? Presença faz a diferença!</p></div></div>
    <div class="ranking-layout">
        <div class="ranking-board">
            <div class="ranking-head"><span>Posição</span><span>Turma</span><span>Índice de presença</span><span>Situação</span></div>
            <?php if ($ranking === []): ?><div class="tv-empty">Ainda não há chamadas registradas para formar o ranking de hoje.</div><?php endif; ?>
            <?php foreach ($ranking as $i => $item): $has = (int)($item['has_attendance'] ?? 0) === 1; $pct = $has ? (float)($item['attendance_percentage'] ?? 0) : 0; $label = !$has ? 'Pendente' : ($pct >= 95 ? 'Excelente' : ($pct >= 85 ? 'Bom' : 'Atenção')); ?>
                <div class="ranking-row"><strong class="rank-medal"><?= $i < 3 ? ['🥇','🥈','🥉'][$i] : ($i + 1) . 'º' ?></strong><strong><?= e((string)($item['class_name'] ?? 'Turma')) ?></strong><div class="rank-progress"><span data-tv-bar style="--bar-width:<?= max(0, min(100, $pct)) ?>%"></span><b><?= number_format($pct, 0, ',', '.') ?>%</b></div><em class="rank-status rank-status-<?= e(strtolower($label)) ?>"><?= e($label) ?></em></div>
            <?php endforeach; ?>
        </div>
        <?php
        $winner = $ranking[0] ?? [];
        $winnerPhoto = trim((string)($winner['class_photo_path'] ?? ''));
        $winnerPhotoVersion = trim((string)($winner['class_photo_updated_at'] ?? ''));
        ?>
        <aside class="ranking-highlight <?= $winnerPhoto !== '' ? 'ranking-highlight-with-photo' : 'ranking-highlight-without-photo' ?>">
            <span>DESTAQUE DO DIA</span>
            <?php if ($winnerPhoto !== ''): ?>
                <div class="winner-class-photo">
                    <img src="<?= asset($winnerPhoto) ?><?= $winnerPhotoVersion !== '' ? '?v=' . urlencode($winnerPhotoVersion) : '' ?>" alt="Foto da turma <?= e((string)($winner['class_name'] ?? 'vencedora')) ?>">
                    <div class="winner-photo-badge">🥇 CAMPEÃ DO DIA</div>
                </div>
            <?php else: ?>
                <div class="big-trophy">🏆</div>
            <?php endif; ?>
            <h2><?= e((string)($winner['class_name'] ?? 'Nossa escola')) ?></h2>
            <?php if ($winner !== []): ?><strong class="winner-percentage"><?= number_format((float)($winner['attendance_percentage'] ?? 0), 1, ',', '.') ?>% de presença</strong><?php endif; ?>
            <p><?= e((string)(($smartEnabled && !empty($tvConfig['autoHighlightMessage'])) ? ($smart['highlightMessage'] ?? $smart['automaticMessage'] ?? 'Parabéns pelo compromisso com a presença e com a aprendizagem!') : 'Parabéns pelo compromisso com a presença e com a aprendizagem!')) ?></p>
            <div class="motivation-box"><?= e($footerSlogan) ?></div>
        </aside>
    </div>
</section>
<?php endif; ?>

<?php if (in_array('frequency', $slides, true)): ?>
<section class="tv-slide tv-slide-frequency" data-tv-slide>
    <div class="slide-heading"><div class="slide-icon"><?= $svgIcon('activity') ?></div><div><span>PANORAMA GERAL</span><h1>Resumo de frequência da escola hoje</h1><p>Dados atualizados automaticamente pelo sistema.</p></div></div>
    <div class="frequency-grid">
        <?php foreach ([
            ['users','Presenças',$present,'success'],
            ['user-x','Faltas',$absent,'danger'],
            ['shield','Faltas justificadas',$justified,'warning'],
            ['file','Atestados',$medical,'info'],
            ['bus','Falta de ônibus',$bus,'purple']
        ] as $card): $part = ($card[2] / $total) * 100; ?>
            <article class="frequency-card frequency-<?= $card[3] ?>"><span><?= $svgIcon($card[0]) ?></span><h2><?= e($card[1]) ?></h2><strong><?= number_format((int)$card[2], 0, ',', '.') ?></strong><small>alunos</small><b><?= number_format($part, 0, ',', '.') ?>% do total</b><div><i data-tv-bar style="--bar-width:<?= min(100, $part) ?>%"></i></div></article>
        <?php endforeach; ?>
    </div>
    <div class="frequency-footer"><article><span>Índice de presença</span><strong><?= number_format($percentage, 1, ',', '.') ?>%</strong></article><article><span>Meta institucional</span><strong><?= number_format((float)($schoolGoals['frequency_goal'] ?? 95), 1, ',', '.') ?>%</strong></article><article><span>Turmas com chamada</span><strong><?= (int)($executive['doneClasses'] ?? 0) ?>/<?= (int)($executive['totalClasses'] ?? 0) ?></strong></article></div>
</section>
<?php endif; ?>

<?php if (in_array('notices', $slides, true)): ?>
<section class="tv-slide tv-slide-notices" data-tv-slide>
    <div class="slide-heading"><div class="slide-icon"><?= $svgIcon('megaphone') ?></div><div><span>COMUNICAÇÃO ESCOLAR</span><h1>Avisos e comunicados</h1><p>Fique por dentro do que acontece em nossa escola.</p></div></div>
    <div class="notice-grid notice-count-<?= max(1, min(4, count($notices))) ?> <?= count($notices) === 1 ? 'notice-grid-single' : '' ?>">
        <?php if (!$notices): ?>
            <article class="notice-card notice-green notice-placeholder"><div class="notice-card-content"><span>PAINEL-TV</span><h2>Nenhum banner publicado</h2><p>Cadastre um aviso ativo para o público <strong>Painel-TV</strong> e envie a Imagem do cartão.</p><small><?= date('d/m/Y') ?></small></div></article>
        <?php else: foreach ($notices as $notice): $bannerUrl = base_url((string)$notice['banner_path']); ?>
            <article class="notice-card notice-card-with-banner notice-image-only"><img class="notice-banner-image" src="<?= e($bannerUrl) ?>" alt="Banner do Painel TV"></article>
        <?php endforeach; endif; ?>
    </div>
</section>
<?php endif; ?>

<?php if (in_array('highlights', $slides, true)): ?>
<section class="tv-slide tv-slide-highlights" data-tv-slide>
    <div class="slide-heading"><div class="slide-icon"><?= $svgIcon('star') ?></div><div><span>ORGULHO DE SER ESCOLA</span><h1>Nossa escola em destaque</h1><p>Juntos, fazemos a diferença todos os dias.</p></div></div>
    <div class="highlight-grid highlight-grid-featured">
        <article class="evolution-card evolution-card-full">
            <header class="evolution-header"><div><?= $svgIcon('chart') ?></div><span><h2>Turmas com maior evolução</h2><p>Desempenho em relação ao período analisado</p></span></header>
            <div class="evolution-list">
                <?php if ($ranking === []): ?><div class="tv-empty">Ainda não há dados suficientes para destacar a evolução das turmas.</div><?php endif; ?>
                <?php foreach (array_slice($ranking, 0, 6) as $index => $item): $pct = (float)($item['attendance_percentage'] ?? 0); ?>
                    <div class="evolution-row <?= $index === 0 ? 'is-leader' : '' ?>">
                        <span class="evolution-position"><?= $index === 0 ? '🏅' : ($index + 1) . 'º' ?></span>
                        <b><?= e((string)($item['class_name'] ?? 'Turma')) ?></b>
                        <span class="evolution-progress"><i data-tv-bar style="--bar-width:<?= max(5, min(100, $pct)) ?>%"></i></span>
                        <strong>+<?= number_format($pct, 0, ',', '.') ?>%</strong>
                    </div>
                <?php endforeach; ?>
            </div>
            <footer>Evolução e desempenho das turmas em destaque</footer>
        </article>
        <div class="highlight-side-column">
            <?php if ($tipBannerUrl !== ''): ?>
                <article class="daily-tip daily-tip-banner"><img src="<?= e($tipBannerUrl) ?>" alt="Banner da Dica do Dia"></article>
            <?php else: ?>
                <article class="daily-tip daily-tip-text"><span><?= $svgIcon('sparkles') ?> <?= e(mb_strtoupper($tipTitle)) ?></span><blockquote>“<?= e($tipText) ?>”</blockquote><div class="plant">🌱</div></article>
            <?php endif; ?>
            <article class="support-message-card"><div class="support-message-icon"><?= $svgIcon('users') ?></div><div><h2><?= e($supportTitle) ?></h2><p><?= e($supportText) ?></p></div></article>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (in_array('hallOfFame', $slides, true)): ?>
<?php
$hallData = (array)($hallOfFame ?? []);
$hallCards = [
    [
        'key' => 'day',
        'label' => 'Campeã do dia',
        'icon' => '🏆',
        'message' => 'Melhor índice de presença registrado hoje.',
    ],
    [
        'key' => 'week',
        'label' => 'Campeã da semana',
        'icon' => '🥇',
        'message' => 'Melhor média acumulada na semana atual.',
    ],
    [
        'key' => 'evolution',
        'label' => 'Maior evolução',
        'icon' => '📈',
        'message' => 'Maior crescimento em relação à semana anterior.',
    ],
    [
        'key' => 'month',
        'label' => 'Destaque do mês',
        'icon' => '⭐',
        'message' => 'Melhor média de frequência no mês atual.',
    ],
];
?>
<section class="tv-slide tv-slide-hall" data-tv-slide>
    <div class="slide-heading"><div class="slide-icon"><?= $svgIcon('trophy') ?></div><div><span>RECONHECIMENTO INSTITUCIONAL</span><h1>Hall da Fama</h1><p><?= e((string)($smart['hallOfFameMessage'] ?? 'Resultados reais do dia, da semana e do mês.')) ?></p></div></div>
    <div class="hall-grid">
        <?php foreach ($hallCards as $index => $card):
            $item = is_array($hallData[$card['key']] ?? null) ? $hallData[$card['key']] : [];
            $photo = trim((string)($item['class_photo_path'] ?? ''));
            $photoVersion = trim((string)($item['class_photo_updated_at'] ?? ''));
            $isEvolution = $card['key'] === 'evolution';
            $value = $isEvolution
                ? (float)($item['evolution_percentage'] ?? 0)
                : (float)($item['attendance_percentage'] ?? 0);
        ?>
        <article class="hall-card <?= $index === 0 ? 'hall-card-featured' : '' ?> <?= $item === [] ? 'hall-card-empty' : '' ?>">
            <div class="hall-photo">
                <?php if ($photo !== ''): ?>
                    <img src="<?= asset($photo) ?><?= $photoVersion !== '' ? '?v=' . urlencode($photoVersion) : '' ?>" alt="Foto da turma <?= e((string)($item['class_name'] ?? '')) ?>">
                <?php else: ?>
                    <span><?= $card['icon'] ?></span>
                <?php endif; ?>
                <b><?= $card['icon'] ?> <?= e(mb_strtoupper($card['label'])) ?></b>
            </div>
            <div class="hall-content">
                <h2><?= e((string)($item['class_name'] ?? 'Aguardando resultado')) ?></h2>
                <strong><?= $item !== [] ? ($isEvolution ? '+' : '') . number_format($value, 1, ',', '.') . '%' : '—' ?></strong>
                <p><?= $item !== [] ? e($card['message']) : 'Ainda não há dados suficientes para esta conquista.' ?></p>
                <?php if ($item !== [] && $isEvolution): ?>
                    <small><?= number_format((float)($item['previous_percentage'] ?? 0), 1, ',', '.') ?>% → <?= number_format((float)($item['current_percentage'] ?? 0), 1, ',', '.') ?>%</small>
                <?php elseif ($item !== [] && !empty($item['attendance_days'])): ?>
                    <small><?= (int)$item['attendance_days'] ?> dia(s) de chamada no período</small>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (in_array('calendar', $slides, true)): ?>
<?php foreach ($eventPages as $eventPageIndex => $eventPage): ?>
<section class="tv-slide tv-slide-calendar" data-tv-slide>
    <div class="slide-heading"><div class="slide-icon"><?= $svgIcon('calendar') ?></div><div><span>AGENDA ESCOLAR<?= count($eventPages) > 1 ? ' • PÁGINA ' . ($eventPageIndex + 1) . ' DE ' . count($eventPages) : '' ?></span><h1>Próximos eventos</h1><p><?= e((string)($smart['calendarMessage'] ?? 'Datas importantes para toda a comunidade escolar.')) ?></p></div></div>
    <div class="calendar-list"><?php if (!$eventPage): ?><article><time><?= date('d') ?><small><?= e(DateHelper::shortMonth()) ?></small></time><div><span>Programação</span><h2>Nenhum evento cadastrado para os próximos dias</h2><p>Cadastre eventos no Calendário Escolar para exibi-los aqui.</p></div></article><?php else: foreach ($eventPage as $event): ?><article><time><?= date('d', strtotime((string)$event['start_date'])) ?><small><?= e(DateHelper::shortMonth((string)$event['start_date'])) ?></small></time><div><span><?= e($eventTypeLabels[strtoupper((string)($event['type'] ?? 'EVENT'))] ?? 'Evento') ?></span><h2><?= e((string)($event['title'] ?? '')) ?></h2><p><?= e((string)($event['description'] ?? $event['location'] ?? 'Programação escolar')) ?></p><?php if (!empty($event['location'])): ?><strong class="event-location">Local: <?= e((string)$event['location']) ?></strong><?php endif; ?></div></article><?php endforeach; endif; ?></div>
</section>
<?php endforeach; ?>
<?php endif; ?>

<?php if (in_array('indicators', $slides, true)): ?>
<section class="tv-slide tv-slide-indicators" data-tv-slide>
    <div class="slide-heading"><div class="slide-icon"><?= $svgIcon('chart') ?></div><div><span>INDICADORES GERAIS</span><h1>Nossa escola em números</h1><p>Um retrato atualizado da comunidade escolar.</p></div></div>
    <div class="indicator-grid"><?php foreach ([['Alunos matriculados',$totalStudents ?? 0,'👨‍🎓'],['Turmas ativas',$totalClasses ?? 0,'🏫'],['Frequência hoje',number_format($percentage,1,',','.') . '%','✅'],['Chamadas concluídas',$executive['doneClasses'] ?? 0,'📝'],['Ocorrências abertas',$occurrenceSummary['open'] ?? 0,'⚠️'],['Avisos ativos',$activeNoticesCount ?? 0,'📣']] as $it): ?><article><span><?= $it[2] ?></span><strong data-tv-counter><?= e((string)$it[1]) ?></strong><small><?= e($it[0]) ?></small></article><?php endforeach; ?></div>
</section>
<?php endif; ?>

<?php if (in_array('automaticMessages', $slides, true)): ?>
<section class="tv-slide tv-slide-smart-message" data-tv-slide>
    <div class="smart-message-center"><span class="smart-orbit">✨</span><small>MENSAGEM DO DIA</small><h1><?= e((string)($smart['automaticMessage'] ?? 'Cada presença fortalece nossa escola.')) ?></h1><p>Conteúdo atualizado automaticamente com os dados de <?= date('d/m/Y') ?>.</p></div>
</section>
<?php endif; ?>

<?php if (in_array('didYouKnow', $slides, true)): ?>
<section class="tv-slide tv-slide-didyouknow" data-tv-slide>
    <div class="didyouknow-card"><div class="didyouknow-icon">💡</div><span>SABIA QUE...</span><h1><?= e((string)($smart['didYouKnow'] ?? 'Cada presença contribui para uma escola mais forte.')) ?></h1><p>Informação gerada a partir dos indicadores reais da escola.</p></div>
</section>
<?php endif; ?>

<?php if (in_array('studentRecognition', $slides, true)): $studentStar = is_array($smart['studentRecognition'] ?? null) ? $smart['studentRecognition'] : []; ?>
<section class="tv-slide tv-slide-student-star" data-tv-slide>
    <div class="slide-heading"><div class="slide-icon"><?= $svgIcon('star') ?></div><div><span>RECONHECIMENTO</span><h1>Estrela da semana</h1><p>Valorizando presença, dedicação e compromisso com a aprendizagem.</p></div></div>
    <article class="student-star-card <?= $studentStar === [] ? 'is-empty' : '' ?>">
        <div class="student-star-photo">
            <?php if (!empty($studentStar['photo_path'])): ?><img src="<?= asset((string)$studentStar['photo_path']) ?><?= !empty($studentStar['photo_updated_at']) ? '?v='.urlencode((string)$studentStar['photo_updated_at']) : '' ?>" alt="Foto do estudante em destaque"><?php else: ?><span>⭐</span><?php endif; ?>
        </div>
        <div class="student-star-content"><small>⭐ FREQUÊNCIA EXEMPLAR</small><h2><?= e((string)($studentStar['student_name'] ?? 'Aguardando dados da semana')) ?></h2><p><?= e((string)($studentStar['class_name'] ?? 'O reconhecimento será exibido quando houver registros de frequência.')) ?></p><?php if ($studentStar !== []): ?><strong><?= number_format((float)($studentStar['attendance_percentage'] ?? 0),1,',','.') ?>% de presença</strong><em><?= (int)($studentStar['records'] ?? 0) ?> registro(s) considerados</em><?php endif; ?></div>
    </article>
</section>
<?php endif; ?>

<?php if (in_array('motivation', $slides, true)): ?>
<section class="tv-slide tv-slide-motivation" data-tv-slide><div class="motivation-center"><span>🌟</span><h1><?= e($motivationText) ?></h1><p><?= e($motivationSubtitle) ?></p><strong>SGE Escola</strong></div></section>
<?php endif; ?>
</main>
<footer class="tv-footer"><span>🏫 <?= e($schoolName) ?></span><div class="tv-footer-stats"><b>👨‍🎓 <?= number_format((int)($totalStudents ?? 0), 0, ',', '.') ?> alunos</b><b>📚 <?= number_format((int)($totalClasses ?? 0), 0, ',', '.') ?> turmas</b><b>✅ <?= number_format($percentage, 1, ',', '.') ?>% hoje</b></div><em><?= e($footerSlogan) ?></em><strong data-tv-progress>1 / 1</strong><div class="tv-dots" data-tv-dots></div><button type="button" data-tv-fullscreen>⛶ Tela cheia</button></footer>
</div>
