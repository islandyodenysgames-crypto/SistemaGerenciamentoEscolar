'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');

    const sidebarButtons = [
        document.getElementById('sidebarToggle'),
        document.getElementById('sidebarToggleTop')
    ];

    sidebarButtons.forEach((button) => {
        if (!button || !sidebar) {
            return;
        }

        button.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    });

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});