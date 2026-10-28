# Grupo 1 — Segurança fundamental e preparação para produção

## Escopo entregue

- Sessões com cookies `HttpOnly`, `SameSite`, `Secure` sob HTTPS, modo estrito, expiração por inatividade e regeneração periódica.
- Cabeçalhos de proxy considerados somente para endereços listados em `TRUSTED_PROXIES`.
- Revalidação periódica do usuário autenticado; desativação remove o acesso e mudanças de papel atualizam a sessão.
- Limitação de tentativas de login por identidade e origem, com bloqueio temporário e armazenamento persistente protegido por lock.
- CSRF central em todos os POST, formulários renderizados e chamadas AJAX de mesma origem. Login e logout estão protegidos; logout usa POST.
- Seed administrativo sem credencial fixa. O primeiro administrador exige variáveis de ambiente e senha mínima de 12 caracteres.
- Tratamento central de exceções, identificador de erro e log interno. Produção não mostra trace, caminho local nem SQL.
- URLs baseadas exclusivamente em `APP_URL`, incluindo HTTPS, raiz ou subdiretório.

## Configuração necessária

Copie as novas chaves de `.env.example` para o `.env`. Em produção use `APP_ENV=production`, `APP_DEBUG=false` e uma `APP_URL` HTTPS. Configure `TRUSTED_PROXIES` somente quando houver proxy reverso conhecido.

Para criar o primeiro administrador, defina temporariamente `INITIAL_ADMIN_NAME`, `INITIAL_ADMIN_EMAIL` e `INITIAL_ADMIN_PASSWORD`, execute o seed e remova a senha do ambiente após a criação. Usuários existentes não são alterados.

## Comportamento operacional

- Sessão inativa: o usuário volta ao login com mensagem neutra.
- Papel ou status alterado: a sessão é atualizada ou invalidada no intervalo configurado.
- CSRF expirado: formulários retornam à página com aviso; AJAX recebe HTTP 419 e JSON com `CSRF_TOKEN_MISMATCH`.
- Exceção em produção: o usuário recebe apenas uma mensagem genérica e um identificador consultável em `storage/logs`.
- Limite de login: recupera-se automaticamente após `LOGIN_LOCKOUT` segundos.

## Validação executada

O conjunto local cobre token válido/inválido/AJAX, variações de HTML, formulários dinâmicos e aninhados, expiração de sessão, limitação de login, arquivo corrompido ou indisponível, limpeza de registros vencidos, autenticação válida/inválida, desativação e mudança de papel, logout POST, múltiplas abas, proxies confiáveis, ausência da senha legada e `APP_URL` com HTTPS/subdiretório. A revisão terminou com 44 testes aprovados, 443 arquivos PHP válidos, auditorias de segurança/CSRF/navegação/ciclo acadêmico aprovadas e nenhuma vulnerabilidade conhecida no lock do Composer.

## Limite desta entrega

Esta etapa reduz os bloqueadores fundamentais, mas não declara o sistema pronto para produção. Uploads, backups/restauração, privacidade do Painel TV, headers HTTP, observabilidade ampliada, infraestrutura e testes end-to-end pertencem aos grupos posteriores.

## Revisão complementar de estabilização

A revisão do PR #5 confirmou dois ajustes necessários. A resposta CSRF de formulário misturava status 419 com redirecionamento; agora HTML recebe `303 See Other` com mensagem de sessão, enquanto `fetch` e AJAX recebem JSON 419 e instrução de recarregamento. O retorno por `Referer` também ficou restrito ao host, esquema e caminho-base de `APP_URL`.

Um token inválido não provoca mais rotação do token atual. Isso evita invalidar simultaneamente formulários legítimos abertos em outras abas; após expiração real da sessão, o redirecionamento carrega normalmente o token da nova sessão.

O injetor de formulários deixou de depender do pareamento simples de `<form>...</form>` e passou a analisar as tags em pilha. Isso cobre atributos com aspas simples, duplas ou sem aspas, múltiplas linhas, formulários legados aninhados e tokens explícitos sem duplicá-los. Formulários criados dinamicamente são cobertos no navegador, inclusive `fetch`, `XMLHttpRequest`, eventos de submit e `form.submit()`.

O limitador de login ganhou arquivo de lock separado, gravação temporária com substituição, permissões restritas, recuperação de JSON corrompido, remoção de registros vencidos e limite de 10 mil chaves. Em falha de disco/permissão ele registra o problema e preserva a disponibilidade do login; nesse cenário excepcional a limitação fica temporariamente indisponível. O limite padrão por origem foi elevado para 50 para reduzir bloqueios indevidos em redes escolares compartilhadas, mantendo o limite por identidade em 5.

A suspeita de que seria obrigatório inserir manualmente `_token` em todos os templates foi descartada para esta base: 73 formulários POST e 90 rotas POST estão cobertos pelo Router, pelo injetor central e pelo cliente JavaScript. Dezesseis formulários críticos continuam com token explícito, e a proteção central permanece como garantia contra omissões futuras. O script `scripts/csrf_audit.php` documenta e verifica essa cobertura.
