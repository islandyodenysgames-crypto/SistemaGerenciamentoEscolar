document.addEventListener('DOMContentLoaded', () => {
    const bodyClass = 'notice-video-modal-open';

    const openModal = (modal) => {
        if (!modal) return;

        document.querySelectorAll('.notice-video-modal.is-open').forEach((item) => {
            if (item !== modal) closeModal(item);
        });

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add(bodyClass);

        const iframe = modal.querySelector('iframe[data-video-src]');
        if (iframe && !iframe.getAttribute('src')) {
            iframe.setAttribute('src', iframe.dataset.videoSrc || '');
        }

        modal.querySelector('[data-notice-video-close]')?.focus?.();
    };

    const closeModal = (modal) => {
        if (!modal) return;

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

        const iframe = modal.querySelector('iframe[data-video-src]');
        if (iframe) iframe.setAttribute('src', '');

        if (!document.querySelector('.notice-video-modal.is-open')) {
            document.body.classList.remove(bodyClass);
        }
    };

    document.querySelectorAll('[data-notice-video-open]').forEach((button) => {
        button.addEventListener('click', () => {
            openModal(document.getElementById(button.dataset.noticeVideoOpen || ''));
        });
    });

    document.querySelectorAll('.notice-video-modal').forEach((modal) => {
        modal.querySelectorAll('[data-notice-video-close]').forEach((button) => {
            button.addEventListener('click', () => closeModal(modal));
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        closeModal(document.querySelector('.notice-video-modal.is-open'));
    });
});
