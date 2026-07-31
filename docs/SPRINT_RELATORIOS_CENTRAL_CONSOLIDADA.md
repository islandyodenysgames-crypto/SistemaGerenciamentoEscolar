# Sprint — Central de Relatórios

## Entregas

- Nova visão executiva em `/relatorios`.
- Indicadores dos últimos 30 dias.
- Atalhos para relatório diário, mês atual, ano letivo e histórico de chamadas.
- Novo relatório consolidado em `/relatorios/consolidado`.
- Filtros por data inicial, data final e turma.
- Resumo de frequência e ocorrências.
- Comparativo de desempenho das turmas.
- Relação dos alunos abaixo da meta configurada da escola.
- Tendência diária da frequência.
- Distribuição das ocorrências por tipo.
- Exportação CSV respeitando os filtros.
- Impressão otimizada, permitindo salvar em PDF pelo navegador.

## Integração

A meta utilizada pelo relatório é `school_goals.frequency_goal`. Alterações feitas nas Configurações da Escola são refletidas automaticamente na classificação dos alunos e no status geral do relatório.

## Rotas

- `GET /relatorios`
- `GET /relatorios/diario`
- `GET /relatorios/consolidado`
- `GET /relatorios/exportar-csv`
