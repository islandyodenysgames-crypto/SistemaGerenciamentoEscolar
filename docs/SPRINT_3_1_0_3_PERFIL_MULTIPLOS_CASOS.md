# Sprint 3.1.0.3 — Perfil do aluno com múltiplos casos

## Entregue

- Seleção explícita de caso por `case_id` no Perfil do Aluno.
- Validação do caso selecionado: somente casos pertencentes ao aluno são aceitos.
- Seleção padrão prioriza caso ativo com acompanhantes; na ausência, usa o caso mais recente.
- Lista visual de casos ativos e concluídos, com contadores e quantidade de acompanhantes ativos.
- Plano, participantes, ações, métricas, leituras integradas, recomendações e linha do tempo passam a ser montados para o caso selecionado.
- Casos em que o usuário não participa podem ser consultados em modo somente leitura.
- Ações de manutenção do vínculo ficam disponíveis apenas quando aplicáveis.
- O fluxo “Novo problema e outro plano” permanece disponível e independente.

## Banco de dados

Nenhuma nova migração é necessária nesta sprint.
