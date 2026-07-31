# Sprint 2.5.2 — Timeline Inteligente

## Entregas

- Serviço reutilizável `IntelligenceTimeline`.
- Timeline da escola na Central de Inteligência.
- Timeline específica no Painel da Turma.
- Timeline específica no Perfil do Aluno.
- Eventos clicáveis para risco, frequência, ocorrências e pendências.
- Comparação automática com a janela imediatamente anterior de mesma duração.
- Estado informativo quando não existem mudanças prioritárias.

## Arquitetura

A timeline não grava dados e não exige migration. Os eventos são produzidos dinamicamente a partir dos mesmos indicadores utilizados pelos dashboards, evitando divergência entre cards, insights e linha do tempo.

## Próxima evolução

Uma etapa futura poderá persistir snapshots para registrar exatamente o dia em que cada aluno ou turma mudou de nível de risco, permitindo histórico longitudinal completo.
