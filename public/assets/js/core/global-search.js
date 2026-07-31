(() => {
  const root = document.querySelector('[data-global-search]');
  if (!root) return;
  const input = root.querySelector('input');
  const panel = root.querySelector('[data-global-search-results]');
  const endpoint = root.dataset.endpoint;
  const labels = {students:'Alunos',classes:'Turmas',occurrences:'Ocorrências',monitoring:'Acompanhamentos',notices:'Avisos',users:'Usuários'};
  const icons = {student:'user-round',class:'users-round',occurrence:'clipboard-alert',monitoring:'heart-handshake',notice:'megaphone',user:'user-cog'};
  let timer = null, controller = null;
  const hide = () => { panel.hidden = true; panel.innerHTML = ''; };
  input.addEventListener('input', () => {
    clearTimeout(timer);
    const q = input.value.trim();
    if (q.length < 2) return hide();
    timer = setTimeout(async () => {
      controller?.abort(); controller = new AbortController();
      panel.hidden = false; panel.innerHTML = '<div class="global-search-loading">Pesquisando...</div>';
      try {
        const response = await fetch(`${endpoint}?q=${encodeURIComponent(q)}`, {signal:controller.signal,headers:{Accept:'application/json'}});
        const data = await response.json();
        const groups = data.results || {};
        let html = '';
        Object.entries(labels).forEach(([key,label]) => {
          const items = groups[key] || []; if (!items.length) return;
          html += `<section><h4>${label}</h4>`;
          items.forEach(item => {
            const href = `${window.location.origin}${window.location.pathname.replace(/\/public\/.*$/, '/public/')}${String(item.url).replace(/^\//,'')}`;
            const favoriteMark = item.is_favorite && ['student','class','monitoring'].includes(item.type)
              ? '<i class="global-search-favorite-mark" data-lucide="star" aria-label="Favorito"></i>'
              : '';
            html += `<a href="${href}"><i data-lucide="${icons[item.type] || 'search'}"></i><span><strong>${escapeHtml(item.title)}</strong><small>${escapeHtml(item.subtitle || '')}</small></span>${favoriteMark}</a>`;
          });
          html += '</section>';
        });
        panel.innerHTML = html || '<div class="global-search-empty">Nenhum resultado encontrado.</div>';
        window.lucide?.createIcons();
      } catch (e) { if (e.name !== 'AbortError') panel.innerHTML='<div class="global-search-empty">Não foi possível pesquisar.</div>'; }
    }, 250);
  });
  input.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); location.href = `${endpoint.replace('/sugestoes','')}?q=${encodeURIComponent(input.value.trim())}`; } if (e.key === 'Escape') hide(); });
  document.addEventListener('click', e => { if (!root.contains(e.target)) hide(); });
  function escapeHtml(value){ const d=document.createElement('div'); d.textContent=String(value??''); return d.innerHTML; }
})();
