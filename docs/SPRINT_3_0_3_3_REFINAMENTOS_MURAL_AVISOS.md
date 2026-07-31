# Sprint 3.0.3.3 — Refinamentos do Mural de Avisos

## Alterações

- Removido o destaque visual permanente do primeiro banner.
- O cartão atual do carrossel mantém apenas `aria-current`, sem receber transformação ou sombra especial.
- A animação de elevação ocorre exclusivamente quando o ponteiro está sobre qualquer banner.
- A consulta da Central de Avisos agora agrega os anexos em lote para todos os avisos.
- Cada cartão em `/avisos` exibe a seção **Arquivos e recursos** quando houver conteúdo relacionado.
- Arquivos recebem ícones conforme o tipo e botão **Abrir**.
- Links válidos do YouTube recebem botão **Assistir** e são abertos em modal responsivo incorporado.
- O modal encerra a reprodução ao fechar e oferece a opção **Abrir no YouTube**.

## Banco de dados

Nenhuma migração adicional é necessária.
