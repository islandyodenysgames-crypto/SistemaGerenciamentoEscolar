# Sprint 2.9.2 — Explicabilidade da Inteligência

## Objetivo

Tornar as classificações e recomendações da Inteligência Escolar mais transparentes, sem introduzir diagnóstico, inferências causais ou informações que não estejam registradas no sistema.

## Alterações

### Inteligência da turma

- Novo card compacto **Por que esta análise?**.
- Exibe o nível e a pontuação de risco atuais.
- Resume fatos registrados na janela analisada: faltas sem justificativa, ocorrências e alunos em risco alto/crítico.
- Informa explicitamente que a leitura não representa diagnóstico.

### Recomendações da turma

- Cada recomendação agora possui uma explicação contextual própria.
- A explicação aparece ao passar o mouse ou ao focar o item pelo teclado.
- Foi adicionado o indicativo **Passe o mouse para entender**.
- O conteúdo diferencia faltas sem justificativa de faltas atenuadas e explica por que pendências ou níveis de risco geram uma sugestão.

### Inteligência do aluno

- Novo card compacto **Por que esta análise?**.
- Reutiliza os motivos já calculados pelo serviço de inteligência.
- Expõe apenas fatos registrados no período e a pontuação atual.

## Acessibilidade

- Recomendações podem receber foco por teclado.
- A explicação está disponível em `aria-label`.
- O tooltip também é exibido no foco visível.

## Regras preservadas

- Nenhum cálculo de risco foi alterado.
- Nenhuma nova causa foi inferida.
- Nenhuma rota, permissão, consulta ou migração foi adicionada.
