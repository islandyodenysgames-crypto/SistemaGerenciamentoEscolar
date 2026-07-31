# Sprint 3.1.0.2 — Parte 6: Casos independentes de acompanhamento

## Implementado

- `student_monitoring` passa a representar um **caso de acompanhamento**.
- Um aluno pode possuir vários casos simultâneos.
- Cada novo início cria um caso independente, mesmo quando o aluno já possui outro acompanhamento ativo.
- Novos campos: título do caso, código do problema e detalhes do problema.
- O fluxo existente **Adicionar ao mesmo plano** continua vinculando participantes ao caso atual.
- Novo fluxo **Novo problema e outro plano** abre o formulário completo e cria outro caso.
- Casos existentes são migrados sem perda de histórico.

## Banco de dados

Execute a nova migration `202608030001_add_case_fields_to_student_monitoring.php`.

## Observação de arquitetura

Nesta parte, planos continuam armazenados por vínculo de participante para preservar compatibilidade. Participantes do mesmo caso recebem a mesma configuração inicial de plano, mas futuras alterações ainda podem ser individualizadas. Uma próxima parte pode consolidar opcionalmente um plano compartilhado por caso sem destruir os planos históricos.
