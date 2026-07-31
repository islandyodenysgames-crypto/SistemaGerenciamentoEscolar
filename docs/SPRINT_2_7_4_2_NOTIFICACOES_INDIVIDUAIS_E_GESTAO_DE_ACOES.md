# Sprint 2.7.4.2 — Notificações individuais e gestão de ações

## Objetivos

- Exibir Editar ação e Excluir ação diretamente na Inteligência Escolar.
- Preservar as mesmas regras de autorização: gestão pode alterar qualquer ação; demais usuários apenas as próprias.
- Redirecionar o usuário de volta ao perfil do aluno após editar ou excluir.
- Enviar notificações individuais aos acompanhantes ativos do aluno.

## Eventos notificados

- Nova ocorrência (já existente).
- Nova ação de acompanhamento.
- Edição de ação.
- Exclusão de ação.
- Entrada de novo acompanhante.
- Saída de acompanhante.

O autor da operação é excluído dos alertas redundantes sobre a própria ação. A entrada de acompanhante é enviada também ao novo participante, para que ele passe a acompanhar as movimentações futuras.

## Página de notificações

As notificações continuam disponíveis em `/notificacoes`, com vínculo direto para o perfil do aluno. O cabeçalho da página foi ampliado para representar toda a Inteligência Escolar, não apenas ocorrências.

## Compatibilidade

Nenhuma nova migração foi necessária. As notificações utilizam a estrutura existente `occurrence_notifications`, que já suporta tipos genéricos, referência ao aluno, metadados e leitura individual por usuário.
