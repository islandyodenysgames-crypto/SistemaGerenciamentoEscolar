# Grupo 6 — Homologação final

## Parecer

**Classificação: APTO PARA HOMOLOGAÇÃO NA VM.**

O código, o schema completo e as principais barreiras de segurança foram exercitados com PHP 8.3.30 e MySQL 8.4.3 descartável em Windows x86_64. Foram corrigidos três bugs reproduzidos. A classificação não é “apto para produção” porque Nginx, PHP-FPM, HTTPS público, Ubuntu ARM64, dispositivo físico do Painel TV e recuperação de desastre fora da máquina não foram executados neste ambiente.

Versão-base: Grupo 5, commit `d165ec2`. Branch: `agent/grupo-6-homologacao-final`.

## Ambiente realmente utilizado

- Windows, x86_64;
- PHP CLI e servidor de desenvolvimento 8.3.30;
- MySQL Community Server 8.4.3 em porta 33079, datadir temporário e credenciais sintéticas;
- navegador HTTP simulado por `Invoke-WebRequest`;
- Git Bash para validação sintática dos scripts;
- Composer com dependências do `composer.lock`.

Não disponíveis: Docker, WSL, Nginx, PHP-FPM, systemd, logrotate, `age`, `rclone`, HTTPS público e ARM64.

## Bugs comprovados e corrigidos

1. **REPROVADO, CORRIGIDO — banco vazio:** `202607020003_create_students_table.php` criava `schools`, impedindo a sequência completa e deixando `students` ausente. A migration agora cria `students` com os campos usados pelo repositório. O nome da migration foi preservado para não afetar instalações que já a registraram.
2. **REPROVADO, CORRIGIDO — automação CLI:** exceções tratadas pelo `ErrorHandler` terminavam com código zero. Migrations incompletas poderiam parecer aprovadas em scripts. O fluxo CLI agora encerra com código 1.
3. **REPROVADO, CORRIGIDO — injeção de PDO:** `SchoolDataMaintenanceService` aceita `?PDO`, mas o container tentava construir `PDO` e gerava erro 500 em Backup/Dados; outro serviço do ciclo acadêmico apresentava o mesmo problema. `PDO::class` passou a apontar para a conexão compartilhada.

## Evidências executadas

| Requisito | Estado | Evidência |
|---|---|---|
| 51 migrations em banco vazio | APROVADO | 43 tabelas, 38 FKs e 154 índices em MySQL 8.4.3 |
| Segunda execução das migrations | APROVADO | nenhuma migration reaplicada; código 0 |
| Prefixos repetidos | APROVADO COM RESSALVA | runner identifica nome completo; nenhuma renomeada |
| Backup/restauração MySQL | APROVADO | `backup_recovery_mysql.php --run`: 10.000 registros, tabela vazia e upload de 5 MiB |
| ZIP inválido, hash, manifesto, traversal e bomb | APROVADO | testes automatizados existentes |
| Login, CSRF, dashboard e logout | APROVADO | HTTP real local; token 64 chars; POST sem token rejeitado; sessão invalidada no logout |
| Páginas principais como ADMIN | APROVADO | dashboard, usuários, alunos, matrículas, turmas, frequência, ocorrências, acompanhamentos, avisos, calendário, relatórios, inteligência, busca e configurações responderam 200 |
| Perfis e autorização | APROVADO PARCIAL | professor acessou alunos/relatórios/ocorrências/acompanhamentos e foi redirecionado em usuários/frequência/inteligência/configurações |
| Download privado inexistente | APROVADO | resposta 404, sem revelar existência |
| Painel TV | APROVADO EM NAVEGADOR SIMULADO | sem pareamento 403; token errado 403; correto 303; JSON 200 com `no-store`, sem ocorrências/inteligência |
| Dependências Composer | APROVADO | `composer audit --locked`: nenhum advisory conhecido |
| Concorrência leve | APROVADO COM RESSALVA | 40 GETs de login, concorrência 10: 40/40; média 229,2 ms, p95 346,2 ms, máximo 424,3 ms |
| Scripts de produção | APROVADO PARCIAL | `bash -n` aprovado; backup falha fechado sem `BACKUP_REMOTE` |
| Nginx/PHP-FPM/HTTPS | NÃO EXECUTADO | binários/serviços ausentes |
| Ubuntu ARM64 | NÃO EXECUTADO | host Windows x86_64 |
| Recuperação externa real | NÃO EXECUTADO | `age`, `rclone` e destino externo não configurados |
| Painel TV físico | NÃO EXECUTADO | nenhum dispositivo físico disponível |

As métricas de desempenho são somente uma verificação local de regressão, usando o servidor PHP monoprocesso; não representam capacidade de produção com Nginx/PHP-FPM nem dimensionam usuários simultâneos reais. Durante a amostra, PHP usou aproximadamente 25 MiB e a instância MySQL descartável aproximadamente 512 MiB de working set.

## Jornadas e limitações

As páginas centrais e os controles de sessão foram exercitados por HTTP. Os testes automatizados cobrem autenticação, revalidação de usuário inativo/perfil alterado, CSRF HTML/AJAX, rate limit, uploads falsos, permissões, URLs privadas, favoritos/pesquisa e integridade dos backups. Não foi executada uma operação CRUD completa via navegador para cada formulário nem upload multipart de cada módulo; isso permanece para a homologação assistida na VM com dados sintéticos.

Não houve teste destrutivo em dados reais. O banco e os arquivos temporários foram isolados.

## Decisões humanas obrigatórias

Classificação: **EXIGE VALIDAÇÃO HUMANA**.

- Professores consultam alunos e relatórios globalmente e possuem CSV.
- Não existe relacionamento professor–turma para restringir escopo.
- Direção e Coordenação equivalem ao administrador.
- A leitura de anexos de ocorrência acompanha `occurrences.view`.

Essas regras foram preservadas. Elas ampliam o universo de dados pessoais acessível e devem ser aprovadas formalmente pela escola antes da produção.

## Segurança final

- CSRF central e explícito: APROVADO nos testes disponíveis.
- SQL preparado nas áreas auditadas e schema real: APROVADO, sem garantia absoluta de ausência de falhas.
- Uploads/downloads e path traversal: APROVADO nos testes automatizados.
- Segredos versionados: APROVADO na varredura; exemplos contêm apenas marcadores.
- Dependências: APROVADO na data desta homologação; repetir antes de cada release.
- Configuração Nginx: NÃO EXECUTADA; somente auditoria estática aprovada.
- Permissões Linux e cookies HTTPS: NÃO EXECUTADOS em Linux/HTTPS real.

## Implantação e rollback

Siga `docs/GRUPO_5_ORACLE_CLOUD_ALWAYS_FREE.md`. Antes da troca de release: backup externo verificado, migrations no banco descartável, `composer quality`, `composer audit`, `nginx -t`, smoke tests por perfil e confirmação humana das regras acima. A troca do symlink deve ser atômica.

Rollback de código reponta `current` para o release anterior. Rollback de banco não é automático: interrompa escrita, preserve evidências e restaure apenas um backup cuja integridade e compatibilidade tenham sido testadas.

## Checklist restante na VM

- [ ] Ubuntu 24.04 ARM64 e extensões PHP confirmados.
- [ ] `nginx -t`, PHP-FPM e roteamento MVC aprovados.
- [ ] HTTPS, renovação, cookie `Secure`, CSP Report-Only e conteúdo misto validados.
- [ ] Jornada CRUD completa para cada módulo, inclusive upload multipart.
- [ ] Matriz de acesso E2E com todos os cinco perfis e IDs cruzados.
- [ ] Painel TV em dispositivo físico, expiração/revogação e atualização longa.
- [ ] Carga representativa com Nginx/PHP-FPM e dados sintéticos equivalentes.
- [ ] Backup cifrado enviado a destino externo e recuperação em VM nova.
- [ ] systemd, logrotate, health check e alerta operacional observados por pelo menos um ciclo.
- [ ] Regras globais de professores e perfis privilegiados aprovadas pela escola.

Somente após concluir estes itens críticos a classificação poderá ser revista para **APTO PARA PRODUÇÃO**.
