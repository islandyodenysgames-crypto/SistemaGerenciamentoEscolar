document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-notice-carousel]').forEach((root) => {
        const viewport = root.querySelector('[data-carousel-viewport]');
        const track = root.querySelector('.notice-wall-track');
        const slides = [...root.querySelectorAll('[data-slide]')];
        const dots = [...root.querySelectorAll('[data-carousel-dot]')];
        const prev = root.querySelector('[data-carousel-prev]');
        const next = root.querySelector('[data-carousel-next]');

        if (!viewport || !track || slides.length === 0) return;

        let index = 0;
        let timer = null;
        let pointerStart = null;

        const visibleCount = () => {
            if (window.innerWidth < 620) return 1;
            if (window.innerWidth < 920) return 2;
            if (window.innerWidth < 1260) return 3;
            return 4;
        };

        const maxIndex = () => Math.max(0, slides.length - visibleCount());

        const setActive = () => {
            slides.forEach((slide, slideIndex) => {
                const active = slideIndex === index;
                // O slide atual controla apenas navegação e acessibilidade.
                // O destaque visual fica reservado exclusivamente ao :hover.
                slide.classList.remove('is-active');
                slide.setAttribute('aria-current', active ? 'true' : 'false');
            });
            dots.forEach((dot, dotIndex) => dot.classList.toggle('active', dotIndex === index));
            if (prev) prev.disabled = slides.length <= visibleCount();
            if (next) next.disabled = slides.length <= visibleCount();
        };

        const go = (requestedIndex, smooth = true) => {
            const last = maxIndex();
            index = requestedIndex > last ? 0 : requestedIndex < 0 ? last : requestedIndex;
            const target = slides[index];
            if (!target) return;
            viewport.scrollTo({
                left: target.offsetLeft - track.offsetLeft,
                behavior: smooth ? 'smooth' : 'auto',
            });
            setActive();
            restart();
        };

        const stop = () => {
            if (timer) window.clearInterval(timer);
            timer = null;
        };

        const restart = () => {
            stop();
            if (slides.length <= visibleCount()) return;
            timer = window.setInterval(() => go(index + 1), 6000);
        };

        prev?.addEventListener('click', () => go(index - 1));
        next?.addEventListener('click', () => go(index + 1));
        dots.forEach((dot, dotIndex) => dot.addEventListener('click', () => go(Math.min(dotIndex, maxIndex()))));

        viewport.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') go(index - 1);
            if (event.key === 'ArrowRight') go(index + 1);
        });

        viewport.addEventListener('pointerdown', (event) => {
            pointerStart = event.clientX;
            stop();
        });
        viewport.addEventListener('pointerup', (event) => {
            if (pointerStart === null) return;
            const distance = event.clientX - pointerStart;
            pointerStart = null;
            if (Math.abs(distance) > 45) go(index + (distance < 0 ? 1 : -1));
            else restart();
        });
        viewport.addEventListener('pointercancel', () => {
            pointerStart = null;
            restart();
        });

        root.addEventListener('mouseenter', stop);
        root.addEventListener('mouseleave', restart);
        root.addEventListener('focusin', stop);
        root.addEventListener('focusout', restart);

        let resizeTimer;
        window.addEventListener('resize', () => {
            window.clearTimeout(resizeTimer);
            resizeTimer = window.setTimeout(() => go(Math.min(index, maxIndex()), false), 120);
        });

        go(0, false);
    });
});

// Detalhes ricos dos avisos: anexos, imagem e vídeo incorporado.
document.addEventListener('DOMContentLoaded', () => {
    const closeModal = (modal) => {
        if (!modal) return;
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('notice-detail-open');
        modal.querySelectorAll('iframe').forEach((iframe) => {
            const src = iframe.getAttribute('src');
            iframe.setAttribute('src', '');
            window.setTimeout(() => iframe.setAttribute('src', src || ''), 10);
        });
    };

    document.querySelectorAll('[data-notice-open]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            const modal = document.getElementById(button.dataset.noticeOpen || '');
            if (!modal) return;
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('notice-detail-open');
            modal.querySelector('[data-notice-close]')?.focus?.();
        });
    });

    document.querySelectorAll('.notice-detail-modal').forEach((modal) => {
        modal.querySelectorAll('[data-notice-close]').forEach((control) => control.addEventListener('click', () => closeModal(modal)));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        closeModal(document.querySelector('.notice-detail-modal.open'));
    });
});
