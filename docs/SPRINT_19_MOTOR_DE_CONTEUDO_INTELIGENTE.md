# Sprint 19 — Motor de Conteúdo Inteligente

## Entregas

- Motor local, diário e determinístico de conteúdo institucional.
- Mensagens automáticas baseadas em frequência, comparação com o dia anterior e ranking.
- Novo slide **Sabia que...** com fatos reais da escola.
- Novo slide **Estrela da Semana** com estudante de frequência exemplar e fotografia.
- Painel motivacional e demais textos com opção automática ou manual.
- Configuração de estilo: institucional, formal, inspirador ou jovem.
- Fundo institucional com marca gráfica abstrata e identidade do tema.
- Animações internas: flutuação, barras, contadores, entrada de cards e zoom suave de fotografia.
- Endpoint JSON do Painel TV ampliado com `intelligentContent`.

## Regras

O gerador não utiliza serviços externos. Os textos são selecionados e compostos diariamente a partir dos dados reais do banco. Isso mantém funcionamento offline, previsibilidade e privacidade.

A opção manual permanece como fallback. Ao desativar um checkbox automático, o texto digitado na configuração volta a ser usado imediatamente.

## Banco de dados

Não há nova migração. As opções são armazenadas no JSON existente `tv_panel.configuration`.
