/**
 * assets/js/contact-form.js
 * AJAX contact form submission (feature #10) with client-side validation
 * (feature #11), a loading spinner, and toast feedback.
 */
import { qs } from './utils.js';
import { apiPost } from './utils.js';
import { showToast } from './toast.js';
import { withSpinner } from './loading.js';

function fieldError(input, message) {
  const row = input.closest('.form-row');
  let err = row.querySelector('.field-error');
  if (!err) {
    err = document.createElement('div');
    err.className = 'field-error';
    row.appendChild(err);
  }
  err.textContent = message;
  input.classList.toggle('invalid', !!message);
}

function validate(form) {
  let valid = true;
  const name = form.querySelector('[name="name"]');
  const email = form.querySelector('[name="email"]');
  const message = form.querySelector('[name="message"]');

  if (name.value.trim().length < 2) { fieldError(name, 'Please enter your full name.'); valid = false; }
  else fieldError(name, '');

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) { fieldError(email, 'Please enter a valid email.'); valid = false; }
  else fieldError(email, '');

  if (message.value.trim().length < 10) { fieldError(message, 'Message must be at least 10 characters.'); valid = false; }
  else fieldError(message, '');

  return valid;
}

export function initContactForm() {
  const form = qs('.contact .form');
  if (!form) return;

  // give the raw markup a name attribute the JS/validation can target
  form.querySelector('[placeholder="Jordan MacKenzie"]')?.setAttribute('name', 'name');
  form.querySelector('[type="email"]')?.setAttribute('name', 'email');
  form.querySelector('select')?.setAttribute('name', 'interest');
  form.querySelector('textarea')?.setAttribute('name', 'message');

  form.addEventListener('submit', (e) => e.preventDefault());

  form.querySelector('.form-submit')?.addEventListener('click', async () => {
    if (!validate(form)) {
      showToast('Please correct the highlighted fields.', 'error');
      return;
    }
    const payload = {
      name: form.querySelector('[name="name"]').value.trim(),
      email: form.querySelector('[name="email"]').value.trim(),
      interest: form.querySelector('[name="interest"]').value,
      message: form.querySelector('[name="message"]').value.trim(),
    };

    const { data } = await withSpinner(() => apiPost('api/contact.php', payload));
    if (data?.success) {
      showToast(data.message, 'success');
      form.reset();
    } else {
      showToast(data?.message || 'Something went wrong. Please try again.', 'error');
    }
  });
}
