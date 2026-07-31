# Sprint 20.10 — Estabilização do Ciclo Acadêmico

## Objetivo

Fortalecer as Sprints 20.0 a 20.9 antes da inclusão de novos módulos, garantindo que fechamento, reabertura e transição anual sejam consistentes mesmo quando uma operação falhar no meio do processo.

## Alterações

- fechamento de período, reabertura, encerramento anual e criação do próximo ano agora são transacionais;
- backup completo é criado automaticamente antes de fechar um período ou encerrar o ano;
- uma restrição no banco impede que dois anos permaneçam ativos simultaneamente;
- somente períodos fechados podem ser reabertos;
- somente o ano ativo pode ser encerrado;
- o Assistente de Novo Ano valida ano, datas, ordem cronológica e vigência;
- ações acadêmicas críticas exibem confirmação antes do envio;
- criada auditoria executável para estrutura, rotas, CSRF, transações e integridade do banco.

## Validação

Auditoria estática:

```bash
php scripts/academic_cycle_audit.php
```

Auditoria com a base local configurada no `.env`:

```bash
php scripts/academic_cycle_audit.php --database
```

Se o código auditado não contiver o `.env`, informe o arquivo da instalação local:

```bash
php scripts/academic_cycle_audit.php --database --env=C:\\laragon\\www\\SistemaFrequenciaEscolar\\.env
```

A auditoria com banco verifica:

- quantidade máxima de um ano ativo;
- períodos dentro da vigência anual;
- ausência de sobreposição de períodos;
- anos encerrados sem snapshot final.

## Roteiro manual essencial

1. Criar um ano em preparação e ativá-lo.
2. Criar períodos sem sobreposição.
3. Gerar dias letivos e cadastrar uma exceção.
4. Fechar e reabrir um período.
5. Fechar todos os períodos e encerrar o ano.
6. Usar o Assistente de Novo Ano com e sem cópia dos períodos.
7. Confirmar os registros em Estatísticas históricas e Auditoria acadêmica.
