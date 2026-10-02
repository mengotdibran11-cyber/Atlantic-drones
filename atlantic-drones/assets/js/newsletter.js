/**
 * assets/js/newsletter.js
 * Newsletter subscription (feature #20), AJAX to /api/newsletter.php.
 */
import { qs } from './utils.js';
import { apiPost } from './utils.js';
import { showToast } from './toast.js';
import { withSpinner } from './loading.js';

export function initNewsletter() {
  const form = qs('#newsletter-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = qs('#newsletter-email').value.trim();
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      showToast('Please enter a valid email address.', 'error');
      return;
    }
    const { data } = await withSpinner(() => apiPost('api/newsletter.php', { email }));
    showToast(data?.message || 'Subscribed.', data?.success ? 'success' : 'error');
    if (data?.success) form.reset();
  });
}
