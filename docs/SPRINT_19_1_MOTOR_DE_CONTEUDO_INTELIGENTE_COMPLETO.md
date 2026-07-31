# Sprint 19.1 — Motor de Conteúdo Inteligente

## Partes concluídas

1. **Núcleo centralizado**: `ContentManager`, contexto escolar e fachada compatível com o Painel TV.
2. **Biblioteca de modelos**: mensagens organizadas por categoria e estilo, com placeholders preenchidos por dados reais.
3. **Gerador por regras**: `RuleBasedProvider`, offline, determinístico e preparado para novos provedores.
4. **Configurações**: automático/manual por categoria, estilo, frequência de renovação e modo de aprovação.
5. **Histórico**: migration e tabela `institutional_generated_contents` com data, tipo, texto, snapshot e provedor.
6. **Aprovação**: tela de sugestões com Aprovar, Editar/Publicar e Descartar.
7. **Integração**: Painel TV consome o serviço central para mensagem, dica, apoio, motivação, slogan, rodapé, destaque, Sabia que, Hall da Fama e calendário.
8. **Cache diário**: um texto por categoria/data é reutilizado em todas as consultas.
9. **Extensibilidade**: contrato `ContentProviderInterface` para futuros provedores locais ou externos.
10. **Documentação**: arquitetura, fluxo e operação registrados neste documento.

## Fluxo

1. O Painel TV solicita o conteúdo do dia.
2. O contexto reúne frequência, comparação com o último dia de chamada, ranking, evolução e calendário.
3. O `ContentManager` verifica configuração manual/automática.
4. Se existir conteúdo publicado em cache, ele é reutilizado.
5. Sem cache, o provedor por regras escolhe um modelo e substitui placeholders.
6. Em publicação automática, o texto é salvo como `published` e exibido.
7. Em revisão, o texto é salvo como `pending`; o texto manual permanece na TV até a aprovação.

## Instalação

Execute as migrations do projeto para criar `institutional_generated_contents`.

## Rotas administrativas

- `GET /configuracoes/conteudo-inteligente`
- `POST /configuracoes/conteudo-inteligente/aprovar`
- `POST /configuracoes/conteudo-inteligente/editar`
- `POST /configuracoes/conteudo-inteligente/descartar`
- `POST /configuracoes/conteudo-inteligente/limpar-cache`

## Privacidade

A geração atual é local e não envia dados para serviços externos. O snapshot armazenado contém apenas indicadores agregados usados para explicar a origem do texto.
