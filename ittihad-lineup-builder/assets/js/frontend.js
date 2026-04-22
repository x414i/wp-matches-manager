/**
 * Ittihad Lineup Builder — Frontend JS
 * Handles lazy loading and mobile detection
 * Developer: محمد بلعيد | github: x414i
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    // Lazy-load player images
    const images = document.querySelectorAll('.ilb-lineup-player img, .ilb-list-player img');

    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            var img = entry.target;
            img.classList.add('ilb-loaded');
            observer.unobserve(img);
          }
        });
      }, { threshold: 0.1 });

      images.forEach(function (img) {
        // Trigger immediately if already loaded
        if (img.complete) {
          img.classList.add('ilb-loaded');
        } else {
          img.addEventListener('load', function () {
            img.classList.add('ilb-loaded');
          });
          img.addEventListener('error', function () {
            img.style.display = 'none';
          });
          observer.observe(img);
        }
      });
    } else {
      // Fallback: show all
      images.forEach(function (img) {
        img.classList.add('ilb-loaded');
      });
    }
  });
}());
