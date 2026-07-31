# Sprint — Atenção Hoje e Índice Geral da Escola

## Página inicial

- O painel "Insights prioritários" foi substituído por "O que merece atenção hoje".
- O painel continua recolhível, com persistência da preferência no navegador.
- Os itens utilizam os insights existentes, mas são apresentados como prioridades com ações diretas.
- O card redundante "Turmas Pendentes" foi substituído pelo "Índice Geral da Escola".

## Índice Geral da Escola

Pontuação total de 100 pontos:

- Frequência: 40 pontos.
- Ocorrências: 25 pontos.
- Risco dos alunos: 20 pontos.
- Cobertura dos acompanhamentos: 10 pontos.
- Turmas em atenção: 5 pontos.

Ao clicar no card são exibidos:

- composição e motivo da pontuação;
- comparação com 7, 30 e 90 dias;
- tendência histórica;
- principal fator positivo;
- principal fator que merece atenção.

## Histórico

A migração `202608010001_create_school_general_index_snapshots_table.php` cria o histórico diário. Antes da migração, o índice atual continua funcionando, mas as comparações aparecem como sem histórico.
