# Sprint 2.7.2 — Evoluções do Acompanhamento

## Objetivo
Registrar as ações efetivamente realizadas durante um acompanhamento ativo, mantendo plano, execução e resultados no mesmo painel do aluno.

## Regras
- Só um participante com vínculo ativo pode registrar uma ação.
- A ação pertence ao vínculo individual (`monitoring_user_id`).
- A linha do tempo reúne as ações do acompanhamento para dar visão compartilhada aos participantes.
- O autor edita e exclui suas próprias ações.
- Administração, direção e coordenação podem editar ou excluir qualquer ação.
- A exclusão é lógica por meio de `deleted_at`.

## Campos
Data, tipo, descrição obrigatória, resultado observado opcional e próxima ação opcional.

## Rotas
- `POST /acompanhamentos/acoes/registrar`
- `POST /acompanhamentos/acoes/atualizar`
- `POST /acompanhamentos/acoes/excluir`
