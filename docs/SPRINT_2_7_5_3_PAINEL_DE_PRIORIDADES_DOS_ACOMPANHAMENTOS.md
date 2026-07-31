# Sprint 2.7.5.3 — Painel de Prioridades dos Acompanhamentos

## Objetivo

Transformar a página `/acompanhamentos` em um painel operacional que ajude professores e gestão a identificar rapidamente quais alunos acompanhados exigem atenção primeiro.

## Implementações

- Cálculo do risco atual de cada aluno usando a Inteligência Escolar já existente.
- Ordenação automática dos cartões por nível de risco, pontuação e tempo sem intervenção.
- Identificação de acompanhamento parado após 14 dias sem ação.
- Identificação de prioridade imediata para risco alto, crítico ou 30 dias sem intervenção.
- Resumo superior com total, prioridades imediatas, críticos, altos e casos sem ação recente.
- Filtros instantâneos na própria página, sem recarregamento.
- Selo de risco e pontuação em cada cartão.
- Informação clara sobre a última intervenção.
- Destaque visual para casos altos, críticos e urgentes.

## Compatibilidade

A sprint não exige migração de banco de dados. Ela reutiliza os dados de frequência, ocorrências, ações e risco já existentes.
