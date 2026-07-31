# Sprint 3.1.0.5 — Listagem de acompanhamentos sem duplicações

## Implementado

- Consulta principal com uma única linha por `student_monitoring.id`.
- Participantes carregados e agrupados dentro de cada caso.
- Visão “Meus acompanhamentos” por existência de vínculo do usuário.
- Visão “Todos os acompanhamentos” sem multiplicação por participantes.
- Contadores calculados por caso.
- Filtros por turma, status, motivo, professor, período e risco.
- Ordenação por prazo, aluno e risco.
- Paginação após consolidação e filtragem dos casos.
- Plano apresentado como plano compartilhado do caso.
- Estado do cartão apresentado como estado do caso.

## Critério atendido

Um caso com vários participantes aparece uma única vez na página `/acompanhamentos`.
