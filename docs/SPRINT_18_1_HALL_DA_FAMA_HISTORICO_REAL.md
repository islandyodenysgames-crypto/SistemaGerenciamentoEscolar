# Sprint 18.1 — Hall da Fama com histórico real

## Objetivo
Substituir os quatro cartões repetidos derivados do ranking diário por resultados calculados em períodos próprios.

## Implementado
- **Campeã do dia:** melhor frequência na data atual.
- **Campeã da semana:** melhor média acumulada desde segunda-feira.
- **Maior evolução:** maior crescimento percentual da semana atual em comparação com a semana anterior.
- **Destaque do mês:** melhor média acumulada desde o primeiro dia do mês.
- Critérios de desempate por quantidade de presenças, registros e nome da turma.
- Uso da fotografia e da versão de cache cadastradas na turma.
- Estado vazio individual quando ainda não existem dados suficientes.
- Exibição da quantidade de dias de chamada usada no cálculo.
- Exibição da comparação anterior → atual no cartão de evolução.
- Inclusão dos dados históricos no endpoint JSON do Painel TV.

## Arquivos principais
- `app/Services/TvHallOfFameService.php`
- `app/Controllers/TvPanelController.php`
- `app/Views/pages/tv-panel/show.php`
- `public/assets/css/pages/tv-panel.css`

## Banco de dados
Nenhuma migração adicional é necessária. Os cálculos usam `attendance`, `attendance_items` e `school_classes`.
