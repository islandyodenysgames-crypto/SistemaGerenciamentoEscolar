# Sprint 2.9.3 — Reconstrução segura do refinamento final

## Base utilizada

A reconstrução foi realizada sobre a versão 51, preservando todas as funcionalidades das Sprints 2.9.0, 2.9.1, 2.9.2 e 2.9.2.1.

A versão 30 foi utilizada exclusivamente como referência visual funcional para confirmar o comportamento dos ícones e dos componentes.

## Regressão identificada

A implementação anterior da Sprint 2.9.3 introduziu alterações globais que afetavam componentes de várias páginas:

- troca do carregamento original do Lucide;
- seletores globais aplicados a `button`, `[role="button"]`, `details`, `summary`, cards e painéis;
- dimensões globais impostas aos SVGs;
- reorganização responsiva genérica de grupos de ações;
- inclusão de uma camada `polish.css` sobre componentes já estilizados.

Essas alterações foram removidas integralmente.

## Refinamentos preservados nesta reconstrução

A nova versão aplica somente mudanças sem impacto visual estrutural:

- nomes acessíveis nos botões do cabeçalho;
- associação do botão de menu à barra lateral por `aria-controls`;
- atualização de `aria-expanded` ao abrir ou recolher o menu;
- rótulo acessível na pesquisa global;
- proteção `noopener noreferrer` em links externos;
- utilitário `.sr-only` isolado para leitores de tela.

## Garantias

- o carregamento de ícones permanece exatamente como na versão 51 funcional;
- nenhuma regra global nova altera botões, cards, tabelas, details ou SVGs;
- nenhuma página ou componente foi removido;
- nenhuma regra de negócio foi alterada;
- nenhuma migração de banco de dados é necessária.
