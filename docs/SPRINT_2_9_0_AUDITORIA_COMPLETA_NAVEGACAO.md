# Sprint 2.9.0 — Auditoria completa da navegação

## Objetivo

Revisar a navegação do Sistema de Frequência Escolar de forma transversal, verificando rotas, menus, links internos, ações, filtros, destinos contextuais e prevenção de regressões.

## Escopo auditado

A revisão estática percorreu os módulos e componentes de:

- painel principal e atalhos executivos;
- Central de Inteligência, casos e comparações;
- alunos, perfil, turma e relatório individual;
- acompanhamentos;
- matrículas e importação;
- frequência, chamada, visualização, edição e histórico;
- relatórios e relatório diário;
- ocorrências, providências, múltiplas ocorrências e calendário;
- notificações;
- avisos;
- usuários;
- disciplinas;
- configurações gerais, identidade e Inteligência;
- busca global;
- menu lateral, cabeçalho e componentes compartilhados.

## Resultado da auditoria automatizada

- 81 caminhos únicos registrados no roteador;
- 102 declarações GET/POST analisadas;
- 444 referências internas geradas por `base_url()` ou `url()` verificadas;
- nenhuma combinação duplicada de método e rota;
- nenhum destino interno restante sem rota registrada.

## Correções realizadas

### 1. Link órfão na linha do tempo do aluno

Eventos de providência registrados em ocorrências apontavam para:

`ocorrencias/acao?id=...`

Esse caminho não existe no roteador. O destino correto é:

`ocorrencias/providencia?id=...`

A linha do tempo agora abre diretamente a tela existente de providência da ocorrência.

### 2. Estado ativo incorreto no menu lateral

A detecção anterior utilizava comparação pelo final da URL. Isso permitia que **Central de Inteligência** e **Configurações** aparecessem ativos ao mesmo tempo em:

`configuracoes/inteligencia`

A identificação passou a utilizar o caminho relativo após `/public`, com correspondência exata ou por descendência de rota. A área de configuração da Inteligência foi explicitamente excluída do item principal da Central de Inteligência.

### 3. Verificação preventiva de regressões

Foi criado o comando:

```bash
php scripts/navigation_audit.php
```

O comando:

- coleta as rotas GET e POST do sistema;
- percorre referências internas em controllers, services, views e components;
- normaliza parâmetros e âncoras;
- ignora arquivos estáticos;
- detecta rotas internas inexistentes;
- detecta duplicidade da mesma combinação método/caminho;
- retorna código de erro quando encontra inconsistências.

## Decisões preservadas

- O item **Turmas** continua abrindo a área acadêmica baseada em `alunos`, pois essa página funciona como visão de turmas e seus estudantes no fluxo atual.
- Rotas GET e POST compartilhando o mesmo caminho foram mantidas, pois representam formulário e processamento da mesma operação.
- Não houve alteração em regras de permissão, regras de negócio, consultas ou cálculos.
- Não foram criadas páginas redundantes nem novos módulos.

## Critérios para novas telas

A partir desta Sprint, novas referências internas devem passar pelo comando de auditoria antes da entrega. Todo card ou ação deve apontar para uma rota existente, preservar o contexto necessário por parâmetros e evitar ativar simultaneamente áreas distintas do menu.
