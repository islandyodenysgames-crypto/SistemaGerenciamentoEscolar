# Sprint 2.7.5.5 — Perfil do Aluno Otimizado

## Objetivo

Reduzir redundâncias no perfil do aluno e permitir que a situação seja compreendida rapidamente.

## Estrutura adotada

1. **Situação atual**: mostra somente o estado do acompanhamento, o risco e a recomendação atual.
2. **Análise atual**: apresenta uma interpretação breve do cenário, sem repetir datas, autores ou métricas detalhadas.
3. **Histórico completo**: concentra intervenções, entrada e saída de acompanhantes, início, retomada, edição e exclusão de ações.

## Remoções

- “Sem acompanhamento desde...”.
- Datas e autoria no resumo atual.
- Métricas repetidas da leitura integrada.
- Bloco separado “Ações realizadas no acompanhamento”.
- Timeline inteligente paralela no perfil.

As informações cronológicas permanecem preservadas no Histórico Completo.

## Arquivos principais

- `app/Services/Monitoring/StudentMonitoringService.php`
- `app/Views/components/students/profile/intelligence.php`
- `public/assets/css/pages/students-profile/intelligence.css`

## Banco de dados

Esta sprint não cria novas tabelas e não exige migração adicional.
