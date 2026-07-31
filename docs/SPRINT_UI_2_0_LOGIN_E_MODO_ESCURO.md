# Sprint UI 2.0 — Login e modo escuro

## Objetivo
Modernizar a autenticação e disponibilizar tema claro/escuro persistente em toda a aplicação.

## Entregas
- Nova tela responsiva de login em duas áreas, inspirada no modelo fornecido.
- Nome apresentado no login: **Sistema de Gerenciamento Escolar**.
- Identidade visual verde e laranja preservada.
- Campo de senha com controle mostrar/ocultar.
- Mensagens de erro acessíveis e sem HTML inseguro.
- Botão de tema no login e no cabeçalho interno.
- Preferência salva em `localStorage` sob a chave `sge-theme`.
- Respeito automático à preferência de tema do sistema operacional no primeiro acesso.
- Aplicação antecipada do tema no `<head>` para reduzir o clarão na troca de páginas.
- Variáveis e ajustes globais para cards, tabelas, formulários, menu lateral e cabeçalho.
- Impressões mantidas em tema claro.

## Critérios de aceite
1. O botão do cabeçalho alterna entre lua e sol.
2. O tema permanece após recarregar ou navegar entre páginas.
3. A tela de login funciona em desktop, tablet e celular.
4. A autenticação continua usando as rotas e os campos atuais.
5. Não são exibidas opções de login social ou recuperação de senha sem implementação de backend.
