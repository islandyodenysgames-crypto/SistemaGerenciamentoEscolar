# Correção 2.9.3.1 — Restauração dos ícones

## Problema
O layout utilizava `https://unpkg.com/lucide@latest`. A distribuição publicada como `latest` deixou de garantir o mesmo bundle UMD/global usado pelo sistema, fazendo com que os elementos `data-lucide` permanecessem vazios.

## Correção
- versão Lucide fixada em `0.468.0`;
- CDN principal alterado para jsDelivr;
- fallback mantido no unpkg, na mesma versão;
- inicialização passou a repetir a tentativa por um curto período, cobrindo o carregamento do fallback;
- nenhum ícone, rota ou regra de negócio foi removido.
