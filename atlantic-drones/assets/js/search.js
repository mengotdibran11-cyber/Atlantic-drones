/**
 * assets/js/search.js
 * Product search and filtering (feature #3) over whatever `[data-product-card]`
 * elements are currently on the page (Fleet + Accessories grids), plus a
 * debounced typeahead against /api/search.php.
 */
import { qs, qsa, debounce, apiGet } from './utils.js';

function applyFilters() {
  const term = (qs('#product-search')?.value || '').toLowerCase().trim();
  const category = qs('#category-filter')?.value || '';
  const maxPrice = parseFloat(qs('#price-filter')?.value || '') || Infinity;

  qsa('[data-product-card]').forEach((card) => {
    const name = card.dataset.name || '';
    const cat = card.dataset.category || '';
    const price = parseFloat(card.dataset.price || '0');

    const matchesTerm = !term || name.includes(term);
    const matchesCategory = !category || cat === category;
    const matchesPrice = price <= maxPrice;

    card.style.display = (matchesTerm && matchesCategory && matchesPrice) ? '' : 'none';
  });
}

async function renderSuggestions(term) {
  const box = qs('#search-suggestions');
  if (!box) return;
  if (!term || term.length < 2) { box.innerHTML = ''; box.hidden = true; return; }

  const { ok, data } = await apiGet('api/search.php?q=' + encodeURIComponent(term));
  const results = ok && data ? data.results : [];
  if (!results.length) { box.innerHTML = '<div class="suggestion-empty">No matches.</div>'; box.hidden = false; return; }

  box.innerHTML = results.map((r) => `
    <a href="#fleet" class="suggestion-item" data-quickview="${r.id}">
      <img src="${r.primary_image || r.image}" alt="">
      <span>${r.name}</span>
    </a>`).join('');
  box.hidden = false;
}

export function initSearch() {
  const searchInput = qs('#product-search');
  searchInput?.addEventListener('input', debounce((e) => {
    applyFilters();
    renderSuggestions(e.target.value.trim());
  }, 300));

  qs('#category-filter')?.addEventListener('change', applyFilters);
  qs('#price-filter')?.addEventListener('input', debounce(applyFilters, 200));

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.search-wrap')) {
      const box = qs('#search-suggestions');
      if (box) box.hidden = true;
    }
  });

  // Re-apply current filters whenever the catalog is (re)rendered.
  document.addEventListener('catalog:rendered', applyFilters);
}
