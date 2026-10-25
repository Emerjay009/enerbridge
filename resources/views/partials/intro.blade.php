{{-- Welcome splash: ~2 s, once per browser session. Click, tap or any key skips it. --}}
<script>
  try { if (sessionStorage.getItem('eb_intro')) document.documentElement.classList.add('intro-done'); } catch (e) {}
</script>
<div class="intro" id="intro" role="dialog" aria-label="Welcome to EnerBridge">
  <div class="intro-body">
    <div class="intro-stage" data-tilt>
      <div class="intro-rings" aria-hidden="true"><i></i><i></i><i></i></div>
      <img class="intro-mark" src="{{ asset('img/enerbridge-mark.png') }}" alt="" width="260" height="172">
    </div>
    <div class="intro-hello">Welcome to</div>
    <div class="intro-title" aria-hidden="true">
      <span class="g" style="--i:0">E</span><span class="g" style="--i:1">n</span><span class="g" style="--i:2">e</span><span class="g" style="--i:3">r</span><span class="w" style="--i:4">B</span><span class="w" style="--i:5">r</span><span class="w" style="--i:6">i</span><span class="w" style="--i:7">d</span><span class="w" style="--i:8">g</span><span class="w" style="--i:9">e</span><span class="w" style="--i:10">!</span>
    </div>
    <div class="intro-tag">Smart energy. Stronger tomorrow.</div>
    <div class="intro-bar" aria-hidden="true"><i></i></div>
  </div>
  <button type="button" class="intro-skip" id="intro-skip">Skip</button>
</div>
<script src="{{ asset('js/enerbridge.js') }}" defer></script>
<script>
  (function () {
    var el = document.getElementById('intro');
    if (!el) return;
    var root = document.documentElement;
    var finish = function () {
      try { sessionStorage.setItem('eb_intro', '1'); } catch (e) {}
      root.classList.add('intro-done');
      if (el.parentNode) el.parentNode.removeChild(el);
    };
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce || root.classList.contains('intro-done')) { finish(); return; }

    var leaving = false;
    var leave = function (ms) {
      if (leaving) return;
      leaving = true;
      el.classList.add('leaving');
      setTimeout(finish, ms);
    };
    setTimeout(function () { leave(450); }, 2000);
    el.addEventListener('click', function () { leave(250); });
    document.addEventListener('keydown', function () { leave(250); }, { once: true });
  })();
</script>
