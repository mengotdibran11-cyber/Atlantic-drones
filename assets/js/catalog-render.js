/**
 * assets/js/catalog-render.js
 * Renders the Fleet grid and the Accessories grid from the product catalog
 * (loadProducts()), wiring each card up to quick-view / wishlist / compare /
 * cart. Keeps the exact existing card markup & CSS classes so the visual
 * design is unchanged -- this module just generates more of the same cards.
 */
import { qs, money } from './utils.js';
import { loadProducts } from './products-data.js';
import { isWishlisted } from './wishlist.js';

function fleetCardHtml(p, index) {
  const coord = String(index + 1).padStart(2, '0');
  return `
    <div class="fleet-card" data-product-card data-category="${p.category}" data-price="${p.price}" data-name="${p.name.toLowerCase()}">
      <div class="imgwrap">
        <span class="coord mono">${coord} / ${(p.tag || '').toUpperCase()}</span>
        <img src="${p.image}" alt="${p.name}" loading="lazy" data-lightbox="${p.image}">
      </div>
      <div class="tag">${p.tag || ''}</div>
      <h3>${p.name}</h3>
      <p>${p.short_desc || ''}</p>
      <div class="meta">
        <div class="price">${money(p.price)} <span>starting</span></div>
        <a href="#" class="view" data-quickview="${p.id}">View specs →</a>
      </div>
      <div class="card-actions">
        <button class="icon-btn" data-wishlist-toggle="${p.id}" title="Save to wishlist">♥</button>
        <button class="icon-btn" data-compare-toggle="${p.id}" title="Add to comparison">⇄</button>
        <button class="icon-btn icon-btn-primary" data-add-to-cart="${p.id}" title="Add to cart">＋</button>
      </div>
    </div>`;
}

function accessoryCardHtml(p) {
  return `
    <div class="access-card" data-product-card data-category="${p.category}" data-price="${p.price}" data-name="${p.name.toLowerCase()}">
      <div class="imgwrap"><img src="${p.image}" alt="${p.name}" loading="lazy" data-lightbox="${p.image}"></div>
      <div class="copy">
        <div class="tag">${p.tag || ''}</div>
        <h4>${p.name}</h4>
        <p>${p.short_desc || ''}</p>
        <div class="price">${money(p.price)}</div>
        <div class="card-actions">
          <button class="icon-btn" data-wishlist-toggle="${p.id}" title="Save to wishlist">♥</button>
          <button class="icon-btn" data-compare-toggle="${p.id}" title="Add to comparison">⇄</button>
          <button class="icon-btn icon-btn-primary" data-add-to-cart="${p.id}" title="Add to cart">＋</button>
        </div>
      </div>
    </div>`;
}

export async function renderCatalog() {
  const products = await loadProducts();
  const drones = products.filter((p) => p.category === 'Drones' || p.category === 'drone');
  const batteries = products.filter((p) => p.category === 'Batteries' || p.category === 'accessory');

  const fleetGrid = qs('.fleet-grid');
  if (fleetGrid) fleetGrid.innerHTML = drones.map(fleetCardHtml).join('');

  const accessGrid = qs('.access-grid');
  if (accessGrid) accessGrid.innerHTML = batteries.map(accessoryCardHtml).join('');

  // Reflect saved wishlist state on freshly-rendered buttons.
  document.querySelectorAll('[data-wishlist-toggle]').forEach((btn) => {
    btn.classList.toggle('active', isWishlisted(btn.dataset.wishlistToggle));
  });

  document.dispatchEvent(new CustomEvent('catalog:rendered'));
}
