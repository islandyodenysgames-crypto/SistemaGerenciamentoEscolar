# Sprint Painel TV 1.5 — Correção de recorte e banner da Dica

## Objetivo
Corrigir o salvamento do banner da Dica do Dia e garantir que o enquadramento selecionado no editor seja o mesmo exibido no Painel TV.

## Entregas
- Banner da Dica gerado como arquivo JPEG 1200 × 900 e enviado via `multipart/form-data`, evitando limites de tamanho de campos POST em Base64.
- Elemento decorativo da planta ocultado sempre que houver banner.
- Painel da Dica com proporção real 4:3, igual à área de recorte.
- Banners de Avisos exibidos em proporção real 4:5, igual ao editor 1080 × 1350.
- Correção da conversão das coordenadas do ponteiro quando o canvas do recorte é redimensionado pelo CSS.
- Atualização das versões dos arquivos JavaScript para evitar cache antigo.
