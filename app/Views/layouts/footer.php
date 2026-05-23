<div id="push"></div>
</div><!-- /.container -->

<footer class="footer mt-auto py-3 bg-light">
  <div class="container text-center">
    <span class="text-muted">Built with ❤️ by Jay · JaanaHai</span>
  </div>
</footer>

<!-- JS load order: jQuery → Bootstrap 5 → Leaflet → map.js → datetime helper → page scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="/js/map.js"></script>

<!-- Native datetime picker helper — replaces the old jQuery datetimepicker plugin -->
<script>
/**
 * initDateTimePicker(inputId)
 * Attaches a native datetime-local picker to any text input.
 * Writes back "yyyy-MM-dd HH:mm:ss" which PostgreSQL accepts directly.
 */
function initDateTimePicker(inputId) {
  var display = document.getElementById(inputId);
  if (!display) return;

  // Hidden native picker sits next to the visible text input
  var native = document.createElement('input');
  native.type = 'datetime-local';
  native.style.cssText = 'position:absolute;opacity:0;pointer-events:none;width:1px;height:1px;top:0;left:0;';
  display.parentNode.style.position = 'relative';
  display.parentNode.appendChild(native);

  // Pre-fill native picker if the text input already has a value
  if (display.value) {
    native.value = display.value.replace(' ', 'T').slice(0, 16);
  }

  function openPicker() {
    try { native.showPicker(); } catch(e) { native.click(); }
  }

  display.addEventListener('click',   openPicker);
  display.addEventListener('focus',   openPicker);
  display.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openPicker(); }
  });

  // When native picker changes, format and write to the visible input
  native.addEventListener('change', function() {
    if (!this.value) return;
    // "yyyy-MM-ddTHH:mm" → "yyyy-MM-dd HH:mm:ss"
    var v = this.value.replace('T', ' ');
    if (v.length === 16) v += ':00';
    display.value = v;
    display.dispatchEvent(new Event('change', { bubbles: true }));
  });

  // Calendar icon via CSS background
  display.style.backgroundImage    = "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z'/%3E%3C/svg%3E\")";
  display.style.backgroundRepeat   = 'no-repeat';
  display.style.backgroundPosition = 'right 10px center';
  display.style.paddingRight       = '36px';
  display.style.cursor             = 'pointer';
}
</script>

<?php if (isset($pageScripts)) : ?>
    <?= $pageScripts ?>
<?php endif; ?>
</body>
</html>
