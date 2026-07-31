# Sprint 2.9.1 — Padronização Visual Completa

## Objetivo

Auditar a apresentação visual do sistema e consolidar padrões globais sem alterar regras de negócio, consultas, rotas ou permissões.

## Diagnóstico

A estrutura já possuía design tokens e componentes reutilizáveis para cartões, botões, tabelas, badges e cabeçalhos. Entretanto, a auditoria identificou dois pontos de inconsistência estrutural:

1. O componente global `components/form/form.css` estava vazio. Por isso, formulários dependiam de estilos definidos em páginas específicas.
2. Tabelas e filtros criados em etapas diferentes utilizavam nomes de classes distintos, embora representassem os mesmos padrões visuais.

## Implementações

### Componente global de formulários

Foi criado um padrão compartilhado para:

- grades de uma ou duas colunas;
- grupos de campos;
- campos de largura total;
- labels e textos auxiliares;
- inputs, selects e textareas;
- estados hover, focus, disabled e inválido;
- checkboxes e radios;
- mensagens de erro;
- campos obrigatórios;
- barra de ações;
- responsividade para telas menores.

Arquivo:

`public/assets/css/components/form/form.css`

### Camada global de compatibilidade visual

Foi criada uma camada carregada após os demais componentes para uniformizar padrões existentes sem exigir reescrita completa das views.

Ela contempla:

- ritmo vertical das páginas;
- cabeçalhos e grupos de ações;
- cartões e painéis;
- tabelas atuais e legadas;
- bordas horizontais e verticais nas células;
- linhas alternadas e hover;
- colunas de ações;
- contêineres responsivos;
- barras de filtros;
- foco visível para teclado;
- alertas;
- estados vazios;
- badges e status;
- responsividade global;
- respeito a `prefers-reduced-motion`.

Arquivo:

`public/assets/css/components/visual-audit.css`

### Visualização de chamada

A tabela da página de visualização individual de frequência passou a utilizar um contêiner responsivo, evitando estouro horizontal e mantendo o mesmo acabamento das demais tabelas.

Arquivo:

`app/Views/pages/attendance/show.php`

## Compatibilidade

A camada considera as classes já existentes no projeto, incluindo:

- `.data-table`
- `.table`
- `.subjects-table`
- `.intelligence-case-table`
- `.comparison-table`
- `.filters`
- `.filter-bar`
- `.report-filters`
- `.occurrences-filters`
- `.notifications-filters`

## Regras preservadas

Não houve alteração em:

- controllers;
- services;
- repositories;
- banco de dados;
- cálculos de frequência;
- inteligência escolar;
- permissões;
- rotas.

## Diretriz para novas páginas

Novas telas devem priorizar os componentes globais e as classes:

- `.page-header`
- `.card` ou `.panel`
- `.form-grid`, `.form-group`, `.form-control`, `.form-actions`
- `.table-responsive` e `.data-table`
- `.filters` ou `.filter-bar`
- `.badge`
- `.btn-primary`, `.btn-secondary` e variantes semânticas
