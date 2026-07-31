# Sprint 3.1.0.4 — Separação entre Caso e Participante

## Regra consolidada

O caso (`student_monitoring`) é o proprietário do problema, motivo, período geral, plano, ações, inteligência e linha do tempo.

O participante (`student_monitoring_users`) representa somente o vínculo da pessoa com o caso: usuário, entrada, saída, situação e autoria da atribuição.

## Alterações técnicas

- `student_monitoring_plans.monitoring_id` passa a identificar o caso dono do plano.
- Há somente um plano ativo por caso.
- Planos legados duplicados por participante são consolidados, preservando a versão mais recente.
- `student_monitoring_actions.monitoring_id` passa a identificar diretamente o caso.
- A remoção de um participante não remove mais o plano nem as ações históricas do caso.
- A criação de caso grava o plano uma única vez.
- A inclusão de participantes compartilha o plano do caso, sem gerar cópias individuais.
- Atualizações do plano geram eventos `PLAN_CREATED` e `PLAN_UPDATED` na linha do tempo.

## Migração

Executar `202608050001_move_monitoring_plan_to_case.php`.
