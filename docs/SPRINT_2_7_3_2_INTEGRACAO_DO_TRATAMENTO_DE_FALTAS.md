# Sprint 2.7.3.2 — Integração do tratamento de faltas

## Objetivo

Integrar as ações relacionadas a faltas sem justificativa à Leitura integrada do aluno, separando a avaliação humana da confirmação pelos registros oficiais.

## Estados exibidos

- Sem intervenção registrada;
- Em investigação;
- Motivo identificado;
- Em acompanhamento;
- Resolvido ou melhora em observação;
- Resolvido ou melhora confirmada pelos dados;
- Resolvido ou melhora não confirmada pelos dados;
- Tratamento sem melhora.

## Verificação

Quando o responsável marca `RESOLVED` ou `IMPROVED`, o sistema analisa os registros posteriores à data da ação:

- poucos registros: permanece em observação;
- nenhuma nova falta sem justificativa e frequência estável ou melhor: confirmado;
- novas faltas ou persistência da infrequência: não confirmado.

O status informado não é apagado nem alterado silenciosamente.

## Reabertura

Quando a melhora ou resolução não é confirmada, o perfil oferece `Reabrir tratamento`. A operação cria uma nova ação no histórico com situação `Em investigação`, preservando a conclusão anterior.
