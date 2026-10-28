# Grupo 5 — Preparação para Oracle Cloud Always Free

## Escopo e parecer

Esta entrega prepara arquivos e procedimentos para uma implantação posterior em uma única VM Ubuntu Server 24.04 LTS ARM64, com Nginx, PHP 8.3-FPM e MySQL local. Ela **não executa deploy**, não cria recursos OCI, não configura DNS e não movimenta dados escolares reais.

O selo Always Free e os limites precisam ser confirmados na região residencial antes da criação. A disponibilidade de Ampere A1 não é garantida e a Oracle pode informar falta temporária de capacidade. A documentação oficial vigente deve ser consultada em <https://docs.oracle.com/en-us/iaas/Content/FreeTier/freetier_topic-Always_Free_Resources.htm>.

Arquitetura proposta:

```text
Internet -> OCI NSG -> UFW -> Nginx :443 -> PHP 8.3-FPM (socket Unix)
                                             -> MySQL :3306 (127.0.0.1)
                                             -> storage/ e public/uploads/
Backup local -> age (criptografia) -> rclone -> armazenamento externo
```

## Arquivos da entrega

- `.env.production.example`: variáveis suportadas, sem segredo real.
- `deploy/oracle-cloud/nginx/sge.conf.example`: virtual host HTTPS e proteção de arquivos.
- `deploy/oracle-cloud/php/99-sge.ini`: limites, sessões e OPcache.
- `deploy/oracle-cloud/mysql/*.example`: serviço local e usuários separados.
- `deploy/oracle-cloud/scripts/`: saúde e backup externo.
- `deploy/oracle-cloud/systemd/`: serviços e timers opcionais.
- `deploy/oracle-cloud/logrotate/sge`: rotação local.
- `scripts/production_readiness_audit.php`: validação estática da preparação.

## 1. Preparação da VM

Crie uma VM ARM64 somente quando houver capacidade, com Ubuntu 24.04 LTS. Reserve IP público e anexe um NSG. Não use a conta `root` para a aplicação.

```bash
sudo apt update
sudo apt full-upgrade
sudo apt install nginx mysql-server git unzip curl ca-certificates composer \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-zip \
  php8.3-gd php8.3-intl php8.3-curl age rclone certbot python3-certbot-nginx
sudo adduser --disabled-password --gecos '' sge-deploy
sudo usermod -aG www-data sge-deploy
```

Confirme arquitetura e módulos:

```bash
uname -m
php -v
php -m | grep -E 'curl|dom|fileinfo|gd|intl|mbstring|openssl|pdo_mysql|xml|zip'
composer check-platform-reqs
```

`aarch64` confirma ARM64. Se um pacote PHP não estiver disponível no repositório oficial utilizado, pare e corrija a origem; não misture PPAs não auditados apenas para concluir a instalação.

## 2. Código e releases

```bash
sudo install -d -o sge-deploy -g www-data -m 0750 /var/www/sge/releases
sudo -u sge-deploy git clone https://github.com/islandyodenysgames-crypto/SistemaGerenciamentoEscolar.git /var/www/sge/releases/PRIMEIRO_RELEASE
cd /var/www/sge/releases/PRIMEIRO_RELEASE
sudo -u sge-deploy composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
sudo ln -sfn /var/www/sge/releases/PRIMEIRO_RELEASE /var/www/sge/current
```

Em repositório privado, use chave deploy somente leitura. Nunca grave token pessoal em comando, histórico ou URL.

## 3. Ambiente e permissões

```bash
sudo -u sge-deploy cp .env.production.example .env
sudo chown sge-deploy:www-data .env
sudo chmod 0640 .env
sudo install -d -o www-data -g www-data -m 0770 storage/logs storage/backups storage/backups/.tmp public/uploads
sudo find public/uploads -type d -exec chmod 0770 {} \;
sudo find public/uploads -type f -exec chmod 0660 {} \;
```

Edite `.env` sem deixar `SUBSTITUA_*`. Gere segredos com `openssl rand -hex 32`. O código pode ser `0750/0640`; somente `storage/` e `public/uploads/` devem ser graváveis por `www-data`. Não use `chmod 777`.

O Nginx publica exclusivamente `public/`. Uploads privados (`students`, `users`, ocorrências, ações, acompanhamentos e anexos de avisos) são negados diretamente e continuam acessíveis apenas pela rota autenticada `/arquivos/visualizar`.

## 4. MySQL

Copie `sge.cnf.example` para `/etc/mysql/mysql.conf.d/sge.cnf`, ajuste `innodb_buffer_pool_size` conforme a RAM real e reinicie. A porta 3306 permanece em `127.0.0.1` e nunca deve ser liberada no NSG/UFW.

Copie `create-users.sql.example` para fora do repositório, substitua as senhas e execute localmente. Use `sge_migrator` somente durante migrations; a aplicação normal usa `sge_app` com DML mínimo.

```bash
sudo mysql < /root/create-sge-users.sql
sudo -u sge-deploy env DB_USERNAME=sge_migrator DB_PASSWORD='SEGREDO_TEMPORARIO' php console.php migrate
```

Evite senha na linha de comando em ambientes com outros usuários. Prefira arquivo temporário MySQL `0600`, `mysql_config_editor` ou entrada interativa, e remova a credencial de migration ao terminar.

## 5. PHP-FPM e Nginx

```bash
sudo cp deploy/oracle-cloud/php/99-sge.ini /etc/php/8.3/fpm/conf.d/99-sge.ini
sudo install -o www-data -g adm -m 0750 -d /var/log/php
sudo touch /var/log/php/sge-error.log
sudo chown www-data:adm /var/log/php/sge-error.log
sudo cp deploy/oracle-cloud/nginx/sge.conf.example /etc/nginx/sites-available/sge
sudo ln -s /etc/nginx/sites-available/sge /etc/nginx/sites-enabled/sge
sudo nginx -t
sudo systemctl restart php8.3-fpm
sudo systemctl reload nginx
```

Antes do primeiro certificado, habilite temporariamente apenas o bloco HTTP/ACME; depois que o Certbot criar os arquivos, habilite o bloco HTTPS. Os limites de 500/512 MB existem por causa da restauração administrativa. Monitore disco e tempo; reduza-os se backups maiores forem restaurados apenas por CLI.

A CSP é `Report-Only` porque existem scripts e estilos inline. Colete violações e só então aplique uma CSP bloqueante. HSTS fica comentado até HTTPS e subdomínios serem validados.

## 6. DNS, HTTPS e rede

No DNS, crie `A` apontando ao IPv4 reservado; se houver IPv6 configurado e testado, crie `AAAA`. Aguarde propagação e valide com `dig`.

No NSG da OCI permita entrada stateful:

- TCP 80 e 443 de `0.0.0.0/0` (e IPv6 apenas se usado);
- TCP 22 somente do CIDR administrativo conhecido;
- nenhuma entrada para 3306, 9000 ou socket PHP-FPM.

No Ubuntu replique a defesa:

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow from CIDR_ADMINISTRATIVO to any port 22 proto tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
sudo ufw status verbose
```

Oracle recomenda NSGs para controle granular: <https://docs.oracle.com/en-us/iaas/Content/Network/Concepts/networksecuritygroups.htm>.

Certificado:

```bash
sudo certbot --nginx -d escola.exemplo.br
sudo certbot renew --dry-run
systemctl list-timers | grep certbot
```

Valide cadeia, redirecionamento, cookie `Secure`, Painel TV pareado e ausência de conteúdo misto em navegador real. Não habilite HSTS antes disso.

## 7. Backups externos e recuperação

`php console.php backup:create` reutiliza o mecanismo validado do Grupo 3 e imprime o caminho do ZIP. `backup-external.sh` o cifra com `age`, gera SHA-256 e usa `rclone` para um remoto configurado. O destino deve ser outro domínio de falha/conta, com versionamento e retenção quando disponíveis. OCI Object Storage cifra dados em repouso por padrão, mas a criptografia cliente continua protegendo o conteúdo do operador de armazenamento: <https://docs.oracle.com/en-us/iaas/Content/Security/Reference/objectstorage_security.htm>.

Crie `/etc/sge/backup.env` (`0600`) com `BACKUP_REMOTE`, `APP_ROOT` e retenção. Guarde a **chave privada age fora da VM**; apenas a chave pública fica em `/etc/sge/backup-age-recipient.txt`. Teste primeiro com dados sintéticos.

```bash
sudo cp deploy/oracle-cloud/systemd/sge-backup.* /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now sge-backup.timer
sudo systemctl start sge-backup.service
sudo journalctl -u sge-backup.service
```

Recuperação de desastre: criar VM limpa, instalar mesma versão, restaurar `.env` por canal seguro, baixar `.age` e `.sha256`, validar `sha256sum -c`, decifrar, testar o ZIP com `SchoolDataMaintenanceService::inspectArchive`, restaurar em MySQL descartável e somente então planejar restauração real. Backup somente na VM não é DR.

## 8. Logs, monitoramento e manutenção

Instale o arquivo logrotate e os timers após substituir domínio/caminhos:

```bash
sudo cp deploy/oracle-cloud/logrotate/sge /etc/logrotate.d/sge
sudo logrotate -d /etc/logrotate.d/sge
sudo cp deploy/oracle-cloud/systemd/sge-health.* /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now sge-health.timer
```

Revise `journalctl`, `df -h`, `free -h`, `systemctl --failed`, logs Nginx/PHP/app, conexões MySQL, backups e expiração TLS. O serviço de saúde falha de forma observável no journal; configure alerta externo leve apenas depois de escolher um destino autorizado.

Atualizações mensais:

```bash
sudo apt update
apt list --upgradable
sudo apt upgrade
sudo nginx -t
sudo systemctl --failed
```

## 9. Atualização e rollback

Antes: confirme backup externo recente, espaço livre e commit/tag aprovado. Crie novo diretório de release, instale Composer, copie `.env` por vínculo seguro, aplique permissões e execute testes. Migrations devem ser compatíveis com a versão anterior ou ter plano explícito de banco.

Troca atômica:

```bash
sudo -u sge-deploy ln -sfn /var/www/sge/releases/NOVO /var/www/sge/current.next
sudo mv -Tf /var/www/sge/current.next /var/www/sge/current
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx
```

Rollback de código: repontar `current` para o release anterior. **Não execute rollback de banco automaticamente.** Se migration incompatível alterou dados, entre em manutenção e siga o plano da migration/backup testado. Comandos de restauração e limpeza são destrutivos e exigem janela, autorização e backup verificado.

## 10. Validação

No desenvolvimento:

```bash
composer quality
bash -n deploy/oracle-cloud/scripts/*.sh
```

Na VM descartável:

```bash
sudo nginx -t
php --ini
php -i | grep opcache
mysql -h 127.0.0.1 -u sge_app -p sge_production
curl -I http://escola.exemplo.br
curl -I https://escola.exemplo.br/login
curl -I https://escola.exemplo.br/.env
curl -I https://escola.exemplo.br/uploads/students/1/test.jpg
```

O acesso a `.env` e upload privado deve retornar 404/403; HTTP deve redirecionar; HTTPS deve responder sem divulgar PHP. Faça smoke tests por perfil escolar.

## 11. Checklist e pendências obrigatórias

- [ ] Capacidade/limites Always Free confirmados na região.
- [ ] Ubuntu ARM64, Nginx e PHP-FPM testados na VM real.
- [ ] `nginx -t`, PHP modules e MySQL local aprovados.
- [ ] DNS, HTTPS, renovação e conteúdo misto validados.
- [ ] Backup/restauração completo testado em MySQL descartável.
- [ ] Recuperação após perda de VM ensaiada.
- [ ] Testes E2E de autorização/IDOR e perfis escolares concluídos.
- [ ] Permissões globais de professores decididas pela escola.
- [ ] Painel TV testado em dispositivo real.
- [ ] Backups externos cifrados, versionados e monitorados.
- [ ] Monitoramento de disco, memória, serviços e certificado ativo.

Enquanto qualquer item obrigatório estiver pendente, o sistema está **preparado para implantação controlada**, não certificado como pronto para produção.
