(function () {
    'use strict';
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    if (!token) return;
    const mutating = method => !['GET', 'HEAD', 'OPTIONS'].includes(String(method || 'GET').toUpperCase());
    const local = value => {
        try { return new URL(typeof value === 'string' ? value : value.url, location.href).origin === location.origin; }
        catch (_) { return false; }
    };
    const originalFetch = window.fetch;
    window.fetch = function (input, init) {
        const options = Object.assign({}, init || {});
        const method = options.method || (input instanceof Request ? input.method : 'GET');
        if (mutating(method) && local(input)) {
            const headers = new Headers(options.headers || (input instanceof Request ? input.headers : undefined));
            if (!headers.has('X-CSRF-Token')) headers.set('X-CSRF-Token', token);
            if (!headers.has('X-Requested-With')) headers.set('X-Requested-With', 'XMLHttpRequest');
            options.headers = headers;
        }
        return originalFetch.call(this, input, options);
    };
    document.addEventListener('submit', event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !mutating(form.method) || form.querySelector('[name="_token"]')) return;
        const field = document.createElement('input'); field.type = 'hidden'; field.name = '_token'; field.value = token; form.appendChild(field);
    }, true);
})();
