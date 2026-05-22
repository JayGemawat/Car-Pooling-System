<?php require __DIR__ . '/../layouts/header.php'; ?>

<?php if (isset($_GET['nerror'])): ?>
  <div class="alert alert-danger">Please enter all required details before continuing.</div>
<?php endif; ?>

<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-lg-6">
    <div class="card p-4">
      <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle me-2"></i>Share your ride</h5>
      <form method="post" action="/share">
        <input type="hidden" name="csrf_token"      value="<?= htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" id="total"             name="totalRequests" value="0">

        <div class="mb-3 position-relative">
          <label class="form-label">From</label>
          <input type="text" id="From" name="from" class="form-control" placeholder="Source" autocomplete="off" required>
        </div>

        <div class="via-inputs mb-2"></div>

        <div class="mb-3 position-relative">
          <label class="form-label">To</label>
          <input type="text" id="To" name="to" class="form-control" placeholder="Destination" autocomplete="off" required>
        </div>

        <div class="d-flex gap-2 mb-3">
          <button type="button" class="btn btn-outline-secondary btn-sm" id="add"><i class="bi bi-plus me-1"></i>Add Via</button>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="remove"><i class="bi bi-dash me-1"></i>Remove Via</button>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="reset"><i class="bi bi-x me-1"></i>Reset Via</button>
        </div>

        <div class="mb-3">
          <label class="form-label">Start time of your ride</label>
          <input type="text" id="uptimepicker" name="uptime" class="form-control" placeholder="yyyy-MM-dd HH:mm:ss" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Mode of travel</label><br>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="vehicle" id="vCar"  value="car" required>
            <label class="form-check-label" for="vCar"><i class="bi bi-car-front me-1"></i>Car</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="vehicle" id="vTaxi" value="taxi">
            <label class="form-check-label" for="vTaxi"><i class="bi bi-taxi-front me-1"></i>Taxi</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="vehicle" id="vAuto" value="auto">
            <label class="form-check-label" for="vAuto">Auto Rickshaw</label>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Approx duration (hours)</label>
          <input type="text" name="time" class="form-control" placeholder="e.g. 1.5" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Number of vacancies</label>
          <input type="number" name="number" class="form-control" placeholder="Seats available" min="1">
        </div>

        <div class="mb-3">
          <label class="form-label">Cost per person (Rs)</label>
          <div class="input-group">
            <span class="input-group-text">Rs</span>
            <input type="number" name="cost" class="form-control" placeholder="0" min="0" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea rows="3" name="description" class="form-control" placeholder="Any further details about your ride"></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-share me-1"></i>Share Ride</button>
      </form>
    </div>
  </div>
</div>

<?php
$pageScripts = <<<'JS'
<script src="/js/datetimepicker.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof $ !== 'undefined' && $.fn.datetimepicker) {
    $('#uptimepicker').datetimepicker({ format: 'yyyy-MM-dd hh:mm:ss' });
  }
  JaanaHaiMap.autocomplete('From');
  JaanaHaiMap.autocomplete('To');

  var i = 0;
  document.getElementById('add').addEventListener('click', function() {
    i++;
    var name = 'dynamic' + i;
    var div  = document.createElement('div');
    div.className = 'mb-2 position-relative';
    div.innerHTML = '<input type="text" class="form-control via-field" placeholder="Via stop" name="' + name + '">';
    document.querySelector('.via-inputs').appendChild(div);
    document.getElementById('total').value = i;
    var inp = div.querySelector('input');
    if (inp) JaanaHaiMap.autocomplete(inp.id || (inp.id = 'via-' + i));
  });
  document.getElementById('remove').addEventListener('click', function() {
    var fields = document.querySelectorAll('.via-inputs .mb-2');
    if (fields.length > 0) { fields[fields.length - 1].remove(); i--; document.getElementById('total').value = i; }
  });
  document.getElementById('reset').addEventListener('click', function() {
    document.querySelector('.via-inputs').innerHTML = '';
    i = 0; document.getElementById('total').value = 0;
  });
});
</script>
JS;
require __DIR__ . '/../layouts/footer.php';
?>
