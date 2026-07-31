# Sprint 2.7.5.3.1 — Correção da consulta da última ação

## Problema corrigido

A consulta `latestActionDateForStudent()` tentava acessar a coluna inexistente
`student_monitoring_actions.monitoring_id`.

A tabela `student_monitoring_actions` relaciona-se ao acompanhamento por meio de:

1. `student_monitoring_actions.monitoring_user_id`;
2. `student_monitoring_users.id`;
3. `student_monitoring_users.monitoring_id`;
4. `student_monitoring.id`.

## Correção

A consulta passou a utilizar os relacionamentos reais do banco:

```sql
INNER JOIN student_monitoring_users smu
    ON smu.id = sma.monitoring_user_id
INNER JOIN student_monitoring sm
    ON sm.id = smu.monitoring_id
```

Isso elimina o erro `Unknown column 'sma.monitoring_id'` ao abrir a página de
Acompanhamentos e mantém o cálculo da data da última intervenção por aluno.

## Banco de dados

Nenhuma migração é necessária.
