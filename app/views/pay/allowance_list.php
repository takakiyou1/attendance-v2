<?php
require __DIR__ . '/../../../config/database.php';

$user_id = $_GET['user_id'] ?? null;
$month   = $_GET['month'] ?? date('Y-m');
if (!$user_id) exit('不正なアクセスです。');

$stmt = $pdo->prepare("
  SELECT * FROM special_allowances
  WHERE user_id = ? AND month = ?
  ORDER BY id ASC
");
$stmt->execute([$user_id, $month]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nameStmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$nameStmt->execute([$user_id]);
$user = $nameStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>特別手当一覧 | Re:time</title>
  <link rel="stylesheet" href="<?= asset('css/modern-design.css') ?>">
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="container">
      <div class="page-header-content">
        <h1 class="page-title">
          🎁 特別手当一覧（<?= htmlspecialchars($user['name']) ?> / <?= htmlspecialchars($month) ?>）
        </h1>
        <div class="page-actions">
          <a href="<?= url('pay/summary') ?>?month=<?= $month ?>" class="btn btn-secondary">
            ← 報酬一覧へ戻る
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container">
    <!-- Add Form -->
    <div class="card" style="margin-bottom: var(--space-lg);">
      <form id="addForm" style="display: flex; align-items: flex-end; gap: var(--space-md); flex-wrap: wrap;">
        <input type="hidden" name="user_id" value="<?= $user_id ?>">
        <input type="hidden" name="month" value="<?= $month ?>">
        <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
          <label class="form-label">手当名</label>
          <input type="text" name="title" class="form-input" placeholder="例：教育担当" required>
        </div>
        <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
          <label class="form-label">金額（円）</label>
          <input type="number" name="amount" class="form-input" placeholder="金額" required>
        </div>
        <button type="submit" class="btn btn-primary">追加</button>
      </form>
    </div>

    <!-- Allowance Table -->
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>手当名</th>
            <th>金額</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= number_format($r['amount']) ?> 円</td>
            <td>
              <button class="btn btn-sm btn-danger" onclick="deleteAllowance(<?= $r['id'] ?>)">削除</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
  // 手当追加
  document.getElementById('addForm').addEventListener('submit', e => {
    e.preventDefault();
    fetch('<?= url('api/save_special_allowance') ?>', {
      method: 'POST',
      body: new FormData(e.target)
    })
    .then(res => res.json())
    .then(res => {
      alert(res.message);
      location.reload();
    });
  });

  // 手当削除
  function deleteAllowance(id) {
    if (!confirm('この手当を削除しますか？')) return;
    fetch('<?= url('api/delete_special_allowance') ?>', {
      method: 'POST',
      body: new URLSearchParams({ id })
    })
    .then(res => res.json())
    .then(res => {
      alert(res.message);
      location.reload();
    });
  }
  </script>
</body>
</html>
