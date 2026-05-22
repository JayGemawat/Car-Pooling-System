<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container">
  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">Incorrect email or password.</div>
  <?php elseif (isset($_GET['nerror'])): ?>
    <div class="alert alert-error">Please fill in all fields.</div>
  <?php elseif (isset($_GET['registered'])): ?>
    <div class="alert alert-success">Registration successful! You can now log in.</div>
  <?php elseif (isset($_GET['logout'])): ?>
    <div class="alert alert-info">You have been logged out.</div>
  <?php endif; ?>

  <div class="row-fluid">
    <div class="span4"></div>
    <div class="span4">
      <h2><small>Login</small></h2>
      <hr>
      <form method="post" action="/login">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="email"    name="email"    class="input-block-level" placeholder="Email address" required><br>
        <input type="password" name="password" class="input-block-level" placeholder="Password" required><br>
        <button type="submit" class="btn btn-primary">Login</button>
        <a href="/register" class="btn">Register</a>
      </form>
    </div>
    <div class="span4"></div>
  </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
