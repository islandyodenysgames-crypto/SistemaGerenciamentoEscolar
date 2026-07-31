# Sprint 2.6.2.2 — Participantes do acompanhamento

## Correções

- Permite à gestão adicionar novos professores depois que o acompanhamento do aluno já foi iniciado.
- Permite reativar um professor que havia encerrado seu vínculo com o mesmo acompanhamento.
- Mantém os participantes ativos visíveis e indisponíveis para seleção duplicada.
- Inclui, no perfil do aluno, ações para encerrar e posteriormente reativar o próprio vínculo.
- A gestão pode adicionar professores sem ser incluída automaticamente; sua participação continua opcional.

## Arquitetura

A correção reutiliza `student_monitoring` e `student_monitoring_users`. Não cria tabelas, migrations, serviços ou rotas adicionais.
