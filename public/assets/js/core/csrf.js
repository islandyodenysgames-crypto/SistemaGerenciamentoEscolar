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

    const nativeSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function () {
        if (mutating(this.method) && !this.querySelector('[name="_token"]')) {
            const field = document.createElement('input'); field.type = 'hidden'; field.name = '_token'; field.value = token; this.appendChild(field);
        }
        return nativeSubmit.call(this);
    };

    const nativeOpen = XMLHttpRequest.prototype.open;
    const nativeSend = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.open = function (method, url) {
        this.__csrfProtectedRequest = mutating(method) && local(String(url));
        return nativeOpen.apply(this, arguments);
    };
    XMLHttpRequest.prototype.send = function () {
        if (this.__csrfProtectedRequest) {
            this.setRequestHeader('X-CSRF-Token', token);
            this.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        }
        return nativeSend.apply(this, arguments);
    };
})();
