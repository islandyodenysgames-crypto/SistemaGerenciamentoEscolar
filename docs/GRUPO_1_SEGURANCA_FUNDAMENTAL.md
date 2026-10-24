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

O conjunto local cobre token válido/inválido/AJAX, injeção em formulários, expiração de sessão, limitação de login, autenticação válida/inválida, desativação e mudança de papel, logout POST, ausência da senha legada e `APP_URL` com HTTPS/subdiretório. Também são executadas auditorias de sintaxe, navegação e dependências.

## Limite desta entrega

Esta etapa reduz os bloqueadores fundamentais, mas não declara o sistema pronto para produção. Uploads, backups/restauração, privacidade do Painel TV, headers HTTP, observabilidade ampliada, infraestrutura e testes end-to-end pertencem aos grupos posteriores.
