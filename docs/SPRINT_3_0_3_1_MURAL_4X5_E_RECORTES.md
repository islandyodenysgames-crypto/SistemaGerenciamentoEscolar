# Sprint 3.0.3.1 — Mural 4:5 e fluxo de recorte

## Alterações

- O Dashboard agora recebe todos os avisos ativos e vigentes, sem o limite anterior de cinco registros.
- O mural foi reconstruído em cartões verticais na proporção 4:5, mostrando até quatro cartões simultaneamente em telas grandes.
- O carrossel mantém todos os avisos no DOM e permite navegar por setas, indicadores, teclado e gesto de arrastar.
- As imagens dos cartões são preparadas em 1080 × 1350 px.
- Também são indicadas como referências válidas 800 × 1000 px e 600 × 750 px.
- Selecionar a imagem não abre mais o editor automaticamente.
- Após a seleção, o botão `Enviar imagem` é habilitado.
- O clique nesse botão abre o editor 4:5.
- Enquanto uma nova imagem estiver selecionada e ainda não tiver o recorte confirmado, o salvamento do aviso fica bloqueado.
- Após `Confirmar recorte`, a prévia é atualizada e o botão de salvar é liberado.

## Banco de dados

Não há nova migração nesta correção. Continua sendo necessária apenas a migração da Sprint 3.0.3:

`202608030001_expand_school_notices_media.php`
