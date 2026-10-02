/**
 * assets/js/loading.js
 * Loading spinner shown during AJAX requests (feature #13).
 */
import { qs } from './utils.js';

function ensureSpinner() {
  let el = qs('#global-spinner');
  if (!el) {
    el = document.createElement('div');
    el.id = 'global-spinner';
    el.innerHTML = '<div class="spinner-ring"></div>';
    document.body.appendChild(el);
  }
  return el;
}

let activeRequests = 0;

export function showSpinner() {
  activeRequests++;
  ensureSpinner().classList.add('visible');
}

export function hideSpinner() {
  activeRequests = Math.max(0, activeRequests - 1);
  if (activeRequests === 0) {
    const el = qs('#global-spinner');
    if (el) el.classList.remove('visible');
  }
}

/** Wrap an async function so the spinner shows for its duration. */
export async function withSpinner(promiseFn) {
  showSpinner();
  try {
    return await promiseFn();
  } finally {
    hideSpinner();
  }
}
