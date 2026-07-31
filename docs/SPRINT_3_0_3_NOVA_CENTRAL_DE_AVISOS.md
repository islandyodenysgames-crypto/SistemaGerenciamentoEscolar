# Sprint 3.0.3 — Nova Central de Avisos

## Destaques

- Reformulação do módulo existente `/avisos`.
- Carrossel responsivo no Dashboard, com autoplay, setas, indicadores e gestos.
- Categorias, quatro níveis de prioridade, aviso em destaque e fixação.
- Resumo específico para o banner, conteúdo completo e vídeo do YouTube.
- Agendamento por início e expiração.
- Imagem de capa recomendada em **1780 × 700 px** (proporção aproximada de 2,54:1).
- Recortador visual obrigatório quando uma nova imagem é selecionada: zoom, arraste e exportação padronizada em 1780 × 700 px.
- Upload múltiplo de anexos e exclusão individual na edição.

## Migração

Execute:

`202608030001_expand_school_notices_media.php`

Ela adiciona os novos campos em `school_notices` e cria `school_notice_attachments`.

## Limites

- Banner: JPG, PNG ou WebP, até 8 MB.
- Anexos: até 10 por envio e 25 MB por arquivo.
- Dimensão final do recorte: 1780 × 700 px.

## Armazenamento

`public/uploads/notices/ANO/MÊS/ID_DO_AVISO/`
