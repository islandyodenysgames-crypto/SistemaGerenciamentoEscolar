# Sprint 3.3.3 — Correção do avatar no cabeçalho

## Problema

O cabeçalho exibia apenas as iniciais porque dependia da cópia do usuário armazenada na sessão. Sessões antigas podiam não conter `photo_path` e `photo_updated_at`, mesmo quando a foto já estava cadastrada na tabela `users`.

## Correção

- O `header.php` sincroniza o usuário logado com a tabela `users` usando o ID da sessão.
- Os campos de foto são mesclados novamente à sessão.
- A imagem é exibida dentro de `.user-avatar` quando o arquivo realmente existe.
- As iniciais continuam como fallback quando não existe foto válida.
- A URL recebe versão baseada em `photo_updated_at` para evitar cache da imagem anterior.
- O CSS garante avatar circular, sem deformação, com `object-fit: cover`.
