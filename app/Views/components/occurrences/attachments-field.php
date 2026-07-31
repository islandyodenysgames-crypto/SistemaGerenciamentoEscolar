<?php
$attachments = $attachments ?? [];
$occurrenceId = (int) ($occurrenceId ?? 0);
$canRemove = (bool) ($canRemove ?? false);
?>
<div class="form-group form-group-full occurrence-attachments-field" id="occurrenceAttachmentsSection">
    <label for="occurrenceAttachments">
        <i data-lucide="paperclip"></i>
        Arquivos
    </label>

    <?php component('media/staged-file-upload', [
        'inputId' => 'occurrenceAttachments',
        'inputName' => 'attachments[]',
        'accept' => '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp,.gif,.mp4,.webm,.mp3,.wav,.ogg,.zip,.txt',
        'maxFiles' => 10,
        'maxSizeMb' => 25,
        'help' => 'PDF, documentos, imagens, vídeos, áudios e ZIP. Máximo de 25 MB por arquivo e 10 arquivos por salvamento.',
    ]); ?>

    <?php if ($canRemove && $occurrenceId > 0): ?>
        <input
            type="hidden"
            name="return_to"
            value="<?= e(base_url('ocorrencias/editar?id=' . $occurrenceId . '#occurrenceAttachmentsSection')) ?>"
        >
    <?php endif; ?>

    <?php if ($attachments !== []): ?>
        <?php component('media/attachment-carousel', ['attachments' => $attachments, 'title' => 'Imagens já anexadas']); ?>
        <div class="occurrence-existing-attachments">
            <strong>Arquivos já anexados</strong>
            <div class="occurrence-attachment-list">
                <?php foreach ($attachments as $attachment): ?>
                    <div class="occurrence-attachment-item">
                        <a href="<?= e((string) ($attachment['url'] ?? '#')) ?>" target="_blank" rel="noopener">
                            <i data-lucide="<?= e((string) ($attachment['icon'] ?? 'paperclip')) ?>"></i>
                            <span>
                                <strong><?= e((string) ($attachment['original_name'] ?? 'Arquivo')) ?></strong>
                                <small><?= e((string) ($attachment['size_label'] ?? '')) ?></small>
                            </span>
                        </a>

                        <?php if ($canRemove && $occurrenceId > 0): ?>
                            <button
                                type="submit"
                                class="occurrence-attachment-remove"
                                name="attachment_id"
                                value="<?= (int) ($attachment['id'] ?? 0) ?>"
                                formaction="<?= base_url('ocorrencias/anexos/excluir') ?>"
                                formmethod="POST"
                                formenctype="application/x-www-form-urlencoded"
                                formnovalidate
                                aria-label="Remover anexo"
                                title="Remover anexo"
                                onclick="return confirm('Remover este anexo?');"
                            >
                                <i data-lucide="trash-2"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
