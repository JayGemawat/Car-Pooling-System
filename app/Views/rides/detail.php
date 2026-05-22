<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-lg-7">

    <?php
      $now    = date('Y-m-d H:i:s');
      $active = $ride['uptime'] > $now;
      $status = $ride['status'] ?? 'open';
    ?>

    <!-- Status timeline -->
    <?php include __DIR__ . '/../partials/ride-status.php'; ?>

    <!-- Route info bar -->
    <div id="route-info" class="text-muted small mb-2"></div>

    <!-- Map -->
    <div id="ride-map" class="mb-4"></div>

    <!-- Ride details card -->
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title fw-bold">Ride Information</h5>
        <hr>
        <p><i class="bi bi-person me-2"></i>Rider:
          <strong><a href="/profile?id=<?= (int)$ride['uid'] ?>"><?= htmlspecialchars($rider['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></a></strong>
        </p>
        <p><i class="bi bi-clock me-2"></i>Starting Time:
          <strong><?= htmlspecialchars($ride['uptime'], ENT_QUOTES, 'UTF-8') ?></strong>
        </p>
        <p><i class="bi bi-geo-alt me-2"></i>From:
          <strong><?= htmlspecialchars($ride['from'], ENT_QUOTES, 'UTF-8') ?></strong>
        </p>
        <p><i class="bi bi-geo me-2"></i>To:
          <strong><?= htmlspecialchars($ride['to'], ENT_QUOTES, 'UTF-8') ?></strong>
        </p>
        <p><i class="bi bi-people me-2"></i>Available Vacancies:
          <strong><span id="seats-left"><?= (int)$ride['people'] ?></span></strong>
        </p>
        <p><i class="bi bi-currency-rupee me-2"></i>Price per person:
          <strong>Rs <?= (int)$ride['price'] ?></strong>
        </p>
        <p><i class="bi bi-car-front me-2"></i>Vehicle:
          <strong><?= htmlspecialchars($ride['vehicle'], ENT_QUOTES, 'UTF-8') ?></strong>
        </p>
        <?php if (!empty($ride['description'])): ?>
          <p><i class="bi bi-info-circle me-2"></i>Description:
            <strong><?= htmlspecialchars($ride['description'], ENT_QUOTES, 'UTF-8') ?></strong>
          </p>
        <?php endif; ?>
        <p><i class="bi bi-flag me-2"></i>Status:
          <span id="ride-status-badge" class="badge bg-primary"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span>
        </p>
      </div>
    </div>

    <!-- Request form -->
    <?php if ($active && (int)$ride['uid'] !== AuthMiddleware::userId()): ?>
      <div class="card mb-4">
        <div class="card-body">
          <h5 class="card-title fw-bold">Request this ride</h5>
          <form method="post" action="/ride/request">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="cid" value="<?= (int)$ride['id'] ?>">

            <?php if (!empty($waypoints)): ?>
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label class="form-label">From stop</label>
                  <select class="form-select" onchange="document.getElementById('formfrom').value=this.value">
                    <option value="">Select stop</option>
                    <?php foreach ($waypoints as $wp): ?>
                      <option value="<?= htmlspecialchars($wp, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($wp, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                  </select>
                  <input type="hidden" id="formfrom" name="from">
                </div>
                <div class="col-6">
                  <label class="form-label">To stop</label>
                  <select class="form-select" onchange="document.getElementById('formto').value=this.value">
                    <option value="">Select stop</option>
                    <?php foreach ($waypoints as $wp): ?>
                      <option value="<?= htmlspecialchars($wp, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($wp, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                  </select>
                  <input type="hidden" id="formto" name="to">
                </div>
              </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary w-100">
              <i class="bi bi-hand-thumbs-up me-1"></i>Request Ride
            </button>
          </form>

          <?php if ($status === 'requested'): ?>
            <div id="pay-section" class="mt-3">
              <button id="pay-btn" class="btn btn-success w-100"
                      data-cid="<?= (int)$ride['id'] ?>"
                      data-amount="<?= (int)$ride['price'] ?>">
                <i class="bi bi-credit-card me-1"></i>Pay Rs <?= (int)$ride['price'] ?> &amp; Confirm
              </button>
            </div>
            <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
          <?php endif; ?>
        </div>
      </div>
    <?php elseif (!$active): ?>
      <div class="alert alert-secondary">This carpool has been archived.</div>
    <?php endif; ?>

  </div>
</div>

<?php
$rideFrom = json_encode($ride['from']);
$rideTo   = json_encode($ride['to']);
$rideId   = (int)$ride['id'];
$pageScripts = <<<SCRIPTS
<script>
document.addEventListener("DOMContentLoaded", function() {
    const map = JaanaHaiMap.init("ride-map");
    JaanaHaiMap.drawRoute(map, {$rideFrom}, {$rideTo});

    function pollRideStatus() {
        fetch("/sse.php?ride_id={$rideId}")
            .then(r => r.text())
            .then(text => {
                const match = text.match(/data: (.+)/);
                if (!match) return;
                const d = JSON.parse(match[1]);
                const statusEl = document.getElementById("ride-status-badge");
                if (statusEl) statusEl.textContent = d.status;
                const seatsEl = document.getElementById("seats-left");
                if (seatsEl) seatsEl.textContent = d.people;
            })
            .catch(() => {});
    }
    setInterval(pollRideStatus, 8000);

    const payBtn = document.getElementById("pay-btn");
    if (payBtn) {
        payBtn.addEventListener("click", async function() {
            const cid  = this.dataset.cid;
            const csrf = document.querySelector("[name=csrf_token]") ? document.querySelector("[name=csrf_token]").value : "";
            const res  = await fetch("/payment/create", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "cid=" + cid + "&csrf_token=" + encodeURIComponent(csrf)
            });
            const order = await res.json();
            if (order.error) { alert(order.error); return; }
            const options = {
                key:      order.key_id,
                amount:   order.amount,
                currency: order.currency,
                order_id: order.order_id,
                name:     "JaanaHai",
                description: "Ride payment",
                handler: async function(response) {
                    const vRes = await fetch("/payment/verify", {
                        method: "POST",
                        headers: {"Content-Type": "application/x-www-form-urlencoded"},
                        body: "razorpay_payment_id=" + response.razorpay_payment_id +
                              "&razorpay_order_id="  + response.razorpay_order_id +
                              "&razorpay_signature=" + response.razorpay_signature +
                              "&cid=" + cid + "&csrf_token=" + encodeURIComponent(csrf)
                    });
                    const v = await vRes.json();
                    if (v.success) { location.reload(); }
                },
                theme: { color: "#0d6efd" }
            };
            new Razorpay(options).open();
        });
    }
});
</script>
SCRIPTS;
require __DIR__ . '/../layouts/footer.php';
?>
