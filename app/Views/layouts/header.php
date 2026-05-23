<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>JaanaHai — Carpool</title>
  <?php if (session_status() !== PHP_SESSION_NONE || !empty($_SESSION['user_id'])) : ?>
  <meta name="csrf-token" content="<?= htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
  <?php endif; ?>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <!-- Datetimepicker CSS (must load before Bootstrap so Bootstrap overrides cleanly) -->
  <link href="/css/datetimepicker.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="/css/app.css" rel="stylesheet">

  <!-- PWA -->
  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#0d6efd">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="JaanaHai">

  <script>
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sw.js').catch(console.warn);
    }
  </script>
  <script>
    async function subscribePush() {
      if (!('PushManager' in window)) return;
      const reg = await navigator.serviceWorker.ready;
      try {
        const sub = await reg.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: '<?= htmlspecialchars($_ENV['VAPID_PUBLIC_KEY'] ?? '', ENT_QUOTES, 'UTF-8') ?>'
        });
        const csrf = document.querySelector('[name=csrf_token]')?.value || '';
        fetch('/push/subscribe', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(Object.assign(sub.toJSON(), { csrf_token: csrf }))
        });
      } catch (e) { console.warn('Push subscription failed', e); }
    }
    window.addEventListener('load', () => {
      if (Notification.permission === 'default') {
        Notification.requestPermission().then(p => { if (p === 'granted') subscribePush(); });
      } else if (Notification.permission === 'granted') {
        subscribePush();
      }
    });
  </script>
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/">
      <img src="/img/logo.jpg" width="32" height="32" class="rounded me-2" alt="">
      JaanaHai
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="/"><i class="bi bi-house"></i> Home</a></li>
        <li class="nav-item"><a class="nav-link" href="/share"><i class="bi bi-plus-circle"></i> Share Ride</a></li>
        <li class="nav-item"><a class="nav-link" href="/search"><i class="bi bi-search"></i> Find Ride</a></li>

        <!-- Notification Bell -->
        <li class="nav-item dropdown" id="notif-dropdown-li">
          <a class="nav-link position-relative" href="#" id="notifBell" role="button"
             data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
            <i class="bi bi-bell fs-5"></i>
            <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
              0
            </span>
          </a>
          <div class="dropdown-menu dropdown-menu-end notif-panel p-0" id="notifPanel" style="min-width:340px;max-width:400px;">
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
              <span class="fw-semibold">Notifications</span>
              <a href="/notifications" class="small text-primary text-decoration-none">View all</a>
            </div>
            <div id="notif-list" style="max-height:360px;overflow-y:auto;">
              <div class="text-center text-muted py-4 small" id="notif-empty">Loading…</div>
            </div>
          </div>
        </li>

        <li class="nav-item"><a class="nav-link" href="/profile"><i class="bi bi-person"></i> Profile</a></li>
        <li class="nav-item"><a class="nav-link text-warning" href="/logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Notification Bell JS -->
<script>
(function() {

  function getCsrf() {
    // Read from meta tag (always present) or fall back to any form field
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.content;
    var t = document.querySelector('[name=csrf_token]');
    return t ? t.value : '';
  }

  function updateBadge(count) {
    var badge = document.getElementById('notif-badge');
    var bell  = document.getElementById('notifBell');
    if (!badge) return;
    if (count > 0) {
      badge.textContent = count > 99 ? '99+' : count;
      badge.classList.remove('d-none');
      if (bell) bell.classList.add('has-unseen');
    } else {
      badge.classList.add('d-none');
      if (bell) bell.classList.remove('has-unseen');
    }
  }

  function pollCount() {
    fetch('/notifications/count', { credentials: 'same-origin' })
      .then(function(r) { return r.json(); })
      .then(function(d) { updateBadge(d.count || 0); })
      .catch(function() {});
  }

  function typeLabel(type, status) {
    if (type == 1) {
      if (status === 'Approved') return '<span class="badge bg-success">Approved</span>';
      if (status === 'Declined') return '<span class="badge bg-danger">Declined</span>';
      return '<span class="badge bg-info text-dark">Ride Request</span>';
    }
    if (type == 2) return '<span class="badge bg-warning text-dark">Feedback</span>';
    if (type == 3) {
      if (status === 'Approved') return '<span class="badge bg-success">Your request approved</span>';
      if (status === 'Declined') return '<span class="badge bg-danger">Your request declined</span>';
      return '<span class="badge bg-secondary">Status update</span>';
    }
    if (type == 4) return '<span class="badge bg-secondary">Pending with rider</span>';
    return '<span class="badge bg-light text-dark">—</span>';
  }

  function loadNotifications() {
    var list = document.getElementById('notif-list');
    var empty = document.getElementById('notif-empty');
    if (!list) return;

    fetch('/notifications/recent', { credentials: 'same-origin' })
      .then(function(r) { return r.json(); })
      .then(function(items) {
        updateBadge(0); // marked as seen by the server
        list.innerHTML = '';

        if (!items || items.length === 0) {
          list.innerHTML = '<div class="text-center text-muted py-4 small"><i class="bi bi-bell-slash d-block fs-3 mb-1"></i>No notifications yet.</div>';
          return;
        }

        items.forEach(function(n) {
          var seen = n.seen == true || n.seen === 't' || n.seen === '1';
          var item = document.createElement('div');
          item.className = 'notif-item d-flex align-items-start gap-2 px-3 py-2 border-bottom' + (seen ? '' : ' notif-unseen');
          item.innerHTML =
            '<div class="flex-grow-1">' +
              '<div class="small fw-semibold">' + escHtml(n.route || '') + '</div>' +
              '<div class="mt-1">' + typeLabel(n.type, n.status) + '</div>' +
              '<div class="text-muted" style="font-size:11px">' + escHtml(n.timestamp || '') + '</div>' +
            '</div>' +
            '<button class="btn btn-link btn-sm text-muted p-0 notif-del-btn" data-slno="' + n.slno + '" title="Dismiss" aria-label="Dismiss notification">' +
              '<i class="bi bi-x-lg"></i>' +
            '</button>';
          list.appendChild(item);
        });

        // Bind delete buttons
        list.querySelectorAll('.notif-del-btn').forEach(function(btn) {
          btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var slno = this.dataset.slno;
            var csrf = getCsrf();
            fetch('/notifications/delete', {
              method: 'POST',
              credentials: 'same-origin',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: 'slno=' + encodeURIComponent(slno) + '&csrf_token=' + encodeURIComponent(csrf)
            }).then(function() {
              var parent = btn.closest('.notif-item');
              if (parent) parent.remove();
              if (!list.querySelector('.notif-item')) {
                list.innerHTML = '<div class="text-center text-muted py-4 small"><i class="bi bi-bell-slash d-block fs-3 mb-1"></i>No notifications yet.</div>';
              }
            });
          });
        });
      })
      .catch(function() {
        list.innerHTML = '<div class="text-center text-muted py-4 small">Could not load notifications.</div>';
      });
  }

  function escHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // Poll count every 15 seconds
  pollCount();
  setInterval(pollCount, 15000);

  // Load notifications when bell is clicked
  document.addEventListener('DOMContentLoaded', function() {
    var bell = document.getElementById('notifBell');
    if (bell) {
      bell.addEventListener('click', function() {
        loadNotifications();
      });
    }
  });
})();
</script>

<div class="container my-4 flex-grow-1">
