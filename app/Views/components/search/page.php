<div class="card">

    <form
        method="GET"
        action="<?= base_url('busca') ?>"
        class="user-form"
    >

        <div class="form-group">

            <label>Pesquisar</label>

            <input
                class="form-control"
                type="text"
                name="q"
                value="<?= e($term ?? '') ?>"
                placeholder="Digite o nome do aluno, matrícula, turma, turno ou data"
            >

        </div>

        <div class="form-actions">

            <button
                type="submit"
                class="btn-primary"
            >
                Buscar
            </button>

        </div>

    </form>

</div>

<?php if (($term ?? '') !== ''): ?>

    <?php component('search/results', [
        'results' => $results ?? []
    ]); ?>

<?php endif; ?>