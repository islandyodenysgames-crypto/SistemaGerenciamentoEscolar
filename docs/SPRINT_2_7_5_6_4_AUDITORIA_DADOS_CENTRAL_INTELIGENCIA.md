# Sprint 2.7.5.6.4 — Auditoria dos dados da Central de Inteligência

## Objetivo
Revisar os critérios e as consultas de todos os indicadores exibidos em `/inteligencia`, com foco no card “Sem intervenção há 14 dias”.

## Correções

### Sem intervenção há 14 dias
- A consulta agora seleciona um único acompanhamento ativo atual por aluno: o registro ativo mais recente.
- A última intervenção é obtida somente de `student_monitoring_actions`, vinculada ao acompanhamento selecionado e com `deleted_at IS NULL`.
- Eventos administrativos da timeline não contam como intervenção.
- O cálculo de dias usa datas de calendário, evitando diferenças causadas pelo horário de execução.
- Cada aluno é contado no máximo uma vez.

### Cobertura e acompanhantes
- Um caso só é coberto quando possui acompanhamento ativo atual e ao menos um acompanhante ativo dentro da vigência.
- Acompanhamento sem acompanhante continua sendo contado em “Sem acompanhante”.

### Recomendações pendentes
- São contados registros `PENDING` ou `IN_PROGRESS` pertencentes ao acompanhamento ativo atual.

### Turmas em atenção
- Agora somente turmas cujo risco calculado é diferente de `LOW` entram no total e na lista de turmas prioritárias.

### Alunos e matrículas
- As consultas de risco usam somente a matrícula ativa mais recente para obter a turma do aluno, evitando duplicidades quando há mais de uma matrícula ativa indevida.

## Critérios preservados
- Frequência: apenas status `F` conta como falta sem justificativa. `FJ`, `AM` e `FO` não entram.
- Ocorrências graves: severidades `HIGH` e `CRITICAL`.
- Pendências críticas: ocorrências `CRITICAL`, abertas e além do prazo configurado.
- Risco combinado: aluno com falta sem justificativa e ocorrência no período.
