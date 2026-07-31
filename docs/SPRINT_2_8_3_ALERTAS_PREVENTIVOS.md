# Sprint 2.8.3 — Alertas Preventivos

## Objetivo
Transformar as previsões individuais e coletivas em avisos proativos no sistema de notificações, sem criar probabilidades artificiais nem repetir alertas continuamente.

## Alertas individuais
São criados para o professor acompanhante quando a previsão do aluno indicar:

- tendência de agravamento; ou
- possibilidade de alcançar risco crítico nos próximos 14 dias.

O alerta abre diretamente a Inteligência do perfil do aluno.

## Alertas por turma
Usuários da administração, direção e coordenação recebem alerta quando uma turma apresenta tendência coletiva de agravamento. O aviso abre o Painel da Turma na seção Inteligência.

## Controle de repetição
A mesma condição gera no máximo um alerta por semana e por aluno ou turma. Estabilidade, melhora e histórico insuficiente não geram avisos preventivos.

## Painel da turma recolhível
O painel “Inteligência da turma” agora pode ser recolhido. A escolha fica salva no navegador. Por padrão ele inicia recolhido, evitando ocupar espaço quando o professor precisa apenas registrar frequência ou ocorrência. O atalho “Inteligência” e alertas da turma abrem o painel automaticamente.

## Banco de dados
Nenhuma migração adicional é necessária. A sprint utiliza a tabela de notificações e os snapshots já existentes.
