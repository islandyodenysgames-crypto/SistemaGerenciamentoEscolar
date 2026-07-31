# Sprint Dados 1.0 — Exportação, restauração e limpeza segura

## Entregue
- Nova área em **Configurações → Backup**.
- Exportação ZIP completa do banco e de `public/uploads`.
- Restauração validada por manifesto próprio.
- Limpeza seletiva com preservação opcional de turmas, alunos/matrículas e respectivas fotos.
- Usuários, identidade da escola e configurações estruturais são sempre preservados na limpeza.
- Confirmações digitadas para ações destrutivas.
- Controle de acesso pela permissão `settings.manage`.

## Segurança
Antes de limpar ou restaurar, gere e guarde um backup. A restauração substitui os dados atuais pelos presentes no ZIP.

## Requisito local
A extensão PHP `zip` deve estar habilitada no Laragon (`extension=zip`).
