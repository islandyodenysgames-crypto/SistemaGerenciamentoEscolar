# Sprint 3.0.1.1 — Ajustes dos anexos nas ocorrências

## Exclusão sem sair da edição

O formulário de remoção envia um destino interno de retorno. Após excluir um anexo, o usuário volta para:

`/ocorrencias/editar?id={id}#occurrenceAttachmentsSection`

O destino é validado no controller para impedir redirecionamentos externos.

## Anexos em Últimas ocorrências

O painel da rota `/ocorrencias` exibe:

- contador de arquivos;
- até três arquivos inicialmente;
- nome, tipo visual e tamanho;
- abertura em nova aba;
- seção recolhível para os arquivos excedentes.

Os anexos são agrupados em uma única consulta para todas as ocorrências carregadas, evitando N+1.

## Banco de dados

Nenhuma nova migração é necessária. Esta sprint usa a tabela `occurrence_attachments` criada anteriormente.
