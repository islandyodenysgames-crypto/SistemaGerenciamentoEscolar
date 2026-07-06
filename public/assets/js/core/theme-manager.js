'use strict';

window.ThemeManager = {
    hexToRgb(hex) {
        const normalized = hex.replace('#', '');

        return {
            r: parseInt(normalized.substring(0, 2), 16),
            g: parseInt(normalized.substring(2, 4), 16),
            b: parseInt(normalized.substring(4, 6), 16)
        };
    },

    mixColor(hex, percent) {
        const rgb = this.hexToRgb(hex);
        const target = percent > 0 ? 255 : 0;
        const amount = Math.abs(percent);

        const r = Math.round(rgb.r + (target - rgb.r) * amount);
        const g = Math.round(rgb.g + (target - rgb.g) * amount);
        const b = Math.round(rgb.b + (target - rgb.b) * amount);

        return `rgb(${r}, ${g}, ${b})`;
    },

    apply(primaryColor = '#16a34a', secondaryColor = '#f97316') {
        const root = document.documentElement;

        root.style.setProperty('--primary', primaryColor);
        root.style.setProperty('--primary-light', this.mixColor(primaryColor, .25));
        root.style.setProperty('--primary-dark', this.mixColor(primaryColor, -.20));
        root.style.setProperty('--green-soft', this.mixColor(primaryColor, .90));

        root.style.setProperty('--secondary', secondaryColor);
        root.style.setProperty('--secondary-light', this.mixColor(secondaryColor, .25));
        root.style.setProperty('--secondary-dark', this.mixColor(secondaryColor, -.20));
        root.style.setProperty('--yellow-soft', this.mixColor(secondaryColor, .90));
    }
};