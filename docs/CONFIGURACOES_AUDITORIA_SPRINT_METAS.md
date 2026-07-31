# Auditoria de configurações — Meta de frequência

## Integrações confirmadas nesta versão

A configuração `school_goals.frequency_goal` passa a ser a fonte única para:

- Painel do Gestor e classificação da frequência geral;
- contagem de alunos em alerta;
- lista de casos de baixa frequência na Central de Inteligência;
- gráfico de evolução da frequência;
- indicadores de hoje, semana, mês e ano;
- ranking diário das turmas;
- mapa de calor da frequência;
- resumo e lista de alunos em alerta no Painel da Turma.

A faixa de **atenção** é calculada automaticamente como 5 pontos percentuais abaixo da meta. No mapa de calor, a faixa intermediária começa 15 pontos abaixo da meta.

## Configurações de Inteligência já integradas

- `analysis_window_days`;
- `stale_critical_days`;
- pesos de risco de frequência, ocorrências e gravidade;
- limites de risco moderado, alto e crítico;
- `trend_stable_margin`;
- `show_attachment_image_carousels`.

## Melhorias de consistência aplicadas

- os três pesos de risco agora precisam somar exatamente 100%;
- os limites de risco continuam obrigados a estar em ordem crescente;
- períodos, reincidência e limite de atenção da turma precisam ser positivos.

## Parâmetros ainda expostos, mas sem consumo completo

Os itens abaixo já existem na interface, porém ainda precisam de integração específica em uma Sprint de regras e notificações:

- `recurrence_limit`;
- `class_attention_threshold`;
- `notification_recurrent_student`;
- `notification_critical_occurrence`;
- `notification_critical_class`;
- `recommendation_guardian_contact`.

Eles não foram removidos para preservar a configuração existente, mas não devem ser tratados como plenamente operacionais até essa integração ser concluída.
