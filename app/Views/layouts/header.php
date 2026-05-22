<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>JaanaHai — Carpool</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
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
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="/"><i class="bi bi-house"></i> Home</a></li>
        <li class="nav-item"><a class="nav-link" href="/share"><i class="bi bi-plus-circle"></i> Share Ride</a></li>
        <li class="nav-item"><a class="nav-link" href="/search"><i class="bi bi-search"></i> Find Ride</a></li>
        <li class="nav-item"><a class="nav-link" href="/notifications"><i class="bi bi-bell"></i> Notifications</a></li>
        <li class="nav-item"><a class="nav-link" href="/profile"><i class="bi bi-person"></i> Profile</a></li>
        <li class="nav-item"><a class="nav-link text-warning" href="/logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container my-4 flex-grow-1">
