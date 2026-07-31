# Sprint 2.7.5.6.3 — Correção da cobertura dos casos prioritários

## Problema
O percentual de cobertura utilizava a quantidade de acompanhamentos ativos, mesmo quando nenhum professor estava vinculado ao caso.

## Regra corrigida
Um caso prioritário só é considerado coberto quando possui pelo menos um acompanhante ativo no período atual.

```text
Cobertura = casos prioritários com acompanhante ativo / total de casos prioritários
```

Exemplo: 1 caso coberto entre 4 casos prioritários resulta em 25%.

Também foi removido um bloco duplicado indevido de `studentDashboard()`, evitando nova ocorrência do erro de variável `$students` indefinida.
