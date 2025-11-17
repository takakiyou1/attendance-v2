<?php
/**
 * My Day View - Display shifts for a specific date
 * This view receives data from ShiftController::my_day()
 *
 * Variables available:
 * - $date: The date being viewed
 * - $returnUrl: URL to return to calendar
 * - $isAllShifts: boolean - true if showing all shifts, false for user's shifts only
 * - $shifts: array of shift data
 * - $count: number of shifts
 */
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($date) ?> の<?= $isAllShifts ? '全体シフト' : '自分のシフト' ?></title>
  <link rel="stylesheet" href="/attendance-v2/public/css/modern-design.css">
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
    <div class="container-narrow">
      <div class="page-header-content">
        <h1 class="page-title">
          📅 <?= htmlspecialchars($date) ?>
        </h1>
        <div class="page-actions">
          <a href="<?= htmlspecialchars($returnUrl) ?>" class="btn btn-secondary">
            ← カレンダーに戻る
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container-narrow">
    <div class="card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg);">
        <h2 style="margin: 0;"><?= $isAllShifts ? '全体シフト' : '自分のシフト' ?></h2>
        <span class="badge badge-primary">登録シフト数: <?= $count ?> 件</span>
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
            <?php if ($isAllShifts): ?>
              <thead>
                <tr>
                  <th>スタッフ</th>
                  <th>勤務時間</th>
                  <th style="text-align: center;">色</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($shifts as $s): ?>
                  <tr>
                    <td><?= htmlspecialchars($s['user_name']) ?></td>
                    <td><?= htmlspecialchars(substr($s['shift_start'], 0, 5)) ?> 〜 <?= htmlspecialchars(substr($s['shift_end'], 0, 5)) ?></td>
                    <td style="text-align: center;">
                      <span class="color-box" style="background:<?= htmlspecialchars($s['color'] ?? '#000') ?>"></span>
                      <span style="font-size: var(--text-xs); color: var(--color-text-tertiary);"><?= htmlspecialchars($s['color'] ?? '#000000') ?></span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            <?php else: ?>
              <thead>
                <tr>
                  <th>勤務時間</th>
                  <th style="text-align: center;">色</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($shifts as $s): ?>
                  <tr>
                    <td><?= htmlspecialchars(substr($s['shift_start'], 0, 5)) ?> 〜 <?= htmlspecialchars(substr($s['shift_end'], 0, 5)) ?></td>
                    <td style="text-align: center;">
                      <span class="color-box" style="background:<?= htmlspecialchars($s['color'] ?? '#000') ?>"></span>
                      <span style="font-size: var(--text-xs); color: var(--color-text-tertiary);"><?= htmlspecialchars($s['color'] ?? '#000000') ?></span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            <?php endif; ?>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
