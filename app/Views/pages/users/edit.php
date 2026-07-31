<?php

$roles = $roles ?? [];

component('base/page-header', [
    'title' => 'Editar usuário',
    'subtitle' => 'Atualize os dados do usuário',
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
        action="<?= base_url('usuarios/editar') ?>"
        class="user-form"
    >

        <input
            type="hidden"
            name="id"
            value="<?= (int) ($user['id'] ?? 0) ?>"
        >

        <?php component('media/profile-photo-uploader', [
            'prefix' => 'userPhoto',
            'title' => 'Foto do usuário',
            'currentPath' => $user['photo_path'] ?? '',
        ]); ?>

        <div class="form-group">

            <label for="name">Nome</label>

            <input
                class="form-control"
                id="name"
                type="text"
                name="name"
                value="<?= e($user['name'] ?? '') ?>"
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
                value="<?= e($user['email'] ?? '') ?>"
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
                        <?= ($user['role'] ?? 'TEACHER') === $value
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

            <label for="password">Nova senha</label>

            <input
                class="form-control"
                id="password"
                type="password"
                name="password"
                minlength="6"
                placeholder="Deixe em branco para manter a senha atual"
            >

        </div>

        <div class="form-group">

            <label for="active">Status</label>

            <select
                class="form-control"
                id="active"
                name="active"
            >

                <option
                    value="1"
                    <?= (int) ($user['active'] ?? 0) === 1
                        ? 'selected'
                        : ''
                    ?>
                >
                    Ativo
                </option>

                <option
                    value="0"
                    <?= (int) ($user['active'] ?? 0) === 0
                        ? 'selected'
                        : ''
                    ?>
                >
                    Inativo
                </option>

            </select>

        </div>

<?php if (!empty($subjects)): ?>

    <div class="form-group">

        <label>Disciplinas</label>

        <div class="subjects-grid">

            <?php foreach ($subjects as $subject): ?>

                <label class="subject-item">

                    <input
                        type="checkbox"
                        name="subjects[]"
                        value="<?= (int) $subject['id'] ?>"
                        <?= in_array(
                            (int) $subject['id'],
                            $selectedSubjects ?? [],
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

    </div>

<?php endif; ?>

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
                Atualizar
            </button>

        </div>

    </form>

</div>