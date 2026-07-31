# Sprint 3.1.0.4 — Separação entre caso e participante

## Implementação inicial aplicada

- Plano gravado uma única vez por `student_monitoring.id`.
- Participantes do mesmo caso consultam o mesmo plano compartilhado.
- Inclusão de participante não cria nem atualiza plano, problema ou motivo do caso.
- Ações novas são gravadas diretamente com `student_monitoring_actions.monitoring_id`.
- Timeline, anexos e inteligência consultam ações pelo caso.
- Prorrogação atualiza o período do caso e registra histórico por `monitoring_id`.
- Campo `internal_notes` adicionado ao vínculo do participante.
- Dados legados de `student_monitoring_users.reason` são migrados para `internal_notes`.
- Interface de inclusão de participante solicita somente período, professor e observações internas.
- Eventos `PLAN_CREATED`, `PLAN_UPDATED` e `PERIOD_EXTENDED` passam a ser registrados.

## Migração

Executar:

```bash
php database/migrate.php
```

A migração `202608060001_consolidate_monitoring_case_ownership.php` conclui a adaptação de participantes e prorrogações.

## Validações realizadas

- 372 arquivos PHP passaram no `php -l`.
- 92 rotas registradas.
- 0 rotas duplicadas.
- 0 destinos internos sem rota.

## Cenários manuais recomendados

1. Criar um caso com dois participantes e confirmar que há somente um plano no banco.
2. Atualizar o plano por um participante e confirmar que o outro visualiza a atualização.
3. Adicionar participante ao caso e confirmar que nenhum plano adicional é criado.
4. Criar ação e remover seu autor do caso; confirmar que a ação permanece.
5. Criar dois casos para o mesmo aluno e confirmar planos e ações independentes.
6. Prorrogar o caso e confirmar atualização do período e registro no histórico.
