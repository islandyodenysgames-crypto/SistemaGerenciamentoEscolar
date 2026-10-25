# Grupo 3 — Backup, restauração e recuperação de desastres

## Formato do backup

O formato `sge-school-backup`, versão 2, é um ZIP privado criado em `storage/backups`. Ele contém:

- `manifest.json`, com versão, esquema, operação, autoria, tabelas e integridade;
- um NDJSON por tabela em `database/`, processado registro a registro;
- uma cópia preparada dos arquivos em `uploads/`;
- SHA-256 e tamanho de cada entrada.

O ZIP é produzido com extensão `.partial`, verificado integralmente e somente então renomeado para o nome definitivo. Backups da versão 1 são deliberadamente rejeitados porque não oferecem manifesto criptográfico nem restauração preparada.

Backup, restauração e limpeza compartilham um bloqueio exclusivo em `storage/backups/maintenance.lock`, impedindo duas operações de manutenção concorrentes no mesmo nó da aplicação.

## Consistência

O banco é lido dentro de uma transação `REPEATABLE READ`, formando um snapshot consistente entre tabelas InnoDB. Os uploads são copiados imediatamente depois do snapshot do banco. Banco e sistema de arquivos não formam uma transação única; portanto o manifesto declara explicitamente `atomic_database_and_files: false`.

Para reduzir diferenças, realize backups em horário de baixa atividade e evite uploads durante a geração. Uma futura fila de manutenção pode suspender gravações durante toda a captura, mas isso não é pressuposto por esta versão.

## Restauração

1. O ZIP completo é validado antes de alterar dados.
2. São verificados formato, versão, esquema, quantidade, tamanho, taxa de compressão, caminhos, links e SHA-256.
3. É criado automaticamente um backup de segurança do estado atual.
4. Banco e uploads do arquivo recebido são preparados em área temporária.
5. O banco é importado em transação, com leitura incremental dos NDJSON.
6. Os uploads atuais são preservados temporariamente e os restaurados são ativados por renomeação.
7. Se ocorrer falha depois do commit do banco, o sistema tenta restaurar automaticamente o backup de segurança.

A restauração não é declarada atomicamente distribuída. Se a recuperação automática também falhar, o log informa o identificador da operação e o backup `pre-restore-*` deve ser restaurado manualmente.

## Limpeza

A limpeza mantém as escolhas funcionais existentes para turmas, alunos, matrículas e fotos. Ela exige autenticação administrativa, permissão `settings.manage`, CSRF e frase explícita. Antes de apagar qualquer dado, cria um backup `pre-cleanup-*`. Falhas posteriores ao commit provocam tentativa de retorno por esse backup.

## Auditoria

As operações são registradas em `storage/logs/data-maintenance.ndjson`, incluindo data, ação, identificador, usuário, IP, resultado, arquivo de segurança e falha. Esse arquivo não deve ser exposto pelo servidor web.

## Política externa recomendada (Oracle Cloud Always Free)

- diário: backup automático criptografado, retenção de 14 dias;
- semanal: retenção de 8 semanas;
- mensal: retenção de 12 meses;
- manter ao menos uma cópia fora da VM e em região/dispositivo diferente;
- verificar SHA-256 depois de cada transferência e testar restauração trimestralmente;
- nunca armazenar a chave de criptografia no mesmo diretório ou repositório do backup.

Solução gratuita sugerida para Ubuntu ARM64: `restic` com repositório SFTP em outra máquina controlada pela escola, ou `rclone crypt` para um provedor já contratado/gratuito. Nenhuma credencial deve ser commitada. Use variáveis/arquivos com permissão `0600` fora da aplicação.

Exemplo conceitual com restic:

```bash
export RESTIC_REPOSITORY='sftp:backup@servidor:/backups/escola'
export RESTIC_PASSWORD_FILE='/etc/sge-backup/restic-password'
restic backup /var/www/sge/storage/backups
restic forget --keep-daily 14 --keep-weekly 8 --keep-monthly 12 --prune
restic check
```

Agende por `systemd timer`, registre a saída e alerte a gestão em caso de falha. O arquivo de senha deve ser incluído no plano institucional de custódia de chaves, com cópia offline em local seguro.

## Perda total da VM

1. Provisionar nova VM Ubuntu ARM64 e instalar PHP/MySQL/extensões compatíveis.
2. Recuperar código da versão/tag correspondente ao backup.
3. Restaurar `.env` e chaves a partir da custódia segura, nunca do Git.
4. Criar banco vazio com usuário restrito e executar somente as migrations correspondentes à versão.
5. Recuperar o ZIP externo e validar sua integridade.
6. Usar a tela administrativa para restaurar, ou procedimento assistido em ambiente de manutenção.
7. Validar contagens, autenticação, fotos, anexos, frequências e amostras de casos.
8. Só então reabrir o sistema aos usuários.

## Dependências e limites

- PHP 8.3 com `pdo_mysql`, `fileinfo` e `zip`;
- tabelas MySQL/InnoDB para a garantia transacional descrita;
- espaço livre suficiente para ZIP, staging, backup anterior e cópia temporária dos uploads;
- permissões de escrita restritas em `storage/backups`, `storage/logs` e `public/uploads`;
- o sistema não configura nem transfere backups para serviços externos nesta etapa;
- testes de falhas reais de disco, desligamento abrupto e banco MySQL volumoso precisam ser executados em homologação operacional.

## Teste descartável de integração

Com um MySQL local de homologação em execução e as credenciais do `.env`, execute:

```bash
php tests/backup_recovery_mysql.php --run
```

O teste cria exclusivamente um banco com prefixo `sge_disposable_`, inclui tabela vazia, 10.000 registros e upload aleatório de 5 MiB, executa backup/restauração e remove o banco no bloco `finally`. Nunca altere o prefixo de segurança nem aponte esse teste para uma conta sem permissão de criar e remover apenas bancos descartáveis.
