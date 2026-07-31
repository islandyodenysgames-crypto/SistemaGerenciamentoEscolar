<div class="login-shell">
    <section class="login-presentation" aria-label="Apresentação do Sistema de Gerenciamento Escolar">
        <div class="login-decorations" aria-hidden="true">
            <i data-lucide="graduation-cap"></i>
            <i data-lucide="book-open"></i>
            <i data-lucide="pencil"></i>
            <i data-lucide="flask-conical"></i>
            <i data-lucide="globe-2"></i>
        </div>

        <header class="login-brand">
            <img src="<?= asset('uploads/schools/school-logo.png') ?>" alt="Brasão da escola" class="login-brand-logo">
            <div>
                <strong><span>SGE</span> <b>Escola</b></strong>
                <small>Sistema de Gerenciamento Escolar</small>
            </div>
        </header>

        <div class="login-message">
            <h1>Gestão completa<br>para uma educação<br>mais eficiente.</h1>
            <span class="login-accent" aria-hidden="true"></span>
            <p>Organize informações, acompanhe o desempenho dos alunos e facilite a comunicação entre escola, professores, alunos e responsáveis.</p>
        </div>

        <div class="login-photo" role="img" aria-label="Estudantes chegando à escola">
            <span class="login-wave login-wave-green" aria-hidden="true"></span>
            <span class="login-wave login-wave-light" aria-hidden="true"></span>
        </div>

        <div class="login-benefits" aria-label="Benefícios do sistema">
            <article>
                <span><i data-lucide="chart-line"></i></span>
                <p><strong>Acompanhe</strong><small>Indicadores e relatórios em tempo real.</small></p>
            </article>
            <article>
                <span><i data-lucide="users"></i></span>
                <p><strong>Integre</strong><small>Comunicação fácil entre todos da escola.</small></p>
            </article>
            <article>
                <span><i data-lucide="shield-check"></i></span>
                <p><strong>Proteja</strong><small>Segurança e privacidade para suas informações.</small></p>
            </article>
        </div>
    </section>

    <main class="login-access">
        <button class="login-theme-toggle" type="button" data-theme-toggle title="Ativar modo escuro" aria-label="Ativar modo escuro">
            <i data-theme-icon data-lucide="moon"></i>
        </button>

        <div class="login-card">
            <div class="login-card-icon" aria-hidden="true">
                <i data-lucide="school"></i>
            </div>

            <div class="login-heading">
                <h2>Bem-vindo!</h2>
                <p>Faça login para acessar o sistema.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="auth-error" role="alert">
                    <i data-lucide="circle-alert"></i>
                    <span><?= e((string) $error) ?></span>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="<?= base_url('login') ?>" data-login-form>
                <div class="form-group">
                    <label for="loginEmail">E-mail ou nome de usuário</label>
                    <div class="input-icon">
                        <i data-lucide="mail"></i>
                        <input id="loginEmail" type="text" name="email" placeholder="Digite seu e-mail ou usuário" autocomplete="username" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="loginPassword">Senha</label>
                    <div class="input-icon">
                        <i data-lucide="lock-keyhole"></i>
                        <input id="loginPassword" type="password" name="password" placeholder="Digite sua senha" autocomplete="current-password" required>
                        <button class="password-toggle" type="button" data-password-toggle aria-label="Mostrar senha" aria-pressed="false">
                            <i data-password-icon data-lucide="eye"></i>
                        </button>
                    </div>
                </div>

                <div class="login-form-options">
                    <label class="remember-option">
                        <input type="checkbox" name="remember" value="1" data-remember-login>
                        <span>Lembrar-me</span>
                    </label>
                    <button class="login-link-button" type="button" data-unavailable-feature="A recuperação de senha ainda não foi configurada.">Esqueceu sua senha?</button>
                </div>

                <button class="btn-login" type="submit">
                    <i data-lucide="log-in"></i>
                    <span>Entrar</span>
                </button>
            </form>

            <div class="login-divider"><span>ou continue com</span></div>

            <div class="login-social-actions">
                <button type="button" class="social-login-button" data-unavailable-feature="A integração com o Google ainda não foi configurada.">
                    <span class="google-mark" aria-hidden="true">G</span>
                    <span>Google</span>
                </button>
                <button type="button" class="social-login-button" data-unavailable-feature="A integração com a Microsoft ainda não foi configurada.">
                    <span class="microsoft-mark" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                    <span>Microsoft</span>
                </button>
            </div>

            <a class="login-tv-link" href="<?= base_url('painel-tv') ?>" target="_blank" rel="noopener"><i data-lucide="chart-line"></i><span>Abrir Painel TV</span></a>

            <div class="login-feedback" data-login-feedback role="status" aria-live="polite"></div>

            <footer class="login-footer">© <?= date('Y') ?> <strong>SGE Escola.</strong> Todos os direitos reservados.</footer>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const email = document.querySelector('#loginEmail');
    const remember = document.querySelector('[data-remember-login]');
    const form = document.querySelector('[data-login-form]');
    const feedback = document.querySelector('[data-login-feedback]');

    try {
        const savedLogin = localStorage.getItem('sge-remembered-login');
        if (savedLogin && email && remember) {
            email.value = savedLogin;
            remember.checked = true;
        }
    } catch (error) {}

    form?.addEventListener('submit', function () {
        try {
            if (remember?.checked && email?.value.trim()) {
                localStorage.setItem('sge-remembered-login', email.value.trim());
            } else {
                localStorage.removeItem('sge-remembered-login');
            }
        } catch (error) {}
    });

    document.querySelectorAll('[data-unavailable-feature]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!feedback) return;
            feedback.textContent = button.getAttribute('data-unavailable-feature') || 'Recurso ainda não configurado.';
            feedback.classList.add('is-visible');
            window.setTimeout(function () { feedback.classList.remove('is-visible'); }, 3500);
        });
    });
});
</script>
