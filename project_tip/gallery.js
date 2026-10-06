(() => {
  const items = Array.from(document.querySelectorAll('[data-gallery]'));
  if (!items.length) return;

  const overlay = document.createElement('div');
  overlay.className = 'gallery-overlay';
  overlay.innerHTML = '<button class="gallery-close" type="button" aria-label="Close">×</button>' +
    '<div class="gallery-modal"><div class="gallery-main">' +
    '<span class="gallery-count"></span><button class="gallery-arrow gallery-prev" type="button">‹</button>' +
    '<img class="gallery-image" alt=""><button class="gallery-arrow gallery-next" type="button">›</button>' +
    '</div><aside class="gallery-side"><span class="gallery-kicker">TAK EXPLORE · GALLERY</span>' +
    '<h2 class="gallery-title"></h2><div class="gallery-location"></div><p class="gallery-desc"></p>' +
    '<div class="gallery-thumbs"></div><div class="gallery-hint">Arrow keys or swipe to browse · ESC to close</div>' +
    '</aside></div>';
  document.body.appendChild(overlay);

  const img = overlay.querySelector('.gallery-image');
  const title = overlay.querySelector('.gallery-title');
  const loc = overlay.querySelector('.gallery-location');
  const desc = overlay.querySelector('.gallery-desc');
  const thumbs = overlay.querySelector('.gallery-thumbs');
  const count = overlay.querySelector('.gallery-count');

  let gallery = [];
  let index = 0;
  let touchX = 0;

  function render() {
    const item = gallery[index];
    img.src = item.src || item;
    img.alt = item.alt || title.textContent;
    count.textContent = (index + 1) + ' / ' + gallery.length;
    thumbs.innerHTML = '';

    gallery.forEach((g, i) => {
      const src = typeof g === 'string' ? g : g.src;
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'gallery-thumb' + (i === index ? ' active' : '');
      const thumb = document.createElement('img');
      thumb.src = src;
      thumb.alt = '';
      button.appendChild(thumb);
      button.onclick = () => { index = i; render(); };
      thumbs.appendChild(button);
    });
  }

  function open(card) {
    try {
      gallery = JSON.parse(card.dataset.gallery || '[]');
    } catch (e) {
      gallery = [];
    }
    if (!gallery.length) return;

    index = 0;
    title.textContent = card.dataset.title || '';
    loc.textContent = card.dataset.location || '';
    desc.textContent = card.dataset.description || 'Explore this place in Tak.';
    render();
    overlay.classList.add('is-open');
    document.body.style.overflow = 'hidden';
  }

  function close() {
    overlay.classList.remove('is-open');
    document.body.style.overflow = '';
  }

  function next(step) {
    if (!gallery.length) return;
    index = (index + step + gallery.length) % gallery.length;
    render();
  }

  items.forEach(card => {
    card.addEventListener('click', event => {
      if (event.target.closest('button[data-place]')) return;
      open(card);
    });
  });

  overlay.querySelector('.gallery-close').onclick = close;
  overlay.querySelector('.gallery-prev').onclick = () => next(-1);
  overlay.querySelector('.gallery-next').onclick = () => next(1);

  overlay.addEventListener('click', event => {
    if (event.target === overlay) close();
  });

  document.addEventListener('keydown', event => {
    if (!overlay.classList.contains('is-open')) return;
    if (event.key === 'Escape') close();
    if (event.key === 'ArrowLeft') next(-1);
    if (event.key === 'ArrowRight') next(1);
  });

  img.addEventListener('touchstart', event => {
    touchX = event.changedTouches[0].clientX;
  }, { passive: true });

  img.addEventListener('touchend', event => {
    const dx = event.changedTouches[0].clientX - touchX;
    if (Math.abs(dx) > 45) next(dx < 0 ? 1 : -1);
  }, { passive: true });
})();