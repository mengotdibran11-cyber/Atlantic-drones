/**
 * assets/js/compare.js
 * Product comparison feature (feature #8) -- pick up to 3 products and
 * view their specs side by side.
 */
import { qs, qsa, lsGet, lsSet, money } from './utils.js';
import { showToast } from './toast.js';
import { getById } from './products-data.js';

const COMPARE_KEY = 'ad_compare';
const MAX_COMPARE = 3;

function readCompare() {
  return lsGet(COMPARE_KEY, []);
}
function writeCompare(ids) {
  lsSet(COMPARE_KEY, ids);
  updateCompareBadge();
  refreshCompareButtons();
  renderCompareTable();
}

export function toggleCompare(id) {
  const ids = readCompare();
  const idx = ids.findIndex((x) => x == id);
  if (idx > -1) {
    ids.splice(idx, 1);
  } else {
    if (ids.length >= MAX_COMPARE) {
      showToast(`You can compare up to ${MAX_COMPARE} products at a time.`, 'error');
      return;
    }
    ids.push(id);
  }
  writeCompare(ids);
}

function updateCompareBadge() {
  const count = readCompare().length;
  qsa('.compare-count').forEach((el) => {
    el.textContent = count;
    el.style.display = count > 0 ? 'inline-flex' : 'none';
  });
}

function refreshCompareButtons() {
  const ids = readCompare();
  qsa('[data-compare-toggle]').forEach((btn) => {
    btn.classList.toggle('active', ids.some((x) => x == btn.dataset.compareToggle));
  });
}

function renderCompareTable() {
  const wrap = qs('#compare-table-wrap');
  if (!wrap) return;
  const ids = readCompare();
  if (!ids.length) {
    wrap.innerHTML = '<p class="empty-state">Add products to compare using the ⇄ icon on any product card.</p>';
    return;
  }
  const products = ids.map(getById).filter(Boolean);
  const specKeys = [...new Set(products.flatMap((p) => Object.keys(p.specs || {})))];

  let html = '<table class="compare-table"><thead><tr><th></th>';
  products.forEach((p) => { html += `<th><img src="${p.image}" alt="${p.name}"><div>${p.name}</div><button class="compare-remove" data-compare-toggle="${p.id}">Remove</button></th>`; });
  html += '</tr></thead><tbody>';
  html += `<tr><td>Price</td>${products.map((p) => `<td>${money(p.price)}</td>`).join('')}</tr>`;
  specKeys.forEach((key) => {
    html += `<tr><td>${key}</td>${products.map((p) => `<td>${(p.specs && p.specs[key]) || '—'}</td>`).join('')}</tr>`;
  });
  html += '</tbody></table>';
  wrap.innerHTML = html;
}

export function initCompare() {
  updateCompareBadge();
  refreshCompareButtons();
  renderCompareTable();

  qs('#compare-toggle')?.addEventListener('click', () => qs('#compare-modal')?.classList.add('open'));
  qs('#compare-close')?.addEventListener('click', () => qs('#compare-modal')?.classList.remove('open'));

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-compare-toggle]');
    if (btn) toggleCompare(btn.dataset.compareToggle);
  });
}
