<?php
$steps = [
    'open'      => ['label' => 'Ride Posted',     'icon' => 'bi-clipboard'],
    'requested' => ['label' => 'Rider Requested', 'icon' => 'bi-hand-index'],
    'confirmed' => ['label' => 'Confirmed',        'icon' => 'bi-check-circle'],
    'completed' => ['label' => 'Completed',        'icon' => 'bi-flag'],
];
$order   = array_keys($steps);
$current = (int) array_search($status ?? 'open', $order);
?>
<div class="status-timeline mb-4">
<?php foreach ($steps as $key => $step):
    $idx    = (int) array_search($key, $order);
    $done   = $idx < $current;
    $active = $idx === $current;
    $bg     = $done ? 'bg-success' : ($active ? 'bg-primary' : 'bg-secondary');
    $lc     = $active ? 'text-primary fw-semibold' : 'text-muted';
?>
  <div class="status-step">
    <div class="dot <?= $bg ?>">
      <?php if ($done): ?>
        <i class="bi bi-check-lg"></i>
      <?php else: ?>
        <i class="bi <?= $step['icon'] ?>"></i>
      <?php endif; ?>
    </div>
    <div class="label <?= $lc ?>"><?= $step['label'] ?></div>
    <?php if ($idx < count($steps) - 1): ?>
      <div class="line" style="background:<?= $done ? '#22c55e' : '#e5e7eb' ?>"></div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>
