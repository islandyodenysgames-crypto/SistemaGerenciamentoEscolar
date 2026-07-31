# Sprint 3.1 — Parte 2

## Correção da evolução da frequência

- Criada rota JSON `GET /dashboard/frequencia-evolucao?period=...`.
- O seletor troca o período por Fetch API, sem recarregar a página.
- O painel inteiro é substituído para sincronizar gráfico, título, selo e indicadores.
- Cache no navegador evita nova consulta ao retornar a um período já carregado.
- Requisições anteriores são canceladas ao trocar rapidamente de opção.
- Estado de carregamento, mensagem de erro e atualização da URL foram adicionados.
