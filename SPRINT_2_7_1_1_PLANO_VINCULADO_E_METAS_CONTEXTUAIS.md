# Sprint 2.7.1.1 — Plano vinculado e metas contextuais

## Ajustes

- O Plano de Acompanhamento permanece disponível somente quando o usuário possui vínculo ativo com o acompanhamento do aluno.
- A interface deixa explícito que o plano usa o mesmo aluno, período e motivo do vínculo ativo.
- O valor inicial não é mais informado manualmente.
- Ao selecionar Frequência, o sistema mostra a frequência atual calculada no período do vínculo e a quantidade de registros usados.
- Ao selecionar Ocorrências, o sistema mostra o total atual no período.
- A meta desejada é opcional e possui rótulos, limites e exemplos específicos para cada indicador.
- Ao salvar, o backend recalcula o valor inicial usando os dados oficiais, impedindo manipulação pelo formulário.

## Banco de dados

Nenhuma nova migration é necessária. A tabela `student_monitoring_plans` existente continua sendo utilizada.
