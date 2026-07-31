# Sprint Painel TV 1.6 — Persistência do banner da Dica do Dia

## Problema
O recorte era gerado e o arquivo era salvo em `public/uploads/tv-panel`, porém o caminho não permanecia após o redirecionamento.

## Causa
O formulário utilizava `SettingRepository::upsert()`. Quando `tv_panel.configuration` já existia, o `ON DUPLICATE KEY UPDATE` do repositório atualizava os metadados, mas preservava a coluna `value`. Assim, o JSON continuava com `dailyTipBanner` vazio ou com o caminho anterior.

## Correção
- Se a configuração já existe, o controller utiliza `updateValue('tv_panel', 'configuration', $json)`.
- Se ainda não existe, utiliza `upsert()` para criar o registro completo.
- O JSON passa a ser gerado com `JSON_THROW_ON_ERROR` para evitar falhas silenciosas.

## Critério de aceite
1. Selecionar e recortar uma imagem 4:3.
2. Salvar as configurações.
3. Reabrir `/painel-tv/configuracoes` e visualizar o banner salvo.
4. Abrir `/painel-tv` e visualizar a imagem na Dica do Dia.
