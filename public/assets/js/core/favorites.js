(() => {
  document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-favorite-toggle]');
    if (!button) return;
    event.preventDefault();
    button.disabled = true;
    try {
      const body = new URLSearchParams({type:button.dataset.favoriteType,id:button.dataset.favoriteId});
      const response = await fetch(button.dataset.favoriteEndpoint, {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','Accept':'application/json'},body});
      const data = await response.json();
      if (!data.success) throw new Error(data.message || 'Não foi possível alterar o favorito.');
      button.classList.toggle('is-active', !!data.active);
      button.setAttribute('aria-pressed', data.active ? 'true' : 'false');
      button.title = data.active ? 'Remover dos favoritos' : 'Adicionar aos favoritos';
      button.querySelector('span') && (button.querySelector('span').textContent = data.active ? 'Favorito' : (button.dataset.favoriteLabel || 'Favoritar'));
      window.lucide?.createIcons();
    } catch (error) { alert(error.message); }
    finally { button.disabled = false; }
  });
})();
