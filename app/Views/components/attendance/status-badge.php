<?php

$done = (bool) ($done ?? false);

?>

<?php if ($done): ?>

    <span class="badge badge-success">
        Frequência realizada
    </span>

<?php else: ?>

    <span class="badge badge-danger">
        Frequência pendente
    </span>

<?php endif; ?>