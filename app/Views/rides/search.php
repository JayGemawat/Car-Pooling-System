<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="row g-4">
  <!-- Search form -->
  <div class="col-12 col-lg-5">
    <div class="card p-4">
      <h5 class="fw-bold mb-3"><i class="bi bi-search me-2"></i>Search for a ride</h5>
      <form method="post" action="/search">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="mb-3 position-relative">
          <label class="form-label">From</label>
          <input type="text" id="from-input" name="from" class="form-control" placeholder="Source"
                 value="<?= htmlspecialchars($_POST['from'] ?? '', ENT_QUOTES, 'UTF-8') ?>" autocomplete="off" required>
        </div>
        <div class="mb-3 position-relative">
          <label class="form-label">To</label>
          <input type="text" id="to-input" name="to" class="form-control" placeholder="Destination"
                 value="<?= htmlspecialchars($_POST['to'] ?? '', ENT_QUOTES, 'UTF-8') ?>" autocomplete="off" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Start time</label>
          <input type="text" id="uptimepicker" name="uptime" class="form-control datetimepicker-input"
                 placeholder="Select date &amp; time" autocomplete="off"
                 value="<?= htmlspecialchars($_POST['uptime'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">End time</label>
          <input type="text" id="downtimepicker" name="downtime" class="form-control datetimepicker-input"
                 placeholder="Select date &amp; time" autocomplete="off"
                 value="<?= htmlspecialchars($_POST['downtime'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Search</button>
      </form>
    </div>
  </div>

  <!-- Results -->
  <div class="col-12 col-lg-7">
    <h5 class="fw-bold mb-3"><i class="bi bi-list-ul me-2"></i>Search Results</h5>
    <?php if (!empty($_POST) && empty($results)) : ?>
      <div class="text-center text-muted py-5">
        <i class="bi bi-emoji-frown fs-1"></i>
        <p class="mt-2">No car pools match your request.</p>
      </div>
    <?php elseif (!empty($results)) : ?>
      <div class="row g-3">
        <?php foreach ($results as $r) : ?>
            <?php
            $direct = ($r['from'] === ($_POST['from'] ?? '') && $r['to'] === ($_POST['to'] ?? ''));
            $icon   = ($r['vehicle'] === 'taxi') ? 'bi-taxi-front' : 'bi-car-front';
            $km = isset($r['distance_km']) && $r['distance_km'] !== null
                  ? (float) $r['distance_km']
                  : null;
            $distLabel = $km ? round($km) . ' km' : '';
            ?>
          <div class="col-12">
            <div class="card ride-card" onclick="window.location='/ride/<?= (int) $r['id'] ?>'">
              <div class="card-body d-flex align-items-center gap-3">
                <i class="bi <?= $icon ?> fs-2 text-primary"></i>
                <div class="flex-grow-1">
                  <div class="fw-semibold">
                    <?= htmlspecialchars($r['from'], ENT_QUOTES, 'UTF-8') ?>
                    <i class="bi bi-arrow-right mx-1"></i>
                    <?= htmlspecialchars($r['to'], ENT_QUOTES, 'UTF-8') ?>
                  </div>
                  <small class="text-muted"><i class="bi bi-clock me-1"></i><?= htmlspecialchars(formatTime($r['uptime']), ENT_QUOTES, 'UTF-8') ?></small>
                </div>
                <div class="d-flex flex-column align-items-end gap-1">
                  <?php if ($distLabel) : ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars($distLabel, ENT_QUOTES, 'UTF-8') ?></span>
                  <?php endif; ?>
                  <span class="badge <?= $direct ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $direct ? 'Direct' : 'Via' ?></span>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
  initDateTimePicker('uptimepicker');
  initDateTimePicker('downtimepicker');
  JaanaHaiMap.autocomplete('from-input');
  JaanaHaiMap.autocomplete('to-input');
});
</script>
JS;
require __DIR__ . '/../layouts/footer.php';
?>
