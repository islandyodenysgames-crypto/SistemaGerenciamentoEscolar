# Sprint 2.3 — Dashboard Inteligente da Turma

## Objetivo
Transformar o painel da turma em uma visão analítica, reutilizando as regras configuráveis da Central de Inteligência.

## Entregas
- Nível e pontuação de risco da turma.
- Distribuição dos alunos por risco: baixo, atenção, alto e crítico.
- Indicadores de faltas sem justificativa, alunos afetados, ocorrências e alta gravidade.
- Tendências em relação ao período anterior.
- Lista dos alunos prioritários com acesso direto ao perfil.
- Recomendações automáticas para a gestão.
- Atalho para a Central de Inteligência.

## Arquitetura
- `IntelligenceRepository`: consultas agregadas e sinais individuais filtrados por turma.
- `IntelligenceService::classDashboard()`: composição, risco, tendências e recomendações.
- `StudentClassActions`: entrega do dashboard à view da turma.
- `components/classes/intelligence.php`: componente visual reutilizável.

## Banco de dados
Nenhuma migration é necessária.
