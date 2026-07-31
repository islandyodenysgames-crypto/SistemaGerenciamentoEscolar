# Sprints 20.1 a 20.9 — Ciclo do Ano Letivo

## Entregas

- 20.1: períodos bimestrais, trimestrais, semestrais ou personalizados, com validação de sobreposição.
- 20.2: dias letivos, feriados, recessos, férias, planejamento, reposições e sábados letivos.
- 20.3: eventos do calendário vinculados ao ano/período e capazes de alterar o calendário letivo.
- 20.4: fechamento e reabertura de períodos, snapshot dos indicadores e bloqueio de frequência em período fechado.
- 20.5: encerramento seguro do ano somente após todos os períodos estarem fechados.
- 20.6: assistente para criar o próximo ano e copiar a estrutura de períodos.
- 20.7: contexto acadêmico central no Dashboard, Painel TV, Calendário e Frequência.
- 20.8: estatísticas históricas baseadas em snapshots anuais imutáveis.
- 20.9: trilha de auditoria para fechamento, reabertura e criação assistida.

## Fonte da meta de frequência

A meta permanece centralizada em `Configurações > Metas`, na chave
`school_goals.frequency_goal`. O cadastro do ano letivo não duplica esse valor.

## Migrações

1. `202608120001_create_school_years_table.php`
2. `202608120002_create_school_periods_table.php`
3. `202608120003_create_school_days_and_integrate_calendar.php`
4. `202608120004_create_academic_closures_snapshots_and_audit.php`

## Implantação

Execute `php console.php migrate`. Depois:

1. cadastre e ative o ano;
2. cadastre os períodos;
3. gere os dias úteis e ajuste as exceções;
4. vincule eventos relevantes do calendário;
5. feche períodos somente após conferir os lançamentos;
6. encerre o ano e use o assistente para preparar o próximo.

Antes do primeiro encerramento em produção, faça uma exportação completa na área
`Configurações > Backup e Dados`.
