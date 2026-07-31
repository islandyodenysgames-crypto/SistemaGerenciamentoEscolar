<?php

$roles = $roles ?? [];
$oldInput = $oldInput ?? [];
$subjects = $subjects ?? [];
$selectedSubjects = $selectedSubjects ?? [];

component('base/page-header', [
    'title' => 'Novo usuário',
    'subtitle' => 'Cadastre um novo usuário do sistema',
]);

?>

<?php if (!empty($userError)): ?>

    <div class="alert alert-danger">
        <?= e($userError) ?>
    </div>

<?php endif; ?>

<div class="card">

    <form
        method="POST"
        action="<?= base_url('usuarios') ?>"
        class="user-form"
    >

        <div class="form-group">

            <label for="name">Nome</label>

            <input
                class="form-control"
                id="name"
                type="text"
                name="name"
                value="<?= e($oldInput['name'] ?? '') ?>"
                required
            >

        </div>

        <div class="form-group">

            <label for="email">E-mail</label>

            <input
                class="form-control"
                id="email"
                type="email"
                name="email"
                value="<?= e($oldInput['email'] ?? '') ?>"
                required
            >

        </div>

        <div class="form-group">

            <label for="role">Perfil de acesso</label>

            <select
                class="form-control"
                id="role"
                name="role"
                required
            >

                <?php foreach ($roles as $value => $label): ?>

                    <option
                        value="<?= e($value) ?>"
                        <?= ($oldInput['role'] ?? 'TEACHER') === $value
                            ? 'selected'
                            : ''
                        ?>
                    >
                        <?= e($label) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <div class="form-group">

            <label for="password">Senha</label>

            <input
                class="form-control"
                id="password"
                type="password"
                name="password"
                minlength="6"
                required
            >

        </div>

        <div class="form-group">

            <label>Disciplinas</label>

            <?php if (!empty($subjects)): ?>

                <div class="subjects-grid">

                    <?php foreach ($subjects as $subject): ?>

                        <label class="subject-item">

                            <input
                                type="checkbox"
                                name="subjects[]"
                                value="<?= (int) $subject['id'] ?>"
                                <?= in_array(
                                    (int) $subject['id'],
                                    $selectedSubjects,
                                    true
                                )
                                    ? 'checked'
                                    : ''
                                ?>
                            >

                            <span>

                                <?= e($subject['name']) ?>

                                <?php if (!empty($subject['code'])): ?>

                                    <small>
                                        (<?= e($subject['code']) ?>)
                                    </small>

                                <?php endif; ?>

                            </span>

                        </label>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="alert alert-warning">

                    Nenhuma disciplina foi cadastrada.

                </div>

            <?php endif; ?>

        </div>

        <div class="form-actions">

            <a
                href="<?= base_url('usuarios') ?>"
                class="btn-secondary"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="btn-primary"
            >
                Salvar
            </button>

        </div>

    </form>

</div>