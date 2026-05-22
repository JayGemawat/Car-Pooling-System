<?php require __DIR__ . '/../layouts/header.php'; ?>

<h5 class="fw-bold mb-3"><i class="bi bi-bell me-2"></i>Notifications</h5>

<?php if (empty($notifications)): ?>
  <div class="text-center text-muted py-5">
    <i class="bi bi-bell-slash fs-1"></i>
    <p class="mt-2">No notifications yet.</p>
  </div>
<?php else: ?>
  <div class="list-group">
    <?php foreach ($notifications as $n): ?>
      <?php
        $type  = (int) $n['type'];
        $slno  = (int) $n['slno'];
        $offer = $offerCache[(int)$n['cid']] ?? null;
        $route = $offer
          ? htmlspecialchars($offer['from'], ENT_QUOTES, 'UTF-8') . ' &rarr; ' . htmlspecialchars($offer['to'], ENT_QUOTES, 'UTF-8')
          : 'Unknown route';
      ?>
      <div class="list-group-item list-group-item-action">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
          <div>
            <div class="fw-semibold"><?= $route ?></div>
            <small class="text-muted"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($n['timestamp'], ENT_QUOTES, 'UTF-8') ?></small>
          </div>
          <div class="text-end">
            <?php if ($type === 1): ?>
              <span class="badge bg-info mb-1">Approve Request</span><br>
              <?php if ($n['status'] === 'Approved'): ?>
                <span class="badge bg-success">Approved</span>
              <?php elseif ($n['status'] === 'Declined'): ?>
                <span class="badge bg-danger">Declined</span>
              <?php else: ?>
                <button class="btn btn-sm btn-success me-1" onclick="approveRequest(<?= $slno ?>, 1)">
                  <i class="bi bi-check-lg"></i> Approve
                </button>
                <button class="btn btn-sm btn-danger" onclick="approveRequest(<?= $slno ?>, 0)">
                  <i class="bi bi-x-lg"></i> Decline
                </button>
              <?php endif; ?>

            <?php elseif ($type === 2): ?>
              <span class="badge bg-warning text-dark mb-1">Feedback</span><br>
              <?php if ($n['status'] !== null && $n['status'] !== ''): ?>
                <span class="badge bg-secondary"><?= (int)$n['status'] ?>/5 stars</span>
              <?php else: ?>
                <div class="d-flex align-items-center gap-1">
                  <select class="form-select form-select-sm" id="rating-<?= $slno ?>" style="width:80px">
                    <?php for ($r = 1; $r <= 5; $r++): ?>
                      <option value="<?= $r ?>"><?= $r ?></option>
                    <?php endfor; ?>
                  </select>
                  <button class="btn btn-sm btn-primary" onclick="submitRating(<?= $slno ?>)">Submit</button>
                </div>
              <?php endif; ?>

            <?php elseif ($type === 3): ?>
              <span class="badge bg-secondary mb-1">Request Status</span><br>
              <?php if ($n['status'] === 'Approved'): ?>
                <span class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>Approved — Enjoy the ride!</span>
              <?php elseif ($n['status'] === 'Declined'): ?>
                <span class="text-danger fw-semibold"><i class="bi bi-x-circle me-1"></i>Declined</span>
              <?php else: ?>
                <span class="text-muted">Pending</span>
              <?php endif; ?>

            <?php elseif ($type === 4): ?>
              <span class="badge bg-secondary mb-1">Request Status</span><br>
              <span class="text-muted small">Still pending with the rider.</span>

            <?php else: ?>
              <span class="badge bg-light text-dark">—</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php
$csrfToken = htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8');
$pageScripts = <<<JS
<script>
var csrfToken = '{$csrfToken}';

function approveRequest(slno, stat) {
  var status = stat === 1 ? 'Approved' : 'Declined';
  fetch('/notifications', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'type=1&serialNo=' + slno + '&stat=' + encodeURIComponent(status) + '&csrf_token=' + encodeURIComponent(csrfToken)
  }).then(function() { location.reload(); });
}

function submitRating(slno) {
  var rating = document.getElementById('rating-' + slno).value;
  fetch('/notifications', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'type=2&serialNo=' + slno + '&rating=' + rating + '&csrf_token=' + encodeURIComponent(csrfToken)
  }).then(function() { location.reload(); });
}
</script>
JS;
require __DIR__ . '/../layouts/footer.php';
?>
