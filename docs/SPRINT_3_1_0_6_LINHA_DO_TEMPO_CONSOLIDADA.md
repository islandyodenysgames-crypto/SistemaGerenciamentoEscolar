# Sprint 3.1.0.6 — Linha do tempo consolidada do caso

## Regra consolidada

A linha do tempo utiliza `student_monitoring.id` (`monitoring_id`) como chave permanente. Eventos de um caso nunca são consultados por aluno ou participante isoladamente.

## Eventos cobertos

- `CASE_CREATED`: criação do caso;
- `PLAN_CREATED` e `PLAN_UPDATED`: criação e atualização do plano compartilhado;
- `PARTICIPANT_ADDED` e `PARTICIPANT_REMOVED`: entrada e saída de participantes;
- `ACTION_CREATED`, `ACTION_UPDATED` e `ACTION_DELETED`: ciclo das ações;
- `ATTACHMENT_ADDED` e `ATTACHMENT_REMOVED`: anexos das ações;
- `PERIOD_EXTENDED`: prorrogação do período do caso;
- `STATUS_CHANGED`: alteração de status;
- `CASE_CLOSED`: encerramento automático após o período, sem participante ativo;
- `CASE_REOPENED`: reservado ao fluxo de reabertura do caso;
- `NO_ACTIVE_FOLLOWERS`: caso temporariamente sem participante ativo.

## Ordem

A consulta consolidada é ordenada por `event_at ASC, id ASC`, exibindo o ciclo do caso em ordem cronológica.

## Autoria

Eventos explícitos usam `performed_by`. Ações usam `created_by`; revisões usam `changed_by`; anexos usam o usuário autenticado que realizou a operação.
