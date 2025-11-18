<?php
require __DIR__ . '/../../../config/database.php';
$date = $_GET['date'] ?? date('Y-m-d');

// シフト取得（開始時間順）
$stmt = $pdo->prepare("
  SELECT s.id, u.name, s.shift_start, s.shift_end, s.color
  FROM shifts s
  JOIN users u ON s.user_id = u.id
  WHERE s.date = ?
  ORDER BY s.shift_start ASC
");
$stmt->execute([$date]);
$shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count = count($shifts);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($date) ?> のシフト一覧 | Re:time</title>
  <link rel="stylesheet" href="<?= asset('css/modern-design.css') ?>">
  <style>
    .color-box {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 1px solid var(--color-border-dark);
      border-radius: var(--radius-sm);
      margin-right: var(--space-sm);
      vertical-align: middle;
    }
  </style>
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="container">
      <div class="page-header-content">
        <h1 class="page-title">
          📅 <?= htmlspecialchars($date) ?> のシフト一覧
        </h1>
        <div class="page-actions">
          <a href="<?= url('shift/calendar') ?>" class="btn btn-secondary">
            ← カレンダーに戻る
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container">
    <div class="card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg);">
        <h2 style="margin: 0;">シフト詳細</h2>
        <span class="badge badge-primary">出勤スタッフ数: <?= $count ?> 名</span>
      </div>

      <?php if ($count === 0): ?>
        <div class="empty-state">
          <div class="empty-state-icon">📭</div>
          <h3 class="empty-state-title">シフトはありません</h3>
          <p class="empty-state-desc">この日には登録されたシフトはありません</p>
        </div>
      <?php else: ?>
        <div class="table-wrapper">
          <table class="table">
            <thead>
              <tr>
                <th>スタッフ名</th>
                <th>勤務時間</th>
                <th style="text-align: center;">色</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($shifts as $s): ?>
                <tr>
                  <td><?= htmlspecialchars($s['name']) ?></td>
                  <td><?= htmlspecialchars(substr($s['shift_start'], 0, 5)) ?> 〜 <?= htmlspecialchars(substr($s['shift_end'], 0, 5)) ?></td>
                  <td style="text-align: center;">
                    <span class="color-box" style="background:<?= htmlspecialchars($s['color'] ?? '#000') ?>"></span>
                    <span style="font-size: var(--text-xs); color: var(--color-text-tertiary);"><?= htmlspecialchars($s['color'] ?? '#000000') ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
