'use strict';

document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.students-select-all').forEach((checkbox) => {

        checkbox.addEventListener('change', () => {

            const panel = checkbox.dataset.targetPanel;

            document
                .querySelectorAll(`.student-checkbox[data-panel="${panel}"]`)
                .forEach((studentCheckbox) => {
                    studentCheckbox.checked = checkbox.checked;
                });

        });

    });

});
/**
 * Sprint 2.7.5.5.1 — expansão progressiva do histórico do acompanhamento.
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-progressive-timeline]').forEach((timeline) => {
        const section = timeline.closest('.student-followup-timeline');
        const toggle = section?.querySelector('[data-timeline-toggle]');
        const items = Array.from(timeline.querySelectorAll('[data-timeline-item]'));

        if (!toggle || items.length === 0) {
            return;
        }

        const initialLimit = Math.max(1, Number.parseInt(timeline.dataset.initialLimit || '8', 10));
        const step = Math.max(1, Number.parseInt(timeline.dataset.step || '8', 10));
        const moreLabel = toggle.dataset.moreLabel || 'Ver mais eventos';
        const lessLabel = toggle.dataset.lessLabel || 'Recolher histórico';
        const label = toggle.querySelector('span');
        let visibleCount = Math.min(initialLimit, items.length);

        const render = () => {
            items.forEach((item, index) => {
                item.classList.toggle('is-timeline-hidden', index >= visibleCount);
            });

            const remaining = Math.max(0, items.length - visibleCount);
            const fullyExpanded = remaining === 0;
            toggle.setAttribute('aria-expanded', fullyExpanded ? 'true' : 'false');

            if (label) {
                label.textContent = fullyExpanded
                    ? lessLabel
                    : `${moreLabel.replace(/ eventos?$/i, '')} ${Math.min(step, remaining)} evento(s)`;
            }
        };

        toggle.addEventListener('click', () => {
            const isFullyExpanded = visibleCount >= items.length;

            if (isFullyExpanded) {
                visibleCount = Math.min(initialLimit, items.length);
                render();
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }

            visibleCount = Math.min(items.length, visibleCount + step);
            render();
        });

        render();
    });
});
