# Checklist de implantacao e recuperacao

## Antes de atualizar

- Confirmar que Apache e MySQL estao ativos.
- Gerar e baixar um backup completo em Configuracoes > Backup e dados.
- Guardar o ZIP fora da pasta publica do sistema.
- Confirmar espaco em disco e acesso de escrita a `storage` e `public/uploads`.

## Atualizacao

1. Colocar o sistema em janela de manutencao.
2. Atualizar os arquivos da versao.
3. Executar `php console.php migrate` na raiz do projeto.
4. Executar `php scripts/project_quality_audit.php`.
5. Entrar com um usuario administrador e validar ano, periodo e dias letivos atuais.

## Homologacao minima

- Criar e editar um periodo letivo de teste.
- Gerar dias letivos sem duplicacao.
- Fechar e reabrir um periodo de teste.
- Confirmar que professor e secretaria nao acessam manutencao critica.
- Gerar um backup e conferir se o ZIP contem `manifest.json`, banco e uploads.

## Recuperacao

1. Interromper novos lancamentos.
2. Preservar uma copia do estado com problema.
3. Restaurar o ultimo backup validado pela area de Backup e dados.
4. Executar novamente as migracoes e a auditoria de qualidade.
5. Conferir totais de alunos, turmas, matriculas e frequencias antes de liberar o acesso.
