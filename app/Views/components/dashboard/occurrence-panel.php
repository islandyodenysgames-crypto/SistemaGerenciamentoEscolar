<?php

$summary = $summary ?? [];

?>

<section class="dashboard-occurrence-panel">

    <div class="dashboard-occurrence-panel-header">

        <div class="dashboard-occurrence-panel-title">

            <div class="dashboard-occurrence-panel-icon">
                <i data-lucide="clipboard-list"></i>
            </div>

            <div>
                <span>Painel disciplinar</span>

                <h2>Ocorrências escolares</h2>

                <p>
                    Resumo executivo dos registros e acompanhamentos
                    realizados na escola.
                </p>
            </div>

        </div>

        <a
            href="<?= base_url('ocorrencias') ?>"
            class="btn-secondary"
        >
            <i data-lucide="layout-dashboard"></i>
            Ver informações completas
        </a>

    </div>

    <div class="dashboard-occurrence-panel-content">

        <?php component('dashboard/occurrence-summary', [
            'summary' => $summary,
        ]); ?>

    </div>

</section>