@if ($errors->any())
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
