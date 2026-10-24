<?php

use App\Auth\Roles;
use App\Core\Session;

$loggedUser = Session::get('user');

if (!is_array($loggedUser)) {
    $loggedUser = [];
}

$userName = trim(
    (string) ($loggedUser['name'] ?? '')
);

if ($userName === '') {
    $userName = 'Usuário';
}

$userRole = Roles::normalize(
    (string) (
        $loggedUser['role']
        ?? Roles::TEACHER
    )
);

$userRoleLabel = trim(
    (string) (
        $loggedUser['role_label']
        ?? Roles::label($userRole)
    )
);

if ($userRoleLabel === '') {
    $userRoleLabel = Roles::label($userRole);
}

/*
 * Iniciais do usuário para o avatar.
 *
 * Exemplos:
 * Islandyo Santos -> IS
 * Maria -> MA
 */
$nameParts = preg_split(
    '/\s+/u',
    $userName,
    -1,
    PREG_SPLIT_NO_EMPTY
);

$userInitials = '';

if (!empty($nameParts)) {
    $firstName = (string) $nameParts[0];

    $userInitials .= mb_strtoupper(
        mb_substr($firstName, 0, 1)
    );

    if (count($nameParts) > 1) {
        $lastName = (string) $nameParts[
            count($nameParts) - 1
        ];

        $userInitials .= mb_strtoupper(
            mb_substr($lastName, 0, 1)
        );
    } elseif (mb_strlen($firstName) > 1) {
        $userInitials .= mb_strtoupper(
            mb_substr($firstName, 1, 1)
        );
    }
}

if ($userInitials === '') {
    $userInitials = 'US';
}

?>

<header class="header">

    <div class="header-left">

        <button
            id="sidebarToggleTop"
            class="header-icon-button"
            type="button"
            title="Abrir ou fechar menu"
            aria-label="Abrir ou fechar menu"
            aria-controls="sidebar"
            aria-expanded="true"
        >

            <i data-lucide="menu"></i>

        </button>

        <div class="header-search" data-global-search data-endpoint="<?= base_url('busca/sugestoes') ?>">

            <i data-lucide="search"></i>

            <label class="sr-only" for="globalSearch">Pesquisar no sistema</label>

            <input
                id="globalSearch"
                type="search"
                placeholder="Pesquisar..."
                autocomplete="off"
                aria-label="Pesquisar no sistema"
            >

            <div class="global-search-results" data-global-search-results hidden></div>
        </div>

    </div>

    <div class="header-right">

        <a
            class="header-icon-button header-notification-link"
            href="<?= base_url('notificacoes') ?>"
            title="Notificações"
            aria-label="Notificações"
        >

            <i data-lucide="bell"></i>

            <span
                id="headerNotificationBadge"
                class="header-notification-badge"
                aria-hidden="true"
            ></span>

        </a>

        <button
            class="header-icon-button"
            type="button"
            data-theme-toggle
            title="Alternar tema"
            aria-label="Alternar tema claro ou escuro"
        >

            <i data-theme-icon data-lucide="moon"></i>

        </button>

        <div
            class="user-profile"
            title="<?= e(
                $userName
                . ' • '
                . $userRoleLabel
            ) ?>"
        >

            <?php
            $headerPhotoPath = trim((string) ($loggedUser['photo_path'] ?? ''));
            $headerPhotoPath = ltrim(str_replace('\\', '/', $headerPhotoPath), '/');
            $hasHeaderPhoto = $headerPhotoPath !== ''
                && is_file(public_path($headerPhotoPath));
            $headerPhotoUrl = '';

            if ($hasHeaderPhoto) {
                $headerPhotoUrl = base_url($headerPhotoPath);
                $photoVersion = trim((string) ($loggedUser['photo_updated_at'] ?? ''));

                if ($photoVersion !== '') {
                    $headerPhotoUrl .= '?v=' . rawurlencode($photoVersion);
                }
            }
            ?>

            <div class="user-avatar<?= $hasHeaderPhoto ? ' has-photo' : '' ?>">
                <?php if ($hasHeaderPhoto): ?>
                    <img
                        class="user-avatar-photo"
                        src="<?= e($headerPhotoUrl) ?>"
                        alt="Foto de <?= e($userName) ?>"
                        width="46"
                        height="46"
                    >
                <?php else: ?>
                    <span aria-hidden="true"><?= e($userInitials) ?></span>
                <?php endif; ?>
            </div>

            <div class="user-info">

                <strong>
                    <?= e($userName) ?>
                </strong>

                <small>
                    <?= e($userRoleLabel) ?>
                </small>

            </div>

        </div>

    </div>

</header>
