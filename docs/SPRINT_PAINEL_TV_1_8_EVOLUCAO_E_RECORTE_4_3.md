# Sprint Painel TV 1.8 — Evolução e fidelidade do recorte 4:3

## Objetivo
Corrigir a composição do painel "Turmas com maior evolução" e garantir que o banner da Dica do Dia seja exibido na mesma proporção utilizada pelo editor de recorte.

## Correções
- Neutralização de regras CSS antigas que transformavam a lista de evolução em uma grade indevida.
- Reestruturação das linhas, posições, nomes, barras e percentuais.
- Garantia de que a lateral esquerda ocupe toda a altura disponível.
- Fixação do painel da Dica do Dia em proporção 4:3.
- Exibição do banner com a mesma área visual do arquivo 1200 × 900 gerado pelo editor.
- Atualização da versão do CSS para evitar cache antigo.

## Critérios de aceite
- Barras e textos do painel de evolução permanecem alinhados em Full HD.
- A primeira posição não é cortada ou escondida.
- A imagem exibida na Dica do Dia corresponde ao enquadramento confirmado no editor.
- O painel inferior "Contamos com você" permanece separado e ocupa o espaço restante da coluna direita.
