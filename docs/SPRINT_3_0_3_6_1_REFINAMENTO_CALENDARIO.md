# Sprint 3.0.3.6.1 — Refinamento do Calendário Escolar

## Correções funcionais

- Horário inicial e horário final agora são persistidos corretamente.
- A marcação antiga de “Dia inteiro” não apaga horários preenchidos.
- O formulário desabilita e limpa os horários somente quando “Dia inteiro” é marcado conscientemente.
- Campos booleanos (`all_day`, `featured` e `active`) agora enviam valores explícitos `0/1`.
- O destaque no Dashboard recebeu identificação visual própria e prioridade na listagem.
- Horários são exibidos no padrão `HH:mm`, inclusive ao editar eventos já salvos como `HH:mm:ss`.

## Dashboard

- Painel ampliado e mais visível.
- Calendário mensal com células maiores.
- Indicadores coloridos por tipo de evento.
- Dia atual com destaque forte.
- Eventos destacados exibem selo e fundo especial.
- Próximos eventos mostram horário inicial/final, local e tipo.
- Legenda de cores adicionada.

## Página /calendario

- Cabeçalho e painel de apresentação padronizados.
- Cards de resumo.
- Visão mensal completa.
- Cores por tipo de evento.
- Lista de eventos redesenhada.
- Busca instantânea por título, descrição ou tipo.
- Formulário reorganizado e alinhado ao padrão visual do sistema.

## Banco de dados

Nenhuma nova migração é necessária. A tabela existente continua compatível.
