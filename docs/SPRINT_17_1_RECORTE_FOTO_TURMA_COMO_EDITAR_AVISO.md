# Sprint 17.1 — Recorte da foto da turma como em Editar Aviso

## Objetivo

Padronizar o envio da foto da turma com o mesmo fluxo usado no formulário **Editar Aviso**:

1. Selecionar imagem;
2. Clicar em **Enviar imagem**;
3. Ajustar o enquadramento no editor;
4. Confirmar o recorte;
5. Salvar a turma.

## Implementação

- Editor modal com proporção fixa 16:9.
- Canvas de trabalho em 800 × 450 px.
- Arquivo final em JPEG com 1600 × 900 px.
- Movimento por arraste e controle de zoom.
- Grade de enquadramento e identificação da área final.
- Prévia atualizada somente após a confirmação do recorte.
- Bloqueio do botão de salvar quando existe uma imagem selecionada ainda sem recorte confirmado.
- Compatibilidade mantida no backend com o upload antigo.
- Validação no servidor do Data URI, tipo JPEG, limite de 8 MB e dimensões exatas.
- Substituição e remoção segura da foto anterior.

## Critérios de aceite

- Selecionar um arquivo não salva nem altera imediatamente a foto da turma.
- O botão **Enviar imagem** abre o editor de recorte.
- O usuário pode mover e ampliar a imagem.
- O enquadramento confirmado é exatamente o exibido no card 16:9 do Painel TV.
- A foto só é persistida quando o formulário da turma é salvo.
- Não é possível salvar uma nova imagem selecionada sem confirmar o recorte.
- A foto anterior continua disponível quando o usuário cancela o editor ou não salva o formulário.
