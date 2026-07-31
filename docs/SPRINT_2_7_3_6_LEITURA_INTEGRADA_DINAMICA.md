# Sprint 2.7.3.6 — Leitura Integrada Dinâmica

## Objetivo
Transformar ações de acompanhamento em leituras contextuais, deixando de depender de um resultado manual para informar evolução.

## Fluxos cobertos
- Faltas sem justificativa
- Ocorrências
- Acompanhamento individual

## Estados automáticos
- Intervenção ainda não registrada
- Intervenção realizada / Em observação
- Evolução positiva confirmada pelos dados
- Intervenção sem resposta confirmada
- Recomendação para reabrir o tratamento de faltas

## Regra
A ação registrada inicia a observação. Frequência e ocorrências posteriores confirmam ou contradizem a evolução. O histórico da intervenção é preservado.
