/**
 * assets/js/animations.js
 * Scroll-reveal animations via IntersectionObserver (feature #14) and
 * animated counters for the trust-strip statistics (feature #15).
 */
import { qsa } from './utils.js';

export function initScrollAnimations() {
  const targets = qsa('.fleet-card, .access-card, .service-card, .feature-copy, .feature-visual, .contact-copy, .form');
  targets.forEach((el) => el.classList.add('reveal'));

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('revealed');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });

  targets.forEach((el) => observer.observe(el));
}

export function initAnimatedCounters() {
  const counters = qsa('.trust-item .num[data-count-to]');
  if (!counters.length) return;

  const animate = (el) => {
    const target = parseFloat(el.dataset.countTo);
    const suffix = el.dataset.countSuffix || '';
    const duration = 1200;
    const start = performance.now();

    function tick(now) {
      const progress = Math.min(1, (now - start) / duration);
      const value = Math.floor(progress * target);
      el.textContent = value + suffix;
      if (progress < 1) requestAnimationFrame(tick);
      else el.textContent = target + suffix;
    }
    requestAnimationFrame(tick);
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        animate(entry.target);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.6 });

  counters.forEach((el) => observer.observe(el));
}
