# Sprint 2.8.0 — Tendências

## Objetivo
Transformar os snapshots diários da Inteligência em leituras históricas explicáveis para cada aluno.

## Implementação
- `TrendAnalysisService`: analisa até 30 dias de snapshots.
- `StudentTrend`: objeto de resultado padronizado.
- Tendências de frequência, ocorrências e risco: `IMPROVING`, `STABLE`, `WORSENING` ou `INSUFFICIENT`.
- Situação das intervenções: `ADEQUATE`, `DELAYED`, `NO_MONITORING` ou `INSUFFICIENT`.
- Confiança baseada na quantidade de capturas e na extensão temporal do histórico.
- A Central mostra tendências nos cinco alunos prioritários.
- O perfil do aluno mostra uma seção completa com quatro indicadores e suas justificativas.

## Critérios
- Frequência: mudança mínima de 2 pontos percentuais.
- Ocorrências: mudança mínima de 1 ocorrência.
- Risco: mudança mínima de 3 pontos.
- A comparação usa a média do primeiro e do último terço do histórico, reduzindo efeito de um único dia atípico.
- Menos de dois snapshots resulta em `Dados insuficientes`.

## Compatibilidade
Nenhuma nova migração é necessária. A sprint usa as tabelas criadas na Sprint 2.7.6.
