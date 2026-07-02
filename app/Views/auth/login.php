<div class="auth-container">

    <div class="auth-card">

        <div class="auth-logo">
            <div class="auth-logo-icon">
                <i data-lucide="graduation-cap"></i>
            </div>

            <h1>SFE</h1>
            <p>Sistema de Frequência Escolar</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="auth-error">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form class="auth-form" method="POST" action="<?= base_url('login') ?>">

            <div class="form-group">
                <label>E-mail</label>

                <div class="input-icon">
                    <i data-lucide="mail"></i>
                    <input type="email" name="email" placeholder="Digite seu e-mail" required>
                </div>
            </div>

            <div class="form-group">
                <label>Senha</label>

                <div class="input-icon">
                    <i data-lucide="lock"></i>
                    <input type="password" name="password" placeholder="Digite sua senha" required>
                </div>
            </div>

            <button class="btn-login" type="submit">
                Entrar
            </button>

        </form>

    </div>

</div>