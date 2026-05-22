<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container">
  <?php if (isset($_GET['changed'])): ?>
    <div class="alert alert-info">Account details updated successfully!</div>
  <?php elseif (isset($_GET['nerror'])): ?>
    <div class="alert alert-error">Please fill in all required fields.</div>
  <?php endif; ?>

  <?php require __DIR__ . '/../layouts/menu.php'; ?>

  <div class="row-fluid" id="main-content">
    <div class="span1"></div>
    <div class="span5">
      <h2><small>Profile</small></h2><hr>
      <?php
        $sex = ($user['gender'] === 'M') ? 'Male' : 'Female';
      ?>
      <p>Name: <strong><?= htmlspecialchars($user['name'],        ENT_QUOTES, 'UTF-8') ?></strong></p>
      <p>Email: <strong><?= htmlspecialchars($user['email'],       ENT_QUOTES, 'UTF-8') ?></strong></p>
      <p>Gender: <strong><?= htmlspecialchars($sex,               ENT_QUOTES, 'UTF-8') ?></strong></p>
      <p>Contact: <strong><?= htmlspecialchars((string)$user['contactno'], ENT_QUOTES, 'UTF-8') ?></strong></p>
      <p>Description: <strong><?= htmlspecialchars($user['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></p>
      <p>Carbon Credits: <strong><?= (int)$user['credits'] ?></strong></p>
      <p>Badge: <span class="label label-success"><?= htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') ?></span></p>

      <hr>
      <h3><small>Update Details</small></h3>
      <form method="post" action="/profile">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="text"   name="name"      class="input-block-level" value="<?= htmlspecialchars($user['name'],        ENT_QUOTES, 'UTF-8') ?>" required><br>
        <input type="number" name="contactno" class="input-block-level" value="<?= htmlspecialchars((string)$user['contactno'], ENT_QUOTES, 'UTF-8') ?>" required><br>
        <label>Gender:</label><br>
        <label class="radio inline"><input type="radio" name="sex" value="male"   <?= $user['gender'] === 'M' ? 'checked' : '' ?>> Male</label>
        <label class="radio inline"><input type="radio" name="sex" value="female" <?= $user['gender'] === 'F' ? 'checked' : '' ?>> Female</label><br><br>
        <textarea name="description" class="input-block-level" rows="3"><?= htmlspecialchars($user['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea><br>
        <button type="submit" class="btn btn-primary">Update</button>
      </form>
    </div>

    <div class="span5">
      <h2><small>My Rides</small></h2><hr>
      <?php if (empty($rides)): ?>
        <p>You haven't shared any rides yet.</p>
      <?php else: ?>
        <table class="table table-hover">
          <thead><tr><th>From</th><th>To</th><th>Time</th><th>Vehicle</th></tr></thead>
          <tbody>
            <?php foreach ($rides as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['from'],    ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['to'],      ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['uptime'],  ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['vehicle'], ENT_QUOTES, 'UTF-8') ?></td>
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
