# Sprint 3.0.1 — Anexos nas Ocorrências

## Entrega

Esta etapa adiciona arquivos vinculados às ocorrências individuais.

### Recursos

- Upload múltiplo no cadastro da ocorrência.
- Inclusão de novos arquivos na edição.
- Listagem dos anexos no perfil do aluno.
- Abertura dos arquivos em nova aba.
- Remoção individual do anexo por usuário autorizado.
- Remoção física dos arquivos quando a ocorrência é excluída.
- Organização em `public/uploads/occurrences/ANO/MES/OCORRENCIA`.

### Formatos

PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, WEBP, GIF, MP4, WEBM, MP3, WAV, OGG, ZIP e TXT.

### Limites

- 25 MB por arquivo.
- 10 arquivos por envio.

### Banco de dados

Executar a migração:

`202608010001_create_occurrence_attachments_table.php`

### Segurança

- Nome físico aleatório.
- Extensão validada por lista permitida.
- MIME identificado no servidor.
- Bloqueio de execução de scripts na pasta de uploads por `.htaccess`.
- Remoção restrita a quem pode gerenciar a ocorrência.

## Próxima etapa

Anexos nas ações de acompanhamento do aluno.
