# Sprint 3.1 — Parte 1

## Inteligência de reincidência

- Serviço `RecurrenceIntelligenceService` para detectar repetição do mesmo tipo de ocorrência por aluno.
- Janela inicial de 60 dias e mínimo de 3 registros.
- Classificação em Atenção, Alto e Crítico.
- Card de reincidências no Dashboard, com alunos, casos ativos, críticos e novos casos da semana.
- Lista resumida dos casos com acesso ao perfil do aluno.
- Notificação específica `STUDENT_RECURRENCE` enviada aos usuários com acompanhamento ativo do aluno.
- Integração tanto no cadastro individual quanto no cadastro múltiplo de ocorrências.

## Evolução da frequência

- Seletor compacto no painel de evolução diária.
- Períodos disponíveis: 7, 15, 30, 60 e 90 dias; mês atual; mês anterior; semestre; ano letivo.
- Atualização por parâmetro `frequency_period` mantendo a página e os demais painéis do Dashboard.
- Consulta de frequência por intervalo reutilizável em `AttendanceAnalyticsService`.

## Banco de dados

Esta parte não exige nova migração.
