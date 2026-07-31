# Sprint 2.7.5.6.7 — Correção da regressão do indicador sem intervenção

A Sprint 2.7.5.6.6 alterou involuntariamente o conjunto do card ao usar a data de início do acompanhamento para casos sem nenhuma ação formal.

A regra foi restaurada e compartilhada com a listagem específica:

- acompanhamento ativo sem nenhuma ação formal: entra no indicador;
- acompanhamento ativo com ação: entra quando a última data efetiva da ação é igual ou superior a 14 dias;
- acompanhamento inativo: não entra;
- eventos administrativos da timeline não contam como intervenção.

O card e `/inteligencia/casos?tipo=stale_monitoring_actions` utilizam novamente o mesmo `monitoringOperationalSummary()`, impedindo divergência entre total e listagem.
