/**
 * assets/js/wishlist.js
 * Wishlist stored in LocalStorage (feature #7).
 */
import { qs, qsa, lsGet, lsSet, money } from './utils.js';
import { showToast } from './toast.js';
import { getById } from './products-data.js';

const WISHLIST_KEY = 'ad_wishlist';

function readWishlist() {
  return lsGet(WISHLIST_KEY, []); // [id, id, ...]
}
function writeWishlist(ids) {
  lsSet(WISHLIST_KEY, ids);
  renderWishlist();
  updateWishlistBadge();
  refreshWishlistButtons();
}

export function isWishlisted(id) {
  return readWishlist().some((x) => x == id);
}

export function toggleWishlist(id) {
  const ids = readWishlist();
  const idx = ids.findIndex((x) => x == id);
  if (idx > -1) {
    ids.splice(idx, 1);
    showToast('Removed from wishlist.', 'info');
  } else {
    ids.push(id);
    showToast('Saved to wishlist.', 'success');
  }
  writeWishlist(ids);
}

function updateWishlistBadge() {
  const count = readWishlist().length;
  qsa('.wishlist-count').forEach((el) => {
    el.textContent = count;
    el.style.display = count > 0 ? 'inline-flex' : 'none';
  });
}

function refreshWishlistButtons() {
  qsa('[data-wishlist-toggle]').forEach((btn) => {
    const id = btn.dataset.wishlistToggle;
    btn.classList.toggle('active', isWishlisted(id));
  });
}

function renderWishlist() {
  const list = qs('#wishlist-items');
  if (!list) return;
  const ids = readWishlist();
  if (!ids.length) {
    list.innerHTML = '<p class="empty-state">No saved items yet.</p>';
    return;
  }
  list.innerHTML = ids.map((id) => {
    const p = getById(id);
    if (!p) return '';
    return `
      <div class="cart-line" data-id="${p.id}">
        <img src="${p.image}" alt="${p.name}">
        <div class="cart-line-body">
          <div class="cart-line-name">${p.name}</div>
          <div class="cart-line-price">${money(p.price)}</div>
        </div>
        <button class="cart-line-remove" data-wishlist-toggle="${p.id}" aria-label="Remove from wishlist">×</button>
      </div>`;
  }).join('');
}

export function initWishlist() {
  updateWishlistBadge();
  renderWishlist();
  refreshWishlistButtons();

  qs('#wishlist-toggle')?.addEventListener('click', () => qs('#wishlist-drawer')?.classList.toggle('open'));
  qs('#wishlist-close')?.addEventListener('click', () => qs('#wishlist-drawer')?.classList.remove('open'));

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-wishlist-toggle]');
    if (btn) toggleWishlist(btn.dataset.wishlistToggle);
  });
}
