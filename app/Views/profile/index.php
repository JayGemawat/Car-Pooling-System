<?php require __DIR__ . '/../layouts/header.php'; ?>

<?php if ($isOwnProfile && isset($_GET['changed'])) : ?>
  <div class="alert alert-success">Account details updated successfully!</div>
<?php elseif ($isOwnProfile && isset($_GET['nerror'])) : ?>
  <div class="alert alert-danger">Please fill in all required fields.</div>
<?php endif; ?>

<div class="row g-4">
  <!-- Profile info -->
  <div class="col-12 col-lg-5">
    <div class="card p-4 mb-4">
      <h5 class="fw-bold mb-3">
        <i class="bi bi-person-circle me-2"></i>
        <?= $isOwnProfile ? 'My Profile' : htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') . "'s Profile" ?>
      </h5>
      <?php $sex = ($user['gender'] === 'M') ? 'Male' : 'Female'; ?>
      <p><i class="bi bi-person me-2"></i>Name: <strong><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></strong></p>
      <?php if ($isOwnProfile) : ?>
        <p><i class="bi bi-envelope me-2"></i>Email: <strong><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></strong></p>
        <p><i class="bi bi-telephone me-2"></i>Contact: <strong><?= htmlspecialchars((string) $user['contactno'], ENT_QUOTES, 'UTF-8') ?></strong></p>
      <?php endif; ?>
      <p><i class="bi bi-gender-ambiguous me-2"></i>Gender: <strong><?= htmlspecialchars($sex, ENT_QUOTES, 'UTF-8') ?></strong></p>
      <?php if (!empty($user['description'])) : ?>
        <p><i class="bi bi-chat-text me-2"></i>About: <strong><?= htmlspecialchars($user['description'], ENT_QUOTES, 'UTF-8') ?></strong></p>
      <?php endif; ?>
      <p><i class="bi bi-leaf me-2"></i>Carbon Credits: <strong><?= (int) $user['credits'] ?></strong></p>
      <p><i class="bi bi-award me-2"></i>Badge: <span class="badge bg-success"><?= htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') ?></span></p>
    </div>

    <?php if ($isOwnProfile) : ?>
    <div class="card p-4">
      <h5 class="fw-bold mb-3"><i class="bi bi-pencil me-2"></i>Update Details</h5>
      <form method="post" action="/profile">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthMiddleware::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="mb-3">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Contact number</label>
          <input type="number" name="contactno" class="form-control" value="<?= htmlspecialchars((string) $user['contactno'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Gender</label><br>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="sex" id="pMale" value="male" <?= $user['gender'] === 'M' ? 'checked' : '' ?>>
            <label class="form-check-label" for="pMale">Male</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="sex" id="pFemale" value="female" <?= $user['gender'] === 'F' ? 'checked' : '' ?>>
            <label class="form-check-label" for="pFemale">Female</label>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">About you</label>
          <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($user['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary w-100">Update</button>
      </form>
    </div>
    <?php endif; ?>
  </div>

  <!-- Rides -->
  <div class="col-12 col-lg-7">
    <h5 class="fw-bold mb-3">
      <i class="bi bi-car-front me-2"></i>
      <?= $isOwnProfile ? 'My Rides' : htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') . "'s Rides" ?>
    </h5>
    <?php if (empty($rides)) : ?>
      <div class="text-center text-muted py-5">
        <i class="bi bi-car-front fs-1"></i>
        <p class="mt-2"><?= $isOwnProfile ? "You haven't shared any rides yet." : "No rides shared yet." ?></p>
        <?php if ($isOwnProfile) : ?>
          <a href="/share" class="btn btn-primary btn-sm">Share a ride</a>
        <?php endif; ?>
      </div>
    <?php else : ?>
      <div class="row g-3">
        <?php foreach ($rides as $r) : ?>
            <?php
            $km = isset($r['distance_km']) && $r['distance_km'] !== null
                  ? (float) $r['distance_km']
                  : null;
            $distLabel = $km ? round($km) . ' km' : '';
            $icon = ($r['vehicle'] === 'taxi') ? 'bi-taxi-front' : 'bi-car-front';
            $isArchived = !empty($r['uptime']) && (new DateTime($r['uptime'])) <= new DateTime();
            ?>
          <div class="col-12">
            <div class="card ride-card" onclick="window.location='/ride/<?= (int) $r['id'] ?>'">
              <div class="card-body d-flex align-items-center gap-3">
                <i class="bi <?= $icon ?> fs-2 text-primary"></i>
                <div class="flex-grow-1">
                  <div class="fw-semibold">
                    <?= htmlspecialchars($r['from'], ENT_QUOTES, 'UTF-8') ?>
                    <i class="bi bi-arrow-right mx-1"></i>
                    <?= htmlspecialchars($r['to'], ENT_QUOTES, 'UTF-8') ?>
                  </div>
                  <small class="text-muted">
                    <i class="bi bi-clock me-1"></i><?= htmlspecialchars($r['uptime'], ENT_QUOTES, 'UTF-8') ?>
                    &nbsp;·&nbsp;<i class="bi bi-car-front me-1"></i><?= htmlspecialchars(ucfirst($r['vehicle']), ENT_QUOTES, 'UTF-8') ?>
                  </small>
                </div>
                <div class="d-flex flex-column align-items-end gap-1">
                  <?php if ($distLabel) : ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars($distLabel, ENT_QUOTES, 'UTF-8') ?></span>
                  <?php endif; ?>
                  <?php if ($isArchived) : ?>
                    <span class="badge bg-warning text-dark">Archived</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
