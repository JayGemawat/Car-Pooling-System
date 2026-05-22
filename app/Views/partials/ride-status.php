<?php
$steps = [
    'open'      => ['label' => 'Ride Posted',    'icon' => '📋'],
    'requested' => ['label' => 'Rider Requested', 'icon' => '🙋'],
    'confirmed' => ['label' => 'Ride Confirmed',  'icon' => '✅'],
    'completed' => ['label' => 'Completed',        'icon' => '🏁'],
];
$order   = array_keys($steps);
$current = array_search($status ?? 'open', $order);
?>
<div style="display:flex;align-items:flex-start;gap:0;margin:16px 0">
<?php foreach ($steps as $key => $step):
    $idx    = array_search($key, $order);
    $done   = $idx < $current;
    $active = $idx === $current;
    $color  = $done ? '#22c55e' : ($active ? '#3b82f6' : '#d1d5db');
?>
    <div style="flex:1;text-align:center;position:relative">
        <div style="width:32px;height:32px;border-radius:50%;background:<?= $color ?>;
             color:white;display:flex;align-items:center;justify-content:center;
             margin:0 auto;font-size:14px">
            <?= $done ? '✓' : $step['icon'] ?>
        </div>
        <div style="font-size:11px;margin-top:4px;color:<?= $active ? '#1d4ed8' : '#6b7280' ?>;font-weight:<?= $active ? '600' : '400' ?>">
            <?= $step['label'] ?>
        </div>
        <?php if ($idx < count($steps) - 1): ?>
        <div style="position:absolute;top:16px;left:50%;width:100%;height:2px;background:<?= $done ? '#22c55e' : '#e5e7eb' ?>;z-index:-1"></div>
        <?php endif ?>
    </div>
<?php endforeach ?>
</div>
