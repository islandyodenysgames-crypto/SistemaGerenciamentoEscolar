# Sprint 2.2 — Dashboard Inteligente do Aluno

## Entrega implementada

O Perfil do Aluno recebeu uma nova seção **Inteligência**, acessível pela aba correspondente.

A seção apresenta:

- risco combinado de 0 a 100;
- nível de risco configurável: baixo, atenção, alto ou crítico;
- faltas sem justificativa e faltas atenuadas;
- total de ocorrências, alta gravidade, abertas e resolvidas;
- comparação com a janela imediatamente anterior;
- sinais que contribuíram para o risco;
- recomendações pedagógicas automáticas.

## Arquitetura

Fluxo respeitado:

Controller → IntelligenceService → IntelligenceRepository → banco de dados

O cálculo utiliza o `SettingManager` criado na Sprint 2.1. Pesos, faixas e janela de análise continuam configuráveis pela gestão.

## Banco de dados

Esta entrega não cria tabelas e não exige nova migration.

## Acesso

Abra o perfil de qualquer aluno e clique na aba **Inteligência**.
