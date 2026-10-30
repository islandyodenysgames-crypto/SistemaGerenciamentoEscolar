<?php
$positive = (array) ($positive ?? []);
$total = (int) ($positive['recognitions'] ?? 0) + (int) ($positive['responses'] ?? 0) + (int) ($positive['resolved_adverse'] ?? 0);
if ($total <= 0) return;
?>
<section class="card dashboard-positive-indicators">
    <div class="card-header">
        <div><h3><i data-lucide="sparkles"></i> Respostas e sinais positivos</h3><p>Atuação pedagógica e reconhecimentos registrados na janela atual da Inteligência.</p></div>
        <a class="table-link" href="<?= base_url('inteligencia') ?>">Abrir Central</a>
    </div>
    <div class="dashboard-positive-indicators__grid">
        <a href="<?= base_url('inteligencia/casos?tipo=positive_recognitions') ?>"><i data-lucide="award"></i><strong><?= (int) ($positive['recognitions'] ?? 0) ?></strong><span>Reconhecimentos</span><small><?= (int) ($positive['students_recognized'] ?? 0) ?> aluno(s)</small></a>
        <a href="<?= base_url('inteligencia/casos?tipo=pedagogical_responses') ?>"><i data-lucide="message-circle-check"></i><strong><?= (int) ($positive['responses'] ?? 0) ?></strong><span>Respostas pedagógicas</span><small><?= (int) ($positive['students_with_response'] ?? 0) ?> aluno(s)</small></a>
        <article><i data-lucide="calendar-check"></i><strong><?= (int) ($positive['attendance_responses'] ?? 0) ?></strong><span>Respostas sobre frequência</span><small>Atuação sem apagar faltas</small></article>
        <article><i data-lucide="shield-check"></i><strong><?= (int) ($positive['resolved_adverse'] ?? 0) ?></strong><span>Situações resolvidas</span><small>Histórico preservado</small></article>
        <article><i data-lucide="trending-up"></i><strong><?= (int) ($positive['students_with_verified_improvement'] ?? 0) ?></strong><span>Alunos com evolução</span><small>Melhora associada à área atendida</small></article>
        <article><i data-lucide="shield-plus"></i><strong><?= (int) ($positive['students_with_protection'] ?? 0) ?></strong><span>Com fator de proteção</span><small><?= (int) ($positive['protection_points'] ?? 0) ?> ponto(s) atenuado(s)</small></article>
    </div>
</section>
