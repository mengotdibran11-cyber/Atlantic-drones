/**
 * assets/js/recently-viewed.js
 * Recently viewed products (feature #18), backed by LocalStorage.
 */
import { qs, lsGet, lsSet, money } from './utils.js';
import { getById } from './products-data.js';

const KEY = 'ad_recently_viewed';
const MAX_ITEMS = 6;

export function pushRecentlyViewed(id) {
  let ids = lsGet(KEY, []);
  ids = ids.filter((x) => x != id);
  ids.unshift(id);
  ids = ids.slice(0, MAX_ITEMS);
  lsSet(KEY, ids);
  renderRecentlyViewed();
}

export function renderRecentlyViewed() {
  const wrap = qs('#recently-viewed-list');
  const section = qs('#recently-viewed');
  if (!wrap || !section) return;

  const ids = lsGet(KEY, []);
  const products = ids.map(getById).filter(Boolean);

  if (!products.length) {
    section.style.display = 'none';
    return;
  }
  section.style.display = '';
  wrap.innerHTML = products.map((p) => `
    <div class="rv-card" data-quickview="${p.id}">
      <img src="${p.image}" alt="${p.name}">
      <div class="rv-name">${p.name}</div>
      <div class="rv-price">${money(p.price)}</div>
    </div>
  `).join('');
}

export function initRecentlyViewed() {
  renderRecentlyViewed();
}
