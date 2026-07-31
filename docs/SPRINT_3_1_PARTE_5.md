# Sprint 3.1 — Parte 5

## Reincidência

A reincidência deixa de ser calculada apenas pela quantidade total ou pelo tipo técnico da ocorrência.

Um caso recorrente exige pelo menos dois registros do mesmo aluno, dentro da janela analisada, que atendam a um dos critérios:

1. mesmo título normalizado; ou
2. mesma disciplina.

Grupos por título têm prioridade. Um grupo por disciplina que contenha exatamente os mesmos registros de um grupo por título não é duplicado na interface.

A regra foi aplicada ao painel de Ocorrências, ao perfil do aluno, às notificações de acompanhantes e à regra da Central de Inteligência.

## Mapa de calor

O mapa de calor da frequência ganhou atualização assíncrona, sem recarregar a página, para:

- última semana;
- último mês;
- último bimestre;
- último semestre.

O período fica preservado na URL. O tooltip informa percentual, presentes e ausentes.
