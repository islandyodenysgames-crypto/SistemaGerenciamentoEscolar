<div class="card daily-ranking-card">

    <div class="card-header">

        <h3>Ranking diário de frequência</h3>

        <span class="badge badge-success">

            <?= count($ranking) ?>

            turma(s)

        </span>

    </div>

    <?php if (empty($ranking)): ?>

        <div class="attendance-success">

            <div class="attendance-success-icon">
                🏫
            </div>

            <h3>Nenhuma turma cadastrada</h3>

            <p>
                Ainda não existem turmas para exibir no ranking.
            </p>

        </div>

    <?php else: ?>

        <div class="ranking-list">

            <?php foreach ($ranking as $index => $item): ?>

                <?php

                $hasAttendance = (int) ($item['has_attendance'] ?? 0) === 1;

                $percentage = $hasAttendance
                    ? (float) ($item['attendance_percentage'] ?? 0)
                    : 0;

                ?>

                <div class="ranking-item">

                    <div class="ranking-position">

                        <?php

                        if ($hasAttendance) {

                            if ($index === 0) {

                                echo "🥇";

                            } elseif ($index === 1) {

                                echo "🥈";

                            } elseif ($index === 2) {

                                echo "🥉";

                            } else {

                                echo $index + 1;

                            }

                        } else {

                            echo "—";

                        }

                        ?>

                    </div>

                    <div>

                        <div class="ranking-title">

                            <?= e($item['class_name']) ?>

                        </div>

                        <div class="ranking-subtitle">

                            <?= $item['year'] ?>

                            •

                            <?= e($item['shift']) ?>

                        </div>

                        <?php if ($hasAttendance): ?>

                            <div class="ranking-metrics">

                                <span>

                                    Presença

                                    <strong><?= (int) $item['presentes'] ?></strong>

                                </span>

                                <span>

                                    Faltas

                                    <strong><?= (int) $item['ranking_absences'] ?></strong>

                                </span>

                                <span>

                                    IFE

                                    <strong><?= number_format((float) $item['ife_score'], 1, ',', '.') ?></strong>

                                </span>

                            </div>

                        <?php else: ?>

                            <span class="badge badge-warning">

                                Chamada pendente

                            </span>

                        <?php endif; ?>

                    </div>

                    <div class="ranking-progress">

                        <?php component('base/progress', [

                            'percentage' => $percentage,

                            'label' => 'Frequência',

                            'size' => 95

                        ]); ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>