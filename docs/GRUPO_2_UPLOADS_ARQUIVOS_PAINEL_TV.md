# Grupo 2 — Segurança de uploads, arquivos e Painel TV

## Arquivos e uploads

Os uploads passam a validar erro do PHP, tamanho real, extensão, MIME obtido por `finfo` e estrutura real de imagens. Nomes persistidos são aleatórios. SVG e formatos executáveis não são aceitos. Exclusões usam resolução confinada ao diretório autorizado e rejeitam caminhos absolutos, `..`, bytes nulos e escapes por links/diretórios.

Anexos e fotografias restritos continuam compatíveis com os caminhos já gravados, mas suas URLs passam pelo endpoint autenticado `/arquivos/visualizar`. O `.htaccess` em `public/uploads` bloqueia acesso direto às áreas de estudantes, usuários, ocorrências, acompanhamentos e anexos de avisos, além de impedir listagem e execução de scripts.

SVG deixou de ser aceito para novos logotipos e é bloqueado no diretório de uploads. Uma escola que ainda utilize logotipo SVG legado deverá substituí-lo por PNG, JPEG ou WebP.

## Painel TV

Em produção, configure um segredo aleatório com pelo menos 32 caracteres:

```env
TV_PANEL_ACCESS_TOKEN=gere-um-segredo-aleatorio-longo
```

No dispositivo da televisão, abra uma única vez:

```text
https://dominio/painel-tv/autorizar?token=SEU_SEGREDO
```

O token é comparado sem aproximações e convertido em cookie `HttpOnly`, `SameSite=Strict`, `Secure` sob HTTPS, válido por 180 dias. O redirecionamento remove o token da URL. Depois disso, o painel e o endpoint JSON atualizam normalmente sem login manual. Usuários autenticados continuam podendo abrir o painel. Em ambiente local não produtivo, a ausência do token mantém a compatibilidade com o Laragon; em produção, a ausência bloqueia o painel.

O payload público contém somente totais agregados de frequência, ranking por turma, banners destinados ao Painel TV, agenda institucional, configurações visuais e reconhecimentos de turmas. Ocorrências, Central de Inteligência, dados individuais, nomes e fotografias de estudantes foram removidos. Respostas usam `no-store`, `nosniff` e `Referrer-Policy: no-referrer`.

## Apache

É necessário permitir a leitura de `.htaccess` em `public/uploads` (`AllowOverride All`). O arquivo incluído no projeto bloqueia scripts e áreas privadas. Em produção, confirme que `mod_rewrite` e `mod_authz_core` estão ativos.

## Nginx

Inclua regras equivalentes no bloco do site:

```nginx
location ~* ^/uploads/(students|users|occurrences|occurrence-actions|monitoring-actions)/ { deny all; }
location ~* ^/uploads/notices/[0-9]{4}/[0-9]{2}/[0-9]+/attachments/ { deny all; }
location ~* ^/uploads/.*\.(php[0-9]?|phtml|phar|cgi|pl|py|sh|svg)$ { deny all; }
location /uploads/ { autoindex off; try_files $uri =404; }
```

Estas regras são preparação de aplicação; não configuram nem implantam Nginx.

## Backups ZIP

A restauração valida extensão/MIME, assinatura ZIP, manifesto, quantidade de entradas, tamanho total descompactado, caminhos, áreas permitidas e links simbólicos antes de modificar o banco. O limite é de 20 mil entradas e 2 GiB descompactados.

## Limitações

Arquivos antigos permanecem fisicamente em `public/uploads` para evitar migração destrutiva. A proteção depende da aplicação das regras do servidor web. Uma futura migração não destrutiva poderá movê-los para `storage/private`; ela não foi incluída neste grupo.
