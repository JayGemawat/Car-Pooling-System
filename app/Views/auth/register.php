<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container">
  <?php if (isset($_GET['nerror'])): ?>
    <div class="alert alert-error">Please fill in all required fields with valid data.</div>
  <?php elseif (isset($_GET['exists'])): ?>
    <div class="alert alert-error">An account with that email already exists.</div>
  <?php endif; ?>

  <div class="row-fluid">
    <div class="span4"></div>
    <div class="span4">
      <h2><small>Register</small></h2>
      <hr>
      <form method="post" action="/register">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="text"     name="name"        class="input-block-level" placeholder="Full name" required><br>
        <input type="email"    name="email"        class="input-block-level" placeholder="Email address" required><br>
        <input type="password" name="password"     class="input-block-level" placeholder="Password" required><br>
        <input type="number"   name="contactno"    class="input-block-level" placeholder="Contact number" required><br>
        <label>Gender:</label><br>
        <label class="radio inline"><input type="radio" name="sex" value="male"   required> Male</label>
        <label class="radio inline"><input type="radio" name="sex" value="female"> Female</label><br><br>
        <textarea name="description" class="input-block-level" rows="3" placeholder="Brief description about yourself"></textarea><br>
        <button type="submit" class="btn btn-primary">Register</button>
        <a href="/login" class="btn">Back to Login</a>
      </form>
    </div>
    <div class="span4"></div>
  </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
