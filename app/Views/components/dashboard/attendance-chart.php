<?php

component('dashboard/attendance-chart-pro', [
    'frequencyLast30Days' => $frequencyLast30Days ?? [],
    'goalPercentage' => $goalPercentage ?? 95,
]);

?>