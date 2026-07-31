# Sprint 20.11 - Homologacao e estabilidade operacional

## Objetivo

Transformar as garantias do ciclo academico em verificacoes repetiveis antes de cada publicacao.

## Entregas

- protecao CSRF nas operacoes de exportacao, restauracao e limpeza;
- testes automatizados de CSRF, permissoes e contratos do ciclo academico;
- auditorias de seguranca, navegacao, ciclo academico e prontidao operacional;
- comando unico `composer quality`;
- verificacao automatica no GitHub Actions;
- checklist de implantacao, homologacao e recuperacao.

## Criterio de conclusao

A Sprint esta aprovada quando `composer quality` termina sem erros e a verificacao do GitHub fica verde. A auditoria com banco continua opcional e deve ser executada no Laragon com MySQL ativo: `php scripts/academic_cycle_audit.php --database`.
