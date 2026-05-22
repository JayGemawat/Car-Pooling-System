<?php require __DIR__ . '/../layouts/header.php'; ?>

<?php if (isset($_GET['share'])): ?>
  <div class="alert alert-info">Your ride was added! Edit it from your profile.</div>
<?php elseif (isset($_GET['success'])): ?>
  <div class="alert alert-success">Request sent to the rider for approval.</div>
<?php elseif (isset($_GET['nerror'])): ?>
  <div class="alert alert-danger">Please enter all the details before continuing.</div>
<?php endif; ?>

<div class="row g-4">
  <!-- Search form -->
  <div class="col-12 col-lg-5">
    <div class="card p-4">
      <h5 class="fw-bold mb-3"><i class="bi bi-search me-2"></i>Search for a ride</h5>
      <form action="/search" method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="mb-3 position-relative">
          <label class="form-label">From</label>
          <input type="text" id="from-input" name="from" class="form-control" placeholder="Source" autocomplete="off" required>
        </div>
        <div class="mb-3 position-relative">
          <label class="form-label">To</label>
          <input type="text" id="to-input" name="to" class="form-control" placeholder="Destination" autocomplete="off" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Start time</label>
          <input type="text" id="uptimepicker" name="uptime" class="form-control" placeholder="yyyy-MM-dd HH:mm:ss">
        </div>
        <div class="mb-3">
          <label class="form-label">End time</label>
          <input type="text" id="downtimepicker" name="downtime" class="form-control" placeholder="yyyy-MM-dd HH:mm:ss">
        </div>
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Search</button>
      </form>
    </div>
  </div>

  <!-- Ride cards -->
  <div class="col-12 col-lg-7">
    <h5 class="fw-bold mb-3"><i class="bi bi-car-front me-2"></i>Latest Car Pools</h5>
    <?php if (empty($rides)): ?>
      <div class="text-center text-muted py-5">
        <i class="bi bi-car-front fs-1"></i>
        <p class="mt-2">No upcoming car pools scheduled yet.</p>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($rides as $r): ?>
          <?php
            $distKey = 'dist_' . md5($r['from'] . $r['to']);
            if (!isset($_SESSION[$distKey])) {
                $_SESSION[$distKey] = Offer::distanceBetween($r['from'], $r['to']);
            }
            $km = $_SESSION[$distKey];
            $distLabel = $km ? round($km) . ' km' : '';
            $icon = ($r['vehicle'] === 'taxi') ? 'bi-taxi-front' : 'bi-car-front';
          ?>
          <div class="col-12">
            <div class="card ride-card" onclick="window.location='/ride/<?= (int)$r['id'] ?>'">
              <div class="card-body d-flex align-items-center gap-3">
                <i class="bi <?= $icon ?> fs-2 text-primary"></i>
                <div class="flex-grow-1">
                  <div class="fw-semibold">
                    <?= htmlspecialchars($r['from'], ENT_QUOTES, 'UTF-8') ?>
                    <i class="bi bi-arrow-right mx-1"></i>
                    <?= htmlspecialchars($r['to'], ENT_QUOTES, 'UTF-8') ?>
                  </div>
                  <small class="text-muted"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($r['uptime'], ENT_QUOTES, 'UTF-8') ?></small>
                </div>
                <div class="d-flex flex-column align-items-end gap-1">
                  <?php if ($distLabel): ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars($distLabel, ENT_QUOTES, 'UTF-8') ?></span>
                  <?php endif; ?>
                  <span class="badge bg-primary"><?= htmlspecialchars(ucfirst($r['vehicle']), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php
$pageScripts = <<<'JS'
<script src="/js/datetimepicker.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof $ !== 'undefined' && $.fn.datetimepicker) {
    $('#uptimepicker').datetimepicker({ format: 'yyyy-MM-dd hh:mm:ss' });
    $('#downtimepicker').datetimepicker({ format: 'yyyy-MM-dd hh:mm:ss' });
  }
  JaanaHaiMap.autocomplete('from-input');
  JaanaHaiMap.autocomplete('to-input');
});
</script>
JS;
require __DIR__ . '/../layouts/footer.php';
?>
