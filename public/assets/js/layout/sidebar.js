'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');

    const sidebarButtons = [
        document.getElementById('sidebarToggle'),
        document.getElementById('sidebarToggleTop')
    ];

    const syncAccessibilityState = () => {
        if (!sidebar) {
            return;
        }

        const expanded = !sidebar.classList.contains('collapsed');
        sidebarButtons.forEach((button) => {
            if (button) {
                button.setAttribute('aria-expanded', String(expanded));
                button.setAttribute('aria-controls', 'sidebar');
            }
        });
    };

    sidebarButtons.forEach((button) => {
        if (!button || !sidebar) {
            return;
        }

        button.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            syncAccessibilityState();

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    });

    syncAccessibilityState();

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});