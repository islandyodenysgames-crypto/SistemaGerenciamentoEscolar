# Sprint 2.7.5.6.2 — Explicações contextuais nos cards

## Objetivo
Facilitar a compreensão dos indicadores da Central de Inteligência sem adicionar textos permanentes ou aumentar a página.

## Implementação
Os principais cards receberam explicações contextuais exibidas ao posicionar o mouse sobre o componente.

As explicações também aparecem ao navegar pelo teclado, usando `Tab`, por meio de `:focus-visible`.

## Cards contemplados
- Alunos prioritários;
- Turmas em atenção;
- Risco combinado;
- Pendências críticas;
- Cobertura dos casos prioritários;
- Sem acompanhante;
- Sem intervenção há 14 dias;
- Recomendações pendentes;
- Frequência;
- Ocorrências;
- cards individuais de alunos prioritários;
- cards de turmas prioritárias.

## Critérios explicados
Os textos informam o significado do número e, quando necessário, o critério usado no indicador. Por exemplo, o card de frequência esclarece que FJ, AM e FO não entram no total de faltas sem justificativa.

## Arquivos modificados
- `app/Views/pages/intelligence/index.php`
- `public/assets/css/pages/intelligence.css`
