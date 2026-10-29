'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const mobileQuery = window.matchMedia('(max-width: 900px)');
    const backdrop = document.querySelector('[data-sidebar-dismiss]');

    const sidebarButtons = [
        document.getElementById('sidebarToggle'),
        document.getElementById('sidebarToggleTop')
    ];

    const syncAccessibilityState = () => {
        if (!sidebar) {
            return;
        }

        const expanded = mobileQuery.matches
            ? sidebar.classList.contains('mobile-open')
            : !sidebar.classList.contains('collapsed');
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
            if (mobileQuery.matches) {
                const opened = sidebar.classList.toggle('mobile-open');
                document.body.classList.toggle('sidebar-open', opened);
            } else {
                sidebar.classList.toggle('collapsed');
            }
            syncAccessibilityState();

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    });

    const closeMobileSidebar = () => {
        if (!sidebar || !mobileQuery.matches) return;
        sidebar.classList.remove('mobile-open');
        document.body.classList.remove('sidebar-open');
        syncAccessibilityState();
    };

    if (backdrop) backdrop.addEventListener('click', closeMobileSidebar);

    sidebar?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeMobileSidebar);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeMobileSidebar();
    });

    const handleViewportChange = () => {
        sidebar?.classList.remove('mobile-open');
        document.body.classList.remove('sidebar-open');
        syncAccessibilityState();
    };

    mobileQuery.addEventListener?.('change', handleViewportChange);

    syncAccessibilityState();

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
