'use strict';

(() => {
    const STORAGE_KEY = 'sge-theme';
    const root = document.documentElement;

    const preferredTheme = () => {
        try {
            const stored = localStorage.getItem(STORAGE_KEY);
            if (stored === 'dark' || stored === 'light') return stored;
        } catch (error) {}
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    };

    const renderIcon = (button, name) => {
        const current = button.querySelector('[data-theme-icon], svg[data-icon-fallback], i[data-lucide]');
        if (!current) return;
        const replacement = document.createElement('i');
        replacement.setAttribute('data-theme-icon', '');
        replacement.setAttribute('data-lucide', name);
        current.replaceWith(replacement);
    };

    const updateControls = (theme) => {
        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            const isDark = theme === 'dark';
            button.setAttribute('aria-pressed', String(isDark));
            button.setAttribute('title', isDark ? 'Ativar modo claro' : 'Ativar modo escuro');
            button.setAttribute('aria-label', isDark ? 'Ativar modo claro' : 'Ativar modo escuro');
            renderIcon(button, isDark ? 'sun' : 'moon');
        });
        if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
    };

    const applyTheme = (theme, persist = false) => {
        const safeTheme = theme === 'dark' ? 'dark' : 'light';
        root.setAttribute('data-theme', safeTheme);
        root.classList.toggle('theme-dark', safeTheme === 'dark');
        root.classList.toggle('theme-light', safeTheme === 'light');
        root.style.colorScheme = safeTheme;
        if (document.body) {
            document.body.setAttribute('data-theme', safeTheme);
            document.body.classList.toggle('theme-dark', safeTheme === 'dark');
            document.body.classList.toggle('theme-light', safeTheme === 'light');
        }
        if (persist) {
            try { localStorage.setItem(STORAGE_KEY, safeTheme); } catch (error) {}
        }
        updateControls(safeTheme);
        document.dispatchEvent(new CustomEvent('sge:theme-changed', { detail: { theme: safeTheme } }));
    };

    const initial = root.getAttribute('data-theme') || preferredTheme();

    document.addEventListener('DOMContentLoaded', () => {
        applyTheme(initial);

        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-theme-toggle]');
            if (!button) return;
            applyTheme(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark', true);
        });

        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const field = button.closest('.input-icon')?.querySelector('input');
                if (!field) return;
                const show = field.type === 'password';
                field.type = show ? 'text' : 'password';
                button.setAttribute('aria-pressed', String(show));
                button.setAttribute('aria-label', show ? 'Ocultar senha' : 'Mostrar senha');
                const current = button.querySelector('[data-password-icon], svg[data-icon-fallback], i[data-lucide]');
                if (current) {
                    const replacement = document.createElement('i');
                    replacement.setAttribute('data-password-icon', '');
                    replacement.setAttribute('data-lucide', show ? 'eye-off' : 'eye');
                    current.replaceWith(replacement);
                    if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
                }
            });
        });
    });

    window.SGETheme = {
        apply: (theme) => applyTheme(theme, true),
        current: () => root.getAttribute('data-theme') || initial
    };
})();
