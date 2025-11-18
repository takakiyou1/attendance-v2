<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>報酬設定一覧 | Re:time</title>
  <link rel="stylesheet" href="<?= asset('css/modern-design.css') ?>">
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="container">
      <div class="page-header-content">
        <h1 class="page-title">
          🧾 報酬設定一覧
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
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>スタッフ名</th>
            <th>支払い方式</th>
            <th>報酬金額</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['id']) ?></td>
              <td><?= htmlspecialchars($u['name']) ?></td>
              <td>
                <span class="badge <?= $u['pay_type'] === 'fixed' ? 'badge-primary' : 'badge-success' ?>">
                  <?= $u['pay_type'] === 'fixed' ? '固定' : '時間報酬' ?>
                </span>
              </td>
              <td>
                <a href="<?= url('pay/history') ?>?user_id=<?= $u['id'] ?>" class="btn btn-sm btn-secondary">
                  <?= number_format($u['pay_rate']) ?> 円
                </a>
              </td>
              <td>
                <a href="<?= url('pay/setting_edit') ?>?user_id=<?= $u['id'] ?>" class="btn btn-sm btn-primary">編集</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
