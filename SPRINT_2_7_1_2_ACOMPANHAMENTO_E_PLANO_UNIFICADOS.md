# Sprint 2.7.1.2 — Acompanhamento e Plano Unificados

## Regra principal
Um vínculo de acompanhamento não pode mais ser criado ou reativado sem um plano obrigatório.

A ação **Acompanhar aluno** abre um único formulário com:
- período;
- motivo e detalhes;
- participantes;
- objetivo principal;
- uma ou mais estratégias;
- indicador e meta opcional;
- observações do plano.

## Integridade no backend
A validação ocorre no `StudentMonitoringService::start()`. O serviço valida o plano antes de criar o acompanhamento e salva o plano na mesma operação para cada vínculo selecionado.

## Metas contextuais
Antes do vínculo, a tela mostra como referência os dados do aluno no ano corrente. Depois do vínculo, usa os dados do período do acompanhamento.

## Banco de dados
Não há nova migration. A estrutura de `student_monitoring`, `student_monitoring_users` e `student_monitoring_plans` foi reutilizada.
