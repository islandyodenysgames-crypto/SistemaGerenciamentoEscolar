# Sprint Painel TV 1.6 — Correção do salvamento da Dica do Dia

## Entregas

- Área de recorte 4:3 claramente demarcada com moldura, grade e indicação de 1200 × 900 px.
- Envio redundante do recorte: arquivo JPEG e Data URL compacta.
- Compatibilidade com navegadores que não permitem substituir `input.files` via JavaScript.
- Atualização de cache do script para a versão 3.

## Critérios de aceite

1. A zona final do recorte é visualmente evidente.
2. O usuário confirma o recorte antes de salvar.
3. O banner é salvo mesmo quando `DataTransfer` não é aceito pelo navegador.
4. O arquivo final possui 1200 × 900 px.
