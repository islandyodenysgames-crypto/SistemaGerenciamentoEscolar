<?php
$inputName = (string) ($inputName ?? 'attachments[]');
$inputId = (string) ($inputId ?? ('stagedFiles' . substr(md5(uniqid('', true)), 0, 8)));
$accept = (string) ($accept ?? '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp,.gif,.mp4,.webm,.mp3,.wav,.ogg,.zip,.txt');
$multiple = (bool) ($multiple ?? true);
$maxFiles = max(1, (int) ($maxFiles ?? 10));
$maxSizeMb = max(1, (int) ($maxSizeMb ?? 25));
$help = (string) ($help ?? "Até {$maxFiles} arquivos, com no máximo {$maxSizeMb} MB por arquivo.");
$selectLabel = (string) ($selectLabel ?? 'Selecionar arquivo');
$sendLabel = (string) ($sendLabel ?? 'Enviar arquivo');
?>
<div class="staged-file-upload" data-staged-file-upload data-max-files="<?= $maxFiles ?>" data-max-size="<?= $maxSizeMb * 1024 * 1024 ?>">
    <input class="staged-file-upload__committed" type="file" id="<?= e($inputId) ?>" name="<?= e($inputName) ?>" <?= $multiple ? 'multiple' : '' ?> accept="<?= e($accept) ?>" hidden>
    <input class="staged-file-upload__picker" type="file" <?= $multiple ? 'multiple' : '' ?> accept="<?= e($accept) ?>" hidden>

    <div class="staged-file-upload__actions">
        <button type="button" class="btn-secondary staged-file-upload__select" data-file-select>
            <i data-lucide="folder-open"></i> <?= e($selectLabel) ?>
        </button>
        <button type="button" class="btn-primary staged-file-upload__send" data-file-send disabled>
            <i data-lucide="upload"></i> <?= e($sendLabel) ?>
        </button>
    </div>

    <div class="staged-file-upload__status" data-file-status aria-live="polite">
        <i data-lucide="circle-dashed"></i>
        <span>Nenhum arquivo selecionado.</span>
    </div>

    <div class="staged-file-upload__queue" data-file-queue hidden></div>
    <small class="staged-file-upload__help"><?= e($help) ?> Os arquivos só serão gravados após salvar o formulário.</small>
</div>
