# Sprint 2.6.1 — Insights Prioritários 2.0

## Objetivo

Evoluir o componente já existente de Insights Prioritários sem criar painéis ou serviços redundantes.

## Implementações

- filtros rápidos por severidade e categoria, sem recarregar a página;
- categoria visível em cada insight;
- cálculo de impacto com pontuação e escala de cinco níveis;
- estimativa de confiança baseada na quantidade de sinais disponíveis;
- explicação expansível de como o insight foi calculado;
- ações contextuais, como Ver alunos, Ver turmas, Ver ocorrências, Analisar frequência e Comparar;
- mesma experiência na Página inicial e na Central de Inteligência, por meio do componente reutilizável existente;
- responsividade e proteção contra estouro de textos.

## Decisão arquitetural

O estado Novo/Em acompanhamento/Resolvido não foi simulado apenas na interface. Essa funcionalidade requer persistência para não apresentar ao usuário um estado que não foi realmente salvo. Ela será implementada em uma etapa específica com histórico e auditoria.

## Banco de dados

Nenhuma migration é necessária nesta entrega.
