# Sprint 2.6.2 — Gestão de Ações Inteligentes

## Objetivo

Permitir que um insight calculado dinamicamente seja acompanhado pela equipe sem duplicar o fluxo operacional de ocorrências.

## Decisões contra redundância

- Providências de ocorrências continuam no módulo de Ocorrências.
- A nova estrutura registra apenas o acompanhamento gerencial do insight agregado.
- O identificador estável já existente em `key` relaciona o insight recalculado ao acompanhamento salvo.
- Não foi criado um painel separado de resumo executivo.
- Não foi implementado o indicador de tempo médio de resolução.

## Estados persistidos

- Novo
- Em acompanhamento
- Resolvido
- Arquivado

Cada acompanhamento pode registrar responsável, data prevista, observação e histórico de alterações.

## Banco de dados

Migration: `202607220003_create_intelligence_actions_tables.php`

Tabelas:

- `intelligence_actions`
- `intelligence_action_history`

Execute as migrations pelo comando já utilizado no projeto.
