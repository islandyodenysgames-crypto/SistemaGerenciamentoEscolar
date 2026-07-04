<div class="card">

    <div class="card-header">

        <h3>Turmas sem chamada hoje</h3>

        <?php if (!empty($classesWithoutAttendance)): ?>

            <span class="badge badge-warning">
                <?= count($classesWithoutAttendance) ?> pendente(s)
            </span>

        <?php endif; ?>

    </div>

    <?php if (empty($classesWithoutAttendance)): ?>

        <div class="attendance-success">

            <div class="attendance-success-icon">
                ✅
            </div>

            <h3>Todas as turmas já registraram frequência hoje</h3>

            <p>
                Excelente! Nenhuma turma está pendente.
            </p>

        </div>

    <?php else: ?>

        <div class="pending-grid">

            <?php foreach ($classesWithoutAttendance as $class): ?>

                <a
                    href="<?= base_url('frequencia/novo?turma=' . $class['id']) ?>"
                    class="pending-card"
                >

                    <div class="pending-icon">
                        🏫
                    </div>

                    <div class="pending-title">
                        <?= e($class['name']) ?>
                    </div>

                    <div class="pending-year">
                        <?= $class['year'] ?>
                    </div>

                    <div class="pending-shift">
                        <?= e($class['shift']) ?>
                    </div>

                    <div class="pending-action">
                        Registrar frequência →
                    </div>

                </a>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>