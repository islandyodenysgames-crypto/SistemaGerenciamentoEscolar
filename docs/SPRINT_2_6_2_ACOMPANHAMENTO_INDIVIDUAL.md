# Sprint 2.6.2 — Acompanhamento Individual de Alunos

## Objetivo
Permitir que professores acompanhem alunos individualmente durante um período escolhido e consultem evolução, ocorrências, frequência, professores participantes e sugestões contextuais de ações.

## Regras
- Professor pode iniciar e encerrar o próprio vínculo.
- Administrador, direção e coordenação podem designar professores.
- Secretaria não possui acesso ao módulo.
- Um aluno pode ter vários professores acompanhadores no mesmo período.
- Os dados do perfil inteligente, frequência, ocorrências e notificações existentes são reutilizados.
- Sugestões não criam providências automaticamente; elas orientam decisões do professor.
- Não existe indicador de tempo médio de resolução.

## Persistência
- `student_monitoring`: período compartilhado do aluno.
- `student_monitoring_users`: professores vinculados.
- `student_monitoring_snapshots`: preparada para preservar retratos inicial/final em evolução futura.

## Notificações
Novas ocorrências de um aluno acompanhado também são enviadas aos professores com vínculo ativo, utilizando a central de notificações já existente.
