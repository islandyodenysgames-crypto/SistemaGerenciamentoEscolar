# Sprint UI 2.0.1 — Correção dos ícones do Login

## Problema
A interface dependia do carregamento externo do Lucide. Em ambiente local sem acesso à internet, os elementos de ícone permaneciam vazios.

## Entregas
- Carregamento do renderizador Lucide local nos layouts autenticado e de login.
- Remoção da dependência obrigatória do CDN externo.
- Inclusão dos ícones usados no login: e-mail, cadeado, entrar, olho, olho fechado, escola, livro, formatura, gráficos, alerta e segurança.
- Compatibilidade do botão de tema e do botão Mostrar/Ocultar senha em ambiente offline.
