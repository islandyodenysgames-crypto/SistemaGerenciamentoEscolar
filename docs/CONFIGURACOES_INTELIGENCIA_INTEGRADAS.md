# Integração das configurações de Inteligência

Nesta versão, as configurações antes parcialmente integradas passaram a controlar regras reais:

- `recurrence_limit`: quantidade mínima usada nos painéis, perfil e detecção após nova ocorrência.
- `analysis_window_days`: janela usada também pela reincidência e pela avaliação de turma.
- `notification_recurrent_student`: habilita/desabilita notificações de reincidência.
- `notification_critical_occurrence`: habilita/desabilita notificações de ocorrências de gravidade alta/crítica.
- `notification_critical_class`: habilita/desabilita alertas para gestão/coordenação quando a pontuação da turma alcança o limite configurado.
- `class_attention_threshold`: define a pontuação mínima para a turma entrar em atenção e para gerar o respectivo alerta.
- `recommendation_guardian_contact`: inclui ou retira a recomendação de contato com responsável nos casos críticos, sem ocultar a necessidade de intervenção pedagógica.

As notificações usam impressão digital para evitar duplicações do mesmo evento.
