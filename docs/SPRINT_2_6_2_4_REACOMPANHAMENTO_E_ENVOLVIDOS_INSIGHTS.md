# Sprint 2.6.2.4 — Novo período de acompanhamento e envolvidos dos insights

## Acompanhamento individual

O período e o motivo passam a pertencer também ao vínculo de cada pessoa acompanhadora. Ao encerrar e voltar a acompanhar um aluno, o usuário informa novamente:

- data inicial;
- data final;
- motivo predefinido;
- detalhes complementares.

A migration adiciona `start_date`, `end_date` e `reason` a `student_monitoring_users` e preserva os dados existentes por meio de backfill.

A gestão também define período e motivo ao adicionar ou reativar professores.

## Insights prioritários

Professores podem abrir a Central de Casos a partir das ações dos Insights Prioritários para visualizar os alunos, turmas ou ocorrências envolvidos no indicador. Esse acesso não libera a Central de Inteligência completa nem as comparações administrativas.
