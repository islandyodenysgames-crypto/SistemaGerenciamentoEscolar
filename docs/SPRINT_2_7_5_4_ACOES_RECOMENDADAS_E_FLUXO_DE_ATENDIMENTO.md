# Sprint 2.7.5.4 — Ações Recomendadas e Fluxo de Atendimento

## Objetivo

Transformar os sinais já calculados pelo acompanhamento em orientações práticas, persistentes e auditáveis para professores e gestão.

## Entregas

- recomendação automática por acompanhamento;
- critérios baseados em acompanhantes ativos, frequência, ocorrências e tempo sem intervenção;
- prioridades `CRITICAL`, `HIGH`, `ATTENTION` e `POSITIVE`;
- estados `PENDING`, `IN_PROGRESS`, `COMPLETED`, `DISMISSED` e `SUPERSEDED`;
- fila “Casos para revisar hoje” em `/acompanhamentos`;
- atalhos para registrar ação, adicionar acompanhante, consultar histórico e abrir o perfil;
- atendimento automático da recomendação quando uma nova intervenção é registrada;
- motivo obrigatório para dispensar uma recomendação;
- preservação do histórico das recomendações substituídas.

## Nova tabela

`student_monitoring_recommendations`

A tabela guarda a recomendação, prioridade, estado, responsável pelo tratamento, observações e datas de geração/atendimento.

## Migração

Execute as migrações antes de abrir a página de acompanhamentos:

```bash
php console.php migrate
```

## Regras principais

1. Sem acompanhante ativo: adicionar acompanhante.
2. Frequência abaixo de 75% com ocorrência pendente: contato com responsáveis e coordenação.
3. Frequência abaixo de 85%: intervenção sobre frequência.
4. Ocorrência pendente: revisar ocorrências.
5. Quatorze dias ou mais sem ação: registrar nova intervenção.
6. Frequência positiva e sem ocorrências pendentes: manter em observação.
7. Demais cenários: revisar o plano de acompanhamento.

## Compatibilidade

A implementação mantém as ações, notificações, timeline, permissões e históricos existentes.
