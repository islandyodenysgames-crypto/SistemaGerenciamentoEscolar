# Sprint 2.7.4.0 — Timeline completa do acompanhamento

## Entregas

- Separação entre autor da operação e acompanhante efetivo.
- Gestores não passam a acompanhar automaticamente ao incluir outra pessoa.
- Inclusão por gestor não exige vínculo pessoal ativo.
- Histórico permanente de entrada, saída, retomada e ausência de acompanhantes.
- Evento automático quando o último acompanhante é removido.
- Snapshot da situação atual na Inteligência Escolar.
- Timeline unificada com movimentações de acompanhantes e intervenções.
- Migração com reconstrução inicial dos vínculos antigos disponíveis.

## Nova tabela

`student_monitoring_events` preserva o autor, o usuário afetado, a quantidade de acompanhantes ativos e a data/hora de cada movimentação.
