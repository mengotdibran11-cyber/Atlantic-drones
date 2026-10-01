/**
 * assets/js/main.js
 * Application entry point -- loaded as <script type="module"> from index.html.
 * Imports every feature module and boots them once the DOM is ready.
 */
import { initMobileNav, initActiveNavHighlighting, initBackToTop } from './nav.js';
import { initTheme } from './theme.js';
import { initScrollAnimations, initAnimatedCounters } from './animations.js';
import { initFaq } from './faq.js';
import { initNewsletter } from './newsletter.js';
import { initContactForm } from './contact-form.js';
import { initModals } from './modal.js';
import { initSearch } from './search.js';
import { initCart } from './cart.js';
import { initWishlist } from './wishlist.js';
import { initCompare } from './compare.js';
import { initRecentlyViewed } from './recently-viewed.js';
import { renderCatalog } from './catalog-render.js';

document.addEventListener('DOMContentLoaded', async () => {
  initMobileNav();
  initActiveNavHighlighting();
  initBackToTop();
  initTheme();
  initFaq();
  initNewsletter();
  initContactForm();
  initModals();
  initCart();
  initWishlist();
  initCompare();
  initRecentlyViewed();

  await renderCatalog();  // fills the Fleet & Accessories grids from the catalog
  initSearch();            // search/filter needs the cards to exist first
  initScrollAnimations();  // re-run after dynamic cards are in the DOM
  initAnimatedCounters();
});
