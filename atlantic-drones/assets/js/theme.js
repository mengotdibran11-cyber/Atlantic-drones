/**
 * assets/js/theme.js
 * Dark/light mode toggle with a saved preference (feature #17).
 */
import { qs } from './utils.js';

const KEY = 'ad_theme';

export function initTheme() {
  const saved = localStorage.getItem(KEY);
  const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches;
  const theme = saved || (prefersDark ? 'dark' : 'light');
  applyTheme(theme);

  qs('#theme-toggle')?.addEventListener('click', () => {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    applyTheme(next);
    localStorage.setItem(KEY, next);
  });
}

function applyTheme(theme) {
  document.documentElement.dataset.theme = theme;
  const btn = qs('#theme-toggle');
  if (btn) btn.textContent = theme === 'dark' ? '☀︎' : '☾';
}
