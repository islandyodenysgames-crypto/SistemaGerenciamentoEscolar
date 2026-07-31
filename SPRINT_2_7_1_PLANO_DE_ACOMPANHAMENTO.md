# Sprint 2.7.1 — Plano de Acompanhamento

Implementa um plano individual por vínculo de acompanhamento. Cada usuário que acompanha um aluno pode registrar objetivo, estratégias, indicador, valor inicial, meta e observações sem alterar o plano dos demais participantes.

## Migration

`202607240001_create_student_monitoring_plans_table.php`

## Regras

- O plano pertence a `student_monitoring_users`.
- Somente um vínculo ativo pode criar ou atualizar seu próprio plano.
- O plano é reutilizado ao editar, sem gerar duplicidades.
- Estratégias são opções padronizadas e podem ser combinadas.
- Meta pode usar frequência, ocorrências ou ser apenas descritiva.
