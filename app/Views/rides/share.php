<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container">
  <?php if (isset($_GET['nerror'])): ?>
    <div class="alert alert-error">Please enter all required details before continuing.</div>
  <?php endif; ?>

  <?php require __DIR__ . '/../layouts/menu.php'; ?>

  <div class="row-fluid" id="main-content">
    <div class="span2"></div>
    <div class="span8">
      <h2 align="center"><small>Share your ride</small></h2>
      <hr><br>
      <form method="post" action="/share">
        <input type="hidden" name="csrf_token"     value="<?= htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" id="total"            name="totalRequests" value="0">

        <input type="text" id="From" name="from" class="typeahead" placeholder="Source"      required><br>
        <div class="inputs"></div>
        <input type="text" id="To"   name="to"   class="typeahead" placeholder="Destination" required><br>

        <div class="btn-group">
          <button type="button" class="btn" id="add">Add Via Route</button>
          <button type="button" class="btn" id="remove">Remove Via</button>
          <button type="button" class="btn" id="reset">Reset Via</button>
        </div>
        <br><br>

        Start Time of your ride:
        <div id="uptimepicker" class="input-append date">
          <input type="text" name="uptime" required>
          <span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span>
        </div><br>

        Mode of Travel:<br>
        <label class="radio inline"><input type="radio" name="vehicle" value="car"  required> Car</label>
        <label class="radio inline"><input type="radio" name="vehicle" value="taxi"> Taxi</label>
        <label class="radio inline"><input type="radio" name="vehicle" value="auto"> Auto Rickshaw</label>
        <br><br>

        <div class="input-append">
          <input type="text" name="time" placeholder="Approx duration of travel" required>
          <span class="add-on">Hrs</span>
        </div><br>

        <input type="number" name="number" placeholder="Number of vacancies"><br>

        <div class="input-prepend">
          <span class="add-on">Rs</span>
          <input class="span10" type="number" name="cost" placeholder="Cost per person" required>
        </div><br><br>

        <textarea rows="3" name="description" placeholder="Any further details about your ride"></textarea><br>
        <input class="btn btn-primary" type="submit" value="Share Ride">
      </form>
    </div>
    <div class="span2"></div>
  </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
<script src="/js/datetimepicker.js"></script>
<script>
  $('#uptimepicker').datetimepicker({ format: 'yyyy-MM-dd hh:mm:ss' });

  var i = 0;
  $('#add').click(function() {
    var name = 'dynamic' + (i + 1);
    $('<div><input type="text" class="field" placeholder="Via stop" name="' + name + '"></div>')
      .fadeIn('slow').appendTo('.inputs');
    i++;
    $('#total').val(i);
  });
  $('#remove').click(function() {
    if (i > 0) { $('.field:last').parent().remove(); i--; $('#total').val(i); }
  });
  $('#reset').click(function() {
    $('.inputs').empty(); i = 0; $('#total').val(0);
  });
</script>
