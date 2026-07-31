# Sprint 3.1 — Parte 3

## Correção do período da evolução da frequência

- O selo do painel agora acompanha imediatamente a opção selecionada.
- A resposta assíncrona devolve o período e seu rótulo textual.
- O painel substituído é sincronizado antes de entrar no DOM.
- A requisição usa `cache: no-store` e parâmetro anticache.
- O helper `asset()` passa a versionar CSS e JavaScript com `filemtime`, evitando execução de arquivos antigos mantidos pelo navegador.

## Inteligência de reincidência no perfil

- Novo resumo por aluno para os últimos 60 dias.
- Comparação com os 60 dias anteriores.
- Tendência por tipo: aumento, redução ou estabilidade.
- Nível de risco: atenção, alto ou crítico.
- Datas da primeira e última ocorrência e dias desde o último registro.
