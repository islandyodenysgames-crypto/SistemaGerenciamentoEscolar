# Sprint Painel TV 1.1 — Datas em português e avisos exclusivos

## Objetivo
Eliminar o uso de `strftime()`, padronizar datas e rótulos do Painel TV em português e permitir avisos exclusivos para a apresentação institucional.

## Entregas
- Helper `App\\Helpers\\DateHelper` sem dependência de `strftime()` ou da extensão Intl.
- Meses e dias da semana em português.
- Tradução das categorias de avisos e dos tipos de eventos exibidos no Painel TV.
- Novo público-alvo `Painel-TV` no cadastro e edição de avisos.
- Painel TV consulta somente avisos ativos destinados a `TV_PANEL`.
- Avisos exclusivos do Painel TV não aparecem no Dashboard e não geram notificações individuais.

## Critérios de aceite
- Nenhum aviso `Deprecated: Function strftime()` ao abrir `/painel-tv`.
- Nenhum rótulo interno como `MANAGEMENT` ou `EVENT` na apresentação.
- O seletor Público-alvo apresenta `Painel-TV`.
- Apenas avisos com público-alvo `Painel-TV` aparecem na tela de avisos do Painel TV.
