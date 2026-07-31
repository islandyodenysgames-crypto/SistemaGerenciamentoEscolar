<?php
component('base/page-header', [
    'title' => 'Exportação, restauração e segurança',
    'subtitle' => 'Proteja, restaure ou reinicie os dados escolares com controle sobre o que será mantido.',
]);
$counts = (array)($summary['counts'] ?? []);
$lastBackup = $summary['lastBackup'] ?? null;
?>

<?php if (!empty($success)): ?><div class="alert alert-success"><i data-lucide="circle-check"></i><span><?=e($success)?></span></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-danger"><i data-lucide="triangle-alert"></i><span><?=e($error)?></span></div><?php endif; ?>

<div class="data-safety-banner card">
    <div class="data-safety-icon"><i data-lucide="shield-check"></i></div>
    <div>
        <strong>Área protegida da gestão</strong>
        <p>As operações desta página exigem permissão administrativa e confirmação digitada. Antes de limpar ou restaurar, faça uma exportação completa.</p>
    </div>
</div>

<div class="data-overview-grid">
    <div class="card data-overview-card"><span>Alunos</span><strong><?=number_format((int)($counts['students']??0),0,',','.')?></strong></div>
    <div class="card data-overview-card"><span>Turmas</span><strong><?=number_format((int)($counts['school_classes']??0),0,',','.')?></strong></div>
    <div class="card data-overview-card"><span>Matrículas</span><strong><?=number_format((int)($counts['enrollments']??0),0,',','.')?></strong></div>
    <div class="card data-overview-card"><span>Frequências</span><strong><?=number_format((int)($counts['attendance']??0),0,',','.')?></strong></div>
</div>

<div class="data-maintenance-grid">
    <section class="card data-maintenance-panel data-panel-safe">
        <div class="data-panel-heading">
            <div class="data-panel-icon"><i data-lucide="download"></i></div>
            <div><h2>Exportar backup completo</h2><p>Gera um ZIP com banco de dados, fotos e arquivos enviados.</p></div>
        </div>
        <ul class="data-feature-list">
            <li><i data-lucide="check"></i> Todos os cadastros e históricos</li>
            <li><i data-lucide="check"></i> Fotos de alunos, turmas e escola</li>
            <li><i data-lucide="check"></i> Avisos, anexos e configurações</li>
        </ul>
        <?php if (is_array($lastBackup)): ?>
            <div class="data-last-backup">Último backup: <strong><?=e(date('d/m/Y H:i', strtotime((string)($lastBackup['created_at']??'now'))))?></strong></div>
        <?php endif; ?>
        <form method="post" action="<?=base_url('configuracoes/dados/exportar')?>">
            <button class="btn-primary" type="submit"><i data-lucide="archive"></i> Gerar e baixar backup</button>
        </form>
    </section>

    <section class="card data-maintenance-panel data-panel-warning">
        <div class="data-panel-heading">
            <div class="data-panel-icon"><i data-lucide="upload"></i></div>
            <div><h2>Restaurar backup</h2><p>Substitui os dados atuais pelos dados de um backup anterior.</p></div>
        </div>
        <form method="post" enctype="multipart/form-data" action="<?=base_url('configuracoes/dados/restaurar')?>" class="data-action-form" data-confirm-form>
            <label class="form-label">Arquivo de backup (.zip)</label>
            <input class="form-control" type="file" name="backup_file" accept=".zip,application/zip" required>
            <label class="form-label">Digite <strong>RESTAURAR DADOS</strong> para confirmar</label>
            <input class="form-control" type="text" name="confirmation" autocomplete="off" required data-required-phrase="RESTAURAR DADOS">
            <button class="btn-secondary" type="submit" disabled><i data-lucide="rotate-ccw"></i> Restaurar backup</button>
        </form>
    </section>
</div>

<section class="card data-maintenance-panel data-panel-danger">
    <div class="data-panel-heading">
        <div class="data-panel-icon"><i data-lucide="trash-2"></i></div>
        <div><h2>Limpar dados para um novo período</h2><p>Remove históricos e registros operacionais. Usuários, identidade da escola e configurações essenciais são sempre mantidos.</p></div>
    </div>

    <form method="post" action="<?=base_url('configuracoes/dados/limpar')?>" class="data-action-form" data-confirm-form id="clear-school-data-form">
        <div class="data-preserve-box">
            <h3>O que deseja manter?</h3>
            <p>Marque os cadastros que serão reaproveitados após a limpeza.</p>
            <label class="data-option">
                <input type="checkbox" name="keep_classes" value="1" id="keep-classes">
                <span><strong>Turmas</strong><small>Mantém nomes, séries, turnos e demais dados cadastrais das turmas.</small></span>
            </label>
            <label class="data-option">
                <input type="checkbox" name="keep_students" value="1" id="keep-students">
                <span><strong>Alunos e matrículas atuais</strong><small>Mantém os cadastros dos alunos e seus vínculos com as turmas. Ao selecionar, as turmas também serão mantidas.</small></span>
            </label>
            <label class="data-option data-option-dependent">
                <input type="checkbox" name="keep_class_photos" value="1" id="keep-class-photos">
                <span><strong>Fotos das turmas</strong><small>Disponível quando as turmas forem mantidas.</small></span>
            </label>
            <label class="data-option data-option-dependent">
                <input type="checkbox" name="keep_student_photos" value="1" id="keep-student-photos">
                <span><strong>Fotos dos alunos</strong><small>Disponível quando os alunos forem mantidos.</small></span>
            </label>
        </div>

        <div class="data-will-remove">
            <strong>Serão removidos, salvo quando necessários aos itens mantidos:</strong>
            <span>frequências, ocorrências, acompanhamentos, inteligência, avisos, calendário, favoritos, relatórios históricos e anexos operacionais.</span>
        </div>

        <label class="form-label">Digite <strong>LIMPAR DADOS</strong> para confirmar</label>
        <input class="form-control" type="text" name="confirmation" autocomplete="off" required data-required-phrase="LIMPAR DADOS">
        <label class="data-final-check"><input type="checkbox" required> <span>Estou ciente de que esta operação é irreversível sem um backup.</span></label>
        <button class="btn-danger" type="submit" disabled><i data-lucide="shield-alert"></i> Executar limpeza segura</button>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-confirm-form]').forEach(function (form) {
        var confirmation = form.querySelector('[data-required-phrase]');
        var button = form.querySelector('button[type="submit"]');
        var finalCheck = form.querySelector('.data-final-check input');
        function update() {
            var phraseOk = confirmation && confirmation.value.trim() === confirmation.dataset.requiredPhrase;
            var checkOk = !finalCheck || finalCheck.checked;
            button.disabled = !(phraseOk && checkOk);
        }
        confirmation && confirmation.addEventListener('input', update);
        finalCheck && finalCheck.addEventListener('change', update);
        update();
    });
    var students = document.getElementById('keep-students');
    var classes = document.getElementById('keep-classes');
    var studentPhotos = document.getElementById('keep-student-photos');
    var classPhotos = document.getElementById('keep-class-photos');
    function dependencies() {
        if (students.checked) { classes.checked = true; classes.disabled = true; }
        else classes.disabled = false;
        studentPhotos.disabled = !students.checked;
        classPhotos.disabled = !classes.checked;
        if (studentPhotos.disabled) studentPhotos.checked = false;
        if (classPhotos.disabled) classPhotos.checked = false;
    }
    [students, classes].forEach(function (el) { el.addEventListener('change', dependencies); });
    dependencies();
});
</script>
