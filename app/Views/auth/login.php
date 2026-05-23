<?php require __DIR__ . '/../layouts/header.php'; ?>

<?php if (isset($_GET['error'])) : ?>
  <div class="alert alert-danger">Incorrect email or password.</div>
<?php elseif (isset($_GET['nerror'])) : ?>
  <div class="alert alert-warning">Please fill in all fields.</div>
<?php elseif (isset($_GET['registered'])) : ?>
  <div class="alert alert-success">Registered! You can now log in.</div>
<?php elseif (isset($_GET['logout'])) : ?>
  <div class="alert alert-info">You have been logged out.</div>
<?php endif; ?>

<div class="row justify-content-center">
  <div class="col-12 col-sm-8 col-md-5 col-lg-4">
    <div class="card p-4">
      <h4 class="mb-3 fw-bold">Sign in</h4>
      <form method="post" action="/login">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
        <div class="text-center mt-3">
          <a href="/register">Don't have an account? Register</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
