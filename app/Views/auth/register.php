<?php require __DIR__ . '/../layouts/header.php'; ?>

<?php if (isset($_GET['nerror'])): ?>
  <div class="alert alert-danger">Please fill in all required fields with valid data.</div>
<?php elseif (isset($_GET['exists'])): ?>
  <div class="alert alert-danger">An account with that email already exists.</div>
<?php endif; ?>

<div class="row justify-content-center">
  <div class="col-12 col-sm-9 col-md-6 col-lg-5">
    <div class="card p-4">
      <h4 class="mb-3 fw-bold">Create account</h4>
      <form method="post" action="/register">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="mb-3">
          <label class="form-label">Full name</label>
          <input type="text" name="name" class="form-control" placeholder="Full name" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Contact number</label>
          <input type="number" name="contactno" class="form-control" placeholder="Contact number" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Gender</label><br>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="sex" id="genderMale" value="male" required>
            <label class="form-check-label" for="genderMale">Male</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="sex" id="genderFemale" value="female">
            <label class="form-check-label" for="genderFemale">Female</label>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">About you</label>
          <textarea name="description" class="form-control" rows="3" placeholder="Brief description about yourself"></textarea>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
        <div class="text-center mt-3">
          <a href="/login">Already have an account? Login</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
