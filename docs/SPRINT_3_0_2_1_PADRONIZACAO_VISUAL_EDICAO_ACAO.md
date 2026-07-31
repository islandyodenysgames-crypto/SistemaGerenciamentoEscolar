# Sprint 3.0.2.1 — Padronização visual da Edição de Ação

## Objetivo

Padronizar a área exibida ao acionar **Editar ação** no painel de acompanhamentos, sem modificar regras de negócio, banco de dados, permissões ou endpoints.

## Alterações

- Cabeçalho próprio com identificação da edição, tipo da ação e botão para fechar.
- Campos organizados em três seções visuais:
  - Dados da ação;
  - Descrição e continuidade;
  - Arquivos da ação.
- Indicador da quantidade de arquivos já vinculados.
- Rodapé de ações padronizado, com botões Cancelar e Salvar alterações.
- Layout responsivo para telas menores.
- Rolagem suave até o formulário ao abrir a edição.
- Os dois controles de cancelamento, no cabeçalho e no rodapé, fecham corretamente o formulário.

## Preservado

- Rotas e controllers existentes.
- Atualização da ação.
- Upload e exclusão de anexos.
- Permissões.
- Histórico e demais cartões do acompanhamento.
- Estrutura do banco de dados.

## Validação

- 317 arquivos PHP verificados sem erros de sintaxe.
- 83 rotas registradas.
- 457 referências internas analisadas.
- Nenhuma rota duplicada.
- Nenhum destino interno sem rota.
