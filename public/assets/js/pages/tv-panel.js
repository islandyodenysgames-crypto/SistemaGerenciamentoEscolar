(function(){
const app=document.querySelector('[data-tv-app]');if(!app)return;
const slides=[...app.querySelectorAll('[data-tv-slide]')];let index=0;
const duration=Math.max(5,Number(app.dataset.duration||15))*1000;
const animationDuration=Math.max(300,Number(app.dataset.animationDuration||800));
app.style.setProperty('--animation-duration',animationDuration+'ms');
const progress=app.querySelector('[data-tv-progress]'),dots=app.querySelector('[data-tv-dots]');
slides.forEach((_,i)=>{const dot=document.createElement('i');if(i===0)dot.classList.add('active');dots?.appendChild(dot)});
function activateAnimations(slide){if(!slide)return;slide.querySelectorAll('[data-tv-bar]').forEach(el=>{el.style.width='0';requestAnimationFrame(()=>requestAnimationFrame(()=>{el.style.width=getComputedStyle(el).getPropertyValue('--bar-width').trim()||'0%'}))});slide.querySelectorAll('[data-tv-counter]').forEach(el=>el.classList.add('counter-pop'));}
function show(next){slides[index]?.classList.remove('is-active');dots?.children[index]?.classList.remove('active');index=(next+slides.length)%slides.length;slides[index]?.classList.add('is-active');dots?.children[index]?.classList.add('active');activateAnimations(slides[index]);if(progress)progress.textContent=`${index+1} / ${slides.length}`}
activateAnimations(slides[0]);if(slides.length>1)setInterval(()=>show(index+1),duration);
function clock(){const now=new Date(),c=document.querySelector('[data-tv-clock]'),d=document.querySelector('[data-tv-date]');if(c)c.textContent=now.toLocaleTimeString('pt-BR',{hour:'2-digit',minute:'2-digit'});if(d)d.textContent=now.toLocaleDateString('pt-BR',{weekday:'long',day:'2-digit',month:'long',year:'numeric'})}clock();setInterval(clock,1000);
document.querySelector('[data-tv-fullscreen]')?.addEventListener('click',()=>{if(!document.fullscreenElement)document.documentElement.requestFullscreen?.();else document.exitFullscreen?.()});
const refresh=Math.max(30,Number(app.dataset.refresh||60))*1000;setInterval(()=>location.reload(),refresh);
document.addEventListener('keydown',e=>{if(e.key==='ArrowRight')show(index+1);if(e.key==='ArrowLeft')show(index-1);if(e.key.toLowerCase()==='f')document.querySelector('[data-tv-fullscreen]')?.click()});
})();
