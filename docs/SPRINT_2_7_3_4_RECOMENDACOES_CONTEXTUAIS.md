# Sprint 2.7.3.4 — Recomendações pedagógicas contextuais

## Correção

A recomendação genérica **Acompanhamento individual** não é mais exibida quando o aluno já possui acompanhamento ativo.

## Regras

- Sem acompanhamento ativo: as recomendações originais da Inteligência continuam válidas.
- Com acompanhamento ativo e sem ações: exibe **Registrar primeira ação**.
- Com acompanhamento ativo e ao menos uma ação: remove **Acompanhamento individual** e **Monitoramento preventivo**.
- Alertas específicos ainda não tratados continuam disponíveis.
- A leitura das faltas e o resumo das ações permanecem responsáveis por orientar as próximas etapas.

## Integração

O contexto do acompanhamento agora é enviado ao método `IntelligenceService::studentDashboard()`, permitindo que as recomendações considerem:

- acompanhamento ativo;
- plano existente;
- quantidade de ações registradas.
