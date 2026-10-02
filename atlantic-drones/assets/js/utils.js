/**
 * assets/js/utils.js
 * Small shared helpers used across every feature module.
 */

/** Query-select shorthand. */
export const qs  = (sel, ctx = document) => ctx.querySelector(sel);
export const qsa = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

/** Debounce -- used for the search box so we don't fire on every keystroke. */
export function debounce(fn, delay = 300) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), delay);
  };
}

/** Format a number as CAD currency. */
export function money(n) {
  return '$' + Number(n).toLocaleString('en-CA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/** Read a cookie-free CSRF token that was stashed on the page by PHP,
 *  or fetched once from /api/csrf.php and cached for the session. */
let cachedToken = null;
export async function getCsrfToken() {
  if (cachedToken) return cachedToken;
  const inline = document.querySelector('meta[name="csrf-token"]');
  if (inline && inline.content) {
    cachedToken = inline.content;
    return cachedToken;
  }
  try {
    const res = await fetch('api/csrf.php');
    const data = await res.json();
    cachedToken = data.csrf_token || '';
  } catch {
    cachedToken = '';
  }
  return cachedToken;
}

/** POST JSON to a PHP endpoint with the CSRF header attached. */
export async function apiPost(url, body) {
  const token = await getCsrfToken();
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': token },
    body: JSON.stringify(body),
  });
  let data;
  try { data = await res.json(); } catch { data = { success: false, message: 'Unexpected server response.' }; }
  return { ok: res.ok, status: res.status, data };
}

export async function apiGet(url) {
  try {
    const res = await fetch(url);
    const data = await res.json();
    return { ok: res.ok, data };
  } catch {
    return { ok: false, data: null };
  }
}

/** Persist small pieces of state to LocalStorage safely. */
export function lsGet(key, fallback) {
  try {
    const raw = localStorage.getItem(key);
    return raw ? JSON.parse(raw) : fallback;
  } catch {
    return fallback;
  }
}
export function lsSet(key, value) {
  try { localStorage.setItem(key, JSON.stringify(value)); } catch { /* storage full/disabled -- ignore */ }
}
