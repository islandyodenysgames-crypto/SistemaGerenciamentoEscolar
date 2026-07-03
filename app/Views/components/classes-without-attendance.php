<div class="card">

    <div class="card-header">
        <h3>Turmas sem chamada hoje</h3>
    </div>

    <?php if (empty($classesWithoutAttendance)): ?>

        <div class="activity-empty">
            Todas as turmas já tiveram chamada registrada hoje.
        </div>

    <?php else: ?>

        <div
            style="
                display:grid;
                grid-template-columns:repeat(3,1fr);
                gap:16px;
                margin-top:18px;
            "
        >

            <?php foreach ($classesWithoutAttendance as $class): ?>

                <div
                    style="
                        background:#fef3c7;
                        border-radius:18px;
                        padding:18px;
                        border:1px solid #fde68a;
                    "
                >

                    <h3 style="margin-bottom:8px;">
                        🏫 <?= htmlspecialchars($class['name']) ?>
                    </h3>

                    <p style="margin-bottom:14px;color:#92400e;">
                        Ano: <strong><?= $class['year'] ?></strong><br>
                        Turno: <strong><?= htmlspecialchars($class['shift']) ?></strong>
                    </p>

                    <a
                        href="<?= base_url('frequencia/novo?turma=' . $class['id']) ?>"
                        class="btn-primary"
                    >
                        Registrar frequência
                    </a>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<style>
@media (max-width: 1100px) {
    .card > div[style*="grid-template-columns:repeat(3,1fr)"] {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

@media (max-width: 700px) {
    .card > div[style*="grid-template-columns:repeat(3,1fr)"] {
        grid-template-columns: 1fr !important;
    }
}
</style>