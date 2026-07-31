(() => {
  const setup = (root) => {
    const input=root.querySelector('[data-photo-input]'), select=root.querySelector('[data-photo-select]'), send=root.querySelector('[data-photo-send]');
    const modal=root.querySelector('[data-photo-modal]'), canvas=root.querySelector('[data-photo-canvas]'), zoom=root.querySelector('[data-photo-zoom]');
    const output=root.querySelector('[data-photo-output]'), remove=root.querySelector('[data-photo-remove]'), preview=root.querySelector('[data-photo-preview]'), status=root.querySelector('[data-photo-status]');
    if(!input||!canvas) return; const ctx=canvas.getContext('2d'); let img=null, scale=1, base=1, x=0,y=0,drag=false,sx=0,sy=0;
    const draw=()=>{ctx.clearRect(0,0,canvas.width,canvas.height); if(!img)return; const w=img.width*base*scale,h=img.height*base*scale; ctx.drawImage(img,x,y,w,h)};
    const close=()=>{modal.classList.remove('open');document.body.classList.remove('notice-modal-open')};
    select.addEventListener('click',()=>input.click());
    input.addEventListener('change',()=>{const f=input.files?.[0]; if(!f)return; if(f.size>10*1024*1024){alert('A imagem deve ter no máximo 10 MB.');input.value='';return} status.textContent=f.name+' selecionada. Clique em Enviar imagem.';status.className='profile-photo-selection pending';send.disabled=false;remove.value='0'});
    send.addEventListener('click',()=>{const f=input.files?.[0];if(!f)return;const r=new FileReader();r.onload=()=>{img=new Image();img.onload=()=>{base=Math.max(canvas.width/img.width,canvas.height/img.height);scale=1;zoom.value='1';x=(canvas.width-img.width*base)/2;y=(canvas.height-img.height*base)/2;draw();modal.classList.add('open');document.body.classList.add('notice-modal-open')};img.src=r.result};r.readAsDataURL(f)});
    zoom.addEventListener('input',()=>{if(!img)return;const old=scale;scale=Number(zoom.value);const cx=canvas.width/2,cy=canvas.height/2;x=cx-(cx-x)*(scale/old);y=cy-(cy-y)*(scale/old);draw()});
    canvas.addEventListener('pointerdown',e=>{drag=true;sx=e.clientX-x;sy=e.clientY-y;canvas.setPointerCapture(e.pointerId)});canvas.addEventListener('pointermove',e=>{if(!drag)return;x=e.clientX-sx;y=e.clientY-sy;draw()});canvas.addEventListener('pointerup',()=>drag=false);
    root.querySelectorAll('[data-photo-close]').forEach(b=>b.addEventListener('click',close));
    root.querySelector('[data-photo-apply]')?.addEventListener('click',()=>{output.value=canvas.toDataURL('image/jpeg',.9);preview.style.backgroundImage=`url("${output.value}")`;preview.classList.add('has-image');status.textContent='Imagem preparada. Clique em Salvar alteração.';status.className='profile-photo-selection ready';send.disabled=true;close()});
    root.querySelector('[data-photo-delete]')?.addEventListener('click',()=>{if(!confirm('Remover a foto atual ao salvar?'))return;remove.value='1';output.value='';preview.style.backgroundImage='';preview.classList.remove('has-image');status.textContent='Foto marcada para remoção. Clique em Salvar alteração.';status.className='profile-photo-selection pending'});
  }; document.querySelectorAll('[data-profile-photo-uploader]').forEach(setup);
})();
