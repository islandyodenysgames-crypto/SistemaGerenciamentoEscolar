# Sprint 2.9.2.1 — Ajustes de explicabilidade e perfil do aluno

## Correção do indicador de 14 dias

O indicador "Sem atualização de intervenção há 14 dias" não considera mais acompanhamentos recém-abertos sem intervenção como atrasados imediatamente.

Regra atual:

- com intervenção: conta desde a data da última intervenção formal;
- sem intervenção: conta desde a data de início do acompanhamento;
- entra no indicador somente ao atingir 14 dias;
- eventos administrativos não contam como intervenção.

## Explicações recolhíveis

O card "Por que esta análise?" passa a iniciar fechado e abre por clique, tanto na Inteligência da turma quanto no perfil do aluno.

## Perfil do aluno

A apresentação da Inteligência Escolar foi suavizada:

- menos sombras, gradientes e cores simultâneas;
- resumo atual permanece visível;
- justificativa fica recolhida;
- evidências e ações preditivas ficam recolhidas;
- componentes secundários usam fundos neutros;
- navegação por teclado é preservada por elementos `details/summary` nativos.

Nenhum cálculo de risco ou regra de permissão foi alterado.
