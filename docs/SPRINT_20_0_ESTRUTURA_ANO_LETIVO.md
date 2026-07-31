# Sprint 20.0 — Estrutura do Ano Letivo

## Objetivo

Criar a entidade anual que servirá de base para períodos letivos, calendário,
relatórios e demais integrações temporais.

## Entregas

- tabela `school_years`;
- cadastro e edição de anos letivos;
- estados de preparação, andamento, encerrado e arquivado;
- garantia transacional de um único ano ativo;
- vigência e observações;
- progresso e dias restantes calculados;
- área administrativa protegida por permissão e CSRF;
- regeneração da sessão após autenticação.

A meta de frequência permanece centralizada em `Configurações → Metas da
Escola` (`school_goals.frequency_goal`), evitando duas fontes de verdade.

## Próxima etapa

A Sprint 20.1 poderá vincular `school_periods.school_year_id` a esta estrutura,
validar sobreposição de datas e identificar automaticamente o período vigente.

## Instalação

Execute:

```bash
php console.php migrate
```

Depois acesse:

```text
/configuracoes/ano-letivo
```
