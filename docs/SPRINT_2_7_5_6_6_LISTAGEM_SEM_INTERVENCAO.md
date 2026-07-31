# Sprint 2.7.5.6.6 — Listagem de casos sem atualização de intervenção

## Objetivo

Fazer o card **Sem atualização de intervenção há 14 dias** abrir uma listagem específica na Central de Inteligência, em vez de encaminhar para o painel geral de acompanhamentos.

## Nova navegação

O card agora aponta para:

```text
/inteligencia/casos?tipo=stale_monitoring_actions
```

## Critério da listagem

A página utiliza o mesmo cálculo do card da Central:

- considera apenas alunos prioritários;
- exige acompanhamento ativo no período atual;
- considera a data efetiva mais recente entre data da ação, criação e atualização;
- inclui o aluno quando o intervalo é de 14 dias ou mais;
- quando não existe ação formal, calcula o intervalo desde o início do acompanhamento;
- conta e exibe cada aluno apenas uma vez.

## Informações exibidas

A tabela apresenta:

- aluno;
- turma;
- risco;
- data da última atualização de intervenção;
- quantidade de dias sem atualização;
- acompanhantes ativos;
- acesso direto ao acompanhamento no perfil.

## Resumo da página

Os cards superiores mostram:

- total de casos;
- casos sem nenhuma ação registrada;
- maior intervalo sem atualização;
- casos com acompanhante ativo.

## Banco de dados

Nenhuma migração foi necessária.
