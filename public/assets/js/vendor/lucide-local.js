(function (global) {
    'use strict';

    if (global.lucide && typeof global.lucide.createIcons === 'function') return;

    const NS = 'http://www.w3.org/2000/svg';
    const common = {
        menu: ['path','M4 6h16M4 12h16M4 18h16'],
        mail: ['rect','3 5 18 14 2','path','m3 7 9 6 9-6'],
        lock: ['rect','4 10 16 11 2','path','M8 10V7a4 4 0 0 1 8 0v3'],
        'lock-keyhole': ['rect','4 10 16 11 2','path','M8 10V7a4 4 0 0 1 8 0v3M12 14v3'],
        'log-in': ['path','M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3'],
        'eye-off': ['path','m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 4.2A10.8 10.8 0 0 1 12 4c6.5 0 10 8 10 8a18.3 18.3 0 0 1-2.1 3.2M6.6 6.6C3.7 8.5 2 12 2 12s3.5 8 10 8a10.5 10.5 0 0 0 5.4-1.5'],
        'graduation-cap': ['path','m2 10 10-5 10 5-10 5ZM6 12v5c3 2 9 2 12 0v-5M22 10v6'],
        'book-open': ['path','M2 4h6a4 4 0 0 1 4 4v12a4 4 0 0 0-4-4H2ZM22 4h-6a4 4 0 0 0-4 4v12a4 4 0 0 1 4-4h6Z'],
        'shield-check': ['path','M20 13c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V5l8-3 8 3ZM9 12l2 2 4-4'],
        'circle-alert': ['circle','12 12 9','path','M12 8v4M12 16h.01'],
        'chart-line': ['path','M3 3v18h18M7 16l4-4 3 3 5-7'],
        'chart-no-axes-combined': ['path','M3 18l6-6 4 4 8-10M14 6h7v7'],
        pencil: ['path','m18 2 4 4L7 21l-4 1 1-4ZM14.5 5.5l4 4'],
        'flask-conical': ['path','M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.7 3h10.6A2 2 0 0 0 19 18l-5-9V3M8 15h8'],
        'globe-2': ['circle','12 12 9','path','M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18'],
        search: ['circle','11 11 7','path','m20 20-3.5-3.5'],
        bell: ['path','M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4'],
        moon: ['path','M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8'],
        sun: ['circle','12 12 4','path','M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41'],
        x: ['path','M18 6 6 18M6 6l12 12'],
        plus: ['path','M12 5v14M5 12h14'],
        minus: ['path','M5 12h14'],
        check: ['path','m20 6-11 11-5-5'],
        'chevron-right': ['path','m9 18 6-6-6-6'],
        'chevron-left': ['path','m15 18-6-6 6-6'],
        'chevron-down': ['path','m6 9 6 6 6-6'],
        'chevron-up': ['path','m18 15-6-6-6 6'],
        'arrow-right': ['path','M5 12h14m-6-6 6 6-6 6'],
        'arrow-left': ['path','M19 12H5m6 6-6-6 6-6'],
        'external-link': ['path','M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6'],
        save: ['path','M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2M17 21v-8H7v8M7 3v5h8'],
        pencil: ['path','m18 2 4 4L7 21l-4 1 1-4ZM14.5 5.5l4 4'],
        trash: ['path','M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6'],
        user: ['circle','12 8 4','path','M4 21a8 8 0 0 1 16 0'],
        users: ['path','M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75'],
        school: ['path','m3 10 9-6 9 6M5 9v11h14V9M9 20v-6h6v6M3 20h18'],
        calendar: ['rect','3 5 18 16 2','path','M16 3v4M8 3v4M3 11h18'],
        clock: ['circle','12 12 9','path','M12 7v5l3 2'],
        file: ['path','M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8ZM14 2v6h6M8 13h8M8 17h6'],
        book: ['path','M4 19.5A2.5 2.5 0 0 1 6.5 17H20V4H6.5A2.5 2.5 0 0 0 4 6.5ZM4 6.5v13M8 7h8'],
        chart: ['path','M4 19V9M10 19V5M16 19v-7M22 19H2'],
        alert: ['path','M10.3 2.9 1.8 17a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 2.9a2 2 0 0 0-3.4 0M12 9v4M12 17h.01'],
        info: ['circle','12 12 9','path','M12 11v5M12 8h.01'],
        help: ['circle','12 12 9','path','M9.1 9a3 3 0 1 1 5.8 1c0 2-3 2-3 4M12 18h.01'],
        settings: ['circle','12 12 3','path','M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1V21H9.6v-.09a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1-.4H3V9.6h.09A1.7 1.7 0 0 0 4.6 8.5a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1V3h4v.09A1.7 1.7 0 0 0 15.5 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.1.4.3.8.6 1 .3.3.7.4 1 .4h.1v4H21a1.7 1.7 0 0 0-1.6.6'],
        home: ['path','m3 11 9-8 9 8v9a2 2 0 0 1-2 2h-4v-7H9v7H5a2 2 0 0 1-2-2Z'],
        target: ['circle','12 12 9','circle','12 12 5','circle','12 12 1'],
        eye: ['path','M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12','circle','12 12 3'],
        filter: ['path','M4 5h16l-6 7v5l-4 2v-7Z'],
        download: ['path','M12 3v12m-5-5 5 5 5-5M5 21h14'],
        upload: ['path','M12 21V9m-5 5 5-5 5 5M5 3h14'],
        logout: ['path','M10 17l5-5-5-5M15 12H3M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4'],
        panel: ['rect','3 3 18 18 2','path','M9 3v18'],
        loader: ['path','M21 12a9 9 0 1 1-6.2-8.6']
    };

    function typeFor(name) {
        const n = String(name || '').toLowerCase();
        if (common[n]) return n;
        if (/trash|delete/.test(n)) return 'trash';
        if (/flask/.test(n)) return 'flask-conical';
        if (/globe/.test(n)) return 'globe-2';
        if (/edit|pencil|pen/.test(n)) return 'pencil';
        if (/save|disk/.test(n)) return 'save';
        if (/calendar|date/.test(n)) return 'calendar';
        if (/clock|history|time/.test(n)) return 'clock';
        if (/users|group|people|class/.test(n)) return 'users';
        if (/user|person|student|teacher/.test(n)) return 'user';
        if (/graduation-cap/.test(n)) return 'graduation-cap';
        if (/school|graduation/.test(n)) return 'school';
        if (/book-open/.test(n)) return 'book-open';
        if (/book|subject/.test(n)) return 'book';
        if (/file|clipboard|report|document/.test(n)) return 'file';
        if (/chart-no-axes-combined/.test(n)) return 'chart-no-axes-combined';
        if (/chart-line/.test(n)) return 'chart-line';
        if (/chart|trend|activity|ranking|signal/.test(n)) return 'chart';
        if (/alert|triangle|warning|risk/.test(n)) return 'alert';
        if (/info/.test(n)) return 'info';
        if (/help|question/.test(n)) return 'help';
        if (/setting|cog|sliders/.test(n)) return 'settings';
        if (/home|dashboard|layout/.test(n)) return 'home';
        if (/target|goal/.test(n)) return 'target';
        if (/eye-off/.test(n)) return 'eye-off';
        if (/eye|view/.test(n)) return 'eye';
        if (/lock-keyhole/.test(n)) return 'lock-keyhole';
        if (/lock/.test(n)) return 'lock';
        if (/mail/.test(n)) return 'mail';
        if (/filter/.test(n)) return 'filter';
        if (/download|image-down|export/.test(n)) return 'download';
        if (/upload/.test(n)) return 'upload';
        if (/log-in|login|sign-in/.test(n)) return 'log-in';
        if (/log-out|logout|exit/.test(n)) return 'logout';
        if (/panel-left|sidebar/.test(n)) return 'panel';
        if (/loader|refresh|rotate/.test(n)) return 'loader';
        if (/shield-check/.test(n)) return 'shield-check';
        if (/circle-alert/.test(n)) return 'circle-alert';
        if (/check|circle-check/.test(n)) return 'check';
        if (/plus|add/.test(n)) return 'plus';
        if (/minus|remove/.test(n)) return 'minus';
        if (/close|^x$/.test(n)) return 'x';
        if (/search/.test(n)) return 'search';
        if (/bell|notification/.test(n)) return 'bell';
        if (/menu/.test(n)) return 'menu';
        if (/moon/.test(n)) return 'moon';
        if (/sun/.test(n)) return 'sun';
        if (/external/.test(n)) return 'external-link';
        if (/chevron-left/.test(n)) return 'chevron-left';
        if (/chevron-right/.test(n)) return 'chevron-right';
        if (/chevron-up/.test(n)) return 'chevron-up';
        if (/chevron-down/.test(n)) return 'chevron-down';
        if (/arrow-left/.test(n)) return 'arrow-left';
        if (/arrow/.test(n)) return 'arrow-right';
        return 'info';
    }

    function node(tag, attrs) {
        const el = document.createElementNS(NS, tag);
        Object.entries(attrs || {}).forEach(([k,v]) => el.setAttribute(k, v));
        return el;
    }

    function draw(svg, spec) {
        for (let i = 0; i < spec.length;) {
            const kind = spec[i++];
            if (kind === 'path') svg.appendChild(node('path', { d: spec[i++] }));
            else if (kind === 'circle') {
                const p = String(spec[i++]).split(' ');
                svg.appendChild(node('circle', { cx:p[0], cy:p[1], r:p[2] }));
            } else if (kind === 'rect') {
                const p = String(spec[i++]).split(' ');
                svg.appendChild(node('rect', { x:p[0], y:p[1], width:p[2], height:p[3], rx:p[4] || 0 }));
            }
        }
    }

    function replaceIcon(el) {
        if (!el || !el.getAttribute) return;
        const name = el.getAttribute('data-lucide');
        if (!name) return;
        const svg = node('svg', {
            xmlns:NS, width:'24', height:'24', viewBox:'0 0 24 24', fill:'none',
            stroke:'currentColor', 'stroke-width':'2', 'stroke-linecap':'round', 'stroke-linejoin':'round',
            'aria-hidden': el.getAttribute('aria-label') ? 'false' : 'true', focusable:'false',
            'data-icon-fallback': name
        });
        if (el.className) svg.setAttribute('class', el.className);
        const style = el.getAttribute('style'); if (style) svg.setAttribute('style', style);
        const title = el.getAttribute('title'); if (title) svg.setAttribute('title', title);
        draw(svg, common[typeFor(name)] || common.info);
        el.replaceWith(svg);
    }

    function createIcons(options) {
        const root = options && options.attrs && options.attrs.root ? options.attrs.root : document;
        (root || document).querySelectorAll('[data-lucide]').forEach(replaceIcon);
    }

    global.lucide = { createIcons: createIcons, icons: {} };
})(window);
