<?php

declare(strict_types=1);

use App\Auth\Permissions;
use App\Core\Authorization;

$subjects = $subjects ?? [];

$canManageSubjects = Authorization::can(
    Permissions::SUBJECTS_MANAGE
);

$totalSubjects = count(
    $subjects
);

$totalActive = 0;
$totalInactive = 0;
$totalLinkedUsers = 0;
$totalOccurrences = 0;

foreach ($subjects as $subject) {
    $isActive = (int) (
        $subject['active'] ?? 0
    ) === 1;

    if ($isActive) {
        $totalActive++;
    } else {
        $totalInactive++;
    }

    $totalLinkedUsers += (int) (
        $subject['total_users'] ?? 0
    );

    $totalOccurrences += (int) (
        $subject['total_occurrences'] ?? 0
    );
}

?>

<?php if (!empty($subjectSuccess)): ?>

    <?php component('base/alert', [
        'type' =>
            'success',

        'message' =>
            $subjectSuccess,
    ]); ?>

<?php endif; ?>

<?php if (!empty($subjectError)): ?>

    <?php component('base/alert', [
        'type' =>
            'danger',

        'message' =>
            $subjectError,
    ]); ?>

<?php endif; ?>

<section class="subjects-page">

    <div class="subjects-summary">

        <article class="subjects-summary-card">

            <div class="subjects-summary-icon">
                <i data-lucide="book-open"></i>
            </div>

            <div>
                <span>Total</span>
                <strong><?= $totalSubjects ?></strong>
            </div>

        </article>

        <article class="subjects-summary-card is-active">

            <div class="subjects-summary-icon">
                <i data-lucide="circle-check"></i>
            </div>

            <div>
                <span>Ativas</span>
                <strong><?= $totalActive ?></strong>
            </div>

        </article>

        <article class="subjects-summary-card is-inactive">

            <div class="subjects-summary-icon">
                <i data-lucide="circle-off"></i>
            </div>

            <div>
                <span>Inativas</span>
                <strong><?= $totalInactive ?></strong>
            </div>

        </article>

        <article class="subjects-summary-card">

            <div class="subjects-summary-icon">
                <i data-lucide="users"></i>
            </div>

            <div>
                <span>Vínculos</span>
                <strong><?= $totalLinkedUsers ?></strong>
            </div>

        </article>

        <article class="subjects-summary-card">

            <div class="subjects-summary-icon">
                <i data-lucide="clipboard-list"></i>
            </div>

            <div>
                <span>Ocorrências</span>
                <strong><?= $totalOccurrences ?></strong>
            </div>

        </article>

    </div>

    <section class="card subjects-list-card">

        <div class="subjects-list-header">

            <div>

                <h3>Disciplinas cadastradas</h3>

                <p>
                    Consulte os vínculos, ocorrências e situação de cada disciplina.
                </p>

            </div>

            <?php if ($canManageSubjects): ?>

                <a
                    href="<?= base_url(
                        'disciplinas/nova'
                    ) ?>"
                    class="btn-primary"
                >
                    <i data-lucide="plus"></i>
                    Nova disciplina
                </a>

            <?php endif; ?>

        </div>

        <div class="subjects-search">

            <i data-lucide="search"></i>

            <input
                type="search"
                id="subjectSearch"
                placeholder="Pesquisar por nome ou código..."
                autocomplete="off"
            >

        </div>

        <?php if (empty($subjects)): ?>

            <div class="activity-empty">

                <i data-lucide="book-x"></i>

                <strong>
                    Nenhuma disciplina cadastrada
                </strong>

                <p>
                    Cadastre a primeira disciplina para iniciar os vínculos com os professores.
                </p>

            </div>

        <?php else: ?>

            <div class="table-responsive">

                <table class="subjects-table">

                    <thead>

                        <tr>
                            <th>Disciplina</th>
                            <th>Código</th>
                            <th>Professores</th>
                            <th>Ocorrências</th>
                            <th>Status</th>

                            <?php if ($canManageSubjects): ?>
                                <th>Ações</th>
                            <?php endif; ?>
                        </tr>

                    </thead>

                    <tbody id="subjectsTableBody">

                        <?php foreach (
                            $subjects
                            as $subject
                        ): ?>

                            <?php

                            $subjectId = (int) (
                                $subject['id'] ?? 0
                            );

                            $subjectName = trim(
                                (string) (
                                    $subject['name'] ?? ''
                                )
                            );

                            $subjectCode = trim(
                                (string) (
                                    $subject['code'] ?? ''
                                )
                            );

                            $isActive = (int) (
                                $subject['active'] ?? 0
                            ) === 1;

                            $isGeneral = !empty(
                                $subject['is_general']
                            )
                                || strtoupper(
                                    $subjectCode
                                ) === 'GERAL';

                            $totalUsers = (int) (
                                $subject['total_users'] ?? 0
                            );

                            $occurrences = (int) (
                                $subject['total_occurrences'] ?? 0
                            );

                            ?>

                            <tr
                                data-subject-search="<?= e(
                                    mb_strtolower(
                                        $subjectName
                                        . ' '
                                        . $subjectCode
                                    )
                                ) ?>"
                            >

                                <td>

                                    <div class="subject-name-cell">

                                        <span class="subject-icon">

                                            <i
                                                data-lucide="<?= $isGeneral
                                                    ? 'school'
                                                    : 'book-open'
                                                ?>"
                                            ></i>

                                        </span>

                                        <div>

                                            <strong>
                                                <?= e(
                                                    $subjectName
                                                ) ?>
                                            </strong>

                                            <?php if ($isGeneral): ?>

                                                <small>
                                                    Disciplina padrão do sistema
                                                </small>

                                            <?php elseif (
                                                !empty(
                                                    $subject['description']
                                                )
                                            ): ?>

                                                <small>
                                                    <?= e(
                                                        mb_strimwidth(
                                                            (string) $subject[
                                                                'description'
                                                            ],
                                                            0,
                                                            80,
                                                            '...'
                                                        )
                                                    ) ?>
                                                </small>

                                            <?php endif; ?>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    <span class="subject-code">
                                        <?= e($subjectCode) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= $totalUsers ?>
                                </td>

                                <td>
                                    <?= $occurrences ?>
                                </td>

                                <td>

                                    <span
                                        class="badge <?= $isActive
                                            ? 'badge-success'
                                            : 'badge-secondary'
                                        ?>"
                                    >
                                        <?= $isActive
                                            ? 'Ativa'
                                            : 'Inativa'
                                        ?>
                                    </span>

                                </td>

                                <?php if ($canManageSubjects): ?>

                                    <td>

                                        <div class="subjects-actions">

                                            <a
                                                href="<?= base_url(
                                                    'disciplinas/editar?id='
                                                    . $subjectId
                                                ) ?>"
                                                class="btn-secondary"
                                                title="Editar disciplina"
                                            >
                                                <i data-lucide="pencil"></i>
                                                Editar
                                            </a>

                                            <?php if (
                                                !$isGeneral
                                            ): ?>

                                                <?php if ($isActive): ?>

                                                    <form
                                                        method="POST"
                                                        action="<?= base_url(
                                                            'disciplinas/inativar'
                                                        ) ?>"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="id"
                                                            value="<?= $subjectId ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="btn-secondary"
                                                            title="Inativar disciplina"
                                                        >
                                                            <i data-lucide="circle-off"></i>
                                                            Inativar
                                                        </button>

                                                    </form>

                                                <?php else: ?>

                                                    <form
                                                        method="POST"
                                                        action="<?= base_url(
                                                            'disciplinas/ativar'
                                                        ) ?>"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="id"
                                                            value="<?= $subjectId ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="btn-primary"
                                                            title="Ativar disciplina"
                                                        >
                                                            <i data-lucide="circle-check"></i>
                                                            Ativar
                                                        </button>

                                                    </form>

                                                <?php endif; ?>

                                                <?php if (
                                                    $totalUsers === 0
                                                    && $occurrences === 0
                                                ): ?>

                                                    <form
                                                        method="POST"
                                                        action="<?= base_url(
                                                            'disciplinas/excluir'
                                                        ) ?>"
                                                        onsubmit="
                                                            return confirm(
                                                                'Deseja realmente excluir esta disciplina?'
                                                            );
                                                        "
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="id"
                                                            value="<?= $subjectId ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="btn-danger"
                                                            title="Excluir disciplina"
                                                        >
                                                            <i data-lucide="trash-2"></i>
                                                            Excluir
                                                        </button>

                                                    </form>

                                                <?php endif; ?>

                                            <?php endif; ?>

                                        </div>

                                    </td>

                                <?php endif; ?>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>

</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput =
        document.getElementById(
            'subjectSearch'
        );

    const rows = document.querySelectorAll(
        '#subjectsTableBody tr'
    );

    searchInput?.addEventListener(
        'input',
        () => {
            const term =
                searchInput.value
                    .trim()
                    .toLocaleLowerCase(
                        'pt-BR'
                    );

            rows.forEach((row) => {
                const searchable =
                    row.dataset.subjectSearch
                    || '';

                row.hidden = !searchable.includes(
                    term
                );
            });
        }
    );
});
</script>