# Sprint Painel TV 1.2 — Visual, banners e textos configuráveis

## Objetivo
Aproximar o Painel TV do modelo institucional de referência, aumentar a legibilidade à distância, exibir as imagens dos avisos e permitir que a gestão personalize as mensagens institucionais.

## Entregas
- Tipografia, indicadores, títulos e cards ampliados para televisores Full HD.
- Ranking, frequência, avisos e destaques com hierarquia visual semelhante ao modelo.
- Banner enviado no módulo Avisos da Gestão exibido como imagem no mural de comunicados.
- Mensagem orientativa quando não existem avisos ativos para o público Painel-TV.
- Campos configuráveis para Dica do dia, mensagem da dica, painel motivacional e frase do rodapé.
- Tema do Painel TV aplicado pelo atributo `data-theme`.
- Cache dos arquivos do Painel TV atualizado.

## Regra dos banners
O aviso precisa estar ativo, dentro do período de publicação e com o público-alvo `Painel-TV`. Havendo `banner_path`, a imagem ocupa o card e recebe uma camada inferior para garantir a leitura do título.

## Critérios de aceite
- Os textos principais devem ser legíveis a vários metros de distância.
- Imagens de banners cadastradas devem aparecer na apresentação.
- Os textos personalizados devem persistir após salvar as configurações.
- Avisos de outros públicos não devem aparecer no Painel TV.
