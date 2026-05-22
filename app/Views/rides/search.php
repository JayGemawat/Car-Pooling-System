<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container">
  <?php require __DIR__ . '/../layouts/menu.php'; ?>

  <div class="row-fluid" id="main-content">
    <div class="span1"></div>
    <div class="span5">
      <h2 align="center"><small>Search for a preferred ride</small></h2>
      <hr><br>
      <form method="post" action="/search">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="text" name="from"     class="typeahead" placeholder="Source"      value="<?= htmlspecialchars($_POST['from']     ?? '', ENT_QUOTES, 'UTF-8') ?>" required><br>
        <input type="text" name="to"       class="typeahead" placeholder="Destination" value="<?= htmlspecialchars($_POST['to']       ?? '', ENT_QUOTES, 'UTF-8') ?>" required><br>
        Time Range for your ride:<br>
        Start Time:
        <div id="uptimepicker" class="input-append date">
          <input type="text" name="uptime"   value="<?= htmlspecialchars($_POST['uptime']   ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span>
        </div><br>
        End Time:
        <div id="downtimepicker" class="input-append date">
          <input type="text" name="downtime" value="<?= htmlspecialchars($_POST['downtime'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span>
        </div><br><br>
        <input class="btn" type="submit" value="Search">
      </form>
    </div>

    <div class="span5">
      <h2 align="center"><small>Search Results</small></h2><hr>
      <?php if (!empty($_POST) && empty($results)): ?>
        <p align="center">No upcoming car pools match your request :(</p>
      <?php elseif (!empty($results)): ?>
        <table id="upcominglist" class="table table-hover">
          <thead>
            <tr><th>Id</th><th>Vehicle</th><th>From</th><th>To</th><th>Starting Time</th><th>Type</th></tr>
          </thead>
          <tbody>
            <?php foreach ($results as $r): ?>
              <?php
                $direct = ($r['from'] === ($_POST['from'] ?? '') && $r['to'] === ($_POST['to'] ?? ''));
              ?>
              <tr>
                <td><?= htmlspecialchars((string)$r['id'],      ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['vehicle'],          ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['from'],             ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['to'],               ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['uptime'],           ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= $direct ? 'Direct' : 'Via' ?></td>
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
  $('td:nth-child(1),th:nth-child(1)').hide();
  $('#upcominglist').find('tr').click(function() {
    var row = $(this).find('td:first').text();
    window.location.href = '/ride/' + row;
  });
</script>
