# Grupo 4 — Autorização, controle de acesso e proteção dos dados

## Matriz auditada

As regras abaixo refletem `Roles.php`, `Permissions.php` e as verificações de propriedade existentes. “Global” significa toda a escola; “próprio/participante” é restrição aplicada pelo servidor.

| Recurso/operação | Admin, Direção, Coordenação | Secretaria | Professor | Escopo confirmado |
|---|---|---|---|---|
| Dashboard | visualizar | visualizar | visualizar | global |
| Inteligência | visualizar/gerenciar fluxos disponíveis | visualizar | negado | global |
| Usuários | visualizar/criar/editar/excluir | negado | negado | global |
| Configurações, backup e ano letivo | gerenciar | negado | negado | global |
| Alunos e turmas | visualizar/criar/editar/excluir/lote | visualizar/criar/editar/excluir/lote | somente visualizar | global; não existe vínculo professor–turma no domínio atual |
| Matrículas | visualizar/gerenciar | visualizar/gerenciar | negado | global |
| Frequência | visualizar/gerenciar | visualizar/gerenciar | negado | global |
| Ocorrências | visualizar/criar/gerenciar todas | somente visualizar | visualizar, criar e editar/excluir somente as próprias | propriedade por `created_by` |
| Providências/anexos de ocorrência | gerenciar | consulta quando exposta pelo módulo | vinculadas à ocorrência permitida | herda ocorrência |
| Acompanhamentos | visualizar/gerenciar todos | negado | visualizar/gerenciar casos dos quais participa; ações próprias | participante/autor |
| Disciplinas | visualizar/gerenciar | visualizar | visualizar e usar as atribuídas | vínculo usuário–disciplina |
| Avisos/calendário | visualizar/gerenciar | visualizar | visualizar | global; somente avisos ativos aparecem na busca sem gestão |
| Relatórios/CSV | visualizar/exportar | visualizar/exportar | visualizar/exportar | global, conforme permissão existente |
| Favoritos | próprios | próprios | próprios | existência e escopo do item |
| Arquivos privados | conforme módulo | conforme módulo | conforme módulo/propriedade aplicável | usuário próprio ou gestão; acompanhamento participante |

## Correções confirmadas

- A página geral de Configurações agora exige autenticação e `settings.manage`.
- Dashboard e endpoints auxiliares agora exigem `dashboard.view`.
- Dashboard de ocorrências exige `occurrences.view`.
- Relatórios e CSV exigem `reports.view`, não apenas uma sessão existente.
- A busca global consulta somente grupos autorizados. Usuários ficam exclusivos de `users.manage`; casos de acompanhamento são limitados aos participantes, salvo gestão; usuários sem gestão veem somente avisos ativos e recebem link de consulta.
- Favoritos validam permissão, existência e, em acompanhamentos, participação ou acesso de gestão.
- Fotos de usuários só podem ser baixadas pelo próprio usuário ou por quem gerencia usuários.
- Anexos de acompanhamento exigem participação no caso ou perfil de gestão.
- Endpoints JSON protegidos recebem 401 para sessão ausente e 403 para permissão insuficiente.
- Downloads fora do escopo respondem 404 para não confirmar a existência do arquivo.

## Regras já corretas e preservadas

- Ocorrências usam `created_by`: gestão administra todas; professor altera/exclui somente as próprias.
- Exclusão de anexos obtém a ocorrência pelo próprio registro, sem confiar no ID enviado pela tela.
- Notificações filtram por `user_id` no serviço.
- Acompanhamentos validam participante, autor da ação ou perfil de gestão na camada de serviço.
- Operações administrativas de frequência, matrículas, calendário, avisos, ano letivo e configurações já exigiam suas permissões específicas.
- Revalidação da sessão revoga conta desativada e atualiza mudança de perfil.

Por padrão `SESSION_REVALIDATE_INTERVAL=0`, portanto conta ativa e perfil são consultados novamente em toda requisição autenticada. Um intervalo maior pode reduzir consultas, mas cria uma janela equivalente de revogação tardia e deve ser uma decisão operacional consciente.

## Ambiguidades que exigem decisão humana

1. Não existe vínculo professor–turma no modelo atual. Portanto `students.view`, `occurrences.view` e `reports.view` são globais para professores. Restringir por “turmas do professor” exigiria criar uma regra e um relacionamento novos; isso não foi inventado nesta revisão.
2. Professores possuem `reports.view`, incluindo exportação CSV global, conforme a matriz existente. Confirmar se essa amplitude é pedagogicamente desejada.
3. Secretaria possui gestão global de frequência e alunos, mas apenas consulta de ocorrências. Essa separação foi preservada.
4. Direção e Coordenação são tratadas como acesso total, inclusive usuários e configurações, por `Roles::hasFullAccess()`. Confirmar se ambas devem manter equivalência ao Administrador.
5. A consulta de anexos de ocorrências acompanha a permissão global `occurrences.view`; não há regra existente de propriedade para leitura.

## Pendências

- Testes ponta a ponta com banco MySQL descartável continuam necessários para manipulação real de IDs entre usuários sintéticos.
- Uma política futura de escopo por escola exigirá `school_id` consistente em todas as entidades; o projeto atual opera como escola única.
- Caso seja aprovada restrição professor–turma, criar primeiro o relacionamento explícito e só depois filtrar repositories e exportações.
