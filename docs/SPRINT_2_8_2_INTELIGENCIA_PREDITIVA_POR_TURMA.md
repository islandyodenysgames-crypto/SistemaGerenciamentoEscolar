# Sprint 2.8.2 — Inteligência Preditiva por Turma

## Objetivo
Consolidar os snapshots individuais por turma para identificar tendências coletivas e projetar o cenário provável dos próximos 14 dias.

## Indicadores
- frequência média;
- ocorrências;
- risco médio;
- cobertura dos acompanhamentos prioritários;
- recomendações pendentes.

## Regras
A análise compara a média do primeiro terço do histórico com a média do último terço. Com menos de dois dias de snapshots, o resultado é `Dados insuficientes`.

## Entregas
- `ClassTrendAnalysisService`;
- `ClassPredictiveAnalysisService`;
- `SchoolClassPrediction`;
- tendências e previsão nos cards de turmas prioritárias;
- ranking positivo **Turmas que mais melhoraram**.

## Decisão de produto
Não foi criado o painel **Turmas com maior agravamento**, conforme solicitado. Agravamentos continuam visíveis individualmente nos cards e nas explicações, sem ranking negativo dedicado.

## Banco de dados
Nenhuma migração nova. A sprint usa `student_intelligence_daily_snapshots`.
