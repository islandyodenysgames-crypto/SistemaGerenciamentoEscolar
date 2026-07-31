<?php
$attachments = is_array($attachments ?? null) ? $attachments : [];
$title = (string) ($title ?? 'Imagens anexadas');
$carouselId = 'attachment-carousel-' . bin2hex(random_bytes(4));
$imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'avif'];
$compact = !array_key_exists('compact', get_defined_vars()) || (bool) $compact;
$contextClass = $compact ? ' is-compact' : '';
$images = array_values(array_filter($attachments, static function (array $attachment) use ($imageExtensions): bool {
    if (!empty($attachment['is_image'])) {
        return true;
    }
    $mime = strtolower((string) ($attachment['mime_type'] ?? $attachment['file_type'] ?? ''));
    $name = (string) ($attachment['original_name'] ?? $attachment['stored_name'] ?? $attachment['relative_path'] ?? $attachment['url'] ?? '');
    $extension = strtolower((string) ($attachment['extension'] ?? pathinfo(parse_url($name, PHP_URL_PATH) ?: $name, PATHINFO_EXTENSION)));
    return str_starts_with($mime, 'image/') || in_array($extension, $imageExtensions, true);
}));

if (!attachment_image_carousels_enabled() || $images === []) {
    return;
}

$groups = array_chunk($images, 3);

$urlFor = static function (array $attachment): string {
    if (!empty($attachment['url'])) {
        return (string) $attachment['url'];
    }
    if (!empty($attachment['relative_path'])) {
        return base_url((string) $attachment['relative_path']);
    }
    return '#';
};
?>
<div class="attachment-image-carousel<?= e($contextClass) ?>" id="<?= e($carouselId) ?>" data-attachment-carousel>
    <div class="attachment-image-carousel__header">
        <span><i data-lucide="images"></i><?= e($title) ?></span>
        <small><b data-carousel-position>1-<?= min(3, count($images)) ?></b>/<?= count($images) ?></small>
    </div>
    <div class="attachment-image-carousel__viewport">
        <div class="attachment-image-carousel__track" data-carousel-track>
            <?php foreach ($groups as $groupIndex => $group): ?>
                <div class="attachment-image-carousel__page" data-carousel-page data-start="<?= $groupIndex * 3 + 1 ?>" data-end="<?= min(($groupIndex + 1) * 3, count($images)) ?>">
                    <div class="attachment-image-carousel__grid count-<?= count($group) ?>">
                        <?php foreach ($group as $localIndex => $image): ?>
                            <?php $absoluteIndex = $groupIndex * 3 + $localIndex; $imageUrl = $urlFor($image); ?>
                            <a class="attachment-image-carousel__item" href="<?= e($imageUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Abrir <?= e((string) ($image['original_name'] ?? 'imagem anexada')) ?>">
                                <img src="<?= e($imageUrl) ?>" alt="<?= e((string) ($image['original_name'] ?? 'Imagem anexada')) ?>" loading="lazy">
                                <span><?= e((string) ($image['original_name'] ?? ('Imagem ' . ($absoluteIndex + 1)))) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($groups) > 1): ?>
            <button type="button" class="attachment-image-carousel__nav is-prev" data-carousel-prev aria-label="Grupo de imagens anterior"><i data-lucide="chevron-left"></i></button>
            <button type="button" class="attachment-image-carousel__nav is-next" data-carousel-next aria-label="Próximo grupo de imagens"><i data-lucide="chevron-right"></i></button>
        <?php endif; ?>
    </div>
    <?php if (count($groups) > 1): ?>
        <div class="attachment-image-carousel__dots">
            <?php foreach ($groups as $index => $_): ?><button type="button" data-carousel-dot="<?= $index ?>" class="<?= $index === 0 ? 'is-active' : '' ?>" aria-label="Exibir grupo <?= $index + 1 ?>"></button><?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php static $carouselAssetsLoaded = false; if (!$carouselAssetsLoaded): $carouselAssetsLoaded = true; ?>
<style>
.attachment-image-carousel{width:100%;max-width:760px;margin:.7rem auto;padding:.7rem;border:1px solid var(--border-color,#e2e8f0);border-radius:14px;background:var(--surface-soft,#f8fafc);overflow:hidden}.attachment-image-carousel__header{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.55rem}.attachment-image-carousel__header>span{display:flex;align-items:center;gap:.4rem;min-width:0;font-size:.84rem;font-weight:750}.attachment-image-carousel__header>span svg{width:16px;height:16px;flex:0 0 auto}.attachment-image-carousel__header small{padding:.16rem .46rem;border-radius:999px;background:#e2e8f0;color:#475569;font-size:.71rem}.attachment-image-carousel__viewport{position:relative;overflow:hidden;border-radius:11px;background:#e2e8f0}.attachment-image-carousel__track{display:flex;transition:transform .32s ease}.attachment-image-carousel__page{min-width:100%;padding:.45rem}.attachment-image-carousel__grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.45rem}.attachment-image-carousel__grid.count-1{grid-template-columns:minmax(0,1fr);max-width:360px;margin:auto}.attachment-image-carousel__grid.count-2{grid-template-columns:repeat(2,minmax(0,1fr));max-width:520px;margin:auto}.attachment-image-carousel__item{position:relative;height:220px;display:block;overflow:hidden;border-radius:9px;color:#fff;background:#cbd5e1}.attachment-image-carousel.is-compact .attachment-image-carousel__item{height:200px}.attachment-image-carousel__item img{width:100%;height:100%;display:block;object-fit:cover}.attachment-image-carousel__item>span{position:absolute;left:0;right:0;bottom:0;padding:1.5rem .65rem .5rem;background:linear-gradient(transparent,rgba(15,23,42,.8));white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.72rem;font-weight:700}.attachment-image-carousel__nav{position:absolute;top:50%;transform:translateY(-50%);width:32px;height:32px;display:grid;place-items:center;border:0;border-radius:50%;background:rgba(15,23,42,.74);color:#fff;cursor:pointer;z-index:2}.attachment-image-carousel__nav.is-prev{left:.55rem}.attachment-image-carousel__nav.is-next{right:.55rem}.attachment-image-carousel__nav svg{width:17px}.attachment-image-carousel__dots{display:flex;justify-content:center;gap:.32rem;margin-top:.5rem}.attachment-image-carousel__dots button{width:7px;height:7px;padding:0;border:0;border-radius:50%;background:#cbd5e1;cursor:pointer}.attachment-image-carousel__dots button.is-active{width:20px;border-radius:999px;background:var(--primary,#16a34a)}@media(max-width:760px){.attachment-image-carousel{max-width:100%}.attachment-image-carousel__grid{grid-template-columns:repeat(2,minmax(0,1fr))}.attachment-image-carousel__grid.count-1{grid-template-columns:minmax(0,1fr)}.attachment-image-carousel__item,.attachment-image-carousel.is-compact .attachment-image-carousel__item{height:190px}}@media(max-width:520px){.attachment-image-carousel__grid,.attachment-image-carousel__grid.count-2{grid-template-columns:minmax(0,1fr);max-width:100%}.attachment-image-carousel__item,.attachment-image-carousel.is-compact .attachment-image-carousel__item{height:210px}}
</style>
<script>
document.addEventListener('DOMContentLoaded',()=>{document.querySelectorAll('[data-attachment-carousel]').forEach(root=>{if(root.dataset.ready==='1')return;root.dataset.ready='1';const track=root.querySelector('[data-carousel-track]');const pages=[...root.querySelectorAll('[data-carousel-page]')];const pos=root.querySelector('[data-carousel-position]');const dots=[...root.querySelectorAll('[data-carousel-dot]')];let index=0;const show=n=>{if(!pages.length)return;index=(n+pages.length)%pages.length;track.style.transform=`translateX(-${index*100}%)`;const page=pages[index];if(pos)pos.textContent=page.dataset.start===page.dataset.end?page.dataset.start:`${page.dataset.start}-${page.dataset.end}`;dots.forEach((dot,i)=>dot.classList.toggle('is-active',i===index));};root.querySelector('[data-carousel-prev]')?.addEventListener('click',()=>show(index-1));root.querySelector('[data-carousel-next]')?.addEventListener('click',()=>show(index+1));dots.forEach(dot=>dot.addEventListener('click',()=>show(Number(dot.dataset.carouselDot))));});});
</script>
<?php endif; ?>
