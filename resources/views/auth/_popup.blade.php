@if ($errors->any())
<style>
  .popup-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.6); display:flex; align-items:center; justify-content:center; padding:24px; z-index:50; }
  .popup { background:#1e293b; border:2px solid #dc2626; border-radius:12px; padding:24px; width:100%; max-width:360px; text-align:center; }
  .popup h2 { margin:0 0 10px; color:#fecaca; font-size:20px; }
  .popup p { margin:6px 0; color:#e2e8f0; font-size:15px; }
  .popup button { margin-top:16px; padding:9px 28px; width:auto; background:#dc2626; }
  .popup button:hover { background:#b91c1c; }
</style>
<div id="popup" class="popup-backdrop" role="alertdialog" aria-modal="true">
  <div class="popup">
    <h2>{{ $popupTitle ?? 'Something went wrong' }}</h2>
    @foreach ($errors->all() as $message)
      <p>{{ $message }}</p>
    @endforeach
    <button type="button" id="popup-ok">OK</button>
  </div>
</div>
<script>
  const closePopup = () => document.getElementById('popup')?.remove();
  document.getElementById('popup-ok').addEventListener('click', closePopup);
  document.addEventListener('keydown', e => { if (e.key === 'Escape' || e.key === 'Enter') closePopup(); });
  document.getElementById('popup-ok').focus();
</script>
@endif
