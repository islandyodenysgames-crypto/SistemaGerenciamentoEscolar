'use strict';

(function () {
    const badge = document.getElementById('headerNotificationBadge');

    if (!badge) {
        return;
    }

    const baseHref = document
        .querySelector('.header-notification-link')
        ?.getAttribute('href');

    if (!baseHref) {
        return;
    }

    const endpoint = `${baseHref}/resumo`;

    fetch(endpoint, {
        headers: {
            Accept: 'application/json',
        },
        credentials: 'same-origin',
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error('Falha ao carregar notificações.');
            }

            return response.json();
        })
        .then((data) => {
            const count = Number(data.unread_count || 0);

            if (count <= 0) {
                badge.textContent = '';
                badge.classList.remove('is-visible');
                badge.setAttribute('aria-hidden', 'true');
                return;
            }

            badge.textContent = count > 99 ? '99+' : String(count);
            badge.classList.add('is-visible');
            badge.setAttribute('aria-hidden', 'false');
        })
        .catch(() => {
            badge.classList.remove('is-visible');
        });
})();
