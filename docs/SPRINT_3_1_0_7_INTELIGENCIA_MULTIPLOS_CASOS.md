# Sprint 3.1.0.7 — Inteligência integrada aos múltiplos casos

## Implementado

- Associação explícita entre alerta da Inteligência e caso de acompanhamento.
- Busca de todos os casos ativos do aluno sem duplicação por participante.
- Correspondência automática pelo tipo de problema do caso.
- Botão **Abrir caso** quando o alerta já possui caso correspondente.
- Botão **Criar novo caso** quando o problema ainda não possui caso relacionado.
- Pré-preenchimento de motivo, título, objetivo, estratégias, indicador e detalhes.
- Registro da associação quando o caso é criado a partir da Central.
- Contadores operacionais calculados por caso.
- Casos diferentes do mesmo aluno permanecem independentes.

## Migração

`202608070001_link_intelligence_alerts_to_monitoring_cases.php`

Cria a tabela `intelligence_alert_case_links`.
