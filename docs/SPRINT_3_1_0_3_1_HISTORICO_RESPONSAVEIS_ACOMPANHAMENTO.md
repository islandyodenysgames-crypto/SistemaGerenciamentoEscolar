# Sprint 3.1.0.3.1 — Responsáveis pelos acompanhamentos concluídos

## Alterações

- Os cartões de casos concluídos exibem os nomes de todos os participantes históricos.
- Quando disponível, o cartão também informa quem encerrou o caso e a data do encerramento.
- Ao abrir um caso concluído, a seção de participantes passa a exibir todos que realizaram o acompanhamento, inclusive vínculos encerrados.
- A data de saída de cada participante é preservada na visualização.
- O resumo do caso diferencia corretamente acompanhamento ativo e concluído.

## Banco de dados

Nenhuma nova migração foi necessária. A implementação utiliza os vínculos e eventos de acompanhamento já existentes.
