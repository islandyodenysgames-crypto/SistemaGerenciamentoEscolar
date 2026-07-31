# Sprint 2.7.3 — Inteligência do Acompanhamento

## Objetivo

Transformar os dados já existentes de acompanhamento, plano, frequência, ocorrências e ações em sinais objetivos dentro da página `/acompanhamentos`.

## Arquitetura

A implementação reutiliza `IntelligenceService` e não cria tabela, motor ou configuração paralela. Os sinais são calculados em tempo de leitura a partir dos dados oficiais.

## Sinais implementados

- acompanhamento ativo sem ações registradas;
- acompanhamento sem movimentação;
- momento de revisão;
- próxima ação pendente;
- meta de frequência atingida;
- frequência em evolução ou regressão;
- distância para a meta;
- meta de ocorrências dentro do esperado ou ultrapassada;
- duas melhoras consecutivas;
- piora na ação mais recente;
- período próximo do encerramento.

## Prioridade

Os sinais são ordenados por: alto, atenção, informativo e positivo. Cada card mostra no máximo quatro sinais para evitar excesso de informação.

## Banco de dados

Nenhuma migration foi necessária.
