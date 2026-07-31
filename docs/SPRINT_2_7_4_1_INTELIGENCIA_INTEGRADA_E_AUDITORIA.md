# Sprint 2.7.4.1 — Inteligência Integrada e Auditoria

## Entregas

- Snapshot ampliado da situação atual do acompanhamento.
- Exibição de acompanhantes ativos, frequência anual, ocorrências abertas e data da última intervenção.
- Indicação exata de desde quando o aluno está sem acompanhamento ativo.
- Histórico de edição das intervenções, preservando o conteúdo anterior.
- Histórico de exclusão lógica das intervenções.
- Identificação do autor original e do usuário que editou ou excluiu cada ação.
- Eventos `ACTION_UPDATED` e `ACTION_DELETED` incorporados à timeline unificada.

## Banco de dados

Foi criada a tabela `student_monitoring_action_revisions`. A tabela guarda uma fotografia da ação antes de cada edição ou exclusão, sem alterar o histórico já existente.

## Segurança e rastreabilidade

A edição e a exclusão continuam obedecendo às permissões anteriores. O novo histórico apenas registra as operações autorizadas e não permite restaurar ou modificar revisões antigas.
