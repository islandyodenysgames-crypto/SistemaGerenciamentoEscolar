# Sprint 2.7.6 — Snapshots da Inteligência

## Objetivo

Criar uma base histórica diária confiável para as próximas sprints de tendências e Inteligência Escolar Preditiva.

A tabela antiga `student_intelligence_snapshots` continua existindo e não foi alterada. Ela mantém apenas o último estado por usuário/aluno para notificações. As novas tabelas são históricas e nunca substituem dias anteriores.

## Novas tabelas

### `student_intelligence_daily_snapshots`

Registra diariamente, por aluno ativo:

- turma atual;
- janela de análise;
- nível e pontuação de risco;
- registros de frequência, presenças e percentual;
- faltas sem justificativa e faltas atenuadas;
- ocorrências totais, graves e abertas;
- existência de acompanhamento ativo;
- acompanhantes ativos;
- recomendações pendentes;
- última intervenção efetiva;
- dias sem intervenção.

A chave única `(snapshot_date, student_id)` torna a captura idempotente.

### `school_intelligence_daily_snapshots`

Registra diariamente os principais indicadores da Central:

- alunos ativos e prioritários;
- distribuição por nível de risco;
- turmas em atenção;
- risco combinado;
- frequência e ocorrências;
- cobertura dos acompanhamentos;
- casos sem acompanhante;
- casos sem atualização de intervenção;
- recomendações pendentes.

## Captura automática

Ao abrir a Central de Inteligência, o sistema verifica se o snapshot do dia já existe. Se existir, nenhuma nova varredura é feita. Se ainda não existir, os dados do dia são gravados.

Antes da migração, essa verificação falha de forma segura e não impede a abertura da Central.

## Captura pelo console

```bash
php console.php intelligence:snapshot
```

Para recalcular e atualizar o snapshot do mesmo dia:

```bash
php console.php intelligence:snapshot --force
```

O modo `--force` não cria duplicidade; ele atualiza o registro diário existente.

## Migração

```bash
php console.php migrate
```

## Próxima sprint

A Sprint 2.8.0 usará essa série histórica para classificar tendências de frequência, ocorrências, risco e resposta das intervenções como melhora, estabilidade ou piora.
