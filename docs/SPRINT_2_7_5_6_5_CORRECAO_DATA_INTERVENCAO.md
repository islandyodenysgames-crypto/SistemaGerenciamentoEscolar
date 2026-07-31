# Sprint 2.7.5.6.5 — Correção da data efetiva de intervenção

O indicador de casos sem intervenção considerava apenas `student_monitoring_actions.action_date`.

Uma ação cadastrada ou atualizada recentemente com data de realização anterior aparecia na linha do tempo, mas continuava sendo classificada como desatualizada.

A data efetiva usada pela Central passa a ser a maior entre:

- `action_date`;
- data de `created_at`;
- data de `updated_at`.

Somente ações formais não excluídas do acompanhamento ativo atual são consideradas. Eventos administrativos continuam fora da contagem.
