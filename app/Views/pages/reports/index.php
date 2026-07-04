<?php

component('page-header', [
    'title' => 'Relatórios',
    'subtitle' => 'Centro de relatórios e indicadores da frequência escolar'
]);

?>

<div class="dashboard-grid">

    <a href="<?= base_url('relatorios/diario') ?>" class="card card-link">
        <h3>📅 Relatório Diário</h3>

        <p>
            Consulte a frequência geral da escola em uma data específica,
            incluindo ranking das turmas e chamadas pendentes.
        </p>
    </a>

    <a href="#" class="card card-link">
        <h3>🏫 Relatório por Turma</h3>

        <p>
            Analise frequência, faltas, justificativas e desempenho de uma
            turma durante um período.
        </p>
    </a>

    <a href="#" class="card card-link">
        <h3>👨‍🎓 Relatório por Aluno</h3>

        <p>
            Histórico completo de frequência, percentual de presença,
            justificativas e ocorrências do aluno.
        </p>
    </a>

    <a href="#" class="card card-link">
        <h3>🏆 Ranking por Período</h3>

        <p>
            Compare o desempenho das turmas por dia, semana, mês ou ano
            utilizando o Índice de Frequência Escolar (IFE).
        </p>
    </a>

    <a href="#" class="card card-link">
        <h3>📈 Evolução da Frequência</h3>

        <p>
            Visualize gráficos comparativos da frequência da escola,
            identificando tendências ao longo do tempo.
        </p>
    </a>

    <a href="#" class="card card-link">
        <h3>⚠️ Turmas sem Chamada</h3>

        <p>
            Identifique rapidamente quais turmas ainda não registraram a
            frequência no período selecionado.
        </p>
    </a>

    <a href="#" class="card card-link">
        <h3>🚨 Alunos em Alerta</h3>

        <p>
            Liste alunos com baixa frequência, faltas consecutivas e risco
            de reprovação por frequência.
        </p>
    </a>

    <a href="#" class="card card-link">
        <h3>📄 Exportações</h3>

        <p>
            Exporte relatórios em PDF ou Excel para impressão, reuniões e
            prestação de contas.
        </p>
    </a>

</div>