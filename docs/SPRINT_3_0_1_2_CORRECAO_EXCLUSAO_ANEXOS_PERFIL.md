# Sprint 3.0.1.2 — Correção da exclusão de anexos no perfil

## Problema corrigido

A exclusão de anexos dependia do `occurrence_id` enviado pelo formulário. Em fluxos diferentes, esse valor podia não estar presente ou divergir, fazendo a operação falhar e redirecionar o usuário sem remover o arquivo.

## Alterações

- O controller agora busca o anexo pelo `attachment_id`.
- A ocorrência é obtida diretamente do vínculo salvo no banco.
- A permissão é validada sobre a ocorrência encontrada.
- O perfil do aluno ganhou botão individual para excluir cada anexo.
- Após a exclusão iniciada no perfil, o usuário permanece em `#studentOccurrences`.
- A edição da ocorrência continua retornando para `#occurrenceAttachmentsSection`.
- Os destinos de retorno aceitos são limitados a páginas internas do sistema.

## Arquivos principais

- `app/Controllers/Concerns/Occurrence/Management/OccurrenceManageActions.php`
- `app/Services/OccurrenceAttachmentService.php`
- `app/Views/components/students/profile/occurrences.php`
- `public/assets/css/pages/students-profile/occurrences.css`

## Banco de dados

Nenhuma nova migração é necessária.
