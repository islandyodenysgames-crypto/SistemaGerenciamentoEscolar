<?php

component('base/page-header', [
    'title' => 'Identidade da Escola',
    'subtitle' => 'Configure os dados institucionais utilizados em todo o sistema'
]);

$schoolName = $school['name'] ?? 'Sistema de Frequência Escolar';
$schoolShortName = $school['short_name'] ?? 'SFE';
$schoolLogo = $school['logo_path'] ?? null;

?>

<form
    method="POST"
    action="<?= base_url('configuracoes/identidade') ?>"
    class="settings-identity-form"
    enctype="multipart/form-data"
>

    <div class="settings-identity-layout">

        <div class="settings-form-column">

            <section class="card settings-section">

                <div class="settings-section-header">
                    <div class="settings-section-icon">
                        <i data-lucide="school"></i>
                    </div>

                    <div>
                        <h3>Identificação</h3>
                        <p>Dados principais da instituição.</p>
                    </div>
                </div>

                <div class="form-grid">

                    <div class="form-group form-span-2">
                        <label>Nome da Escola</label>
                        <input
                            class="form-control"
                            type="text"
                            name="name"
                            id="schoolNameInput"
                            value="<?= e($school['name'] ?? '') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label>Nome abreviado</label>
                        <input
                            class="form-control"
                            type="text"
                            name="short_name"
                            id="schoolShortNameInput"
                            value="<?= e($school['short_name'] ?? '') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label>Código INEP</label>
                        <input class="form-control" type="text" name="inep_code" value="<?= e($school['inep_code'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Diretor(a)</label>
                        <input class="form-control" type="text" name="principal" value="<?= e($school['principal'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Vice-diretor(a)</label>
                        <input class="form-control" type="text" name="vice_principal" value="<?= e($school['vice_principal'] ?? '') ?>">
                    </div>

                </div>

            </section>

            <section class="card settings-section">

                <div class="settings-section-header">
                    <div class="settings-section-icon">
                        <i data-lucide="map-pin"></i>
                    </div>

                    <div>
                        <h3>Localização</h3>
                        <p>Endereço e dados de localização da escola.</p>
                    </div>
                </div>

                <div class="form-grid">

                    <div class="form-group form-span-2">
                        <label>Endereço</label>
                        <input class="form-control" type="text" name="address" value="<?= e($school['address'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Cidade</label>
                        <input class="form-control" type="text" name="city" value="<?= e($school['city'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Estado</label>
                        <input class="form-control" type="text" name="state" maxlength="2" value="<?= e($school['state'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>CEP</label>
                        <input class="form-control" type="text" name="zip_code" value="<?= e($school['zip_code'] ?? '') ?>">
                    </div>

                </div>

            </section>

            <section class="card settings-section">

                <div class="settings-section-header">
                    <div class="settings-section-icon">
                        <i data-lucide="phone"></i>
                    </div>

                    <div>
                        <h3>Contato</h3>
                        <p>Canais oficiais de comunicação da escola.</p>
                    </div>
                </div>

                <div class="form-grid">

                    <div class="form-group">
                        <label>Telefone</label>
                        <input class="form-control" type="text" name="phone" value="<?= e($school['phone'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>E-mail</label>
                        <input class="form-control" type="email" name="email" value="<?= e($school['email'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Site</label>
                        <input class="form-control" type="text" name="website" value="<?= e($school['website'] ?? '') ?>">
                    </div>

                </div>

            </section>

            <section class="card settings-section">

                <div class="settings-section-header">
                    <div class="settings-section-icon">
                        <i data-lucide="globe"></i>
                    </div>

                    <div>
                        <h3>Presença Digital</h3>
                        <p>Perfis oficiais da escola nas redes sociais.</p>
                    </div>
                </div>

                <div class="form-grid">

                    <div class="form-group">
                        <label>Instagram</label>
                        <input class="form-control" type="text" name="instagram" value="<?= e($school['instagram'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Facebook</label>
                        <input class="form-control" type="text" name="facebook" value="<?= e($school['facebook'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>YouTube</label>
                        <input class="form-control" type="text" name="youtube" value="<?= e($school['youtube'] ?? '') ?>">
                    </div>

                </div>

            </section>

            <section class="card settings-section">

                <div class="settings-section-header">
                    <div class="settings-section-icon">
                        <i data-lucide="palette"></i>
                    </div>

                    <div>
                        <h3>Identidade Visual</h3>
                        <p>Logo e cores institucionais utilizadas no sistema.</p>
                    </div>
                </div>

                <div class="settings-visual-grid">

                    <div class="settings-logo-preview">

                        <div class="settings-logo-box">
                            <?php if (!empty($schoolLogo)): ?>
                                <img src="<?= asset($schoolLogo) ?>" alt="Logo da escola">
                            <?php else: ?>
                                <i data-lucide="image"></i>
                            <?php endif; ?>
                        </div>

                        <div>
                            <strong>Logo da Escola</strong>
                            <p>PNG, JPG ou SVG. Recomendado: imagem quadrada.</p>

                            <label class="settings-logo-upload" for="schoolLogoInput">
                                <input
                                    type="file"
                                    name="logo"
                                    id="schoolLogoInput"
                                    accept="image/png,image/jpeg,image/svg+xml"
                                >

                                <span>
                                    <i data-lucide="upload-cloud"></i>
                                    Selecionar logo
                                </span>
                            </label>
                        </div>

                    </div>

                    <div class="form-group">
                        <label>Cor Primária</label>
                        <input class="form-control" type="color" name="primary_color" value="<?= e($school['primary_color'] ?? '#16a34a') ?>">
                    </div>

                    <div class="form-group">
                        <label>Cor Secundária</label>
                        <input class="form-control" type="color" name="secondary_color" value="<?= e($school['secondary_color'] ?? '#f97316') ?>">
                    </div>

                </div>

            </section>

        </div>

        <aside class="settings-preview-column">

            <div class="card settings-preview-card">

                <div class="settings-preview-header">
                    <span>Pré-visualização</span>
                    <strong>Identidade institucional</strong>
                </div>

                <?php component('settings/preview-sidebar', [
                    'name' => $schoolName,
                    'shortName' => $schoolShortName,
                    'logoPath' => $schoolLogo,
                ]); ?>

            </div>

        </aside>

    </div>

    <div class="form-actions">
        <a href="<?= base_url('configuracoes') ?>" class="btn-secondary">
            Voltar
        </a>

        <button type="submit" class="btn-primary">
            Salvar alterações
        </button>
    </div>

</form>