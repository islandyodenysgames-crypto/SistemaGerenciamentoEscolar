<?php

$percentage = (float) ($percentage ?? 0);
$label = $label ?? 'Frequência';

$color = '#16a34a';

if ($percentage < 75) {
    $color = '#dc2626';
} elseif ($percentage < 85) {
    $color = '#f97316';
} elseif ($percentage < 95) {
    $color = '#d97706';
}

?>

<div
    style="
        width:<?= $size ?? 120 ?>px;
        height:<?= $size ?? 120 ?>px;
        border-radius:50%;
        background:conic-gradient(
            <?= $color ?> <?= $percentage ?>%,
            #e5e7eb <?= $percentage ?>%
        );
        display:flex;
        align-items:center;
        justify-content:center;
        margin:auto;
    "
>
    <div
        style="
            width:75%;
            height:75%;
            border-radius:50%;
            background:#fff;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            font-weight:900;
        "
    >
        <strong style="font-size:24px;">
            <?= number_format($percentage, 1, ',', '.') ?>%
        </strong>

        <small style="color:#64748b;">
            <?= htmlspecialchars($label) ?>
        </small>
    </div>
</div>