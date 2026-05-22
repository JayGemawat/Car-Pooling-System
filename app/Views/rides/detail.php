<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container">
  <?php require __DIR__ . '/../layouts/menu.php'; ?>

  <div class="row-fluid" id="main-content">
    <div class="span2"></div>
    <div class="span8">
      <h2 align="center"><small>Ride Information</small></h2>
      <hr><br>

      <?php
        $now    = date('Y-m-d H:i:s');
        $active = $ride['uptime'] > $now;
      ?>

      <?php $status = $ride['status'] ?? 'open'; include __DIR__ . '/../partials/ride-status.php'; ?>

      <!-- Route map -->
      <div id="route-info" style="padding:8px 0;font-size:14px;color:#555;margin-bottom:8px"></div>
      <div id="ride-map" style="height:320px;border-radius:8px;overflow:hidden;margin-bottom:16px"></div>

      <p>Rider: <strong><a href="/profile?id=<?= (int)$ride['uid'] ?>"><?= htmlspecialchars($rider['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></a></strong></p>
      <p>Starting Time: <strong><?= htmlspecialchars($ride['uptime'],      ENT_QUOTES, 'UTF-8') ?></strong></p>
      <p>From: <strong><?= htmlspecialchars($ride['from'],       ENT_QUOTES, 'UTF-8') ?></strong></p>
      <p>To: <strong><?= htmlspecialchars($ride['to'],         ENT_QUOTES, 'UTF-8') ?></strong></p>
      <p>Available Vacancies: <strong><?= (int)$ride['people'] ?></strong></p>
      <p>Price per person: <strong>INR <?= (int)$ride['price'] ?></strong></p>
      <p>Vehicle: <strong><?= htmlspecialchars($ride['vehicle'],    ENT_QUOTES, 'UTF-8') ?></strong></p>
      <p>Description: <strong><?= htmlspecialchars($ride['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></p>

      <?php if ($active && (int)$ride['uid'] !== AuthMiddleware::userId()): ?>
        <h3><small>Request this ride</small></h3>
        <form method="post" action="/ride/request">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="cid" value="<?= (int)$ride['id'] ?>">

          <?php if (!empty($waypoints)): ?>
            <div class="btn-group">
              <button id="from-btn" class="btn dropdown-toggle" data-toggle="dropdown">From <span class="caret"></span></button>
              <ul class="dropdown-menu from-menu">
                <?php foreach ($waypoints as $wp): ?>
                  <li><a href="#"><?= htmlspecialchars($wp, ENT_QUOTES, 'UTF-8') ?></a></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <div class="btn-group">
              <button id="to-btn" class="btn dropdown-toggle" data-toggle="dropdown">To <span class="caret"></span></button>
              <ul class="dropdown-menu to-menu">
                <?php foreach ($waypoints as $wp): ?>
                  <li><a href="#"><?= htmlspecialchars($wp, ENT_QUOTES, 'UTF-8') ?></a></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <input type="hidden" id="formfrom" name="from">
            <input type="hidden" id="formto"   name="to">
            <br><br>
          <?php endif; ?>

          <input class="btn btn-primary" type="submit" value="Request Ride">
        </form>
      <?php elseif (!$active): ?>
        <p class="muted">This carpool has been archived.</p>
      <?php endif; ?>
    </div>
    <div class="span2"></div>
  </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
<script>
  $('.from-menu li a').click(function() {
    $('#from-btn').html($(this).text() + '&nbsp;<span class="caret"></span>');
    $('#formfrom').val($(this).text());
  });
  $('.to-menu li a').click(function() {
    $('#to-btn').html($(this).text() + '&nbsp;<span class="caret"></span>');
    $('#formto').val($(this).text());
  });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = JaanaHaiMap.init('ride-map');
    JaanaHaiMap.drawRoute(map, '<?= htmlspecialchars($ride['from'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($ride['to'], ENT_QUOTES, 'UTF-8') ?>');
});
</script>
