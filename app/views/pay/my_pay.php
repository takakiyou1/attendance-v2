<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>自分の報酬 | Re:time</title>
  <link rel="stylesheet" href="<?= asset('css/modern-design.css') ?>">
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="container">
      <div class="page-header-content">
        <h1 class="page-title">
          💰 <?= htmlspecialchars($name) ?> さんの報酬（<?= htmlspecialchars($month) ?>）
        </h1>
        <div class="page-actions">
          <a href="<?= url('menu') ?>" class="btn btn-secondary">
            ← メニューに戻る
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container">
    <!-- Month Navigation -->
    <div class="card" style="margin-bottom: var(--space-lg);">
      <div style="display: flex; align-items: center; justify-content: center; gap: var(--space-md); flex-wrap: wrap;">
        <?php
          $prevMonth = date('Y-m', strtotime($month . ' -1 month'));
          $nextMonth = date('Y-m', strtotime($month . ' +1 month'));
        ?>
        <a href="?month=<?= $prevMonth ?>" class="btn btn-secondary">← 前月</a>
        <form method="GET" action="" style="display: flex; align-items: center; gap: var(--space-sm);">
          <input type="month" name="month" class="form-input" value="<?= $month ?>" style="width: auto;">
          <button type="submit" class="btn btn-primary">表示</button>
        </form>
        <a href="?month=<?= $nextMonth ?>" class="btn btn-secondary">次月 →</a>
      </div>
    </div>

    <!-- Basic Pay Info -->
    <div class="card" style="margin-bottom: var(--space-lg);">
      <h2 style="margin-top: 0; margin-bottom: var(--space-md); font-size: var(--text-xl);">📋 基本情報</h2>
      <div class="table-wrapper">
        <table class="table">
          <tbody>
            <tr>
              <th style="width: 40%;">勤務時間</th>
              <td><?= round($total_hours, 1) ?> 時間</td>
            </tr>
            <tr>
              <th>支払い方式</th>
              <td>
                <span class="badge <?= $pay_type === 'fixed' ? 'badge-primary' : 'badge-success' ?>">
                  <?= ($pay_type === 'fixed') ? '固定報酬' : '時間報酬制（'.number_format($pay_rate).'円）' ?>
                </span>
              </td>
            </tr>
            <tr>
              <th>基本報酬</th>
              <td style="font-weight: var(--font-bold);"><?= number_format($base_pay) ?> 円</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Special Allowances -->
    <div class="card">
      <h2 style="margin-top: 0; margin-bottom: var(--space-md); font-size: var(--text-xl);">🎁 特別手当</h2>
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>手当名</th>
              <th>金額</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($allowances)): ?>
              <tr>
                <td colspan="2" style="color: var(--color-text-secondary);">手当はありません。</td>
              </tr>
            <?php else: ?>
              <?php foreach ($allowances as $a): ?>
                <tr>
                  <td><?= htmlspecialchars($a['title']) ?></td>
                  <td><?= number_format($a['amount']) ?> 円</td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            <tr style="background: var(--color-success-light);">
              <th>合計支給額</th>
              <td style="font-size: var(--text-xl); font-weight: var(--font-bold); color: var(--color-success);">
                <?= number_format($total_pay) ?> 円
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
