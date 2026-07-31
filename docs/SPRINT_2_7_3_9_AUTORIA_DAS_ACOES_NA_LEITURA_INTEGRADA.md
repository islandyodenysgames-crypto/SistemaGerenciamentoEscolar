# Sprint 2.7.3.9 — Autoria das ações na Leitura Integrada

## Objetivo

Identificar claramente quem realizou cada intervenção exibida na Leitura integrada do aluno.

## Alterações

- A seção **Ações realizadas no acompanhamento** passou a mostrar **Realizada por: Nome do usuário** em cada ação.
- O resumo **Última intervenção** das mensagens dinâmicas passou a informar também o responsável pela ação.
- Registros antigos sem autoria disponível exibem **Responsável não identificado**.
- A consulta existente já retornava `author_name`; portanto, nenhuma migration foi necessária.
