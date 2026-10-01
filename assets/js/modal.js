/**
 * assets/js/modal.js
 * Product quick-view modal (feature #5) and a simple image lightbox
 * (feature #6 in the brief -- "image gallery/lightbox").
 */
import { qs, money } from './utils.js';
import { getById } from './products-data.js';
import { addToCart } from './cart.js';
import { pushRecentlyViewed } from './recently-viewed.js';

export function openQuickView(id) {
  const p = getById(id);
  if (!p) return;
  pushRecentlyViewed(id);

  const modal = qs('#quickview-modal');
  if (!modal) return;

  qs('#qv-image', modal).src = p.image;
  qs('#qv-image', modal).alt = p.name;
  qs('#qv-tag', modal).textContent = p.tag || '';
  qs('#qv-name', modal).textContent = p.name;
  qs('#qv-desc', modal).textContent = p.short_desc || '';
  qs('#qv-price', modal).textContent = money(p.price);
  qs('#qv-add', modal).dataset.addToCart = p.id;
  qs('#qv-wishlist', modal).dataset.wishlistToggle = p.id;
  qs('#qv-compare', modal).dataset.compareToggle = p.id;

  const specsEl = qs('#qv-specs', modal);
  specsEl.innerHTML = Object.entries(p.specs || {})
    .map(([k, v]) => `<div class="spec-item"><div class="k">${k}</div><div class="v">${v}</div></div>`)
    .join('');

  modal.classList.add('open');
}

export function openLightbox(src, alt = '') {
  const box = qs('#lightbox');
  if (!box) return;
  qs('#lightbox-img', box).src = src;
  qs('#lightbox-img', box).alt = alt;
  box.classList.add('open');
}

export function initModals() {
  document.addEventListener('click', (e) => {
    const qvBtn = e.target.closest('[data-quickview]');
    if (qvBtn) openQuickView(qvBtn.dataset.quickview);

    const lightboxTrigger = e.target.closest('[data-lightbox]');
    if (lightboxTrigger) openLightbox(lightboxTrigger.dataset.lightbox, lightboxTrigger.alt || '');

    if (e.target.matches('[data-close-modal]') || e.target.classList.contains('modal-backdrop')) {
      e.target.closest('.modal')?.classList.remove('open');
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') document.querySelectorAll('.modal.open').forEach((m) => m.classList.remove('open'));
  });
}
