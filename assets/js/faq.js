/**
 * assets/js/faq.js
 * FAQ accordion (feature #19).
 */
import { qsa } from './utils.js';

export function initFaq() {
  qsa('.faq-item').forEach((item) => {
    const question = item.querySelector('.faq-question');
    question?.addEventListener('click', () => {
      const isOpen = item.classList.contains('open');
      qsa('.faq-item').forEach((i) => i.classList.remove('open'));
      if (!isOpen) item.classList.add('open');
    });
  });
}
