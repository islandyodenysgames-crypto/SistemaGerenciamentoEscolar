# Sprint 2.7.3.5 — Acompanhantes nos Casos de Inteligência

## Objetivo

Manter alunos em risco nos Insights prioritários mesmo quando já estão em acompanhamento, acrescentando contexto operacional à lista de casos.

## Alterações

- Os alunos acompanhados continuam contabilizados em `students_attention`.
- A consulta dos casos agora agrega os vínculos ativos de `student_monitoring_users`.
- A tabela de alunos ganhou a coluna **Acompanhantes**.
- A coluna mostra a quantidade de professores/acompanhantes ativos e seus nomes.
- A ação passou a ser **Ver acompanhamento**, direcionando ao painel individual do aluno.
- O painel de resumo ganhou os indicadores **Já acompanhados** e **Acompanhantes ativos**.
- O componente de acompanhamento no perfil recebeu a âncora `studentMonitoring`.

## Regra funcional

Acompanhamento não elimina risco. O insight responde à pergunta “quem precisa continuar recebendo atenção?”, enquanto a nova coluna responde “quem já está atuando nesse caso?”.

## Banco de dados

Nenhuma migration foi necessária. A implementação reutiliza as tabelas existentes:

- `student_monitoring`
- `student_monitoring_users`
- `users`
