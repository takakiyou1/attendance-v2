<?php
require __DIR__ . '/../../../config/database.php';

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) exit('不正なアクセスです。');

$stmt = $pdo->prepare("
  SELECT * FROM pay_rate_history
  WHERE user_id = ?
  ORDER BY start_date DESC
");
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ユーザー名を取得
$nameStmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$nameStmt->execute([$user_id]);
$user = $nameStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>報酬履歴 | Re:time</title>
  <link rel="stylesheet" href="<?= asset('css/modern-design.css') ?>">
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="container">
      <div class="page-header-content">
        <h1 class="page-title">
          📊 報酬履歴（<?= htmlspecialchars($user['name']) ?> さん）
        </h1>
        <div class="page-actions">
          <a href="<?= url('pay/settings') ?>" class="btn btn-secondary">
            ← 報酬設定一覧へ戻る
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
            <th>支払い方式</th>
            <th>報酬金額</th>
            <th>適用開始日</th>
            <th>適用終了日</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td>
                <span class="badge <?= $r['pay_type'] === 'fixed' ? 'badge-primary' : 'badge-success' ?>">
                  <?= $r['pay_type'] === 'fixed' ? '固定' : '時間報酬' ?>
                </span>
              </td>
              <td><?= number_format($r['pay_rate']) ?> 円</td>
              <td><?= htmlspecialchars($r['start_date']) ?></td>
              <td>
                <?php if ($r['end_date']): ?>
                  <?= htmlspecialchars($r['end_date']) ?>
                <?php else: ?>
                  <span class="badge badge-success">現在有効</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
