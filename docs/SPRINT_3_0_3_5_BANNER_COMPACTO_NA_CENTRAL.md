# Sprint 3.0.3.5 — Banner compacto na Central de Avisos

## Objetivo

Reduzir o tamanho excessivo das imagens dos cartões na página `/avisos`, mantendo a proporção vertical 4:5 e aproximando o porte visual dos cartões exibidos na Página Inicial.

## Alteração realizada

- A imagem continua com proporção 4:5.
- A largura máxima foi limitada a 300 px, equivalente ao limite utilizado nos cartões do mural da Página Inicial.
- O banner fica centralizado dentro do aviso.
- Em telas menores, o banner respeita a largura disponível sem ultrapassar 300 px.
- Foram preservados os selos de categoria, o botão de vídeo, o recorte da imagem e o efeito suave de hover.

## Arquivo alterado

- `public/assets/css/pages/notices.css`

## Banco de dados

Nenhuma migração necessária.
