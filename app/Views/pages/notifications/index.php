<section class="notifications-page">
    <div class="notifications-page__header">
        <div>
            <span class="notifications-page__eyebrow">Central de Comunicação Escolar</span>
            <h1>Notificações</h1>
            <p>Acompanhe eventos dos seus alunos, avisos da gestão, ocorrências e sinais da Inteligência Escolar.</p>
        </div>
        <?php if (($unreadCount ?? 0) > 0): ?>
            <form action="<?= base_url('notificacoes/ler-todas') ?>" method="post">
                <button class="btn btn-secondary" type="submit"><i data-lucide="check-check"></i> Marcar todas como lidas</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (!empty($notificationSuccess)): ?><div class="alert alert-success"><?= e((string)$notificationSuccess) ?></div><?php endif; ?>

    <div class="notifications-page__summary"><strong><?= (int)($unreadCount ?? 0) ?></strong><span>não lida(s)</span></div>

    <?php
    $categories = ['ALL'=>'Todas','MONITORING'=>'Meus alunos','INTELLIGENCE'=>'Inteligência','OCCURRENCE'=>'Ocorrências','MANAGEMENT'=>'Gestão','SYSTEM'=>'Sistema'];
    $statuses = ['ALL'=>'Todas','UNREAD'=>'Não lidas','READ'=>'Lidas'];
    ?>
    <form class="notifications-filters" method="get" action="<?= base_url('notificacoes') ?>">
        <label>Categoria
            <select name="categoria">
                <?php foreach ($categories as $code=>$label): ?><option value="<?= e($code) ?>" <?= ($selectedCategory ?? 'ALL')===$code?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?>
            </select>
        </label>
        <label>Status
            <select name="status">
                <?php foreach ($statuses as $code=>$label): ?><option value="<?= e($code) ?>" <?= ($selectedStatus ?? 'ALL')===$code?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?>
            </select>
        </label>
        <button class="btn btn-primary" type="submit"><i data-lucide="filter"></i> Filtrar</button>
        <a class="btn btn-secondary" href="<?= base_url('notificacoes') ?>">Limpar</a>
    </form>

    <?php if (empty($notifications)): ?>
        <div class="notifications-empty"><i data-lucide="bell-off"></i><h2>Nenhuma notificação</h2><p>Não há itens correspondentes aos filtros selecionados.</p></div>
    <?php else: ?>
        <div class="notifications-list">
            <?php foreach ($notifications as $notification): $severity=strtolower((string)($notification['severity']??'INFO')); ?>
                <article class="notification-card notification-card--<?= e($severity) ?> <?= empty($notification['is_read'])?'is-unread':'' ?>">
                    <div class="notification-card__icon"><i data-lucide="<?= e((string)($notification['severity_icon']??'info')) ?>"></i></div>
                    <div class="notification-card__content">
                        <div class="notification-card__topline">
                            <span class="notification-card__severity"><?= e((string)($categories[$notification['category']??'SYSTEM']??'Sistema')) ?> · <?= e((string)($notification['severity_label']??'Informativa')) ?></span>
                            <?php if (empty($notification['is_read'])): ?><span class="notification-card__unread">Não lida</span><?php endif; ?>
                        </div>
                        <h2><?= e((string)($notification['title']??'Notificação')) ?></h2>
                        <p><?= e((string)($notification['message']??'')) ?></p>
                        <div class="notification-card__footer">
                            <time><?= e((string)($notification['created_at']??'')) ?></time>
                            <div class="notification-card__actions">
                                <a class="btn btn-sm btn-secondary" href="<?= e((string)($notification['destination_url']??base_url('notificacoes'))) ?>">Abrir</a>
                                <?php if (empty($notification['is_read'])): ?>
                                    <form action="<?= base_url('notificacoes/ler') ?>" method="post"><input type="hidden" name="id" value="<?= (int)($notification['id']??0) ?>"><button class="btn btn-sm btn-primary" type="submit">Marcar como lida</button></form>
                                <?php endif; ?>
                                <form action="<?= base_url('notificacoes/excluir') ?>" method="post"><input type="hidden" name="id" value="<?= (int)($notification['id']??0) ?>"><button class="btn btn-sm btn-danger" type="submit" title="Excluir notificação"><i data-lucide="trash-2"></i></button></form>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
