# Correção MySQL 3065 — Acompanhamentos

Foi corrigida a consulta `StudentMonitoringRepository::allForUser()`.

## Problema

A consulta combinava `SELECT DISTINCT` com ordenação por colunas que não faziam parte diretamente da lista selecionada. No MySQL 8, isso gera o erro 3065.

## Solução

- Remoção do `DISTINCT`.
- Seleção da matrícula ativa mais recente por aluno mediante subconsulta correlacionada.
- Preservação da ordenação de vínculos ativos antes dos concluídos.
- Inclusão de `smu.id DESC` como critério estável de desempate.

A alteração mantém uma linha por vínculo de acompanhamento e evita duplicidades causadas por mais de uma matrícula ativa cadastrada para o mesmo aluno.
