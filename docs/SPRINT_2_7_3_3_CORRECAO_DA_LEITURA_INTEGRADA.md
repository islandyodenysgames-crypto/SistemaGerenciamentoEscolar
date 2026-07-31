# Sprint 2.7.3.3 — Correção da Leitura Integrada

## Correções

- Envio de `studentMonitoring` ao componente da Leitura integrada.
- Exibição do tratamento de faltas sem justificativa no perfil do aluno.
- Exibição das ações gerais, de frequência e de ocorrências realizadas no acompanhamento.
- Remoção do campo manual `Resultado observado` do formulário Registrar Ação.
- Evolução positiva ou negativa passa a depender dos dados oficiais e do status do tratamento, não de uma avaliação manual duplicada.
- A recomendação genérica `Verificar faltas sem justificativa` deixa de ser repetida quando já existe uma leitura contextual do tratamento.

## Banco de dados

Nenhuma migration foi necessária. A coluna legada `result_status` permanece apenas por compatibilidade, mas novos registros são salvos com valor nulo.
