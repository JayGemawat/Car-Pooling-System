<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container">
  <?php require __DIR__ . '/../layouts/menu.php'; ?>

  <div class="row-fluid" id="main-content">
    <div class="span1"></div>
    <div class="span10">
      <h2><small>Notifications</small></h2><hr>
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Time</th>
            <th>Car Pool</th>
            <th>Type</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($notifications as $n): ?>
            <?php
              $type  = (int) $n['type'];
              $slno  = (int) $n['slno'];
              $offer = $offerCache[(int)$n['cid']] ?? null;
              $route = $offer
                ? htmlspecialchars($offer['from'], ENT_QUOTES, 'UTF-8') . ' → ' . htmlspecialchars($offer['to'], ENT_QUOTES, 'UTF-8')
                : 'Unknown route';
            ?>
            <tr>
              <td><?= htmlspecialchars($n['timestamp'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= $route ?></td>

              <?php if ($type === 1): ?>
                <td>Approve Request</td>
                <td>
                  <?php if ($n['status'] === 'Approved'): ?>
                    <button class="btn" disabled>Approved</button>
                  <?php elseif ($n['status'] === 'Declined'): ?>
                    <button class="btn" disabled>Declined</button>
                  <?php else: ?>
                    <button class="btn" onclick="ApproveRequest(<?= $slno ?>, 1)">Approve</button>
                    <button class="btn" onclick="ApproveRequest(<?= $slno ?>, 0)">Decline</button>
                  <?php endif; ?>
                </td>

              <?php elseif ($type === 2): ?>
                <td>Feedback</td>
                <td>
                  <?php if ($n['status'] !== null && $n['status'] !== ''): ?>
                    <?= (int)$n['status'] ?>/5
                  <?php else: ?>
                    <div class="btn-group">
                      <button id="ratingBtn<?= $slno ?>" class="btn dropdown-toggle" data-toggle="dropdown">
                        Rating <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu">
                        <?php for ($r = 1; $r <= 5; $r++): ?>
                          <li><a href="#" onclick="setRating(<?= $slno ?>, <?= $r ?>)"><?= $r ?></a></li>
                        <?php endfor; ?>
                      </ul>
                    </div>
                    <button class="btn" onclick="RateRequest(<?= $slno ?>)">Submit</button>
                  <?php endif; ?>
                </td>

              <?php elseif ($type === 3): ?>
                <td>Request Status</td>
                <td>
                  <?php if ($n['status'] === 'Approved'): ?>
                    Approved — Enjoy the ride!
                  <?php elseif ($n['status'] === 'Declined'): ?>
                    Declined :-(
                  <?php else: ?>
                    Pending
                  <?php endif; ?>
                </td>

              <?php elseif ($type === 4): ?>
                <td>Request Status</td>
                <td>Still pending with the rider — please check back later.</td>

              <?php else: ?>
                <td>—</td><td>—</td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($notifications)): ?>
            <tr><td colspan="4" align="center">No notifications yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="span1"></div>
  </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
<script>
  var ratingValues = {};

  function setRating(slno, val) {
    ratingValues[slno] = val;
    $('#ratingBtn' + slno).html(val + '&nbsp;<span class="caret"></span>');
  }

  function RateRequest(slno) {
    var rating = ratingValues[slno] || 0;
    $.post('/notifications', { type: 2, serialNo: slno, rating: rating, csrf_token: '<?= htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8') ?>' })
      .done(function() { location.reload(); });
  }

  function ApproveRequest(slno, stat) {
    var status = stat === 1 ? 'Approved' : 'Declined';
    $.post('/notifications', { type: 1, serialNo: slno, stat: status, csrf_token: '<?= htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8') ?>' })
      .done(function() { location.reload(); });
  }

  $('.dropdown-toggle').dropdown();
</script>
