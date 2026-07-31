# Sprint 3.2 — Experiência, Inteligência e Produtividade

## Escopo implementado

- Bloco A: pesquisa global instantânea e página de resultados agrupados.
- Bloco B: favoritos personalizados por usuário para alunos, turmas e casos.
- Bloco D: preservação e refinamento da inteligência contextual já existente no perfil do aluno.
- Bloco E: visão gerencial no Dashboard com distribuição de risco, cobertura, turmas em atenção e turmas em melhora.
- Bloco F: padronização visual, responsividade e componentes reutilizáveis.

## Fora do escopo

- Bloco C — Ações rápidas.
- Relatórios e exportações.

## Migração

Execute as migrações do sistema para criar a tabela `user_favorites`:

```bash
php console migrate
```
