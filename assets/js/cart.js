/**
 * assets/js/cart.js
 * Shopping cart stored in LocalStorage (feature #6), with dynamic price
 * calculation (feature #9) and a slide-out drawer UI.
 */
import { qs, qsa, money, lsGet, lsSet, apiPost } from './utils.js';
import { showToast } from './toast.js';
import { getById } from './products-data.js';

const CART_KEY = 'ad_cart';

function readCart() {
  return lsGet(CART_KEY, []); // [{ id, qty }]
}
function writeCart(items) {
  lsSet(CART_KEY, items);
  renderCart();
  updateCartBadge();
}

export function addToCart(id, qty = 1) {
  const items = readCart();
  const existing = items.find((i) => i.id == id);
  if (existing) existing.qty += qty;
  else items.push({ id, qty });
  writeCart(items);
  showToast('Added to cart.', 'success');
}

export function updateQty(id, qty) {
  let items = readCart();
  if (qty <= 0) {
    items = items.filter((i) => i.id != id);
  } else {
    const line = items.find((i) => i.id == id);
    if (line) line.qty = qty;
  }
  writeCart(items);
}

export function removeFromCart(id) {
  writeCart(readCart().filter((i) => i.id != id));
  showToast('Removed from cart.', 'info');
}

export function clearCart() {
  writeCart([]);
}

export function cartTotal() {
  return readCart().reduce((sum, line) => {
    const p = getById(line.id);
    return sum + (p ? p.price * line.qty : 0);
  }, 0);
}

function updateCartBadge() {
  const count = readCart().reduce((n, l) => n + l.qty, 0);
  qsa('.cart-count').forEach((el) => {
    el.textContent = count;
    el.style.display = count > 0 ? 'inline-flex' : 'none';
  });
}

function renderCart() {
  const list = qs('#cart-items');
  if (!list) return;
  const items = readCart();

  if (!items.length) {
    list.innerHTML = '<p class="empty-state">Your cart is empty.</p>';
  } else {
    list.innerHTML = items.map((line) => {
      const p = getById(line.id);
      if (!p) return '';
      return `
        <div class="cart-line" data-id="${p.id}">
          <img src="${p.image}" alt="${p.name}">
          <div class="cart-line-body">
            <div class="cart-line-name">${p.name}</div>
            <div class="cart-line-price">${money(p.price)}</div>
            <div class="qty-stepper">
              <button class="qty-dec" aria-label="Decrease quantity">−</button>
              <span>${line.qty}</span>
              <button class="qty-inc" aria-label="Increase quantity">+</button>
            </div>
          </div>
          <button class="cart-line-remove" aria-label="Remove item">×</button>
        </div>`;
    }).join('');
  }

  const totalEl = qs('#cart-total');
  if (totalEl) totalEl.textContent = money(cartTotal());
}

export function initCart() {
  updateCartBadge();
  renderCart();

  qs('#cart-toggle')?.addEventListener('click', () => qs('#cart-drawer')?.classList.toggle('open'));
  qs('#cart-close')?.addEventListener('click', () => qs('#cart-drawer')?.classList.remove('open'));

  qs('#cart-items')?.addEventListener('click', (e) => {
    const line = e.target.closest('.cart-line');
    if (!line) return;
    const id = line.dataset.id;
    const items = readCart();
    const current = items.find((i) => i.id == id);
    if (e.target.classList.contains('qty-inc')) updateQty(id, (current?.qty || 0) + 1);
    if (e.target.classList.contains('qty-dec')) updateQty(id, (current?.qty || 0) - 1);
    if (e.target.classList.contains('cart-line-remove')) removeFromCart(id);
  });

  // "Add to cart" buttons rendered anywhere on the page.
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-add-to-cart]');
    if (btn) addToCart(btn.dataset.addToCart, 1);
  });

  qs('#checkout-btn')?.addEventListener('click', async () => {
    const items = readCart();
    if (!items.length) { showToast('Your cart is empty.', 'error'); return; }
    await apiPost('api/cart.php', { action: 'merge', items: items.map((i) => ({ product_id: i.id, quantity: i.qty })) });
    showToast('Quote request drafted — a specialist will follow up by email.', 'success');
  });
}
