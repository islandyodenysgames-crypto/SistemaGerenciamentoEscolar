<?php

use App\Config\Menu;

$menu = Menu::items();

$schoolName = school('name', app_name());
$schoolShortName = school('short_name', 'SFE');
$schoolLogo = school('logo_path');

?>

<aside
    class="sidebar"
    id="sidebar"
>

    <div>

        <div class="sidebar-header">

            <button
                class="sidebar-toggle"
                id="sidebarToggle"
                type="button"
            >
                <i data-lucide="panel-left-close"></i>
            </button>

            <div class="logo">

                <?php if (!empty($schoolLogo)): ?>

                    <div class="logo-image">
                        <img
                            src="<?= asset($schoolLogo) ?>"
                            alt="<?= e($schoolName) ?>"
                        >
                    </div>

                <?php else: ?>

                    <div class="logo-icon">
                        <i data-lucide="graduation-cap"></i>
                    </div>

                <?php endif; ?>

                <div class="logo-text">
                    <strong><?= e($schoolShortName) ?></strong>
                    <small><?= e($schoolName) ?></small>
                </div>

            </div>

        </div>

        <nav class="sidebar-menu">

            <?php foreach ($menu as $item): ?>

                <a
                    href="<?= $item['url'] ?>"
                    class="<?= $item['active'] ? 'active' : '' ?>"
                >
                    <i data-lucide="<?= $item['icon'] ?>"></i>
                    <span><?= $item['title'] ?></span>
                </a>

            <?php endforeach; ?>

        </nav>

    </div>

    <div class="sidebar-footer">

        <a href="#">
            <i data-lucide="circle-help"></i>
            <span>Ajuda</span>
        </a>

        <a href="<?= base_url('logout') ?>">
            <i data-lucide="log-out"></i>
            <span>Sair</span>
        </a>

    </div>

</aside>