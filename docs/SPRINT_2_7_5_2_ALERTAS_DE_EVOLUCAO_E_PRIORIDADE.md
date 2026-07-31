# Sprint 2.7.5.2 — Alertas de Evolução e Prioridade

## Objetivo
Transformar o índice de risco já calculado pela Inteligência Escolar em notificações úteis para cada acompanhante, evitando repetições sem mudança relevante.

## Entregas
- Snapshot individual por acompanhante e aluno acompanhado.
- Notificação quando o nível de risco sobe.
- Notificação positiva quando o nível de risco cai.
- Alerta inicial quando um aluno acompanhado já está em risco alto ou crítico.
- Alerta de acompanhamento sem movimentação após 14 dias.
- Reemissão do alerta de inatividade no máximo uma vez a cada sete dias.
- Integração automática ao abrir a página de notificações ou consultar o contador do cabeçalho.
- Filtro dos novos eventos na categoria Inteligência.

## Migração
`202607290001_create_student_intelligence_snapshots_table.php`

A tabela `student_intelligence_snapshots` guarda somente o último estado avaliado por usuário e aluno. O histórico oficial permanece nas notificações e nos demais registros do sistema.
