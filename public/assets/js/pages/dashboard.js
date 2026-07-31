'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('shareRankingBtn');

    if (!button) {
        return;
    }

    button.addEventListener('click', async () => {
        if (!window.AppExport) {
            alert('AppExport não foi carregado.');
            return;
        }

        button.disabled = true;
        button.innerHTML = '<i data-lucide="loader-circle"></i>';

        if (window.lucide) {
            lucide.createIcons();
        }

        try {
            await window.AppExport.downloadPNG({
                elementSelector: '#rankingShareCard',
                fileName: 'ranking-diario'
            });
        } catch (error) {
            console.error(error);
            alert('Erro ao gerar PNG.');
        }

        button.disabled = false;
        button.innerHTML = '<i data-lucide="image-down"></i>';

        if (window.lucide) {
            lucide.createIcons();
        }
    });
});

/**
 * Atualização assíncrona do painel de evolução da frequência.
 * O painel inteiro é substituído para manter gráfico, selo, indicadores
 * e opção selecionada sincronizados, sem recarregar a página.
 */
(() => {
    const cache = new Map();
    let requestController = null;

    const labels = {
        '7d': 'Últimos 7 dias',
        '15d': 'Últimos 15 dias',
        '30d': 'Últimos 30 dias',
        '60d': 'Últimos 60 dias',
        '90d': 'Últimos 90 dias',
        'month': 'Este mês',
        'previous_month': 'Mês anterior',
        'semester': 'Este semestre',
        'year': 'Ano letivo'
    };

    const refreshIcons = () => {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    };

    const synchronizePanelLabel = (panel, period, label = null) => {
        const resolvedLabel = label || labels[period] || labels['30d'];
        const badge = panel.querySelector('[data-frequency-period-label]');
        const select = panel.querySelector('[data-frequency-period]');

        panel.dataset.frequencyPeriodCurrent = period;
        panel.dataset.frequencyPeriodLabelCurrent = resolvedLabel;

        if (badge) badge.textContent = resolvedLabel;
        if (select) select.value = period;
    };

    const replacePanel = (currentPanel, html, period, label = null) => {
        const template = document.createElement('template');
        template.innerHTML = String(html).trim();
        const replacement = template.content.firstElementChild;

        if (!replacement) {
            throw new Error('Painel de frequência não foi retornado pelo servidor.');
        }

        synchronizePanelLabel(replacement, period, label);
        currentPanel.replaceWith(replacement);
        bindFrequencyPanel();
        refreshIcons();
    };

    const bindFrequencyPanel = () => {
        const panel = document.getElementById('attendance-frequency-panel');
        if (!panel || panel.dataset.frequencyBound === '1') return;

        const select = panel.querySelector('[data-frequency-period]');
        const form = panel.querySelector('[data-frequency-filter]');
        const endpoint = panel.dataset.frequencyEndpoint;

        if (!select || !form || !endpoint) return;

        panel.dataset.frequencyBound = '1';
        synchronizePanelLabel(panel, select.value);
        cache.set(select.value, {
            html: panel.outerHTML,
            label: labels[select.value] || labels['30d']
        });

        form.addEventListener('submit', (event) => event.preventDefault());

        select.addEventListener('change', async () => {
            const period = select.value;
            const previousPeriod = panel.dataset.frequencyPeriodCurrent || '30d';
            const requestedLabel = labels[period] || labels['30d'];

            // O selo responde imediatamente, enquanto os dados são buscados.
            synchronizePanelLabel(panel, period, requestedLabel);

            const cached = cache.get(period);
            if (cached) {
                replacePanel(panel, cached.html, period, cached.label);
                updateFrequencyUrl(period);
                return;
            }

            if (requestController) requestController.abort();

            requestController = new AbortController();
            panel.classList.add('is-loading');
            select.disabled = true;
            panel.setAttribute('aria-busy', 'true');

            try {
                const url = new URL(endpoint, window.location.href);
                url.searchParams.set('period', period);
                url.searchParams.set('_', Date.now().toString());

                const response = await fetch(url.toString(), {
                    method: 'GET',
                    cache: 'no-store',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: requestController.signal
                });

                if (!response.ok) throw new Error(`Falha HTTP ${response.status}`);

                const payload = await response.json();
                if (!payload.success || typeof payload.html !== 'string') {
                    throw new Error(payload.message || 'Resposta inválida do servidor.');
                }

                const returnedPeriod = String(payload.period || period);
                const returnedLabel = String(payload.periodLabel || labels[returnedPeriod] || requestedLabel);
                cache.set(returnedPeriod, { html: payload.html, label: returnedLabel });
                replacePanel(panel, payload.html, returnedPeriod, returnedLabel);
                updateFrequencyUrl(returnedPeriod);
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Erro ao atualizar evolução da frequência:', error);
                    panel.classList.remove('is-loading');
                    panel.removeAttribute('aria-busy');
                    select.disabled = false;
                    synchronizePanelLabel(panel, previousPeriod);
                    showFrequencyError(panel);
                }
            } finally {
                requestController = null;
            }
        });
    };

    const updateFrequencyUrl = (period) => {
        const url = new URL(window.location.href);
        if (period === '30d') url.searchParams.delete('frequency_period');
        else url.searchParams.set('frequency_period', period);
        window.history.replaceState({}, '', url.toString());
    };

    const showFrequencyError = (panel) => {
        let alert = panel.querySelector('.attendance-frequency-error');
        if (!alert) {
            alert = document.createElement('div');
            alert.className = 'attendance-frequency-error';
            alert.setAttribute('role', 'alert');
            alert.textContent = 'Não foi possível atualizar o período. Tente novamente.';
            panel.prepend(alert);
        }
        window.setTimeout(() => alert.remove(), 5000);
    };

    document.addEventListener('DOMContentLoaded', bindFrequencyPanel);
})();

/** Atualização assíncrona do mapa de calor da frequência. */
(() => {
    const cache = new Map();
    let controller = null;
    let requestSequence = 0;

    const getPanel = () => document.getElementById('frequency-heatmap-panel');

    const cacheCurrentPanel = () => {
        const panel = getPanel();
        const select = panel?.querySelector('[data-heatmap-period]');
        if (!panel || !select) return;
        const clone = panel.cloneNode(true);
        clone.classList.remove('is-loading');
        clone.removeAttribute('aria-busy');
        const cloneSelect = clone.querySelector('[data-heatmap-period]');
        if (cloneSelect) cloneSelect.disabled = false;
        cache.set(String(select.value), clone.outerHTML);
    };

    const replaceHeatmap = (html, expectedPeriod) => {
        const panel = getPanel();
        if (!panel) return false;

        const template = document.createElement('template');
        template.innerHTML = String(html).trim();
        const replacement = template.content.firstElementChild;
        if (!replacement) throw new Error('Mapa de calor não retornado.');

        const replacementSelect = replacement.querySelector('[data-heatmap-period]');
        if (replacementSelect && expectedPeriod) replacementSelect.value = expectedPeriod;
        replacement.classList.remove('is-loading');
        replacement.removeAttribute('aria-busy');
        panel.replaceWith(replacement);
        if (window.lucide) window.lucide.createIcons();
        return true;
    };

    const updateHeatmapUrl = (period) => {
        const url = new URL(window.location.href);
        if (period === 'month') url.searchParams.delete('heatmap_period');
        else url.searchParams.set('heatmap_period', period);
        window.history.replaceState({}, '', url.toString());
    };

    const showError = () => {
        const panel = getPanel();
        if (!panel) return;
        panel.classList.remove('is-loading');
        panel.removeAttribute('aria-busy');
        const select = panel.querySelector('[data-heatmap-period]');
        if (select) select.disabled = false;
        let alertBox = panel.querySelector('.frequency-heatmap-error');
        if (!alertBox) {
            alertBox = document.createElement('div');
            alertBox.className = 'frequency-heatmap-error';
            alertBox.setAttribute('role', 'alert');
            alertBox.textContent = 'Não foi possível atualizar o mapa de calor. Tente novamente.';
            panel.prepend(alertBox);
        }
        window.setTimeout(() => alertBox.remove(), 5000);
    };

    const changePeriod = async (period) => {
        const panel = getPanel();
        const endpoint = panel?.dataset.heatmapEndpoint;
        if (!panel || !endpoint) return;

        if (controller) controller.abort();
        controller = new AbortController();
        const currentRequest = ++requestSequence;

        const cached = cache.get(period);
        if (cached) {
            replaceHeatmap(cached, period);
            updateHeatmapUrl(period);
            controller = null;
            return;
        }

        panel.classList.add('is-loading');
        panel.setAttribute('aria-busy', 'true');
        const select = panel.querySelector('[data-heatmap-period]');
        if (select) select.disabled = true;

        try {
            const url = new URL(endpoint, window.location.href);
            url.searchParams.set('period', period);
            url.searchParams.set('_', Date.now().toString());
            const response = await fetch(url.toString(), {
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                cache: 'no-store',
                signal: controller.signal
            });
            if (!response.ok) throw new Error(`Falha HTTP ${response.status}`);
            const payload = await response.json();
            if (!payload.success || typeof payload.html !== 'string') {
                throw new Error(payload.message || 'Resposta inválida do servidor.');
            }
            if (currentRequest !== requestSequence) return;

            const returnedPeriod = String(payload.period || period);
            cache.set(returnedPeriod, payload.html);
            replaceHeatmap(payload.html, returnedPeriod);
            updateHeatmapUrl(returnedPeriod);
        } catch (error) {
            if (error.name !== 'AbortError' && currentRequest === requestSequence) {
                console.error('Erro ao atualizar mapa de calor:', error);
                showError();
            }
        } finally {
            if (currentRequest === requestSequence) controller = null;
        }
    };

    document.addEventListener('change', (event) => {
        const select = event.target.closest('[data-heatmap-period]');
        if (!select) return;
        changePeriod(String(select.value));
    });

    document.addEventListener('submit', (event) => {
        if (event.target.matches('[data-heatmap-filter]')) event.preventDefault();
    });

    document.addEventListener('DOMContentLoaded', cacheCurrentPanel);
})();
