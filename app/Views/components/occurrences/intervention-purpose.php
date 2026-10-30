<?php

$selectedNature = strtoupper((string) ($selectedNature ?? 'ADVERSE'));
$selectedFocusArea = strtoupper((string) ($selectedFocusArea ?? ''));

?>

<div class="form-group form-group-full occurrence-purpose">
    <label for="nature">Finalidade da intervenção</label>
    <select id="nature" name="nature" required>
        <option value="ADVERSE" <?= $selectedNature === 'ADVERSE' ? 'selected' : '' ?>>Registrar uma situação que exige atenção</option>
        <option value="RESPONSE" <?= $selectedNature === 'RESPONSE' ? 'selected' : '' ?>>Registrar uma resposta pedagógica pontual</option>
        <option value="POSITIVE" <?= $selectedNature === 'POSITIVE' ? 'selected' : '' ?>>Registrar um reconhecimento positivo</option>
    </select>
    <small>
        A resposta pontual registra uma providência já realizada pela escola sem exigir a abertura de acompanhamento.
    </small>
</div>

<div class="form-group form-group-full" id="occurrenceFocusAreaGroup" hidden>
    <label for="focus_area">Área atendida pela resposta</label>
    <select id="focus_area" name="focus_area">
        <option value="">Selecione...</option>
        <option value="ATTENDANCE" <?= $selectedFocusArea === 'ATTENDANCE' ? 'selected' : '' ?>>Frequência / infrequência</option>
        <option value="BEHAVIOR" <?= $selectedFocusArea === 'BEHAVIOR' ? 'selected' : '' ?>>Comportamento e convivência</option>
        <option value="LEARNING" <?= $selectedFocusArea === 'LEARNING' ? 'selected' : '' ?>>Aprendizagem</option>
        <option value="WELLBEING" <?= $selectedFocusArea === 'WELLBEING' ? 'selected' : '' ?>>Bem-estar socioemocional</option>
        <option value="HEALTH" <?= $selectedFocusArea === 'HEALTH' ? 'selected' : '' ?>>Saúde</option>
        <option value="TRANSPORT" <?= $selectedFocusArea === 'TRANSPORT' ? 'selected' : '' ?>>Transporte escolar</option>
        <option value="OTHER" <?= $selectedFocusArea === 'OTHER' ? 'selected' : '' ?>>Outra área</option>
    </select>
    <small>
        Em “Frequência / infrequência”, a providência aparecerá como resposta positiva da escola, mas não apagará faltas.
    </small>
</div>

<div class="alert alert-success form-group-full" id="responseRecordNotice" hidden>
    <strong>Resposta positiva da escola</strong>
    <div>
        Este registro demonstrará a providência realizada. Se a área for frequência, ele aparecerá na Visão Analítica sem reduzir as faltas registradas.
    </div>
</div>
