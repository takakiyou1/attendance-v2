<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>報酬一覧 | Re:time</title>
  <link rel="stylesheet" href="<?= asset('css/modern-design.css') ?>">
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="container">
      <div class="page-header-content">
        <h1 class="page-title">
          💰 報酬一覧（<?= htmlspecialchars($month) ?>）
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

    <!-- Data Table -->
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>スタッフ名</th>
            <th>支払い方式</th>
            <th>勤務時間</th>
            <th>報酬額</th>
            <th>特別手当</th>
            <th>合計支給額</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data as $d): ?>
            <tr>
              <td><?= htmlspecialchars($d['name']) ?></td>
              <td>
                <span class="badge <?= $d['pay_type'] === 'fixed' ? 'badge-primary' : 'badge-success' ?>">
                  <?= ($d['pay_type'] === 'fixed') ? '固定' : '時間報酬' ?>
                </span>
              </td>
              <td><?= round($d['total_hours'], 1) ?> 時間</td>
              <td><?= number_format($d['calculated_pay']) ?> 円</td>
              <td><?= number_format($d['special_allowance'] ?? 0) ?> 円</td>
              <td><strong><?= number_format($d['total_pay'] ?? $d['calculated_pay']) ?> 円</strong></td>
              <td>
                <a href="<?= url('pay/edit') ?>?user_id=<?= $d['user_id'] ?>&month=<?= $month ?>" class="btn btn-sm btn-secondary">編集</a>
                <a href="<?= url('pay/allowance_list') ?>?user_id=<?= $d['user_id'] ?>&month=<?= $month ?>" class="btn btn-sm btn-primary">手当</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
