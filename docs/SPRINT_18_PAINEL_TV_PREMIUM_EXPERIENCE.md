# Sprint 18 — Painel TV Premium Experience

## Implementado
- Novo slide Hall da Fama com quatro reconhecimentos e fotos de turmas.
- Ranking premium com medalhas, percentual da campeã e animação das barras.
- Cabeçalho institucional com nome, logotipo e slogan configurável.
- Rodapé permanente com alunos, turmas e frequência do dia.
- Temas institucionais verde, azul, roxo, vermelho e automático.
- Controle para ativar animações e ajustar sua duração.
- Entradas suaves nos avisos, barras e indicadores.
- Ajustes responsivos para Full HD e 4K.

## Compatibilidade
A configuração anterior é preservada por valores padrão. Nenhuma migração de banco é necessária, pois os novos campos são armazenados no JSON `tv_panel.configuration`.

## Observação sobre o Hall da Fama
Nesta entrega, os cartões são alimentados pelos melhores resultados disponíveis no ranking atual. A estrutura visual e de configuração já está preparada para receber, em uma evolução futura, agregações históricas específicas de dia, semana e mês.
