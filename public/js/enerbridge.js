/* EnerBridge: gentle 3D tilt for the logo, following the pointer.
   Purely decorative; does nothing for people who prefer reduced motion. */
(function () {
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var els = document.querySelectorAll('[data-tilt]');
  if (!els.length) return;
  window.addEventListener('pointermove', function (e) {
    var x = e.clientX / window.innerWidth - 0.5;
    var y = e.clientY / window.innerHeight - 0.5;
    for (var i = 0; i < els.length; i++) {
      els[i].style.setProperty('--ry', (x * 16).toFixed(2) + 'deg');
      els[i].style.setProperty('--rx', (-y * 12).toFixed(2) + 'deg');
    }
  }, { passive: true });
})();
