# Sprint 2.8.2.2 — Módulo Preditivo das Turmas

## Objetivo

Dar visibilidade própria à Inteligência Preditiva por Turma sem duplicar a página de comparação já existente.

## Central de Inteligência

Foi criado o painel **Inteligência Preditiva das Turmas**, contendo todas as turmas ativas e mostrando:

- risco atual;
- tendência geral;
- previsão coletiva para 14 dias;
- tendências de frequência, ocorrências, risco e cobertura;
- acesso à análise detalhada;
- acesso à comparação existente com a turma destacada.

Não foi criado o painel **Turmas com maior agravamento**.

## Painel da Turma

A seção Inteligência da turma agora apresenta:

- projeção coletiva para 14 dias;
- tendências de frequência, ocorrências, risco médio, cobertura e recomendações pendentes;
- evidências consideradas;
- ações preventivas recomendadas;
- quantidade de snapshots, período histórico e confiança;
- atalho para `/inteligencia/comparacoes?turma={id}`.

## Comparação entre turmas

A página existente `/inteligencia/comparacoes` foi preservada. Nenhuma tela paralela ou duplicada foi criada.

## Banco de dados

Nenhuma migração nova é necessária.
