# Sprint 2.7.5.6.1 — Correção do painel individual de inteligência

## Problema

Ao abrir `/acompanhamentos`, o método `IntelligenceService::studentDashboard()` lançava:

- `Undefined variable $students`
- `array_filter(): Argument #1 ($array) must be of type array, null given`

## Causa

Um bloco destinado ao dashboard geral da Central de Inteligência foi duplicado dentro do método de análise individual. Esse bloco tentava filtrar uma coleção `$students`, que não existe no contexto de um único aluno.

## Correção

O bloco duplicado foi removido de `studentDashboard()`.

A lógica correta permanece em `dashboard()`, onde a coleção de alunos é realmente carregada e usada para calcular a cobertura operacional dos casos prioritários.

## Banco de dados

Nenhuma migração é necessária.
