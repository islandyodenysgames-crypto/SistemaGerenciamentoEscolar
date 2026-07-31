# Sprint 2.8.1 — Inteligência Escolar Preditiva Individual

## Objetivo

Projetar, de forma explicável e conservadora, o cenário provável de cada aluno para os próximos 14 dias a partir dos snapshots e tendências da Inteligência Escolar.

## Componentes criados

- `App\Services\Intelligence\PredictiveAnalysisService`
- `App\ValueObjects\Intelligence\StudentPrediction`

## Regras

A projeção considera:

- pontuação e nível de risco no snapshot mais recente;
- tendência histórica da frequência;
- tendência histórica das ocorrências;
- tendência histórica do risco;
- acompanhamento inexistente ou intervenção atrasada;
- recomendações pendentes;
- ocorrências de alta gravidade.

O resultado não é apresentado como probabilidade estatística. O sistema informa um cenário provável e mostra todas as evidências que influenciaram a projeção.

## Resultados possíveis

- Histórico insuficiente;
- Tendência de estabilidade;
- Tendência de melhora;
- Tendência de agravamento;
- Risco de agravamento crítico.

## Confiança

A confiança continua vinculada à quantidade de snapshots e ao intervalo histórico:

- baixa para históricos iniciais;
- média a partir de cinco capturas distribuídas por pelo menos sete dias;
- alta a partir de quatorze capturas distribuídas por pelo menos 21 dias.

## Interface

### Central de Inteligência

Os alunos prioritários recebem um selo compacto com o cenário projetado.

### Perfil do aluno

Nova seção com:

- previsão para 14 dias;
- risco atual e risco projetado;
- nível de confiança;
- evidências utilizadas;
- ações preventivas sugeridas;
- aviso de transparência sobre a natureza da projeção.

## Banco de dados

Nenhuma migração nova é necessária. A sprint utiliza as tabelas diárias criadas na Sprint 2.7.6.
