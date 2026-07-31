# Sprint 2.7.5.6 — Central de Inteligência Enxuta e Orientada à Ação

## Objetivo

Reduzir redundâncias na Central de Inteligência e apresentar uma sequência de leitura gerencial mais direta: situação geral, prioridades, resposta da equipe e casos que exigem análise.

## Alterações

- Cabeçalho reduzido e período apresentado de forma compacta.
- Resumo estratégico consolidado em quatro indicadores.
- Insights simplificados, sem impacto em estrelas e confiança na visualização principal.
- Filtros reduzidos para `Todos`, `Exigem ação` e `Evolução positiva`.
- Remoção da Timeline Inteligente da escola, pois repetia os insights atuais.
- Remoção do painel separado de Recomendações; a ação sugerida permanece integrada ao insight.
- Novo painel `Situação dos acompanhamentos`, contendo:
  - cobertura dos casos prioritários;
  - casos sem acompanhante;
  - acompanhamentos sem intervenção há 14 dias;
  - recomendações pendentes.
- Frequência e ocorrências transformadas em indicadores compactos.
- Lista limitada aos cinco alunos prioritários, agora com situação operacional do acompanhamento.
- Lista limitada às três turmas prioritárias, mostrando o principal sinal de cada turma.

## Banco de dados

Nenhuma nova migração é necessária. Os dados são obtidos das tabelas já criadas nas sprints de acompanhamento e recomendações.

## Resultado

A Central passa a responder rapidamente:

1. Qual é a situação geral?
2. O que exige ação?
3. Os casos prioritários estão sendo acompanhados?
4. Quais alunos e turmas devem ser abertos agora?
