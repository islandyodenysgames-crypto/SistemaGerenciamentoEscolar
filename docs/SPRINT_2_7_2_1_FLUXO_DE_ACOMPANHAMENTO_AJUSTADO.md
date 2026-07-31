# Sprint 2.7.2.1 — Fluxo de acompanhamento ajustado

## Alterações

- `Registrar Ação` foi removido do perfil do aluno e incluído nos cards de `/acompanhamentos`.
- O painel `Acompanhamento e plano do aluno` passou a se chamar `Acompanhamento`.
- Após a criação do vínculo, os formulários ficam recolhidos.
- O perfil exibe apenas `Atualizar plano do acompanhamento` e, para a gestão, `Adicionar novo acompanhante`.
- A inclusão de acompanhantes ganhou uma operação própria e não exige encerrar o acompanhamento atual.
- O novo acompanhante recebe o período, o motivo e uma cópia inicial do plano vigente.
- As ações recentes são exibidas no card do aluno acompanhado.

## Nova rota

`POST /acompanhamentos/acompanhantes/adicionar`

## Banco de dados

Nenhuma migration nova foi necessária.
