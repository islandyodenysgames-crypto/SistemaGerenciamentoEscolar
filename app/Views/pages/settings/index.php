<?php

component('base/page-header', [
    'title' => 'Configurações',
    'subtitle' => 'Central administrativa do Sistema de Frequência Escolar'
]);

?>

<div class="settings-grid">

    <a href="<?= base_url('configuracoes/identidade') ?>" class="card settings-card">
        <div class="settings-card-icon">
            <i data-lucide="school"></i>
        </div>

        <div>
            <h3>Identidade da Escola</h3>
            <p>Logo, nome, contatos, redes sociais e dados institucionais.</p>
        </div>
    </a>

    <a href="#" class="card settings-card">
        <div class="settings-card-icon">
            <i data-lucide="target"></i>
        </div>

        <div>
            <h3>Metas da Escola</h3>
            <p>Frequência mínima, objetivos e parâmetros de acompanhamento.</p>
        </div>
    </a>

    <a href="#" class="card settings-card">
        <div class="settings-card-icon">
            <i data-lucide="calendar-days"></i>
        </div>

        <div>
            <h3>Ano Letivo</h3>
            <p>Calendário escolar, períodos e configurações do ano vigente.</p>
        </div>
    </a>

    <a href="<?= base_url('usuarios') ?>" class="card settings-card">
        <div class="settings-card-icon">
            <i data-lucide="users"></i>
        </div>

        <div>
            <h3>Usuários e Permissões</h3>
            <p>Gerencie usuários, acessos e permissões do sistema.</p>
        </div>
    </a>

    <a href="#" class="card settings-card">
        <div class="settings-card-icon">
            <i data-lucide="palette"></i>
        </div>

        <div>
            <h3>Aparência</h3>
            <p>Cores, tema visual e identidade gráfica da aplicação.</p>
        </div>
    </a>

    <a href="#" class="card settings-card">
        <div class="settings-card-icon">
            <i data-lucide="database-backup"></i>
        </div>

        <div>
            <h3>Backup</h3>
            <p>Exportação, restauração e segurança dos dados escolares.</p>
        </div>
    </a>

</div>