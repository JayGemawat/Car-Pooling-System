<div id="push"></div>
</div><!-- /.container -->

<footer class="footer mt-auto py-3 bg-light">
  <div class="container text-center">
    <span class="text-muted">Built with ❤️ by Jay · JaanaHai</span>
  </div>
</footer>

<!-- JS load order: jQuery → Bootstrap 5 → Leaflet → map.js → page scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="/js/map.js"></script>
<?php if (isset($pageScripts)): ?>
  <?= $pageScripts ?>
<?php endif; ?>
</body>
</html>
