# Sprint 3.0.3.4 — Banner na Central e categorias sem redundância

## Alterações

- A imagem do cartão agora também aparece nos cards da página `/avisos`, mantendo proporção 4:5.
- Quando não existe banner personalizado, avisos com YouTube usam a miniatura do vídeo.
- A categoria é exibida sobre a imagem; sem imagem, aparece acima do cabeçalho.
- O botão de reprodução sobre a miniatura abre o modal já existente.
- `Urgente` foi removido das categorias e permanece apenas como prioridade.
- Avisos antigos com categoria `URGENT` são tratados como `GENERAL` para compatibilidade.
- Corrigida também a identidade visual da prioridade `HIGH` na listagem.

## Banco de dados

Nenhuma nova migração é necessária.
