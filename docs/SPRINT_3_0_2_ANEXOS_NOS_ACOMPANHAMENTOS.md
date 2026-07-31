# Sprint 3.0.2 — Anexos nos acompanhamentos

## Entrega

- Upload múltiplo ao registrar uma ação de acompanhamento.
- Inclusão de novos arquivos ao editar uma ação.
- Exibição dos anexos no histórico de ações.
- Abertura do arquivo em nova aba.
- Exclusão individual com confirmação e validação de permissão.
- Exclusão física dos anexos quando a ação é excluída.
- Carregamento agrupado dos anexos para evitar consultas por ação.

## Limites

- Até 10 arquivos por envio.
- Até 25 MB por arquivo.
- PDF, documentos, planilhas, imagens, vídeos, áudios, ZIP e TXT.

## Armazenamento

`public/uploads/monitoring-actions/ANO/MES/ID_DA_ACAO/`

## Migração

`202608020001_create_student_monitoring_action_attachments_table.php`
