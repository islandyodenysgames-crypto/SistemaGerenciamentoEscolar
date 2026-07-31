# Sprint UI 2.0.6 — Auditoria global do modo escuro

## Objetivo
Eliminar fundos claros, cores legadas e textos camuflados remanescentes em todas as páginas do Sistema de Gerenciamento Escolar.

## Trabalho executado
- Varredura dos 89 arquivos CSS do projeto.
- Identificação de mais de 500 regras com fundos claros fixos.
- Criação de uma camada final de compatibilidade carregada depois dos estilos de todas as páginas.
- Padronização de quatro níveis visuais: fundo da página, painel, painel elevado e hover.
- Correção geral de cards, painéis, widgets, filtros, tabelas, modais, listas, formulários e gráficos.
- Tratamento de estilos inline legados com branco e tons claros.
- Correção de textos escuros fixos sobre superfícies escuras.
- Preservação de badges, estados semânticos, imagens, banners e impressão em tema claro.

## Critérios de aceite
- Nenhum card estrutural deve permanecer branco no modo escuro.
- Títulos, descrições, labels e valores devem manter contraste legível.
- Painéis vizinhos devem possuir bordas e sombras perceptíveis.
- Campos, tabelas, modais e filtros devem acompanhar o tema.
- Cores de sucesso, atenção, perigo e informação devem continuar distinguíveis.
