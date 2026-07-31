<?php

component('base/page-header', [
    'title' => 'Ano Letivo',
    'subtitle' => 'Organize a vigência anual que servirá de base para períodos, calendário e relatórios.',
]);

$editing = is_array($editingYear ?? null) ? $editingYear : null;
$todayYear = (int) date('Y');
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($selectedYearId)): $daySummary=$schoolDays['summary']??[]; ?>
<div class="card" style="margin-top:24px">
    <div class="card-header"><div><h2>Dias letivos</h2><p>Gere os dias úteis e registre exceções, reposições e sábados letivos.</p></div>
        <form method="post" action="<?= base_url('configuracoes/ano-letivo/dias/gerar') ?>"><input type="hidden" name="_token" value="<?= e($csrfToken) ?>"><input type="hidden" name="school_year_id" value="<?= (int)$selectedYearId ?>"><button class="btn btn-secondary" type="submit">Gerar segunda a sexta</button></form>
    </div>
    <div class="settings-summary">
        <div class="settings-summary-card"><span>Previstos</span><strong><?= (int)($daySummary['planned']??0) ?></strong></div>
        <div class="settings-summary-card"><span>Decorridos</span><strong><?= (int)($daySummary['elapsed']??0) ?></strong></div>
        <div class="settings-summary-card"><span>Restantes</span><strong><?= (int)($daySummary['remaining']??0) ?></strong></div>
    </div>
    <form method="post" action="<?= base_url('configuracoes/ano-letivo/dias/salvar') ?>">
        <input type="hidden" name="_token" value="<?= e($csrfToken) ?>"><input type="hidden" name="school_year_id" value="<?= (int)$selectedYearId ?>">
        <div class="form-grid">
            <div class="form-group"><label>Data</label><input type="date" name="school_date" required></div>
            <div class="form-group"><label>Classificação</label><select name="day_type"><option value="SCHOOL_DAY">Dia letivo</option><option value="HOLIDAY">Feriado</option><option value="RECESS">Recesso</option><option value="VACATION">Férias</option><option value="PLANNING">Planejamento</option><option value="COUNCIL">Conselho</option><option value="STOPPAGE">Paralisação</option><option value="MAKEUP">Reposição</option><option value="SATURDAY_SCHOOL">Sábado letivo</option><option value="OPTIONAL">Ponto facultativo</option></select></div>
            <div class="form-group"><label>Título</label><input name="title" maxlength="180"></div>
        </div>
        <div class="form-group"><label>Observações</label><textarea name="notes" rows="2"></textarea></div>
        <button class="btn btn-primary" type="submit">Salvar dia</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const confirmations = {
        '/periodos/fechar': 'Fechar este período e preservar um snapshot dos indicadores atuais?',
        '/periodos/reabrir': 'Reabrir este período? Os lançamentos voltarão a aceitar alterações.',
        '/periodos/excluir': 'Excluir definitivamente este período letivo?',
        '/encerrar-seguro': 'Encerrar definitivamente este ano letivo? Todos os períodos devem estar fechados e um snapshot final será criado.',
        '/arquivar': 'Arquivar este ano letivo encerrado?',
        '/reabrir': 'Reabrir este ano arquivado em modo de preparação?'
    };

    document.querySelectorAll('form[action]').forEach(function (form) {
        const action = form.getAttribute('action') || '';
        const suffix = Object.keys(confirmations).find(function (candidate) {
            return action.endsWith(candidate);
        });
        if (!suffix) return;

        form.addEventListener('submit', function (event) {
            if (!window.confirm(confirmations[suffix])) {
                event.preventDefault();
            }
        });
    });
});
</script>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="settings-summary">
    <div class="settings-summary-card">
        <span>Ano ativo</span>
        <strong><?= e($activeYear['name'] ?? 'Não definido') ?></strong>
    </div>
    <div class="settings-summary-card">
        <span>Vigência</span>
        <strong>
            <?= $activeYear
                ? e(date('d/m/Y', strtotime($activeYear['start_date'])) . ' a ' . date('d/m/Y', strtotime($activeYear['end_date'])))
                : '—' ?>
        </strong>
    </div>
    <div class="settings-summary-card">
        <span>Progresso</span>
        <strong><?= $activeYear ? e(number_format((float) $activeYear['progress'], 1, ',', '.') . '%') : '—' ?></strong>
    </div>
</div>

<?php if (!empty($selectedYearId)): ?>
<div class="card" style="margin-top:24px">
    <div class="card-header"><div><h2>Períodos letivos</h2><p>Bimestres, trimestres, semestres ou divisões personalizadas do ano selecionado.</p></div></div>
    <form method="post" action="<?= base_url('configuracoes/ano-letivo/periodos/salvar') ?>">
        <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">
        <input type="hidden" name="school_year_id" value="<?= (int)$selectedYearId ?>">
        <div class="form-grid">
            <div class="form-group"><label>Nome</label><input name="name" required placeholder="1º Bimestre"></div>
            <div class="form-group"><label>Nome curto</label><input name="short_name" maxlength="40" placeholder="1º Bim."></div>
            <div class="form-group"><label>Tipo</label><select name="type"><option value="BIMESTER">Bimestre</option><option value="TRIMESTER">Trimestre</option><option value="SEMESTER">Semestre</option><option value="CUSTOM">Personalizado</option></select></div>
            <div class="form-group"><label>Ordem</label><input name="order_number" type="number" min="1" value="<?= count($periods)+1 ?>" required></div>
            <div class="form-group"><label>Início</label><input name="start_date" type="date" required></div>
            <div class="form-group"><label>Término</label><input name="end_date" type="date" required></div>
            <div class="form-group"><label>Cor</label><input name="color" type="color" value="#168a55"></div>
        </div>
        <div class="form-group"><label>Descrição</label><textarea name="description" rows="2"></textarea></div>
        <button class="btn btn-primary" type="submit">Adicionar período</button>
    </form>
    <?php if(!empty($periods)): ?>
    <div class="table-responsive" style="margin-top:20px"><table><thead><tr><th>Ordem</th><th>Período</th><th>Vigência</th><th>Progresso</th><th></th></tr></thead><tbody>
    <?php foreach($periods as $period): ?><tr>
        <td><?= (int)$period['order_number'] ?></td><td><strong><?= e($period['name']) ?></strong><br><small><?= e($period['short_name']??'') ?></small></td>
        <td><?= e(date('d/m/Y',strtotime($period['start_date'])).' a '.date('d/m/Y',strtotime($period['end_date']))) ?></td>
        <td><?= e(number_format((float)$period['progress'],1,',','.').'%') ?></td>
        <td>
        <?php if($period['status']==='CLOSED'): ?><form method="post" action="<?= base_url('configuracoes/ano-letivo/periodos/reabrir') ?>"><input type="hidden" name="_token" value="<?= e($csrfToken) ?>"><input type="hidden" name="id" value="<?= (int)$period['id'] ?>"><button class="btn btn-sm btn-secondary" type="submit">Reabrir</button></form>
        <?php else: ?><form method="post" action="<?= base_url('configuracoes/ano-letivo/periodos/fechar') ?>"><input type="hidden" name="_token" value="<?= e($csrfToken) ?>"><input type="hidden" name="id" value="<?= (int)$period['id'] ?>"><button class="btn btn-sm btn-primary" type="submit">Fechar período</button></form>
        <form method="post" action="<?= base_url('configuracoes/ano-letivo/periodos/excluir') ?>"><input type="hidden" name="_token" value="<?= e($csrfToken) ?>"><input type="hidden" name="id" value="<?= (int)$period['id'] ?>"><button class="btn btn-sm btn-secondary" type="submit">Excluir</button></form><?php endif; ?>
        </td>
    </tr><?php endforeach; ?>
    </tbody></table></div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if(!empty($selectedYearId)): ?>
<div class="card" style="margin:24px 0">
 <div class="card-header"><div><h2>Assistente de Novo Ano</h2><p>Crie o próximo ano e, se desejar, replique a estrutura dos períodos.</p></div></div>
 <form method="post" action="<?=base_url('configuracoes/ano-letivo/assistente')?>">
  <input type="hidden" name="_token" value="<?=e($csrfToken)?>"><input type="hidden" name="source_year_id" value="<?=(int)$selectedYearId?>">
  <div class="form-grid"><div class="form-group"><label>Ano</label><input type="number" name="year" min="2000" max="2200" value="<?=$todayYear+1?>" required></div><div class="form-group"><label>Nome</label><input name="name" placeholder="Ano Letivo <?=$todayYear+1?>"></div><div class="form-group"><label>Início</label><input type="date" name="start_date" required></div><div class="form-group"><label>Término</label><input type="date" name="end_date" required></div></div>
  <label class="checkbox-row"><input type="checkbox" name="copy_periods" value="1" checked> Copiar a estrutura dos períodos letivos</label>
  <button class="btn btn-primary" type="submit">Preparar novo ano</button>
 </form>
</div>
<?php endif; ?>

<div class="card" style="margin:24px 0">
 <div class="card-header"><div><h2>Estatísticas históricas</h2><p>Snapshots preservados no encerramento de cada ano.</p></div></div>
 <div class="table-responsive"><table><thead><tr><th>Ano</th><th>Situação</th><th>Frequência final</th><th>Registros</th></tr></thead><tbody>
 <?php foreach($academicHistory as $item): $metrics=json_decode((string)($item['metrics_json']??'{}'),true)?:[]; ?><tr><td><?=e($item['name'])?></td><td><?=e($item['status'])?></td><td><?=isset($metrics['frequency_percentage'])?e(number_format((float)$metrics['frequency_percentage'],2,',','.').'%'):'—'?></td><td><?=(int)($metrics['attendance_records']??0)?></td></tr><?php endforeach; ?>
 </tbody></table></div>
</div>

<div class="card" style="margin:24px 0">
 <div class="card-header"><div><h2>Auditoria acadêmica</h2><p>Ações críticas de fechamento, reabertura e criação de ano.</p></div></div>
 <div class="table-responsive"><table><thead><tr><th>Data</th><th>Ação</th><th>Escopo</th><th>Responsável</th></tr></thead><tbody>
 <?php foreach($academicAudits as $item): ?><tr><td><?=e(date('d/m/Y H:i',strtotime($item['created_at'])))?></td><td><?=e($item['description'])?></td><td><?=e(trim(($item['year_name']??'').' '.($item['period_name']??'')))?></td><td><?=e($item['user_name']??'Sistema')?></td></tr><?php endforeach; ?>
 </tbody></table></div>
</div>

<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <div>
            <h2><?= $editing ? 'Editar ano letivo' : 'Novo ano letivo' ?></h2>
            <p>Somente um ano pode permanecer ativo por vez.</p>
        </div>
    </div>

    <form method="post" action="<?= base_url($editing ? 'configuracoes/ano-letivo/atualizar' : 'configuracoes/ano-letivo') ?>">
        <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">
        <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int) $editing['id'] ?>"><?php endif; ?>

        <div class="form-grid">
            <div class="form-group">
                <label for="school-year-name">Nome</label>
                <input id="school-year-name" name="name" maxlength="120" required
                       value="<?= e($editing['name'] ?? ('Ano Letivo ' . $todayYear)) ?>">
            </div>
            <div class="form-group">
                <label for="school-year-number">Ano</label>
                <input id="school-year-number" name="year" type="number" min="2000" max="2200" required
                       value="<?= e((string) ($editing['year'] ?? $todayYear)) ?>">
            </div>
            <div class="form-group">
                <label for="school-year-start">Início</label>
                <input id="school-year-start" name="start_date" type="date" required
                       value="<?= e($editing['start_date'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="school-year-end">Término</label>
                <input id="school-year-end" name="end_date" type="date" required
                       value="<?= e($editing['end_date'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="school-year-status">Situação</label>
                <select id="school-year-status" name="status">
                    <?php foreach (['PREPARATION' => 'Em preparação', 'ACTIVE' => 'Em andamento', 'CLOSED' => 'Encerrado', 'ARCHIVED' => 'Arquivado'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= ($editing['status'] ?? 'PREPARATION') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="school-year-notes">Observações</label>
            <textarea id="school-year-notes" name="notes" rows="3"><?= e($editing['notes'] ?? '') ?></textarea>
        </div>
        <label class="checkbox-row">
            <input type="checkbox" name="is_active" value="1" <?= !empty($editing['is_active']) ? 'checked' : '' ?>>
            Definir como ano letivo ativo
        </label>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?= $editing ? 'Salvar alterações' : 'Criar ano letivo' ?></button>
            <?php if ($editing): ?><a class="btn btn-secondary" href="<?= base_url('configuracoes/ano-letivo') ?>">Cancelar</a><?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <div><h2>Anos cadastrados</h2><p>O histórico é preservado para as integrações anuais futuras.</p></div>
    </div>

    <?php if (empty($years)): ?>
        <div class="empty-state"><p>Nenhum ano letivo cadastrado.</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Ano</th><th>Vigência</th><th>Situação</th><th>Progresso</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($years as $year): ?>
                    <tr>
                        <td><strong><?= e($year['name']) ?></strong><?= !empty($year['is_active']) ? ' • Ativo' : '' ?></td>
                        <td><?= e(date('d/m/Y', strtotime($year['start_date'])) . ' a ' . date('d/m/Y', strtotime($year['end_date']))) ?></td>
                        <td><?= e($year['status_label']) ?></td>
                        <td><?= e(number_format((float) $year['progress'], 1, ',', '.') . '%') ?></td>
                        <td>
                            <a class="btn btn-sm btn-secondary" href="<?= base_url('configuracoes/ano-letivo?editar=' . (int) $year['id']) ?>">Editar</a>
                            <?php if (empty($year['is_active']) && !in_array($year['status'], ['CLOSED', 'ARCHIVED'], true)): ?>
                                <form method="post" action="<?= base_url('configuracoes/ano-letivo/ativar') ?>" style="display:inline">
                                    <input type="hidden" name="_token" value="<?= e($csrfToken) ?>"><input type="hidden" name="id" value="<?= (int) $year['id'] ?>">
                                    <button class="btn btn-sm btn-primary" type="submit">Ativar</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($year['status'] === 'ACTIVE'): ?>
                                <form method="post" action="<?= base_url('configuracoes/ano-letivo/encerrar-seguro') ?>" style="display:inline">
                                    <input type="hidden" name="_token" value="<?= e($csrfToken) ?>"><input type="hidden" name="id" value="<?= (int) $year['id'] ?>">
                                    <button class="btn btn-sm btn-secondary" type="submit">Encerrar</button>
                                </form>
                            <?php elseif ($year['status'] === 'CLOSED'): ?>
                                <form method="post" action="<?= base_url('configuracoes/ano-letivo/arquivar') ?>" style="display:inline">
                                    <input type="hidden" name="_token" value="<?= e($csrfToken) ?>"><input type="hidden" name="id" value="<?= (int) $year['id'] ?>">
                                    <button class="btn btn-sm btn-secondary" type="submit">Arquivar</button>
                                </form>
                            <?php elseif ($year['status'] === 'ARCHIVED'): ?>
                                <form method="post" action="<?= base_url('configuracoes/ano-letivo/reabrir') ?>" style="display:inline">
                                    <input type="hidden" name="_token" value="<?= e($csrfToken) ?>"><input type="hidden" name="id" value="<?= (int) $year['id'] ?>">
                                    <button class="btn btn-sm btn-secondary" type="submit">Reabrir</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
