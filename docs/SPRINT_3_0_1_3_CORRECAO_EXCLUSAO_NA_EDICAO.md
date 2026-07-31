# Sprint 3.0.1.3 — Correção da exclusão de anexos na edição

## Causa identificada

O componente de anexos era renderizado dentro do formulário principal de edição da ocorrência e criava outro `<form>` para cada botão de exclusão. Formulários aninhados são inválidos em HTML. Dependendo da interpretação do navegador, o clique enviava o formulário de atualização da ocorrência em vez da rota de exclusão, causando o retorno ao perfil do aluno sem remover o anexo.

## Correção

- Removidos os formulários internos do componente de anexos.
- O botão de remoção agora pertence ao formulário principal, mas utiliza `formaction`, `formmethod`, `formenctype` e `formnovalidate` para enviar diretamente à rota `/ocorrencias/anexos/excluir`.
- O identificador do anexo é enviado pelo próprio botão.
- O destino de retorno permanece `/ocorrencias/editar?id=...#occurrenceAttachmentsSection`.
- O controller continua obtendo a ocorrência a partir do registro do anexo, sem confiar no `occurrence_id` da tela.

## Auditoria de regressão

- Rota de exclusão registrada uma única vez.
- Nenhum formulário aninhado permanece no componente usado pela edição.
- Upload de novos anexos preservado.
- Exclusão pelo perfil do aluno preservada.
- Exibição no painel de últimas ocorrências preservada.
- Todos os arquivos PHP de `app`, `routes` e `database` passaram no lint.
- Nenhuma migração nova é necessária.
