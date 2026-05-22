<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container">
  <?php if (isset($_GET['share'])): ?>
    <div class="alert alert-info">Your ride was successfully added! You can edit it from your profile.</div>
  <?php elseif (isset($_GET['success'])): ?>
    <div class="alert alert-success">Your request has been sent to the rider for approval. You may expect a call soon!</div>
  <?php elseif (isset($_GET['nerror'])): ?>
    <div class="alert alert-error">Please enter all the details before continuing.</div>
  <?php endif; ?>

  <?php require __DIR__ . '/../layouts/menu.php'; ?>

  <div class="row-fluid" id="main-content">
    <div class="span1"></div>
    <div class="span5">
      <h2 align="center"><small>Search for a ride</small></h2>
      <hr><br>
      <form action="/search" method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="text" id="from-input" name="from" class="typeahead" placeholder="Source" autocomplete="off" required><br>
        <input type="text" id="to-input"   name="to"   class="typeahead" placeholder="Destination" autocomplete="off" required><br>
        Time Range:<br>
        Start Time:
        <div id="uptimepicker" class="input-append date">
          <input type="text" name="uptime">
          <span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span>
        </div><br>
        End Time:
        <div id="downtimepicker" class="input-append date">
          <input type="text" name="downtime">
          <span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span>
        </div><br><br>
        <input class="btn" type="submit" value="Search">
      </form>
    </div>

    <div class="span5">
      <h2 align="center"><small>Latest Car Pools</small></h2>
      <?php if (empty($rides)): ?>
        <p align="center">No upcoming car pools are scheduled currently :(</p>
      <?php else: ?>
        <table id="upcomingList" class="table table-hover">
          <thead>
            <tr><th>Id</th><th>Vehicle</th><th>From</th><th>To</th><th>Starting Time</th></tr>
          </thead>
          <tbody>
            <?php foreach ($rides as $r): ?>
              <tr>
                <td><?= htmlspecialchars((string)$r['id'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['vehicle'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['from'],    ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['to'],      ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['uptime'],  ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
    <div class="span1"></div>
  </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
<script src="/js/datetimepicker.js"></script>
<script>
  $('#uptimepicker').datetimepicker({ format: 'yyyy-MM-dd hh:mm:ss' });
  $('#downtimepicker').datetimepicker({ format: 'yyyy-MM-dd hh:mm:ss' });
  JaanaHaiMap.autocomplete('from-input');
  JaanaHaiMap.autocomplete('to-input');
  $('td:nth-child(1),th:nth-child(1)').hide();
  $('#upcomingList').find('tr').click(function() {
    var row = $(this).find('td:first').text();
    window.location.href = '/ride/' + row;
  });
</script>
